@extends('layouts.app') {{-- Sesuaikan dengan nama layout induk skripsimu, misal layouts.admin atau layouts.main --}}

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-check"></i> Form Penerimaan Hasil Jadi & Quality Control (QC)</h5>
                    <span class="badge bg-light text-success fw-bold p-2">JOB-ORDER ID: {{ $job->id }}</span>
                </div>
                
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                <div><strong>Berhasil!</strong> {{ session('success') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                <div><strong>Sistem Menolak:</strong> {{ session('error') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <div class="alert alert-secondary border-0 mb-4">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-info-circle-fill"></i> Monitoring Progress Kerja Parsial Penjahit:</h6>
                        <div class="row text-center small">
                            <div class="col-4 border-end">
                                <span class="text-muted d-block">Target Total Pesanan</span>
                                <strong class="fs-6 text-dark">{{ $job->jumlah }} Pcs</strong>
                            </div>
                            <div class="col-4 border-end">
                                <span class="text-muted d-block">Sudah Masuk Gudang</span>
                                <strong class="fs-6 text-success">{{ $totalSudahDiterima }} Pcs</strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block">Sisa Batas Tagihan</span>
                                <strong class="fs-5 text-danger" id="sisa_wajib">{{ $sisaTanggungan }} Pcs</strong>
                            </div>
                        </div>
                        
                        @if($totalSudahDiterima > 0)
                        <div class="mt-2 pt-2 border-top text-muted text-center xsmall">
                            <i class="bi bi-clock-history"></i> Penjahit sudah pernah menyetor data sebelumnya. Form ini ditujukan untuk memproses batch/tahap berikutnya.
                        </div>
                        @endif
                    </div>

                    <form action="{{ route('hasil_produksi.store') }}" method="POST">
                        @csrf
                        {{-- Hidden Input penting untuk mengirimkan ID Job Order ke Controller --}}
                        <input type="hidden" name="job_order_id" value="{{ $job->id }}">

                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-calculator"></i> 1. Input Pembagian Kuantitas Hasil Pemeriksaan QC Batch Hari Ini:</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label text-success fw-bold">Layak / Lolos QC (Qty Accepted)</label>
                                <input type="number" id="qty_accepted" name="qty_accepted" class="form-control form-control-lg border-success text-center fw-bold text-success" min="0" value="0" required>
                                <div class="form-text text-success small">Akan menambah <strong>stok_tersedia</strong> di gudang.</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label text-warning fw-bold">Cacat Bahan (Qty Defect)</label>
                                <input type="number" id="qty_rejected_defect" name="qty_rejected_defect" class="form-control form-control-lg border-warning text-center fw-bold text-warning" min="0" value="0" required>
                                <div class="form-text text-warning small">Baju rusak total/cacat bahan baku bawaan.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-danger fw-bold">Perbaikan/Rework (Qty Rejected)</label>
                                <input type="number" id="qty_rejected" name="qty_rejected" class="form-control form-control-lg border-danger text-center fw-bold text-danger" min="0" value="0" required>
                                <div class="form-text text-danger small">Ditolak sementara untuk dijahit ulang penjahit.</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-truck"></i> 2. Logistik & Catatan QC:</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Ongkos Kirim Balik Batch Ini (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="ongkir_hasil_produksi" class="form-control" value="0" min="0" required>
                                </div>
                                <div class="form-text small text-muted">Biaya pengiriman barang dari penjahit ke gudang utama untuk tahap ini.</div>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label fw-bold">Catatan Hasil Pemeriksaan Lapangan</label>
                                <textarea name="catatan_qc" class="form-control" rows="2" placeholder="Contoh: Setoran parsial tahap 1 aman, sisa pengerjaan dilanjut besok." maxlength="45"></textarea>
                                <div class="form-text small text-muted">Maksimal 45 karakter sesuai struktur database.</div>
                            </div>
                        </div>

                        <div id="validator_box" class="alert alert-warning d-flex justify-content-between align-items-center p-3 mb-4">
                            <div class="fw-bold">
                                Total Diinput Sekarang: <span id="total_hitung">0</span> Pcs
                            </div>
                            <div class="small fw-bold text-end" id="status_keterangan">
                                Masukkan kuantitas barang untuk membuka tombol kunci.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between pt-2">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-4"><i class="bi bi-arrow-left"></i> Kembali</a>
                            <button type="submit" id="btn_submit" class="btn btn-success px-5 fw-bold" disabled>
                                <i class="bi bi-cloud-arrow-up-fill"></i> Kunci Batch Ini
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const sisaMaksimal = {{ $sisaTanggungan }};
    const inputAcc = document.getElementById('qty_accepted');
    const inputDef = document.getElementById('qty_rejected_defect');
    const inputRej = document.getElementById('qty_rejected');
    
    const displayTotal = document.getElementById('total_hitung');
    const validatorBox = document.getElementById('validator_box');
    const btnSubmit = document.getElementById('btn_submit');
    const statusKeterangan = document.getElementById('status_keterangan');

    function validasiKuantitasParsial() {
        const valAcc = parseInt(inputAcc.value) || 0;
        const valDef = parseInt(inputDef.value) || 0;
        const valRej = parseInt(inputRej.value) || 0;
        
        // Menghitung berapa yang mau di-QC saat ini saja
        const totalInputSekarang = valAcc + valDef + valRej;
        displayTotal.innerText = totalInputSekarang;

        if (totalInputSekarang > 0 && totalInputSekarang <= sisaMaksimal) {
            // Kondisi Aman: Inputan valid dan di bawah atau pas dengan sisa kuantitas baju yang belum disetor
            validatorBox.className = "alert alert-success d-flex justify-content-between align-items-center p-3 mb-4";
            statusKeterangan.innerHTML = `<i class="bi bi-check-circle-fill"></i> Valid (Sisa kuantitas aman: ${sisaMaksimal - totalInputSekarang} Pcs)`;
            btnSubmit.removeAttribute('disabled');
        } else if (totalInputSekarang > sisaMaksimal) {
            // Kondisi Error: Inputan melampaui sisa orderan (Over-input)
            validatorBox.className = "alert alert-danger d-flex justify-content-between align-items-center p-3 mb-4";
            statusKeterangan.innerHTML = `<i class="bi bi-exclamation-octagon-fill"></i> Over-Input! Maksimal sisa hanya ${sisaMaksimal} Pcs.`;
            btnSubmit.setAttribute('disabled', 'true');
        } else {
            // Kondisi Awal / Masih Kosong (0 pcs)
            validatorBox.className = "alert alert-warning d-flex justify-content-between align-items-center p-3 mb-4";
            statusKeterangan.innerText = "Masukkan kuantitas barang untuk membuka tombol kunci.";
            btnSubmit.setAttribute('disabled', 'true');
        }
    }

    // Pasang listener ketikan keyboard pada ketiga kolom input kuantitas
    inputAcc.addEventListener('input', validasiKuantitasParsial);
    inputDef.addEventListener('input', validasiKuantitasParsial);
    inputRej.addEventListener('input', validasiKuantitasParsial);
</script>
@endsection