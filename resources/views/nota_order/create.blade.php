@extends('layouts.app') 

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold">🧾 Buat Nota Order / Pesanan Baru</h5>
        </div>
        <div class="card-body">
            
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('nota_order.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <h5 class="text-secondary border-bottom pb-2 mb-3">Informasi Utama Nota</h5>
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold text-dark">Tipe Order</label>
                        <select name="tipe_order" id="tipe_order" class="form-select" required>
                            <option value="Customer">Customer (Pesanan Luar)</option>
                            <option value="Internal">Internal (Stok Toko Sendiri)</option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-3" id="wrapper_customer">
                        <label class="form-label fw-semibold text-dark">Pilih Customer</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold text-dark">Tanggal Deadline</label>
                        <input type="date" name="tgl_deadline" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="row mb-4 bg-light p-3 rounded border mx-0">
                    <div class="col-md-6 mb-2">
                        <label class="form-label fw-bold text-dark">Total Keseluruhan Tagihan (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted small">Rp</span>
                            <input type="number" name="total_harga" id="total_harga" class="form-control bg-white border-start-0 fw-bold text-primary" value="0" readonly required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label fw-bold text-dark">Nominal Uang DP Masuk (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted small">Rp</span>
                            <input type="number" name="nominal_dp_masuk" class="form-control border-start-0 text-success fw-bold" placeholder="Isi 0 jika belum bayar" min="0" value="0" required>
                        </div>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                    <span>🛠️ Detail Baris Pesanan (Job Order)</span>
                    <button type="button" id="btn-tambah-baris" class="btn btn-sm btn-success px-3 fw-semibold">+ Tambah Produk</button>
                </h5>

                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-bordered align-middle mb-0" id="tabel-job-order" style="min-width: 1200px;">
                        <thead class="table-dark text-nowrap">
                            <tr>
                                <th style="width: 22%;">Pilih Produk</th>
                                <th style="width: 20%;">Pilih Varian</th>
                                <th style="width: 8%;" class="text-center">Qty</th>
                                <th style="width: 16%;">Harga Jual Satuan</th>
                                <th style="width: 10%;">Tipe</th>
                                <th style="width: 20%;">Spesifikasi Kustomisasi</th>
                                <th style="width: 4%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="container-baris-order">
                            <tr class="baris-job" data-index="0">
                                <td>
                                    <select class="form-select select-produk" required>
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach($produks as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][produk_varian_id]" class="form-select select-varian" required disabled>
                                        <option value="">-- Pilih Varian --</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[0][jumlah]" class="form-control input-qty text-center fw-semibold text-primary" min="1" value="1" required>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted small">Rp</span>
                                        <input type="number" name="items[0][harga_satuan_jual]" class="form-control input-harga text-end fw-semibold text-success" placeholder="0" min="0" required>
                                    </div>
                                </td>
                                <td>
                                    <select name="items[0][is_custom]" class="form-select select-is-custom">
                                        <option value="0">Standard</option>
                                        <option value="1">Custom</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="wrapper-fitur-custom d-none">
                                        <input type="text" name="items[0][catatan_custom]" class="form-control form-control-sm mb-2" placeholder="Contoh: Sablon/Bordir Belakang">
                                        <label class="form-label small text-muted mb-1 fw-semibold">Upload Foto Desain:</label>
                                        <input type="file" name="items[0][foto_desain]" class="form-control form-control-sm input-foto-desain" accept="image/*">
                                    </div>
                                    <span class="text-muted small standar-label bg-light d-block text-center rounded py-1">Mengikuti Spek Standard BoM</span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-baris px-2" disabled>×</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-end border-top pt-3 mt-4">
                    <a href="{{ route('nota_order.index') }}" class="btn btn-secondary px-4 me-2">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan Transaksi Order</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let barisIndex = 1;
    const containerBaris = document.getElementById('container-baris-order');
    const btnTambah = document.getElementById('btn-tambah-baris');
    const tipeOrder = document.getElementById('tipe_order');
    const wrapperCustomer = document.getElementById('wrapper_customer');

    // Fungsi Hitung Total Tagihan Otomatis
    function hitungGrandTotal() {
        let grandTotal = 0;
        containerBaris.querySelectorAll('.baris-job').forEach(baris => {
            const qty = parseFloat(baris.querySelector('.input-qty').value) || 0;
            const harga = parseFloat(baris.querySelector('.input-harga').value) || 0;
            grandTotal += (qty * harga);
        });
        document.getElementById('total_harga').value = grandTotal;
    }

    // Toggle Tipe Order (Customer vs Internal)
    tipeOrder.addEventListener('change', function() {
        if (this.value === 'Internal') {
            wrapperCustomer.classList.add('d-none');
            wrapperCustomer.querySelector('select').removeAttribute('required');
            wrapperCustomer.querySelector('select').value = '';
        } else {
            wrapperCustomer.classList.remove('d-none');
            wrapperCustomer.querySelector('select').setAttribute('required', 'required');
        }
    });

    // Aksi Tambah Baris Baru
    btnTambah.addEventListener('click', function() {
        const barisPertama = containerBaris.querySelector('.baris-job');
        const barisBaru = barisPertama.cloneNode(true);
        barisBaru.setAttribute('data-index', barisIndex);
        
        // Reset isi inputan baris baru
        barisBaru.querySelectorAll('input').forEach(input => {
            if (input.classList.contains('input-qty')) {
                input.value = 1;
            } else if (input.type === 'file') {
                input.value = ''; 
            } else {
                input.value = '';
            }
        });
        barisBaru.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        
        const selectVarianBaru = barisBaru.querySelector('.select-varian');
        selectVarianBaru.innerHTML = '<option value="">-- Pilih Varian --</option>';
        selectVarianBaru.setAttribute('disabled', 'disabled');
        barisBaru.querySelector('.wrapper-fitur-custom').classList.add('d-none');
        barisBaru.querySelector('.standar-label').classList.remove('d-none');

        // Ganti index item name array agar dinamis (items[0] -> items[1], dst)
        barisBaru.querySelectorAll('[name]').forEach(element => {
            const nameAttr = element.getAttribute('name');
            const updatedName = nameAttr.replace(/items\[\d+\]/, `items[${barisIndex}]`);
            element.setAttribute('name', updatedName);
        });

        barisBaru.querySelector('.btn-hapus-baris').removeAttribute('disabled');
        containerBaris.appendChild(barisBaru);
        barisIndex++;
    });

    // Event Delegation untuk Input & Select di dalam tabel
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
                            selectVarian.innerHTML += `<option value="${varian.id}">${varian.warna} - [Sizing: ${varian.ukuran}]</option>`;
                        });
                        selectVarian.removeAttribute('disabled');
                    } else {
                        selectVarian.innerHTML = '<option value="">Belum ada varian produk</option>';
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
                wrapperCustom.querySelector('input[type="text"]').value = '';
                wrapperCustom.querySelector('input[type="file"]').value = '';
            }
        }
    });

    // Deteksi input angka kuantiti & harga jitu untuk update total tagihan
    containerBaris.addEventListener('input', hitungGrandTotal);

    // Tombol X Hapus Baris
    containerBaris.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-hapus-baris')) {
            e.target.closest('.baris-job').remove();
            hitungGrandTotal();
        }
    });
});
</script>
@endsection