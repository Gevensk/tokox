@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold text-dark">Riwayat Pembelian Bahan Baku</h3>
        <a href="{{ route('nota_pembelian.create') }}" class="btn btn-primary px-3 shadow-sm">
            + Input Nota Baru
        </a>
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
                            <th class="ps-3">Tanggal Beli</th>
                            <th>Supplier</th>
                            <th>Total Harga Bahan</th>
                            <th>Ongkir</th>
                            <th>Total Bayar</th>
                            <th class="text-center pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Mengganti @foreach menjadi @forelse untuk menangani kondisi data kosong --}}
                        @forelse($history as $item)
                        <tr>
                            <td class="ps-3">{{ date('d M Y H:i', strtotime($item->tgl_beli)) }}</td>
                            <td>
                                <span class="badge bg-info text-dark me-1">Vendor</span> 
                                <span class="fw-semibold">{{ $item->supplier->nama }}</span>
                            </td>
                            <td>Rp {{ number_format($item->total_harga_bahan, 0, ',', '.') }}</td>
                            <td><span class="text-muted">Rp {{ number_format($item->ongkir, 0, ',', '.') }}</span></td>
                            <td><strong class="text-success">Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</strong></td>
                            <td class="text-center pe-3">
                                <a href="{{ route('nota_pembelian.show', $item->id) }}" class="btn btn-sm btn-outline-secondary text-nowrap">
                                    👁 Detail Nota
                                </a>
                            </td>
                        </tr>
                        @empty
                        {{-- Tampilan informatif jika riwayat nota pembelian masih kosong --}}
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="my-2">
                                    Belum ada data riwayat pembelian bahan baku yang tersimpan.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection