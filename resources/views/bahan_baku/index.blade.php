@extends('layouts.app')

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between">
        <h5 class="mb-0">Daftar Bahan Baku</h5>
        <a href="/bahan_baku/create" class="btn btn-light btn-sm">+ Tambah</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Satuan</th>
                    <th>Stok Total</th>
                    <th>Harga Rata-Rata</th>
                    <th>Last Updated</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bahan_baku as $d)
                <tr>
                    <td>{{ $d->nama }}</td>
                    <td>{{ $d->satuan }}</td>
                    <td>{{ $d->stok_total }}</td>   
                    <td>Rp {{ number_format($d->harga_rata_rata ?? 0, 0, ',', '.') }}</td>
                    <td>{{$d->updated_at}}</td>
                    <td>
                        <a href="/bahan_baku/{{ $d->id }}/edit" class="btn btn-warning btn-sm">Edit</a>
                        <form action="/bahan_baku/{{ $d->id }}" method="POST" class="d-inline">
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