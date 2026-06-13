<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan Bahan Baku - {{ $nota->no_surat_jalan }}</title>
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
        .ttd-table {
            width: 100%;
            margin-top: 50px;
            text-align: center;
        }
        .ttd-table td {
            width: 33%;
            height: 80px;
            vertical-align: bottom;
        }
        .line-dot {
            border-bottom: 1px dashed #333;
            width: 120px;
            margin: 0 auto 5px auto;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                <span class="title-store">TOKO X KONVEKSI</span><br>
                <small>Sistem Manajemen Produksi Gudang & Meja Jahit</small>
            </td>
            <td style="text-align: right;">
                <span class="title-document">SURAT JALAN BAHAN BAKU</span><br>
                <strong>No: {{ $nota->no_surat_jalan }}</strong>
            </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 2px solid #333; margin-top: -10px; margin-bottom: 15px;">

    <table class="info-table">
        <tr>
            <td style="width: 15%;"><strong>Kepada Yth (PIC)</strong></td>
            <td style="width: 45%;">: {{ $nota->penjahit->nama ?? '-' }}</td>
            <td style="width: 15%;"><strong>Tanggal Keluar</strong></td>
            <td style="width: 25%;">: {{ date('d-m-Y H:i', strtotime($nota->tgl_order)) }}</td>
        </tr>
        <tr>
            <td><strong>Alamat Penjahit</strong></td>
            <td>: {{ $nota->penjahit->alamat ?? '-' }}</td>
            <td><strong>Status Kirim</strong></td>
            <td>: {{ $nota->status_kirim }}</td>
        </tr>
        <tr>
            <td><strong>No. Telp</strong></td>
            <td>: {{ $nota->penjahit->telp ?? '-' }}</td>
            <td><strong>Biaya Ongkir</strong></td>
            <td>: Rp {{ number_format($nota->ongkir_kirim_bahan, 0, ',', '.') }}</td>
        </tr>
    </table>

    <h4 style="margin-bottom: 8px; text-transform: uppercase; color: #444;">I. Rincian Bahan Baku Yang Dikirim (Keluar Gudang)</h4>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">No</th>
                <th>Nama Komponen / Bahan Baku</th>
                <th style="width: 25%; text-align: right;">Jumlah Fisik</th>
                <th style="width: 20%;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @php $noBahan = 1; @endphp
            @forelse($komponenBahan as $item)
                <tr>
                    <td style="text-align: center;">{{ $noBahan++ }}</td>
                    <td><strong>{{ $item['nama'] }}</strong></td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($item['jumlah'], 2) }}</td>
                    <td>{{ $item['satuan'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-center">Tidak ada bahan baku yang dialokasikan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h4 style="margin-bottom: 8px; text-transform: uppercase; color: #444;">II. Target Output Produksi Pakaian (Job Order)</h4>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">No</th>
                <th>Model Pakaian & Varian Produk</th>
                <th style="width: 20%; text-align: center;">Target Qty</th>
                <th>Kustomisasi Ukuran / Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php $noJob = 1; @endphp
            @foreach($nota->jobOrders as $job)
                <tr>
                    <td style="text-align: center;">{{ $noJob++ }}</td>
                    <td>
                        <strong>{{ $job->produk_varian->produk->nama ?? 'Produk' }}</strong><br>
                        <small style="color: #666;">Warna: {{ $job->produk_varian->warna }} | Ukuran: {{ $job->produk_varian->ukuran }}</small>
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ $job->jumlah }} Pcs</td>
                    <td>
                        @if($job->is_custom)
                            Custom (LD: {{ $job->ld_req ?? '-' }}, PB: {{ $job->pb_req ?? '-' }})<br>
                            <small style="color: red;">Ket: {{ $job->catatan_custom ?? '-' }}</small>
                        @else
                            Standar Pola Toko
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="font-size: 11px; italic: true; color: #666;">* Harap periksa kembali kecocokan jumlah fisik kain dan aksesoris sebelum menandatangani surat jalan ini.</p>

    <table class="ttd-table">
        <tr>
            <td>
                Pembawa/Kurir
                <div style="height: 50px;"></div>
                <div class="line-dot"></div>
                Nama Jelas
            </td>
            <td>
                Penerima (Penjahit)
                <div style="height: 50px;"></div>
                <div class="line-dot"></div>
                {{ $nota->penjahit->nama ?? '.......................' }}
            </td>
            <td>
                Hormat Kami (Gudang)
                <div style="height: 50px;"></div>
                <div class="line-dot"></div>
                Admin Toko
            </td>
        </tr>
    </table>

</body>
</html>