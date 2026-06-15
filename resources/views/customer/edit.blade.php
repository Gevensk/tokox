@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Form Edit Customer</h5>
            </div>
            <div class="card-body">
                <form action="/customer/{{ $customer->id }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="nama">Nama</label>
                        <input type="text" name="nama" id="nama" class="form-control" value="{{ $customer->nama }}" placeholder="Masukkan nama customer" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="alamat">Alamat</label>
                        <input type="text" name="alamat" id="alamat" class="form-control" value="{{ $customer->alamat }}" placeholder="Masukkan alamat lengkap" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="no_hp">No HP</label>
                        <input type="text" name="no_hp" id="no_hp" class="form-control" value="{{ $customer->no_hp }}" placeholder="Contoh: 08123456789" required>
                    </div>

                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="/customer" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Data</button>
                    </div>
                </form>        
            </div>
        </div>
    </div>
</div>
@endsection