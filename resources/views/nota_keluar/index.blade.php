@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Riwayat Nota Stok Keluar</h3>
        <a href="{{ route('nota_keluar.create') }}" class="btn btn-primary">+ Buat Transaksi Keluar</a>
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
                            <th>No. Nota</th>
                            <th>Customer / Pelanggan</th>
                            <th>Tgl Keluar</th>
                            <th>Tipe Pengeluaran</th>
                            <th>Referensi Order</th>
                            <th>Total Uang Masuk</th>
                            <th>Rincian Muatan Item</th>
                            <th class="text-center">Aksi</th>
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
                            <td class="text-center">
                                <a href="{{ route('nota_keluar.print', $nota->id) }}" class="btn btn-sm btn-warning text-dark text-nowrap" target="_blank">
                                    🖨️ Cetak
                                </a>
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
</div>
@endsection