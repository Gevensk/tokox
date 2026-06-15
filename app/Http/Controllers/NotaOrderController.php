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
    public function index()
    {
        $notaOrders = NotaOrder::with(['customer', 'job_orders'])
            ->latest()
            ->paginate(10);
            
        return view('nota_order.index', compact('notaOrders'));
    }

    public function create()
    {
        $customers = Customer::orderBy('nama', 'asc')->get();
        $produks = Produk::all();
        
        return view('nota_order.create', compact('customers', 'produks'));
    }

    public function getVarianByProduk($id)
    {
        $varians = ProdukVarian::where('produk_id', $id)->get();
        return response()->json($varians);
    }

public function store(Request $request)
{
    $request->validate([
        'tipe_order' => 'required|in:Internal,Customer',
        'customer_id' => 'required_if:tipe_order,Customer',
        'tgl_deadline' => 'required|date',
        'total_harga' => 'required|numeric|min:0',
        'nominal_dp_masuk' => 'required|numeric|min:0',
        'items' => 'required|array|min:1',
        'items.*.produk_varian_id' => 'required|exists:produk_varian,id',
        'items.*.jumlah' => 'required|numeric|min:1',
        'items.*.harga_satuan_jual' => 'required|numeric|min:0',
        // VALIDASI BARU: Memastikan file berupa gambar dengan kapasitas maksimal 2MB
        'items.*.foto_desain' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
    ]);

    DB::beginTransaction();
    try {
        $tahunBulan = date('Ym');
        $hitungOrderBulanIni = NotaOrder::whereYear('tgl_order', date('Y'))
            ->whereMonth('tgl_order', date('m'))
            ->count();
            
        $nomorUrutNext = $hitungOrderBulanIni + 1;
        $noOrderOtomatis = 'ORD-' . $tahunBulan . '-' . str_pad($nomorUrutNext, 4, '0', STR_PAD_LEFT);

        // Simpan Data Parent Nota
        $nota = NotaOrder::create([
            'no_order' => $noOrderOtomatis,
            'tipe_order' => $request->tipe_order,
            'customer_id' => $request->tipe_order == 'Customer' ? $request->customer_id : null,
            'tgl_order' => now(),
            'tgl_deadline' => $request->tgl_deadline,
            'total_harga' => $request->total_harga,
            'nominal_dp_masuk' => $request->nominal_dp_masuk,
            'status_bayar' => $request->nominal_dp_masuk >= $request->total_harga ? 'Lunas' : 'Belum Lunas',
            'status_order' => 'Valid',
            'harga_satuan_items' => 0, 
        ]);

        foreach ($request->items as $index => $item) {
            $varian = ProdukVarian::with('bill_of_materials.bahan_baku')->find($item['produk_varian_id']);
            $estimasiBiayaBahanPerPcs = 0;
            $daftarBahanOtomatis = [];
            
            if ($varian && $varian->bill_of_materials) {
                foreach ($varian->bill_of_materials as $bom) {
                    if ($bom->bahan_baku) {
                        $hargaBahanSaatIni = $bom->bahan_baku->harga_rata_rata ?? 0;
                        $estimasiBiayaBahanPerPcs += ($bom->jumlah * $hargaBahanSaatIni);
                        
                        $daftarBahanOtomatis[] = [
                            'bahan_baku_id' => $bom->bahan_baku_id,
                            'jumlah' => $bom->jumlah * $item['jumlah'],
                            'harga_rata_saat_ini' => $hargaBahanSaatIni,
                            'tgl_keluar' => null,
                        ];
                    }
                }
            }

            $totalBiayaBahanJob = $estimasiBiayaBahanPerPcs * $item['jumlah'];

            // PROSES UPLOAD FOTO DESAIN BARU:
            $namaFileGambar = null;
            if ($request->hasFile("items.$index.foto_desain")) {
                $file = $request->file("items.$index.foto_desain");
                // Membuat nama berkas yang unik (Contoh: desain_1718451234_0.jpg)
                $namaFileGambar = 'desain_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                // Menyimpan fisik file ke folder storage/app/public/desain_order
                $file->storeAs('desain_order', $namaFileGambar, 'public');
            }

            // Simpan baris item ke Child (JobOrder)
            $jobOrder = JobOrder::create([
                'nota_order_id' => $nota->id,
                'produk_varian_id' => $item['produk_varian_id'],
                'jumlah' => $item['jumlah'],
                'biaya_jasa_satuan' => $item['harga_satuan_jual'],
                'is_custom' => $item['is_custom'] ?? 0,
                'catatan_custom' => $item['catatan_custom'] ?? null,
                'foto_desain' => $namaFileGambar, // Disimpan ke database 'job_order' nomor 13
                'total_biaya_bahan' => $totalBiayaBahanJob,
                'total_hpp_job' => $totalBiayaBahanJob,
                'status_pesanan' => 'Antrean',
            ]);

            foreach ($daftarBahanOtomatis as $bahan) {
                $jobOrder->pemakaian_bahan()->create([
                    'bahan_baku_id' => $bahan['bahan_baku_id'],
                    'jumlah' => $bahan['jumlah'],
                    'harga_rata_saat_ini' => $bahan['harga_rata_saat_ini'],
                    'tgl_keluar' => $bahan['tgl_keluar'],
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

    public function show($id)
    {
        $notaOrder = NotaOrder::with([
            'customer',
            'job_orders.produk_varian.produk',
            'job_orders.pemakaian_bahan.bahan_baku'
        ])->findOrFail($id);

        return view('nota_order.show', compact('notaOrder'));
    }

public function edit($id)
    {
        $notaOrder = NotaOrder::with('job_orders.produk_varian.produk')->findOrFail($id);
        $customers = Customer::orderBy('nama', 'asc')->get();
        $produks = Produk::all(); 
        
        // PENCEGEKAN: Jika ada salah satu item yang sudah bukan 'Antrean', tolak akses edit
        foreach ($notaOrder->job_orders as $job) {
            if ($job->status_pesanan !== 'Antrean' || !is_null($job->nota_produksi_id)) {
                return redirect()->route('nota_order.index')
                    ->with('error', 'Gagal Mengubah! Nota ini tidak bisa diedit karena beberapa item sudah masuk proses produksi.');
            }
        }
        
        return view('nota_order.edit', compact('notaOrder', 'customers', 'produks'));
    }

public function update(Request $request, $id)
    {
        $notaOrder = NotaOrder::with('job_orders')->findOrFail($id);
        
        // PENCEGEKAN KEAMANAN SISI SERVER: Kuatkan kembali sebelum eksekusi update database
        foreach ($notaOrder->job_orders as $job) {
            if ($job->status_pesanan !== 'Antrean' || !is_null($job->nota_produksi_id)) {
                return redirect()->route('nota_order.index')
                    ->with('error', 'Gagal Memperbarui! Transaksi dikunci karena item sedang diproduksi.');
            }
        }
        
        $request->validate([
            'tipe_order' => 'required|in:Customer,Internal',
            'tgl_deadline' => 'required|date',
            'total_harga' => 'required|numeric|min:0',
            'nominal_dp_masuk' => 'required|numeric|min:0',
            'status_bayar' => 'required|in:Belum Lunas,Lunas',
            'harga_satuan_items' => 'required|numeric|min:0', 
            'items' => 'required|array|min:1',
            'items.*.produk_varian_id' => 'required|exists:produk_varian,id',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.harga_satuan_jual' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 1. Update data parent (NotaOrder)
            $notaOrder->update([
                'tipe_order' => $request->tipe_order,
                'customer_id' => $request->tipe_order === 'Customer' ? $request->customer_id : null,
                'tgl_deadline' => $request->tgl_deadline,
                'total_harga' => $request->total_harga,
                'nominal_dp_masuk' => $request->nominal_dp_masuk,
                'status_bayar' => $request->status_bayar,
                'harga_satuan_items' => $request->harga_satuan_items, 
            ]);

            // 2. Hapus data job order lama beserta pemakaian bahannya terlebih dahulu
            foreach ($notaOrder->job_orders as $job) {
                DB::table('pemakaian_bahan')->where('job_order_id', $job->id)->delete();
                $job->delete();
            }

            // 3. Masukkan kembali data item baru/pembaruan dari form
            foreach ($request->items as $item) {
                $varian = ProdukVarian::with('bill_of_materials.bahan_baku')->find($item['produk_varian_id']);
                $estimasiBiayaBahanPerPcs = 0;
                $daftarBahanOtomatis = [];
                
                if ($varian && $varian->bill_of_materials) {
                    foreach ($varian->bill_of_materials as $bom) {
                        if ($bom->bahan_baku) {
                            $hargaBahanSaatIni = $bom->bahan_baku->harga_rata_rata;
                            $estimasiBiayaBahanPerPcs += ($bom->jumlah * $hargaBahanSaatIni);
                            
                            $daftarBahanOtomatis[] = [
                                'bahan_baku_id' => $bom->bahan_baku_id,
                                'jumlah' => $bom->jumlah * $item['jumlah'],
                                'harga_rata_saat_ini' => $hargaBahanSaatIni,
                                'tgl_keluar' => null,
                            ];
                        }
                    }
                }

                $totalBiayaBahanJob = $estimasiBiayaBahanPerPcs * $item['jumlah'];

                $newJob = JobOrder::create([
                    'nota_order_id' => $notaOrder->id,
                    'produk_varian_id' => $item['produk_varian_id'],
                    'jumlah' => $item['jumlah'],
                    'biaya_jasa_satuan' => 0, 
                    'is_custom' => $item['is_custom'] ?? 0,
                    'catatan_custom' => $item['catatan_custom'] ?? null,
                    'total_biaya_bahan' => $totalBiayaBahanJob,
                    'total_hpp_job' => $totalBiayaBahanJob,
                    'status_pesanan' => 'Antrean', // Tetap kembali ke status antrean awal
                ]);

                foreach ($daftarBahanOtomatis as $bahan) {
                    $newJob->pemakaian_bahan()->create([
                        'bahan_baku_id' => $bahan['bahan_baku_id'],
                        'jumlah' => $bahan['jumlah'],
                        'harga_rata_saat_ini' => $bahan['harga_rata_saat_ini'],
                        'tgl_keluar' => $bahan['tgl_keluar'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('nota_order.index')->with('success', 'Nota Order dan seluruh item berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage())->withInput();
        }
    }

public function destroy($id)
    {
        $notaOrder = NotaOrder::with('job_orders')->findOrFail($id);
        
        // PENCEGEKAN: Memastikan tidak ada satu pun item yang lolos dari status 'Antrean'
        foreach ($notaOrder->job_orders as $job) {
            if ($job->status_pesanan !== 'Antrean' || !is_null($job->nota_produksi_id)) {
                return redirect()->back()->with('error', 'Gagal Menghapus! Nota ini tidak bisa dihapus karena beberapa item sudah masuk proses produksi.');
            }
        }

        DB::beginTransaction();
        try {
            foreach ($notaOrder->job_orders as $job) {
                DB::table('pemakaian_bahan')->where('job_order_id', $job->id)->delete();
                $job->delete();
            }
            $notaOrder->delete();
            
            DB::commit();
            return redirect()->route('nota_order.index')->with('success', 'Nota Order berhasil dihapus bersih.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function cetakPdf($id)
    {
        $notaOrder = NotaOrder::with([
            'customer',
            'job_orders.produk_varian.produk',
            'job_orders.pemakaian_bahan.bahan_baku'
        ])->findOrFail($id);
        
        return view('nota_order.print', compact('notaOrder'));
    }
}