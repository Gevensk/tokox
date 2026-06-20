@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Mengatur agar Select2 cocok dengan gaya Bootstrap 5 dan tetap lega */
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>

<div class="container-fluid px-4 py-4">
    <div class="card shadow-sm border-0">
        
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold">🧾 Input Nota Pembelian Baru</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('nota_pembelian.store') }}" method="POST">
                @csrf
                
                <h5 class="text-secondary border-bottom pb-2 mb-3">Informasi Utama Nota</h5>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold text-dark">Supplier</label>
                        <select name="supplier_id" class="form-control select2" required>
                            <option value="">Cari Supplier...</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold text-dark">Tanggal Beli</label>
                        <input type="datetime-local" name="tgl_beli" class="form-control" 
                               value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold text-dark">Ongkir (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">Rp</span>
                            <input type="number" name="ongkir" id="ongkir" class="form-control text-end" placeholder="0" min="0">
                        </div>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                    <span>📦 Detail Bahan Baku</span>
                    <button type="button" id="addRow" class="btn btn-success btn-sm px-3 fw-semibold">Tambah Baris</button>
                </h5>

                <div class="table-responsive shadow-sm rounded mb-4">
                    <table class="table table-bordered align-middle mb-0" id="tableBahan" style="min-width: 1100px;">
                        <thead class="table-dark text-nowrap">
                            <tr>
                                <th>Bahan Baku</th>
                                <th style="width: 12%;" class="text-center">Jumlah (Qty)</th>
                                <th style="width: 18%;">Harga Satuan</th>
                                <th style="width: 20%;">Subtotal</th>
                                <th style="width: 10%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="item-row">
                                <td>
                                    <select name="items[0][bahan_baku_id]" class="form-control select2" required>
                                        <option value="">Cari Bahan...</option>
                                        @foreach($bahan_baku as $b)
                                            <option value="{{ $b->id }}">{{ $b->nama }} ({{ $b->satuan }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[0][jumlah]" class="form-control qty text-center" step="any" placeholder="0" required>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted small">Rp</span>
                                        <input type="number" name="items[0][harga_satuan]" class="form-control harga text-end" placeholder="0" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted small">Rp</span>
                                        <input type="number" class="form-control subtotal text-end bg-light" readonly placeholder="0">
                                    </div>
                                </td>
                                <td class="text-center text-muted fw-light">—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="row justify-content-end text-end g-3">
                    <div class="col-md-5 col-lg-4">
                        <div class="row align-items-center mb-2">
                            <label class="col-6 col-form-label text-muted fw-semibold small">Total Harga Bahan:</label>
                            <div class="col-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted small">Rp</span>
                                    <input type="text" id="display_total_text" class="form-control text-end bg-white border-start-0 fw-semibold" readonly value="0">
                                    <input type="hidden" name="total_harga_bahan" id="total_harga_bahan" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center border-top pt-2">
                            <label class="col-6 col-form-label text-dark fw-bold">Subtotal Akhir:</label>
                            <div class="col-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-primary fw-bold">Rp</span>
                                    <input type="text" id="display_total_bayar" class="form-control text-end bg-white border-start-0 fw-bold text-primary" readonly value="0">
                                    <input type="hidden" name="total_bayar" id="total_bayar" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end border-top pt-3 mt-4">
                    <a href="{{ route('nota_pembelian.index') }}" class="btn btn-secondary px-4 me-2">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnSubmitForm">Simpan Pembelian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function initSelect2() {
        $('.select2').select2({
            width: '100%',
            placeholder: "Cari...",
            allowClear: true
        });
    }

    $(document).ready(function() {
        initSelect2();
    });

    let rowIdx = 1;

    // Tambah Baris Dinamis
    document.getElementById('addRow').addEventListener('click', function() {
        let table = document.querySelector('#tableBahan tbody');
        let newRow = `
            <tr class="item-row">
                <td>
                    <select name="items[${rowIdx}][bahan_baku_id]" class="form-control select2" required>
                        <option value="">Cari Bahan...</option>
                        @foreach($bahan_baku as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }} ({{ $b->satuan }})</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="items[${rowIdx}][jumlah]" class="form-control qty text-center" step="any" placeholder="0" required></td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted small">Rp</span>
                        <input type="number" name="items[${rowIdx}][harga_satuan]" class="form-control harga text-end" placeholder="0" required>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted small">Rp</span>
                        <input type="number" class="form-control subtotal text-end bg-light" readonly placeholder="0">
                    </div>
                </td>
                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row px-2">Hapus</button></td>
            </tr>`;
        table.insertAdjacentHTML('beforeend', newRow);
        
        initSelect2();
        rowIdx++;
    });

    // Logika Hitung Otomatis
    document.addEventListener('input', function(e) {
        if(e.target.classList.contains('qty') || e.target.classList.contains('harga') || e.target.id === 'ongkir') {
            let row = e.target.closest('.item-row');
            if(row) {
                let qty = parseFloat(row.querySelector('.qty').value) || 0;
                let harga = parseFloat(row.querySelector('.harga').value) || 0;
                row.querySelector('.subtotal').value = qty * harga;
            }
            calculateTotal();
        }
    });

    function calculateTotal() {
        let totalsBahan = 0;
        document.querySelectorAll('.subtotal').forEach(el => {
            totalsBahan += parseFloat(el.value || 0);
        });

        let ongkir = parseFloat(document.getElementById('ongkir').value) || 0;
        let totalBayar = totalsBahan + ongkir;

        document.getElementById('display_total_text').value = totalsBahan.toLocaleString('id-ID');
        document.getElementById('display_total_bayar').value = totalBayar.toLocaleString('id-ID');

        document.getElementById('total_harga_bahan').value = totalsBahan;
        document.getElementById('total_bayar').value = totalBayar;
    }

    // Hapus Baris
    document.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-row')) {
            e.target.closest('.item-row').remove();
            calculateTotal();
        }
    });
</script>
@endsection