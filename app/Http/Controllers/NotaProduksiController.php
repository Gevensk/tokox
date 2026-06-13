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

            foreach ($request->job_order_ids as $jobOrderId) {
                $job = JobOrder::with('produk_varian.bill_of_materials.bahan_baku')->find($jobOrderId);
                
                if ($job) {
                    $accumulatedBiayaBahan = 0;

                    // --- BAGIAN 1: PROSES PENGURANGAN BAHAN BAKU UTAMA (BOM STANDAR) ---
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

                    // --- BAGIAN 2: OTOMATISASI PENYESUAIAN STOK BAHAN EKSTRA CUSTOM ---
                    if ($job->is_custom && isset($request->ekstra_bahan_id[$jobOrderId]) && $request->ekstra_jumlah[$jobOrderId] > 0) {
                        $ekstraBahanId = $request->ekstra_bahan_id[$jobOrderId];
                        $ekstraJumlah = $request->ekstra_jumlah[$jobOrderId];

                        $bahanEkstra = BahanBaku::find($ekstraBahanId);
                        if ($bahanEkstra) {
                            if ($bahanEkstra->stok_total < $ekstraJumlah) {
                                throw new \Exception("Stok bahan tambahan [{$bahanEkstra->nama}] tidak mencukupi untuk kustomisasi!");
                            }

                            // 1. Potong stok fisik gudang untuk bahan tambahan
                            $bahanEkstra->decrement('stok_total', $ekstraJumlah);

                            // 2. Hitung nilai rupiah bahan tambahan dan akumulasikan ke HPP
                            $biayaEkstraBahan = $ekstraJumlah * $bahanEkstra->harga_rata_rata;
                            $accumulatedBiayaBahan += $biayaEkstraBahan;

                            // 3. Catat history ke tabel pemakaian_bahan agar sinkron dengan relasi di ERD
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
                        'status_pesanan' => 'Diproduksi',
                        'total_biaya_bahan' => $accumulatedBiayaBahan,
                        'total_hpp_job' => $totalHppJobCalculated
                    ]);
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
        try {
            $nota = NotaProduksi::findOrFail($id);
            $nota->update([
                'status_kirim' => 'Dikirim',
                'tgl_kirim' => now() // Otomatis mengisi field tgl_kirim yang ada di database kamu!
            ]);

            return redirect()->route('nota_production.index')->with('success', 'Status logistik bahan baku diperbarui menjadi: DIKIRIM.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui status kirim: ' . $e->getMessage());
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
    public function storeHasilProduksi(Request $request)
    {
        // 1. Validasi Input Form
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
            // 2. Ambil data Job Order terkait
            $job = JobOrder::findOrFail($request->job_order_id);

            // 3. SINKRONISASI: Hitung total semua batch yang sudah masuk (Lolos + Cacat + Rework)
            $totalSudahDiterima = HasilProduksi::where('job_order_id', $job->id)
                ->selectRaw('SUM(qty_accepted + qty_rejected_defect + qty_rejected) as total')
                ->value('total') ?? 0;

            $sisaTanggungan = $job->jumlah - $totalSudahDiterima;

            // 4. Validasi Sisi Server
            $totalInputSekarang = $request->qty_accepted + $request->qty_rejected_defect + $request->qty_rejected;

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
                    $varian->stok_tersedia = $varian->stok_tersedia + $request->qty_accepted; 
                    $varian->save();
                }
            }   

            DB::commit();

            // 7. Jika sukses, kembali ke form dengan pesan flash sukses
            return redirect()->route('hasil_produksi.create', ['job_id' => $job->id])
                ->with('success', "Data batch hasil produksi sebesar $totalInputSekarang Pcs berhasil disimpan & stok diperbarui!");

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