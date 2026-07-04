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
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th width="40%">Produk & Varian</th>
                    <th width="15%">Jumlah Komponen</th>
                    <th width="20%">Estimasi Biaya Material</th>
                    <th width="25%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($boms as $varianId => $items)
                @php
                    // Hitung total biaya untuk varian ini
                    $totalEstimasi = $items->sum(function($bom) {
                        return $bom->jumlah * ($bom->bahan_baku->harga_rata_rata ?? 0);
                    });
                @endphp
                
                {{-- Baris Utama Produk --}}
                <tr class="table-default">
                    <td>
                        <strong class="text-primary fs-6">{{ $items->first()->varian->produk->nama ?? 'Produk N/A' }}</strong>
                        <small class="text-muted d-block">
                            Warna: {{ $items->first()->varian->warna ?? '-' }} | Ukuran: {{ $items->first()->varian->ukuran ?? '-' }}
                        </small>
                    </td>
                    <td><span class="fw-bold text-dark">{{ $items->count() }} Jenis Bahan</span></td>
                    <td class="fw-bold text-success">
                        Rp {{ number_format($totalEstimasi, 0, ',', '.') }}
                    </td>
                    <td>
                        <button class="btn btn-info btn-sm text-dark" type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#detail-bom-{{ $varianId }}" 
                                aria-expanded="false">
                            Detail
                        </button>
                        
                        <a href="{{ route('bill_of_material.edit', $varianId) }}" class="btn btn-warning btn-sm">Edit</a>
                        
                        <form action="{{ route('bill_of_material.destroy', $varianId) }}" method="POST" class="d-inline">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus seluruh resep BoM untuk varian ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>

                {{-- Baris Tersembunyi (Akan muncul saat tombol "Detail" diklik) --}}
                <tr class="collapse" id="detail-bom-{{ $varianId }}">
                    <td colspan="4" class="bg-light p-3">
                        <div class="card card-body border-secondary shadow-sm">
                            <h6 class="fw-bold text-secondary mb-3">🛠️ Rincian Kebutuhan Komponen Material (Per 1 Pcs):</h6>
                            <table class="table table-sm table-bordered bg-white mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nama Bahan Baku</th>
                                        <th class="text-center">Kebutuhan Resep</th>
                                        <th class="text-end">Harga Estimasi / Satuan</th>
                                        <th class="text-end">Subtotal Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $bom)
                                    @php
                                        $subtotalItem = $bom->jumlah * ($bom->bahan_baku->harga_rata_rata ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $bom->bahan_baku->nama ?? 'Bahan N/A' }}</td>
                                        <td class="text-center fw-bold text-dark">
                                            {{ (float)$bom->jumlah }} {{ $bom->bahan_baku->satuan ?? '' }}
                                        </td>
                                        <td class="text-end text-muted">
                                            Rp {{ number_format($bom->bahan_baku->harga_rata_rata ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold text-secondary">
                                            Rp {{ number_format($subtotalItem, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="table-light fw-bold">
                                        <td colspan="3" class="text-end">Total Estimasi Pokok Material:</td>
                                        <td class="text-end text-success fs-6">Rp {{ number_format($totalEstimasi, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection