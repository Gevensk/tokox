@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Buat Resep (Bill of Material)</h2>
        <a href="{{ route('bill_of_material.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('bill_of_material.store') }}" method="POST">
                @csrf
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="fw-bold">Pilih Varian Produk (Induk)</label>
                        <select name="produk_varian_id" class="form-control select2" required>
                            <option value="">-- Cari Varian Produk --</option>
                            @foreach($varians as $v)
                                <option value="{{ $v->id }}">
                                    {{ $v->produk->nama }} - {{ $v->warna }} ({{ $v->ukuran }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih satu varian yang ingin dibuatkan resepnya.</small>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Komposisi Bahan Baku</h4>
                    <button type="button" id="addRow" class="btn btn-success btn-sm">+ Tambah Bahan</button>
                </div>

                <table class="table table-hover table-bordered" id="tableBoM">
                    <thead class="bg-light">
                        <tr>
                            <th>Nama Bahan Baku</th>
                            <th width="250">Jumlah Kebutuhan</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td>
                                <select name="items[0][bahan_baku_id]" class="form-control select2" required>
                                    <option value="">-- Cari Bahan Baku --</option>
                                    @foreach($bahan_bakus as $b)
                                        <option value="{{ $b->id }}">{{ $b->nama }} ({{ $b->satuan }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="number" name="items[0][jumlah]" class="form-control" step="any" placeholder="0.00" required>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">Simpan Resep BoM</button>
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
            placeholder: "-- Pilih/Ketik --",
            allowClear: true
        });
    }

    $(document).ready(function() {
        initSelect2();

        // Otomatis fokus ke kolom pencarian saat Select2 dibuka
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });
    });

    let rowIdx = 1;
    document.getElementById('addRow').addEventListener('click', function() {
        let table = document.querySelector('#tableBoM tbody');
        let newRow = `
            <tr class="item-row">
                <td>
                    <select name="items[${rowIdx}][bahan_baku_id]" class="form-control select2" required>
                        <option value="">-- Cari Bahan Baku --</option>
                        @foreach($bahan_bakus as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }} ({{ $b->satuan }})</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${rowIdx}][jumlah]" class="form-control" step="any" placeholder="0.00" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-row">Hapus</button>
                </td>
            </tr>`;
        table.insertAdjacentHTML('beforeend', newRow);
        initSelect2(); // Inisialisasi ulang Select2 untuk baris baru
        rowIdx++;
    });

    document.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-row')) {
            e.target.closest('.item-row').remove();
        }
    });

    document.querySelector('form').addEventListener('submit', function(e) {
    let selectedBahan = [];
    let isDuplicate = false;

    document.querySelectorAll('select[name*="bahan_baku_id"]').forEach(select => {
        if (selectedBahan.includes(select.value)) {
            isDuplicate = true;
        }
        selectedBahan.push(select.value);
    });

    if (isDuplicate) {
        e.preventDefault();
        alert('Ada bahan baku yang sama terpilih lebih dari satu kali. Tolong gabungkan jumlahnya saja.');
    }
    });
</script>
@endsection