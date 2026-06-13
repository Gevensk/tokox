@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Form Edit Bahan Baku</h5>
            </div>
            <div class="card-body">
                <form action="/bahan_baku/{{ $bahan_baku->id }}" method="POST">
                    @csrf
                    @method('PUT') 
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Bahan</label>
                        <input type="text" name="nama" class="form-control" value="{{ $bahan_baku->nama }}" placeholder="Contoh: Kain Katun" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Satuan</label>
                        <input type="text" name="satuan" class="form-control" value="{{ $bahan_baku->satuan }}" placeholder="Contoh: Roll / Meter" required>
                    </div>

                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="/bahan_baku" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection