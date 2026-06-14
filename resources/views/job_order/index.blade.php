@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">📋 Daftar & Pemantauan Job Order (Surat Jalan)</h1>
            <p class="text-muted mb-0">Kelola riwayat pengerjaan vendor penjahit dan audit nilai HPP aktual.</p>
        </div>
        <a href="{{ route('job_order.create') }}" class="btn btn-primary fw-bold">
            <i class="fas fa-plus-circle"></i> Buat Surat Jalan Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0 mb-4">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0 fw-bold">📦 Status Produksi Vendor & Akuntansi</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th width="80" class="text-center">ID Job</th>
                            <th width="70">Desain</th>
                            <th>Vendor Penjahit</th>
                            <th>Nama Produk Varian</th>
                            <th class="text-center">Target Qty</th>
                            <th class="text-center">Status Pesanan</th>
                            <th class="text-center" width="220">Aksi / Evaluasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobOrders as $job)
                        <tr>
                            <td class="text-center font-weight-bold text-secondary">#{{ $job->id }}</td>
                            <td>
                                @if($job->foto_desain)
                                    <img src="{{ asset('storage/' . $job->foto_desain) }}" class="img-thumbnail rounded" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imgModal{{ $job->id }}">
                                @else
                                    <div class="bg-light rounded text-center text-muted d-flex align-items-center justify-content-center border" style="width: 50px; height: 50px; font-size: 0.75rem;">No Img</div>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-dark">{{ $job->nota_produksi->penjahit->nama ?? 'Belum Ditunjuk' }}</span>
                                <div class="small text-muted">SJ: {{ $job->nota_produksi->no_surat_jalan ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="fw-bold text-primary">{{ $job->produk_varian->produk->nama ?? 'N/A' }}</span>
                                <div class="small text-secondary">Varian: {{ $job->produk_varian->warna ?? '' }} | {{ $job->produk_varian->ukuran ?? '' }}</div>
                            </td>
                            <td class="text-center fw-bold fs-6">{{ number_format($job->jumlah) }} pcs</td>
                            <td class="text-center">
                                @if($job->status_pesanan == 'Selesai')
                                    <span class="badge bg-success py-2 px-3">Selesai</span>
                                @elseif($job->status_pesanan == 'Selesai Parsial')
                                    <span class="badge bg-warning text-dark py-2 px-3">Selesai Parsial</span>
                                @else
                                    <span class="badge bg-info text-dark py-2 px-3">Diproduksi</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group gap-1" role="group">
                                    <a href="{{ route('job_order.show', $job->id) }}" class="btn btn-sm btn-outline-info fw-bold" title="Lihat Evaluasi Perhitungan HPP">
                                        <i class="fas fa-calculator"></i> Audit HPP
                                    </a>

                                    @if($job->status_pesanan != 'Selesai')
                                    <a href="{{ route('hasil_produksi.create', ['job_id' => $job->id]) }}" class="btn btn-sm btn-success fw-bold">
                                        <i class="fas fa-check-circle"></i> Input QC
                                    </a>
                                    @else
                                    <button class="btn btn-sm btn-light text-muted border" disabled><i class="fas fa-lock"></i> Lock</button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        @if($job->foto_desain)
                        <div class="modal fade" id="imgModal{{ $job->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title">Sketsa Desain Job #{{ $job->id }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center p-2 bg-light">
                                        <img src="{{ asset('storage/' . $job->foto_desain) }}" class="img-fluid rounded shadow-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">📭 Belum ada Job Order yang berjalan saat ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection