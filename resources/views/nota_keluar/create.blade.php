@extends('layouts.app') 

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Form Buat Nota Stok Keluar</h5>
                <a href="{{ route('nota_keluar.index') }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
            <div class="card-body">
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('nota_keluar.store') }}" method="POST">
                @csrf

                {{-- Hidden Input untuk Customer ID (Diisi otomatis lewat JS) --}}
                <input type="hidden" name="customer_id" id="customer_id" value="">

                <h5 class="text-secondary border-bottom pb-2 mb-3">Informasi Utama Nota</h5>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label for="tipe_keluar" class="form-label fw-semibold">Tipe Pengeluaran Stok</label>
                        <select name="tipe_keluar" id="tipe_keluar" class="form-select" required>
                            <option value="penjualan_langsung">Penjualan Langsung</option>
                            <option value="penjualan_order">Pelunasan Nota Order</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3 d-none" id="wrapper_nota_order">
                        <label for="nota_order_id" class="form-label fw-semibold">Pilih Nota Order Terkait</label>
                        <select name="nota_order_id" id="nota_order_id" class="form-select">
                            <option value="">-- Pilih Nota Order --</option>
                            @foreach($notaOrders as $order)
                                @php
                                    $sisaTagihan = $order->status_bayar === 'Lunas' ? 0 : max(0, $order->total_harga - $order->nominal_dp_masuk);
                                @endphp
                                <option value="{{ $order->id }}" 
                                        data-customer="{{ $order->customer_id }}"
                                        data-customer-name="{{ $order->customer->nama ?? $order->customer->nama_customer ?? 'N/A' }}"
                                        data-total-harga="{{ $order->total_harga }}"
                                        data-dp="{{ $order->nominal_dp_masuk }}"
                                        data-status-bayar="{{ $order->status_bayar }}"
                                        data-harga-satuan="{{ $order->harga_satuan_items }}"
                                        data-items='[
                                            @foreach($order->job_orders as $job)
                                                @php
                                                    $qtyDiambil = \App\Models\NotaStokKeluarHasProdukVarian::where('produk_varian_id', $job->produk_varian_id)
                                                        ->whereHas('nota_keluar', function($query) use ($order) {
                                                            $query->where('nota_order_id', $order->id);
                                                        })
                                                        ->sum('qty') ?? 0;
                                                    $qtySisa = max(0, $job->jumlah - $qtyDiambil);
                                                @endphp
                                                {
                                                    "produk_varian_id": {{ $job->produk_varian_id }},
                                                    "qty_order": {{ $job->jumlah }},
                                                    "qty_diambil": {{ $qtyDiambil }},
                                                    "qty_sisa": {{ $qtySisa }},
                                                    "warna": "{{ $job->produk_varian->warna ?? "" }}",
                                                    "ukuran": "{{ $job->produk_varian->ukuran ?? "" }}",
                                                    "produk_nama": "{{ $job->produk_varian->produk->nama ?? "" }}"
                                                }
                                                @if(!$loop->last),@endif
                                            @endforeach
                                        ]'>
                                    Nota #{{ $order->no_order }} - {{ $order->customer->nama ?? $order->customer->nama_customer ?? 'N/A' }} 
                                    @if($order->status_bayar === 'Lunas')
                                        (Lunas)
                                    @else
                                        (Sisa: Rp {{ number_format($sisaTagihan, 0, ',', '.') }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3 d-none" id="wrapper_customer_display">
                        <label class="form-label fw-semibold">Customer Pemesan</label>
                        <input type="text" id="customer_name_display" class="form-control bg-light" readonly placeholder="Nama Customer">
                    </div>
                </div>

                {{-- Alert Info Keuangan PO Khusus Penjualan Order --}}
                <div class="alert alert-info border-info mb-4 d-none" id="info_keuangan_box">
                    <i class="me-1">ℹ️</i> <span id="info_keuangan" class="fw-semibold text-dark"></span>
                </div>

                {{-- Kotak Kasir Ringkas Bergaya Grey Box --}}
                <div class="row mb-4 bg-light p-3 rounded border">
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-bold text-dark">Total Nilai Tagihan (Rp)</label>
                        <input type="number" name="total_harga" id="total_harga_hidden" value="0" hidden>
                        <input type="text" id="grand_total_display" class="form-control bg-white fw-bold text-primary fs-5" value="Rp 0" readonly>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-bold text-dark">Nominal Uang Dibayar (Rp)</label>
                        <input type="number" name="total_bayar" id="total_bayar" class="form-control text-success fw-bold fs-5" value="0" min="0" required>
                    </div>
                    <div class="col-md-4 mb-2" id="wrapper_kembalian">
                        <label class="form-label fw-bold text-dark">Uang Kembalian (Rp)</label>
                        <input type="text" id="kembalian_display" class="form-control bg-white fw-bold fs-5 text-secondary" value="Rp 0" readonly>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                    <span>📦 Detail Baris Barang Keluar</span>
                    <button type="button" id="btn-tambah-item" class="btn btn-sm btn-success px-3">+ Tambah Produk</button>
                </h5>

                <div class="table-responsive shadow-sm rounded border mb-4">
                    <table class="table table-hover table-bordered align-middle mb-0 text-secondary" id="tabel-items" style="min-width: 1000px; font-size: 0.85rem;">
                        <thead class="table-dark text-nowrap">
                            <tr>
                                <th style="width: 45%" class="py-3">Produk Varian</th>
                                <th style="width: 15%" class="text-center py-3">Qty</th>
                                <th style="width: 25%" class="py-3">Harga Satuan (Rp)</th>
                                <th style="width: 15%" class="text-center py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="wrapper-item-rows">
                            {{-- Diisi dinamis via JavaScript --}}
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-start gap-2 mt-4">
                    <a href="{{ route('nota_keluar.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan Transaksi Keluar</button>
                </div>
            </form>

        </div>
    </div>
</div>
</div>

<script>
    let itemIdx = 0;

    // Format mata uang Rupiah
    function formatRupiah(angka) {
        return 'Rp ' + parseFloat(angka).toLocaleString('id-ID');
    }

    // Fungsi utama kalkulasi kasir
    function hitungKasir() {
        const tipeKeluar = document.getElementById('tipe_keluar').value;
        const inputTotalBayar = document.getElementById('total_bayar');
        const grandTotalDisplay = document.getElementById('grand_total_display');
        const kembalianDisplay = document.getElementById('kembalian_display');
        const wrapperKembalian = document.getElementById('wrapper_kembalian');
        const totalHargaHidden = document.getElementById('total_harga_hidden');
        
        let grandTotal = 0;

        if (tipeKeluar === 'penjualan_langsung') {
            const rows = document.querySelectorAll('#wrapper-item-rows tr');
            rows.forEach(function(row) {
                const qtyInput = row.querySelector('input[name$="[qty]"]');
                const hargaInput = row.querySelector('input[name$="[harga_satuan]"]');
                
                const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
                const harga = parseFloat(hargaInput ? hargaInput.value : 0) || 0;
                grandTotal += qty * harga;
            });

            totalHargaHidden.value = grandTotal;
            grandTotalDisplay.value = formatRupiah(grandTotal);
            wrapperKembalian.classList.remove('d-none');
            
            const bayar = parseFloat(inputTotalBayar.value) || 0;
            const kembalian = bayar - grandTotal;
            
            kembalianDisplay.value = formatRupiah(kembalian);
            if (kembalian >= 0) {
                kembalianDisplay.className = 'form-control bg-white fw-bold fs-5 text-success';
            } else {
                kembalianDisplay.className = 'form-control bg-white fw-bold fs-5 text-danger';
            }
        } else {
            const selectOrder = document.getElementById('nota_order_id');
            const selectedOption = selectOrder.options[selectOrder.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const totalHarga = parseFloat(selectedOption.getAttribute('data-total-harga')) || 0;
                const dp = parseFloat(selectedOption.getAttribute('data-dp')) || 0;
                const statusBayar = selectedOption.getAttribute('data-status-bayar');
                
                const sisaPelunasan = statusBayar === 'Lunas' ? 0 : Math.max(0, totalHarga - dp);
                
                grandTotal = sisaPelunasan;
                totalHargaHidden.value = grandTotal;
                grandTotalDisplay.value = formatRupiah(grandTotal);
                inputTotalBayar.value = sisaPelunasan;
                
                wrapperKembalian.classList.add('d-none');
            } else {
                totalHargaHidden.value = 0;
                grandTotalDisplay.value = formatRupiah(0);
                inputTotalBayar.value = 0;
                wrapperKembalian.classList.add('d-none');
            }
        }
    }

    // Fungsi untuk menambah baris produk secara dinamis
    function tambahBaris(isFirstRow = false) {
        const tbody = document.getElementById('wrapper-item-rows');
        const deleteButtonHtml = isFirstRow ? '' : `<button type="button" class="btn btn-sm btn-danger px-3" onclick="hapusBaris(${itemIdx})">×</button>`;
        
        const rowHtml = `
            <tr id="row-${itemIdx}">
                <td class="py-2">
                    <select name="items[${itemIdx}][produk_varian_id]" class="form-select" required>
                        <option value="">-- Pilih Varian --</option>
                        @foreach($produkVarians as $pv)
                            <option value="{{ $pv->id }}">
                                {{ $pv->produk->nama ?? '' }} [Warna: {{ $pv->warna }}, Ukuran: {{ $pv->ukuran }}] (Stok: {{ $pv->stok_tersedia }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="py-2">
                    <input type="number" name="items[${itemIdx}][qty]" class="form-control text-center input-qty" min="1" value="1" required>
                </td>
                <td class="py-2">
                    <input type="number" name="items[${itemIdx}][harga_satuan]" class="form-control input-harga" min="0" value="0" required>
                </td>
                <td class="text-center py-2">
                    ${deleteButtonHtml}
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', rowHtml);
        itemIdx++;
        hitungKasir();
    }

    // Tombol tambah produk manual (hanya untuk penjualan langsung)
    document.getElementById('btn-tambah-item').addEventListener('click', function() {
        tambahBaris(false);
    });

    function hapusBaris(index) {
        const row = document.getElementById(`row-${index}`);
        if (row) {
            row.remove();
            hitungKasir();
        }
    }

    // Perubahan Tipe Pengeluaran
    document.getElementById('tipe_keluar').addEventListener('change', function() {
        const wrapperOrder = document.getElementById('wrapper_nota_order');
        const wrapperCustomer = document.getElementById('wrapper_customer_display');
        const inputTotalBayar = document.getElementById('total_bayar');
        const selectOrder = document.getElementById('nota_order_id');
        const infoBox = document.getElementById('info_keuangan_box');
        const inputCustomerId = document.getElementById('customer_id');
        const inputCustomerName = document.getElementById('customer_name_display');
        const btnTambahItem = document.getElementById('btn-tambah-item');
        const tbody = document.getElementById('wrapper-item-rows');

        tbody.innerHTML = '';
        itemIdx = 0;
        
        if (this.value === 'penjualan_order') {
            wrapperOrder.classList.remove('d-none');
            wrapperCustomer.classList.remove('d-none');
            inputTotalBayar.setAttribute('readonly', true);
            inputTotalBayar.value = 0;
            btnTambahItem.classList.add('d-none');
        } else {
            wrapperOrder.classList.add('d-none');
            wrapperCustomer.classList.add('d-none');
            infoBox.classList.add('d-none');
            selectOrder.value = "";
            inputTotalBayar.removeAttribute('readonly');
            inputTotalBayar.value = 0;
            btnTambahItem.classList.remove('d-none');
            inputCustomerId.value = "";
            inputCustomerName.value = "";
            tambahBaris(true); // Load satu baris kosong pertama secara otomatis
        }
        hitungKasir();
    });

    // Inisialisasi awal saat halaman dimuat
    window.addEventListener('DOMContentLoaded', function() {
        const tipeKeluar = document.getElementById('tipe_keluar').value;
        if (tipeKeluar === 'penjualan_langsung') {
            tambahBaris(true); // Load satu baris kosong pertama secara default
        }
    });

    // Ketika Nota Order Dipilih
    document.getElementById('nota_order_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const infoBox = document.getElementById('info_keuangan_box');
        const inputTotalBayar = document.getElementById('total_bayar');
        const inputCustomerId = document.getElementById('customer_id');
        const inputCustomerName = document.getElementById('customer_name_display');
        const tbody = document.getElementById('wrapper-item-rows');

        tbody.innerHTML = '';
        itemIdx = 0;

        if (!selectedOption.value) {
            infoBox.classList.add('d-none');
            inputTotalBayar.value = 0;
            inputCustomerId.value = "";
            inputCustomerName.value = "";
            hitungKasir();
            return;
        }

        const customerId = selectedOption.getAttribute('data-customer');
        const customerName = selectedOption.getAttribute('data-customer-name');
        const totalHarga = parseFloat(selectedOption.getAttribute('data-total-harga'));
        const dp = parseFloat(selectedOption.getAttribute('data-dp'));
        const statusBayar = selectedOption.getAttribute('data-status-bayar');
        const sisaPelunasan = statusBayar === 'Lunas' ? 0 : Math.max(0, totalHarga - dp);
        const hargaSatuanItems = parseFloat(selectedOption.getAttribute('data-harga-satuan')) || 0;
        const items = JSON.parse(selectedOption.getAttribute('data-items') || '[]');

        inputCustomerId.value = customerId;
        inputCustomerName.value = customerName;
        inputTotalBayar.value = sisaPelunasan;
        
        infoBox.classList.remove('d-none');
        if (statusBayar === 'Lunas') {
            document.getElementById('info_keuangan').innerText = 
                `Harga Deal PO: Rp ${totalHarga.toLocaleString('id-ID')} | Pembayaran PO: Sudah Lunas | Sisa Kekurangan: Rp 0`;
        } else {
            document.getElementById('info_keuangan').innerText = 
                `Harga Deal PO: Rp ${totalHarga.toLocaleString('id-ID')} | Sudah Masuk DP: Rp ${dp.toLocaleString('id-ID')} | Sisa Kekurangan (Pelunasan): Rp ${sisaPelunasan.toLocaleString('id-ID')}`;
        }

        items.forEach(function(item) {
            if (item.qty_sisa <= 0) {
                return;
            }
            const rowHtml = `
                <tr id="row-${itemIdx}">
                    <td class="py-2.5">
                        <input type="hidden" name="items[${itemIdx}][produk_varian_id]" value="${item.produk_varian_id}">
                        <span class="fw-bold text-dark d-block mb-0.5" style="font-size: 0.88rem;">${item.produk_nama}</span>
                        <div class="small text-muted" style="font-size: 0.78rem;">Warna: ${item.warna} | Ukuran: ${item.ukuran}</div>
                    </td>
                    <td class="py-2">
                        <input type="number" name="items[${itemIdx}][qty]" class="form-control text-center bg-white" min="1" max="${item.qty_sisa}" value="${item.qty_sisa}" required>
                        <small class="text-muted d-block text-center mt-1">Sisa: ${item.qty_sisa} pcs (Order: ${item.qty_order} pcs)</small>
                    </td>
                    <td class="py-2">
                        <input type="number" name="items[${itemIdx}][harga_satuan]" class="form-control text-end bg-white" value="${hargaSatuanItems}" required readonly>
                    </td>
                    <td class="text-center py-2">
                        <span class="badge bg-light text-secondary border py-2 px-3">Terkunci PO</span>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', rowHtml);
            itemIdx++;
        });

        hitungKasir();
    });

    document.getElementById('total_bayar').addEventListener('input', hitungKasir);
    document.getElementById('wrapper-item-rows').addEventListener('input', function(e) {
        if (e.target.name && (e.target.name.includes('[qty]') || e.target.name.includes('[harga_satuan]'))) {
            hitungKasir();
        }
    });
</script>
@endsection