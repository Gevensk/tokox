@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="row justify-content-center">
    <div class="col-md-10"> {{-- col-md-10 agar area tabel dinamis tetap lega --}}
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Form Buat Resep (Bill of Material)</h5>
                <a href="{{ route('bill_of_material.index') }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <form action="{{ route('bill_of_material.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Varian Produk (Induk)</label>
                        <select name="produk_varian_id" class="form-control select2" required>
                            <option value="">-- Cari Varian Produk --</option>
                            @foreach($varians as $v)
                                <option value="{{ $v->id }}">
                                    {{ $v->produk->nama }} - {{ $v->warna }} ({{ $v->ukuran }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">Pilih satu varian yang ingin dibuatkan resepnya.</div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold">Komposisi Bahan Baku</h5>
                        <button type="button" id="addRow" class="btn btn-success btn-sm">+ Tambah Bahan</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tableBoM">
                            <thead>
                                <tr>
                                    <th>Nama Bahan Baku</th>
                                    <th style="width: 250px;">Jumlah Kebutuhan</th>
                                    <th style="width: 100px;" class="text-center">Aksi</th>
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
                                        <input type="number" name="items[0][jumlah]" class="form-control" step="any" placeholder="0.00" required>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-muted small">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="{{ route('bill_of_material.index') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Resep BoM</button>
                    </div>
                </form>
            </div>
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
                    <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
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
            if (select.value && selectedBahan.includes(select.value)) {
                isDuplicate = true;
            }
            if(select.value) {
                selectedBahan.push(select.value);
            }
        });

        if (isDuplicate) {
            e.preventDefault();
            alert('Ada bahan baku yang sama terpilih lebih dari satu kali. Tolong gabungkan jumlahnya saja.');
        }
    });
</script>
@endsection