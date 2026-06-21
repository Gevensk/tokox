@extends('layouts.app')

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar & Pemantauan Job Order (Surat Jalan)</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 80px;" class="text-center">ID Job</th>
                    <th style="width: 70px;">Desain</th>
                    <th>Vendor Penjahit</th>
                    <th>Nama Produk Varian</th>
                    <th class="text-center">Target Qty</th>
                    <th class="text-center">Status Pesanan</th>
                    <th class="text-center" style="width: 220px;">Aksi / Evaluasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobOrders as $job)
                <tr>
                    <td class="text-center font-weight-bold text-secondary">#{{ $job->id }}</td>
                    
                    <td>
                        @if(!empty($job->foto_desain))
                            <img src="{{ \Illuminate\Support\Facades\Storage::url('desain_order/' . $job->foto_desain) }}" 
                                 class="img-thumbnail rounded" 
                                 style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#imgModal{{ $job->id }}"
                                 onerror="this.onerror=null; this.src='{{ asset('storage/desain_order/' . $job->foto_desain) }}';">
                        @else
                            <div class="bg-light rounded text-center text-muted d-flex align-items-center justify-content-center border" style="width: 50px; height: 50px; font-size: 0.75rem;">No Img</div>
                        @endif
                    </td>

                    <td>
                        <span class="fw-bold text-dark">{{ $job->nota_produksi->penjahit->nama ?? 'Belum Ditunjuk' }}</span>
                        <div class="small text-muted">SJ: {{ $job->nota_produksi->no_surat_jalan ?? '-' }}</div>
                    </td>
                    <td>
                        <span class="fw-bold text-primary">{{ $job->produk_varian->produk->nama ?? 'N/A' }}</span>
                        <div class="small text-secondary">Varian: {{ $job->produk_varian->warna ?? '' }} | {{ $job->produk_varian->ukuran ?? '' }}</div>
                    </td>
                    <td class="text-center fw-bold fs-6">{{ number_format($job->jumlah) }} pcs</td>
                    <td class="text-center">
                        @if($job->status_pesanan == 'Selesai')
                            <span class="badge bg-success py-2 px-3">Selesai</span>
                        @elseif($job->status_pesanan == 'Selesai Parsial')
                            <span class="badge bg-warning text-dark py-2 px-3">Selesai Parsial</span>
                        @else
                            <span class="badge bg-info text-dark py-2 px-3">Diproduksi</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group gap-1" role="group">
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('job_order.show', $job->id) }}" class="btn btn-sm btn-outline-info fw-bold text-nowrap" style="width: 110px;" title="Lihat Evaluasi Perhitungan HPP">
                                <i class="fas fa-calculator"></i> Audit HPP
                            </a>
                            @endif

                            @if($job->status_pesanan != 'Selesai')
                            <a href="{{ route('nota_produksi.terima_hasil', ['job_id' => $job->id]) }}" class="btn btn-sm btn-success fw-bold text-nowrap" style="width: 110px;">
                                <i class="fas fa-check-circle"></i> Input QC
                            </a>
                            @else
                            <button class="btn btn-sm btn-light text-muted border fw-bold text-nowrap" style="width: 110px;" disabled>
                                <i class="fas fa-lock"></i> Lock
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">Belum ada Job Order yang berjalan saat ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection