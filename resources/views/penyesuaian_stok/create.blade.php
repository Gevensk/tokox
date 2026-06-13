@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Form Input Penyesuaian Stok</h5>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('penyesuaian_stok.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="bahan_baku_id" class="form-label fw-bold">Pilih Bahan Baku</label>
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

                    <div class="mb-3">
                        <label for="tipe" class="form-label fw-bold">Tipe Penyesuaian</label>
                        <select name="tipe" id="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                            <option value="in" {{ old('tipe') == 'in' ? 'selected' : '' }}>Stok In (Fisik Lebih Banyak / Ketemu)</option>
                            <option value="out" {{ old('tipe') == 'out' ? 'selected' : '' }}>Stok Out (Rusak / Hilang / Selisih Kurang)</option>
                        </select>
                        @error('tipe')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="jumlah" class="form-label fw-bold">Jumlah Qty Selisih</label>
                        <input type="number" step="0.01" name="jumlah" id="jumlah" class="form-control @error('jumlah') is-invalid @enderror" value="{{ old('jumlah') }}" placeholder="Contoh: 2.5 atau 10" required min="0.01">
                        <div class="form-text text-muted">Gunakan titik (.) jika memasukkan angka desimal.</div>
                        @error('jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label fw-bold">Alasan Penyesuaian</label>
                        <textarea name="keterangan" id="keterangan" rows="3" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Contoh: Kain basah terkena bocoran atap di rak B, atau Salah hitung" required>{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="{{ route('penyesuaian_stok.index') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin data koreksi stok ini sudah benar? Stok bahan baku utama akan langsung berubah.')">
                            Simpan Penyesuaian Stok
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection