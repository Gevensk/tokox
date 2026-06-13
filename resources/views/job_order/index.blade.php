@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <form action="{{ route('job_order.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold">📦 Pilih Antrean Pesanan untuk Dilempar ke Penjahit</h5>
                        <span class="badge bg-primary" id="counter-terpilih">0 Item Terpilih</span>
                    </div>
                    <div class="card-body p-0">
                        @if(session('error'))
                            <div class="alert alert-danger m-3">{{ session('error') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th width="40" class="text-center">
                                            <input type="checkbox" id="check-all" class="form-check-input">
                                        </th>
                                        <th>No. Order / Pelanggan</th>
                                        <th>Nama Produk Varian</th>
                                        <th class="text-center">Qty Pesanan</th>
                                        <th>Spesifikasi / Custom</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($antreanJobs as $job)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="job_ids[]" value="{{ $job->id }}" class="form-check-input checkbox-job" data-qty="{{ $job->jumlah }}">
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $job->nota_order->no_order }}</span>
                                            <div class="small text-muted">
                                                {{ $job->nota_order->tipe_order === 'Customer' ? $job->nota_order->customer->nama : 'Stok Internal' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $job->produk_varian->produk->nama }}</span>
                                            <div class="small text-secondary">Warna: {{ $job->produk_varian->warna }} | Size: {{ $job->produk_varian->ukuran }}</div>
                                        </td>
                                        <td class="text-center fw-bold text-dark fs-5">{{ $job->jumlah }} pcs</td>
                                        <td>
                                            @if($job->is_custom)
                                                <span class="badge bg-warning text-dark mb-1">Custom Size</span>
                                                <div class="small text-muted">LD: {{ $job->ld_req ?? '-' }} | PB: {{ $job->pb_req ?? '-' }}</div>
                                                <div class="small text-danger italic">"{{ $job->catatan_custom }}"</div>
                                            @else
                                                <span class="badge bg-light text-secondary border">Ukuran Standar</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            🎉 Semua antrean order sudah dialokasikan ke Surat Jalan kerja!
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 fw-bold">📄 Detail Surat Jalan (SPK)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Vendor Penjahit (CMT)</label>
                            <select name="penjahit_id" class="form-select" required>
                                <option value="">-- Pilih Penjahit --</option>
                                @foreach($penjahits as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->wilayah ?? 'Luar' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal Pengiriman Kain</label>
                            <input type="date" name="tgl_order" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Ongkos Kirim Logistik Bahan (Rp)</label>
                            <input type="number" name="ongkir_kirim_bahan" class="form-control" placeholder="Contoh: 50000" min="0" value="0" required>
                            <div class="form-text text-muted">Biaya logistik ini akan otomatis dibagi rata ke dalam beban HPP setiap helai pakaian secara proporsional.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Catatan untuk Penjahit</label>
                            <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Jangan sampai motif terbalik, pengerjaan prioritas utama..."></textarea>
                        </div>

                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Total Qty Terpilih:</span>
                            <span id="total-qty-view" class="fw-bold fs-5 text-dark">0 pcs</span>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold fs-5 mt-2" id="btn-submit-spk" disabled>
                            🚀 Terbitkan Surat Jalan Kerja
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('check-all');
    const checkboxes = document.querySelectorAll('.checkbox-job');
    const counterTerpilih = document.getElementById('counter-terpilih');
    const totalQtyView = document.getElementById('total-qty-view');
    const btnSubmit = document.getElementById('btn-submit-spk');

    function hitungKalkulasi() {
        let count = 0;
        let totalQty = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                count++;
                totalQty += parseInt(cb.getAttribute('data-qty'));
            }
        });

        counterTerpilih.textContent = `${count} Item Terpilih`;
        totalQtyView.textContent = `${totalQty} pcs`;
        
        // Aktifkan tombol submit hanya jika ada minimal 1 item baju yang dicentang
        if (count > 0) {
            btnSubmit.removeAttribute('disabled');
        } else {
            btnSubmit.setAttribute('disabled', 'disabled');
        }
    }

    checkAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        hitungKalkulasi();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            hitungKalkulasi();
            // Jika ada satu checkbox dilepas, hilangkan status checked master di atas
            if (!this.checked) checkAll.checked = false;
        });
    });
});
</script>
@endsection