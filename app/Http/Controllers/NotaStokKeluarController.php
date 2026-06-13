<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\NotaStokKeluar;
use App\Models\NotaOrder;
use App\Models\ProdukVarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaStokKeluarController extends Controller
{
    public function index()
    {
        // Menggunakan eager loading sesuai nama relasimu: 'nota_keluar'
        $notaKeluar = NotaStokKeluar::with(['customer', 'nota_order', 'nota_keluar.produk_varian'])
            ->latest()
            ->paginate(10);

        return view('nota_keluar.index', compact('notaKeluar'));
    }

    public function create()
    {
        $customers = Customer::all();
        
        // Ambil produk varian yang stoknya ready untuk skenario beli langsung
        $produkVarians = ProdukVarian::where('stok_tersedia', '>', 0)->get();

        // PENYESUAIAN: Status diganti dari 'belum lunas' menjadi selain 'Lunas' agar sinkron dengan data nota_order kamu
        $notaOrders = NotaOrder::with('customer')
            ->where('status_bayar', '!=', 'Lunas')
            ->latest()
            ->get();

        return view('nota_keluar.create', compact('customers', 'notaOrders', 'produkVarians'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'   => 'required|exists:customer,id',
            'tipe_keluar'   => 'required|in:penjualan_order,penjualan_langsung',
            'nota_order_id' => 'nullable|required_if:tipe_keluar,penjualan_order|exists:nota_order,id',
            'total_bayar'   => 'required|numeric|min:0',
            'items'         => 'required|array|min:1',
            'items.*.produk_varian_id' => 'required|exists:produk_varian,id',
            'items.*.qty'              => 'required|integer|min:1',
            'items.*.harga_satuan'     => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $notaOrderId = $request->nota_order_id;
            $totalBayarInput = $request->total_bayar;

            // VALIDASI SKENARIO 1: JIKA MELALUI NOTA ORDER (INDEN)
            if ($request->tipe_keluar === 'penjualan_order' && $notaOrderId) {
                $notaOrder = NotaOrder::findOrFail($notaOrderId);
                
                // Hitung sisa yang harus dilunasi
                $sisaPelunasan = $notaOrder->harga_deal - $notaOrder->nominal_dp_masuk;

                // Aturan: Barang TIDAK BISA KELUAR jika pembayaran kurang dari sisa pelunasan
                if ($totalBayarInput < $sisaPelunasan) {
                    return redirect()->back()
                        ->with('error', "Gagal! Sisa pelunasan order ini adalah Rp " . number_format($sisaPelunasan, 0, ',', '.') . ". Uang yang dibayarkan kurang.")
                        ->withInput();
                }

                // PENYESUAIAN: Mengubah update status menggunakan huruf kapital 'Lunas' agar terbaca di View kamu
                $notaOrder->update(['status_bayar' => 'Lunas']);
            }

            // 1. Simpan data induk (Header)
            $notaKeluar = NotaStokKeluar::create([
                'customer_id'   => $request->customer_id,
                'nota_order_id' => $request->tipe_keluar === 'penjualan_order' ? $notaOrderId : null,
                'tipe_keluar'   => $request->tipe_keluar,
                'total_bayar'   => $totalBayarInput, // Uang tunai yang diterima hari ini
            ]);

            // 2. Simpan detail barang & potong stok
            foreach ($request->items as $item) {
                $produkVarian = ProdukVarian::findOrFail($item['produk_varian_id']);

                // Validasi fisik kuantitas gudang
                if ($produkVarian->stok_tersedia < $item['qty']) {
                    throw new \Exception("Stok tidak mencukupi untuk varian produk: {$produkVarian->nama_varian}");
                }

                $subtotal = $item['qty'] * $item['harga_satuan'];

                // Manfaatkan method relasi 'nota_keluar' milikmu untuk insert ke tabel pivot detail
                $notaKeluar->nota_keluar()->create([
                    'produk_varian_id' => $item['produk_varian_id'],
                    'qty'              => $item['qty'],
                    'harga_satuan'     => $item['harga_satuan'],
                    'subtotal'         => $subtotal,
                ]);

                // Potong stok barang ready di gudang
                $produkVarian->stok_tersedia -= $item['qty'];
                $produkVarian->save();
            }

            DB::commit();
            return redirect()->route('nota_keluar.index')->with('success', 'Nota stok keluar berhasil diterbitkan!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}