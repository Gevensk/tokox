@extends('layouts.app') 

@section('content')
<div class="row justify-content-center">
    <div class="col-md-11">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Nota Order Baru</h5>
                <a href="{{ route('nota_order.index') }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('nota_order.update', $notaOrder->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="harga_satuan_items" id="harga_satuan_items" value="{{ (int)$notaOrder->harga_satuan_items }}">

                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Informasi Nota (No. Order: {{ $notaOrder->no_order }})</h6>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tipe Order</label>
                            <select name="tipe_order" id="tipe_order" class="form-select" required>
                                <option value="Customer" {{ $notaOrder->tipe_order == 'Customer' ? 'selected' : '' }}>Customer (Pesanan Luar)</option>
                                <option value="Internal" {{ $notaOrder->tipe_order == 'Internal' ? 'selected' : '' }}>Internal (Stok Toko Sendiri)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 {{ $notaOrder->tipe_order == 'Internal' ? 'd-none' : '' }}" id="wrapper_customer">
                            <label class="form-label fw-bold">Pilih Customer</label>
                            <select name="customer_id" class="form-select" {{ $notaOrder->tipe_order == 'Customer' ? 'required' : '' }}>
                                <option value="">-- Pilih Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ $notaOrder->customer_id == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tanggal Deadline</label>
                            <input type="date" name="tgl_deadline" class="form-control" value="{{ \Carbon\Carbon::parse($notaOrder->tgl_deadline)->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-success">Total Keseluruhan (Rp)</label>
                            <input type="number" name="total_harga" id="total_harga" class="form-control bg-light fw-bold text-success fs-5" value="{{ (int)$notaOrder->total_harga }}" readonly required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Nominal DP Masuk (Rp)</label>
                            <input type="number" name="nominal_dp_masuk" class="form-control" value="{{ (int)$notaOrder->nominal_dp_masuk }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Status Bayar</label>
                            <select name="status_bayar" class="form-select" required>
                                <option value="Belum Lunas" {{ $notaOrder->status_bayar == 'Belum Lunas' ? 'selected' : '' }}>DP / Belum Lunas</option>
                                <option value="Lunas" {{ $notaOrder->status_bayar == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="text-primary fw-bold mb-0">Detail Baris Pesanan (Job Order)</h6>
                        <button type="button" id="btn-tambah-baris" class="btn btn-success btn-sm">+ Tambah Produk</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tabel-job-order">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">Pilih Produk</th>
                                    <th style="width: 20%;">Pilih Varian</th>
                                    <th style="width: 10%;">Qty</th>
                                    <th style="width: 15%;">Harga Satuan (Rp)</th>
                                    <th style="width: 10%;">Custom?</th>
                                    <th style="width: 20%;">Spesifikasi Kustomisasi</th>
                                    <th style="width: 5%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="container-baris-order">
                                @foreach($notaOrder->job_orders as $index => $job)
                                <tr class="baris-job" data-index="{{ $index }}">
                                    <td>
                                        <select class="form-select select-produk" data-selected-varian="{{ $job->produk_varian_id }}" required>
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach($produks as $p)
                                                <option value="{{ $p->id }}" {{ $job->produk_varian->produk_id == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][produk_varian_id]" class="form-select select-varian" required>
                                            <option value="{{ $job->produk_varian_id }}">
                                                {{ $job->produk_varian->warna }} - Sizing: {{ $job->produk_varian->ukuran }}
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][jumlah]" class="form-control input-qty" min="1" value="{{ $job->jumlah }}" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][harga_satuan_jual]" class="form-control input-harga" placeholder="0" min="0" value="{{ (int)$notaOrder->harga_satuan_items }}" required>
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][is_custom]" class="form-select select-is-custom">
                                            <option value="0" {{ $job->is_custom == 0 ? 'selected' : '' }}>Standar</option>
                                            <option value="1" {{ $job->is_custom == 1 ? 'selected' : '' }}>Custom</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="wrapper-fitur-custom {{ $job->is_custom == 1 ? '' : 'd-none' }}">
                                            <input type="text" name="items[{{ $index }}][catatan_custom]" class="form-control form-control-sm" value="{{ $job->catatan_custom }}" placeholder="Spesifikasi kustom">
                                        </div>
                                        <span class="text-muted standar-label small {{ $job->is_custom == 1 ? 'd-none' : '' }}">Mengikuti Spek Standard</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm btn-hapus-baris" {{ $notaOrder->job_orders->count() <= 1 ? 'disabled' : '' }}>X</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="{{ route('nota_order.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<table class="d-none">
    <tbody id="template-baris-baru">
        <tr class="baris-job" data-index="__INDEX__">
            <td>
                <select class="form-select select-produk" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($produks as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="items[__INDEX__][produk_varian_id]" class="form-select select-varian" required disabled>
                    <option value="">-- Pilih Varian --</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[__INDEX__][jumlah]" class="form-control input-qty" min="1" value="1" required>
            </td>
            <td>
                <input type="number" name="items[__INDEX__][harga_satuan_jual]" class="form-control input-harga" placeholder="0" min="0" required>
            </td>
            <td>
                <select name="items[__INDEX__][is_custom]" class="form-select select-is-custom">
                    <option value="0">Standar</option>
                    <option value="1">Custom</option>
                </select>
            </td>
            <td>
                <div class="wrapper-fitur-custom d-none">
                    <input type="text" name="items[__INDEX__][catatan_custom]" class="form-control form-control-sm" placeholder="Spesifikasi kustom">
                </div>
                <span class="text-muted standar-label small">Mengikuti Spek Standard</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-hapus-baris">X</button>
            </td>
        </tr>
    </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let barisIndex = {{ $notaOrder->job_orders->count() }};
    const containerBaris = document.getElementById('container-baris-order');
    const btnTambah = document.getElementById('btn-tambah-baris');
    const tipeOrder = document.getElementById('tipe_order');
    const wrapperCustomer = document.getElementById('wrapper_customer');
    const selectCustomer = wrapperCustomer.querySelector('select');

    containerBaris.querySelectorAll('.select-produk').forEach(selectProd => {
        const produkId = selectProd.value;
        const selectedVarianId = selectProd.getAttribute('data-selected-varian');
        const baris = selectProd.closest('.baris-job');
        const selectVarian = baris.querySelector('.select-varian');

        if (produkId) {
            fetch(`/api/produk/${produkId}/varian`)
                .then(response => response.json())
                .then(data => {
                    selectVarian.innerHTML = '<option value="">-- Pilih Varian --</option>';
                    if (data.length > 0) {
                        data.forEach(varian => {
                            const isSelected = varian.id == selectedVarianId ? 'selected' : '';
                            selectVarian.innerHTML += `<option value="${varian.id}" ${isSelected}>${varian.warna} - Sizing: ${varian.ukuran}</option>`;
                        });
                    }
                });
        }
    });

    tipeOrder.addEventListener('change', function() {
        if (this.value === 'Internal') {
            wrapperCustomer.classList.add('d-none');
            selectCustomer.removeAttribute('required');
            selectCustomer.value = '';
        } else {
            wrapperCustomer.classList.remove('d-none');
            selectCustomer.setAttribute('required', 'required');
        }
    });

    // FIX PERBAIKAN JAVASCRIPT: Selain menghitung total_harga, fungsi ini sekarang mengisi input hidden 'harga_satuan_items'
    function hitungTotal() {
        let grandTotal = 0;
        let hargaSatuanTerakhir = 0;

        containerBaris.querySelectorAll('.baris-job').forEach(baris => {
            const qty = parseFloat(baris.querySelector('.input-qty').value) || 0;
            const harga = parseFloat(baris.querySelector('.input-harga').value) || 0;
            grandTotal += (qty * harga);
            
            if(harga > 0) {
                hargaSatuanTerakhir = harga; 
            }
        });

        document.getElementById('total_harga').value = grandTotal;
        document.getElementById('harga_satuan_items').value = hargaSatuanTerakhir;
    }

    function validasiTombolHapus() {
        const semuaBaris = containerBaris.querySelectorAll('.baris-job');
        semuaBaris.forEach(baris => {
            const btn = baris.querySelector('.btn-hapus-baris');
            if (semuaBaris.length <= 1) {
                btn.setAttribute('disabled', 'disabled');
            } else {
                btn.removeAttribute('disabled');
            }
        });
    }

    btnTambah.addEventListener('click', function() {
        const templateHtml = document.getElementById('template-baris-baru').innerHTML;
        const barisBaruHtml = templateHtml.replace(/__INDEX__/g, barisIndex);
        
        containerBaris.insertAdjacentHTML('beforeend', barisBaruHtml);
        barisIndex++;
        validasiTombolHapus();
    });

    containerBaris.addEventListener('change', function(e) {
        if (e.target.classList.contains('select-produk')) {
            const produkId = e.target.value;
            const baris = e.target.closest('.baris-job');
            const selectVarian = baris.querySelector('.select-varian');

            if (!produkId) {
                selectVarian.innerHTML = '<option value="">-- Pilih Varian --</option>';
                selectVarian.setAttribute('disabled', 'disabled');
                return;
            }

            fetch(`/api/produk/${produkId}/varian`)
                .then(response => response.json())
                .then(data => {
                    selectVarian.innerHTML = '<option value="">-- Pilih Varian --</option>';
                    if (data.length > 0) {
                        data.forEach(varian => {
                            selectVarian.innerHTML += `<option value="${varian.id}">${varian.warna} - Sizing: ${varian.ukuran}</option>`;
                        });
                        selectVarian.removeAttribute('disabled');
                    } else {
                        selectVarian.innerHTML = '<option value="">Belum ada data varian</option>';
                        selectVarian.setAttribute('disabled', 'disabled');
                    }
                });
        }

        if (e.target.classList.contains('select-is-custom')) {
            const baris = e.target.closest('.baris-job');
            const wrapperCustom = baris.querySelector('.wrapper-fitur-custom');
            const standarLabel = baris.querySelector('.standar-label');

            if (e.target.value === '1') {
                wrapperCustom.classList.remove('d-none');
                standarLabel.classList.add('d-none');
            } else {
                wrapperCustom.classList.add('d-none');
                standarLabel.classList.remove('d-none');
                wrapperCustom.querySelector('input').value = '';
            }
        }
    });

    containerBaris.addEventListener('input', function() {
        hitungTotal();
    });

    containerBaris.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-hapus-baris') || e.target.closest('.btn-hapus-baris')) {
            const button = e.target.classList.contains('btn-hapus-baris') ? e.target : e.target.closest('.btn-hapus-baris');
            if (!button.hasAttribute('disabled')) {
                button.closest('.baris-job').remove();
                hitungTotal();
                validasiTombolHapus();
            }
        }
    });
});
</script>
@endsection