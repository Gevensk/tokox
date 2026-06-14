<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotaProduksi;
use App\Models\JobOrder;
use App\Models\Penjahit;
use App\Models\PemakaianBahan;
use App\Models\BahanBaku;
use App\Models\HasilProduksi;
use App\Models\ProdukVarian;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class NotaProduksiController extends Controller
{
    // 1. Halaman Form Buat Nota Produksi (Tempat Centang-Centang Job Order)
    public function create()
    {
        // Ambil penjahit untuk dropdown
        $penjahits = Penjahit::all(); 

        // FIX ERROR: Ambil semua data bahan baku untuk dropdown kustomisasi di view
        $allBahanBaku = BahanBaku::all();

        // Ambil semua job order yang masih 'Antrean' (Belum diproduksi) beserta data varian & produknya
        $jobOrders = JobOrder::with(['produk_varian.produk', 'nota_order.customer'])
                            ->where('status_pesanan', 'Antrean')
                            ->get();

        // Pastikan allBahanBaku ikut dikirim ke view via compact
        return view('nota_produksi.create', compact('penjahits', 'jobOrders', 'allBahanBaku'));
    }

    // 2. Proses Simpan Nota Produksi & Update Status Job Order
    public function store(Request $request)
    {
        $request->validate([
            'penjahit_id' => 'required',
            'job_order_ids' => 'required|array|min:1',
            'ongkir_kirim_bahan' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $tahunBulan = date('Ym');
            $hitungProdBulanIni = \App\Models\NotaProduksi::whereYear('tgl_order', date('Y'))
                                                            ->whereMonth('tgl_order', date('m'))
                                                            ->count();
            $nomorUrutNext = $hitungProdBulanIni + 1;
            $noProdOtomatis = 'PROD-' . $tahunBulan . '-' . str_pad($nomorUrutNext, 4, '0', STR_PAD_LEFT);

            // Simpan Induk Nota Produksi dengan Ongkir Dinamis
            $notaProduksi = \App\Models\NotaProduksi::create([
                'penjahit_id' => $request->penjahit_id,
                'no_surat_jalan' => $noProdOtomatis, 
                'tgl_order' => now(),
                'status_nota' => 'Proses', 
                'status_kirim' => 'Belum Dikirim', 
                'ongkir_kirim_bahan' => $request->ongkir_kirim_bahan 
            ]);

            // --- EDIT BAGIAN CONTROLLER INI ---
            // Di dalam NotaProduksiController.php fungsi store()

            foreach ($request->job_order_ids as $jobOrderId) {
                // Tarik juga relasi nota_order agar bisa kita update statusnya
                $job = JobOrder::with(['produk_varian.bill_of_materials.bahan_baku', 'nota_order'])->find($jobOrderId);
                
                if ($job) {
                    $accumulatedBiayaBahan = 0;

                    // --- BAGIAN 1 & 2: PENGURANGAN BAHAN BAKU (Tetap Sesuai Kode Awal Anda) ---
                    if ($job->produk_varian && $job->produk_varian->bill_of_materials) {
                        foreach ($job->produk_varian->bill_of_materials as $bom) {
                            $totalKebutuhanBahan = $bom->jumlah * $job->jumlah;
                            $bahanBaku = $bom->bahan_baku;

                            if ($bahanBaku) {
                                if ($bahanBaku->stok_total < $totalKebutuhanBahan) {
                                    throw new \Exception("Stok bahan baku [{$bahanBaku->nama}] tidak mencukupi!");
                                }
                                $bahanBaku->decrement('stok_total', $totalKebutuhanBahan);
                                $accumulatedBiayaBahan += ($totalKebutuhanBahan * $bahanBaku->harga_rata_rata);

                                PemakaianBahan::create([
                                    'bahan_baku_id' => $bahanBaku->id,
                                    'job_order_id' => $job->id,
                                    'tgl_keluar' => now(),
                                    'jumlah' => $totalKebutuhanBahan,
                                    'harga_rata_saat_ini' => $bahanBaku->harga_rata_rata 
                                ]);
                            }
                        }
                    }

                    if ($job->is_custom && isset($request->ekstra_bahan_id[$jobOrderId]) && $request->ekstra_jumlah[$jobOrderId] > 0) {
                        $ekstraBahanId = $request->ekstra_bahan_id[$jobOrderId];
                        $ekstraJumlah = $request->ekstra_jumlah[$jobOrderId];

                        $bahanEkstra = BahanBaku::find($ekstraBahanId);
                        if ($bahanEkstra) {
                            if ($bahanEkstra->stok_total < $ekstraJumlah) {
                                throw new \Exception("Stok bahan tambahan [{$bahanEkstra->nama}] tidak mencukupi untuk kustomisasi!");
                            }
                            $bahanEkstra->decrement('stok_total', $ekstraJumlah);
                            $biayaEkstraBahan = $ekstraJumlah * $bahanEkstra->harga_rata_rata;
                            $accumulatedBiayaBahan += $biayaEkstraBahan;

                            PemakaianBahan::create([
                                'bahan_baku_id' => $bahanEkstra->id,
                                'job_order_id' => $job->id,
                                'tgl_keluar' => now(),
                                'jumlah' => $ekstraJumlah,
                                'harga_rata_saat_ini' => $bahanEkstra->harga_rata_rata
                            ]);
                        }
                    }

                    // --- BAGIAN 3: LOCK NILAI HPP AKHIR ---
                    $totalHppJobCalculated = $accumulatedBiayaBahan + ($job->biaya_jasa_satuan * $job->jumlah);

                    $job->update([
                        'nota_produksi_id' => $notaProduksi->id,
                        'status_pesanan' => 'Diproduksi', // Status item berubah jadi Diproduksi
                        'total_biaya_bahan' => $accumulatedBiayaBahan,
                        'total_hpp_job' => $totalHppJobCalculated
                    ]);

                    // === 💥 LOGIKA BARU: SAMBUNGKAN OTOMATISASI STATUS NOTA ORDER INDUK 💥 ===
                    if ($job->nota_order) {
                        // Jika status order sebelumnya masih 'Valid', naikkan menjadi 'Progress'
                        if ($job->nota_order->status_order === 'Valid') {
                            $job->nota_order->update([
                                'status_order' => 'Progress'
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('nota_production.index')->with('success', 'Nota Produksi berhasil dibuat dan HPP Bahan Baku berhasil dikunci!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memproses produksi: ' . $e->getMessage());
        }
    }
    

    public function index()
    {
        // Mengambil data nota produksi beserta relasi penjahitnya
        $notaProduksis = NotaProduksi::with('penjahit')
                                    ->latest()
                                    ->paginate(10); // 10 data per halaman sesuai dengan template view-mu

        return view('nota_produksi.index', compact('notaProduksis'));
    }

    /**
     * 4. Menerima Hasil Jadi dari Penjahit (Menambah Stok Produk Jadi)
     */
    public function finish($id)
    {
        DB::beginTransaction();

        try {
            // Cari nota produksi
            $nota = NotaProduksi::with('jobOrders.produk_varian')->findOrFail($id);

            // Update status nota produksi menjadi Selesai
            $nota->update([
                'status_nota' => 'Selesai'
            ]);

            // Loop semua job order di dalam nota produksi ini
            foreach ($nota->jobOrders as $job) {
                // 1. Update status per item baju menjadi Selesai
                $job->update([
                    'status_pesanan' => 'Selesai'
                ]);

                // 2. LOGIKA SKRIPSI: Tambah stok produk jadi di tabel produk_varian
                $varian = $job->produk_varian;
                if ($varian) {
                    // Tambahkan stok sesuai jumlah yang berhasil diproduksi
                    $varian->increment('stok_total', $job->jumlah);
                }
            }

            DB::commit();
            return redirect()->route('nota_production.index')->with('success', 'Hasil jahit berhasil diterima! Stok produk jadi otomatis bertambah.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyelesaikan produksi: ' . $e->getMessage());
        }
    }

    /**
     * 5. Mengubah status pengiriman bahan baku menjadi 'Dikirim'
     */
    public function kirimBahan($id)
    {
        DB::beginTransaction();

        try {
            // 1. Cari data Nota Produksi beserta semua Job Order di dalamnya
            $nota = NotaProduksi::with('jobOrders')->findOrFail($id);
            
            // 2. Update status logistik pada Nota Produksi
            $nota->update([
                'status_kirim' => 'Dikirim',
                'tgl_kirim' => now()
            ]);

            // 3. Ambil semua ID Nota Order induk yang terlibat di dalam nota produksi ini
            $notaOrderIdsToUpdate = [];
            foreach ($nota->jobOrders as $job) {
                if ($job->nota_order_id) {
                    $notaOrderIdsToUpdate[] = $job->nota_order_id;
                }
            }

            // 4. Jika ada nota order yang terlibat, update statusnya menjadi 'Progress'
            if (!empty($notaOrderIdsToUpdate)) {
                // Saring ID agar unik (mencegah double query jika beberapa job order berasal dari 1 nota order yang sama)
                $uniqueNotaOrderIds = array_unique($notaOrderIdsToUpdate);

                DB::table('nota_order')
                    ->whereIn('id', $uniqueNotaOrderIds)
                    ->where('status_order', 'Valid') // Hanya update nota order yang posisinya masih 'Valid'
                    ->update([
                        'status_order' => 'Progress',
                        'updated_at'   => now()
                    ]);
            }

            DB::commit();
            return redirect()->route('nota_production.index')->with('success', 'Bahan baku berhasil dilepas ke penjahit! Status logistik menjadi DIKIRIM & Status Nota Order otomatis berubah menjadi Progress.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memperbarui status kirim dan progress order: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // Mengambil nota produksi beserta relasi lengkap hingga ke bahan baku (termasuk bahan baku standar & ekstra custom)
        $nota = NotaProduksi::with([
            'penjahit',
            'jobOrders.produk_varian.produk',
            'jobOrders.pemakaian_bahan.bahan_baku'
        ])->findOrFail($id);

        return view('nota_produksi.show', compact('nota'));
    }

    public function cetakPdf($id)
    {
        $nota = NotaProduksi::with([
            'penjahit',
            'jobOrders.produk_varian.produk',
            'jobOrders.pemakaian_bahan.bahan_baku'
        ])->findOrFail($id);

        // Agregasi/Total komponen bahan agar di surat jalan penjahit, item yang sama otomatis dijumlahkan (tidak double baris)
        $komponenBahan = [];
        foreach ($nota->jobOrders as $job) {
            foreach ($job->pemakaian_bahan as $pakai) {
                $bahanId = $pakai->bahan_baku_id;
                $namaBahan = $pakai->bahan_baku->nama ?? 'Bahan';
                $satuan = $pakai->bahan_baku->satuan ?? '';
                
                if (!isset($komponenBahan[$bahanId])) {
                    $komponenBahan[$bahanId] = [
                        'nama' => $namaBahan,
                        'jumlah' => 0,
                        'satuan' => $satuan
                    ];
                }
                $komponenBahan[$bahanId]['jumlah'] += $pakai->jumlah;
            }
        }

        // Sesuai kode awalmu, ini akan langsung me-return view surat jalan
        return view('nota_produksi.pdf_surat_jalan', compact('nota', 'komponenBahan'));
    }

    /**
     * 4. Menerima Hasil Jadi & QC secara Parsial / Bertahap
     */
    // --- EDIT BAGIAN CONTROLLER INI ---
// Di dalam NotaProduksiController.php fungsi storeHasilProduksi()

public function storeHasilProduksi(Request $request)
{
    // 1. Validasi Input Form (Tetap Sesuai Kode Awal Anda)
    $request->validate([
        'job_order_id' => 'required|exists:job_order,id',
        'qty_accepted' => 'required|integer|min:0',
        'qty_rejected_defect' => 'required|integer|min:0',
        'qty_rejected' => 'required|integer|min:0',
        'ongkir_hasil_produksi' => 'required|numeric|min:0',
        'catatan_qc' => 'nullable|string|max:45',
    ]);

    DB::beginTransaction();

    try {
        // 2. Ambil data Job Order terkait beserta Nota Induknya
        $job = JobOrder::with('nota_order')->findOrFail($request->job_order_id);

        // 3. Hitung total semua batch yang sudah masuk sebelumnya
        $totalSudahDiterima = HasilProduksi::where('job_order_id', $job->id)
            ->selectRaw('SUM(qty_accepted + qty_rejected_defect + qty_rejected) as total')
            ->value('total') ?? 0;

        $sisaTanggungan = $job->jumlah - $totalSudahDiterima;
        $totalInputSekarang = $request->qty_accepted + $request->qty_rejected_defect + $request->qty_rejected;

        // 4. Validasi Batas Input
        if ($totalInputSekarang > $sisaTanggungan) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', "Gagal Simpan! Total QC ($totalInputSekarang Pcs) melebihi sisa batas tagihan job order ($sisaTanggungan Pcs).");
        }

        if ($totalInputSekarang <= 0) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', "Gagal Simpan! Total kuantitas yang dimasukkan tidak boleh 0 Pcs.");
        }

        // 5. Simpan Data ke Tabel hasil_produksi
        $hasil = new HasilProduksi();
        $hasil->job_order_id         = $job->id;
        $hasil->produk_varian_id     = $job->produk_varian_id; 
        $hasil->tgl_terima           = now(); 
        $hasil->ongkir               = $request->ongkir_hasil_produksi;
        $hasil->qty_accepted         = $request->qty_accepted;
        $hasil->qty_rejected_defect  = $request->qty_rejected_defect;
        $hasil->qty_rejected         = $request->qty_rejected;
        $hasil->catatan_qc           = $request->catatan_qc;
        $hasil->save();

        // 6. Update Stok Produk Varian di Gudang Utama (Hanya yang lolos/Accepted)
        if ($request->qty_accepted > 0) {
            $varian = ProdukVarian::find($job->produk_varian_id);
            if ($varian) {
                // Sesuai kode awal Anda, ini menambah stok_tersedia/stok_total
                $varian->stok_tersedia = $varian->stok_tersedia + $request->qty_accepted; 
                $varian->save();
            }
        }   

        // 7. Cek apakah Job Order ini sudah SELESAI PENUH (Kuantitas terpenuhi)
        $totalSetelahInputBaru = $totalSudahDiterima + $totalInputSekarang;
        if ($totalSetelahInputBaru >= $job->jumlah) {
            $job->update([
                'status_pesanan' => 'Selesai'
            ]);
        }

        // === 💥 LOGIKA BARU: OTOMATISASI STATUS 'SELESAI' PADA NOTA ORDER INDUK 💥 ===
        if ($job->nota_order) {
            $notaOrderInduk = $job->nota_order;

            // Ambil semua anak job_orders yang berada di bawah nota order yang sama
            $semuaJobDiNotaIni = JobOrder::where('nota_order_id', $notaOrderInduk->id)->get();

            // Hitung berapa banyak job order yang statusnya sudah 'Selesai'
            $jumlahJobSelesai = $semuaJobDiNotaIni->where('status_pesanan', 'Selesai')->count();
            $totalSemuaJob     = $semuaJobDiNotaIni->count();

            // JIKA semua item baju di dalam nota tersebut sudah berstatus 'Selesai'
            if ($jumlahJobSelesai === $totalSemuaJob) {
                $notaOrderInduk->update([
                    'status_order' => 'Selesai'
                ]);
            }
        }

        DB::commit();

        return redirect()->route('hasil_produksi.create', ['job_id' => $job->id])
            ->with('success', "Data batch hasil produksi sebesar $totalInputSekarang Pcs berhasil disimpan, stok diperbarui, dan status order diperiksa!");

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->withInput()
            ->with('error', "Terjadi kesalahan sistem: " . $e->getMessage());
    }
}

    /**
     * Mengirimkan data sisa target ke halaman view create
     */
    public function createHasilProduksi($job_id)
    {
        $job = JobOrder::with('produk_varian.produk')->findOrFail($job_id);
        
        // Hitung total akumulasi yang sudah disetor penjahit sebelumnya (Lolos + Cacat + Rework)
        $totalSudahDiterima = HasilProduksi::where('job_order_id', $job->id)
            ->selectRaw('SUM(qty_accepted + qty_rejected_defect + qty_rejected) as total')
            ->value('total') ?? 0;

        // Sisa yang harus ditagih ke penjahit
        $sisaTanggungan = $job->jumlah - $totalSudahDiterima;

        return view('hasil_produksi.create', compact('job', 'totalSudahDiterima', 'sisaTanggungan'));
    }

}