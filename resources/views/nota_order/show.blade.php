@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <a href="{{ route('nota_order.index') }}" class="btn btn-sm btn-outline-secondary">← Kembali ke Daftar</a>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('nota_order.pdf', $notaOrder->id) }}" target="_blank" class="btn btn-success fw-bold">
                🖨️ Cetak / Simpan PDF
            </a>
            
            <a href="{{ route('nota_order.edit', $notaOrder->id) }}" class="btn btn-warning fw-bold">✏️ Edit Order</a>
            <form action="{{ route('nota_order.destroy', $notaOrder->id) }}" method="POST" onsubmit="return confirm('⚠️ Hapus nota?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger fw-bold">❌ Hapus Total</button>
            </form>
        </div>
    </div>

    <div class="print-area">
        <div class="print-header d-none mb-4 text-center">
            <h2>SISTEM KONVEKSI MANAGEMENT</h2>
            <p class="mb-0">Nota Manajemen Produksi & Aturan Job Order</p>
            <hr style="border-top: 2px solid #000;">
        </div>

        <div class="row">
            <div class="col-md-4 mb-4 section-nota">
                <div class="card shadow-sm border-0 print-card">
                    <div class="card-header bg-dark text-white fw-bold py-3 no-print-bg">
                        📋 Resume Nota Order
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" width="40%">No. Order</td>
                                <td class="fw-bold">: {{ $notaOrder->no_order }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tipe Order</td>
                                <td>: {{ $notaOrder->tipe_order }}</td>
                            </tr>
                            @if($notaOrder->tipe_order === 'Customer')
                            <tr>
                                <td class="text-muted">Customer</td>
                                <td class="fw-bold text-primary">: {{ $notaOrder->customer->nama ?? 'N/A' }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Tgl Masuk</td>
                                <td>: {{ \Carbon\Carbon::parse($notaOrder->tgl_order)->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Deadline</td>
                                <td class="text-danger fw-bold print-black">: {{ \Carbon\Carbon::parse($notaOrder->tgl_deadline)->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status Order</td>
                                <td>: {{ $notaOrder->status_order }}</td>
                            </tr>
                        </table>
                        
                        <hr class="my-3">
                        
                        <div class="bg-light p-3 rounded print-bg-light">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Deal (Jual):</span>
                                <span class="fw-bold text-dark">Rp {{ number_format($notaOrder->harga_deal, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Uang Muka (DP):</span>
                                <span class="text-secondary fw-bold">Rp {{ number_format($notaOrder->nominal_dp_masuk, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                <span class="fw-bold">Status Bayar:</span>
                                <span class="fw-bold">{{ $notaOrder->status_bayar }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8 section-job">
                <div class="card shadow-sm border-0 print-card">
                    <div class="card-header bg-primary text-white fw-bold py-3 no-print-bg">
                        👕 Detail Item Pesanan & Alokasi Kebutuhan Kain
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 print-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk & Varian</th>
                                        <th width="100" class="text-center">Qty</th>
                                        <th>Kustomisasi Spesifikasi</th>
                                        <th>Estimasi Kebutuhan Bahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notaOrder->job_orders as $job)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $job->produk_varian->produk->nama }}</div>
                                            <div class="small text-muted">Varian: {{ $job->produk_varian->warna }} - Size: {{ $job->produk_varian->ukuran }}</div>
                                        </td>
                                        <td class="text-center fw-bold text-secondary fs-5 print-black">
                                            {{ $job->jumlah }} <span class="small fs-6 text-muted">pcs</span>
                                        </td>
                                        <td>
                                            @if($job->is_custom)
                                                <div class="small fw-bold text-wrap">Custom Specs:</div>
                                                <div class="small italic text-danger print-black">"{{ $job->catatan_custom }}"</div>
                                            @else
                                                <span class="text-muted small">Standar Pola</span>
                                            @endif
                                        </td>
                                        <td>
                                            <ul class="list-unstyled mb-0 small text-secondary print-black">
                                                @foreach($job->pemakaian_bahan as $p_bahan)
                                                    <li>• {{ $p_bahan->bahan_baku->nama }}: <strong>{{ number_format($p_bahan->jumlah, 2) }}</strong> {{ $p_bahan->bahan_baku->satuan }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* 1. Sembunyikan navigasi, tombol, dan footer bawaan aplikasi */
    .no-print, nav, .navbar, .sidebar, footer, .btn {
        display: none !important;
    }
    
    /* 2. Atur halaman penuh tanpa margin bawaan web */
    body, .container, .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        background-color: #fff !important;
    }

    /* 3. Tampilkan judul kop surat tersembunyi */
    .print-header {
        display: block !important;
    }

    /* 4. Paksa layout bootstrap grid kiri-kanan agar tidak patah ke bawah */
    .row {
        display: flex !important;
        flex-wrap: nowrap !important;
    }
    .col-md-4 { width: 33.333% !important; }
    .col-md-8 { width: 66.666% !important; }

    /* 5. Bersihkan bayangan shadow bootstrap agar tidak buram di kertas/PDF */
    .print-card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        margin-bottom: 15px !important;
    }

    /* 6. Atur teks warna netral hitam agar tajam */
    .print-black, .text-danger, .text-primary {
        color: #000 !important;
    }
}
</style>
@endsection