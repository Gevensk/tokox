@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Form Edit Penjahit</h5>
            </div>
            <div class="card-body">
                <form action="/penjahit/{{ $penjahit->id }}" method="POST">
                    @csrf
                    @method('PUT') 
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama</label>
                        <input type="text" name="nama" class="form-control" value="{{ $penjahit->nama }}" placeholder="Masukkan nama penjahit" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Alamat</label>
                        <input type="text" name="alamat" class="form-control" value="{{ $penjahit->alamat }}" placeholder="Masukkan alamat lengkap" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">No HP</label>
                        <input type="text" name="no_hp" class="form-control" value="{{ $penjahit->no_hp }}" placeholder="Contoh: 08123456789" required>
                    </div>

                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="/penjahit" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection