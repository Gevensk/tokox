@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0"><i class="bi bi-scissors"></i> Buat Nota Produksi Baru (Mulai Kerja Konveksi)</h4>
        </div>
        <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('nota_production.store') }}" method="POST">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Pilih Penjahit / PIC Produksi</label>
                        <select name="penjahit_id" class="form-select" required>
                            <option value="">-- Pilih Penjahit --</option>
                            @foreach($penjahits as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->alamat }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Biaya Kirim Bahan (Ongkir)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="ongkir_kirim_bahan" class="form-control" value="0" min="0" required>
                        </div>
                        <small class="text-muted small">Biaya transportasi pengiriman bahan baku ke penjahit</small>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mb-3">Pilih Antrean Job Order Yang Mau Dikerjakan Hari Ini</h5>
                
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light small fw-bold">
                            <tr>
                                <th style="width: 5%; text-align: center;">Pilih</th>
                                <th style="width: 12%;">No. Order Induk</th>
                                <th style="width: 25%;">Nama Produk & Detail Varian</th>
                                <th style="width: 8%; text-align: center;">Qty (Pcs)</th>
                                <th style="width: 15%;">Biaya Jasa / Upah Penjahit</th>
                                <th style="width: 10%; text-align: center;">Tipe Alokasi</th>
                                <th style="width: 15%;">Spesifikasi / Catatan</th>
                                <th style="width: 15%;">Input Bahan Ekstra</th> 
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jobOrders as $job)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="job_order_ids[]" value="{{ $job->id }}" class="form-check-input" style="transform: scale(1.2);">
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace fs-6">{{ $job->nota_order->no_order }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark d-block mb-1">{{ $job->produk_varian->produk->nama ?? 'Produk' }}</span>
                                        <small class="text-secondary bg-light px-2 py-1 rounded border d-inline-block">
                                            Varian: {{ $job->produk_varian->warna }} - {{ $job->produk_varian->ukuran }}
                                        </small>
                                    </td>
                                    <td class="fw-bold text-center fs-5 text-primary">{{ $job->jumlah }}</td>
                                    
                                    {{-- 🛠️ KOLOM BIAYA JASA YANG DIKEMBALIKAN & DI-RENAME 🛠️ --}}
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="biaya_jasa[{{ $job->id }}]" class="form-control" placeholder="Upah per pcs" min="0" required>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.75rem;">*Isi tarif ongkos jahit per satuan baju</small>
                                    </td>

                                    <td class="text-center">
                                        @if($job->is_custom)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1.5 w-100small">
                                                <i class="bi bi-person-fill"></i> Custom Order
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1.5 w-100small">
                                                <i class="bi bi-shop"></i> Stok Toko
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($job->is_custom)
                                            <div class="mb-1 text-dark fw-semibold small" style="font-size: 0.8rem;">
                                                Pemesan: <span class="text-primary">{{ $job->nota_order->customer->nama ?? 'Pelanggan' }}</span>
                                            </div>
                                            <div class="p-1 border rounded bg-light-subtle text-danger" style="font-size: 0.75rem; line-height: 1.2;">
                                                <strong>Req:</strong> {{ $job->catatan_custom ?? 'Tidak ada catatan' }}
                                            </div>
                                        @else
                                            <span class="text-muted small italic">Standar Reguler</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($job->is_custom)
                                            <div class="row g-1">
                                                <div class="col-12 mb-1">
                                                    <select name="ekstra_bahan_id[{{ $job->id }}]" class="form-select form-select-sm" style="font-size: 0.8rem;">
                                                        <option value="">-- Pilih Bahan --</option>
                                                        @foreach($allBahanBaku as $b)
                                                            <option value="{{ $b->id }}">{{ $b->nama }} (Stok: {{ (float)$b->stok_total }} {{ $b->satuan }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step="0.01" min="0" name="ekstra_jumlah[{{ $job->id }}]" class="form-control" placeholder="Qty" style="font-size: 0.8rem;">
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small">Tidak butuh ekstra</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-exclamation-circle"></i> Tidak ada antrean job order aktif saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-dark px-5 py-2 fw-bold">
                        <i class="bi bi-printer"></i> Naikkan Cetak / Mulai Produksi
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection