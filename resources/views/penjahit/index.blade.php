@extends('layouts.app') {{-- MEMANGGIL BINGKAI TADI --}}

@section('content') {{-- MEMASUKKAN ISI KE DALAM @yield('content') --}}
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between">
        <h5 class="mb-0">Daftar Penjahit</h5>
        @if(Auth::user()->isAdmin())
        <a href="/penjahit/create" class="btn btn-light btn-sm">+ Tambah</a>
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
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>No HP</th>
                    @if(Auth::user()->isAdmin())
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($penjahit as $s)
                <tr>
                    <td>{{ $s->nama }}</td>
                    <td>{{ $s->alamat }}</td>
                    <td>{{ $s->no_hp }}</td>
                    @if(Auth::user()->isAdmin())
                    <td>
                        <a href="/penjahit/{{ $s->id }}/edit" class="btn btn-warning btn-sm">Edit</a>
                        <form action="/penjahit/{{ $s->id }}" method="POST" class="d-inline">
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