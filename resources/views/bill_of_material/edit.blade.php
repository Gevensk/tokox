@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Resep (Bill of Material)</h2>
        <a href="{{ route('bill_of_material.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('bill_of_material.update', $varian->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="fw-bold">Varian Produk (Induk)</label>
                        <input type="text" class="form-control bg-light" 
                               value="{{ $varian->produk->nama }} - {{ $varian->warna }} ({{ $varian->ukuran }})" disabled>
                        <small class="text-muted">Nama varian produk tidak dapat diubah.</small>
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
                                <button type="button" class="btn btn-outline-danger btn-sm remove-row">Hapus</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">Simpan Perubahan</button>
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
            allowClear: true,
            // Fitur pencarian pintar case-insensitive
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

    // Indeks dilanjutkan dari jumlah resep yang ada agar tidak bentrok
    let rowIdx = {{ $current_bom->count() }};
    
    document.getElementById('addRow').addEventListener('click', function() {
        let table = document.querySelector('#tableBoM tbody');
        
        // REVISI: Tanda backslash (\) sebelum ${rowIdx} sudah dihapus total di bawah ini
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
        initSelect2(); 
        rowIdx++;
    });

    // Event handler untuk menghapus baris bahan
    document.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-row')) {
            // Validasi: Sisakan minimal 1 baris resep agar tidak kosong melompong
            if(document.querySelectorAll('.item-row').length > 1){
                e.target.closest('.item-row').remove();
            } else {
                alert('Resep BoM minimal harus memiliki 1 jenis bahan baku.');
            }
        }
    });

    // Validasi anti bahan baku ganda sebelum disubmit
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