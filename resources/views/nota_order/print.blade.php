<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Pemesanan - {{ $notaOrder->no_order }}</title>
    <style>
        @page {
            size: auto;
            margin: 0mm;
        }

        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 12px; 
            color: #333; 
            line-height: 1.4; 
            margin: 20px; 
        }
        
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .title-store { font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .title-document { font-size: 16px; font-weight: bold; text-align: right; color: #2c3e50; }
        .info-table { width: 100%; margin-bottom: 25px; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .content-table th { background-color: #f8f9fa; border: 1px solid #eeeeee; padding: 10px 8px; text-align: left; font-weight: bold; color: #333; }
        .content-table td { border: 1px solid #eeeeee; padding: 10px 8px; vertical-align: middle; }
        .box-pembayaran { width: 40%; margin-left: auto; border-collapse: collapse; margin-top: 15px; background-color: #fdfdfd; }
        .box-pembayaran td { padding: 8px 10px; border: 1px solid #eeeeee; }
        .thanks-reason { margin-top: 40px; text-align: center; font-style: italic; color: #777; }
        
        .btn-print-manual { background-color: #198754; color: white; border: none; padding: 8px 16px; font-weight: bold; border-radius: 4px; cursor: pointer; margin-bottom: 20px; font-size: 12px; }
        @media print {
            .btn-print-manual { display: none !important; }
            body { margin: 15mm; } 
        }
    </style>
</head>
<body onload="window.print();">

    <button class="btn-print-manual" onclick="window.print()">🖨️ Cetak Bukti Nota</button>

    <table class="header-table">
        <tr>
            <td>
                <span class="title-store">TOKO X</span><br>
                <small style="color: #666;">Konveksi & Garment Profesional</small>
            </td>
            <td style="text-align: right;">
                <span class="title-document">NOTA ORDER</span><br>
                <strong style="font-size: 13px; color: #333;">No: {{ $notaOrder->no_order }}</strong>
            </td>
        </tr>
    </table>
    
    <hr style="border: 0; border-top: 2px solid #2c3e50; margin-top: -10px; margin-bottom: 15px;">
    
    <table class="info-table">
        <tr>
            <td style="width: 15%;"><strong>Nama Customer</strong></td>
            <td style="width: 45%;">: {{ $notaOrder->customer?->nama ?? 'Internal Stok' }}</td>
            <td style="width: 15%;"><strong>Tanggal Order</strong></td>
            <td style="width: 25%;">: {{ $notaOrder->tgl_order ? \Carbon\Carbon::parse($notaOrder->tgl_order)->format('d-m-Y H:i') : '-' }}</td>
        </tr>
        <tr>
            <td><strong>Status Order</strong></td>
            <td>: <span style="font-weight: bold; text-transform: uppercase;">{{ $notaOrder->status_order }}</span></td>
            <td style="color: #c0392b;"><strong>Estimasi Selesai</strong></td>
            <td style="color: #c0392b; font-weight: bold;">: {{ $notaOrder->tgl_deadline ? \Carbon\Carbon::parse($notaOrder->tgl_deadline)->format('d-m-Y') : '-' }}</td>
        </tr>
        <tr>
            <td><strong>Tipe Order</strong></td>
            <td>: {{ $notaOrder->tipe_order }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <h4 style="margin-bottom: 10px; text-transform: uppercase; color: #2c3e50; letter-spacing: 0.5px;">Rincian Pesanan</h4>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 45%;">Nama Item / Produk Varian</th>
                <th style="width: 15%; text-align: center;">Kuantitas (Qty)</th>
                <th style="width: 15%; text-align: right;">Harga Satuan</th>
                <th style="width: 20%; text-align: right;">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1; 
                $totalKalkulasiPenjualan = 0;
            @endphp
            
            @if(isset($notaOrder->job_orders) && $notaOrder->job_orders->count() > 0)
                @foreach($notaOrder->job_orders as $job)
                    @php
                        // Mengambil harga jual dari database (diambil dari skema input awal form Anda)
                        // Menggunakan fallback harga_satuan_items dari parent jika item kosong
                        $hargaJualSatuan = $notaOrder->harga_satuan_items ?? 0; 
                        $subtotalBarisJual = $hargaJualSatuan * $job->jumlah;
                        $totalKalkulasiPenjualan += $subtotalBarisJual;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td>
                            <strong>{{ $job->produk_varian?->produk?->nama ?? 'Produk Standar' }}</strong><br>
                            <span style="color: #666; font-size: 11px;">
                                Varian: {{ $job->produk_varian?->warna ?? '-' }} ({{ $job->produk_varian?->ukuran ?? '-' }})
                            </span>
                            @if($job->is_custom && $job->catatan_custom)
                                <br><small style="color: #27ae60; font-style: italic;">* Custom: {{ $job->catatan_custom }}</small>
                            @endif
                        </td>
                        <td style="text-align: center; font-weight: bold;">{{ $job->jumlah }} Pcs</td>
                        <td style="text-align: right;">Rp {{ number_format($hargaJualSatuan, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: bold;">Rp {{ number_format($subtotalBarisJual, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" style="text-align: center; color: #999; padding: 20px;">Belum ada rincian item.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="box-pembayaran">
        <tr style="font-weight: bold; background-color: #f8f9fa;">
            <td>Total Tagihan</td>
            <td style="text-align: right;">Rp {{ number_format($notaOrder->total_harga ?? $totalKalkulasiPenjualan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Uang Muka / DP</td>
            <td style="text-align: right; color: #2980b9;">Rp {{ number_format($notaOrder->nominal_dp_masuk ?? 0, 0, ',', '.') }}</td>
        </tr>
        
        @php
            $sisaTagihan = ($notaOrder->total_harga) - ($notaOrder->nominal_dp_masuk ?? 0);
        @endphp
        
        @if($sisaTagihan > 0)
            <tr style="font-weight: bold; color: #c0392b;">
                <td>Sisa Pelunasan</td>
                <td style="text-align: right;">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</td>
            </tr>
        @endif
        
        <tr style="background-color: #f1f2f6; font-weight: bold;">
            <td>Status Pembayaran</td>
            <td style="text-align: right; color: {{ $notaOrder->status_bayar == 'Lunas' ? '#27ae60' : '#d35400' }};">
                {{ $notaOrder->status_bayar }}
            </td>
        </tr>
    </table>

    <div class="thanks-reason">
        <p>Terima kasih telah mempercayakan konveksi Anda kepada kami.<br>Harap simpan nota ini sebagai bukti pengambilan barang yang sah.</p>
    </div>

</body>
</html>