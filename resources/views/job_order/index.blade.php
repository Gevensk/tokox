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
                    <th style="width: 1%;">Aksi / Evaluasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobOrders as $job)
                <tr>
                    <td class="text-center font-weight-bold text-secondary">#{{ $job->id }}</td>
                    
                    <td>
                        @if($job->is_custom == 1 && !empty($job->foto_desain))
                            <img src="{{ asset('storage/desain_order/' . $job->foto_desain) }}" 
                                 class="img-thumbnail rounded border border-warning" 
                                 style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#imgModal{{ $job->id }}"
                                 title="Desain Custom (Klik untuk memperbesar)">
                            
                            <!-- Modal preview custom -->
                            <div class="modal fade" id="imgModal{{ $job->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-warning text-dark py-2">
                                            <h6 class="modal-title fw-bold mb-0">Desain Custom - Job #{{ $job->id }}</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center p-2 bg-light">
                                            <img src="{{ asset('storage/desain_order/' . $job->foto_desain) }}" class="img-fluid rounded" style="max-height: 75vh; object-fit: contain;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif(!empty($job->produk_varian->produk->foto))
                            <img src="{{ asset('storage/produk_katalog/' . $job->produk_varian->produk->foto) }}" 
                                 class="img-thumbnail rounded" 
                                 style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#imgModal{{ $job->id }}"
                                 title="Katalog Produk (Klik untuk memperbesar)">

                            <!-- Modal preview katalog -->
                            <div class="modal fade" id="imgModal{{ $job->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-dark text-white py-2">
                                            <h6 class="modal-title fw-bold mb-0">Foto Produk Katalog - {{ $job->produk_varian->produk->nama }}</h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center p-2 bg-light">
                                            <img src="{{ asset('storage/produk_katalog/' . $job->produk_varian->produk->foto) }}" class="img-fluid rounded" style="max-height: 75vh; object-fit: contain;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-light rounded text-center text-muted d-flex align-items-center justify-content-center border" style="width: 50px; height: 50px; font-size: 0.72rem;" title="Tidak ada gambar">No Img</div>
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
                    <td class="text-nowrap" style="width: 1%;">
                        <div class="d-flex justify-content-start align-items-center gap-1">
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('job_order.show', $job->id) }}" class="btn btn-info btn-sm text-white" title="Lihat Evaluasi Perhitungan HPP">
                                Rincian HPP
                            </a>
                            @endif

                            @if($job->status_pesanan != 'Selesai')
                            <a href="{{ route('nota_produksi.terima_hasil', ['job_id' => $job->id]) }}" class="btn btn-success btn-sm">
                                Input QC
                            </a>
                            @else
                            <span class="text-success small fw-semibold ms-1">✓ Selesai</span>
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