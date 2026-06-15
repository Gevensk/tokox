@extends('layouts.app') 

@section('content') 
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between">
        <h5 class="mb-0">Daftar Customer</h5>
        <a href="/customer/create" class="btn btn-light btn-sm">+ Tambah</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>No HP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customer as $s)
                <tr>
                    <td>{{ $s->nama }}</td>
                    <td>{{ $s->alamat }}</td>
                    <td>{{ $s->no_hp }}</td>
                    <td>
                        <a href="/customer/{{ $s->id }}/edit" class="btn btn-warning btn-sm">Edit</a>
                        <form action="/customer/{{ $s->id }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection