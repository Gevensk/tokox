@extends('layouts.app')

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between">
        <h5 class="mb-0">Daftar Bahan Baku</h5>
        @if(Auth::user()->isAdmin())
        <a href="/bahan_baku/create" class="btn btn-light btn-sm">+ Tambah</a>
        @endif
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
                    @if(Auth::user()->isAdmin())
                    <th>Harga Rata-Rata</th>
                    @endif
                    <th>Last Updated</th>
                    @if(Auth::user()->isAdmin())
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($bahan_baku as $d)
                <tr>
                    <td>{{ $d->nama }}</td>
                    <td>{{ $d->satuan }}</td>
                    <td>{{ (float)$d->stok_total }}</td>   
                    @if(Auth::user()->isAdmin())
                    <td>Rp {{ number_format($d->harga_rata_rata ?? 0, 0, ',', '.') }}</td>
                    @endif
                    <td>{{$d->updated_at}}</td>
                    @if(Auth::user()->isAdmin())
                    <td>
                        <a href="/bahan_baku/{{ $d->id }}/edit" class="btn btn-warning btn-sm">Edit</a>
                        <form action="/bahan_baku/{{ $d->id }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">Hapus</button>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection