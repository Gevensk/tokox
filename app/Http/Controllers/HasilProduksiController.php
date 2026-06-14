<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HasilProduksi;
use App\Models\JobOrder;
use App\Models\ProdukVarian;
use Illuminate\Support\Facades\DB;

class HasilProduksiController extends Controller
{
    /**
     * Menampilkan form input QC berdasarkan Job Order tertentu
     */
    public function create(Request $request)
    {
        $jobId = $request->query('job_id');
        
        $job = JobOrder::with(['produk_varian.produk', 'nota_order'])->findOrFail($jobId);

        $totalTerQCSebelumnya = HasilProduksi::where('job_order_id', $job->id)
            ->selectRaw('SUM(qty_accepted + qty_rejected_defect + qty_rejected) as total')
            ->value('total') ?? 0;

        $sisaTanggungan = $job->jumlah - $totalTerQCSebelumnya;

        return view('hasil_produksi.create', compact('job', 'sisaTanggungan', 'totalTerQCSebelumnya'));
    }

    /**
     * Menyimpan data QC hasil jahit secara Parsial / Sekaligus
     */
    public function store(Request $request)
    {
        // 1. Validasi request form input
        $request->validate([
            'job_order_id'          => 'required|exists:job_order,id',
            'qty_accepted'          => 'required|integer|min:0',
            'qty_rejected_defect'   => 'required|integer|min:0',
            'qty_rejected'          => 'required|integer|min:0',
            'ongkir_hasil_produksi' => 'required|numeric|min:0', 
            'catatan_qc'            => 'nullable|string|max:45',
        ]);

        DB::beginTransaction();

        try {
            // Ambil data job order beserta varian produk utamanya
            $job = JobOrder::with(['nota_order', 'produk_varian'])->lockForUpdate()->findOrFail($request->job_order_id);

            // Hitung akumulasi qty yang sudah masuk QC sebelumnya
            $totalSudahQC = HasilProduksi::where('job_order_id', $job->id)
                ->selectRaw('SUM(qty_accepted + qty_rejected_defect + qty_rejected) as total')
                ->value('total') ?? 0;

            $sisaTanggungan = $job->jumlah - $totalSudahQC;
            $totalInputSekarang = $request->qty_accepted + $request->qty_rejected_defect + $request->qty_rejected;

            // Validasi batasan kuota sisa produksi
            if ($totalInputSekarang > $sisaTanggungan) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Gagal! Penjahit menyetor {$totalInputSekarang} Pcs, sedangkan sisa tanggungan baju hanya tinggal {$sisaTanggungan} Pcs.");
            }

            if ($totalInputSekarang <= 0) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Gagal! Jumlah setoran tidak boleh 0 Pcs.");
            }

            // 2. Pemetaan data ke Model HasilProduksi 
            $hasil = new HasilProduksi();
            $hasil->job_order_id        = $job->id;
            $hasil->produk_varian_id    = $job->produk_varian_id; 
            $hasil->tgl_terima          = now();
            $hasil->ongkir              = $request->ongkir_hasil_produksi; 
            $hasil->qty_accepted        = $request->qty_accepted;
            $hasil->qty_rejected_defect = $request->qty_rejected_defect;
            $hasil->qty_rejected        = $request->qty_rejected;
            $hasil->catatan_qc          = $request->catatan_qc;
            $hasil->save();

            // Total fisik yang selamat dan bernilai ekonomis (bisa dijual) pada batch ini
            $totalBarangLayakJual = $request->qty_accepted + $request->qty_rejected_defect;

            // ========================================================================
            // HITUNG HPP AKTUAL & MOVING AVERAGE (LOGIKA SKRIPSI AKUNTANSI)
            // ========================================================================
            if ($totalBarangLayakJual > 0) {
                $varianNormal = $job->produk_varian;

                if ($varianNormal) {
                    // A. Ambil porsi nilai Job Order induk (Bahan baku + Jasa + Ongkir Kirim Awal) yang tersimpan di total_hpp_job
                    // Nilai ini dialokasikan secara proporsional berdasarkan jumlah barang layak jual dari batch ini terhadap target total job
                    $porsiModalIndukBatchIni = ($totalBarangLayakJual / $job->jumlah) * $job->total_hpp_job;

                    // B. Tambahkan biaya ongkir kurir balik (ongkir hasil produksi) secara penuh untuk batch penerimaan saat ini
                    $totalModalAktualBatchIni = $porsiModalIndukBatchIni + $request->ongkir_hasil_produksi;

                    // C. Hitung HPP Satuan Aktual Baru untuk batch ini
                    $hppSatuanAktual = $totalModalAktualBatchIni / $totalBarangLayakJual;

                    // 3. TAMBAH STOK & HITUNG MOVING AVERAGE PRODUK BAGUS (GRADE A - ACCEPTED)
                    if ($request->qty_accepted > 0) {
                        $stokLamaA = $varianNormal->stok_tersedia;
                        $hargaAvgLamaA = $varianNormal->harga_rata_rata;

                        // Rumus Baku Moving Average: (Stok Lama * Avg Lama) + (Qty Baru * HPP Baru) / (Stok Lama + Qty Baru)
                        if (($stokLamaA + $request->qty_accepted) > 0) {
                            $hargaAvgBaruA = (($stokLamaA * $hargaAvgLamaA) + ($request->qty_accepted * $hppSatuanAktual)) / ($stokLamaA + $request->qty_accepted);
                            $varianNormal->harga_rata_rata = $hargaAvgBaruA;
                        }

                        $varianNormal->stok_tersedia += $request->qty_accepted;
                        $varianNormal->save();
                    }

                    // 4. TAMBAH STOK & HITUNG MOVING AVERAGE PRODUK CACAT (GRADE B - REJECTED DEFECT)
                    if ($request->qty_rejected_defect > 0) {
                        // Cari baris produk cacat yang berakhiran SKU '-DF' atau bertanda is_defect = 1
                        $varianDefect = ProdukVarian::where('produk_id', $varianNormal->produk_id)
                            ->where('sku', $varianNormal->sku . '-DF')
                            ->where('is_defect', 1)
                            ->first();

                        // Jika item penampung produk cacat belum pernah ada di database, buat otomatis
                        if (!$varianDefect) {
                            $varianDefect = ProdukVarian::create([
                                'produk_id'       => $varianNormal->produk_id,
                                'nama_varian'     => $varianNormal->nama_varian . ' (Defect/Grade B)',
                                'sku'             => $varianNormal->sku . '-DF',
                                'harga_jual'      => $varianNormal->harga_jual * 0.6, // Otomatis diskon harga jual 40%
                                'stok_tersedia'   => 0,
                                'harga_rata_rata' => 0,
                                'is_defect'       => 1
                            ]);
                        }

                        $stokLamaB = $varianDefect->stok_tersedia;
                        $hargaAvgLamaB = $varianDefect->harga_rata_rata;

                        // Perhitungan Moving Average untuk produk Cacat menggunakan nilai dasar HPP yang sama adilnya
                        if (($stokLamaB + $request->qty_rejected_defect) > 0) {
                            $hargaAvgBaruB = (($stokLamaB * $hargaAvgLamaB) + ($request->qty_rejected_defect * $hppSatuanAktual)) / ($stokLamaB + $request->qty_rejected_defect);
                            $varianDefect->harga_rata_rata = $hargaAvgBaruB;
                        }

                        $varianDefect->stok_tersedia += $request->qty_rejected_defect;
                        $varianDefect->save();
                    }
                }
            }

            // 5. Update Status Job Order dengan standarisasi tipe data Integer
            $totalSetelahQCBaru = (int)$totalSudahQC + (int)$totalInputSekarang;
            $targetJumlah = (int)$job->jumlah;
            
            if ($totalSetelahQCBaru >= $targetJumlah) {
                $job->update([
                    'status_pesanan' => 'Selesai',
                    'tgl_selesai'    => now()
                ]);
            } else {
                $job->update([
                    'status_pesanan' => 'Selesai Parsial'
                ]);
            }

            // 6. OTOMATISASI UPDATE STATUS PADA NOTA PRODUKSI (Halaman Surat Jalan Monitoring)
            if ($job->nota_produksi_id) {
                $semuaJobDiNotaProduksi = JobOrder::where('nota_produksi_id', $job->nota_produksi_id)->get();
                
                $jumlahJobSelesaiQC = $semuaJobDiNotaProduksi->where('status_pesanan', 'Selesai')->count();
                $totalJobDiNotaProduksi = $semuaJobDiNotaProduksi->count();

                if ($jumlahJobSelesaiQC === $totalJobDiNotaProduksi) {
                    DB::table('nota_produksi')
                        ->where('id', $job->nota_produksi_id)
                        ->update(['status_nota' => 'Selesai']);
                } else {
                    DB::table('nota_produksi')
                        ->where('id', $job->nota_produksi_id)
                        ->update(['status_nota' => 'Proses']);
                }
            }

            // 7. Otomatisasi Penutupan Status Nota Order Induk (Pesanan Pelanggan)
            if ($job->nota_order_id) {
                $semuaJobDiNotaIni = JobOrder::where('nota_order_id', $job->nota_order_id)->get();
                $jumlahJobSelesai  = $semuaJobDiNotaIni->where('status_pesanan', 'Selesai')->count();
                $totalSemuaJob     = $semuaJobDiNotaIni->count();

                if ($jumlahJobSelesai === $totalSemuaJob) {
                    DB::table('nota_order')
                        ->where('id', $job->nota_order_id)
                        ->update(['status_order' => 'Selesai']);
                } else {
                    DB::table('nota_order')
                        ->where('id', $job->nota_order_id)
                        ->update(['status_order' => 'Progress']);
                }
            }

            DB::commit();
            return redirect()->route('job_order.index')
                ->with('success', "Setoran berhasil disimpan! Stok produk diperbarui dan Nilai HPP Rata-rata (Moving Average) berhasil dikalkulasi ulang otomatis.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', "Terjadi kesalahan sistem: " . $e->getMessage());
        }
    }
}