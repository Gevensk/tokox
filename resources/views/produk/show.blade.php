@extends('layouts.app') 

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Detail Produk: {{ $produk->nama }}</h3>
                    <p class="text-muted mb-0">Satuan Hitung: <strong class="text-dark">{{ $produk->satuan }}</strong></p>
                </div>
                <a href="{{ url('/produk') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
            <hr>

            {{-- Pesan Alert --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Form Tambah Varian Masal --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-plus-circle-fill text-primary"></i> Tambah Varian Baru Sekaligus
                </div>
                <div class="card-body">
                    <form action="{{ route('produk_varian.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                        
                        <div class="row align-items-end">
                            {{-- Input Warna --}}
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label class="form-label fw-bold small text-secondary">Warna</label>
                                <input type="text" name="warna" class="form-control" placeholder="Contoh: Hitam" required>
                            </div>
                            
                            {{-- Pilihan Banyak Ukuran Sekaligus menggunakan Checkbox --}}
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-bold small text-secondary d-block">Pilih Ukuran yang Tersedia</label>
                                <div class="d-flex gap-3 flex-wrap p-2 border rounded bg-light">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ukuran[]" value="S" id="sizeS" checked>
                                        <label class="form-check-label fw-semibold" for="sizeS">S</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ukuran[]" value="M" id="sizeM" checked>
                                        <label class="form-check-label fw-semibold" for="sizeM">M</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ukuran[]" value="L" id="sizeL" checked>
                                        <label class="form-check-label fw-semibold" for="sizeL">L</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ukuran[]" value="XL" id="sizeXL" checked>
                                        <label class="form-check-label fw-semibold" for="sizeXL">XL</label>
                                    </div>                                 
                                </div>
                            </div>
                            
                            {{-- Tombol Eksekusi Masal --}}
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-save"></i> Simpan Semua Varian
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Bagian Tabel Daftar Varian Produk --}}
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-table"></i> Daftar Varian & Pemisahan Alokasi Kuantitas</span>
                    <span class="badge bg-secondary">Total Varian: {{ $varians->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light text-center small uppercase fw-bold">
                                <tr>
                                    <th class="py-3">Warna</th>
                                    <th class="py-3">Ukuran</th>
                                    <th class="py-3 text-success" width="200">Stok Toko (Ready Stock)</th>
                                    <th class="py-3 text-primary" width="200">Stok Custom (Pesanan)</th>
                                    <th class="py-3 text-warning" width="150">Stok Defect</th>
                                    <th class="py-3" width="180">Harga Modal (Avg)</th>
                                    <th class="py-3" width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($varians as $v)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $v->warna ?? '-' }}</td>
                                    <td class="text-center {{ $v->ukuran == 'Custom Size' ? 'fw-bold text-primary' : '' }}">
                                        {{ $v->ukuran ?? '-' }}
                                    </td>
                                    
                                    {{-- Kuantitas Ready Stock Toko --}}
                                    <td class="p-2">
                                        @if($v->stok_toko_ready > 0)
                                            <span class="badge bg-success-subtle text-success fs-6 w-100 py-2 border border-success-subtle">
                                                {{ $v->stok_toko_ready }} {{ $produk->satuan }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted fs-6 w-100 py-2 border text-center">
                                                0 {{ $produk->satuan }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kuantitas Mengunci Milik Pelanggan (Budi/Siti) --}}
                                    <td class="p-2">
                                        @if($v->stok_pesanan_customer > 0)
                                            <span class="badge bg-primary-subtle text-primary fs-6 w-100 py-2 border border-primary-subtle">
                                                {{ $v->stok_pesanan_customer }} {{ $produk->satuan }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted fs-6 w-100 py-2 border text-center">
                                                0 {{ $produk->satuan }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom Stok Defect --}}
                                    <td class="text-center">
                                        @if($v->stok_defect > 0)
                                            <span class="badge bg-warning text-dark px-3 py-1.5">{{ $v->stok_defect }} {{ $produk->satuan }}</span>
                                        @else
                                            <span class="text-muted small">0</span>
                                        @endif
                                    </td>

                                    <td class="text-end pe-3 fw-bold text-secondary">
                                        Rp {{ number_format($v->harga_avg_produk, 0, ',', '.') }}
                                    </td>
                                    
                                    <td class="text-center">
                                        <form action="{{ url('/produk_varian/'.$v->id) }}" method="POST" onsubmit="return confirm('Hapus varian warna {{ $v->warna }} ukuran {{ $v->ukuran }} ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bi bi-info-circle"></i> Belum ada varian warna dan ukuran untuk produk ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection