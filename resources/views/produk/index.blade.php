@extends('layouts.app')

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between">
        <h5 class="mb-0">Daftar Produk</h5>
        @if(Auth::user()->isAdmin())
        <a href="/produk/create" class="btn btn-light btn-sm">+ Tambah</a>
        @endif
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width: 80px;">Foto</th>
                    <th>Nama</th>
                    <th>Satuan</th>
                    <th>Tipe Produk</th>
                    <th>Last Updated</th>
                    <th>DetailVarian</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($produk as $d)
                <tr>
                    <td>
                        @if($d->foto)
                            <img src="{{ asset('storage/produk_katalog/' . $d->foto) }}" alt="Foto" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                        @else
                            <div class="bg-light border text-center text-muted rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 0.72rem;">
                                No Foto
                            </div>
                        @endif
                    </td>
                    <td>{{ $d->nama }}</td>
                    <td>{{ $d->satuan }}</td>
                    <td>
                    @if($d->customer)
                        <span class="badge bg-primary">Custom: {{ $d->customer->nama }}</span>
                    @else
                        <span class="badge bg-secondary">Umum / Reguler</span>
                    @endif
                </td>
                    <td>{{$d->updated_at}}</td>
                    <td><a href="/produk/{{ $d->id }}" class="btn btn-info btn-sm text-white">Detail Varian</a></td>
                    <td>
                        
                        @if(Auth::user()->isAdmin())
                        <a href="/produk/{{ $d->id }}/edit" class="btn btn-warning btn-sm">Edit</a>
                        <form action="/produk/{{ $d->id }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection