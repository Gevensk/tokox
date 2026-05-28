@extends('layouts.app')

@section('content')
    {{-- SEMUA KODE BOOTSTRAP HARUS DI SINI --}}
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4>Edit Bahan Baku</h4>
            </div>
            <div class="card-body">
                <form action="/bahan_baku/{{ $bahan_baku->id }}" method="POST">
                    @csrf
                    @method('PUT') 
                    
                    <label>Nama:</label><br>
                    <input type="text" name="nama" value="{{ $bahan_baku->nama }}" required><br><br>

                    <label>Satuan:</label><br>
                    <input type="text" name="satuan" value="{{ $bahan_baku->satuan }}" required><br><br>

                    <div class="d-inline btn-sm">
                        <a href="/bahan_baku" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-success ">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
