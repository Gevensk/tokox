@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Daftar Resep (Bill of Materials)</h3>
        <a href="{{ route('bill_of_material.create') }}" class="btn btn-primary">+ Buat Resep Baru</a>
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
                            <th>Produk & Varian</th>
                            <th>Jumlah Komponen</th>
                            <th>Estimasi Biaya Material</th>
                            <th class="text-center" style="width: 20%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boms as $varianId => $items)
                        <tr>
                            <td>
                                <strong class="text-dark">{{ $items->first()->varian->produk->nama ?? 'Produk N/A' }}</strong>
                                <div class="text-secondary small mt-1">
                                    <span class="badge bg-light text-dark border">{{ $items->first()->varian->warna ?? '-' }}</span>
                                    <span class="badge bg-light text-dark border">{{ $items->first()->varian->ukuran ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $items->count() }} Jenis Bahan</span>
                            </td>
                            <td>
                                @php
                                    $totalEstimasi = $items->sum(function($bom) {
                                        return $bom->jumlah * ($bom->bahan_baku->harga_estimasi ?? 0);
                                    });
                                @endphp
                                <strong class="text-success">Rp {{ number_format($totalEstimasi, 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    <a href="{{ route('bill_of_material.edit', $varianId) }}" class="btn btn-sm btn-outline-secondary text-nowrap" style="width: 80px;">
                                        ⚙️ Edit
                                    </a>
                                    
                                    <form action="{{ route('bill_of_material.destroy', $varianId) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus seluruh resep untuk varian ini?')">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger text-nowrap" style="width: 80px;">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada daftar resep (BoM) yang dikonfigurasi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection