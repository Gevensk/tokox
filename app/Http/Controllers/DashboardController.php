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
        // 1. Core Production counters
        $queueJobsCount = JobOrder::where('status_pesanan', 'Antrean')->count();
        $activeJobsCount = JobOrder::where('status_pesanan', 'Diproduksi')->count();
        $completedPcsCount = (int)HasilProduksi::sum('qty_accepted') + (int)HasilProduksi::sum('qty_rejected_defect');
        $defectPcsCount = (int)HasilProduksi::sum('qty_rejected_defect') + (int)HasilProduksi::sum('qty_rejected');
        
        // 2. Job Orders approaching deadline (sorted by tgl_deadline asc)
        $approachingDeadlineJobs = JobOrder::with(['nota_order.customer', 'produk_varian.produk', 'nota_produksi.penjahit'])
            ->whereIn('status_pesanan', ['Antrean', 'Diproduksi', 'Selesai Parsial'])
            ->whereHas('nota_order', function($query) {
                $query->whereNotNull('tgl_deadline');
            })
            ->join('nota_order', 'job_order.nota_order_id', '=', 'nota_order.id')
            ->orderBy('nota_order.tgl_deadline', 'asc')
            ->select('job_order.*')
            ->take(10)
            ->get();
        
        // 3. Active Penjahit Workload Overview
        $penjahits = Penjahit::all();
        $penjahitWorkload = [];
        foreach ($penjahits as $p) {
            // Count active job orders assigned to this penjahit (status_pesanan 'Diproduksi')
            $activeJobs = JobOrder::whereHas('nota_produksi', function($query) use ($p) {
                $query->where('penjahit_id', $p->id);
            })->where('status_pesanan', 'Diproduksi')->get();

            $totalActiveQty = $activeJobs->sum('jumlah');

            $penjahitWorkload[] = [
                'nama' => $p->nama,
                'no_hp' => $p->telp,
                'active_jobs_count' => $activeJobs->count(),
                'total_qty' => $totalActiveQty
            ];
        }

        // Sort tailors by workload (descending)
        usort($penjahitWorkload, function($a, $b) {
            return $b['total_qty'] <=> $a['total_qty'];
        });
        
        // 4. Low stock alerts (items with stock < 15)
        $lowStockMaterials = BahanBaku::where('stok_total', '<', 15)
            ->orderBy('stok_total', 'asc')
            ->take(10)
            ->get();
            
        // 5. Recent activities
        $recentProduction = NotaProduksi::with('penjahit')
            ->latest()
            ->take(5)
            ->get();
            
        $recentQcSetoran = HasilProduksi::with(['job_order.produk_varian.produk'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'queueJobsCount',
            'activeJobsCount',
            'completedPcsCount',
            'defectPcsCount',
            'approachingDeadlineJobs',
            'penjahitWorkload',
            'lowStockMaterials',
            'recentProduction',
            'recentQcSetoran'
        ));
    }
}
