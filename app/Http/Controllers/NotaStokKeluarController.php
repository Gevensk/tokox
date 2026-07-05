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
        
        // Ambil produk varian yang stoknya ready untuk skenario beli langsung (baik normal maupun defect)
        $produkVarians = ProdukVarian::where('stok_tersedia', '>', 0)
            ->orWhere('stok_defect', '>', 0)
            ->get();

        // Ambil nota order yang belum lunas ATAU sudah lunas tapi belum semua barang diambil (Hanya tipe Customer)
        $notaOrders = NotaOrder::with(['customer', 'job_orders.produk_varian.produk'])
            ->where('tipe_order', 'Customer')
            ->latest()
            ->get()
            ->filter(function($order) {
                if ($order->status_bayar !== 'Lunas') {
                    return true;
                }
                
                $totalOrdered = $order->job_orders->sum('jumlah');
                $totalReleased = \App\Models\NotaStokKeluarHasProdukVarian::whereHas('nota_keluar', function($q) use ($order) {
                    $q->where('nota_order_id', $order->id);
                })->sum('qty');
                
                return $totalReleased < $totalOrdered;
            });

        return view('nota_keluar.create', compact('customers', 'notaOrders', 'produkVarians'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'   => 'required_if:tipe_keluar,penjualan_order|nullable|exists:customer,id',
            'tipe_keluar'   => 'required|in:penjualan_order,penjualan_langsung',
            'nota_order_id' => 'nullable|required_if:tipe_keluar,penjualan_order|exists:nota_order,id',
            'total_bayar'   => 'required|numeric|min:0',
            'items'         => 'required|array|min:1',
            'items.*.produk_varian_id' => 'required|exists:produk_varian,id',
            'items.*.kondisi'          => 'nullable|in:normal,defect',
            'items.*.qty'              => 'required|integer|min:1',
            'items.*.harga_satuan'     => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

    try {
        $notaOrderId = $request->nota_order_id;
        $totalBayarInput = $request->total_bayar;

        // VALIDASI SKENARIO 1: JIKA MELALUI NOTA ORDER (INDEN / CUSTOM BUDI & SITI)
        if ($request->tipe_keluar === 'penjualan_order' && $notaOrderId) {
            $notaOrder = NotaOrder::findOrFail($notaOrderId);
            
            if ($notaOrder->tipe_order !== 'Customer') {
                throw new \Exception("Gagal! Nota order #{$notaOrderId} bukan merupakan pesanan pelanggan.");
            }
            
            // Hitung sisa yang harus dilunasi
            $sisaPelunasan = $notaOrder->status_bayar === 'Lunas' ? 0 : max(0, $notaOrder->total_harga - $notaOrder->nominal_dp_masuk);

            // Aturan: Barang TIDAK BISA KELUAR jika pembayaran kurang dari sisa pelunasan
            if ($totalBayarInput < $sisaPelunasan) {
                return redirect()->back()
                    ->with('error', "Gagal! Sisa pelunasan order ini adalah Rp " . number_format($sisaPelunasan, 0, ',', '.') . ". Uang yang dibayarkan kurang.")
                    ->withInput();
            }

            $notaOrder->update(['status_bayar' => 'Lunas']);
        }

        if ($request->tipe_keluar === 'penjualan_langsung') {
            $customer = Customer::firstOrCreate(
                ['nama' => 'Customer Umum'],
                [
                    'no_hp' => '-',
                    'alamat' => '-'
                ]
            );
            $customerId = $customer->id;
        } else {
            $customerId = $request->customer_id;
        }

        // 1. Simpan data induk (Header)
        $notaKeluar = NotaStokKeluar::create([
            'customer_id'   => $customerId,
            'nota_order_id' => $request->tipe_keluar === 'penjualan_order' ? $notaOrderId : null,
            'tipe_keluar'   => $request->tipe_keluar,
            'total_bayar'   => $totalBayarInput, 
        ]);

        // 2. Simpan detail barang & potong stok pintar (Mengakomodasi Custom Order & Sisa Qty Pesanan)
        foreach ($request->items as $item) {
            $produkVarian = ProdukVarian::findOrFail($item['produk_varian_id']);
            $subtotal = $item['qty'] * $item['harga_satuan'];
            $kondisi = $item['kondisi'] ?? 'normal';

            // VALIDASI SISA PESANAN YANG BELUM DIAMBIL (BAIK CUSTOM MAUPUN STANDAR)
            if ($request->tipe_keluar === 'penjualan_order' && $notaOrderId) {
                $jobQtyOrdered = \App\Models\JobOrder::where('nota_order_id', $notaOrderId)
                    ->where('produk_varian_id', $item['produk_varian_id'])
                    ->value('jumlah') ?? 0;

                $qtySudahDiambil = \App\Models\NotaStokKeluarHasProdukVarian::where('produk_varian_id', $item['produk_varian_id'])
                    ->whereHas('nota_keluar', function($query) use ($notaOrderId) {
                        $query->where('nota_order_id', $notaOrderId);
                    })
                    ->sum('qty') ?? 0;

                $maxAllowedToTake = max(0, $jobQtyOrdered - $qtySudahDiambil);

                if ($item['qty'] > $maxAllowedToTake) {
                    throw new \Exception("Gagal Keluar! Jumlah yang diambil ({$item['qty']} Pcs) melebihi sisa pesanan yang belum diambil ({$maxAllowedToTake} Pcs).");
                }
            }

            // =========================================================================
            // PERUBAHAN LOGIKA POTONG STOK (MENGAKOMODASI STOK TOKO & CUSTOM)
            // =========================================================================
            $isCustomOrder = false;

            if ($request->tipe_keluar === 'penjualan_order' && $notaOrderId) {
                // Cari apakah ada JobOrder terkait yang bertipe custom
                $jobOrder = \App\Models\JobOrder::where('nota_order_id', $notaOrderId)
                    ->where('produk_varian_id', $item['produk_varian_id'])
                    ->first();
                
                if ($jobOrder && $jobOrder->is_custom == 1) {
                    $isCustomOrder = true;
                }
            }

            if ($isCustomOrder) {
                // SKENARIO A: Pengambilan Pesanan Custom (Milik Budi / Siti)
                // 1. Hitung total baju custom yang selesai diproduksi (sesuai kondisi normal/defect)
                $qtyField = ($kondisi === 'defect') ? 'qty_rejected_defect' : 'qty_accepted';
                $stokCustomReady = \App\Models\HasilProduksi::where('produk_varian_id', $item['produk_varian_id'])
                    ->whereHas('job_order', function($query) use ($notaOrderId) {
                        $query->where('nota_order_id', $notaOrderId);
                    })
                    ->where('status_alokasi', 'Pesanan Customer')
                    ->sum($qtyField);

                // 2. Hitung total baju custom yang SUDAH diambil sebelumnya lewat Nota Stok Keluar
                $qtySudahDiambil = \App\Models\NotaStokKeluarHasProdukVarian::where('produk_varian_id', $item['produk_varian_id'])
                    ->where('kondisi', $kondisi)
                    ->whereHas('nota_keluar', function($query) use ($notaOrderId) {
                        $query->where('nota_order_id', $notaOrderId);
                    })
                    ->sum('qty');

                $sisaStokCustom = max(0, $stokCustomReady - $qtySudahDiambil);

                if ($sisaStokCustom < $item['qty']) {
                    $stokLabel = ($kondisi === 'defect') ? 'defect' : 'bagus';
                    throw new \Exception("Gagal Keluar! Sisa baju custom {$stokLabel} yang selesai diproduksi & belum diambil ({$sisaStokCustom} Pcs) kurang dari jumlah yang mau diambil ({$item['qty']} Pcs).");
                }

                // Untuk pesanan custom, kita TIDAK memotong $produkVarian->stok_tersedia 
                // karena stoknya dicatat dinamis melalui hasil_produksi & detail pengeluarannya.
            } else {
                // SKENARIO B: Penjualan Langsung Toko ATAU Pesanan Order Standar (Ready Stock)
                // Validasi & Potong Stok Toko Ready (Bagus vs Defect)
                if ($kondisi === 'defect') {
                    if ($produkVarian->stok_defect < $item['qty']) {
                        throw new \Exception("Stok defect tidak mencukupi untuk varian produk: {$produkVarian->warna} - {$produkVarian->ukuran}");
                    }
                    $produkVarian->stok_defect -= $item['qty'];
                } else {
                    if ($produkVarian->stok_tersedia < $item['qty']) {
                        throw new \Exception("Stok toko tidak mencukupi untuk varian produk: {$produkVarian->warna} - {$produkVarian->ukuran}");
                    }
                    $produkVarian->stok_tersedia -= $item['qty'];
                }
                $produkVarian->save();
            }
            // =========================================================================

            // Simpan ke tabel pivot detail
            $notaKeluar->nota_keluar()->create([
                'produk_varian_id' => $item['produk_varian_id'],
                'kondisi'          => $kondisi,
                'qty'              => $item['qty'],
                'harga_satuan'     => $item['harga_satuan'],
                'subtotal'         => $subtotal,
            ]);
        }

        DB::commit();
        return redirect()->route('nota_keluar.index')->with('success', 'Nota stok keluar berhasil diterbitkan dan stok disesuaikan dengan akurat!');

    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
    }
}

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $nota = NotaStokKeluar::with(['nota_keluar', 'nota_order'])->findOrFail($id);
            $notaOrderId = $nota->nota_order_id;

            // Revert pembayaran nota order ke Belum Lunas jika sebelumnya baru bayar DP
            if ($notaOrderId) {
                $notaOrder = $nota->nota_order;
                if ($notaOrder && $notaOrder->nominal_dp_masuk < $notaOrder->total_harga) {
                    $notaOrder->update(['status_bayar' => 'Belum Lunas']);
                }
            }

            foreach ($nota->nota_keluar as $detail) {
                $produkVarian = ProdukVarian::find($detail->produk_varian_id);
                if (!$produkVarian) {
                    continue;
                }

                $isCustomOrder = false;
                if ($nota->tipe_keluar === 'penjualan_order' && $notaOrderId) {
                    $jobOrder = \App\Models\JobOrder::where('nota_order_id', $notaOrderId)
                        ->where('produk_varian_id', $detail->produk_varian_id)
                        ->first();
                    
                    if ($jobOrder && $jobOrder->is_custom == 1) {
                        $isCustomOrder = true;
                    }
                }

                // Hanya kembalikan stok jika bukan pesanan kustom (Skenario B / stok toko ready)
                if (!$isCustomOrder) {
                    if ($detail->kondisi === 'defect') {
                        $produkVarian->stok_defect += $detail->qty;
                    } else {
                        $produkVarian->stok_tersedia += $detail->qty;
                    }
                    $produkVarian->save();
                }
            }

            // Hapus induk nota (cascade delete akan menghapus rincian detailnya)
            $nota->delete();

            DB::commit();
            return redirect()->route('nota_keluar.index')->with('success', 'Nota stok keluar berhasil dibatalkan dan persediaan toko dipulihkan!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal membatalkan nota stok keluar: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $nota = NotaStokKeluar::with([
            'customer',
            'nota_order',
            'nota_keluar.produk_varian.produk'
        ])->findOrFail($id);

        return view('nota_keluar.show', compact('nota'));
    }

    public function cetakPdf($id)
    {
        $nota = NotaStokKeluar::with(['customer', 'nota_order', 'nota_keluar.produk_varian.produk'])->findOrFail($id);
        return view('nota_keluar.pdf_print', compact('nota'));
    }
}