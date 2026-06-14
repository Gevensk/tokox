<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Order - {{ $notaOrder->no_order }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4; margin: 20px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .title-store { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .title-document { font-size: 16px; font-weight: bold; text-align: right; color: #555; }
        .info-table { width: 100%; margin-bottom: 25px; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .content-table th { background-color: #f2f2f2; border: 1px solid #dddddd; padding: 8px; text-align: left; font-weight: bold; }
        .content-table td { border: 1px solid #dddddd; padding: 8px; }
        .box-pembayaran { width: 35%; margin-left: auto; border-collapse: collapse; margin-top: 15px; background-color: #f9f9f9; }
        .box-pembayaran td { padding: 6px 8px; border: 1px solid #dddddd; }
        
        /* Tombol Cetak Manual Biasa */
        .btn-print-manual { background-color: #0d6efd; color: white; border: none; padding: 8px 16px; font-weight: bold; border-radius: 4px; cursor: pointer; margin-bottom: 20px; font-size: 12px; }
        @media print {
            .btn-print-manual { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print();">

    <button class="btn-print-manual" onclick="window.print()">🖨️ Cetak Nota Ini</button>

    <table class="header-table">
        <tr>
            <td>
                <span class="title-store">TOKO X</span><br>
                <small>Sistem Manajemen Produksi & Administrasi Nota</small>
            </td>
            <td style="text-align: right;">
                <span class="title-document">NOTA ORDER</span><br>
                <strong>No. Order: {{ $notaOrder->no_order }}</strong>
            </td>
        </tr>
    </table>
    
    <hr style="border: 0; border-top: 2px solid #333; margin-top: -10px; margin-bottom: 15px;">
    
    <table class="info-table">
        <tr>
            <td style="width: 15%;"><strong>Customer</strong></td>
            <td style="width: 45%;">: {{ $notaOrder->customer?->nama ?? 'Internal Stok' }}</td>
            <td style="width: 15%;"><strong>Tanggal Masuk</strong></td>
            <td style="width: 25%;">: {{ $notaOrder->tgl_order ? \Carbon\Carbon::parse($notaOrder->tgl_order)->format('d-m-Y H:i') : '-' }}</td>
        </tr>
        <tr>
            <td><strong>Status Order</strong></td>
            <td>: {{ $notaOrder->status_order }}</td>
            <td style="color: red;"><strong>Deadline</strong></td>
            <td style="color: red; font-weight: bold;">: {{ $notaOrder->tgl_deadline ? \Carbon\Carbon::parse($notaOrder->tgl_deadline)->format('d-m-Y') : '-' }}</td>
        </tr>
        <tr>
            <td><strong>Status Bayar</strong></td>
            <td style="font-weight: bold;">: {{ $notaOrder->status_bayar }}</td>
            <td></td><td></td>
        </tr>
    </table>

    <h4 style="margin-bottom: 8px; text-transform: uppercase; color: #444;">Detail Garment & Item Pesanan</h4>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 25%;">Produk / Varian</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Harga Kain</th>
                <th style="width: 15%; text-align: right;">Biaya Jasa</th>
                <th style="width: 15%; text-align: right;">Subtotal</th>
                <th style="width: 15%;">Spesifikasi Kustom</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            
            @if(isset($notaOrder->job_orders) && $notaOrder->job_orders->count() > 0)
                @foreach($notaOrder->job_orders as $job)
                    @php
                        $hargaSatuan = $job->harga_satuan ?? 0;
                        $biayaJasa = $job->biaya_jasa_satuan ?? 0;
                        $subtotalBaris = ($hargaSatuan + $biayaJasa) * $job->jumlah;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td>
                            <strong>{{ $job->produk_varian?->produk?->nama ?? 'Produk Umum' }}</strong><br>
                            <small style="color: #666;">Warna: {{ $job->produk_varian?->warna ?? '-' }} | Size: {{ $job->produk_varian?->ukuran ?? '-' }}</small>
                        </td>
                        <td style="text-align: center; font-weight: bold;">{{ $job->jumlah }} Pcs</td>
                        <td style="text-align: right;">Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($biayaJasa, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: bold;">Rp {{ number_format($subtotalBaris, 0, ',', '.') }}</td>
                        <td>{{ $job->is_custom ? $job->catatan_custom : 'Standar Pola Toko' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" style="text-align: center; color: #999; padding: 15px;">Tidak ada rincian item pesanan.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="box-pembayaran">
        <tr>
            <td>Total Keseluruhan</td>
            <td style="text-align: right; font-weight: bold;">Rp {{ number_format($notaOrder->total_harga ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Uang Muka (DP)</td>
            <td style="text-align: right;">Rp {{ number_format($notaOrder->nominal_dp_masuk ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <td>Status Bayar</td>
            <td style="text-align: right; color: green;">{{ $notaOrder->status_bayar }}</td>
        </tr>
    </table>
</body>
</html>