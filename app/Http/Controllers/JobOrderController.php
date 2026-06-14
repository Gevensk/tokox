<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobOrder;
use App\Models\ProdukVarian;
use App\Models\NotaProduksi;
use Exception;
use Illuminate\Support\Facades\DB;

class JobOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Menampilkan antrean baju yang siap dilempar ke penjahit
    public function index()
    {
        // Ambil semua job order yang belum dikirim ke penjahit (nota_produksi_id masih kosong)
        $antreanJobs = JobOrder::with(['nota_order.customer', 'produk_varian.produk'])
            ->whereNull('nota_produksi_id')
            ->get();

        // Ambil data penjahit untuk dropdown pilihan di view
        // (Asumsi kamu memiliki model Penjahit / Vendor)
        $penjahits = DB::table('penjahit')->get(); 

        return view('job_order.index', compact('antreanJobs', 'penjahits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'penjahit_id' => 'required',
        'tgl_order' => 'required|date',
        'ongkir_kirim_bahan' => 'required|numeric|min:0',
        'jobs' => 'required|array|min:1', 
        'jobs.*.id' => 'required|exists:job_order,id',
        'jobs.*.biaya_jasa_satuan' => 'required|numeric|min:0',
    ]);

    try {
        DB::beginTransaction();

        $jobIds = collect($request->jobs)->pluck('id')->toArray();
        // Tarik juga relasi nota_order untuk mengupdate status nota induknya nanti
        $jobsFromDb = JobOrder::with(['produk_varian.bill_of_materials.bahan_baku', 'nota_order'])
            ->whereIn('id', $jobIds)
            ->get()
            ->keyBy('id');

        $totalQtyNota = $jobsFromDb->sum('jumlah');

        $lastId = NotaProduksi::max('id') ?? 0;
        $noSuratJalan = 'SJ/' . date('Ymd') . '/' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

        // Buat Induk Nota Produksi
        $totalOngkirInduk = $request->ongkir_kirim_bahan;
        $notaProduksi = NotaProduksi::create([
            'no_surat_jalan' => $noSuratJalan, // Pastikan sudah fillable di Model NotaProduksi
            'penjahit_id' => $request->penjahit_id,
            'tgl_order' => $request->tgl_order,
            'status_nota' => 'Draft',
            'status_kirim' => 'Belum Dikirim',
            'ongkir_kirim_bahan' => $totalOngkirInduk,
            'catatan' => $request->catatan
        ]);

        // Kumpulkan ID Nota Order induk yang terlibat untuk di-update statusnya sekaligus
        $notaOrderIdsToUpdate = [];

        foreach ($request->jobs as $formItem) {
            $job = $jobsFromDb[$formItem['id']]; 
            $varian = $job->produk_varian;
            
            $biayaJasaSatuanRil = $formItem['biaya_jasa_satuan']; 

            // A. Hitung ulang total biaya bahan baku
            $total_biaya_bahan = 0;
            if ($varian && $varian->bill_of_materials) {
                foreach ($varian->bill_of_materials as $bom) {
                    if ($bom->bahan_baku) {
                        $kebutuhan_bahan = $job->jumlah * $bom->jumlah;
                        $biaya_bahan = $kebutuhan_bahan * $bom->bahan_baku->harga_rata_rata;
                        $total_biaya_bahan += $biaya_bahan;
                    }
                }
            }

            // B. Alokasi ongkir logistik kain secara proporsional
            $ongkirPorsiJob = 0;
            if ($totalQtyNota > 0 && $totalOngkirInduk > 0) {
                $ongkirPorsiJob = ($job->jumlah / $totalQtyNota) * $totalOngkirInduk;
            }

            // C. Hitung total Jasa Jahit
            $total_jasa_jahit = $job->jumlah * $biayaJasaSatuanRil;

            // D. Kalkulasi Final HPP
            $total_hpp_job = $total_jasa_jahit + $total_biaya_bahan + $ongkirPorsiJob;

            // E. Update data Job Order ke database
            // PASAL PERBAIKAN: status_pesanan diubah ke 'Diproduksi' sesuai ENUM database Anda!
            $job->update([
                'nota_produksi_id' => $notaProduksi->id,
                'biaya_jasa_satuan' => $biayaJasaSatuanRil, 
                'total_biaya_bahan' => $total_biaya_bahan,
                'total_hpp_job' => $total_hpp_job, 
                'status_pesanan' => 'Diproduksi', 
            ]);

            // Catat ID Nota Order induk jika ada
            if ($job->nota_order_id) {
                $notaOrderIdsToUpdate[] = $job->nota_order_id;
            }
        }

        // === 💥 PERBAIKAN: OTOMATISASI STATUS NOTA ORDER INDUK MENJADI 'PROGRESS' 💥 ===
        if (!empty($notaOrderIdsToUpdate)) {
            // Ambil ID unik agar tidak melakukan query ganda pada nota yang sama
            $uniqueNotaOrderIds = array_unique($notaOrderIdsToUpdate);
            
            // Update semua nota order yang terlibat menjadi 'Progress'
            DB::table('nota_order')
                ->whereIn('id', $uniqueNotaOrderIds)
                ->where('status_order', 'Valid') // Hanya update yang bertatus Valid/Draft awal
                ->update(['status_order' => 'Progress']);
        }

        DB::commit();
        return redirect()->route('nota_order.index')->with('success', 'Surat Jalan Produksi berhasil diterbitkan! Status pengerjaan otomatis diperbarui menjadi Progress.');

    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Gagal memproses antrean produksi: ' . $e->getMessage())->withInput();
    }
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
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
