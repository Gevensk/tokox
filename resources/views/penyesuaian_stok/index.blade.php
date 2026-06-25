@extends('layouts.app')

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Riwayat Penyesuaian Stok Bahan (Stock Opname)</h5>
        <a href="{{ route('penyesuaian_stok.create') }}" class="btn btn-light btn-sm">+ Tambah</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Tanggal & Waktu</th>
                    <th>Nama Bahan Baku</th>
                    <th>Tipe</th>
                    <th>Jumlah</th>
                    <th>Keterangan Alasan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penyesuaianStoks as $index => $stok)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ date('d M Y H:i', strtotime($stok->created_at)) }}</td>
                    <td><strong>{{ $stok->bahan_baku->nama ?? 'Bahan Terhapus' }}</strong></td>
                    <td>
                        @if($stok->tipe == 'in')
                            <span class="text-success fw-bold">Stok In</span>
                        @else
                            <span class="text-danger fw-bold">Stok Out</span>
                        @endif
                    </td>
                    <td>{{ (float)$stok->jumlah }} {{ $stok->bahan_baku->satuan ?? '' }}</td>
                    <td>{{ $stok->keterangan }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat penyesuaian stok gudang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($penyesuaianStoks->hasPages())
        <div class="mt-3">
            {{ $penyesuaianStoks->links() }}
        </div>
        @endif
    </div>
</div>
@endsection