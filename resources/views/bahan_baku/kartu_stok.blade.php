@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-11">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold text-white">📋 Kartu Stok Bahan Baku</h5>
                    <p class="mb-0 small text-white-50">Bahan: <strong>{{ $bahan->nama }}</strong> | Satuan: {{ $bahan->satuan }}</p>
                </div>
                <a href="{{ route('bahan_baku.index') }}" class="btn btn-light btn-sm border fw-bold text-dark">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light-subtle shadow-sm text-center">
                            <small class="text-muted fw-bold d-block text-uppercase mb-1" style="font-size: 0.75rem;">Stok Fisik Saat Ini</small>
                            <h3 class="fw-bold text-primary mb-0">
                                {{ (float)$bahan->stok_total }} <span class="fs-6 text-muted">{{ $bahan->satuan }}</span>
                            </h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light-subtle shadow-sm text-center">
                            <small class="text-muted fw-bold d-block text-uppercase mb-1" style="font-size: 0.75rem;">Harga Rata-Rata Saat Ini</small>
                            <h3 class="fw-bold text-success mb-0">
                                Rp {{ number_format($bahan->harga_rata_rata, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="table-responsive shadow-sm rounded border" style="max-height: 450px; overflow-y: auto;">
                    <table class="table table-striped table-hover align-middle mb-0" style="font-size: 0.95rem;">
                        <thead class="table-dark" style="font-size: 0.95rem; position: sticky; top: 0; z-index: 10;">
                            <tr>
                                <th style="width: 15%; position: sticky; top: 0; z-index: 10;">Tanggal</th>
                                <th style="width: 15%; text-align: center; position: sticky; top: 0; z-index: 10;">Tipe Transaksi</th>
                                <th style="width: 12%; text-align: right; position: sticky; top: 0; z-index: 10;">Masuk</th>
                                <th style="width: 12%; text-align: right; position: sticky; top: 0; z-index: 10;">Keluar</th>
                                <th style="width: 12%; text-align: right; position: sticky; top: 0; z-index: 10;">Saldo Berjalan</th>
                                <th style="width: 34%; position: sticky; top: 0; z-index: 10;">Detail Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mutations as $m)
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ date('d-m-Y H:i', strtotime($m->tanggal)) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($m->tipe == 'Pembelian')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1.5 w-100 fw-bold" style="max-width: 150px; font-size: 0.88rem;">Pembelian</span>
                                        @elseif($m->tipe == 'Pemakaian Resep BoM')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1.5 w-100 fw-bold" style="max-width: 150px; font-size: 0.88rem;">Resep BoM</span>
                                        @elseif($m->tipe == 'Pemakaian Ekstra')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1.5 w-100 fw-bold" style="max-width: 150px; font-size: 0.88rem;">Bahan Ekstra</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1.5 w-100 fw-bold" style="max-width: 150px; font-size: 0.88rem;">Penyesuaian</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-success fw-semibold" style="font-size: 1rem;">
                                        @if($m->masuk > 0)
                                            +{{ (float)$m->masuk }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end text-danger fw-semibold" style="font-size: 1rem;">
                                        @if($m->keluar > 0)
                                            -{{ (float)$m->keluar }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark" style="font-size: 1rem;">
                                        {{ (float)$m->saldo }} <small class="text-muted">{{ $bahan->satuan }}</small>
                                    </td>
                                    <td>
                                        <span class="text-dark d-block" style="font-size: 0.95rem;">{{ $m->keterangan }}</span>
                                        @if($m->harga > 0)
                                            <small class="text-muted" style="font-size: 0.88rem;">*Harga Bahan: Rp {{ number_format($m->harga, 0, ',', '.') }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-history me-1"></i> Belum ada riwayat mutasi stok untuk bahan baku ini.
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
@endsection
