<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenyesuaianStokBahan;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;

class PenyesuaianStokBahanController extends Controller
{
    /**
     * Menampilkan halaman riwayat penyesuaian stok
     */
    public function index()
    {
        // Mengambil data riwayat, diurutkan dari yang terbaru, beserta relasi data bahan bakunya
        $penyesuaianStoks = PenyesuaianStokBahan::with('bahan_baku')->latest()->paginate(10);
        
        return view('penyesuaian_stok.index', compact('penyesuaianStoks'));
    }

    /**
     * Menampilkan form input penyesuaian stok baru
     */
    public function create()
    {
        // Mengambil semua data master bahan baku untuk pilihan dropdown di form
        $bahanBakus = BahanBaku::all();
        
        return view('penyesuaian_stok.create', compact('bahanBakus'));
    }

    /**
     * Menyimpan data penyesuaian stok dan memperbarui stok master
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'bahan_baku_id' => 'required|exists:bahan_baku,id',
            'jumlah'        => 'required|numeric|min:0.01',
            'tipe'          => 'required|in:in,out',
            'keterangan'    => 'required|string|max:255',
        ]);

        // 2. Gunakan Database Transaction agar aman
        DB::beginTransaction();

        try {
            $bahan = BahanBaku::findOrFail($request->bahan_baku_id);

            // Cek jika tipenya out, apakah stok_total nya cukup?
            if ($request->tipe == 'out' && $bahan->stok_total < $request->jumlah) {
                return redirect()->back()->with('error', 'Stok tidak cukup untuk melakukan pengurangan!');
            }

            // Simpan data riwayat penyesuaian ke database
            PenyesuaianStokBahan::create([
                'bahan_baku_id' => $request->bahan_baku_id,
                'jumlah'        => $request->jumlah,
                'tipe'          => $request->tipe,
                'keterangan'    => $request->keterangan,
            ]);

            // Update otomatis stok_total utama di tabel bahan_baku
            if ($request->tipe == 'in') {
                $bahan->stok_total += $request->jumlah; // Menggunakan stok_total
            } else {
                $bahan->stok_total -= $request->jumlah; // Menggunakan stok_total
            }
            $bahan->save();

            DB::commit();
            return redirect()->route('penyesuaian_stok.index')->with('success', 'Stok berhasil disesuaikan!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}