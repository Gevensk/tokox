@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Riwayat Pembelian Bahan Baku</h2>
        <a href="{{ route('nota_pembelian.create') }}" class="btn btn-primary">
            + Input Nota Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tanggal Beli</th>
                        <th>Supplier</th>
                        <th>Total Harga Bahan</th>
                        <th>Ongkir</th>
                        <th>Total Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $item)
                    <tr>
                        <td>{{ date('d-m-Y H:i', strtotime($item->tgl_beli)) }}</td>
                        <td>{{ $item->supplier->nama }}</td>
                        <td>Rp {{ number_format($item->total_harga_bahan, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->ongkir, 0, ',', '.') }}</td>
                        <td><strong>Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</strong></td>
                        <td>
                            <button class="btn btn-sm btn-info btn-detail" data-id="{{ $item->id }}">
                                Detail
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Nota Pembelian: <span id="det-nomor"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <p><strong>Supplier:</strong> <span id="det-supplier"></span></p>
                        <p><strong>Tanggal:</strong> <span id="det-tgl"></span></p>
                    </div>
                    <div class="col-6 text-right">
                        <p><strong>Ongkir:</strong> Rp <span id="det-ongkir"></span></p>
                        <p><strong>Total Bayar:</strong> <span id="det-total" class="font-weight-bold text-primary"></span></p>
                    </div>
                </div>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr class="bg-light">
                            <th>Bahan Baku</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="det-list-bahan">
                        </tbody>
                </table>
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
                    <td>${item.bahan_baku.nama}</td>
                    <td>${item.jumlah} ${item.bahan_baku.satuan}</td>
                    <td>Rp ${new Intl.NumberFormat('id-ID').format(item.harga_satuan)}</td>
                    <td>Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                </tr>`;
                $('#det-list-bahan').append(row);
            });

            $('#modalDetail').modal('show');
        });
    });
});
</script>
@endsection