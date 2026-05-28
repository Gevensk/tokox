@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Edit Customer</h4>
        </div>
        <div class="card-body">
            <form action="/customer/{{ $customer->id }}" method="POST">
                @csrf
                @method('PUT')

                <label>Tipe Customer:</label><br>
                <input type="radio" name="tipe" id="tipeCustomer" value="customer" {{ $customer->tipe == 'customer' ? 'checked' : '' }} onclick="toggleInput(false)"> Pelanggan Umum
                <input type="radio" name="tipe" id="tipeInternal" value="internal" {{ $customer->tipe == 'internal' ? 'checked' : '' }} onclick="toggleInput(true)"> Internal (Stok)
                <br><br>

                <label>Nama:</label><br>
                <input type="text" name="nama" id="nama" value="{{ $customer->nama }}" {{ $customer->tipe == 'internal' ? 'readonly' : '' }} required><br><br>

                <label>Alamat:</label><br>
                <input type="text" name="alamat" id="alamat" value="{{ $customer->alamat }}" {{ $customer->tipe == 'internal' ? 'readonly' : '' }} required><br><br>

                <label>No HP:</label><br>
                <input type="text" name="no_hp" id="no_hp" value="{{ $customer->no_hp }}" {{ $customer->tipe == 'internal' ? 'readonly' : '' }} required><br><br>

                <div class="d-inline btn-sm">
                    <a href="/customer" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Update Data</button>
                </div>
            </form>        
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
    } else {
        nama.readOnly = false;
        // Opsional: kosongkan jika sebelumnya dari internal
        if(nama.value == "INTERNAL / STOK TOKO") {
            nama.value = "";
            alamat.value = "";
            no_hp.value = "";
        }
    }
}
</script>
@endsection