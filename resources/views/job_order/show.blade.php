@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h4 mb-1 fw-bold text-dark">📊 Detail Evaluasi HPP & Ongkir</h1>
            <p class="text-muted small mb-0">Job Order: <strong>#{{ $job->id }}</strong> | Vendor: {{ $job->nota_produksi->penjahit->nama ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('job_order.index') }}" class="btn btn-light btn-sm border fw-bold">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @php
        $targetPcs = max(1, $job->jumlah);
        $totalBagus = $job->hasil_produksis->sum('qty_accepted');
        $totalCacat = $job->hasil_produksis->sum('qty_rejected_defect');
        $totalRusak = $job->hasil_produksis->sum('qty_rejected');
        
        // Produk layak jual (Grade A + Grade B)
        $totalLayakJual = $totalBagus + $totalCacat; 
        
        // Ongkir Kirim Awal (Mencari selisih modal)
        $ongkirKirimAwal = max(0, $job->total_hpp_job - $job->total_biaya_bahan - ($targetPcs * $job->biaya_jasa_satuan));
        
        // Total Ongkir Balik dari semua setoran parsial
        $totalOngkirBalik = $job->hasil_produksis->sum('ongkir');

        // Rumus Pembebanan Biaya (Sesuai kode awalmu)
        $porsiModalDiserap = $totalLayakJual > 0 ? (($totalLayakJual / $targetPcs) * $job->total_hpp_job) : 0;
        $totalModalAktual = $porsiModalDiserap + $totalOngkirBalik;
        $hppAktualSatuan = $totalLayakJual > 0 ? ($totalModalAktual / $totalLayakJual) : 0;
    @endphp

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-bold text-secondary mb-3">🧮 Alur Akumulasi Biaya</h6>
                    
                    <table class="table table-sm table-borderless align-middle mb-3">
                        <tbody>
                            <tr>
                                <td class="text-muted">Biaya Bahan Baku</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($job->total_biaya_bahan, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jasa Jahit Vendor <small>({{ $targetPcs }} pcs)</small></td>
                                <td class="text-end fw-semibold">Rp {{ number_format($targetPcs * $job->biaya_jasa_satuan, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted">🚚 Porsi Ongkir Kirim (Awal)</td>
                                <td class="text-end text-danger fw-semibold">+ Rp {{ number_format($ongkirKirimAwal, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-light">
                                <td class="fw-bold">Total Modal</td>
                                <td class="text-end fw-bold text-dark">Rp {{ number_format($job->total_hpp_job, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted">🚚 Total Ongkir Setoran (Balik)</td>
                                <td class="text-end text-danger fw-semibold">+ Rp {{ number_format($totalOngkirBalik, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="p-3 bg-success bg-opacity-10 rounded border border-success border-opacity-25 text-center">
                        <small class="text-success fw-bold d-block mb-1">REAL MOVING AVERAGE HPP</small>
                        <h3 class="fw-bold text-success mb-0">Rp {{ number_format($hppAktualSatuan, 0, ',', '.') }} <span class="fs-6 text-muted">/ Pcs</span></h3>
                        <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">
                            *Sudah membebankan biaya baju rusak total ({{ $totalRusak }} pcs) dan semua ongkir balik ke baju layak jual ({{ $totalLayakJual }} pcs).
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-secondary mb-3">📦 Riwayat Terima & Ongkir Parsial</h6>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch / Tgl</th>
                                    <th class="text-center text-success">Layak (A+B)</th>
                                    <th class="text-center text-danger">Gagal</th>
                                    <th class="text-end">Ongkir Balik</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->hasil_produksis as $index => $history)
                                <tr>
                                    <td>
                                        <span class="fw-bold d-block">Batch #{{ $index + 1 }}</span>
                                        <small class="text-muted" style="font-size: 0.75rem;">{{ date('d/m/y', strtotime($history->tgl_terima)) }}</small>
                                    </td>
                                    <td class="text-center fw-semibold">{{ $history->qty_accepted + $history->qty_rejected_defect }} pcs</td>
                                    <td class="text-center text-danger">{{ $history->qty_rejected }} pcs</td>
                                    <td class="text-end font-monospace text-muted">Rp {{ number_format($history->ongkir, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat setoran QC.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($job->hasil_produksis->count() > 0)
                            <tfoot class="table-light fw-bold border-top">
                                <tr>
                                    <td>TOTAL</td>
                                    <td class="text-center text-success">{{ $totalLayakJual }} pcs</td>
                                    <td class="text-center text-danger">{{ $totalRusak }} pcs</td>
                                    <td class="text-end text-dark">Rp {{ number_format($totalOngkirBalik, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection