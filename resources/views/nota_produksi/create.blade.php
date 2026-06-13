@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4>Buat Nota Produksi Baru (Mulai Kerja Konveksi)</h4>
        </div>
        <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('nota_production.store') }}" method="POST">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Pilih Penjahit / PIC Produksi</label>
                        <select name="penjahit_id" class="form-select" required>
                            <option value="">-- Pilih Penjahit --</option>
                            @foreach($penjahits as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->alamat }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Biaya Kirim Bahan (Ongkir)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="ongkir_kirim_bahan" class="form-control" value="0" min="0" required>
                        </div>
                        <small class="text-muted">Biaya transportasi pengiriman bahan baku ke penjahit</small>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mb-3">Pilih Antrean Job Order Yang Mau Dikerjakan Hari Ini</h5>
                
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">Pilih</th>
                                <th style="width: 15%;">No. Order Induk</th>
                                <th style="width: 25%;">Nama Produk & Varian</th>
                                <th style="width: 10%;">Qty (Pcs)</th>
                                <th style="width: 10%;">Tipe Custom</th>
                                <th style="width: 20%;">Spesifikasi / Catatan Kustomisasi</th>
                                <th style="width: 25%;">Input Bahan Ekstra</th> </tr>
                        </thead>
                        <tbody>
                            @forelse($jobOrders as $job)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="job_order_ids[]" value="{{ $job->id }}" class="form-check-input style-lg">
                                    </td>
                                    <td><strong>{{ $job->nota_order->no_order }}</strong></td>
                                    <td>{{ $job->produk_varian->produk->nama ?? 'Produk' }}</td>
                                    <td class="fw-bold text-center">{{ $job->jumlah }}</td>
                                    <td>
                                        <span class="badge {{ $job->is_custom ? 'bg-danger' : 'bg-success' }}">
                                            {{ $job->is_custom ? 'Custom' : 'Standar' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($job->is_custom)
                                            <small class="text-danger">Ket: {{ $job->catatan_custom ?? 'Tidak ada catatan' }}</small>
                                        @else
                                            <span class="text-muted">Standar</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($job->is_custom)
                                            <div class="row g-1">
                                                <div class="col-7">
                                                    <select name="ekstra_bahan_id[{{ $job->id }}]" class="form-select form-select-sm">
                                                        <option value="">-- Pilih Bahan --</option>
                                                        @foreach($allBahanBaku as $b)
                                                            <option value="{{ $b->id }}">{{ $b->nama }} (Stok: {{ (float)$b->stok_total }} {{ $b->satuan }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-5">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step="0.01" min="0" name="ekstra_jumlah[{{ $job->id }}]" class="form-control" placeholder="Qty">
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small">Tidak butuh ekstra</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-dark px-5">Naikkan Cetak / Mulai Produksi</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection