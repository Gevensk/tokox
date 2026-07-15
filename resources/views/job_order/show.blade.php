@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Detail Akumulasi Evaluasi HPP: #{{ $job->id }}</h3>
            <p class="text-muted mb-0">Vendor: <strong>{{ $job->nota_produksi->penjahit->nama ?? 'N/A' }}</strong> | Status Job: <strong>{{ $job->status_pesanan }}</strong></p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('job_order.index') }}" class="btn btn-secondary btn-sm">
                Kembali
            </a>
        </div>
    </div>
    <hr>

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

        // Total Biaya Rework dari semua setoran parsial
        $totalRework = $job->hasil_produksis->sum('biaya_rework_tambahan');

        // Rumus Pembebanan Biaya (Jika job order sudah Selesai, seluruh biaya awal diserap habis)
        $porsiModalDiserap = ($job->status_pesanan === 'Selesai')
            ? $job->total_hpp_job
            : ($totalLayakJual > 0 ? (($totalLayakJual / $targetPcs) * $job->total_hpp_job) : 0);
        $totalModalAktual = $porsiModalDiserap + $totalOngkirBalik + $totalRework;
        $hppAktualSatuan = $totalLayakJual > 0 ? ($totalModalAktual / $totalLayakJual) : 0;
    @endphp

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold py-3">
                    🧮 Alur Akumulasi Biaya
                </div>
                <div class="card-body">
                    
                    <table class="table table-sm table-borderless align-middle mb-3">
                        <tbody>
                            <tr>
                                <td class="text-muted">Biaya Bahan Baku</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($job->total_biaya_bahan, 2, ',', '.') }}</td>
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
                            <tr class="border-bottom">
                                <td class="text-muted">🔧 Total Biaya Rework Tambahan</td>
                                <td class="text-end text-danger fw-semibold">+ Rp {{ number_format($totalRework, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-light border-top">
                                <td class="fw-bold text-dark">Total HPP (Modal Aktual)</td>
                                <td class="text-end fw-bold text-dark">Rp {{ number_format($totalModalAktual, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="p-3 bg-success bg-opacity-10 rounded border border-success border-opacity-25 text-center">
                        <small class="text-success fw-bold d-block mb-1">TOTAL HPP</small>
                        <h4 class="fw-bold text-success mb-3">Rp {{ number_format($totalModalAktual, 0, ',', '.') }}</h4>

                        <small class="text-success fw-bold d-block mb-1">TOTAL AVERAGE HPP</small>
                        <h3 class="fw-bold text-success mb-0">Rp {{ number_format($hppAktualSatuan, 0, ',', '.') }} <span class="fs-6 text-muted">/ Pcs</span></h3>
                        <small class="text-muted mt-2 d-block" style="font-size: 0.82rem;">
                            *Sudah membebankan biaya baju rusak total ({{ $totalRusak }} pcs), biaya rework tambahan, dan semua ongkir balik ke baju layak jual ({{ $totalLayakJual }} pcs).
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-secondary text-white fw-bold py-3">
                    📦 Riwayat Terima & Ongkir Parsial
                </div>
                <div class="card-body p-0">
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.95rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Batch / Tgl</th>
                                    <th class="text-center text-success">Accepted</th>
                                    <th class="text-center text-warning">Defect</th>
                                    <th class="text-center text-danger">Reject</th>
                                    <th class="text-end">Rework Cost</th>
                                    <th class="text-end">Ongkir Balik</th>
                                    <th class="text-center pe-3" style="width: 1%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->hasil_produksis as $index => $history)
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-bold d-block">Batch #{{ $index + 1 }}</span>
                                        <small class="text-muted" style="font-size: 0.82rem;">{{ date('d/m/y', strtotime($history->tgl_terima)) }}</small>
                                    </td>
                                    <td class="text-center text-success fw-semibold">{{ $history->qty_accepted }} pcs</td>
                                    <td class="text-center text-warning fw-semibold">{{ $history->qty_rejected_defect }} pcs</td>
                                    <td class="text-center text-danger fw-semibold">{{ $history->qty_rejected }} pcs</td>
                                    <td class="text-end text-dark">Rp {{ number_format($history->biaya_rework_tambahan, 0, ',', '.') }}</td>
                                    <td class="text-end text-dark">Rp {{ number_format($history->ongkir, 0, ',', '.') }}</td>
                                    <td class="text-center pe-3">
                                        <form action="{{ route('hasil_produksi.batal', $history->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan setoran hasil jahit Batch ini? Stok produk dan harga rata-rata akan disesuaikan kembali.');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm fw-bold">Batal</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat setoran QC.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($job->hasil_produksis->count() > 0)
                            <tfoot class="table-light fw-bold border-top">
                                <tr>
                                    <td class="ps-3">TOTAL</td>
                                    <td class="text-center text-success">{{ $totalBagus }} pcs</td>
                                    <td class="text-center text-warning">{{ $totalCacat }} pcs</td>
                                    <td class="text-center text-danger">{{ $totalRusak }} pcs</td>
                                    <td class="text-end text-dark">Rp {{ number_format($totalRework, 0, ',', '.') }}</td>
                                    <td class="text-end text-dark">Rp {{ number_format($totalOngkirBalik, 0, ',', '.') }}</td>
                                    <td></td>
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