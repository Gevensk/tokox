@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Form Tambah Supplier</h5>
            </div>
            <div class="card-body">
                <form action="/supplier" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Supplier</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: PT. Maju Jaya" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nomor HP</label>
                        <input type="tel" name="no_hp" class="form-control" placeholder="Contoh: 0812xxxxxxxx" required>
                    </div>

                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="/supplier" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-success">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection