@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Form Buat Nota Produksi</h5>
                <a href="{{ route('nota_production.index') }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
            <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('nota_production.store') }}" method="POST">
                @csrf

                <h5 class="text-secondary border-bottom pb-2 mb-3">Informasi Utama Nota</h5>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold text-dark">Pilih Penjahit / PIC Produksi</label>
                        <select name="penjahit_id" class="form-select" required>
                            <option value="">-- Pilih Penjahit --</option>
                            @foreach($penjahits as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->alamat }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold text-dark">Biaya Kirim Bahan (Ongkir)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">Rp</span>
                            <input type="number" name="ongkir_kirim_bahan" class="form-control" value="0" min="0" required>
                        </div>
                        <small class="text-muted small">Biaya transportasi pengiriman bahan baku ke penjahit</small>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mb-3">📋 Pilih Antrean Job Order Yang Mau Dikerjakan Hari Ini</h5>
                
                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-bordered align-middle mb-0" style="min-width: 1200px;">
                        <thead class="table-dark text-nowrap">
                            <tr>
                                <th style="width: 5%; text-align: center;">Pilih</th>
                                <th style="width: 15%;">No. Order Induk</th>
                                <th style="width: 25%;">Nama Produk & Detail Varian</th>
                                <th style="width: 10%; text-align: center;">Qty (Pcs)</th>
                                <th style="width: 15%;">Biaya Jasa / Upah Penjahit</th>
                                <th style="width: 15%; text-align: center;">Tipe Alokasi</th>
                                <th style="width: 15%;">Spesifikasi / Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jobOrders as $job)
                                @php
                                    $isChecked = is_array(old('job_order_ids')) && in_array($job->id, old('job_order_ids'));
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="job_order_ids[]" value="{{ $job->id }}" class="form-check-input check-job" style="transform: scale(1.2);" data-job-id="{{ $job->id }}" {{ $isChecked ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace fs-6">{{ $job->nota_order->no_order }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark d-block mb-1">{{ $job->produk_varian->produk->nama ?? 'Produk' }}</span>
                                        <small class="text-secondary bg-light px-2 py-1 rounded border d-inline-block">
                                            Varian: {{ $job->produk_varian->warna }} - {{ $job->produk_varian->ukuran }}
                                        </small>
                                    </td>
                                    <td class="fw-bold text-center fs-5 text-primary">{{ $job->jumlah }}</td>
                                    
                                    {{-- 🛠️ KOLOM BIAYA JASA YANG DIKEMBALIKAN & DI-RENAME 🛠️ --}}
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-muted small">Rp</span>
                                            <input type="number" name="biaya_jasa[{{ $job->id }}]" class="form-control input-biaya-jasa" placeholder="Upah per pcs" min="0" required {{ !$isChecked ? 'disabled' : '' }} value="{{ old('biaya_jasa.' . $job->id) }}">
                                        </div>
                                        <small class="text-muted" style="font-size: 0.75rem;">*Isi tarif ongkos jahit per satuan baju</small>
                                    </td>

                                    <td class="text-center">
                                        @if($job->is_custom)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1.5 w-100small">
                                                <i class="bi bi-person-fill"></i> Custom Order
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1.5 w-100small">
                                                <i class="bi bi-shop"></i> Stok Toko
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($job->is_custom)
                                            <div class="mb-1 text-dark fw-semibold small" style="font-size: 0.8rem;">
                                                Pemesan: <span class="text-primary">{{ $job->nota_order->customer->nama ?? 'Pelanggan' }}</span>
                                            </div>
                                            <div class="p-1 border rounded bg-light-subtle text-danger" style="font-size: 0.75rem; line-height: 1.2;">
                                                <strong>Req:</strong> {{ $job->catatan_custom ?? 'Tidak ada catatan' }}
                                            </div>
                                        @else
                                            <span class="text-muted small italic">Standar Reguler</span>
                                        @endif
                                    </td>
                                </tr>

                                @if($job->is_custom)
                                <tr class="ekstra-bahan-row" id="ekstra-bahan-row-{{ $job->id }}" style="{{ !$isChecked ? 'display: none;' : '' }}">
                                    <td></td>
                                    <td colspan="6" class="p-3 bg-light-subtle">
                                        <div class="border rounded p-3 bg-white shadow-sm">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="fw-bold text-secondary mb-0">
                                                    <i class="fa-solid fa-square-plus text-primary me-1"></i> Bahan Baku Ekstra (Kustomisasi) - Job Order #{{ $job->id }}
                                                </h6>
                                                <button type="button" class="btn btn-outline-primary btn-sm add-ekstra-row" data-job-id="{{ $job->id }}">
                                                    <i class="fa-solid fa-plus me-1"></i> Tambah Bahan Baku Ekstra
                                                </button>
                                            </div>
                                            
                                            <!-- Container for extra materials list -->
                                            <div id="ekstra-bahan-container-{{ $job->id }}">
                                                @php
                                                    $oldBahanIds = old('ekstra_bahan_id.' . $job->id, []);
                                                    $oldJumlahs = old('ekstra_jumlah.' . $job->id, []);
                                                    if (empty($oldBahanIds)) {
                                                        $oldBahanIds = [''];
                                                    }
                                                @endphp
                                                @foreach($oldBahanIds as $index => $oldBahanId)
                                                    @php
                                                        $oldJumlah = $oldJumlahs[$index] ?? '';
                                                    @endphp
                                                    <div class="row g-2 mb-2 align-items-center ekstra-row">
                                                        <div class="col-md-7">
                                                            <select name="ekstra_bahan_id[{{ $job->id }}][]" class="form-select form-select-sm select-bahan-ekstra" {{ !$isChecked ? 'disabled' : '' }}>
                                                                <option value="">-- Pilih Bahan Baku --</option>
                                                                @foreach($allBahanBaku as $b)
                                                                    <option value="{{ $b->id }}" data-satuan="{{ $b->satuan }}" {{ $oldBahanId == $b->id ? 'selected' : '' }}>
                                                                        {{ $b->nama }} (Stok: {{ (float)$b->stok_total }} {{ $b->satuan }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" step="0.01" min="0" name="ekstra_jumlah[{{ $job->id }}][]" class="form-control form-control-sm input-jumlah-ekstra" placeholder="Qty" {{ !$isChecked ? 'disabled' : '' }} value="{{ $oldJumlah }}">
                                                                <span class="input-group-text bg-light text-muted satuan-label" style="font-size: 0.75rem;">-</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 text-end">
                                                            <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-ekstra-row">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bi bi-exclamation-circle"></i> Tidak ada antrean job order aktif saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-start gap-2 mt-4">
                    <a href="{{ route('nota_production.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Naikkan Cetak / Mulai Produksi</button>
                </div>
            </form>

        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Function to enable/disable row inputs based on checkbox state
    function toggleJobRow(checkbox) {
        const jobId = checkbox.getAttribute('data-job-id');
        const isChecked = checkbox.checked;
        
        // 1. Toggle biaya_jasa input
        const biayaInput = document.querySelector('input[name="biaya_jasa[' + jobId + ']"]');
        if (biayaInput) {
            biayaInput.disabled = !isChecked;
            if (isChecked) {
                biayaInput.setAttribute('required', 'required');
            } else {
                biayaInput.removeAttribute('required');
            }
        }
        
        // 2. Toggle ekstra bahan row visibility & its inputs
        const ekstraRow = document.getElementById('ekstra-bahan-row-' + jobId);
        if (ekstraRow) {
            ekstraRow.style.display = isChecked ? '' : 'none';
            
            // Toggle all inputs inside the ekstra row
            ekstraRow.querySelectorAll('select, input').forEach(function (input) {
                input.disabled = !isChecked;
            });
        }
    }

    // Attach event listeners to job checkboxes
    document.querySelectorAll('.check-job').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            toggleJobRow(this);
        });
        
        // Run toggle on load to align with initial state (old input or defaults)
        toggleJobRow(checkbox);
    });

    // Handle dynamic addition of extra materials
    document.querySelectorAll('.add-ekstra-row').forEach(function (button) {
        button.addEventListener('click', function () {
            const jobId = this.getAttribute('data-job-id');
            const container = document.getElementById('ekstra-bahan-container-' + jobId);
            if (!container) return;

            // Find first row to use as template
            const firstRow = container.querySelector('.ekstra-row');
            if (!firstRow) return;

            // Clone row
            const newRow = firstRow.cloneNode(true);

            // Reset inputs in the new row
            const selectEl = newRow.querySelector('.select-bahan-ekstra');
            const inputEl = newRow.querySelector('.input-jumlah-ekstra');
            const labelEl = newRow.querySelector('.satuan-label');

            if (selectEl) {
                selectEl.value = '';
                selectEl.disabled = false;
            }
            if (inputEl) {
                inputEl.value = '';
                inputEl.disabled = false;
            }
            if (labelEl) labelEl.textContent = '-';

            // Setup event listeners for the new row's elements
            setupRowEventListeners(newRow);

            // Append cloned row
            container.appendChild(newRow);
        });
    });

    // Handle dynamic removal of extra materials
    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-ekstra-row')) {
            const btn = e.target.closest('.remove-ekstra-row');
            const row = btn.closest('.ekstra-row');
            const container = row.parentNode;
            
            // Check how many rows are currently in this container
            const allRows = container.querySelectorAll('.ekstra-row');
            if (allRows.length > 1) {
                row.remove();
            } else {
                // If it's the last row, just clear it instead of removing
                const selectEl = row.querySelector('.select-bahan-ekstra');
                const inputEl = row.querySelector('.input-jumlah-ekstra');
                const labelEl = row.querySelector('.satuan-label');
                if (selectEl) selectEl.value = '';
                if (inputEl) inputEl.value = '';
                if (labelEl) labelEl.textContent = '-';
            }
        }
    });

    // Helper to update the unit label based on selected option
    function updateUnitLabel(selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const labelEl = selectEl.closest('.ekstra-row').querySelector('.satuan-label');
        if (labelEl) {
            if (selectedOption && selectedOption.getAttribute('data-satuan')) {
                labelEl.textContent = selectedOption.getAttribute('data-satuan');
            } else {
                labelEl.textContent = '-';
            }
        }
    }

    // Set up change event listeners for select inputs
    function setupRowEventListeners(row) {
        const selectEl = row.querySelector('.select-bahan-ekstra');
        if (selectEl) {
            selectEl.addEventListener('change', function () {
                updateUnitLabel(this);
            });
        }
    }

    // Initialize existing rows
    document.querySelectorAll('.ekstra-row').forEach(function (row) {
        setupRowEventListeners(row);
        
        // Also trigger initial unit label update
        const selectEl = row.querySelector('.select-bahan-ekstra');
        if (selectEl && selectEl.value) {
            updateUnitLabel(selectEl);
        }
    });
});
</script>
@endsection