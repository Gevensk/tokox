@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Tambah Nota Stok Keluar</h3>
        <a href="{{ route('nota_keluar.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <form action="{{ route('nota_keluar.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipe_keluar" class="form-label fw-bold">Tipe Pengeluaran Stok</label>
                        <select name="tipe_keluar" id="tipe_keluar" class="form-control form-select" required>
                            <option value="penjualan_langsung">Penjualan Langsung (Cash & Carry)</option>
                            <option value="penjualan_order">Pelunasan Nota Order (Inden/PO)</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="customer_id" class="form-label fw-bold">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-control form-select" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->nama ?? $c->nama_customer }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3 d-none" id="wrapper_nota_order">
                    <label for="nota_order_id" class="form-label fw-bold">Pilih Nota Order Terkait</label>
                    <select name="nota_order_id" id="nota_order_id" class="form-control form-select">
                        <option value="">-- Pilih Nota Order --</option>
                        @foreach($notaOrders as $order)
                            <option value="{{ $order->id }}" 
                                    data-customer="{{ $order->customer_id }}"
                                    data-harga-deal="{{ $order->harga_deal }}"
                                    data-dp="{{ $order->nominal_dp_masuk }}">
                                Nota #{{ $order->id }} - {{ $order->customer->nama ?? $order->customer->nama_customer ?? 'N/A' }} (Sisa: Rp {{ number_format($order->harga_deal - $order->nominal_dp_masuk, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    <div class="alert alert-info mt-2 mb-0 d-none" id="info_keuangan_box">
                        <span id="info_keuangan"></span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="total_bayar" class="form-label fw-bold">Jumlah Uang Yang Dibayarkan (Rp)</label>
                    <input type="number" name="total_bayar" id="total_bayar" class="form-control" value="0" min="0" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 pt-3 border-top">
                    <h5 class="mb-0 fw-bold">Daftar Item Barang Keluar</h5>
                    <button type="button" class="btn btn-success btn-sm" id="btn-tambah-item">+ Tambah Produk</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" id="tabel-items">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 45%">Produk Varian</th>
                                <th style="width: 15%">Qty</th>
                                <th style="width: 25%">Harga Satuan (Rp)</th>
                                <th style="width: 15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="wrapper-item-rows">
                            </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-primary px-4">Proses Pengeluaran Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let itemIdx = 0;

    document.getElementById('btn-tambah-item').addEventListener('click', function() {
        const tbody = document.getElementById('wrapper-item-rows');
        const rowHtml = `
            <tr id="row-${itemIdx}">
                <td>
                    <select name="items[${itemIdx}][produk_varian_id]" class="form-control form-select" required>
                        <option value="">-- Pilih Varian --</option>
                        @foreach($produkVarians as $pv)
                            <option value="{{ $pv->id }}">
                                {{ $pv->produk->nama_product ?? '' }} [Warna: {{ $pv->warna }}, Ukuran: {{ $pv->ukuran }}] (Stok: {{ $pv->stok_tersedia }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${itemIdx}][qty]" class="form-control text-center" min="1" value="1" required>
                </td>
                <td>
                    <input type="number" name="items[${itemIdx}][harga_satuan]" class="form-control" min="0" value="0" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${itemIdx})">Hapus</button>
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', rowHtml);
        itemIdx++;
    });

    function hapusBaris(index) {
        const row = document.getElementById(`row-${index}`);
        if (row) row.remove();
    }

    document.getElementById('tipe_keluar').addEventListener('change', function() {
        const wrapperOrder = document.getElementById('wrapper_nota_order');
        const inputTotalBayar = document.getElementById('total_bayar');
        const selectOrder = document.getElementById('nota_order_id');
        const infoBox = document.getElementById('info_keuangan_box');
        
        if (this.value === 'penjualan_order') {
            wrapperOrder.classList.remove('d-none');
            inputTotalBayar.setAttribute('readonly', true);
            inputTotalBayar.value = 0;
        } else {
            wrapperOrder.classList.add('d-none');
            infoBox.classList.add('d-none');
            selectOrder.value = "";
            inputTotalBayar.removeAttribute('readonly');
            inputTotalBayar.value = 0;
        }
    });

    document.getElementById('nota_order_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const infoBox = document.getElementById('info_keuangan_box');
        const inputTotalBayar = document.getElementById('total_bayar');

        if (!selectedOption.value) {
            infoBox.classList.add('d-none');
            inputTotalBayar.value = 0;
            return;
        }

        const customerId = selectedOption.getAttribute('data-customer');
        const hargaDeal = parseFloat(selectedOption.getAttribute('data-harga-deal'));
        const dp = parseFloat(selectedOption.getAttribute('data-dp'));
        const sisaPelunasan = hargaDeal - dp;

        document.getElementById('customer_id').value = customerId;
        inputTotalBayar.value = sisaPelunasan;
        
        infoBox.classList.remove('d-none');
        document.getElementById('info_keuangan').innerText = 
            `Harga Deal PO: Rp ${hargaDeal.toLocaleString('id-ID')} | Sudah Masuk DP: Rp ${dp.toLocaleString('id-ID')} | Sisa Kekurangan: Rp ${sisaPelunasan.toLocaleString('id-ID')}`;
    });
</script>
@endsection