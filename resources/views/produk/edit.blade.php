@extends('layouts.app')

@section('content')
    {{-- SEMUA KODE BOOTSTRAP HARUS DI SINI --}}
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4>Edit Produk</h4>
            </div>
            <div class="card-body">
                <form action="/produk/{{ $produk->id }}" method="POST">
                    @csrf
                    @method('PUT') 
                    
                    <label>Nama:</label><br>
                    <input type="text" name="nama" value="{{ $produk->nama }}" required><br><br>

                    <label>Satuan:</label><br>
                    <input type="text" name="satuan" value="{{ $produk->satuan }}" required><br><br>

                    <div class="d-inline btn-sm">
                        <a href="/produk" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-success ">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
