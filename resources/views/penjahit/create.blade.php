@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Form Tambah Penjahit</h5>
                </div>
                <div class="card-body">
                    <form action="/penjahit" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Penjahit</label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: Hanna" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor HP</label>
                            <input type="number" name="no_hp" class="form-control" placeholder="0812xxxx" required>
                        </div>
                        <div class="d-inline">
                            <a href="/penjahit" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-success">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection