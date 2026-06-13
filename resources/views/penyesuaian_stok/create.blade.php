@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="d-flex align-items-center mb-3">
                <a href="{{ route('penyesuaian_stok.index') }}" class="btn btn-outline-secondary me-3">
                    ⬅ Kembali
                </a>
                <h3 class="mb-0">Form Input Penyesuaian Stok</h3>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white fw-bold py-3">
                    📦 Form Stock Opname / Koreksi Stok Gudang
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('penyesuaian_stok.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="bahan_baku_id" class="form-label fw-bold">Pilih Bahan Baku <span class="text-danger">*</span></label>
                            <select name="bahan_baku_id" id="bahan_baku_id" class="form-select @error('bahan_baku_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kain / Aksesoris --</option>
                                @foreach($bahanBakus as $bahan)
                                    <option value="{{ $bahan->id }}" {{ old('bahan_baku_id') == $bahan->id ? 'selected' : '' }}>
                                        {{ $bahan->nama }} (Stok Saat Ini: {{ $bahan->stok_total }} {{ $bahan->satuan }})
                                    </option>
                                @endforeach
                            </select>
                            @error('bahan_baku_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipe" class="form-label fw-bold">Tipe Penyesuaian <span class="text-danger">*</span></label>
                                <select name="tipe" id="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                    <option value="in" {{ old('tipe') == 'in' ? 'selected' : '' }}>➕ Stok In (Fisik Lebih Banyak / Ketemu)</option>
                                    <option value="out" {{ old('tipe') == 'out' ? 'selected' : '' }}>➖ Stok Out (Rusak / Hilang / Selisih Kurang)</option>
                                </select>
                                @error('tipe')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="jumlah" class="form-label fw-bold">Jumlah Qty Selisih <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="jumlah" id="jumlah" class="form-control @error('jumlah') is-invalid @enderror" value="{{ old('jumlah') }}" placeholder="Contoh: 2.5 atau 10" required min="0.01">
                                <small class="text-muted">Gunakan titik (.) jika memasukkan angka desimal.</small>
                                @error('jumlah')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="keterangan" class="form-label fw-bold">Alasan Penyesuaian <span class="text-danger">*</span></label>
                            <textarea name="keterangan" id="keterangan" rows="3" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Contoh: Kain basah terkena bocoran atap di rak B, atau Salah hitung saat Stock Opname bulanan" required>{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold" onclick="return confirm('Apakah Anda yakin data koreksi stok ini sudah benar? Stok bahan baku utama akan langsung berubah.')">
                                💾 Simpan Penyesuaian Stok
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection