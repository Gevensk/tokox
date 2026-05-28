<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Supplier - TokoX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Form Tambah Supplier</h5>
                </div>
                <div class="card-body">
                    <form action="/supplier" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Supplier</label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: PT. Maju Jaya" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor HP</label>
                            <input type="number" name="no_hp" class="form-control" placeholder="0812xxxx" required>
                        </div>
                        <div class="d-inline">
                            <a href="/supplier" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-success">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>