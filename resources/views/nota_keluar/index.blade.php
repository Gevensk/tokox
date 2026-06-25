@extends('layouts.app')

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Nota Stok Keluar (Nota Keluar)</h5>
        <a href="{{ route('nota_keluar.create') }}" class="btn btn-light btn-sm">+ Tambah</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Nota</th>
                        <th>Customer / Pelanggan</th>
                        <th>Tgl Keluar</th>
                        <th>Tipe Pengeluaran</th>
                        <th>Referensi Order</th>
                        <th>Total Uang Masuk</th>
                        <th>Rincian Muatan Item</th>
                        <th style="width: 1%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notaKeluar as $nota)
                    <tr>
                        <td>
                            <strong>#{{ $nota->id }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark">Cust</span> 
                            {{ $nota->customer->nama ?? $nota->customer->nama_customer ?? 'N/A' }}
                        </td>
                        <td>{{ $nota->created_at->format('d M Y') }}</td>
                        <td>
                            @if($nota->tipe_keluar === 'penjualan_order')
                                <span class="badge bg-success">Pelunasan PO</span>
                            @else
                                <span class="badge bg-secondary">Beli Langsung</span>
                            @endif
                        </td>
                        <td>
                            @if($nota->nota_order_id)
                                <span class="badge bg-light text-dark border">
                                    Order #{{ $nota->nota_order_id }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><strong class="text-success">Rp {{ number_format($nota->total_bayar, 0, ',', '.') }}</strong></td>
                        <td>
                            <ul class="mb-0 ps-3 small text-secondary">
                                @foreach($nota->nota_keluar as $detail)
                                    <li>
                                        {{ $detail->produk_varian->produk->nama_product ?? $detail->produk_varian->warna ?? 'Varian ID: '.$detail->produk_varian_id }} 
                                        <span class="badge bg-light text-dark border badge-sm">x{{ $detail->qty }} pcs</span>
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-nowrap" style="width: 1%;">
                            <div class="d-flex justify-content-start align-items-center gap-1">
                                <a href="{{ route('nota_keluar.print', $nota->id) }}" class="btn btn-info btn-sm text-white" target="_blank">Cetak</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat pengeluaran stok barang keluar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($notaKeluar->hasPages())
    <div class="card-footer bg-white">
        {{ $notaKeluar->links() }}
    </div>
    @endif
</div>
@endsection