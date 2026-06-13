@extends('layouts.app') 

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Buat Nota Order Baru</h4>
        </div>
        <div class="card-body">
            
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('nota_order.store') }}" method="POST">
                @csrf

                <h5 class="text-secondary border-bottom pb-2 mb-3">Informasi Nota</h5>
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tipe Order</label>
                        <select name="tipe_order" id="tipe_order" class="form-select" required>
                            <option value="Customer">Customer (Pesanan Luar)</option>
                            <option value="Internal">Internal (Stok Toko Sendiri)</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3" id="wrapper_customer">
                        <label class="form-label">Pilih Customer</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tanggal Deadline</label>
                        <input type="date" name="tgl_deadline" class="form-select" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Harga Deal (Rp)</label>
                        <input type="number" name="harga_deal" class="form-control" placeholder="Contoh: 160000" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nominal DP Masuk (Rp)</label>
                        <input type="number" name="nominal_dp_masuk" class="form-control" placeholder="Isi 0 jika belum bayar" required>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                    Detail Baris Pesanan (Job Order)
                    <button type="button" id="btn-tambah-baris" class="btn btn-sm btn-success">+ Tambah Produk</button>
                </h5>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="tabel-job-order">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Pilih Produk</th>
                                <th style="width: 25%;">Pilih Varian</th>
                                <th style="width: 10%;">Qty</th>
                                <th style="width: 10%;">Custom?</th>
                                <th style="width: 25%;">Spesifikasi Kustomisasi</th>
                                <th style="width: 5%;">Aksi</th>
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
                                    <input type="number" name="items[0][jumlah]" class="form-control" min="1" value="1" required>
                                </td>
                                <td>
                                    <select name="items[0][is_custom]" class="form-select select-is-custom">
                                        <option value="0">Standar</option>
                                        <option value="1">Custom</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="wrapper-fitur-custom d-none">
                                        <input type="text" name="items[0][catatan_custom]" class="form-control form-control-sm" placeholder="Contoh: LD 105cm, PB 75cm, Lengan 3/4, kancing merah">
                                    </div>
                                    <span class="text-muted standar-label">Mengikuti Spek Standard</span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger btn-hapus-baris" disabled>X</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('nota_order.index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">Simpan Transaksi Order</button>
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

    // 1. Sembunyikan Customer jika internal stok toko sendiri
    tipeOrder.addEventListener('change', function() {
        if (this.value === 'Internal') {
            wrapperCustomer.classList.add('d-none');
            wrapperCustomer.querySelector('select').removeAttribute('required');
        } else {
            wrapperCustomer.classList.remove('d-none');
            wrapperCustomer.querySelector('select').setAttribute('required', 'required');
        }
    });

    // 2. Klik Tambah Baris Baru Secara Dinamis
    btnTambah.addEventListener('click', function() {
        const barisPertama = containerBaris.querySelector('.baris-job');
        const barisBaru = barisPertama.cloneNode(true);

        barisBaru.setAttribute('data-index', barisIndex);
        
        // Reset input value di baris baru
        barisBaru.querySelectorAll('input').forEach(input => input.value = input.type === 'number' ? 1 : '');
        barisBaru.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        
        // Reset Dropdown varian agar terkunci kembali di baris baru
        const selectVarianBaru = barisBaru.querySelector('.select-varian');
        selectVarianBaru.innerHTML = '<option value="">-- Pilih Varian --</option>';
        selectVarianBaru.setAttribute('disabled', 'disabled');

        barisBaru.querySelector('.wrapper-fitur-custom').classList.add('d-none');
        barisBaru.querySelector('.standar-label').classList.remove('d-none');

        // Mapping ulang nama attribute array index html (items[0] -> items[1])
        barisBaru.querySelectorAll('[name]').forEach(element => {
            const nameAttr = element.getAttribute('name');
            const updatedName = nameAttr.replace(/items\[\d+\]/, `items[${barisIndex}]`);
            element.setAttribute('name', updatedName);
        });

        barisBaru.querySelector('.btn-hapus-baris').removeAttribute('disabled');
        containerBaris.appendChild(barisBaru);
        barisIndex++;
    });

    // 3. Logika Utama: Fetch API Dropdown Berantai Saat Produk Dipilih
    containerBaris.addEventListener('change', function(e) {
        
        // JIKA DROPDOWN PRODUK DIUBAH
        if (e.target.classList.contains('select-produk')) {
            const produkId = e.target.value;
            const baris = e.target.closest('.baris-job');
            const selectVarian = baris.querySelector('.select-varian');

            if (!produkId) {
                selectVarian.innerHTML = '<option value="">-- Pilih Varian --</option>';
                selectVarian.setAttribute('disabled', 'disabled');
                return;
            }

            // Panggil API Varian sesuai ID produk
            fetch(`/api/produk/${produkId}/varian`)
                .then(response => response.json())
                .then(data => {
                    selectVarian.innerHTML = '<option value="">-- Pilih Varian --</option>';
                    
                    if (data.length > 0) {
                        data.forEach(varian => {
                            selectVarian.innerHTML += `<option value="${varian.id}">${varian.warna} - Sizing: ${varian.ukuran} (Stok: ${varian.stok_tersedia ?? 0})</option>`;
                        });
                        selectVarian.removeAttribute('disabled');
                    } else {
                        selectVarian.innerHTML = '<option value="">Belum ada data varian</option>';
                        selectVarian.setAttribute('disabled', 'disabled');
                    }
                })
                .catch(error => {
                    console.error('Gagal memuat varian:', error);
                    selectVarian.innerHTML = '<option value="">Gagal memuat data</option>';
                    selectVarian.setAttribute('disabled', 'disabled');
                });
        }

        // JIKA DROPDOWN IS_CUSTOM DIUBAH
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
                wrapperCustom.querySelector('input').value = ''; // Hanya mereset input catatan_custom
            }
        }
    });

    // 4. Tombol Hapus Baris
    containerBaris.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-hapus-baris')) {
            e.target.closest('.baris-job').remove();
        }
    });
});
</script>
@endsection