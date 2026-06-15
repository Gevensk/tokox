@extends('layouts.app') 

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold">🧾 Edit Nota Order / Pesanan</h5>
        </div>
        <div class="card-body">
            
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('nota_order.update', $notaOrder->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" name="harga_satuan_items" id="harga_satuan_items" value="{{ (int)$notaOrder->harga_satuan_items }}">

                <h5 class="text-secondary border-bottom pb-2 mb-3">Informasi Utama Nota (No. Order: {{ $notaOrder->no_order }})</h5>
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Tipe Order</label>
                        <select name="tipe_order" id="tipe_order" class="form-select" required>
                            <option value="Customer" {{ $notaOrder->tipe_order == 'Customer' ? 'selected' : '' }}>Customer (Pesanan Luar)</option>
                            <option value="Internal" {{ $notaOrder->tipe_order == 'Internal' ? 'selected' : '' }}>Internal (Stok Toko Sendiri)</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 {{ $notaOrder->tipe_order == 'Internal' ? 'd-none' : '' }}" id="wrapper_customer">
                        <label class="form-label fw-semibold">Pilih Customer</label>
                        <select name="customer_id" class="form-select" {{ $notaOrder->tipe_order == 'Customer' ? 'required' : '' }}>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ $notaOrder->customer_id == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Tanggal Deadline</label>
                        <input type="date" name="tgl_deadline" class="form-control" value="{{ \Carbon\Carbon::parse($notaOrder->tgl_deadline)->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row mb-4 bg-light p-3 rounded border">
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-bold text-dark">Total Keseluruhan Tagihan (Rp)</label>
                        <input type="number" name="total_harga" id="total_harga" class="form-control bg-white fw-bold text-primary fs-5" value="{{ (int)$notaOrder->total_harga }}" readonly required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-bold text-dark">Nominal Uang DP Masuk (Rp)</label>
                        <input type="number" name="nominal_dp_masuk" class="form-control text-success fw-bold fs-5" value="{{ (int)$notaOrder->nominal_dp_masuk }}" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-bold text-dark">Status Bayar</label>
                        <select name="status_bayar" class="form-select fw-semibold" required>
                            <option value="Belum Lunas" {{ $notaOrder->status_bayar == 'Belum Lunas' ? 'selected' : '' }}>DP / Belum Lunas</option>
                            <option value="Lunas" {{ $notaOrder->status_bayar == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                    <span>🛠️ Detail Baris Pesanan (Job Order)</span>
                    <button type="button" id="btn-tambah-baris" class="btn btn-sm btn-success px-3">+ Tambah Produk</button>
                </h5>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="tabel-job-order">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 20%;">Pilih Produk</th>
                                <th style="width: 20%;">Pilih Varian</th>
                                <th style="width: 8%;" class="text-center">Qty</th>
                                <th style="width: 14%;">Harga Jual Satuan (Rp)</th>
                                <th style="width: 10%;">Tipe</th>
                                <th style="width: 23%;">Spesifikasi Kustomisasi</th>
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
                                            {{ $job->produk_varian->warna }} - [Sizing: {{ $job->produk_varian->ukuran }}]
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][jumlah]" class="form-control input-qty text-center" min="1" value="{{ $job->jumlah }}" required>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][harga_satuan_jual]" class="form-control input-harga" placeholder="0" min="0" value="{{ $notaOrder->harga_satuan_items > 0 ? (int)$notaOrder->harga_satuan_items : (int)$job->biaya_jasa_satuan }}" required>
                                </td>
                                <td>
                                    <select name="items[{{ $index }}][is_custom]" class="form-select select-is-custom">
                                        <option value="0" {{ $job->is_custom == 0 ? 'selected' : '' }}>Standard</option>
                                        <option value="1" {{ $job->is_custom == 1 ? 'selected' : '' }}>Custom</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="wrapper-fitur-custom {{ $job->is_custom == 1 ? '' : 'd-none' }}">
                                        <input type="text" name="items[{{ $index }}][catatan_custom]" class="form-control form-control-sm mb-2" value="{{ $job->catatan_custom }}" placeholder="Contoh: Sablon/Bordir">
                                        
                                        <label class="form-label small text-muted mb-0 fw-semibold">Upload Foto Desain:</label>
                                        
                                        @if($job->foto_desain)
                                            <div class="my-2 p-1 border rounded bg-white text-center d-inline-block">
                                                <a href="{{ asset('storage/desain_order/' . $job->foto_desain) }}" target="_blank" title="Klik untuk memperbesar">
                                                    <img src="{{ asset('storage/desain_order/' . $job->foto_desain) }}" alt="Desain Lama" class="img-thumbnail" style="max-height: 80px; width: auto; object-fit: contain; display: block;">
                                                </a>
                                                <span class="badge bg-secondary mt-1" style="font-size: 9px;">Gambar Saat Ini</span>
                                            </div>
                                            <small class="text-muted d-block mb-2" style="font-size: 10px; line-height: 1.2;">*Biarkan kosong jika tidak ingin mengganti gambar lama.</small>
                                        @endif
                                        
                                        <input type="file" name="items[{{ $index }}][foto_desain]" class="form-control form-control-sm input-foto-desain" accept="image/*">
                                    </div>
                                    <span class="text-muted small standar-label {{ $job->is_custom == 1 ? 'd-none' : '' }}">Mengikuti Spek Standard BoM</span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger btn-hapus-baris" {{ $notaOrder->job_orders->count() <= 1 ? 'disabled' : '' }}>×</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('nota_order.index') }}" class="btn btn-secondary px-3 me-2">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan Perubahan Order</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Memulai hitungan indeks kelanjutan dari total data yang ada
    let barisIndex = {{ $notaOrder->job_orders->count() }};
    const containerBaris = document.getElementById('container-baris-order');
    const btnTambah = document.getElementById('btn-tambah-baris');
    const tipeOrder = document.getElementById('tipe_order');
    const wrapperCustomer = document.getElementById('wrapper_customer');

    // 1. Ajax Ambil Data Varian untuk Data Baris yang Sudah Ada Sejak Awal Load Halaman
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
                            selectVarian.innerHTML += `<option value="${varian.id}" ${isSelected}>${varian.warna} - [Sizing: ${varian.ukuran}]</option>`;
                        });
                        selectVarian.removeAttribute('disabled');
                    }
                });
        }
    });

    // 2. Fungsi Hitung Total Tagihan Otomatis
    function hitungGrandTotal() {
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

    // 3. PERBAIKAN AKSI TAMBAH BARIS BARU (Fix Cloning Issues)
    btnTambah.addEventListener('click', function() {
        const barisPertama = containerBaris.querySelector('.baris-job');
        const barisBaru = barisPertama.cloneNode(true);
        
        // Atur index baru ke attribute pembungkus
        barisBaru.setAttribute('data-index', barisIndex);
        
        // Bersihkan pembungkus preview gambar lama milik baris pertama jika ada
        const oldPreview = barisBaru.querySelector('.my-1');
        if(oldPreview) oldPreview.remove();

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
        
        // Reset element Select
        barisBaru.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        
        // Kembalikan state select varian ke default kosong & disabled
        const selectVarianBaru = barisBaru.querySelector('.select-varian');
        selectVarianBaru.innerHTML = '<option value="">-- Pilih Varian --</option>';
        selectVarianBaru.setAttribute('disabled', 'disabled');
        
        // Reset area kustomisasi
        barisBaru.querySelector('.wrapper-fitur-custom').classList.add('d-none');
        barisBaru.querySelector('.standar-label').classList.remove('d-none');

        // Ganti index item name array agar dinamis (misal: items[0] -> items[1])
        barisBaru.querySelectorAll('[name]').forEach(element => {
            const nameAttr = element.getAttribute('name');
            if (nameAttr) {
                const updatedName = nameAttr.replace(/items\[\d+\]/, `items[${barisIndex}]`);
                element.setAttribute('name', updatedName);
            }
        });

        barisBaru.querySelector('.btn-hapus-baris').removeAttribute('disabled');
        containerBaris.appendChild(barisBaru);
        
        barisIndex++;
        validasiTombolHapus();
        hitungGrandTotal(); // Hitung ulang total setelah baris default masuk
    });

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

    // 4. Event Delegation untuk Input & Select Dinamis di dalam Tabel
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

    // Listener untuk mendeteksi perubahan ketikan nominal qty / harga satuan
    containerBaris.addEventListener('input', function(e) {
        if (e.target.classList.contains('input-qty') || e.target.classList.contains('input-harga')) {
            hitungGrandTotal();
        }
    });

    // Listener Hapus Baris
    containerBaris.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-hapus-baris')) {
            const semuaBaris = containerBaris.querySelectorAll('.baris-job');
            if(semuaBaris.length > 1) {
                e.target.closest('.baris-job').remove();
                hitungGrandTotal();
                validasiTombolHapus();
            }
        }
    });

    // Jalankan kalkulasi pertama kali saat halaman dibuka
    hitungGrandTotal();
    validasiTombolHapus();
});
</script>
@endsection