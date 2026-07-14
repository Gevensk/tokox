<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class BahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $bahan_baku=BahanBaku::all();
        return view('bahan_baku.index',compact('bahan_baku')) ;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('bahan_baku.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'nama' =>'required',
            'satuan' => 'required'
        ]);

            BahanBaku::create([
                'nama' => $request->nama,
                'satuan' => $request->satuan,
                'stok_total' => 0,          // Set awal 0
                'harga_rata_rata' => 0,     // Set awal 0
            ]);

            return redirect('/bahan_baku')->with('success','Bahan baku berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $bahan_baku =BahanBaku::find($id);
        return view('bahan_baku.edit',compact('bahan_baku'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        //
        $request->validate([
            'nama'=> 'required',
            'satuan'=>'required'
        ]);

        $bahan_baku=BahanBaku::find($id);
        $bahan_baku->update($request->all());

        return redirect('/bahan_baku')->with('success','Data berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $bahan_baku = BahanBaku::findOrFail($id);
            $bahan_baku->delete();
            return redirect('/bahan_baku')->with('success', 'Data berhasil dihapus!');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()->with('error', 'Bahan baku ini tidak dapat dihapus karena sudah memiliki riwayat transaksi (Pembelian / Pemakaian / Penyesuaian Stok).');
            }
            throw $e;
        }
    }

    public function kartuStok($id)
    {
        $bahan = BahanBaku::findOrFail($id);

        // 1. Ambil data Pembelian
        $purchases = DB::table('nota_pembelian_has_bahan_baku')
            ->join('nota_pembelian', 'nota_pembelian_has_bahan_baku.nota_pembelian_id', '=', 'nota_pembelian.id')
            ->leftJoin('supplier', 'nota_pembelian.supplier_id', '=', 'supplier.id')
            ->where('nota_pembelian_has_bahan_baku.bahan_baku_id', $id)
            ->select([
                'nota_pembelian.tgl_beli as tanggal',
                DB::raw("'Pembelian' as tipe"),
                'nota_pembelian_has_bahan_baku.jumlah as masuk',
                DB::raw('0 as keluar'),
                'nota_pembelian_has_bahan_baku.harga_satuan as harga',
                DB::raw("CONCAT('Pembelian dari supplier: ', IFNULL(supplier.nama, '-'), ' (Nota ID: #', nota_pembelian.id, ')') as keterangan")
            ]);

        // 2. Ambil data Pemakaian Jahit (Resep BoM & Bahan Tambahan Ekstra)
        $usages = DB::table('pemakaian_bahan')
            ->join('job_order', 'pemakaian_bahan.job_order_id', '=', 'job_order.id')
            ->join('produk_varian', 'job_order.produk_varian_id', '=', 'produk_varian.id')
            ->join('produk', 'produk_varian.produk_id', '=', 'produk.id')
            ->where('pemakaian_bahan.bahan_baku_id', $id)
            ->whereNotNull('pemakaian_bahan.tgl_keluar')
            ->select([
                'pemakaian_bahan.tgl_keluar as tanggal',
                DB::raw("CASE WHEN pemakaian_bahan.tipe = 'ekstra' THEN 'Pemakaian Ekstra' ELSE 'Pemakaian Resep BoM' END as tipe"),
                DB::raw('0 as masuk'),
                'pemakaian_bahan.jumlah as keluar',
                'pemakaian_bahan.harga_rata_saat_ini as harga',
                DB::raw("CONCAT('Untuk Job Order #', job_order.id, ' (', produk.nama, ' ', produk_varian.warna, ' ', produk_varian.ukuran, ')') as keterangan")
            ]);

        // 3. Ambil data Penyesuaian Stok
        $adjustments = DB::table('penyesuaian_stok_bahan')
            ->where('bahan_baku_id', $id)
            ->select([
                'created_at as tanggal',
                DB::raw("'Penyesuaian' as tipe"),
                DB::raw("CASE WHEN tipe = 'in' THEN jumlah ELSE 0 END as masuk"),
                DB::raw("CASE WHEN tipe = 'out' THEN jumlah ELSE 0 END as keluar"),
                DB::raw('0 as harga'),
                DB::raw("CONCAT('Penyesuaian stok. Alasan: ', IFNULL(keterangan, '-')) as keterangan")
            ]);

        // Gabungkan seluruh mutasi dan urutkan berdasarkan tanggal secara ascending (dari tertua ke terbaru)
        $mutations = $purchases->unionAll($usages)->unionAll($adjustments)
            ->orderBy('tanggal', 'asc')
            ->get();

        // Hitung saldo awal agar saldo berjalan di baris akhir cocok dengan stok fisik saat ini
        $totalNettoMutation = $mutations->sum(function ($m) {
            return $m->masuk - $m->keluar;
        });
        
        $runningSaldo = (float)$bahan->stok_total - $totalNettoMutation;

        foreach ($mutations as $m) {
            $runningSaldo += ($m->masuk - $m->keluar);
            $m->saldo = $runningSaldo;
        }

        // Balikkan urutan koleksi agar yang terbaru tampil di atas pada tabel view
        $mutations = $mutations->reverse();

        return view('bahan_baku.kartu_stok', compact('bahan', 'mutations'));
    }
}
