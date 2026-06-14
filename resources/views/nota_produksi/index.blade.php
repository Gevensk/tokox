@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Monitoring Nota Produksi</h3>
        <a href="{{ route('nota_production.create') }}" class="btn btn-primary">+ Proses Antrean Baru</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No. Nota Produksi</th>
                            <th>Nama Penjahit (PIC)</th>
                            <th>Tanggal Mulai</th>
                            <th>Ongkos Kirim Bahan</th>
                            <th>Status Nota</th>
                            <th>Status Kirim Bahan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notaProduksis as $prod)
                            <tr>
                                <td><strong>{{ $prod->no_surat_jalan ?? 'PROD-'.str_pad($prod->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>
                                    <span class="badge bg-info text-dark">Mitra</span> 
                                    {{ $prod->penjahit->nama ?? 'Tidak Ada Penjahit' }}
                                </td>
                                <td>{{ date('d M Y H:i', strtotime($prod->tgl_order)) }}</td>
                                <td><strong class="text-secondary">Rp {{ number_format($prod->ongkir_kirim_bahan ?? 0, 0, ',', '.') }}</strong></td>
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
                                <td> 
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <a href="{{ route('nota_production.show', $prod->id) }}" class="btn btn-sm btn-outline-secondary text-nowrap" style="width: 110px;">
                                            👁 Detail
                                        </a>

                                        <a href="{{ route('nota_production.cetak_pdf', $prod->id) }}" class="btn btn-sm btn-warning text-dark text-nowrap" target="_blank" style="width: 110px;">
                                            🖨 Cetak
                                        </a>

                                        @if(($prod->status_kirim ?? 'Belum Dikirim') == 'Belum Dikirim')
                                            <form action="{{ route('nota_production.kirim_bahan', $prod->id) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa bahan baku kain & aksesoris sudah diserahkan ke kurir/penjahit?')" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary text-nowrap" style="width: 110px;">
                                                    🚚 Kirim Bahan
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-light text-muted text-nowrap" disabled style="width: 110px; border-style: dashed; background: transparent;">
                                                ✓ Terkirim
                                            </button>
                                        @endif

                                        @if(($prod->status_nota ?? 'Proses') == 'Proses')
                                            @if(($prod->status_kirim ?? 'Belum Dikirim') == 'Belum Dikirim')
                                                <button class="btn btn-sm btn-secondary text-white text-nowrap" disabled data-bs-toggle="tooltip" title="Kirim bahan terlebih dahulu" style="width: 120px;">
                                                    🔒 Terima Hasil
                                                </button>
                                            @else
                                                <a href="{{ route('hasil_produksi.create', ['job_id' => $prod->jobOrders->first()->id ?? 0]) }}" class="btn btn-sm btn-success fw-bold text-nowrap" style="width: 120px;">
                                                    ✔ Terima & QC
                                                </a>
                                            @endif
                                        @else
                                            <span class="badge bg-success py-2 text-nowrap text-center d-inline-block" style="width: 120px; line-height: 1.5;">
                                                ✓ Selesai
                                            </span>
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