<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\HasilProduksi;
use App\Models\JobOrder;
use App\Models\Customer;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        // Mengambil produk beserta variannya dan customer
        $produk = Produk::with(['varians', 'customer'])->get();
        return view('produk.index', compact('produk'));
    }

    public function create()
    {
        return view('produk.create');
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

    /**
     * Menampilkan Detail Produk & Menghitung Pembagian Kuantitas Stok Otomatis
     */
    public function show(string $id)
    {
        $produk = Produk::findOrFail($id);

        // Ambil semua varian milik produk ini
        $varians = ProdukVarian::where('produk_id', $id)->get();

        $detailStokCustom = []; // Untuk menampung siapa saja yang belum mengambil barang custom

        // Loop setiap varian untuk menyuntikkan kalkulasi pemisahan kuantitas secara real-time
        foreach ($varians as $varian) {
            
            // 1. Kuantitas Ready Stock (Sesuai akumulasi Langkah 2)
            $varian->stok_toko_ready = $varian->stok_tersedia;

            // 2. Kuantitas Custom Order yang sedang/sudah dikerjakan (Milik Budi / Siti)
            // Diambil otomatis dari tabel hasil_produksi yang status_alokasi-nya 'Pesanan Customer'
            $stokCustomReady = HasilProduksi::where('produk_varian_id', $varian->id)
                ->where('status_alokasi', 'Pesanan Customer')
                ->sum('qty_accepted');

            // 3. Kuantitas Custom Order yang sudah diambil lewat Nota Stok Keluar
            $qtySudahDiambil = \App\Models\NotaStokKeluarHasProdukVarian::where('produk_varian_id', $varian->id)
                ->whereHas('nota_keluar.nota_order', function($query) {
                    $query->whereNotNull('customer_id');
                })
                ->sum('qty');

            $varian->stok_pesanan_customer = max(0, $stokCustomReady - $qtySudahDiambil);

            // 4. Cari rincian kepemilikan (siapa customer pemesan yang belum mengambil barang ini)
            $jobOrders = JobOrder::where('produk_varian_id', $varian->id)
                ->where('is_custom', 1)
                ->with(['nota_order.customer'])
                ->get();

            foreach ($jobOrders as $job) {
                if ($job->nota_order && $job->nota_order->customer) {
                    $produced = HasilProduksi::where('job_order_id', $job->id)->sum('qty_accepted') ?? 0;
                    
                    $taken = \App\Models\NotaStokKeluarHasProdukVarian::where('produk_varian_id', $varian->id)
                        ->whereHas('nota_keluar', function($q) use ($job) {
                            $q->where('nota_order_id', $job->nota_order_id);
                        })
                        ->sum('qty') ?? 0;

                    $sisa = $produced - $taken;

                    if ($sisa > 0) {
                        $detailStokCustom[] = [
                            'varian' => "{$varian->warna} - {$varian->ukuran}",
                            'customer_nama' => $job->nota_order->customer->nama ?? 'N/A',
                            'no_order' => $job->nota_order->no_order,
                            'nota_order_id' => $job->nota_order_id,
                            'total_produksi' => $produced,
                            'sudah_diambil' => $taken,
                            'sisa' => $sisa
                        ];
                    }
                }
            }
        }

        return view('produk.show', compact('produk', 'varians', 'detailStokCustom'));
    }

    public function edit(string $id)
    {
        $produk = Produk::findOrFail($id);
        return view('produk.edit', compact('produk'));
    }

    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'nama'   => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'customer_id' => 'nullable|exists:customer,id',
        ]);

        $produk = Produk::findOrFail($id);
        
        $data = [
            'nama'   => trim($request->nama),
            'satuan' => trim($request->satuan),
        ];

        if ($request->has('customer_id')) {
            $data['customer_id'] = $request->customer_id;
        }

        $produk->update($data);

        return redirect('/produk')->with('success', 'Data produk berhasil diubah!');
    }

    public function destroy(string $id)
    {
        try {
            $produk = Produk::findOrFail($id);
            $produk->delete();
            return redirect('/produk')->with('success', 'Produk berhasil dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()->with('error', 'Produk ini tidak dapat dihapus karena salah satu variannya sudah memiliki riwayat transaksi.');
            }
            throw $e;
        }
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
        try {
            $varian = ProdukVarian::findOrFail($id);
            $varian->delete();
            return redirect()->back()->with('success', 'Varian berhasil dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()->with('error', 'Varian produk ini tidak dapat dihapus karena sudah memiliki riwayat transaksi (Job Order, Hasil Produksi, atau Nota Stok Keluar).');
            }
            throw $e;
        }
    }
}