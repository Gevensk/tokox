@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            {{-- Alert Notifikasi Sukses / Gagal --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal Validasi!</strong> Periksa kembali input Anda:
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Form Quality Control & Terima Hasil Produksi</h5>
                    <span class="badge bg-light text-primary">ID Job: #{{ $job->id }}</span>
                </div>
                
                <div class="card-body">
                    {{-- Informasi Singkat Job Order --}}
                    <div class="row mb-4 p-3 bg-light rounded mx-1">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nota Order Induk:</small>
                            <strong>{{ $job->nota_order->no_order ?? '-' }}</strong>
                            
                            <small class="text-muted d-block mt-2">Produk & Varian:</small>
                            <strong>{{ $job->produk_varian->produk->nama ?? 'Produk Tidak Diketahui' }}</strong> 
                            <span class="badge bg-secondary">
                                {{ $job->produk_varian->warna ?? '-' }} - {{ $job->produk_varian->ukuran ?? '-' }}
                            </span>
                        </div>
                        <div class="col-md-6 border-start">
                            <small class="text-muted d-block">Target Total Produksi:</small>
                            <span class="fs-5 text-dark fw-bold">{{ $job->jumlah }} Pcs</span>
                            
                            <small class="text-muted d-block mt-2">Sudah Masuk QC Sebelumnya:</small>
                            <span class="text-info fw-bold">{{ $totalTerQCSebelumnya ?? 0 }} Pcs</span>

                            <small class="text-muted d-block mt-2">Maksimal Sisa Tanggungan:</small>
                            <span class="text-danger fw-bold fs-5" id="sisa-tanggungan">{{ $sisaTanggungan }} Pcs</span>
                        </div>
                    </div>

                    {{-- Form Input QC Hasil Produksi --}}
                    <form action="{{ route('hasil_produksi.store') }}" method="POST">
                        @csrf
                        
                        {{-- Hidden Input ID Job Order --}}
                        <input type="hidden" name="job_order_id" value="{{ $job->id }}">

                        <div class="row">
                            {{-- Qty Lolos QC (Accepted) --}}
                            <div class="col-md-4 mb-3">
                                <label for="qty_accepted" class="form-label text-success fw-bold">1. Qty Accepted (Lolos QC)</label>
                                <input type="number" name="qty_accepted" id="qty_accepted" class="form-control form-control-lg border-success input-hitung" value="{{ old('qty_accepted', 0) }}" min="0" required>
                                <small class="text-muted">Akan menambah stok gudang baju jadi.</small>
                            </div>

                            {{-- Qty Defect (Bisa Diperbaiki) --}}
                            <div class="col-md-4 mb-3">
                                <label for="qty_rejected_defect" class="form-label text-warning fw-bold">2. Qty Defect (Rework)</label>
                                <input type="number" name="qty_rejected_defect" id="qty_rejected_defect" class="form-control form-control-lg border-warning input-hitung" value="{{ old('qty_rejected_defect', 0) }}" min="0" required>
                                <small class="text-muted">Baju reject yang bisa diperbaiki/dijahit ulang.</small>
                            </div>

                            {{-- Qty Rejected (Rusak Total / Buang) --}}
                            <div class="col-md-4 mb-3">
                                <label for="qty_rejected" class="form-label text-danger fw-bold">3. Qty Rejected (Gagal)</label>
                                <input type="number" name="qty_rejected" id="qty_rejected" class="form-control form-control-lg border-danger input-hitung" value="{{ old('qty_rejected', 0) }}" min="0" required>
                                <small class="text-muted">Rusak parah, kain bolong, atau tidak bisa dipakai.</small>
                            </div>
                        </div>

                        {{-- Total Kalkulasi Realtime via JS --}}
                        <div class="alert alert-secondary d-flex justify-content-between align-items-center my-2 py-2">
                            <span>Total yang akan diinput sekarang:</span>
                            <strong class="fs-5" id="total-input-sekarang">0 Pcs</strong>
                        </div>

                        <hr>

                        <div class="row">
                            {{-- Ongkir Pengiriman Hasil Jadi --}}
                            <div class="col-md-6 mb-3">
                                <label for="ongkir_hasil_produksi" class="form-label fw-bold">Ongkos Kirim Balik Setoran (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="ongkir_hasil_produksi" id="ongkir_hasil_produksi" class="form-control" value="{{ old('ongkir_hasil_produksi', 0) }}" min="0" required>
                                </div>
                                <small class="text-muted">Biaya logistik pengiriman baju dari penjahit ke gudang Anda.</small>
                            </div>

                            {{-- Catatan QC --}}
                            <div class="col-md-6 mb-3">
                                <label for="catatan_qc" class="form-label fw-bold">Catatan Tim QC</label>
                                <input type="text" name="catatan_qc" id="catatan_qc" class="form-control" placeholder="Contoh: Jahitan lengan kiri agak renggang" value="{{ old('catatan_qc') }}" maxlength="45">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('job_order.index') }}" class="btn btn-light border">Kembali</a>
                            <button type="submit" id="btn-submit" class="btn btn-primary px-4 fw-bold">Simpan Setoran / QC</button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- JavaScript Real-Time Validation & Calculation --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    const inputs = document.querySelectorAll('.input-hitung');
    const totalDisplay = document.getElementById('total-input-sekarang');
    const btnSubmit = document.getElementById('btn-submit');
    
    let sisaTanggungan = parseInt("{{ $sisaTanggungan }}");
    if (isNaN(sisaTanggungan)) {
        sisaTanggungan = parseInt("{{ $job->jumlah ?? 0 }}") || 0;
    }

    function hitungTotal() {
        let total = 0;
        inputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });
        
        totalDisplay.innerText = total + " Pcs";

        // Jika kuota sisa tanggungan dari database sudah 0 (Pekerjaan Selesai)
        if (sisaTanggungan <= 0) {
            totalDisplay.innerText = "0 Pcs (Sudah Terpenuhi)";
            btnSubmit.disabled = true;
            btnSubmit.className = "btn btn-success px-4 fw-bold";
            btnSubmit.innerText = "Job Order Selesai";
            inputs.forEach(input => input.disabled = true);
            return;
        }

        // Validasi pembatasan inputan user secara realtime
        if (total > sisaTanggungan) {
            totalDisplay.classList.remove('text-dark');
            totalDisplay.classList.add('text-danger', 'fw-bold');
            btnSubmit.disabled = true; 
            btnSubmit.innerText = "Melebihi Sisa Kuota (" + sisaTanggungan + " Pcs)!";
        } else if (total <= 0) {
            btnSubmit.disabled = true;
            btnSubmit.innerText = "Jumlah Setoran Tidak Boleh 0";
        } else {
            totalDisplay.classList.remove('text-danger');
            totalDisplay.classList.add('text-dark');
            btnSubmit.disabled = false; 
            
            // Perubahan teks tombol dinamis mengikuti target hitungan
            if (total === sisaTanggungan) {
                btnSubmit.innerText = "Simpan & Selesaikan Job Order";
            } else {
                btnSubmit.innerText = "Simpan Setoran / QC (Parsial)";
            }
        }
    }

    inputs.forEach(input => {
        input.addEventListener('input', hitungTotal);
    });

    // Jalankan kalkulasi pertama kali saat halaman dimuat
    hitungTotal();
});
</script>
@endsection