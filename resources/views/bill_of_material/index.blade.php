@extends('layouts.app')

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Resep (Bill of Materials)</h5>
        <a href="{{ route('bill_of_material.create') }}" class="btn btn-light btn-sm">+ Tambah</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Produk & Varian</th>
                    <th>Jumlah Komponen</th>
                    <th>Estimasi Biaya Material</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($boms as $varianId => $items)
                <tr>
                    <td>
                        <strong>{{ $items->first()->varian->produk->nama ?? 'Produk N/A' }}</strong>
                        <small class="text-muted d-block">
                            Warna: {{ $items->first()->varian->warna ?? '-' }} | Ukuran: {{ $items->first()->varian->ukuran ?? '-' }}
                        </small>
                    </td>
                    <td>{{ $items->count() }} Jenis Bahan</td>
                    <td>
                        @php
                            $totalEstimasi = $items->sum(function($bom) {
                                return $bom->jumlah * ($bom->bahan_baku->harga_estimasi ?? 0);
                            });
                        @endphp
                        Rp {{ number_format($totalEstimasi, 0, ',', '.') }}
                    </td>
                    <td>
                        <a href="{{ route('bill_of_material.edit', $varianId) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('bill_of_material.destroy', $varianId) }}" method="POST" class="d-inline">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection