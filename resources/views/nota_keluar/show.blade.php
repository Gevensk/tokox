@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1 fw-bold text-dark">Detail Nota Stok Keluar: #{{ $nota->id }}</h3>
                    <p class="text-muted mb-0">Tanggal Keluar: <strong class="text-dark">{{ $nota->created_at->format('d M Y H:i') }}</strong></p>
                </div>
                <div class="d-flex gap-2 align-items-center">

                    <a href="{{ route('nota_keluar.print', $nota->id) }}" class="btn btn-info text-white btn-sm fw-bold" target="_blank">
                        🖨️ Cetak Nota
                    </a>
                    <a href="{{ route('nota_keluar.index') }}" class="btn btn-secondary btn-sm">
                         Kembali
                    </a>
                </div>
            </div>
            <hr>

            <!-- Row 1: Informasi Ringkasan Finansial & Customer -->
            <div class="row mb-4">
                <!-- Info Customer -->
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-dark text-white fw-bold">👤 Informasi Pelanggan</div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.95rem;">
                                <tr>
                                    <td class="fw-bold text-dark" style="width: 35%;">Nama Pelanggan</td>
                                    <td class="text-dark">: {{ $nota->customer->nama ?? $nota->customer->nama_customer ?? 'Customer Umum' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-dark">No. HP</td>
                                    <td class="text-dark">: {{ $nota->customer->no_hp ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-dark">Alamat</td>
                                    <td class="text-dark">: {{ $nota->customer->alamat ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Info Finansial & Status -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-dark text-white fw-bold">💰 Informasi Keuangan & Tipe</div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.95rem;">
                                <tr>
                                    <td class="fw-bold text-dark" style="width: 35%;">Tipe Pengeluaran</td>
                                    <td class="text-dark">: 
                                        @if($nota->tipe_keluar === 'penjualan_order')
                                            <span class="text-success fw-bold">Pelunasan Nota Order (PO)</span>
                                        @else
                                            <span class="text-secondary fw-bold">Penjualan Langsung (Cash & Carry)</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-dark">Referensi Order</td>
                                    <td class="text-dark">: 
                                        @if($nota->nota_order_id && $nota->nota_order)
                                            <a href="{{ route('nota_order.show', $nota->nota_order_id) }}" class="fw-bold text-primary text-decoration-none">
                                                {{ $nota->nota_order->no_order }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-dark">Total Uang Masuk</td>
                                    <td class="text-dark">: <strong class="text-success fs-5">Rp {{ number_format($nota->total_bayar, 0, ',', '.') }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Rincian Barang Keluar -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-secondary text-white fw-bold">📦 Rincian Muatan Barang Keluar</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.95rem;">
                            <thead class="table-light" style="font-size: 0.95rem;">
                                <tr>
                                    <th class="ps-3" style="width: 5%;">No</th>
                                    <th style="width: 80px;">Foto</th>
                                    <th>Produk & Varian</th>
                                    <th class="text-center" style="width: 15%;">Qty</th>
                                    <th class="text-end" style="width: 20%;">Harga Satuan</th>
                                    <th class="text-end" style="width: 20%;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach($nota->nota_keluar as $detail)
                                    @php
                                        // Cari apakah ada JobOrder terkait order ini untuk cek is_custom & foto_desain
                                        $isCustom = false;
                                        $fotoDesain = null;
                                        if ($nota->nota_order_id) {
                                            $job = \App\Models\JobOrder::where('nota_order_id', $nota->nota_order_id)
                                                ->where('produk_varian_id', $detail->produk_varian_id)
                                                ->first();
                                            if ($job) {
                                                $isCustom = $job->is_custom;
                                                $fotoDesain = $job->foto_desain;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="ps-3 text-secondary">{{ $no++ }}</td>
                                        <td>
                                            @if($isCustom && $fotoDesain)
                                                <a href="{{ asset('storage/desain_order/' . $fotoDesain) }}" target="_blank" title="Klik untuk memperbesar">
                                                    <img src="{{ asset('storage/desain_order/' . $fotoDesain) }}" alt="Desain" class="img-thumbnail rounded border border-warning" style="width: 50px; height: 50px; object-fit: cover;">
                                                </a>
                                            @elseif($detail->produk_varian->produk->foto)
                                                <img src="{{ asset('storage/produk_katalog/' . $detail->produk_varian->produk->foto) }}" alt="Foto" class="img-thumbnail rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light border text-center text-muted rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 0.65rem;">
                                                    No Foto
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark" style="font-size: 1rem;">{{ $detail->produk_varian->produk->nama ?? 'Produk' }}</span>
                                            <div class="text-muted" style="font-size: 0.85rem;">
                                                Warna: {{ $detail->produk_varian->warna ?? '-' }} | Ukuran: {{ $detail->produk_varian->ukuran ?? '-' }}
                                            </div>
                                            @if($isCustom)
                                                <span class="badge bg-warning text-dark mt-1" style="font-size: 0.68rem;">Custom Spec</span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold text-dark" style="font-size: 1rem;">
                                            {{ $detail->qty }} <span class="fw-normal text-secondary" style="font-size: 0.85rem;">pcs</span>
                                        </td>
                                        <td class="text-end text-dark">
                                            Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold text-dark" style="font-size: 1rem;">
                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
