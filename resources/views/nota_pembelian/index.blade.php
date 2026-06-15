@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold text-dark">Riwayat Pembelian Bahan Baku</h3>
        <a href="{{ route('nota_pembelian.create') }}" class="btn btn-primary px-3 shadow-sm">
            + Input Nota Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">Tanggal Beli</th>
                            <th>Supplier</th>
                            <th>Total Harga Bahan</th>
                            <th>Ongkir</th>
                            <th>Total Bayar</th>
                            <th class="text-center pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Mengganti @foreach menjadi @forelse untuk menangani kondisi data kosong --}}
                        @forelse($history as $item)
                        <tr>
                            <td class="ps-3">{{ date('d M Y H:i', strtotime($item->tgl_beli)) }}</td>
                            <td>
                                <span class="badge bg-info text-dark me-1">Vendor</span> 
                                <span class="fw-semibold">{{ $item->supplier->nama }}</span>
                            </td>
                            <td>Rp {{ number_format($item->total_harga_bahan, 0, ',', '.') }}</td>
                            <td><span class="text-muted">Rp {{ number_format($item->ongkir, 0, ',', '.') }}</span></td>
                            <td><strong class="text-success">Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</strong></td>
                            <td class="text-center pe-3">
                                <button class="btn btn-sm btn-outline-secondary btn-detail text-nowrap" data-id="{{ $item->id }}">
                                    👁 Detail Nota
                                </button>
                            </td>
                        </tr>
                        @empty
                        {{-- Tampilan informatif jika riwayat nota pembelian masih kosong --}}
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="my-2">
                                    Belum ada data riwayat pembelian bahan baku yang tersimpan.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL NOTA --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="exampleModalLabel">Detail Nota Pembelian: <span id="det-nomor" class="fw-bold text-warning"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-4 pb-3 border-bottom">
                    <div class="col-6">
                        <p class="mb-1 text-muted small text-uppercase fw-bold">Pemasok / Vendor</p>
                        <h5 id="det-supplier" class="text-dark fw-bold">-</h5>
                        <p class="mb-0 text-secondary small"><i class="fa-regular fa-calendar me-1"></i> <span id="det-tgl">-</span></p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1 text-secondary">Ongkos Kirim: <span class="fw-bold text-dark">Rp <span id="det-ongkir">0</span></span></p>
                        <h4 class="mb-0 fw-bold text-success">Total: <span id="det-total">Rp 0</span></h4>
                    </div>
                </div>
                
                <h6 class="fw-bold mb-3 text-secondary">Daftar Muatan Bahan Baku</h6>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Bahan Baku</th>
                                <th class="text-center" style="width: 15%">Jumlah</th>
                                <th style="width: 25%">Harga Satuan</th>
                                <th style="width: 25%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="det-list-bahan">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.btn-detail').on('click', function() {
        let id = $(this).data('id');
        $('#det-list-bahan').empty();

        $.get("{{ url('nota_pembelian') }}/" + id, function(data) {
            $('#det-nomor').text('#' + data.id);
            $('#det-supplier').text(data.supplier.nama);
            $('#det-tgl').text(data.tgl_beli);
            $('#det-ongkir').text(new Intl.NumberFormat('id-ID').format(data.ongkir));
            $('#det-total').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.total_bayar));

            data.details.forEach(function(item) {
                let row = `<tr>
                    <td><span class="fw-bold">${item.bahan_baku.nama}</span></td>
                    <td class="text-center"><span class="badge bg-light text-dark border">${item.jumlah} ${item.bahan_baku.satuan}</span></td>
                    <td>Rp ${new Intl.NumberFormat('id-ID').format(item.harga_satuan)}</td>
                    <td><strong class="text-dark">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</strong></td>
                </tr>`;
                $('#det-list-bahan').append(row);
            });

            $('#modalDetail').modal('show');
        });
    });
});
</script>
@endsection