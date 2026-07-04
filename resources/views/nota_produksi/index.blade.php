@extends('layouts.app')

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Nota Produksi Jahit (Nota Produksi)</h5>
        <a href="{{ route('nota_production.create') }}" class="btn btn-light btn-sm">+ Tambah</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Nota Produksi</th>
                        <th>Nama Penjahit (PIC)</th>
                        <th>Tanggal Mulai</th>
                        @if(Auth::user()->isAdmin())
                        <th>Ongkos Kirim Bahan</th>
                        @endif
                        <th>Status Nota</th>
                        <th>Status Kirim Bahan</th>
                        <th style="width: 1%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notaProduksis as $prod)
                        <tr>
                            <td><strong>{{ $prod->no_surat_jalan ?? 'PROD-'.str_pad($prod->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>
                                <span class="badge bg-info text-dark me-1">Mitra</span> 
                                {{ $prod->penjahit->nama ?? 'Tidak Ada Penjahit' }}
                            </td>
                            <td>{{ date('d M Y H:i', strtotime($prod->tgl_order)) }}</td>
                            @if(Auth::user()->isAdmin())
                            <td><strong class="text-secondary">Rp {{ number_format($prod->ongkir_kirim_bahan ?? 0, 0, ',', '.') }}</strong></td>
                            @endif
                            <td>
                                @if(($prod->status_nota ?? 'Proses') == 'Proses')
                                    <span class="badge bg-warning text-dark">Proses</span>
                                @else
                                    <span class="badge bg-success">Selesai</span>
                                @endif
                            </td>
                            <td>
                                @if(($prod->status_kirim ?? 'Belum Dikirim') == 'Belum Dikirim')
                                    <span class="badge bg-danger">Belum Dikirim</span>
                                @else
                                    <span class="badge bg-info text-dark">
                                        🚚 Dikirim 
                                        @if($prod->tgl_kirim)
                                            ({{ date('d-m-Y', strtotime($prod->tgl_kirim)) }})
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td class="text-nowrap" style="width: 1%;"> 
                                <div class="d-flex justify-content-start align-items-center gap-1">
                                    <a href="{{ route('nota_production.show', $prod->id) }}" class="btn btn-secondary btn-sm">Detail</a>

                                    <a href="{{ route('nota_production.cetak_nota', $prod->id) }}" class="btn btn-info btn-sm text-white" target="_blank">Cetak</a>

                                    @if(($prod->status_nota ?? 'Proses') == 'Proses')
                                        <a href="{{ route('nota_production.edit', $prod->id) }}" class="btn btn-warning btn-sm">Edit</a>

                                        <form action="{{ route('nota_production.destroy', $prod->id) }}" method="POST" onsubmit="return confirm('Hapus?')" class="d-inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Batal</button>
                                        </form>

                                        @if(($prod->status_kirim ?? 'Belum Dikirim') == 'Belum Dikirim')
                                            <form action="{{ route('nota_production.kirim_bahan', $prod->id) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa bahan baku kain & aksesoris sudah diserahkan ke kurir/penjahit?')" class="d-inline m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Kirim Bahan</button>
                                            </form>
                                        @else
                                            <span class="text-success small fw-semibold ms-1">Terkirim</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada aktivitas produksi jahit yang berjalan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($notaProduksis->hasPages())
        <div class="card-footer bg-white">
            {{ $notaProduksis->links() }}
        </div>
        @endif
    </div>
</div>
@endsection