@extends('layouts.app')

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between">
        <h5 class="mb-0">Daftar User</h5>
        <a href="/users/create" class="btn btn-light btn-sm">+ Tambah</a>
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
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        {{ $user->name }}
                        @if($user->id === Auth::id())
                            <span class="badge bg-secondary ms-1">Akun Anda</span>
                        @endif
                    </td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email ?? '-' }}</td>
                    <td>
                        @if($user->role === 'admin')
                            <span class="badge bg-success">Administrator</span>
                        @else
                            <span class="badge bg-info text-dark">Staff Produksi</span>
                        @endif
                    </td>
                    <td>
                        <a href="/users/{{ $user->id }}/edit" class="btn btn-warning btn-sm">Edit</a>
                        
                        @if($user->id !== Auth::id())
                        <form action="/users/{{ $user->id }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">Hapus</button>
                        </form>
                        @else
                        <button class="btn btn-secondary btn-sm" disabled title="Tidak bisa menghapus diri sendiri">Hapus</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">Belum ada user terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
