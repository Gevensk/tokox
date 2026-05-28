@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Edit Supplier</h4>
        </div>
        <div class="card-body">
            <form action="/supplier/{{ $supplier->id }}" method="POST">
                @csrf
                @method('PUT') {{-- WAJIB: Laravel butuh ini untuk update data --}}
                
                <label>Nama:</label><br>
                <input type="text" name="nama" value="{{ $supplier->nama }}" required><br><br>

                <label>Alamat:</label><br>
                <input type="text" name="alamat" value="{{ $supplier->alamat }}" required><br><br>

                <label>No HP:</label><br>
                <input type="text" name="no_hp" value="{{ $supplier->no_hp }}" required><br><br>

                <div class="d-inline btn-sm">
                    <a href="/supplier" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Update Data</button>
                </div>
            </form>        
        </div>
    </div>
</div>
@endsection

