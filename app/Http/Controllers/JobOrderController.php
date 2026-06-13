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
            
            // Menerima array berstruktur: jobs => [ ['id' => 1, 'biaya_jasa_satuan' => 25000], ... ]
            'jobs' => 'required|array|min:1', 
            'jobs.*.id' => 'required|exists:job_order,id',
            'jobs.*.biaya_jasa_satuan' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 1. Ekstrak semua ID Job Order yang dipilih untuk ditarik sekaligus dari DB
            $jobIds = collect($request->jobs)->pluck('id')->toArray();
            $jobsFromDb = JobOrder::with('produk_varian.bill_of_materials.bahan_baku')
                ->whereIn('id', $jobIds)
                ->get()
                ->keyBy('id'); // Di-key berdasarkan ID agar mudah dicocokkan dengan input form

            // 2. Hitung total Qty baju yang akan diproduksi dalam batch SPK ini
            $totalQtyNota = $jobsFromDb->sum('jumlah');

            // 3. Generate nomor Surat Jalan Produksi otomatis
            $lastId = NotaProduksi::max('id') ?? 0;
            $noSuratJalan = 'SJ/' . date('Ymd') . '/' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            // 4. Buat Induk Nota Produksi
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

            // 5. Loop input dari form untuk mengawinkan Ongkos Jasa Baru dengan Data DB
            foreach ($request->jobs as $formItem) {
                $job = $jobsFromDb[$formItem['id']]; // Ambil data DB asli
                $varian = $job->produk_varian;
                
                // Ambil biaya_jasa_satuan yang BARU DIINPUT OLEH ADMIN untuk penjahit ini
                $biayaJasaSatuanRil = $formItem['biaya_jasa_satuan']; 

                // A. Hitung ulang total biaya bahan baku ril dari BoM terbaru
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

                // B. Hitung alokasi ongkir logistik kain secara proporsional
                $ongkirPorsiJob = 0;
                if ($totalQtyNota > 0 && $totalOngkirInduk > 0) {
                    $ongkirPorsiJob = ($job->jumlah / $totalQtyNota) * $totalOngkirInduk;
                }

                // C. Hitung total Jasa Jahit berdasarkan input penjahit yang baru dimasukkan
                $total_jasa_jahit = $job->jumlah * $biayaJasaSatuanRil;

                // D. KALKULASI FINAL HPP SEBENARNYA: (Jasa Jahit Penjahit + Modal Bahan Baku + Porsi Ongkir Kain)
                $total_hpp_job = $total_jasa_jahit + $total_biaya_bahan + $ongkirPorsiJob;

                // E. Update data Job Order ke database dengan status 'Pending' produksi
                $job->update([
                    'nota_produksi_id' => $notaProduksi->id,
                    'biaya_jasa_satuan' => $biayaJasaSatuanRil, // 🔥 Nilai dari penjahit resmi tersimpan di sini!
                    'total_biaya_bahan' => $total_biaya_bahan,
                    'total_hpp_job' => $total_hpp_job, // HPP menjadi 100% akurat dan valid!
                    'status_pesanan' => 'Pending', 
                ]);
            }

            DB::commit();
            return redirect()->route('nota_order.index')->with('success', 'Surat Jalan Produksi berhasil diterbitkan! Ongkos jahit penjahit dan kalkulasi HPP terhitung sempurna.');

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
