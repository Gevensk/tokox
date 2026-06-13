@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Form Edit Supplier</h5>
            </div>
            <div class="card-body">
                <form action="/supplier/{{ $supplier->id }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Supplier</label>
                        <input type="text" name="nama" class="form-control" value="{{ $supplier->nama }}" placeholder="Contoh: PT. Maju Jaya" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap..." required>{{ $supplier->alamat }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nomor HP</label>
                        <input type="tel" name="no_hp" class="form-control" value="{{ $supplier->no_hp }}" placeholder="Contoh: 0812xxxxxxxx" required>
                    </div>

                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="/supplier" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Data</button>
                    </div>
                </form>        
            </div>
        </div>
    </div>
</div>
@endsection