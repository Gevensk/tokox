@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detail Nota Produksi: {{ $nota->no_surat_jalan }}</h3>
        <<div>
        <a href="{{ route('nota_production.cetak_pdf', $nota->id) }}" class="btn btn-warning text-dark me-2" target="_blank">
            🖨 Cetak Surat Jalan (PDF)
        </a>
        <a href="{{ route('nota_production.index') }}" class="btn btn-outline-secondary">⬅ Kembali</a>
    </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">ℹ Informasi Produksi</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 40%;">Penjahit / PIC</td>
                            <td>: <strong>{{ $nota->penjahit->nama ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alamat Penjahit</td>
                            <td>: {{ $nota->penjahit->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Mulai</td>
                            <td>: {{ date('d-m-Y H:i', strtotime($nota->tgl_order)) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Kirim</td>
                            <td>: {{ $nota->tgl_kirim ? date('d-m-Y H:i', strtotime($nota->tgl_kirim)) : '-' }}</td>
                        </tr>
                        @if(Auth::user()->isAdmin())
                        <tr>
                            <td class="text-muted">Ongkir Bahan</td>
                            <td>: <span class="text-danger fw-bold">Rp {{ number_format($nota->ongkir_kirim_bahan, 0, ',', '.') }}</span></td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Status Nota</td>
                            <td>: 
                                <span class="badge {{ $nota->status_nota == 'Proses' ? 'bg-warning text-dark' : 'bg-success' }}">
                                    {{ $nota->status_nota }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Kirim</td>
                            <td>: 
                                <span class="badge {{ $nota->status_kirim == 'Belum Dikirim' ? 'bg-danger' : 'bg-info text-dark' }}">
                                    {{ $nota->status_kirim }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    @if($nota->status_kirim == 'Belum Dikirim')
                        <div class="alert alert-warning mt-3 mb-0 py-2 small">
                            📦 <strong>Skenario Toko:</strong> Kain & aksesoris sedang disiapkan oleh tim logistik toko sebelum diserahkan ke penjahit.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-secondary text-white fw-bold">👕 Daftar Pesanan Pakaian (Job Order)</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Job ID</th>
                                    <th>Produk & Varian</th>
                                    <th class="text-center">Qty (Pcs)</th>
                                    <th class="text-center">Estimasi Selesai</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nota->jobOrders as $job)
                                    <tr>
                                        <td>JOB-{{ $job->id }}</td>
                                        <td>{{ $job->produk_varian->produk->nama ?? 'Produk' }}</td>
                                        <td class="text-center fw-bold">{{ $job->jumlah }} Pcs</td>
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
                                                <span class="badge bg-warning text-dark">Sedang Dijahit</span>
                                            @else
                                                <span class="badge bg-success">Selesai & Lolos QC</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                        @if($job->status_pesanan == 'Diproduksi')
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
        <div class="card-header bg-dark text-white fw-bold">📋 Kalkulasi Bahan Baku Keluar (Berdasarkan Resep BoM)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Bahan Baku</th>
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
                                @endphp
                                <tr>
                                    <td><strong>{{ $pakai->bahan_baku->nama ?? 'Bahan' }}</strong></td>
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
                                <td colspan="5" class="text-center py-3 text-muted">Belum ada history pengeluaran bahan.</td>
                            </tr>
                        @endforelse
                        @if(Auth::user()->isAdmin())
                        <tr class="table-dark fw-bold">
                            <td colspan="4" class="text-end">TOTAL BIAYA PRODUKSI BAHAN BAKU :</td>
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