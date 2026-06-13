<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Order - {{ $notaOrder->no_order }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .title-store {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .title-document {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            color: #555;
        }
        .info-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .content-table th {
            background-color: #f2f2f2;
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        .content-table td {
            border: 1px solid #dddddd;
            padding: 8px;
        }
        .box-pembayaran {
            width: 35%;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: #f9f9f9;
        }
        .box-pembayaran td {
            padding: 6px 8px;
            border: 1px solid #dddddd;
        }
    </style>
</head>
<body onload="window.print()">

    <table class="header-table">
        <tr>
            <td>
                <span class="title-store">TOKO X</span><br>
                <small>Sistem Manajemen Produksi & Administrasi Nota</small>
            </td>
            <td style="text-align: right;">
                <span class="title-document"> NOTA ORDER </span><br>
                <strong>No. Order: {{ $notaOrder->no_order }}</strong>
            </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 2px solid #333; margin-top: -10px; margin-bottom: 15px;">

    <table class="info-table">
        <tr>
            <td style="width: 15%;"><strong>Customer</strong></td>
            <td style="width: 45%;">: {{ $notaOrder->customer->nama ?? 'Internal Stok' }}</td>
            <td style="width: 15%;"><strong>Tanggal Masuk</strong></td>
            <td style="width: 25%;">: {{ \Carbon\Carbon::parse($notaOrder->tgl_order)->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Status Order</strong></td>
            <td>: {{ $notaOrder->status_order }}</td>
            <td style="color: red;"><strong>Deadline</strong></td>
            <td style="color: red; font-weight: bold;">: {{ \Carbon\Carbon::parse($notaOrder->tgl_deadline)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td><strong>Status Bayar</strong></td>
            <td style="font-weight: bold;">: {{ $notaOrder->status_bayar }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <h4 style="margin-bottom: 8px; text-transform: uppercase; color: #444;">Detail Garment & Item Pesanan</h4>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 30%;">Produk / Varian</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Harga Satuan</th>
                <th style="width: 15%; text-align: right;">Total Harga</th>
                <th style="width: 25%;">Spesifikasi Kustom</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($notaOrder->job_orders as $job)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>
                        <strong>{{ $job->produk_varian->produk->nama }}</strong><br>
                        <small style="color: #666;">Warna: {{ $job->produk_varian->warna }} | Size: {{ $job->produk_varian->ukuran }}</small>
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ $job->jumlah }} Pcs</td>
                    <td style="text-align: right;">Rp {{ number_format($job->harga_satuan ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format(($job->jumlah * ($job->harga_satuan ?? 0)), 0, ',', '.') }}</td>
                    <td>
                        @if($job->is_custom)
                            {{ $job->catatan_custom }}
                        @else
                            <span style="color: #888; font-style: italic;">Standar Pola Toko</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="box-pembayaran">
        <tr>
            <td>Total Deal Jual</td>
            <td style="text-align: right; font-weight: bold;">Rp {{ number_format($notaOrder->harga_deal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Uang Muka (DP)</td>
            <td style="text-align: right;">Rp {{ number_format($notaOrder->nominal_dp_masuk, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <td>Status Bayar</td>
            <td style="text-align: right; color: green;">{{ $notaOrder->status_bayar }}</td>
        </tr>
    </table>

</body>
</html>