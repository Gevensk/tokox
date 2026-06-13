@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark fw-bold">
            <h4>✏️ Edit Informasi Nota Order</h4>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('nota_order.update', $notaOrder->id) }}" method="POST">
                @csrf
                @method('PUT')

                <h5 class="text-secondary border-bottom pb-2 mb-3">Informasi Nota (No. Order: {{ $notaOrder->no_order }})</h5>
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tipe Order</label>
                        <select name="tipe_order" id="tipe_order" class="form-select" required>
                            <option value="Customer" {{ $notaOrder->tipe_order == 'Customer' ? 'selected' : '' }}>Customer (Pesanan Luar)</option>
                            <option value="Internal" {{ $notaOrder->tipe_order == 'Internal' ? 'selected' : '' }}>Internal (Stok Toko Sendiri)</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 {{ $notaOrder->tipe_order == 'Internal' ? 'd-none' : '' }}" id="wrapper_customer">
                        <label class="form-label">Pilih Customer</label>
                        <select name="customer_id" class="form-select">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ $notaOrder->customer_id == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tanggal Deadline</label>
                        <input type="date" name="tgl_deadline" class="form-control" value="{{ \Carbon\Carbon::parse($notaOrder->tgl_deadline)->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Harga Deal (Rp)</label>
                        <input type="number" name="harga_deal" class="form-control" value="{{ (int)$notaOrder->harga_deal }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nominal DP Masuk (Rp)</label>
                        <input type="number" name="nominal_dp_masuk" class="form-control" value="{{ (int)$notaOrder->nominal_dp_masuk }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status Bayar</label>
                        <select name="status_bayar" class="form-select" required>
                            <option value="Belum Lunas" {{ $notaOrder->status_bayar == 'Belum Lunas' ? 'selected' : '' }}>DP / Belum Lunas</option>
                            <option value="Lunas" {{ $notaOrder->status_bayar == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mb-3">📦 Daftar Item yang Terkunci di Nota Ini</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Produk / Varian</th>
                                <th width="120" class="text-center">Quantity</th>
                                <th>Kustomisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notaOrder->job_orders as $job)
                            <tr>
                                <td>
                                    <strong>{{ $job->produk_varian->produk->nama }}</strong>
                                    <div class="small text-muted">Varian: {{ $job->produk_varian->warna }} - Ukuran: {{ $job->produk_varian->ukuran }}</div>
                                </td>
                                <td class="text-center fw-bold fs-5">{{ $job->jumlah }} pcs</td>
                                <td>
                                    @if($job->is_custom)
                                        <span class="badge bg-warning text-dark mb-1">Custom Specs</span>
                                        <div class="small text-muted">"{{ $job->catatan_custom }}"</div>
                                    @else
                                        <span class="text-muted small">Standard Pola</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-end">
                    <a href="{{ route('nota_order.index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan Info</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipeOrder = document.getElementById('tipe_order');
    const wrapperCustomer = document.getElementById('wrapper_customer');
    const selectCustomer = wrapperCustomer.querySelector('select');

    tipeOrder.addEventListener('change', function() {
        if (this.value === 'Internal') {
            wrapperCustomer.classList.add('d-none');
            selectCustomer.removeAttribute('required');
            selectCustomer.value = '';
        } else {
            wrapperCustomer.classList.remove('d-none');
            selectCustomer.setAttribute('required', 'required');
        }
    });
});
</script>
@endsection