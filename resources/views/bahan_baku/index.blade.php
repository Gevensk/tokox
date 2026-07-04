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
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Satuan</th>
                    <th>Stok Total</th>
                    @if(Auth::user()->isAdmin())
                    <th>Harga Rata-Rata</th>
                    @endif
                    <th>Last Updated</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bahan_baku as $d)
                <tr>
                    <td><strong>{{ $d->nama }}</strong></td>
                    <td>{{ $d->satuan }}</td>
                    <td>{{ (float)$d->stok_total }}</td>   
                    @if(Auth::user()->isAdmin())
                    <td>Rp {{ number_format($d->harga_rata_rata ?? 0, 2, ',', '.') }}</td>
                    @endif
                    <td>{{$d->updated_at}}</td>
                    <td>
                        <a href="{{ route('bahan_baku.kartu_stok', $d->id) }}" class="btn btn-outline-info btn-sm">
                            📋 Kartu Stok
                        </a>
                        @if(Auth::user()->isAdmin())
                        <a href="/bahan_baku/{{ $d->id }}/edit" class="btn btn-warning btn-sm">Edit</a>
                        <form action="/bahan_baku/{{ $d->id }}" method="POST" class="d-inline">
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