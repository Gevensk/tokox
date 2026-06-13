@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Riwayat Penyesuaian Stok Bahan (Stock Opname)</h3>
        <a href="{{ route('penyesuaian_stok.create') }}" class="btn btn-primary">+ Input Penyesuaian Baru</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>Tanggal & Waktu</th>
                            <th>Nama Bahan Baku</th>
                            <th class="text-center">Tipe</th>
                            <th class="text-end" style="width: 15%;">Jumlah</th>
                            <th>Keterangan Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penyesuaianStoks as $index => $stok)
                            <tr>
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                <td>{{ date('d M Y H:i', strtotime($stok->created_at)) }}</td>
                                <td><strong>{{ $stok->bahan_baku->nama ?? 'Bahan Terhapus' }}</strong></td>
                                <td class="text-center">
                                    @if($stok->tipe == 'in')
                                        <span class="badge bg-success py-2 px-3 text-uppercase" style="letter-spacing: 0.5px;">➕ Stok In</span>
                                    @else
                                        <span class="badge bg-danger py-2 px-3 text-uppercase" style="letter-spacing: 0.5px;">➖ Stok Out</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    {{ $stok->jumlah }} {{ $stok->bahan_baku->satuan ?? '' }}
                                </td>
                                <td><span class="text-secondary small">{{ $stok->keterangan }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat penyesuaian stok gudang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($penyesuaianStoks->hasPages())
        <div class="card-footer bg-white">
            {{ $penyesuaianStoks->links() }}
        </div>
        @endif
    </div>
</div>
@endsection