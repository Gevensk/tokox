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
            $job = JobOrder::with('nota_order')->lockForUpdate()->findOrFail($request->job_order_id);

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

            // 3. Tambah stok komoditas di tabel produk_varian (Hanya yang Accepted)
            if ($request->qty_accepted > 0) {
                $varian = ProdukVarian::lockForUpdate()->find($job->produk_varian_id);
                if ($varian) {
                    $varian->stok_tersedia += $request->qty_accepted;
                    $varian->save();
                }
            }

            // 4. Update Status Job Order dengan standarisasi tipe data Integer
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

            // 5. OTOMATISASI UPDATE STATUS PADA NOTA PRODUKSI (Halaman Surat Jalan Monitoring)
            if ($job->nota_produksi_id) {
                // Tarik semua job order yang berada di bawah satu surat jalan nota jahit ini
                $semuaJobDiNotaProduksi = JobOrder::where('nota_produksi_id', $job->nota_produksi_id)->get();
                
                $jumlahJobSelesaiQC = $semuaJobDiNotaProduksi->where('status_pesanan', 'Selesai')->count();
                $totalJobDiNotaProduksi = $semuaJobDiNotaProduksi->count();

                if ($jumlahJobSelesaiQC === $totalJobDiNotaProduksi) {
                    // Jika seluruh item di nota produksi ini berstatus 'Selesai', tandai Nota Produksi Utama sebagai Selesai
                    DB::table('nota_produksi')
                        ->where('id', $job->nota_produksi_id)
                        ->update(['status_nota' => 'Selesai']);
                } else {
                    // Jika masih ada sisa item jahit yang belum lunas
                    DB::table('nota_produksi')
                        ->where('id', $job->nota_produksi_id)
                        ->update(['status_nota' => 'Proses']);
                }
            }

            // 6. Otomatisasi Penutupan Status Nota Order Induk (Pesanan Pelanggan)
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
                ->with('success', "Setoran berhasil disimpan! {$request->qty_accepted} Pcs masuk ke stok produk jadi.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', "Terjadi kesalahan sistem: " . $e->getMessage());
        }
    }
}