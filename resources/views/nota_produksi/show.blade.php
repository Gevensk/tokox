@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detail Nota Produksi: {{ $nota->no_surat_jalan }}</h3>
        <div>
        <a href="{{ route('nota_production.cetak_surat_jalan', $nota->id) }}" class="btn btn-warning text-dark me-2" target="_blank">
            🖨 Cetak Surat Jalan (PDF)
        </a>
        <a href="{{ route('nota_production.index') }}" class="btn btn-outline-secondary">⬅ Kembali</a>
    </div>
    </div>

    @php
        $maxEstimasi = $nota->jobOrders->max('estimasi_selesai');
    @endphp

    <!-- 1. Row Informasi Produksi (Full Width) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold d-flex align-items-center">
                    <i class="fa-solid fa-circle-info me-2"></i> Informasi Produksi
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 border-end">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Penjahit / PIC</small>
                            <h5 class="fw-bold mb-1 text-dark">{{ $nota->penjahit->nama ?? '-' }}</h5>
                            <p class="mb-0 text-muted small"><i class="fa-solid fa-location-dot me-1"></i> {{ $nota->penjahit->alamat ?? '-' }}</p>
                        </div>
                        <div class="col-md-3 border-end">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Status</small>
                            <div class="d-flex flex-column gap-1">
                                <div>
                                    <span class="badge {{ $nota->status_nota == 'Proses' ? 'bg-warning text-dark' : 'bg-success' }} fw-bold" style="font-size: 0.75rem;">
                                        Nota: {{ $nota->status_nota }}
                                    </span>
                                </div>
                                <div>
                                    <span class="badge {{ $nota->status_kirim == 'Belum Dikirim' ? 'bg-danger' : 'bg-info text-dark' }} fw-bold" style="font-size: 0.75rem;">
                                        Kirim: {{ $nota->status_kirim }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 border-end">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Tanggal & Deadline</small>
                            <div class="small">
                                <span class="text-muted">Kirim Bahan:</span> {{ $nota->tgl_kirim ? date('d-m-Y H:i', strtotime($nota->tgl_kirim)) : 'Belum Dikirim' }}
                            </div>
                            <div class="small mt-1">
                                <span class="text-muted">Est. Selesai:</span> <strong class="text-danger">{{ $maxEstimasi ? date('d-m-Y', strtotime($maxEstimasi)) : '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Ongkir Kirim Bahan</small>
                            @if(Auth::user()->isAdmin())
                                <h4 class="fw-bold text-danger mb-0">Rp {{ number_format($nota->ongkir_kirim_bahan, 0, ',', '.') }}</h4>
                            @else
                                <span class="text-muted small">Akses Finansial Terbatas</span>
                            @endif
                        </div>
                    </div>

                    @if($nota->status_kirim == 'Belum Dikirim')
                        <div class="alert alert-warning mt-3 mb-0 py-2 px-3 small border-0 shadow-sm" style="background-color: #fff3cd; color: #664d03;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Bahan baku sedang disiapkan dan belum dikirim.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Row Daftar Pesanan Pakaian (Full Width) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white fw-bold">👕 Daftar Pesanan Pakaian (Job Order)</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Job ID</th>
                                    <th>Produk & Varian</th>
                                    <th class="text-center">Qty (Pcs)</th>
                                    <th class="text-end">Jasa Penjahit</th>
                                    <th class="text-end">Total Jasa</th>
                                    <th class="text-center">Estimasi Selesai</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nota->jobOrders as $job)
                                    <tr>
                                        <td>JOB-{{ $job->id }}</td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $job->produk_varian->produk->nama ?? 'Produk' }}</span>
                                            <div class="small text-muted">
                                                Warna: {{ $job->produk_varian->warna ?? '-' }} | Ukuran: {{ $job->produk_varian->ukuran ?? '-' }}
                                            </div>
                                            @if($job->is_custom)
                                                <div class="mt-1 small">
                                                    <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">Custom Spec</span>
                                                    <span class="text-danger fw-semibold" style="font-size: 0.78rem;">* {{ $job->catatan_custom ?? '-' }}</span>
                                                </div>
                                                
                                                @php
                                                    $ekstras = $job->pemakaian_bahan->where('tipe', 'ekstra');
                                                @endphp
                                                
                                                @if($ekstras->count() > 0)
                                                    <div class="mt-2 p-2 bg-light rounded border border-warning" style="font-size: 0.78rem; max-width: 320px;">
                                                        <strong class="text-secondary d-block mb-1">🛠️ Bahan Baku Ekstra:</strong>
                                                        <ul class="list-unstyled mb-0 ps-1">
                                                            @foreach($ekstras as $e)
                                                                <li class="text-dark mb-1">
                                                                    • {{ $e->bahan_baku->nama ?? 'Bahan' }} 
                                                                    <span class="badge bg-secondary ms-1">{{ number_format($e->jumlah, 2) }} {{ $e->bahan_baku->satuan ?? '' }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold">{{ $job->jumlah }} Pcs</td>
                                        <td class="text-end fw-semibold text-success">Rp {{ number_format($job->biaya_jasa_satuan, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold text-dark">Rp {{ number_format($job->biaya_jasa_satuan * $job->jumlah, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if($job->estimasi_selesai)
                                                <span class="fw-semibold text-dark">{{ date('d-m-Y H:i', strtotime($job->estimasi_selesai)) }}</span>
                                            @else
                                                <span class="text-muted small italic">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($job->status_pesanan == 'Antrean')
                                                <span class="badge bg-secondary">Antrean</span>
                                            @elseif($job->status_pesanan == 'Diproduksi')
                                                <span class="badge bg-info text-dark">Sedang Dijahit</span>
                                            @elseif($job->status_pesanan == 'Selesai Parsial')
                                                <span class="badge bg-warning text-dark">Selesai Parsial</span>
                                            @else
                                                <span class="badge bg-success">Selesai & Lolos QC</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                        @if($job->status_pesanan == 'Diproduksi' || $job->status_pesanan == 'Selesai Parsial')
                                            @if($nota->status_kirim == 'Dikirim')
                                                <a href="{{ route('nota_produksi.terima_hasil', ['job_id' => $job->id]) }}" class="btn btn-sm btn-success fw-bold">
                                                    <i class="bi bi-shield-check"></i> Terima Hasil & QC
                                                </a>
                                            @else
                                                <span class="text-muted small"><i class="bi bi-lock-fill"></i> Bahan belum dikirim</span>
                                            @endif
                                        @else
                                            <button class="btn btn-sm btn-light text-success fw-bold" disabled>
                                                <i class="bi bi-patch-check-fill"></i> Terarsip Gudang
                                            </button>
                                        @endif
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

    <div class="card shadow-sm mt-2">
        <div class="card-header bg-dark text-white fw-bold">📋 Rincian Pengeluaran Bahan Baku (Resep BoM & Bahan Ekstra)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Bahan Baku</th>
                            <th>Tipe Bahan</th>
                            <th>Untuk Kebutuhan Job Order</th>
                            <th>Jumlah Keluar Gudang</th>
                            @if(Auth::user()->isAdmin())
                            <th>Harga Rata-Rata Saat Keluar</th>
                            <th>Total Estimasi Biaya Bahan</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotalBahan = 0; @endphp
                        @forelse($nota->jobOrders as $job)
                            @foreach($job->pemakaian_bahan as $pakai)
                                @php 
                                    $subtotal = $pakai->jumlah * $pakai->harga_rata_saat_ini;
                                    $grandTotalBahan += $subtotal;

                                    $isBom = ($pakai->tipe ?? 'bom') === 'bom';
                                @endphp
                                <tr>
                                    <td><strong>{{ $pakai->bahan_baku->nama ?? 'Bahan' }}</strong></td>
                                    <td>
                                        @if($isBom)
                                            <span class="badge bg-primary text-white" style="font-size: 0.75rem;">Resep BoM</span>
                                        @else
                                            <span class="badge bg-warning text-dark" style="font-size: 0.75rem;">Bahan Ekstra</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">Job Order ID: #{{ $job->id }} ({{ $job->produk_varian->produk->nama ?? '' }})</small></td>
                                    <td>{{ number_format($pakai->jumlah, 2) }} {{ $pakai->bahan_baku->satuan ?? '' }}</td>
                                    @if(Auth::user()->isAdmin())
                                    <td>Rp {{ number_format($pakai->harga_rata_saat_ini, 0, ',', '.') }}</td>
                                    <td class="fw-bold text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">Belum ada history pengeluaran bahan.</td>
                            </tr>
                        @endforelse
                        @if(Auth::user()->isAdmin())
                        @php
                            $totalBomCost = 0;
                            $totalEkstraCost = 0;
                            foreach($nota->jobOrders as $job) {
                                foreach($job->pemakaian_bahan as $pakai) {
                                    $sub = $pakai->jumlah * $pakai->harga_rata_saat_ini;
                                    if (($pakai->tipe ?? 'bom') === 'bom') {
                                        $totalBomCost += $sub;
                                    } else {
                                        $totalEkstraCost += $sub;
                                    }
                                }
                            }
                        @endphp
                        <tr class="table-light fw-bold text-secondary" style="font-size: 0.85rem;">
                            <td colspan="5" class="text-end">TOTAL BIAYA RESEP BoM :</td>
                            <td class="text-end text-dark">Rp {{ number_format($totalBomCost, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-warning fw-bold text-dark" style="font-size: 0.85rem;">
                            <td colspan="5" class="text-end">TOTAL BIAYA BAHAN EKSTRA :</td>
                            <td class="text-end text-danger">Rp {{ number_format($totalEkstraCost, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-dark fw-bold">
                            <td colspan="5" class="text-end">TOTAL BIAYA PRODUKSI BAHAN BAKU :</td>
                            <td class="text-end">Rp {{ number_format($grandTotalBahan, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection