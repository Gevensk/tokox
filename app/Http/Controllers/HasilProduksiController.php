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
            'qty_rejected_defect'   => 'required|integer|min:0', // Cacat mikro (Masuk ke kolom stok_defect)
            'qty_rejected'          => 'required|integer|min:0', // Gagal total (Sampah, tidak masuk stok sama sekali)
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

            // === AUTOMATION ALOKASI BERDASARKAN STATUS CUSTOM ===
            if ($job->is_custom == 1 && $job->nota_order && !is_null($job->nota_order->customer_id)) {
                $alokasiOtomatis = 'Pesanan Customer';
            } else {
                $alokasiOtomatis = 'Stok Toko';
            }

            // 2. Pemetaan data ke Model HasilProduksi (Log Riwayat QC)
            $hasil = new HasilProduksi();
            $hasil->job_order_id        = $job->id;
            $hasil->produk_varian_id    = $job->produk_varian_id; 
            $hasil->tgl_terima          = now();
            $hasil->ongkir              = $request->ongkir_hasil_produksi; 
            $hasil->qty_accepted        = $request->qty_accepted;
            $hasil->qty_rejected_defect = $request->qty_rejected_defect;
            $hasil->qty_rejected        = $request->qty_rejected;
            $hasil->catatan_qc          = $request->catatan_qc;
            $hasil->status_alokasi      = $alokasiOtomatis;
            $hasil->save();

            // Total fisik yang selamat dan bernilai ekonomis (bisa dijual) pada batch ini
            $totalBarangLayakJual = $request->qty_accepted + $request->qty_rejected_defect;

            // ========================================================================
            // HITUNG HPP AKTUAL & MOVING AVERAGE (LOGIKA AKUNTANSI KONVEKSI)
            // ========================================================================
            if ($totalBarangLayakJual > 0) {
                $varianNormal = $job->produk_varian;

                if ($varianNormal) {
                    // A. Ambil porsi nilai Job Order induk (Bahan baku + Jasa + Ongkir Kirim Awal) yang tersimpan di total_hpp_job
                    $porsiModalIndukBatchIni = ($totalBarangLayakJual / $job->jumlah) * $job->total_hpp_job;

                    // B. Tambahkan biaya ongkir kurir balik (ongkir hasil produksi) secara penuh untuk batch penerimaan saat ini
                    $totalModalAktualBatchIni = $porsiModalIndukBatchIni + $request->ongkir_hasil_produksi;

                    // C. Hitung HPP Satuan Aktual Baru untuk batch ini
                    $hppSatuanAktual = $totalModalAktualBatchIni / $totalBarangLayakJual;

                    // HANYA UPDATE STOK JIKA INI PESANAN INTERNAL (Isi Stok Toko)
                    if ($job->is_custom == 0 || is_null($job->nota_order->customer_id)) {
                        
                        // 3. TAMBAH STOK & HITUNG MOVING AVERAGE PRODUK BAGUS (GRADE A - ACCEPTED)
                        if ($request->qty_accepted > 0) {
                            $stokLamaA = $varianNormal->stok_tersedia;
                            
                            // PERBAIKAN: Ganti nama kolom dari harga_rata_rata ke harga_avg_produk
                            $hargaAvgLamaA = $varianNormal->harga_avg_produk;

                            // Rumus Baku Moving Average
                            if (($stokLamaA + $request->qty_accepted) > 0) {
                                // Jika stok lama ada, jalankan akumulasi rata-rata bergulir
                                if ($stokLamaA > 0) {
                                    $hargaAvgBaruA = (($stokLamaA * $hargaAvgLamaA) + ($request->qty_accepted * $hppSatuanAktual)) / ($stokLamaA + $request->qty_accepted);
                                } else {
                                    // Jika stok lama 0 atau minus, langsung pakai HPP aktual batch ini
                                    $hargaAvgBaruA = $hppSatuanAktual;
                                }
                                
                                // PERBAIKAN: Ganti nama kolom ke harga_avg_produk
                                $varianNormal->harga_avg_produk = $hargaAvgBaruA;
                            }

                            $varianNormal->stok_tersedia += $request->qty_accepted;
                        }

                        // 4. ALOKASI BARANG CACAT MIKRO (GRADE B) LANGSUNG KE KOLOM stok_defect
                        if ($request->qty_rejected_defect > 0) {
                            // Sesuai keinginan Anda, data langsung dimasukkan ke kolom stok_defect 
                            // tanpa perlu membuat baris varian baru / SKU baru di database.
                            $varianNormal->stok_defect += $request->qty_rejected_defect;
                        }

                        // Catatan: $request->qty_rejected murni diabaikan (tidak masuk ke penambahan stok mana pun)

                        // Simpan semua perubahan stok dan nilai harga_avg_produk ke database
                        $varianNormal->save();
                    }
                }
            }

            // 5. Update Status Job Order dengan standarisasi tipe data Integer
            $totalSetelahQCBaru = (int)$totalSudahQC + (int)$totalInputSekarang;
            $targetJumlah = (int)$job->jumlah;
            
            if ($totalSetelahQCBaru >= $targetJumlah) {
                $job->update([
                    'status_pesanan' => 'Selesai'
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
                ->with('success', "Hasil produksi berhasil disimpan!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', "Terjadi kesalahan sistem: " . $e->getMessage());
        }
    }
}