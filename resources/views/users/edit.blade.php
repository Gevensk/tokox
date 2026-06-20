@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Form Edit User</h5>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="/users/{{ $user->id }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Nama -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <!-- Username -->
                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold">Username</label>
                        <input type="text" name="username" id="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email (Opsional)</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}">
                    </div>

                    <!-- Role -->
                    <div class="mb-3">
                        <label for="role" class="form-label fw-bold">Level Akses (Role)</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Staff Produksi</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                    </div>

                    <div class="p-3 bg-light rounded border mb-4">
                        <span class="fw-bold text-dark d-block mb-1">Ganti Password (Opsional)</span>
                        <small class="text-muted d-block mb-3">Kosongkan kolom di bawah ini jika tidak ingin mengubah password.</small>
                        
                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label text-secondary fw-bold">Password Baru</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter">
                        </div>

                        <!-- Password Confirmation -->
                        <div class="mb-0">
                            <label for="password_confirmation" class="form-label text-secondary fw-bold">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ketik ulang password baru">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="/users" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
