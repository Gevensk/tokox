@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Daftar Transaksi & Antrean Order</h3>
        <a href="{{ route('nota_order.create') }}" class="btn btn-primary">+ Buat Order Baru</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No. Order</th>
                            <th>Customer / Tipe</th>
                            <th>Tgl Masuk</th>
                            <th>Deadline</th>
                            <th>Total Baris Item</th>
                            <th>Total Bayar (Deal)</th>
                            <th>Nominal DP Masuk</th>
                            <th>Status Bayar</th>
                            <th>Status Order</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notaOrders as $nota)
                        <tr>
                            <td>
                                <strong>{{ $nota->no_order }}</strong>
                            </td>
                            <td>
                                @if($nota->tipe_order === 'Customer')
                                    <span class="badge bg-info text-dark">Customer</span> 
                                    {{ $nota->customer->nama ?? $nota->customer->nama_customer ?? 'N/A' }}
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
                                <span class="badge bg-light text-dark border">
                                    {{ $nota->job_orders->count() }} Baris Pesanan
                                </span>
                            </td>
                            <td><strong>Rp {{ number_format($nota->harga_deal, 0, ',', '.') }}</strong></td>
                            <td><span class="text-secondary">Rp {{ number_format($nota->nominal_dp_masuk, 0, ',', '.') }}</span></td>
                            <td>
                                @if($nota->status_bayar === 'Lunas')
                                    <span class="badge bg-success">Lunas</span>
                                @else
                                    <span class="badge bg-warning text-dark">DP / Belum Lunas</span>
                                @endif
                            </td>
                            <td>
                                @if($nota->status_order === 'Valid')
                                    <span class="badge bg-primary">Valid (Antrean)</span>
                                @elseif($nota->status_order === 'Progress')
                                    <span class="badge bg-info text-dark">Diproduksi</span>
                                @else
                                    <span class="badge bg-success">Selesai</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('nota_order.show', $nota->id) }}" class="btn btn-sm btn-outline-secondary text-nowrap">
                                    👁 Lihat Detail & Spek
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">Belum ada transaksi order masuk.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($notaOrders->hasPages())
        <div class="card-footer bg-white">
            {{ $notaOrders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection