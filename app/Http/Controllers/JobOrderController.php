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
        //
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
            'tgl_order' => 'required',
            'ongkir_kirim_bahan' => 'required|numeric|min:0', // Validasi ongkir
            'jobs' => 'required|array|min:1',
            'jobs.*.customer_id' => 'required',
            'jobs.*.produk_varian_id' => 'required',
            'jobs.*.jumlah' => 'required|integer|min:1',
            'jobs.*.biaya_jasa_satuan' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 1. HITUNG TOTAL QTY SEMUA BAJU DALAM NOTA INI (Untuk pembagi ongkir)
            $totalQtyNota = 0;
            foreach ($request->jobs as $jobData) {
                $totalQtyNota += $jobData['jumlah'];
            }

            // 2. Generate nomor Surat Jalan
            $lastId = NotaProduksi::max('id') ?? 0;
            $noSuratJalan = 'SJ/' . date('Ymd') . '/' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            // 3. Simpan Data Induk Nota Produksi beserta Total Ongkirnya
            $totalOngkirInduk = $request->ongkir_kirim_bahan;
            $nota = NotaProduksi::create([
                'no_surat_jalan' => $noSuratJalan,
                'penjahit_id' => $request->penjahit_id,
                'tgl_order' => $request->tgl_order,
                'status_nota' => 'Draft',
                'status_kirim' => 'Belum Dikirim',
                'ongkir_kirim_bahan' => $totalOngkirInduk,
                'catatan' => $request->catatan
            ]);

            // 4. Loop Array Detail Job Order
            foreach ($request->jobs as $index => $jobData) {
                $varian = ProdukVarian::with('boms.bahanBaku')->findOrFail($jobData['produk_varian_id']);

                // A. Hitung total biaya bahan baku berdasarkan BoM
                $total_biaya_bahan = 0;
                if ($varian->boms) {
                    foreach ($varian->boms as $bom) {
                        $kebutuhan_bahan = $jobData['jumlah'] * $bom->jumlah;
                        $biaya_bahan = $kebutuhan_bahan * $bom->bahanBaku->harga_beli;
                        $total_biaya_bahan += $biaya_bahan;
                    }
                }

                // B. HITUNG ALOKASI ONGKIR PROPORSIONAL UNTUK JOB INI
                $ongkirPorsiJob = 0;
                if ($totalQtyNota > 0 && $totalOngkirInduk > 0) {
                    $ongkirPorsiJob = ($jobData['jumlah'] / $totalQtyNota) * $totalOngkirInduk;
                }

                // C. Hitung total Jasa Jahit
                $total_jasa_jahit = $jobData['jumlah'] * $jobData['biaya_jasa_satuan'];

                // D. UPDATE RUMUS TOTAL HPP JOB (Jasa + Bahan + Ongkir Porsi)
                $total_hpp_job = $total_jasa_jahit + $total_biaya_bahan + $ongkirPorsiJob;

                // Handle Upload Foto Desain jika ada kustomisasi
                $namaFoto = null;
                if ($request->hasFile("jobs.{$index}.foto_desain")) {
                    $file = $request->file("jobs.{$index}.foto_desain");
                    $namaFoto = 'DSN_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/desain'), $namaFoto);
                }

                // Simpan ke tabel job_order
                JobOrder::create([
                    'nota_produksi_id' => $nota->id,
                    'produk_varian_id' => $jobData['produk_varian_id'],
                    'customer_id' => $jobData['customer_id'],
                    'jumlah' => $jobData['jumlah'],
                    'biaya_jasa_satuan' => $jobData['biaya_jasa_satuan'],
                    'total_biaya_bahan' => $total_biaya_bahan,
                    'total_hpp_job' => $total_hpp_job, // <--- Sudah akurat include porsi ongkir!
                    'status_pesanan' => 'Pending',
                    
                    'is_custom' => isset($jobData['is_custom']) ? 1 : 0,
                    'id_req' => $jobData['id_req'] ?? null,
                    'pb_req' => $jobData['pb_req'] ?? null,
                    'catatan_custom' => $jobData['catatan_custom'] ?? null,
                    'foto_desain' => $namaFoto,
                ]);
            }

            DB::commit();
            return redirect()->route('nota_produksi.index')->with('success', 'Nota Produksi Berhasil Disimpan dengan Alokasi Ongkir HPP Akurat!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage())->withInput();
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
