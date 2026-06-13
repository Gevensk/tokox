<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotaOrder;
use App\Models\JobOrder;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class NotaOrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi inputan dari form web
        $request->validate([
            'tipe_order' => 'required|in:Internal,Customer',
            'customer_id' => 'required_if:tipe_order,Customer',
            'tgl_deadline' => 'required|date',
            'harga_deal' => 'required|numeric',
            'nominal_dp_masuk' => 'required|numeric',
            'items' => 'required|array|min:1', // List baju yang dipesan
        ]);

        // Amankan transaksi database dengan DB::beginTransaction
        DB::beginTransaction();

        try {
            // 2. Generate Nomor Order Otomatis (Contoh: ORD-202606-0001)
            $tahunBulan = date('Ym');
            $hitungOrderBulanIni = NotaOrder::whereYear('tgl_order', date('Y'))
                                            ->whereMonth('tgl_order', date('m'))
                                            ->count();
            $nomorUrutNext = $hitungOrderBulanIni + 1;
            $noOrderOtomatis = 'ORD-' . $tahunBulan . '-' . str_pad($nomorUrutNext, 4, '0', STR_PAD_LEFT);

            // 3. Simpan data induk ke table nota_order
            $nota = NotaOrder::create([
                'no_order' => $noOrderOtomatis,
                'tipe_order' => $request->tipe_order,
                'customer_id' => $request->tipe_order == 'Customer' ? $request->customer_id : null,
                'tgl_order' => now(),
                'tgl_deadline' => $request->tgl_deadline,
                'harga_deal' => $request->harga_deal,
                'nominal_dp_masuk' => $request->nominal_dp_masuk,
                'status_bayar' => $request->nominal_dp_masuk >= $request->harga_deal ? 'Lunas' : 'Belum Lunas',
                'status_order' => 'Valid',
            ]);

            // 4. Looping untuk menyimpan detail ke table job_order
            foreach ($request->items as $item) {
                
                // --- KITA KEMBALIKAN LOGIKA BoM LANGSUNG KE PRODUK_VARIAN ---
                // Sesuai ERD: produk_varian -> bill_of_materials -> bahan_baku
                $varian = ProdukVarian::with('bill_of_materials.bahan_baku')->find($item['produk_varian_id']);
                
                $estimasiBiayaBahanPerPcs = 0;
                
                // Cek jika varian ini memiliki racikan BoM di database
                if ($varian && $varian->bill_of_materials) {
                    foreach ($varian->bill_of_materials as $bom) {
                        // Rumus: Takaran bahan baku di BoM x Harga Rata-rata Bahan Saat ini
                        if ($bom->bahan_baku) {
                            $estimasiBiayaBahanPerPcs += ($bom->jumlah * $bom->bahan_baku->harga_rata_rata);
                        }
                    }
                }

                $totalBiayaBahanJob = $estimasiBiayaBahanPerPcs * $item['jumlah'];
                $totalHppJob = $totalBiayaBahanJob + ($item['biaya_jasa_satuan'] * $item['jumlah']);
                // ---------------------------------------------------------------------

                // Simpan baris detail baju (Job Order)
                JobOrder::create([
                    'nota_order_id' => $nota->id,
                    'produk_varian_id' => $item['produk_varian_id'],
                    'jumlah' => $item['jumlah'],
                    'biaya_jasa_satuan' => $item['biaya_jasa_satuan'],
                    'is_custom' => $item['is_custom'] ?? 0,
                    'ld_req' => $item['ld_req'] ?? null,
                    'pb_req' => $item['pb_req'] ?? null,
                    'catatan_custom' => $item['catatan_custom'] ?? null,
                    'total_biaya_bahan' => $totalBiayaBahanJob, 
                    'total_hpp_job' => $totalHppJob,       
                    'status_pesanan' => 'Antrean',
                ]);
            }

            // --- INI PERBAIKANNYA ---
            // 1. Kunci data ke database agar tidak hilang / ter-rollback
            DB::commit();

            // 2. Alihkan halaman ke index dengan pesan sukses
            return redirect()->route('nota_order.index')->with('success', 'Nota Order berhasil disimpan!');

        }
        catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal...'.$e->getMessage())->withInput();
        }
    }

    public function create()
    {
        $customers = Customer::all();
        // Cukup ambil produk induknya saja untuk dropdown pertama
        $produks = Produk::all(); 

        return view('nota_order.create', compact('customers', 'produks'));
    }

    // Fungsi API Baru untuk merespon pilihan produk
    public function getVarianByProduk($id)
    {
        // Cari varian yang memiliki produk_id sesuai pilihan kasir
        $varians = ProdukVarian::where('produk_id', $id)->get();
        return response()->json($varians);
    }

    public function index()
    {
        // Mengambil data nota order beserta relasi job_orders dan customer-nya
        // Diurutkan dari yang paling baru masuk
        $notaOrders = NotaOrder::with(['customer', 'job_orders'])
                                ->latest()
                                ->paginate(10); // Menggunakan pagination agar rapi

        return view('nota_order.index', compact('notaOrders'));
    }
}
