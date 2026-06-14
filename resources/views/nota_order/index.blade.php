@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold text-dark">Daftar Transaksi & Antrean Order</h3>
        <a href="{{ route('nota_order.create') }}" class="btn btn-primary px-3 shadow-sm">+ Buat Order Baru</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">No. Order</th>
                            <th>Customer / Tipe</th>
                            <th>Tgl Masuk</th>
                            <th>Deadline</th>
                            <th>Total Baris Item</th>
                            <th>Total Bayar (Deal)</th>
                            <th>Nominal DP Masuk</th>
                            <th>Status Bayar</th>
                            <th>Status Order</th>
                            <th class="text-center pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notaOrders as $nota)
                            <tr>
                                <td class="ps-3">
                                    <strong class="text-primary">{{ $nota->no_order }}</strong>
                                </td>
                                <td>
                                    @if($nota->tipe_order === 'Customer')
                                        <span class="badge bg-info text-dark me-1">Customer</span>
                                        <span class="fw-semibold">{{ $nota->customer->nama ?? $nota->customer->nama_customer ?? 'N/A' }}</span>
                                    @else
                                        <span class="badge bg-secondary">Internal Stok</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($nota->tgl_order)->format('d M Y') }}</td>
                                <td>
                                    <span class="text-danger fw-bold">
                                        {{ \Carbon\Carbon::parse($nota->tgl_deadline)->format('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1.5">
                                        {{ $nota->job_orders->count() }} Baris Pesanan
                                    </span>
                                </td>
                                <td><strong class="text-dark">Rp {{ number_format($nota->total_harga, 0, ',', '.') }}</strong></td>
                                <td><span class="text-secondary">Rp {{ number_format($nota->nominal_dp_masuk, 0, ',', '.') }}</span></td>
                                <td>
                                    @if($nota->status_bayar === 'Lunas')
                                        <span class="badge bg-success px-2 py-1.5">Lunas</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2 py-1.5">DP / Belum Lunas</span>
                                    @endif
                                </td>
                                <td>
                                    @if($nota->status_order === 'Valid')
                                        <span class="badge bg-primary px-2 py-1.5">Valid (Antrean)</span>
                                    @elseif($nota->status_order === 'Progress')
                                        <span class="badge bg-info text-dark px-2 py-1.5">Progress</span>
                                    @else
                                        <span class="badge bg-success px-2 py-1.5">Selesai</span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <a href="{{ route('nota_order.show', $nota->id) }}" class="btn btn-sm btn-outline-secondary px-2 text-nowrap">
                                        👁 Detail & Spek
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <div class="my-2">
                                        <span class="fs-4 d-block">📦</span>
                                        Belum ada data transaksi order yang tersimpan.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Area Pagination yang Sudah Diperbaiki UI-nya --}}
        @if($notaOrders->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="text-muted small">
                        Menampilkan <strong>{{ $notaOrders->currentPage() }}</strong> dari <strong>{{ $notaOrders->lastPage() }}</strong> (Total: {{ $notaOrders->total() }} data)
                    </div>
                    <div class="pagination-clean m-0">
                        {{ $notaOrders->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Inline CSS Tambahan untuk mereduksi ukuran pagination bawaan agar proposional dengan tabel --}}
<style>
    .pagination-clean .pagination {
        margin-bottom: 0 !important;
    }
    .pagination-clean .page-link {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
</style>
@endsection