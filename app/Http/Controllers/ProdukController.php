<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\ProdukVarian;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::with('varians')->get();
        return view('produk.index', compact('produk'));
    }

    public function create()
    {
        return view('produk.create');
    }

    public function store(Request $request)
    {
        $request->validate([
        'nama'   => 'required|string|max:255', 
        'satuan' => 'required|string|max:50'
        ]);

        Produk::create([
            'nama'            => trim($request->nama),
            'satuan'          => trim($request->satuan),
        ]);

        return redirect('/produk')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function show(string $id){
        $produk = Produk::with('varians')->findOrFail($id);
        return view('produk.show',compact('produk'));
    }

    public function edit(string $id)
    {
        // Menggunakan findOrFail agar otomatis 404 jika ID salah
        $produk = Produk::findOrFail($id);
        return view('produk.edit', compact('produk'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama'   => 'required|string|max:255',
            'satuan' => 'required|string|max:50'
        ]);

        $produk = Produk::findOrFail($id);
        
        // Kita update secara spesifik agar stok_total dan harga_rata_rata 
        // tidak sengaja berubah lewat form edit
        $produk->update([
            'nama'   => trim($request->nama),
            'satuan' => trim($request->satuan),
        ]);

        return redirect('/produk')->with('success', 'Data produk berhasil diubah!');
    }

    public function destroy(string $id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();

        return redirect('/produk')->with('success', 'Produk berhasil dihapus!');
    }

    // bagian varian

    public function storeVarian(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'warna'     => 'nullable|string|max:50',
            'ukuran'    => 'nullable|string|max:20',
        ]);

        // --- PROSES CEK DUPLIKAT ---
        // Cari apakah sudah ada produk_id dengan warna & ukuran yang sama
        $cekDuplikat = ProdukVarian::where('produk_id', $request->produk_id)
                        ->where('warna', trim($request->warna))
                        ->where('ukuran', trim($request->ukuran))
                        ->where('is_defect', 0) // Pastikan hanya cek yang status normal
                        ->first();

        if ($cekDuplikat) {
            return redirect()->back()
                ->withInput() // Agar ketikan user tidak hilang
                ->with('error', 'Varian dengan warna dan ukuran tersebut sudah terdaftar!');
        }
        // ---------------------------

        ProdukVarian::create([
            'produk_id'        => $request->produk_id,
            'warna'            => trim($request->warna),
            'ukuran'           => trim($request->ukuran),
            'stok_tersedia'    => 0,
            'harga_avg_produk' => 0,
            'is_defect'        => 0,
        ]);

        return redirect()->back()->with('success', 'Varian berhasil ditambahkan!');
    }

    public function destroyVarian(string $id){
        $varian =ProdukVarian::findOrFail($id);
        $varian->delete();
        return redirect()->back()->with('success','Varian berhasil dihapus!');
    }
}