@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="row justify-content-center">
    <div class="col-md-10"> {{-- Menggunakan col-md-10 agar ruang tabel bahan baku tetap luas --}}
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Form Edit Resep (Bill of Material)</h5>
                <a href="{{ route('bill_of_material.index') }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('bill_of_material.update', $varian->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Varian Produk (Induk)</label>
                        <input type="text" class="form-control bg-light" 
                               value="{{ $varian->produk->nama }} - {{ $varian->warna }} ({{ $varian->ukuran }})" disabled>
                        <div class="form-text text-muted">Nama varian produk tidak dapat diubah.</div>
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
                                @foreach($current_bom as $index => $bom)
                                <tr class="item-row">
                                    <td>
                                        <select name="items[{{ $index }}][bahan_baku_id]" class="form-control select2" required>
                                            <option value="">-- Cari Bahan Baku --</option>
                                            @foreach($bahan_bakus as $b)
                                                <option value="{{ $b->id }}" {{ $bom->bahan_baku_id == $b->id ? 'selected' : '' }}>
                                                    {{ $b->nama }} ({{ $b->satuan }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][jumlah]" class="form-control" 
                                               step="any" placeholder="0.00" value="{{ $bom->jumlah }}" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="{{ route('bill_of_material.index') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
            allowClear: true,
            matcher: function(params, data) {
                if ($.trim(params.term) === '') {
                    return data;
                }
                if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                    return data;
                }
                return null;
            }
        });
    }

    $(document).ready(function() {
        initSelect2();

        // Otomatis fokus ke kolom pencarian saat Select2 dibuka
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });
    });

    let rowIdx = {{ $current_bom->count() }};
    
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
        initSelect2(); 
        rowIdx++;
    });

    document.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-row')) {
            if(document.querySelectorAll('.item-row').length > 1){
                e.target.closest('.item-row').remove();
            } else {
                alert('Resep BoM minimal harus memiliki 1 jenis bahan baku.');
            }
        }
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        let selectedBahan = [];
        let isDuplicate = false;

        document.querySelectorAll('select[name*="bahan_baku_id"]').forEach(select => {
            if (select.value) {
                if (selectedBahan.includes(select.value)) {
                    isDuplicate = true;
                }
                selectedBahan.push(select.value);
            }
        });

        if (isDuplicate) {
            e.preventDefault();
            alert('Ada bahan baku yang sama terpilih lebih dari sekali. Silakan gabungkan jumlah kebutuhannya saja.');
        }
    });
</script>
@endsection