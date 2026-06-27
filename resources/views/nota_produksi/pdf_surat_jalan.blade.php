<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Produksi - {{ $nota->no_surat_jalan }}</title>
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
        
        .thanks-reason { margin-top: 40px; text-align: center; font-style: italic; color: #777; }
        
        .btn-print-manual { background-color: #198754; color: white; border: none; padding: 8px 16px; font-weight: bold; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .form-check-input { cursor: pointer; }
        
        .ttd-table { width: 100%; margin-top: 40px; text-align: center; border-collapse: collapse; }
        .ttd-table td { width: 50%; vertical-align: top; padding: 0 10px; }
        .line-dot { border-bottom: 1px dashed #333; width: 140px; margin: 60px auto 5px auto; }

        @media print {
            .action-bar { display: none !important; }
            body { margin: 15mm; } 
        }
    </style>
</head>
<body onload="window.print();">

    <div class="action-bar" style="margin-bottom: 20px; display: flex; align-items: center; gap: 15px; background-color: #f8f9fa; padding: 10px; border-radius: 6px; border: 1px solid #e3e6f0;">
        <button class="btn-print-manual" onclick="window.print()">🖨️ Cetak Bukti Nota</button>
        <div style="font-size: 13px; font-weight: 600;">
            <input type="checkbox" id="hideFinancials" class="form-check-input" onchange="toggleFinancials(this)">
            <label for="hideFinancials" style="user-select: none; cursor: pointer;">Sembunyikan Ongkos & Tarif (Untuk Kurir/Jalan)</label>
        </div>
    </div>

    <table class="header-table">
        <tr>
            <td>
                <span class="title-store">TOKO X</span><br>
                <small style="color: #666;">Konveksi & Garment Profesional</small>
            </td>
            <td style="text-align: right;">
                <span class="title-document">NOTA PRODUKSI</span><br>
                <strong style="font-size: 13px; color: #333;">No: {{ $nota->no_surat_jalan }}</strong>
            </td>
        </tr>
    </table>
    
    <hr style="border: 0; border-top: 2px solid #2c3e50; margin-top: -10px; margin-bottom: 15px;">
    
    <table class="info-table">
        <tr>
            <td style="width: 15%;"><strong>Mitra Penjahit</strong></td>
            <td style="width: 45%;">: {{ $nota->penjahit->nama ?? '-' }}</td>
            <td style="width: 15%;"><strong>Tanggal Mulai</strong></td>
            <td style="width: 25%;">: {{ date('d-m-Y H:i', strtotime($nota->tgl_order)) }}</td>
        </tr>
        <tr>
            <td><strong>Status Nota</strong></td>
            <td>: <span style="font-weight: bold; text-transform: uppercase;">{{ $nota->status_nota }}</span></td>
            <td style="color: #c0392b;"><strong>Tanggal Kirim</strong></td>
            <td style="color: #c0392b; font-weight: bold;">: {{ $nota->tgl_kirim ? date('d-m-Y H:i', strtotime($nota->tgl_kirim)) : '-' }}</td>
        </tr>
        <tr>
            <td><strong>Status Kirim</strong></td>
            <td>: {{ $nota->status_kirim }}</td>
            @if(Auth::user()->isAdmin())
            <td class="financial-info"><strong>Ongkir Bahan</strong></td>
            <td class="financial-info">: <span style="color: #c0392b; font-weight: bold;">Rp {{ number_format($nota->ongkir_kirim_bahan, 0, ',', '.') }}</span></td>
            @else
            <td></td>
            <td></td>
            @endif
        </tr>
    </table>

    <h4 style="margin-bottom: 10px; text-transform: uppercase; color: #2c3e50; letter-spacing: 0.5px;">I. Rincian Bahan Baku Yang Dikirim (Keluar Gudang)</h4>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">No</th>
                <th>Nama Komponen / Bahan Baku</th>
                <th style="width: 25%; text-align: right;">Jumlah Fisik</th>
                <th style="width: 20%; text-align: center;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @php $noBahan = 1; @endphp
            @forelse($komponenBahan as $item)
                <tr>
                    <td style="text-align: center;">{{ $noBahan++ }}</td>
                    <td><strong>{{ $item['nama'] }}</strong></td>
                    <td style="text-align: right; font-weight: bold; color: #2980b9;">{{ number_format($item['jumlah'], 2) }}</td>
                    <td style="text-align: center;">{{ $item['satuan'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #999; padding: 20px;">Tidak ada bahan baku yang dialokasikan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h4 style="margin-bottom: 10px; text-transform: uppercase; color: #2c3e50; letter-spacing: 0.5px;">II. Target Output Produksi Pakaian (Job Order)</h4>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th>Model Pakaian & Varian Produk</th>
                <th style="width: 15%; text-align: center;">Target Qty</th>
                <th style="width: 20%; text-align: center;">Estimasi Selesai</th>
                <th style="width: 15%; text-align: right;" class="financial-info">Tarif Jasa / Pcs</th>
                <th style="width: 20%; text-align: right;" class="financial-info">Total Ongkos Kerja</th>
                <th>Kustomisasi / Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php $noJob = 1; @endphp
            @foreach($nota->jobOrders as $job)
                <tr>
                    <td style="text-align: center;">{{ $noJob++ }}</td>
                    <td>
                        <strong>{{ $job->produk_varian->produk->nama ?? 'Produk' }}</strong><br>
                        <span style="color: #666; font-size: 11px;">
                            Warna: {{ $job->produk_varian->warna }} | Ukuran: {{ $job->produk_varian->ukuran }}
                        </span>
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ $job->jumlah }} Pcs</td>
                    <td style="text-align: center;">
                        @if($job->estimasi_selesai)
                            {{ date('d-m-Y', strtotime($job->estimasi_selesai)) }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #27ae60;" class="financial-info">
                        Rp {{ number_format($job->biaya_jasa_satuan, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #2c3e50;" class="financial-info">
                        Rp {{ number_format($job->biaya_jasa_satuan * $job->jumlah, 0, ',', '.') }}
                    </td>
                    <td>
                        @if($job->is_custom)
                            <span style="font-weight: 500;">Custom Spec</span><br>
                            <small style="color: #c0392b; font-style: italic;">* Ket: {{ $job->catatan_custom ?? '-' }}</small>
                        @else
                            <span class="text-muted small">Standard Pola Toko</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="thanks-reason" style="margin-top: 30px;">
        <p>Harap periksa kembali kecocokan jumlah fisik kain dan aksesoris sesuai dengan nota produksi ini.<br>Selamat bekerja dengan penuh tanggung jawab.</p>
    </div>

    <table class="ttd-table">
        <tr>
            <td>
                Penerima (Mitra Penjahit)
                <div class="line-dot"></div>
                <span style="color: #666; font-size: 11px;">{{ $nota->penjahit->nama ?? 'Mitra Penjahit' }}</span>
            </td>
            <td>
                Hormat Kami (Gudang)
                <div class="line-dot"></div>
                <span style="color: #666; font-size: 11px;">Admin Toko</span>
            </td>
        </tr>
    </table>

    <script>
        function toggleFinancials(checkbox) {
            const elements = document.querySelectorAll('.financial-info');
            elements.forEach(el => {
                if (checkbox.checked) {
                    el.style.display = 'none';
                } else {
                    el.style.display = '';
                }
            });
        }
    </script>

</body>
</html>