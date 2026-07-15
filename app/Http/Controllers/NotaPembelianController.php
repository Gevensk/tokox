<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotaPembelian;
use App\Models\NotaPembelianDetail;
use App\Models\BahanBaku;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Facades\DB;

class NotaPembelianController extends Controller
{
    //
    public function create(){
        $suppliers=Supplier::all();
        $bahan_baku=BahanBaku::all();
        return view('nota_pembelian.create',compact('suppliers','bahan_baku'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Simpan Header Nota
            $nota = NotaPembelian::create([
                'supplier_id' => $request->supplier_id,
                'tgl_beli' => $request->tgl_beli,
                'total_harga_bahan' => $request->total_harga_bahan,
                'ongkir' => $request->ongkir ?? 0,
                'total_bayar' => $request->total_harga_bahan + ($request->ongkir ?? 0),
            ]);

            $totalHargaBahan = $request->total_harga_bahan;
            $totalOngkir = $request->ongkir ?? 0;

            // 2. Loop setiap bahan
            foreach ($request->items as $item) {
                $bahan = BahanBaku::findOrFail($item['bahan_baku_id']);

                // --- LOGIKA ALOKASI ONGKIR ---
                // Contoh: Harga bahan 10.000, Ongkir 5.000, Total Belanja 100.000. 
                // Rasio ongkir = 5.000 / 100.000 = 0.05
                // Ongkir per item = 0.05 * 10.000 = 500. Jadi harga beli asli adalah 10.500
                
                $hargaSatuanBeli = $item['harga_satuan'];
                $alokasiOngkir = ($totalHargaBahan > 0) ? ($totalOngkir / $totalHargaBahan) * $hargaSatuanBeli : 0;
                $hargaBeliNetto = $hargaSatuanBeli + $alokasiOngkir; // Ini harga beli yang sudah ada ongkirnya

                // --- LOGIKA AVERAGE DENGAN HARGA NETTO ---
                $stokLama = $bahan->stok_total;
                $hargaRataLama = $bahan->harga_rata_rata;
                $stokBeli = $item['jumlah'];

                $totalStokBaru = $stokLama + $stokBeli;

                if ($totalStokBaru > 0) {
                    // Rumus: ((Stok Lama * Harga Rata Lama) + (Stok Baru * Harga Beli Netto)) / Total Stok
                    $hargaRataBaru = (($stokLama * $hargaRataLama) + ($stokBeli * $hargaBeliNetto)) / $totalStokBaru;
                } else {
                    $hargaRataBaru = $hargaBeliNetto;
                }

                // 3. Simpan Detail
                NotaPembelianDetail::create([
                    'nota_pembelian_id' => $nota->id,
                    'bahan_baku_id' => $item['bahan_baku_id'],
                    'jumlah' => $stokBeli,
                    'harga_satuan' => $hargaSatuanBeli, // Tetap simpan harga asli di detail nota
                    'subtotal' => $stokBeli * $hargaSatuanBeli
                ]);

                // 4. Update Master Bahan (Stok & Harga Rata-rata yang baru)
                $bahan->update([
                    'stok_total' => $totalStokBaru,
                    'harga_rata_rata' => $hargaRataBaru
                    
                ]);
            }
    //         dd([
    //     'Stok Lama' => $stokLama,
    //     'Stok Baru' => $stokBeli,
    //     'Total Stok' => $totalStokBaru,
    //     'Harga Rata Baru' => $hargaRataBaru
    // ]);
            DB::commit();
            return redirect()->route('nota_pembelian.index')->with('success', 'Pembelian berhasil dan harga rata-rata telah diperbarui (termasuk ongkir).');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $history = NotaPembelian::with('supplier')->orderBy('tgl_beli', 'desc')->get();
        return view('nota_pembelian.index', compact('history'));
    }

    public function show($id)
    {
        // Mengambil nota beserta detail dan nama bahan bakunya
        $nota = NotaPembelian::with(['supplier', 'details.bahanBaku'])->findOrFail($id);
        return view('nota_pembelian.show', compact('nota'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $nota = NotaPembelian::with('details')->findOrFail($id);

            // 1. Cek kecukupan stok untuk semua item sebelum melakukan delete
            foreach ($nota->details as $detail) {
                $bahan = BahanBaku::findOrFail($detail->bahan_baku_id);
                if ($bahan->stok_total < $detail->jumlah) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Gagal menghapus: Bahan baku "' . $bahan->nama . '" sudah digunakan dalam transaksi produksi lain (stok saat ini tidak mencukupi untuk dikurangi).');
                }
            }

            // 2. Jika semua cukup, lakukan update stok dan hitung ulang harga rata-rata
            $totalHargaBahan = $nota->total_harga_bahan;
            $totalOngkir = $nota->ongkir;

            foreach ($nota->details as $detail) {
                $bahan = BahanBaku::findOrFail($detail->bahan_baku_id);
                
                $alokasiOngkir = ($totalHargaBahan > 0) ? ($totalOngkir / $totalHargaBahan) * $detail->harga_satuan : 0;
                $hargaBeliNetto = $detail->harga_satuan + $alokasiOngkir;

                $stokLama = $bahan->stok_total;
                $stokKurang = $detail->jumlah;
                $totalStokBaru = $stokLama - $stokKurang;

                if ($totalStokBaru > 0) {
                    // Rumus membalikkan average:
                    // (Total Nilai Saat Ini - Total Nilai Pembelian Yang Dihapus) / Total Stok Baru
                    $hargaRataBaru = (($stokLama * $bahan->harga_rata_rata) - ($stokKurang * $hargaBeliNetto)) / $totalStokBaru;
                    if ($hargaRataBaru < 0) {
                        $hargaRataBaru = 0;
                    }
                } else {
                    $hargaRataBaru = 0;
                }

                $bahan->update([
                    'stok_total' => $totalStokBaru,
                    'harga_rata_rata' => $hargaRataBaru
                ]);
            }

            // 3. Hapus data detail dan header nota
            $nota->details()->delete();
            $nota->delete();

            DB::commit();
            return redirect()->route('nota_pembelian.index')->with('success', 'Nota pembelian berhasil dihapus dan HPP rata-rata bahan baku berhasil dikoreksi.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menghapus nota pembelian: ' . $e->getMessage());
        }
    }
}

