@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Form Edit Customer</h5>
            </div>
            <div class="card-body">
                <form action="/customer/{{ $customer->id }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Tipe Customer</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipe" id="tipeCustomer" value="customer" {{ $customer->tipe == 'customer' ? 'checked' : '' }} onclick="toggleInput(false)">
                            <label class="form-check-label" for="tipeCustomer">Pelanggan Umum</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipe" id="tipeInternal" value="internal" {{ $customer->tipe == 'internal' ? 'checked' : '' }} onclick="toggleInput(true)">
                            <label class="form-check-label" for="tipeInternal">Internal (Stok)</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="nama">Nama</label>
                        <input type="text" name="nama" id="nama" class="form-control" value="{{ $customer->nama }}" {{ $customer->tipe == 'internal' ? 'readonly' : '' }} placeholder="Masukkan nama customer" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="alamat">Alamat</label>
                        <input type="text" name="alamat" id="alamat" class="form-control" value="{{ $customer->alamat }}" {{ $customer->tipe == 'internal' ? 'readonly' : '' }} placeholder="Masukkan alamat lengkap" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="no_hp">No HP</label>
                        <input type="text" name="no_hp" id="no_hp" class="form-control" value="{{ $customer->no_hp }}" {{ $customer->tipe == 'internal' ? 'readonly' : '' }} placeholder="Contoh: 08123456789" required>
                    </div>

                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <a href="/customer" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Data</button>
                    </div>
                </form>        
            </div>
        </div>
    </div>
</div>

<script>
function toggleInput(isInternal) {
    const nama = document.getElementById('nama');
    const alamat = document.getElementById('alamat');
    const no_hp = document.getElementById('no_hp');

    if (isInternal) {
        nama.value = "INTERNAL / STOK TOKO";
        alamat.value = "-";
        no_hp.value = "-";
        nama.readOnly = true;
        alamat.readOnly = true; // Ditambahkan agar field alamat ikut terkunci saat internal dipilih
        no_hp.readOnly = true;  // Ditambahkan agar field no_hp ikut terkunci saat internal dipilih
    } else {
        nama.readOnly = false;
        alamat.readOnly = false;
        no_hp.readOnly = false;
        
        if(nama.value == "INTERNAL / STOK TOKO") {
            nama.value = "";
            alamat.value = "";
            no_hp.value = "";
        }
    }
}
</script>
@endsection