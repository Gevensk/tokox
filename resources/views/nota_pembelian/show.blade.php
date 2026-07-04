@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Detail Nota Pembelian: #{{ $nota->id }}</h3>
            <p class="text-muted mb-0">Tanggal Pembelian: <strong class="text-dark">{{ date('d M Y H:i', strtotime($nota->tgl_beli)) }}</strong></p>
        </div>
        <div class="d-flex gap-2 align-items-center">

            <a href="{{ route('nota_pembelian.index') }}" class="btn btn-secondary btn-sm">
                Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Kolom Kiri: Informasi Nota & Supplier -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold py-3">
                    <i class="fa-solid fa-circle-info me-2"></i> Informasi Pembelian
                </div>
                <div class="card-body">
                    <!-- Informasi Pemasok -->
                    <h6 class="fw-bold text-secondary mb-3 text-uppercase small" style="letter-spacing: 0.5px;">Pemasok / Vendor</h6>
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-light p-2 rounded me-3 text-primary">
                            <i class="fa-solid fa-truck-field fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">{{ $nota->supplier->nama }}</h5>
                            <p class="mb-1 text-muted small"><i class="fa-solid fa-phone me-1"></i> {{ $nota->supplier->no_hp ?? 'Tidak ada kontak' }}</p>
                            <p class="mb-0 text-muted small"><i class="fa-solid fa-location-dot me-1"></i> {{ $nota->supplier->alamat ?? 'Tidak ada alamat' }}</p>
                        </div>
                    </div>

                    <hr class="my-3 text-secondary">

                    <!-- Metadata Transaksi -->
                    <table class="table table-sm table-borderless mb-4">
                        <tr>
                            <td class="text-muted" style="width: 45%;">Tanggal Pembelian</td>
                            <td>: <strong class="text-dark">{{ date('d M Y H:i', strtotime($nota->tgl_beli)) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Item</td>
                            <td>: {{ $nota->details->count() }} Jenis Bahan</td>
                        </tr>
                    </table>

                    <!-- Rincian Biaya -->
                    <div class="bg-light p-3 rounded border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Harga Bahan:</span>
                            <span class="fw-semibold text-dark">Rp {{ number_format($nota->total_harga_bahan, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ongkos Kirim:</span>
                            <span class="fw-semibold text-muted">Rp {{ number_format($nota->ongkir, 0, ',', '.') }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Total Bayar:</span>
                            <span class="fs-5 fw-bold text-success">Rp {{ number_format($nota->total_bayar, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Daftar Muatan Bahan Baku -->
        <div class="col-md-8 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-secondary text-white fw-bold py-3">
                    <i class="fa-solid fa-boxes-stacked me-2"></i> Daftar Muatan Bahan Baku
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 8%">No</th>
                                    <th>Nama Bahan Baku</th>
                                    <th class="text-center" style="width: 20%">Jumlah (Qty)</th>
                                    <th style="width: 25%">Harga Satuan</th>
                                    <th class="pe-3" style="width: 25%">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nota->details as $index => $detail)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $detail->bahanBaku->nama ?? 'Bahan Baku Terhapus' }}</div>
                                            <small class="text-muted">ID: #{{ $detail->bahanBaku->id ?? '-' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-dark border px-2 py-1.5 fw-semibold fs-7">
                                                {{ number_format($detail->jumlah, 0, ',', '.') }} {{ $detail->bahanBaku->satuan ?? '' }}
                                            </span>
                                        </td>
                                        <td>Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="pe-3 fw-bold text-dark">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-inbox fa-2x mb-2 text-muted"></i>
                                            <div>Tidak ada detail item bahan baku dalam nota ini.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
