@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Form Tambah Produk</h5>
            </div>
            <div class="card-body">
                <form action="/produk" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Kaos Polos Merah" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" value="Pcs" placeholder="Pcs / Set / Lusin" required>
                        <small class="text-muted">Contoh: Pcs, Set, Lusin</small>
                    </div>



                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="/produk" class="btn btn-secondary px-4">Kembali</a>
                        <button type="submit" class="btn btn-success px-4">Daftarkan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection