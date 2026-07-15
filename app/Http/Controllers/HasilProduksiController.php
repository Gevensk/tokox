<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HasilProduksi;
use App\Models\JobOrder;
use App\Models\ProdukVarian;
use App\Models\NotaStokKeluar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
     * Menyimpan data QC hasil jahit secara bertahap / Sekaligus
     */
    public function store(Request $request)
    {
        // Set default 0 jika input kosong (karena value kosong di-submit dari placeholder)
        $request->merge([
            'qty_accepted'          => $request->qty_accepted ?? 0,
            'qty_rejected_defect'   => $request->qty_rejected_defect ?? 0,
            'qty_rejected'          => $request->qty_rejected ?? 0,
            'ongkir_hasil_produksi' => $request->ongkir_hasil_produksi ?? 0,
        ]);

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

            // === AUTOMATION ALOKASI BERDASARKAN STATUS CUSTOM ===
            if ($job->is_custom == 1 && !is_null($job->nota_order?->customer_id)) {
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

            // Total fisik yang selamat dan bernilai ekonomis (bisa dijual) pada job ini
            $totalBarangLayakJual = $request->qty_accepted + $request->qty_rejected_defect;

            // HITUNG HPP AKTUAL & AVERAGE 
            if ($totalBarangLayakJual > 0) {
                $varianNormal = $job->produk_varian;

                if ($varianNormal) {
                    // A. Ambil porsi nilai Job Order induk (Bahan baku + Jasa + Ongkir Kirim Awal) yang tersimpan di total_hpp_job
                    // Jika ini adalah setoran terakhir yang menyelesaikan Job Order, serap seluruh sisa modal jahit yang belum diserap sebelumnya
                    $totalSetelahQCBaru = (int)$totalSudahQC + (int)$totalInputSekarang;
                    $targetJumlah = (int)$job->jumlah;
                    
                    if ($totalSetelahQCBaru >= $targetJumlah) {
                        $totalLayakSebelumnya = HasilProduksi::where('job_order_id', $job->id)
                            ->selectRaw('SUM(qty_accepted + qty_rejected_defect) as total')
                            ->value('total') ?? 0;
                        $porsiModalIndukJobIni = $job->total_hpp_job - (($totalLayakSebelumnya / $job->jumlah) * $job->total_hpp_job);
                    } else {
                        $porsiModalIndukJobIni = ($totalBarangLayakJual / $job->jumlah) * $job->total_hpp_job;
                    }

                    // B. Tambahkan biaya ongkir kurir balik (ongkir hasil produksi) secara penuh untuk job penerimaan saat ini
                    $totalModalAktualjobIni = $porsiModalIndukJobIni + $request->ongkir_hasil_produksi;

                    // C. Hitung HPP Satuan Aktual Baru untuk job ini
                    $hppSatuanAktual = $totalModalAktualjobIni / $totalBarangLayakJual;

                    // HANYA UPDATE STOK JIKA INI PESANAN INTERNAL (Isi Stok Toko)
                    if ($job->is_custom == 0 || is_null($job->nota_order?->customer_id)) {
                        
                        // 3. TAMBAH STOK & HITUNG AVERAGE PRODUK BAGUS (GRADE A - ACCEPTED)
                        if ($request->qty_accepted > 0) {
                            $stokLamaA = $varianNormal->stok_tersedia;
                            
                            // PERBAIKAN: Ganti nama kolom dari harga_rata_rata ke harga_avg_produk
                            $hargaAvgLamaA = $varianNormal->harga_avg_produk;

                            // Rumus Baku Average
                            if (($stokLamaA + $request->qty_accepted) > 0) {
                                // Jika stok lama ada, jalankan akumulasi rata-rata bergulir
                                if ($stokLamaA > 0) {
                                    $hargaAvgBaruA = (($stokLamaA * $hargaAvgLamaA) + ($request->qty_accepted * $hppSatuanAktual)) / ($stokLamaA + $request->qty_accepted);
                                } else {
                                    // Jika stok lama 0 atau minus, langsung pakai HPP aktual job ini
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

    /**
     * Membatalkan setoran QC (Hasil Produksi) secara aman
     */
    public function cancelQC($id)
    {
        if (Auth::user()->role != 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak! Hanya Admin yang dapat membatalkan setoran QC.');
        }

        DB::beginTransaction();
        try {
            // 1. Ambil data HasilProduksi yang ingin dihapus
            $hasil = HasilProduksi::findOrFail($id);
            $job = JobOrder::with(['nota_order', 'produk_varian'])->lockForUpdate()->findOrFail($hasil->job_order_id);

            // 2. CHECK RULE 1: Jika produk custom dan sudah ada Nota Stok Keluar, pembatalan ditolak
            if ($job->is_custom == 1 && $job->nota_order_id) {
                $hasStokKeluar = NotaStokKeluar::where('nota_order_id', $job->nota_order_id)->exists();
                if ($hasStokKeluar) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Gagal membatalkan setoran QC! Produk custom untuk order ini sudah dibuatkan Nota Stok Keluar (barang sudah diambil oleh pelanggan).');
                }
            }

            // 3. CHECK RULE 2: Validasi kecukupan stok di toko jika stok perlu direstorasi (dikurangi)
            $qtyAcceptedToSubtract = $hasil->qty_accepted;
            $qtyDefectToSubtract = $hasil->qty_rejected_defect;
            $totalBarangLayakJual = $qtyAcceptedToSubtract + $qtyDefectToSubtract;

            $varianNormal = $job->produk_varian;

            if ($varianNormal) {
                // Di store, penambahan stok dan average hanya berlaku jika:
                // ($job->is_custom == 0 || is_null($job->nota_order->customer_id))
                $shouldRevertStock = ($job->is_custom == 0 || is_null($job->nota_order?->customer_id));

                if ($shouldRevertStock) {
                    // Validasi kecukupan stok agar tidak minus
                    if ($varianNormal->stok_tersedia < $qtyAcceptedToSubtract) {
                        DB::rollBack();
                        return redirect()->back()->with('error', "Gagal membatalkan! Stok barang bagus di toko tidak mencukupi (Stok saat ini: {$varianNormal->stok_tersedia} pcs, stok yang dibutuhkan untuk pembatalan: {$qtyAcceptedToSubtract} pcs).");
                    }

                    if ($varianNormal->stok_defect < $qtyDefectToSubtract) {
                        DB::rollBack();
                        return redirect()->back()->with('error', "Gagal membatalkan! Stok barang cacat (defect) di toko tidak mencukupi (Stok saat ini: {$varianNormal->stok_defect} pcs, stok yang dibutuhkan untuk pembatalan: {$qtyDefectToSubtract} pcs).");
                    }

                    // Rekalkulasi (Reversi) Average Produk
                    if ($qtyAcceptedToSubtract > 0 && $totalBarangLayakJual > 0) {
                        // Cek apakah batch ini adalah batch yang menyelesaikan Job Order
                        $totalTerQCSebelumDihapus = HasilProduksi::where('job_order_id', $job->id)
                            ->selectRaw('SUM(qty_accepted + qty_rejected_defect + qty_rejected) as total')
                            ->value('total') ?? 0;
                        
                        if ($totalTerQCSebelumDihapus >= $job->jumlah) {
                            $totalLayakSebelumnya = HasilProduksi::where('job_order_id', $job->id)
                                ->where('id', '!=', $hasil->id)
                                ->selectRaw('SUM(qty_accepted + qty_rejected_defect) as total')
                                ->value('total') ?? 0;
                            $porsiModalIndukJobIni = $job->total_hpp_job - (($totalLayakSebelumnya / $job->jumlah) * $job->total_hpp_job);
                        } else {
                            $porsiModalIndukJobIni = ($totalBarangLayakJual / $job->jumlah) * $job->total_hpp_job;
                        }
                        $totalModalAktualjobIni = $porsiModalIndukJobIni + $hasil->ongkir;
                        $hppSatuanAktual = $totalModalAktualjobIni / $totalBarangLayakJual;

                        $stokLamaA = $varianNormal->stok_tersedia;
                        $hargaAvgLamaA = $varianNormal->harga_avg_produk;
                        $stokKurang = $qtyAcceptedToSubtract;
                        $totalStokBaru = $stokLamaA - $stokKurang;

                        if ($totalStokBaru > 0) {
                            $hargaAvgBaruA = (($stokLamaA * $hargaAvgLamaA) - ($stokKurang * $hppSatuanAktual)) / $totalStokBaru;
                        } else {
                            $hargaAvgBaruA = 0;
                        }

                        $varianNormal->harga_avg_produk = max(0, $hargaAvgBaruA);
                    }

                    // Kurangi stok toko kembali
                    $varianNormal->stok_tersedia -= $qtyAcceptedToSubtract;
                    $varianNormal->stok_defect -= $qtyDefectToSubtract;
                    $varianNormal->save();
                }
            }

            // 4. Hapus data HasilProduksi
            $hasil->delete();

            // 5. Update Status Job Order
            $totalTerQCAfterCancel = HasilProduksi::where('job_order_id', $job->id)
                ->selectRaw('SUM(qty_accepted + qty_rejected_defect + qty_rejected) as total')
                ->value('total') ?? 0;

            if ($totalTerQCAfterCancel == 0) {
                $job->update([
                    'status_pesanan' => 'Diproduksi'
                ]);
            } elseif ($totalTerQCAfterCancel < $job->jumlah) {
                $job->update([
                    'status_pesanan' => 'Selesai Parsial'
                ]);
            } else {
                $job->update([
                    'status_pesanan' => 'Selesai'
                ]);
            }

            // 6. Update Status Nota Produksi
            if ($job->nota_produksi_id) {
                DB::table('nota_produksi')
                    ->where('id', $job->nota_produksi_id)
                    ->update(['status_nota' => 'Proses']);
            }

            // 7. Update Status Nota Order
            if ($job->nota_order_id) {
                DB::table('nota_order')
                    ->where('id', $job->nota_order_id)
                    ->update(['status_order' => 'Progress']);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Setoran QC hasil produksi berhasil dibatalkan! Stok produk dan harga rata-rata telah disesuaikan kembali.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat membatalkan QC: ' . $e->getMessage());
        }
    }
}