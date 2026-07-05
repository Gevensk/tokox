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
            <table class="table table-hover align-middle" style="font-size: 0.95rem;">
                <thead class="table-light" style="font-size: 0.95rem;">
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
                                <span class="text-success fw-bold">Pelunasan PO</span>
                            @else
                                <span class="text-secondary fw-bold">Beli Langsung</span>
                            @endif
                        </td>
                        <td>
                            @if($nota->nota_order_id && $nota->nota_order)
                                <span class="fw-bold text-dark">{{ $nota->nota_order->no_order }}</span>
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
                                        <span class="fw-bold text-dark">x{{ $detail->qty }} pcs</span>
                                        @if($detail->kondisi === 'defect')
                                            <span class="text-danger small fw-bold">(Defect)</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-nowrap" style="width: 1%;">
                            <div class="d-flex justify-content-start align-items-center gap-1">
                                <a href="{{ route('nota_keluar.print', $nota->id) }}" class="btn btn-info btn-sm text-white" target="_blank">Cetak</a>
                                <a href="{{ route('nota_keluar.show', $nota->id) }}" class="btn btn-secondary btn-sm">Detail</a>
                                @if(Auth::check() && Auth::user()->isAdmin())
                                    <form action="{{ route('nota_keluar.destroy', $nota->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan nota stok keluar ini? Stok produk toko akan dikembalikan.')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Batal</button>
                                    </form>
                                @endif
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