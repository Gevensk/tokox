@extends('layouts.app')

@section('content')
<style>
@keyframes pulse {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(0.98); }
    100% { opacity: 1; transform: scale(1); }
}
.animate-pulse-badge {
    animation: pulse 1.5s infinite ease-in-out;
}
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header Welcome -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Dashboard Monitoring Produksi</h2>
            <p class="text-muted mb-0">Sistem monitoring antrean, proses jahit mitra, kualitas QC hasil produksi, dan ketersediaan bahan.</p>
        </div>
        <div class="bg-white px-3 py-2 rounded shadow-sm border fw-semibold text-muted" style="font-size: 0.92rem;">
            <i class="fa-regular fa-calendar-check me-2 text-primary"></i>{{ date('d F Y') }}
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Queue Jobs Count -->
        <div class="col-lg-4 col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; overflow: hidden; min-height: 120px;">
                <div class="card-body p-4 position-relative">
                    <div class="position-absolute" style="right: 20px; top: 20px; opacity: 0.25;">
                        <i class="fa-solid fa-hourglass-start fa-3x"></i>
                    </div>
                    <span class="small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Antrean Job Order</span>
                    <h2 class="fw-bold mt-2 mb-1">{{ $queueJobsCount }}</h2>
                    <span class="small opacity-75">Menunggu alokasi produksi</span>
                </div>
            </div>
        </div>

        <!-- Active Jobs Count -->
        <div class="col-lg-4 col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 12px; overflow: hidden; min-height: 120px;">
                <div class="card-body p-4 position-relative">
                    <div class="position-absolute" style="right: 20px; top: 20px; opacity: 0.25;">
                        <i class="fa-solid fa-scissors fa-3x"></i>
                    </div>
                    <span class="small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Sedang Dijahit (Proses)</span>
                    <h2 class="fw-bold mt-2 mb-1">{{ $activeJobsCount }}</h2>
                    <span class="small opacity-75">Sedang dikerjakan vendor jahit</span>
                </div>
            </div>
        </div>

        <!-- Completed Garments Pcs -->
        <div class="col-lg-4 col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); border-radius: 12px; overflow: hidden; min-height: 120px;">
                <div class="card-body p-4 position-relative">
                    <div class="position-absolute" style="right: 20px; top: 20px; opacity: 0.25;">
                        <i class="fa-solid fa-shirt fa-3x"></i>
                    </div>
                    <span class="small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Menunggu Proses QC</span>
                    <h2 class="fw-bold mt-2 mb-1">{{ number_format($waitingQcCount, 0, ',', '.') }}</h2>
                    <span class="small opacity-75">Job order belum ditutup</span>
                </div>
            </div>
        </div>

        <!-- Defective Garments Pcs -->
        {{-- <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); border-radius: 12px; overflow: hidden; min-height: 120px;">
                <div class="card-body p-4 position-relative">
                    <div class="position-absolute" style="right: 20px; top: 20px; opacity: 0.25;">
                        <i class="fa-solid fa-triangle-exclamation fa-3x"></i>
                    </div>
                    <span class="small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Cacat / Reject QC (Pcs)</span>
                    <h2 class="fw-bold mt-2 mb-1">{{ number_format($defectPcsCount, 0, ',', '.') }} pcs</h2>
                    <span class="small opacity-75">Hasil jahit bermasalah / gagal</span>
                </div>
            </div>
        </div> --}}
    </div>

    <!-- Main Content Area -->
    <div class="row mb-4">
        <!-- Left Column: Approaching Deadlines & Workloads -->
        <div class="col-lg-8 mb-4 mb-lg-0">
            <!-- Job Orders Approaching Deadline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-clock-rotate-left text-danger me-2"></i>Job Order Mendekati Deadline</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="min-width: 600px; font-size: 0.95rem;">
                            <thead class="table-light" style="font-size: 0.95rem;">
                                <tr>
                                    <th class="ps-3">Job ID & Varian</th>
                                    <th>No. Order</th>
                                    <th class="text-center">Qty</th>
                                    <th>Penjahit</th>
                                    <th>Deadline</th>
                                    <th class="text-end pe-3">Sisa Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($approachingDeadlineJobs as $job)
                                    @php
                                        $deadline = \Carbon\Carbon::parse($job->nota_order->tgl_deadline);
                                        $today = \Carbon\Carbon::today();
                                        $diffDays = $today->diffInDays($deadline, false);
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark" style="font-size: 1.05rem;">JOB-{{ $job->id }}</div>
                                            <span class="text-secondary d-block" style="font-size: 0.88rem;">{{ $job->produk_varian->produk->nama ?? 'Produk' }} ({{ $job->produk_varian->warna ?? '' }} - {{ $job->produk_varian->ukuran ?? '' }})</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $job->nota_order->no_order }}</span>
                                        </td>
                                        <td class="text-center fw-bold text-secondary" style="font-size: 1rem;">{{ $job->jumlah }} pcs</td>
                                        <td>
                                            @if($job->nota_produksi && $job->nota_produksi->penjahit)
                                                <span class="text-dark fw-bold" style="font-size: 1rem;">{{ $job->nota_produksi->penjahit->nama }}</span>
                                                <span class="text-muted d-block" style="font-size: 0.82rem;">SJ: {{ $job->nota_produksi->no_surat_jalan }}</span>
                                            @else
                                                <span class="text-muted italic" style="font-size: 0.92rem;">Belum dialokasikan</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold" style="font-size: 0.95rem;">{{ date('d-m-Y', strtotime($job->nota_order->tgl_deadline)) }}</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            @if($diffDays < 0)
                                                <span class="badge bg-danger px-2 py-1.5 fw-bold text-nowrap"><i class="fa-solid fa-triangle-exclamation me-1"></i> Terlambat {{ abs($diffDays) }} hari</span>
                                            @elseif($diffDays == 0)
                                                <span class="badge bg-danger px-2 py-1.5 fw-bold text-nowrap animate-pulse-badge"><i class="fa-solid fa-circle-exclamation me-1"></i> HARI INI!</span>
                                            @elseif($diffDays <= 2)
                                                <span class="badge bg-warning text-dark px-2 py-1.5 fw-bold text-nowrap"><i class="fa-solid fa-clock me-1"></i> {{ $diffDays }} hari lagi</span>
                                            @elseif($diffDays <= 5)
                                                <span class="badge bg-info text-dark px-2 py-1.5 fw-bold text-nowrap">{{ $diffDays }} hari lagi</span>
                                            @else
                                                <span class="badge bg-success px-2 py-1.5 fw-bold text-nowrap">{{ $diffDays }} hari lagi</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-circle-check fa-2x mb-2 text-success"></i>
                                            <div>Tidak ada job order aktif yang memiliki deadline dekat.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
 
            <!-- Tailor Workload Overview -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-user-group text-primary me-2"></i>Beban Kerja & Kapasitas Mitra Penjahit</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.95rem;">
                            <thead class="table-light" style="font-size: 0.95rem;">
                                <tr>
                                    <th class="ps-3">Nama Penjahit</th>
                                    <th>Kontak HP</th>
                                    <th class="text-center">Active Job Orders</th>
                                    <th class="text-end pe-3">Beban Pengerjaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penjahitWorkload as $load)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark" style="font-size: 1.05rem;">{{ $load['nama'] }}</div>
                                    </td>
                                    <td>{{ $load['no_hp'] ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border fw-bold py-1.5 px-2.5" style="font-size: 0.92rem;">{{ $load['active_jobs_count'] }} Jobs</span>
                                    </td>
                                    <td class="text-end pe-3">
                                        @if($load['total_qty'] > 0)
                                            <strong class="text-primary" style="font-size: 1.05rem;">{{ number_format($load['total_qty'], 0, ',', '.') }} Pcs</strong>
                                        @else
                                            <span class="text-muted italic" style="font-size: 0.92rem;">Tidak ada antrean</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada mitra penjahit yang terdaftar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Low Stock Alerts & Recent Activities -->
        <div class="col-lg-4">
            <!-- Low Stock Warnings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-danger"><i class="fa-solid fa-circle-exclamation me-2"></i>Peringatan Stok Bahan Baku (< 15)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3">Bahan Baku</th>
                                    <th class="text-end pe-3">Stok Saat Ini</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockMaterials as $material)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">{{ $material->nama }}</div>
                                        <small class="text-muted">Unit: {{ $material->satuan }}</small>
                                    </td>
                                    <td class="text-end pe-3">
                                        @if($material->stok_total <= 5)
                                            <span class="badge bg-danger px-2 py-1.5 fw-bold text-nowrap"><i class="fa-solid fa-fire me-1"></i> {{ (float)$material->stok_total }} {{ $material->satuan }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark px-2 py-1.5 fw-bold text-nowrap"><i class="fa-solid fa-triangle-exclamation me-1"></i> {{ (float)$material->stok_total }} {{ $material->satuan }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-circle-check fa-2x mb-2 text-success"></i>
                                        <div>Stok seluruh bahan baku aman!</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent QC / Setoran Hasil Produksi -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-clipboard-check text-success me-2"></i>Setoran / QC Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 275px; overflow-y: auto;">
                        @forelse($recentQcSetoran as $qc)
                        <div class="list-group-item p-3 border-0 border-bottom">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-dark" style="font-size: 0.95rem;">JOB-{{ $qc->job_order_id }}</span>
                                <small class="text-muted" style="font-size: 0.85rem;">{{ date('d-m-Y', strtotime($qc->tgl_terima)) }}</small>
                            </div>
                            <div class="text-secondary mb-2" style="font-size: 0.92rem;">{{ $qc->job_order->produk_varian->produk->nama ?? 'Produk' }}</div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle py-1.5 px-2.5" style="font-size: 0.88rem;">Layak: {{ $qc->qty_accepted + $qc->qty_rejected_defect }} pcs</span>
                                @if($qc->qty_rejected > 0)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1.5 px-2.5" style="font-size: 0.88rem;">Reject: {{ $qc->qty_rejected }} pcs</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted small">Belum ada setoran masuk.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
