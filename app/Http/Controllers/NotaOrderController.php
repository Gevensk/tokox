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
use Barryvdh\DomPDF\Facade\Pdf;

class NotaOrderController extends Controller
{
    public function show($id)
    {
        $notaOrder = NotaOrder::with([
            'customer', 
            'job_orders.produk_varian.produk', 
            'job_orders.pemakaian_bahan.bahan_baku'
        ])->findOrFail($id);

        return view('nota_order.show', compact('notaOrder'));
    }

    // 2. HALAMAN EDIT INFO NOTA
    public function edit($id)
    {
        $notaOrder = NotaOrder::with('job_orders.produk_varian.produk')->findOrFail($id);
        $customers = Customer::orderBy('nama', 'asc')->get();

        return view('nota_order.edit', compact('notaOrder', 'customers'));
    }

    // 3. PROSES UPDATE DATA INFO NOTA
    public function update(Request $request, $id)
    {
        $notaOrder = NotaOrder::findOrFail($id);

        $request->validate([
            'tipe_order'    => 'required|in:Customer,Internal',
            'tgl_deadline'  => 'required|date',
            'harga_deal'    => 'required|numeric|min:0',
            'nominal_dp_masuk' => 'required|numeric|min:0',
            'status_bayar'  => 'required|in:Belum Lunas,Lunas',
        ]);

        $notaOrder->update([
            'tipe_order'       => $request->tipe_order,
            'customer_id'      => $request->tipe_order === 'Customer' ? $request->customer_id : null,
            'tgl_deadline'     => $request->tgl_deadline,
            'harga_deal'       => $request->harga_deal,
            'nominal_dp_masuk' => $request->nominal_dp_masuk,
            'status_bayar'     => $request->status_bayar,
        ]);

        return redirect()->route('nota_order.index')->with('success', 'Informasi nota order berhasil diperbarui.');
    }

    // 4. 🔥 PROSES HAPUS CASCADE (MEMBERSIHKAN SEMUA DATA TURUNAN)
    public function destroy($id)
    {
        $notaOrder = NotaOrder::with('job_orders')->findOrFail($id);

        // Proteksi: Jika ada salah satu item baju yang sudah masuk proses produksi, nota jangan boleh dihapus!
        foreach ($notaOrder->job_orders as $job) {
            if ($job->status_pesanan !== 'Antrean' || !is_null($job->nota_produksi_id)) {
                return redirect()->back()->with('error', 'Gagal Menghapus! Beberapa item dalam nota ini sudah diproses ke meja produksi CMT.');
            }
        }

        // Gunakan Database Transaction agar jika satu gagal, semua dibatalkan
        DB::beginTransaction();
        try {
            foreach ($notaOrder->job_orders as $job) {
                // A. Bersihkan data pemakaian kain (BoM draf) milik job ini terlebih dahulu
                DB::table('pemakaian_bahan')->where('job_order_id', $job->id)->delete();
                
                // B. Hapus baris data Job Order-nya
                $job->delete();
            }

            // C. Terakhir, hapus data induk Nota Order
            $notaOrder->delete();

            DB::commit();
            return redirect()->route('nota_order.index')->with('success', 'Nota Order beserta seluruh antrean job dan draf pemakaian kain berhasil dihapus bersih.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
    public function store(Request $request)
    {
        // 1. Validasi inputan dari form web
        $request->validate([
            'tipe_order' => 'required|in:Internal,Customer',
            'customer_id' => 'required_if:tipe_order,Customer',
            'tgl_deadline' => 'required|date',
            'harga_deal' => 'required|numeric',
            'nominal_dp_masuk' => 'required|numeric',
            'items' => 'required|array|min:1', 
            'items.*.produk_varian_id' => 'required|exists:produk_varian,id',
            'items.*.jumlah' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {
            // 2. Generate Nomor Order Otomatis
            $tahunBulan = date('Ym');
            $hitungOrderBulanIni = NotaOrder::whereYear('tgl_order', date('Y'))
                                            ->whereMonth('tgl_order', date('m'))
                                            ->count();
            $nomorUrutNext = $hitungOrderBulanIni + 1;
            $noOrderOtomatis = 'ORD-' . $tahunBulan . '-' . str_pad($nomorUrutNext, 4, '0', STR_PAD_LEFT);

            // 💡 Cukup tambah baris ini: ambil semua 'harga_satuan_jual' lalu petakan berdasarkan 'produk_varian_id'
            $hargajual = array_column($request->items, 'harga_satuan_jual', 'produk_varian_id');

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
                'rincian_harga_nota' => $hargajual, // 🔥 Cukup sisipkan ini
            ]);

            // 4. Looping untuk menyimpan detail ke table job_order & pemakaian_bahan
            foreach ($request->items as $item) {
    
    $varian = ProdukVarian::with('bill_of_materials.bahan_baku')->find($item['produk_varian_id']);
    $estimasiBiayaBahanPerPcs = 0;
    $daftarBahanOtomatis = [];
    
    if ($varian && $varian->bill_of_materials) {
        foreach ($varian->bill_of_materials as $bom) {
            if ($bom->bahan_baku) {
                // Ambil harga rata-rata bahan baku saat ini dari master data
                $hargaBahanSaatIni = $bom->bahan_baku->harga_rata_rata;

                $estimasiBiayaBahanPerPcs += ($bom->jumlah * $hargaBahanSaatIni);
                
                // 💡 MASUKKAN harga_rata_saat_ini KE SINI
                $daftarBahanOtomatis[] = [
                    'bahan_baku_id'        => $bom->bahan_baku_id,
                    'jumlah'               => $bom->jumlah * $item['jumlah'],
                    'harga_rata_saat_ini'  => $hargaBahanSaatIni, 
                    'tgl_keluar'           => null,
                ];
            }
        }
    }

    $totalBiayaBahanJob = $estimasiBiayaBahanPerPcs * $item['jumlah'];
    $totalHppJobInitial = $totalBiayaBahanJob; 

    // Simpan baris Job Order
    $jobOrder = JobOrder::create([
        'nota_order_id'     => $nota->id,
        'produk_varian_id'  => $item['produk_varian_id'],
        'jumlah'            => $item['jumlah'],
        'biaya_jasa_satuan' => 0, 
        'is_custom'         => $item['is_custom'] ?? 0,
        'catatan_custom'    => $item['catatan_custom'] ?? null,
        'total_biaya_bahan' => $totalBiayaBahanJob, 
        'total_hpp_job'     => $totalHppJobInitial,    
        'status_pesanan'    => 'Antrean',
    ]);

    // Saat menyimpan ke pemakaian_bahan, pastikan kolomnya ikut terisi
    foreach ($daftarBahanOtomatis as $bahan) {
        // Asumsi kamu menggunakan relasi Eloquent atau DB::table
        $jobOrder->pemakaian_bahan()->create([
            'bahan_baku_id'        => $bahan['bahan_baku_id'],
            'jumlah'               => $bahan['jumlah'],
            'harga_rata_saat_ini'  => $bahan['harga_rata_saat_ini'], // 🔥 Pastikan tersimpan ke DB
            'tgl_keluar'           => $bahan['tgl_keluar'],
        ]);
    }
}

            DB::commit();
            return redirect()->route('nota_order.index')->with('success', 'Nota Order berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage())->withInput();
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



    public function cetakPdf($id)
    {
        // Mengambil data induk NotaOrder beserta relasi-relasinya
        $notaOrder = NotaOrder::with([
            'customer', 
            'job_orders.produk_varian.produk', 
            'job_orders.pemakaian_bahan.bahan_baku'
        ])->findOrFail($id);

        // Mengembalikan view biasa (bukan pdf stream), persis seperti pola Surat Jalan kamu
        return view('nota_order.pdf_nota_order', compact('notaOrder'));
    }
}
