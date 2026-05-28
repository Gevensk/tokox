<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TokoX - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; overflow-x: hidden; }
        #sidebar { min-width: 250px; max-width: 250px; background: #343a40; color: white; transition: all 0.3s; }
        #content { width: 100%; padding: 20px; background: #f8f9fa; }
        .nav-link { color: rgba(255,255,255,.5); }
        .nav-link:hover, .nav-link.active { color: white; background: #495057; }
    </style>
</head>
<body>

    <nav id="sidebar" class="p-3">
        <h4>Toko X</h4>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
        <a href="/dashboard" class="nav-link {{ Request::is('dashboard*') ? 'active' : '' }}">Dashboard</a>
    </li>
    <li>
        <a href="/supplier" class="nav-link {{ Request::is('supplier*') ? 'active' : '' }}">Supplier</a>
    </li>
    <li>
        <a href="/bahan_baku" class="nav-link {{ Request::is('bahan_baku*') ? 'active' : '' }}">Bahan Baku</a>
    </li>
    <li>
        <a href="/produk" class="nav-link {{ Request::is('produk*') ? 'active' : '' }}">Produk</a>
    </li>
    <li>
        <a href="/penjahit" class="nav-link {{ Request::is('penjahit*') ? 'active' : '' }}">Penjahit</a>
    </li>
    <li>
        <a href="/customer" class="nav-link {{ Request::is('customer*') ? 'active' : '' }}">Customer</a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="{{ route('bill_of_material.index') }}">
        <i class="fa fa-scroll"></i> <span>Bill of Materials (Resep)</span>
    </a>
    </li>

    <li>
        <a href="/users" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">User</a>
    </li>



    <hr> <li class="nav-item">
        <a href="{{ route('nota_pembelian.index') }}">Pembelian Bahan (Input Nota)</a>
    </li>
    
</ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Sistem Informasi Produksi Toko X</span>
            </div>
        </nav>

        @yield('content') {{-- TEMPAT KONTEN HALAMAN LAIN MUNCUL --}}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>