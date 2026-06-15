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
                <div class="card-header fw-bold">Tambah Varian Baru Sekaligus</div>
                
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="card-body">
                    {{-- Form ini mengirimkan data array ukuran sekaligus --}}
                    <form action="{{ route('produk_varian.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                        
                        <div class="row align-items-center">
                            {{-- Input Warna --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Warna</label>
                                <input type="text" name="warna" class="form-control" placeholder="Contoh: Hitam" required>
                            </div>
                            
                            {{-- Pilihan Banyak Ukuran Sekaligus menggunakan Checkbox --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold d-block">Pilih Ukuran yang Tersedia</label>
                                <div class="d-flex gap-3 flex-wrap mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ukuran[]" value="S" id="sizeS" checked>
                                        <label class="form-check-label" for="sizeS">S</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ukuran[]" value="M" id="sizeM" checked>
                                        <label class="form-check-label" for="sizeM">M</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ukuran[]" value="L" id="sizeL" checked>
                                        <label class="form-check-label" for="sizeL">L</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ukuran[]" value="XL" id="sizeXL" checked>
                                        <label class="form-check-label" for="sizeXL">XL</label>
                                    </div>                                 
                                </div>
                            </div>
                            
                            {{-- Tombol Eksekusi Masal --}}
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100 mt-3">Simpan Semua Varian</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Bagian Tabel Daftar Varian Produk --}}
            <div class="card">
                <div class="card-header fw-bold">Daftar Varian Produk</div>
                <div class="card-body">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Warna</th>
                                <th>Ukuran</th>
                                <th>Stok Tersedia (Accepted)</th>
                                <th>Stok Defect</th>
                                <th>Harga Modal (Avg)</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produk->varians as $v)
                            <tr>
                                <td>{{ $v->warna ?? '-' }}</td>
                                <td class="{{ $v->ukuran == 'Custom Size' ? 'fw-bold text-primary' : '' }}">{{ $v->ukuran ?? '-' }}</td>
                                
                                {{-- Kolom Stok Tersedia / Bagus --}}
                                <td>
                                    <span class="badge bg-success fs-6">{{ $v->stok_tersedia }} {{ $produk->satuan }}</span>
                                </td>

                                {{-- Kolom Stok Defect --}}
                                <td>
                                    @if($v->stok_defect > 0)
                                        <span class="badge bg-warning text-dark fs-6">{{ $v->stok_defect }} {{ $produk->satuan }}</span>
                                    @else
                                        <span class="text-muted">{{ $v->stok_defect }}</span>
                                    @endif
                                </td>

                                <td>Rp {{ number_format($v->harga_avg_produk, 0, ',', '.') }}</td>
                                <td>
                                    <form action="{{ url('/produk_varian/'.$v->id) }}" method="POST" onsubmit="return confirm('Hapus varian ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada varian untuk produk ini.</td>
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