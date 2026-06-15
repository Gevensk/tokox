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

        // Ambil semua data bahan baku untuk dropdown kustomisasi di view
        $allBahanBaku = BahanBaku::all();

        // Ambil semua job order yang masih 'Antrean' (Belum diproduksi) beserta data varian & produknya
        $jobOrders = JobOrder::with(['produk_varian.produk', 'nota_order.customer'])
                            ->where('status_pesanan', 'Antrean')
                            ->get();

        return view('nota_produksi.create', compact('penjahits', 'jobOrders', 'allBahanBaku'));
    }

    // 2. Proses Simpan Nota Produksi & Update Status Job Order
    public function store(Request $request)
    {
        // 🔥 PERBAIKAN VALIDASI: Disamakan dengan nama input di view (biaya_jasa)
        $request->validate([
            'penjahit_id' => 'required',
            'job_order_ids' => 'required|array|min:1',
            'biaya_jasa' => 'required|array', 
            'biaya_jasa.*' => 'required|numeric|min:0', 
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

            $notaProduksi = \App\Models\NotaProduksi::create([
                'penjahit_id' => $request->penjahit_id,
                'no_surat_jalan' => $noProdOtomatis, 
                'tgl_order' => now(),
                'status_nota' => 'Proses', 
                'status_kirim' => 'Belum Dikirim', 
                'ongkir_kirim_bahan' => $request->ongkir_kirim_bahan 
            ]);

            // === LOGIKA AKUMULASI TOTAL QTY UNTUK ALOKASI ONGKIR KIRIM BAHAN PROPORSIONAL ===
            $totalQtyNota = JobOrder::whereIn('id', $request->job_order_ids)->sum('jumlah');

            foreach ($request->job_order_ids as $jobOrderId) {
                $job = JobOrder::with(['produk_varian.bill_of_materials.bahan_baku', 'nota_order'])->find($jobOrderId);
                
                if ($job) {
                    $accumulatedBiayaBahan = 0;

                    // 🔥 PERBAIKAN BINDING: Ambil nilai upah dari array 'biaya_jasa'
                    $biayaJasaSatuanRil = isset($request->biaya_jasa[$jobOrderId]) ? $request->biaya_jasa[$jobOrderId] : 0;

                    $sudahAdaBahan = PemakaianBahan::where('job_order_id', $job->id)->exists();

                    if ($job->produk_varian && $job->produk_varian->bill_of_materials) {
                        foreach ($job->produk_varian->bill_of_materials as $bom) {
                            $totalKebutuhanBahan = $bom->jumlah * $job->jumlah;
                            $bahanBaku = $bom->bahan_baku;

                            if ($bahanBaku) {
                                if (!$sudahAdaBahan) {
                                    if ($bahanBaku->stok_total < $totalKebutuhanBahan) {
                                        throw new \Exception("Stok bahan baku [{$bahanBaku->nama}] tidak mencukupi!");
                                    }
                                    $bahanBaku->decrement('stok_total', $totalKebutuhanBahan);

                                    PemakaianBahan::create([
                                        'bahan_baku_id' => $bahanBaku->id,
                                        'job_order_id'  => $job->id,
                                        'tgl_keluar'    => now(),
                                        'jumlah'        => $totalKebutuhanBahan,
                                        'harga_rata_saat_ini' => $bahanBaku->harga_rata_rata 
                                    ]);
                                }
                                $accumulatedBiayaBahan += ($totalKebutuhanBahan * $bahanBaku->harga_rata_rata);
                            }
                        }
                    }

                    // --- BAGIAN BAHAN TAMBAHAN (CUSTOM) ---
                    if ($job->is_custom && isset($request->ekstra_bahan_id[$jobOrderId]) && $request->ekstra_jumlah[$jobOrderId] > 0) {
                        $ekstraBahanId = $request->ekstra_bahan_id[$jobOrderId];
                        $ekstraJumlah = $request->ekstra_jumlah[$jobOrderId];
                        $bahanEkstra = BahanBaku::find($ekstraBahanId);

                        if ($bahanEkstra) {
                            if (!$sudahAdaBahan) {
                                if ($bahanEkstra->stok_total < $ekstraJumlah) {
                                    throw new \Exception("Stok bahan tambahan [{$bahanEkstra->nama}] tidak mencukupi untuk kustomisasi!");
                                }
                                $bahanEkstra->decrement('stok_total', $ekstraJumlah);

                                PemakaianBahan::create([
                                    'bahan_baku_id' => $bahanEkstra->id,
                                    'job_order_id'  => $job->id,
                                    'tgl_keluar'    => now(),
                                    'jumlah'        => $ekstraJumlah,
                                    'harga_rata_saat_ini' => $bahanEkstra->harga_rata_rata
                                ]);
                            }
                            $biayaEkstraBahan = $ekstraJumlah * $bahanEkstra->harga_rata_rata;
                            $accumulatedBiayaBahan += $biayaEkstraBahan;
                        }
                    }

                    // --- HITUNG PORSI ONGKIR KIRIM BAHAN SECARA PROPORSIONAL ---
                    $porsiOngkirKirim = 0;
                    if ($totalQtyNota > 0 && $request->ongkir_kirim_bahan > 0) {
                        $porsiOngkirKirim = ($job->jumlah / $totalQtyNota) * $request->ongkir_kirim_bahan;
                    }

                    // --- LOCK NILAI HPP AWAL ---
                    $totalJasaJahitJob = $biayaJasaSatuanRil * $job->jumlah;
                    $totalHppJobCalculated = $accumulatedBiayaBahan + $totalJasaJahitJob + $porsiOngkirKirim;

                    $job->update([
                        'nota_produksi_id'   => $notaProduksi->id,
                        'biaya_jasa_satuan'  => $biayaJasaSatuanRil, 
                        'status_pesanan'     => 'Diproduksi', 
                        'total_biaya_bahan'  => $accumulatedBiayaBahan,
                        'total_hpp_job'      => $totalHppJobCalculated
                    ]);

                    if ($job->nota_order) {
                        if ($job->nota_order->status_order === 'Valid') {
                            $job->nota_order->update([
                                'status_order' => 'Progress'
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('nota_production.index')->with('success', 'Nota Produksi berhasil dibuat, Distribusi Ongkir & HPP Awal berhasil dikunci!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memproses produksi: ' . $e->getMessage());
        }
    }
    
    public function index()
    {
        $notaProduksis = NotaProduksi::with('penjahit')
                                    ->latest()
                                    ->paginate(10);

        return view('nota_produksi.index', compact('notaProduksis'));
    }

    public function finish($id)
    {
        DB::beginTransaction();

        try {
            $nota = NotaProduksi::with('jobOrders.produk_varian')->findOrFail($id);

            $nota->update([
                'status_nota' => 'Selesai'
            ]);

            foreach ($nota->jobOrders as $job) {
                $job->update([
                    'status_pesanan' => 'Selesai'
                ]);

                $varian = $job->produk_varian;
                if ($varian) {
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

    public function kirimBahan($id)
    {
        DB::beginTransaction();

        try {
            $nota = NotaProduksi::with('jobOrders')->findOrFail($id);
            
            $nota->update([
                'status_kirim' => 'Dikirim',
                'tgl_kirim' => now()
            ]);

            $notaOrderIdsToUpdate = [];
            foreach ($nota->jobOrders as $job) {
                if ($job->nota_order_id) {
                    $notaOrderIdsToUpdate[] = $job->nota_order_id;
                }
            }

            if (!empty($notaOrderIdsToUpdate)) {
                $uniqueNotaOrderIds = array_unique($notaOrderIdsToUpdate);

                DB::table('nota_order')
                    ->whereIn('id', $uniqueNotaOrderIds)
                    ->where('status_order', 'Valid') 
                    ->update([
                        'status_order' => 'Progress',
                        'updated_at'   => now()
                    ]);
            }

            DB::commit();
            return redirect()->route('nota_production.index')->with('success', 'Bahan baku berhasil dilepas ke penjahit! Status logistik menjadi DIKIRIM.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memperbarui status kirim: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
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

        $komponenBahan = [];
        foreach ($nota->jobOrders as $job) {
            foreach ($job->pemakaian_bahan as $pakai) {
                $bahanId = $pakai->bahan_baku_id;
                $namaBahan = $pakai->bahan_baku->nama ?? 'Bahan';
                $satuan = $pakai->bahan_baku->satuan ?? '';
                
                if (!isset($komponenBahan[$bahanId])) {
                    $komponenBahan[$bahanId] = [
                        'name' => $namaBahan, // Disamakan dengan struktur awal
                        'jumlah' => 0,
                        'satuan' => $satuan
                    ];
                }
                $komponenBahan[$bahanId]['jumlah'] += $pakai->jumlah;
            }
        }

        return view('nota_produksi.pdf_surat_jalan', compact('nota', 'komponenBahan'));
    }

    // 4. Menerima Hasil Jadi & QC secara Parsial / Bertahap
    public function storeHasilProduksi(Request $request)
    {
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
            $job = JobOrder::with('nota_order')->findOrFail($request->job_order_id);

            $totalSudahDiterima = HasilProduksi::where('job_order_id', $job->id)
                ->selectRaw('SUM(qty_accepted + qty_rejected_defect + qty_rejected) as total')
                ->value('total') ?? 0;

            $sisaTanggungan = $job->jumlah - $totalSudahDiterima;
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

            // === AUTOMATION ALOKASI BERDASARKAN STATUS CUSTOM ===
            if ($job->is_custom == 1) {
                $alokasiOtomatis = 'Pesanan Customer'; 
            } else {
                $alokasiOtomatis = 'Stok Toko'; 
            }

            $hasil = new HasilProduksi();
            $hasil->job_order_id         = $job->id;
            $hasil->produk_varian_id     = $job->produk_varian_id; 
            $hasil->tgl_terima           = now(); 
            $hasil->ongkir               = $request->ongkir_hasil_produksi;
            $hasil->qty_accepted         = $request->qty_accepted;
            $hasil->qty_rejected_defect  = $request->qty_rejected_defect;
            $hasil->qty_rejected         = $request->qty_rejected;
            $hasil->catatan_qc           = $request->catatan_qc;
            $hasil->status_alokasi       = $alokasiOtomatis; 
            $hasil->save();

            // Sesuai logika, hanya stok reguler yang masuk ke inventory bebas jual
            // === PROSES UPDATE INVENTORY BERDASARKAN ALOKASI ===
if ($request->qty_accepted > 0) {
    $varian = ProdukVarian::find($job->produk_varian_id);
    
    if ($varian) {
        if ($alokasiOtomatis === 'Pesanan Customer') {
            // JIKA PESANAN CUSTOM: Tambah ke kolom stok custom Anda
            // (Silakan sesuaikan nama kolom 'stok_custom' dengan nama asli di database Anda, misal: stok_pesanan / stok_custom)
            $varian->stok_custom = $varian->stok_custom + $request->qty_accepted;
        } else {
            // JIKA READY STOCK: Masuk ke stok toko reguler
            $varian->stok_tersedia = $varian->stok_tersedia + $request->qty_accepted;
        }
        $varian->save();
    }
}

            $totalSetelahInputBaru = $totalSudahDiterima + $totalInputSekarang;
            if ($totalSetelahInputBaru >= $job->jumlah) {
                $job->update([
                    'status_pesanan' => 'Selesai'
                ]);
            }

            if ($job->nota_order) {
                $notaOrderInduk = $job->nota_order;
                $semuaJobDiNotaIni = JobOrder::where('nota_order_id', $notaOrderInduk->id)->get();
                $jumlahJobSelesai = $semuaJobDiNotaIni->where('status_pesanan', 'Selesai')->count();
                $totalSemuaJob     = $semuaJobDiNotaIni->count();

                if ($jumlahJobSelesai === $totalSemuaJob) {
                    $notaOrderInduk->update([
                        'status_order' => 'Selesai'
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('hasil_produksi.create', ['job_id' => $job->id])
                ->with('success', "Data batch hasil produksi sebesar $totalInputSekarang Pcs berhasil disimpan dengan alokasi [$alokasiOtomatis]!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', "Terjadi kesalahan sistem: " . $e->getMessage());
        }
    }

    public function createHasilProduksi($job_id)
    {
        $job = JobOrder::with('produk_varian.produk')->findOrFail($job_id);
        
        $totalSudahDiterima = HasilProduksi::where('job_order_id', $job->id)
            ->selectRaw('SUM(qty_accepted + qty_rejected_defect + qty_rejected) as total')
            ->value('total') ?? 0;

        $sisaTanggungan = $job->jumlah - $totalSudahDiterima;

        return view('hasil_produksi.create', compact('job', 'totalSudahDiterima', 'sisaTanggungan'));
    }

    public function daftarSiapKirim()
    {
        $siapKirim = HasilProduksi::with([
                        'job_order.nota_order.customer', 
                        'produk_varian.produk'
                     ])
                     ->where('status_alokasi', 'Pesanan Customer')
                     ->latest()
                     ->get();

        return view('hasil_produksi.siap_kirim', compact('siapKirim'));
    }
}