@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Form Tambah Customer</h5>
            </div>
            <div class="card-body">
                <form action="/customer" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nama Customer</label>
                        <input type="text" name="nama" id="nama" class="form-control" placeholder="Contoh: Budi Sudarsono" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" id="alamat" class="form-control" rows="2" placeholder="Alamat lengkap..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor HP</label>
                        <input type="text" name="no_hp" id="no_hp" class="form-control" placeholder="0812xxxx">
                    </div>

                    <div class="d-inline">
                        <a href="/customer" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-success">Simpan Data</button>
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
        // Opsional: buat jadi read-only agar tidak diubah-ubah
        nama.readOnly = true;
        alamat.readOnly = true;
        no_hp.readOnly = true;
    } else {
        nama.value = "";
        alamat.value = "";
        no_hp.value = "";
        nama.readOnly = false;
        alamat.readOnly = false;
        no_hp.readOnly = false;
    }
}
</script>
@endsection