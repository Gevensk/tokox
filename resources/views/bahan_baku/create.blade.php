@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Form Tambah Bahan Baku</h5>
            </div>
            <div class="card-body">
                <form action="/bahan_baku" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Bahan</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Kain Katun" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" placeholder="Contoh: Roll / Meter" required>
                    </div>

                    <div class="d-inline">
                        <a href="/bahan_baku" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-success">Daftarkan Bahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection