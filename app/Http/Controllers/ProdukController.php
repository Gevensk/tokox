<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\HasilProduksi;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        // Mengambil produk beserta variannya
        $produk = Produk::with('varians')->get();
        return view('produk.index', compact('produk'));
    }

    public function create()
    {
        // KONTROLER BERSIH: Tidak perlu melempar data Customer ke form produk reguler lagi
        return view('produk.create');
    }

    public function store(Request $request)
    {
        // Mengeluarkan customer_id dari validasi
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
        ]);

        Produk::create($validatedData);

        return redirect('/produk')->with('success', 'Produk berhasil ditambahkan!');
    }

    /**
     * Menampilkan Detail Produk & Menghitung Pembagian Kuantitas Stok Otomatis
     */
    public function show(string $id)
    {
        $produk = Produk::findOrFail($id);

        // Ambil semua varian milik produk ini
        $varians = ProdukVarian::where('produk_id', $id)->get();

        // Loop setiap varian untuk menyuntikkan kalkulasi pemisahan kuantitas secara real-time
        foreach ($varians as $varian) {
            
            // 1. Kuantitas Ready Stock (Sesuai akumulasi Langkah 2)
            $varian->stok_toko_ready = $varian->stok_tersedia;

            // 2. Kuantitas Custom Order yang sedang/sudah dikerjakan (Milik Budi / Siti)
            // Diambil otomatis dari tabel hasil_produksi yang status_alokasi-nya 'Pesanan Customer'
            $varian->stok_pesanan_customer = HasilProduksi::where('produk_varian_id', $varian->id)
                ->where('status_alokasi', 'Pesanan Customer')
                ->sum('qty_accepted');
        }

        return view('produk.show', compact('produk', 'varians'));
    }

    public function edit(string $id)
    {
        $produk = Produk::findOrFail($id);
        // Bersih dari data customer
        return view('produk.edit', compact('produk'));
    }

    public function update(Request $request, string $id)
    {
        // Bersih dari customer_id
        $request->validate([
            'nama'   => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
        ]);

        $produk = Produk::findOrFail($id);
        
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

        return redirect('/produk')->with('success', 'Produk berhasil deleted!');
    }

    // ==========================================
    // BAGIAN VARIANS (TETAP SAMA SEPERTI AWAL)
    // ==========================================

    public function storeVarian(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'warna'     => 'required|string|max:50',
            'ukuran'    => 'required|array|min:1', 
        ]);

        $produkId = $request->produk_id;
        $warnaInput = ucwords(strtolower(trim($request->warna))); 
        $ukurans = $request->ukuran; 

        $varianBerhasil = 0;
        $varianDuplikat = [];

        foreach ($ukurans as $ukuranRaw) {
            $ukuranInput = trim($ukuranRaw);

            $cekDuplikat = ProdukVarian::where('produk_id', $produkId)
                            ->where('warna', $warnaInput)
                            ->where('ukuran', $ukuranInput)
                            ->first();

            if ($cekDuplikat) {
                $varianDuplikat[] = $ukuranInput;
                continue; 
            }

            ProdukVarian::create([
                'produk_id'        => $produkId,
                'warna'            => $warnaInput,
                'ukuran'           => $ukuranInput,
                'stok_tersedia'    => 0,
                'stok_defect'      => 0,
                'harga_avg_produk' => 0,
            ]);

            $varianBerhasil++;
        }

        if ($varianBerhasil === 0 && count($varianDuplikat) > 0) {
            $listDuplikat = implode(', ', $varianDuplikat);
            return redirect()->back()->withInput()->with('error', "Gagal! Ukuran [$listDuplikat] untuk warna $warnaInput sudah terdaftar.");
        }

        if (count($varianDuplikat) > 0) {
            $listDuplikat = implode(', ', $varianDuplikat);
            return redirect()->back()->with('success', "Berhasil menambah $varianBerhasil varian baru untuk warna $warnaInput. (Catatan: Ukuran [$listDuplikat] dilewati karena sudah ada).");
        }

        return redirect()->back()->with('success', "Semua varian ukuran untuk warna $warnaInput berhasil ditambahkan!");
    }

    public function destroyVarian(string $id)
    {
        $varian = ProdukVarian::findOrFail($id);
        $varian->delete();
        return redirect()->back()->with('success','Varian berhasil dihapus!');
    }
}