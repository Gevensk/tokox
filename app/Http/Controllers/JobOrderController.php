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
    public function index()
    {
        // Mengambil data job order dengan relasi berjenjang menuju penjahit melalui induk nota produksinya
        $jobOrders = JobOrder::with(['nota_produksi.penjahit', 'produk_varian.produk'])->latest()->get();
        
        return view('job_order.index', compact('jobOrders'));
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
                'no_surat_jalan' => $noSuratJalan, 
                'penjahit_id' => $request->penjahit_id,
                'tgl_order' => $request->tgl_order,
                'status_nota' => 'Draft',
                'status_kirim' => 'Belum Dikirim',
                'ongkir_kirim_bahan' => $totalOngkirInduk,
                'catatan' => $request->catatan
            ]);

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
                $job->update([
                    'nota_produksi_id' => $notaProduksi->id,
                    'biaya_jasa_satuan' => $biayaJasaSatuanRil, 
                    'total_biaya_bahan' => $total_biaya_bahan,
                    'total_hpp_job' => $total_hpp_job, 
                    'status_pesanan' => 'Diproduksi', 
                ]);

                if ($job->nota_order_id) {
                    $notaOrderIdsToUpdate[] = $job->nota_order_id;
                }
            }

            if (!empty($notaOrderIdsToUpdate)) {
                $uniqueNotaOrderIds = array_unique($notaOrderIdsToUpdate);
                DB::table('nota_order')
                    ->whereIn('id', $uniqueNotaOrderIds)
                    ->where('status_order', 'Valid') 
                    ->update(['status_order' => 'Progress']);
            }

            DB::commit();
            return redirect()->route('job_order.index')->with('success', 'Surat Jalan Produksi berhasil diterbitkan!');

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
        $job = JobOrder::with(['produk_varian.produk', 'nota_produksi.penjahit', 'hasil_produksis' => function($query) {
            $query->latest();
        }])->findOrFail($id);

        return view('job_order.show', compact('job'));
    }
}