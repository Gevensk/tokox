@extends('layouts.app')

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pembelian Bahan (Nota Pembelian)</h5>
        <a href="{{ route('nota_pembelian.create') }}" class="btn btn-light btn-sm">+ Tambah</a>
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
                        <th class="ps-3">Tanggal Beli</th>
                        <th>Supplier</th>
                        <th>Total Harga Bahan</th>
                        <th>Ongkir</th>
                        <th>Total Bayar</th>
                        <th style="width: 1%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
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
                        <td class="text-nowrap" style="width: 1%;">
                            <div class="d-flex justify-content-start align-items-center gap-1">
                                <a href="{{ route('nota_pembelian.show', $item->id) }}" class="btn btn-secondary btn-sm">Detail</a>
                                <form action="{{ route('nota_pembelian.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus nota pembelian ini? Stok akan dikurangi dan harga rata-rata bahan baku akan dihitung ulang.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Belum ada data riwayat pembelian bahan baku yang tersimpan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection