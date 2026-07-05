<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Stok Keluar - #{{ $nota->id }}</title>
    <style>
        
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 11px; 
            color: #333; 
            line-height: 1.4; 
            margin: 10px;
            position: relative;
        }

        @media print {
            @page {
                margin: 0;
            }
            body {
                margin: 0;
                padding: 1.5cm;
            }
        }
        
        /* KONTEN UTAMA NOTA (Lapisan Bawah) */
        .print-wrapper {
            position: relative;
            z-index: 1; /* Diturunkan agar berada di bawah stempel */
        }

        /* STEMPEL LUNAS DI TENGAH (Lapisan Paling Depan / Menimpa Tabel) */
        .watermark-lunas-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-12deg);
            z-index: 9999; /* Angka besar memastikan dia berada di paling depan */
            pointer-events: none; /* Supaya klik/blok teks di browser tidak terganggu */
            opacity: 0.18; /* Transparansi pas agar teks di bawahnya tetap tembus pandang */
            text-align: center;
        }
        .watermark-lunas-box {
            border: 8px double #27ae60;
            color: #27ae60;
            font-size: 75px;
            font-weight: 900;
            letter-spacing: 6px;
            padding: 12px 45px;
            border-radius: 10px;
            text-transform: uppercase;
            font-family: 'Arial Black', Gadget, sans-serif;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .store-info { width: 50%; }
        .title-store { 
            font-size: 18px; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            color: #111;
        }
        .doc-info { text-align: right; width: 45%; }
        .title-document { 
            font-size: 15px; 
            font-weight: bold; 
            color: #2c3e50; 
            margin-bottom: 2px;
        }

        .info-table { 
            width: 100%; 
            margin-bottom: 20px; 
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding: 8px 0;
        }
        .info-table td { padding: 3px 0; vertical-align: top; }
        
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .content-table th { 
            background-color: #2c3e50; 
            color: #ffffff;
            border: 1px solid #2c3e50; 
            padding: 8px 6px; 
            text-align: left; 
            font-weight: bold; 
            font-size: 10px;
            text-transform: uppercase;
        }
        .content-table td { border: 1px solid #e0e0e0; padding: 8px 6px; vertical-align: middle; }
        
        .right-align { text-align: right; }
        .center-align { text-align: center; }

        .footer-note {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            margin-top: 35px;
            font-size: 10px;
            border-top: 1px dashed #ccc;
            padding-top: 8px;
        }
        
        .btn-print-manual { 
            background-color: #2c3e50; 
            color: white; 
            border: none; 
            padding: 8px 16px; 
            font-weight: bold; 
            border-radius: 4px; 
            cursor: pointer; 
            margin-bottom: 15px; 
            font-size: 11px; 
        }
        
        @media print {
            .btn-print-manual { display: none !important; }
            body { margin: 0; }
            .content-table th { background-color: #f2f2f2 !important; color: #333 !important; border: 1px solid #ccc; }
            .watermark-lunas-container { opacity: 0.15 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print();">

    <button class="btn-print-manual" onclick="window.print()">🖨️ Cetak Nota Stok Keluar</button>

    <div class="print-wrapper">
        <div class="header-container">
            <div class="store-info">
                <span class="title-store">TOKO X</span><br>
                <small style="color: #666; font-size: 10px;">Konveksi & Garment Profesional</small>
            </div>
            <div class="doc-info">
                <span class="title-document">NOTA STOK KELUAR</span><br>
                <span style="font-size: 12px; color: #555;">No: <strong>#{{ $nota->id }}</strong></span>
            </div>
        </div>
        
        <table class="info-table">
            <tr>
                <td style="width: 18%;"><strong>Customer</strong></td>
                <td style="width: 42%;">: {{ $nota->customer->nama ?? $nota->customer->nama_customer ?? 'Customer Umum' }}</td>
                <td style="width: 18%;"><strong>Waktu Keluar</strong></td>
                <td style="width: 22%;">: {{ date('d-m-Y H:i', strtotime($nota->created_at)) }}</td>
            </tr>
            <tr>
                <td><strong>Tipe Keluar</strong></td>
                <td>: {{ $nota->tipe_keluar === 'penjualan_order' ? 'Pelunasan Nota Order (Inden/PO)' : 'Penjualan Langsung (Cash)' }}</td>
                @if($nota->nota_order)
                    <td><strong>Ref. Order ID</strong></td>
                    <td>: #{{ $nota->nota_order->no_order }}</td>
                @else
                    <td></td>
                    <td></td>
                @endif
            </tr>
            @if($nota->customer && ($nota->customer->no_hp || $nota->customer->alamat))
            <tr>
                <td><strong>Kontak / Alamat</strong></td>
                <td colspan="3">: {{ $nota->customer->no_hp ?? '-' }} / {{ $nota->customer->alamat ?? '-' }}</td>
            </tr>
            @endif
        </table>

        <table class="content-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="center-align">No</th>
                    <th style="width: 50%;">Nama Produk & Detail Varian</th>
                    <th style="width: 15%;" class="center-align">Qty</th>
                    <th style="width: 15%;" class="right-align">Harga Satuan</th>
                    <th style="width: 15%;" class="right-align">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $no = 1; 
                    $totalSertaan = 0;
                @endphp
                @forelse($nota->nota_keluar as $detail)
                    @php 
                        $totalSertaan += $detail->subtotal; 
                    @endphp
                    <tr>
                        <td class="center-align">{{ $no++ }}</td>
                        <td>
                            <strong style="color: #111;">{{ $detail->produk_varian->produk->nama ?? 'Produk N/A' }}</strong><br>
                            <span style="color: #666; font-size: 10px;">Warna: {{ $detail->produk_varian->warna ?? '-' }} | Size: {{ $detail->produk_varian->ukuran ?? '-' }}</span>
                        </td>
                        <td class="center-align" style="font-weight: bold;">{{ number_format($detail->qty) }} Pcs</td>
                        <td class="right-align">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                        <td class="right-align" style="font-weight: bold;">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center-align" style="color: #999; padding: 15px;">Tidak ada detail barang keluar.</td>
                    </tr>
                @endforelse
                
                <tr style="font-weight: bold;">
                    <td colspan="4" class="right-align">Total Nilai Belanja:</td>
                    <td class="right-align">Rp {{ number_format($totalSertaan, 0, ',', '.') }}</td>
                </tr>
                <tr style="font-weight: bold;">
                    <td colspan="4" class="right-align">Total Jumlah Bayar:</td>
                    <td class="right-align" style="color: #27ae60;">Rp {{ number_format($nota->total_bayar, 0, ',', '.') }}</td>
                </tr>
                @if(($nota->total_bayar - $totalSertaan) > 0 && $nota->tipe_keluar !== 'penjualan_order')
                <tr style="font-weight: bold;">
                    <td colspan="4" class="right-align">Uang Kembalian:</td>
                    <td class="right-align" style="color: #2980b9;">Rp {{ number_format($nota->total_bayar - $totalSertaan, 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="footer-note">
            Terima kasih atas kerja sama Anda. Barang yang sudah dibeli tidak dapat ditukar/dikembalikan tanpa perjanjian.<br>
            <strong>Nota ini sah sebagai bukti penyerahan barang yang valid dan LUNAS.</strong>
        </div>
    </div>

    <div class="watermark-lunas-container">
        <div class="watermark-lunas-box">LUNAS</div>
    </div>

</body>
</html>