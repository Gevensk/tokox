<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\Customer;
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
        $customers = Customer::all();
        return view('produk.create',compact('customers'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
        'nama' => 'required|string|max:255',
        'satuan' => 'required|string|max:50',
        'customer_id' => 'nullable|exists:customer,id', 
        ]);


        Produk::create($validatedData);

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
        // 1. Validasi input array ukuran
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'warna'     => 'required|string|max:50', // Diwajibkan agar tidak null kosong
            'ukuran'    => 'required|array|min:1', 
        ]);

        $produkId = $request->produk_id;
        // Gunakan ucwords agar jika admin mengetik "hitam" atau "HITAM", otomatis tersimpan rapi menjadi "Hitam"
        $warnaInput = ucwords(strtolower(trim($request->warna))); 
        $ukurans = $request->ukuran; 

        $varianBerhasil = 0;
        $varianDuplikat = [];

        foreach ($ukurans as $ukuranRaw) {
            $ukuranInput = trim($ukuranRaw);

            // --- PROSES CEK DUPLIKAT ---
            $cekDuplikat = ProdukVarian::where('produk_id', $produkId)
                            ->where('warna', $warnaInput)
                            ->where('ukuran', $ukuranInput)
                            ->where('is_defect', 0) 
                            ->first();

            if ($cekDuplikat) {
                $varianDuplikat[] = $ukuranInput;
                continue; 
            }
            // ---------------------------

            // Simpan ke database dengan nilai default operasional konveksimu
            ProdukVarian::create([
                'produk_id'        => $produkId,
                'warna'            => $warnaInput,
                'ukuran'           => $ukuranInput, // S, M, L, XL, atau "Custom Size" akan masuk ke sini
                'stok_tersedia'    => 0,
                'harga_avg_produk' => 0,
                'is_defect'        => 0,
            ]);

            $varianBerhasil++;
        }

        // 3. Response feedback yang lebih rapi ke pengguna
        if ($varianBerhasil === 0 && count($varianDuplikat) > 0) {
            $listDuplikat = implode(', ', $varianDuplikat);
            return redirect()->back()
                ->withInput()
                ->with('error', "Gagal! Ukuran [$listDuplikat] untuk warna $warnaInput sudah terdaftar.");
        }

        if (count($varianDuplikat) > 0) {
            $listDuplikat = implode(', ', $varianDuplikat);
            return redirect()->back()->with('success', "Berhasil menambah $varianBerhasil varian baru untuk warna $warnaInput. (Catatan: Ukuran [$listDuplikat] dilewati karena sudah ada).");
        }

        return redirect()->back()->with('success', "Semua varian ukuran untuk warna $warnaInput berhasil ditambahkan!");
    }

    public function destroyVarian(string $id){
        $varian =ProdukVarian::findOrFail($id);
        $varian->delete();
        return redirect()->back()->with('success','Varian berhasil dihapus!');
    }
}