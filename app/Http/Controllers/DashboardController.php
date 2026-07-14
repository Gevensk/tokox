<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobOrder;
use App\Models\BahanBaku;
use App\Models\Penjahit;
use App\Models\NotaProduksi;
use App\Models\HasilProduksi;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Hitung jumlah status produksi utama
        $jumlahAntreanJahit = JobOrder::where('status_pesanan', 'Antrean')->count();
        $jumlahJobAktif     = JobOrder::where('status_pesanan', 'Diproduksi')->count();
        $jumlahMenungguQc   = JobOrder::whereIn('status_pesanan', ['Diproduksi', 'Selesai Parsial'])->count();
        
        // 2. Job Order yang mendekati deadline (diurutkan dari tanggal deadline paling dekat)
        $jobMendekatiDeadline = JobOrder::with(['nota_order.customer', 'produk_varian.produk', 'nota_produksi.penjahit'])
            ->whereIn('status_pesanan', ['Antrean', 'Diproduksi', 'Selesai Parsial'])
            ->whereHas('nota_order', function($query) {
                $query->whereNotNull('tgl_deadline');
            })
            ->join('nota_order', 'job_order.nota_order_id', '=', 'nota_order.id')
            ->orderBy('nota_order.tgl_deadline', 'asc')
            ->select('job_order.*')
            ->take(10)
            ->get();
        
        // 3. Ringkasan beban kerja penjahit aktif
        $penjahits = Penjahit::all();
        $bebanKerjaPenjahit = [];
        foreach ($penjahits as $p) {
            // Hitung job order aktif yang sedang dikerjakan penjahit ini
            $activeJobs = JobOrder::whereHas('nota_produksi', function($query) use ($p) {
                $query->where('penjahit_id', $p->id);
            })->where('status_pesanan', 'Diproduksi')->get();

            $totalActiveQty = $activeJobs->sum('jumlah');

            $bebanKerjaPenjahit[] = [
                'nama' => $p->nama,
                'no_hp' => $p->telp,
                'active_jobs_count' => $activeJobs->count(),
                'total_qty' => $totalActiveQty
            ];
        }

        // Urutkan penjahit berdasarkan beban jumlah jahitan terbanyak
        usort($bebanKerjaPenjahit, function($a, $b) {
            return $b['total_qty'] <=> $a['total_qty'];
        });
        
        // 4. Peringatan stok bahan baku menipis (stok kurang dari 15)
        $bahanStokMenipis = BahanBaku::where('stok_total', '<', 15)
            ->orderBy('stok_total', 'asc')
            ->take(10)
            ->get();
            
        // 5. Aktivitas terbaru
        $produksiTerbaru = NotaProduksi::with('penjahit')
            ->latest()
            ->take(5)
            ->get();
            
        $setoranQcTerbaru = HasilProduksi::with(['job_order.produk_varian.produk'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'jumlahAntreanJahit',
            'jumlahJobAktif',
            'jumlahMenungguQc',
            'jobMendekatiDeadline',
            'bebanKerjaPenjahit',
            'bahanStokMenipis',
            'produksiTerbaru',
            'setoranQcTerbaru'
        ));
    }
}
