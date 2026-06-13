<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TokoX - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { 
            display: flex; 
            min-height: 100vh; 
            overflow-x: hidden; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Modernized Sidebar Style */
        #sidebar { 
            min-width: 260px; 
            max-width: 260px; 
            background: #1e2229; /* Warna abu-abu gelap kebiruan yang modern */
            color: #bdc6d1; 
            transition: all 0.3s; 
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        
        #sidebar .brand-title {
            color: #ffffff;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        /* Menu Header Kategori */
        .menu-header {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #6c757d;
            font-weight: 700;
            margin-top: 20px;
            margin-bottom: 8px;
            padding-left: 12px;
        }

        /* Nav Link Styling */
        #sidebar .nav-link { 
            color: #98a6ad; 
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 4px;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
        }
        
        #sidebar .nav-link i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 10px;
            text-align: center;
        }
        
        /* Hover & Active Effects */
        #sidebar .nav-link:hover { 
            color: #ffffff; 
            background: rgba(255, 255, 255, 0.05); 
        }
        
        #sidebar .nav-link.active { 
            color: #ffffff; 
            background: #3b7ddd; /* Warna biru aksen modern */
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(59, 125, 221, 0.25);
        }

        /* Content Area Adjustments */
        #content { 
            width: 100%; 
            padding: 25px; 
            background: #f5f7fb; 
        }
        
        .navbar {
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <nav id="sidebar" class="p-3 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-center py-2 mb-2">

            <h4 class="mb-0 brand-title">TOKO X</h4>
        </div>
        
        <hr class="text-secondary my-2">
        
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="/dashboard" class="nav-link {{ Request::is('dashboard*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </a>
            </li>

            <div class="menu-header">Master Data</div>
            <li>
                <a href="/supplier" class="nav-link {{ Request::is('supplier*') ? 'active' : '' }}">
                    <i class="fa-solid fa-truck-field"></i> Supplier
                </a>
            </li>
            <li>
                <a href="/bahan_baku" class="nav-link {{ Request::is('bahan_baku*') ? 'active' : '' }}">
                    <i class="fa-solid fa-box-open"></i> Bahan Baku
                </a>
            </li>
            <li>
                <a href="/produk" class="nav-link {{ Request::is('produk*') ? 'active' : '' }}">
                    <i class="fa-solid fa-tags"></i> Produk 
                </a>
            </li>
            <li>
                <a href="/penjahit" class="nav-link {{ Request::is('penjahit*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-tie"></i> Mitra Penjahit
                </a>
            </li>
            <li>
                <a href="/customer" class="nav-link {{ Request::is('customer*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i> Pelanggan / Cust
                </a>
            </li>
            <li>
                <a href="/users" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-gear"></i> Manajemen User
                </a>
            </li>

            <div class="menu-header">Manajemen Produksi</div>
            <li>
                <a href="{{ route('bill_of_material.index') }}" class="nav-link {{ Request::is('*bill_of_material*') ? 'active' : '' }}">
                    <i class="fa-solid fa-receipt"></i> Bill of Materials
                </a>
            </li>
            <li>
                <a href="/penyesuaian_stok" class="nav-link {{ Request::is('penyesuaian_stok*') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-packing"></i> Penyesuaian Stok
                </a>
            </li>

            <div class="menu-header">Transaksi Nota</div>
            <li>
                <a href="{{ route('nota_pembelian.index') }}" class="nav-link {{ Request::is('*nota_pembelian*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Pembelian Bahan
                </a>
            </li>
            <li>
                <a href="{{ route('nota_order.index') }}" class="nav-link {{ Request::is('*nota_order*') ? 'active' : '' }}">
                    <i class="fa-solid fa-cart-shopping"></i> Nota Order Pesanan
                </a>
            </li>
            <li>
                <a href="{{ route('nota_produksi.index') }}" class="nav-link {{ Request::is('*nota_produksi*') ? 'active' : '' }}">
                    <i class="fa-solid fa-industry"></i> Nota Produksi Jahit
                </a>
            </li>
            <li>
                <a href="{{ route('nota_keluar.index') }}" class="nav-link {{ Request::is('*nota_keluar*') ? 'active' : '' }}">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Nota Stok Keluar
                </a>
            </li>
        </ul>
        
        <div class="p-2 mt-4 text-center" style="font-size: 0.75rem; color: #495057;">
            v1.2 © 2026 TokoX Corp.
        </div>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 py-3 px-4">
            <div class="container-fluid p-0">
                <span class="navbar-brand mb-0 h5 fw-bold text-dark">
                    <i class="fa-solid fa-laptop-code text-muted me-2"></i> Sistem Informasi Produksi
                </span>
            </div>
        </nav>

        @yield('content') 
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>