@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Mengatur agar Select2 cocok dengan gaya Bootstrap */
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
    }
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Input Nota Pembelian</h2>
        <a href="{{ route('nota_pembelian.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('nota_pembelian.store') }}" method="POST">
                @csrf
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="fw-bold">Supplier</label>
                        <select name="supplier_id" class="form-control select2" required>
                            <option value="">Cari Supplier...</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Tanggal Beli</label>
                        <input type="datetime-local" name="tgl_beli" class="form-control" 
                               value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Ongkir (Opsional)</label>
                        <input type="number" name="ongkir" id="ongkir" class="form-control" placeholder="Masukkan ongkir...">
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Detail Bahan Baku</h4>
                    <button type="button" id="addRow" class="btn btn-success btn-sm">Tambah Baris</button>
                </div>

                <table class="table table-bordered" id="tableBahan">
                    <thead class="thead-light">
                        <tr>
                            <th>Bahan Baku</th>
                            <th width="150">Jumlah (Qty)</th>
                            <th width="200">Harga Satuan</th>
                            <th width="200">Subtotal</th>
                            <th width="100">Aksi</th>
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
                            <td><input type="number" name="items[0][jumlah]" class="form-control qty" step="any" placeholder="0" required></td>
                            <td><input type="number" name="items[0][harga_satuan]" class="form-control harga" placeholder="0" required></td>
                            <td><input type="number" class="form-control subtotal" readonly placeholder="0"></td>
                            <td class="text-center">-</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="row mt-4">
                    <div class="col-md-8 text-right">
                        <h6 class="mt-2 text-muted">Total Harga Bahan:</h6>
                        <h2 class="mt-3 fw-bold">Subtotal:</h2>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="text" id="display_total_text" class="form-control" readonly value="0">
                            <input type="hidden" name="total_harga_bahan" id="total_harga_bahan" value="0">
                        </div>

                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="text" id="display_total_bayar" class="form-control fw-bold" style="font-size: 1.8rem; height: auto;" readonly value="0">
                            <input type="hidden" name="total_bayar" id="total_bayar" value="0">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg shadow-sm">Simpan Pembelian</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Fungsi untuk mengaktifkan Select2 pada elemen
    function initSelect2() {
        $('.select2').select2({
            width: '100%',
            placeholder: "-- Pilih --",
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
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="items[${rowIdx}][jumlah]" class="form-control qty" step="any" placeholder="0" required></td>
                <td><input type="number" name="items[${rowIdx}][harga_satuan]" class="form-control harga" placeholder="0" required></td>
                <td><input type="number" class="form-control subtotal" readonly placeholder="0"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button></td>
            </tr>`;
        table.insertAdjacentHTML('beforeend', newRow);
        
        // Penting: Aktifkan Select2 untuk baris baru
        initSelect2();
        rowIdx++;
    });

    // Logika Hitung Otomatis (Tetap sama)
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