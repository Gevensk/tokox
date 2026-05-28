@extends('layouts.app') @section('content')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between mb-4">
        <h2>Daftar Resep (BoM)</h2>
        <a href="{{ route('bill_of_material.create') }}" class="btn btn-primary">+ Buat Resep Baru</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Produk & Varian</th>
                        <th>Jumlah Bahan</th>
                        <th>Estimasi Biaya Material</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($boms as $varianId => $items)
                    <tr>
                        <td>
                            <strong>{{ $items->first()->varian->produk->nama }}</strong><br>
                            <small>{{ $items->first()->varian->warna }} - {{ $items->first()->varian->ukuran }}</small>
                        </td>
                        <td>{{ $items->count() }} Jenis Bahan</td>
                        <td>
                            @php
                                $totalEstimasi = $items->sum(function($bom) {
                                    return $bom->jumlah * $bom->bahanBaku->harga_estimasi;
                                });
                            @endphp
                            Rp {{ number_format($totalEstimasi, 0, ',', '.') }}
                        </td>
                        <td>
                            <a href="{{ route('bill_of_material.edit', $varianId) }}" class="btn btn-sm btn-warning">Edit</a>
                            
                            <form action="{{ route('bill_of_material.destroy', $varianId) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus seluruh resep untuk varian ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection