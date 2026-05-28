@extends('layouts.app') 

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h3>Detail Produk: {{ $produk->nama }}</h3>
            <p>Satuan: {{ $produk->satuan }}</p>
            <a href="{{ url('/produk') }}" class="btn btn-secondary btn-sm mb-3">Kembali</a>
            <hr>

            <div class="card mb-4">
                <div class="card-header">Tambah Varian Baru</div>
                
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <div class="card-body">
                      <form action="{{ route('produk_varian.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Warna</label>
                                <input type="text" name="warna" class="form-control" placeholder="Contoh: Merah">
                            </div>
                            <div class="col-md-2">
                                <label>Ukuran</label>
                                <input type="text" name="ukuran" class="form-control" placeholder="Contoh: XL">
                            </div>
                            <div class="col-md-3 mt-4">
                                <button type="submit" class="btn btn-primary">Simpan Varian</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Daftar Varian Produk</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Warna</th>
                                <th>Ukuran</th>
                                <th>Stok</th>
                                <th>Harga Modal (Avg)</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produk->varians as $v)
                            <tr>
                                <td>{{ $v->warna ?? '-' }}</td>
                                <td>{{ $v->ukuran ?? '-' }}</td>
                                <td>{{ $v->stok_tersedia }}</td>
                                <td>Rp {{ number_format($v->harga_avg_produk, 0, ',', '.') }}</td>
                                <td>
                                    @if($v->is_defect)
                                        <span class="badge bg-danger">Defect</span>
                                    @else
                                        <span class="badge bg-success">Normal</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Tombol Hapus Varian --}}
                                    <form action="{{ url('/produk_varian/'.$v->id) }}" method="POST" onsubmit="return confirm('Hapus varian ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada varian.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection