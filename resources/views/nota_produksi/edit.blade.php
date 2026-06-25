@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Form Edit Nota Produksi (Koreksi)</h5>
                <a href="{{ route('nota_production.index') }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
            <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('nota_production.update', $nota->id) }}" method="POST">
                @csrf
                @method('PUT')

                <h5 class="text-secondary border-bottom pb-2 mb-3">Informasi Utama Nota</h5>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold text-dark">Pilih Penjahit / PIC Produksi</label>
                        <select name="penjahit_id" class="form-select" required>
                            <option value="">-- Pilih Penjahit --</option>
                            @foreach($penjahits as $p)
                                <option value="{{ $p->id }}" {{ $nota->penjahit_id == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama }} ({{ $p->alamat }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold text-dark">Biaya Kirim Bahan (Ongkir)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">Rp</span>
                            <input type="number" name="ongkir_kirim_bahan" class="form-control" value="{{ (int)$nota->ongkir_kirim_bahan }}" min="0" required>
                        </div>
                        <small class="text-muted small">Biaya transportasi pengiriman bahan baku ke penjahit</small>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mb-3">👕 Daftar Pesanan Pakaian (Job Order)</h5>
                
                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-bordered align-middle mb-0" style="min-width: 1000px;">
                        <thead class="table-dark text-nowrap">
                            <tr>
                                <th style="width: 15%;">No. Order Induk</th>
                                <th style="width: 25%;">Nama Produk & Detail Varian</th>
                                <th style="width: 10%; text-align: center;">Qty (Pcs)</th>
                                <th style="width: 25%;">Biaya Jasa / Upah Penjahit</th>
                                <th style="width: 25%;">Estimasi Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nota->jobOrders as $job)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace fs-6">{{ $job->nota_order->no_order ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark d-block mb-1">{{ $job->produk_varian->produk->nama ?? 'Produk' }}</span>
                                        <small class="text-secondary bg-light px-2 py-1 rounded border d-inline-block">
                                            Varian: {{ $job->produk_varian->warna }} - {{ $job->produk_varian->ukuran }}
                                        </small>
                                    </td>
                                    <td class="fw-bold text-center fs-5 text-primary">{{ $job->jumlah }}</td>
                                    
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-muted small">Rp</span>
                                            <input type="number" name="biaya_jasa[{{ $job->id }}]" class="form-control" placeholder="Upah per pcs" min="0" required value="{{ old('biaya_jasa.' . $job->id, (int)$job->biaya_jasa_satuan) }}">
                                        </div>
                                        <small class="text-muted" style="font-size: 0.75rem;">*Tarif jahit per pcs</small>
                                    </td>

                                    <td>
                                        <input type="datetime-local" name="estimasi_selesai[{{ $job->id }}]" class="form-control form-control-sm" required value="{{ old('estimasi_selesai.' . $job->id, $job->estimasi_selesai ? date('Y-m-d\TH:i', strtotime($job->estimasi_selesai)) : '') }}">
                                        <small class="text-muted" style="font-size: 0.75rem;">*Tanggal perkiraan selesai</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-start gap-2 mt-4">
                    <a href="{{ route('nota_production.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>

        </div>
    </div>
</div>
</div>
@endsection
