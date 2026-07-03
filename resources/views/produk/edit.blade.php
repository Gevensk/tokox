@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Edit Produk</h5>
            </div>
            <div class="card-body">
                <form action="/produk/{{ $produk->id }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="nama" class="form-control" value="{{ $produk->nama }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" value="{{ $produk->satuan }}" required>
                        <small class="text-muted">Contoh: Pcs, Set, Lusin</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Foto Katalog Produk</label>
                        @if($produk->foto)
                            <div class="mb-2">
                                <img src="{{ asset('storage/produk_katalog/' . $produk->foto) }}" alt="Foto Katalog" class="img-thumbnail" style="max-height: 120px; width: auto; display: block;">
                                <small class="text-muted">Foto saat ini</small>
                            </div>
                        @endif
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <small class="text-muted">Pilih file jika ingin mengganti foto. Maks: 2MB</small>
                    </div>



                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="/produk" class="btn btn-secondary px-4">Kembali</a>
                        <button type="submit" class="btn btn-primary px-4">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
