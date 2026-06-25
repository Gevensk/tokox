<?php

use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PenjahitController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\NotaPembelianController;
use App\Http\Controllers\BillOfMaterialController;
use App\Http\Controllers\NotaProduksiController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\NotaOrderController;
use App\Http\Controllers\PenyesuaianStokBahanController;
use App\Http\Controllers\NotaStokKeluarController;
use App\Http\Controllers\HasilProduksiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Guest / Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Admin-Only Routes
    Route::middleware('admin')->group(function () {
        Route::resource('/users', UserController::class);

        // Entirely admin-only resources
        Route::resource('/supplier', SupplierController::class);
        Route::resource('bill_of_material', BillOfMaterialController::class);
        Route::get('/nota_pembelian', [NotaPembelianController::class, 'index'])->name('nota_pembelian.index');
        Route::get('/nota_pembelian/create',[NotaPembelianController::class,'create'])->name('nota_pembelian.create');
        Route::post('/nota_pembelian',[NotaPembelianController::class,'store'])->name('nota_pembelian.store');
        Route::get('/nota_pembelian/{id}', [NotaPembelianController::class, 'show'])->name('nota_pembelian.show');
        Route::prefix('penyesuaian_stok')->name('penyesuaian_stok.')->group(function () {
            Route::get('/', [PenyesuaianStokBahanController::class, 'index'])->name('index');
            Route::get('/create', [PenyesuaianStokBahanController::class, 'create'])->name('create');
            Route::post('/store', [PenyesuaianStokBahanController::class, 'store'])->name('store');
        });

        // Write operations for Master data
        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::get('/produk/create', [ProdukController::class, 'create'])->name('produk.create');
        Route::get('/produk/{produk}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
        Route::put('/produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('/produk/{produk}', [ProdukController::class, 'destroy'])->name('produk.destroy');
        Route::post('produk_varian', [ProdukController::class, 'storeVarian'])->name('produk_varian.store');
        Route::delete('/produk_varian/{id}',[ProdukController::class,'destroyVarian']);

        Route::post('/bahan_baku', [BahanBakuController::class, 'store'])->name('bahan_baku.store');
        Route::get('/bahan_baku/create', [BahanBakuController::class, 'create'])->name('bahan_baku.create');
        Route::get('/bahan_baku/{bahan_baku}/edit', [BahanBakuController::class, 'edit'])->name('bahan_baku.edit');
        Route::put('/bahan_baku/{bahan_baku}', [BahanBakuController::class, 'update'])->name('bahan_baku.update');
        Route::delete('/bahan_baku/{bahan_baku}', [BahanBakuController::class, 'destroy'])->name('bahan_baku.destroy');

        Route::post('/penjahit', [PenjahitController::class, 'store'])->name('penjahit.store');
        Route::get('/penjahit/create', [PenjahitController::class, 'create'])->name('penjahit.create');
        Route::get('/penjahit/{penjahit}/edit', [PenjahitController::class, 'edit'])->name('penjahit.edit');
        Route::put('/penjahit/{penjahit}', [PenjahitController::class, 'update'])->name('penjahit.update');
        Route::delete('/penjahit/{penjahit}', [PenjahitController::class, 'destroy'])->name('penjahit.destroy');

        Route::get('/customer/{customer}/edit', [CustomerController::class, 'edit'])->name('customer.edit');
        Route::put('/customer/{customer}', [CustomerController::class, 'update'])->name('customer.update');
        Route::delete('/customer/{customer}', [CustomerController::class, 'destroy'])->name('customer.destroy');

        // Write operations for job orders (HPP Audit detail view and store)
        Route::post('job_order', [JobOrderController::class, 'store'])->name('job_order.store');
        Route::get('job_order/{job_order}', [JobOrderController::class, 'show'])->name('job_order.show');
        Route::get('job_order/create', [JobOrderController::class, 'create'])->name('job_order.create');
        Route::get('job_order/{job_order}/edit', [JobOrderController::class, 'edit'])->name('job_order.edit');
        Route::put('job_order/{job_order}', [JobOrderController::class, 'update'])->name('job_order.update');
        Route::delete('job_order/{job_order}', [JobOrderController::class, 'destroy'])->name('job_order.destroy');

        // Write operations for nota order (edit, update, destroy)
        Route::get('nota_order/{nota_order}/edit', [NotaOrderController::class, 'edit'])->name('nota_order.edit');
        Route::put('nota_order/{nota_order}', [NotaOrderController::class, 'update'])->name('nota_order.update');
        Route::delete('nota_order/{nota_order}', [NotaOrderController::class, 'destroy'])->name('nota_order.destroy');

        // Write operations for nota stok keluar (edit, update, destroy)
        Route::get('nota-stok-keluar/{nota_stok_keluar}/edit', [NotaStokKeluarController::class, 'edit'])->name('nota_keluar.edit');
        Route::put('nota-stok-keluar/{nota_stok_keluar}', [NotaStokKeluarController::class, 'update'])->name('nota_keluar.update');
        Route::delete('nota-stok-keluar/{nota_stok_keluar}', [NotaStokKeluarController::class, 'destroy'])->name('nota_keluar.destroy');
    });

    // General Production & Staff Routes (Read-only or limited write)
    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    Route::get('/produk/{id}', [ProdukController::class, 'show'])->name('produk.show');

    Route::get('/bahan_baku', [BahanBakuController::class, 'index'])->name('bahan_baku.index');
    Route::get('/bahan_baku/{bahan_baku}', [BahanBakuController::class, 'show'])->name('bahan_baku.show');

    Route::get('/penjahit', [PenjahitController::class, 'index'])->name('penjahit.index');
    Route::get('/penjahit/{penjahit}', [PenjahitController::class, 'show'])->name('penjahit.show');

    // Customer: Staff can read and add new
    Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/customer/create', [CustomerController::class, 'create'])->name('customer.create');
    Route::post('/customer', [CustomerController::class, 'store'])->name('customer.store');
    Route::get('/customer/{customer}', [CustomerController::class, 'show'])->name('customer.show');

    // Job Order: Staff can only read list
    Route::get('job_order', [JobOrderController::class, 'index'])->name('job_order.index');

    // Nota Order: Staff can read and create
    Route::get('nota_order', [NotaOrderController::class, 'index'])->name('nota_order.index');
    Route::get('nota_order/create', [NotaOrderController::class, 'create'])->name('nota_order.create');
    Route::post('nota_order', [NotaOrderController::class, 'store'])->name('nota_order.store');
    Route::get('nota_order/{nota_order}', [NotaOrderController::class, 'show'])->name('nota_order.show');
    Route::get('nota_order/{id}/print', [NotaOrderController::class, 'cetakPdf'])->name('nota_order.print');
    Route::get('/api/produk/{id}/varian', [App\Http\Controllers\NotaOrderController::class, 'getVarianByProduk']);

    // Nota Stok Keluar: Staff can read and create
    Route::get('nota-stok-keluar', [NotaStokKeluarController::class, 'index'])->name('nota_keluar.index');
    Route::get('nota-stok-keluar/create', [NotaStokKeluarController::class, 'create'])->name('nota_keluar.create');
    Route::post('nota-stok-keluar', [NotaStokKeluarController::class, 'store'])->name('nota_keluar.store');
    Route::get('nota-stok-keluar/{id}/print', [NotaStokKeluarController::class, 'cetakPdf'])->name('nota_keluar.print');

    // Production Notes and Hasil Produksi are available to everyone for transactions
    Route::post('nota_produksi/hitung_bom', [NotaProduksiController::class, 'hitungBom'])->name('nota_produksi.hitung_bom');
    Route::resource('nota_produksi', NotaProduksiController::class);
    Route::resource('nota_production', App\Http\Controllers\NotaProduksiController::class);
    Route::post('nota_production/{id}/finish', [App\Http\Controllers\NotaProduksiController::class, 'finish'])->name('nota_production.finish');
    Route::post('nota_production/{id}/kirim-bahan', [App\Http\Controllers\NotaProduksiController::class, 'kirimBahan'])->name('nota_production.kirim_bahan');
    Route::get('nota_production/{id}', [App\Http\Controllers\NotaProduksiController::class, 'show'])->name('nota_production.show');
    Route::get('nota_production/{id}/cetak-pdf', [App\Http\Controllers\NotaProduksiController::class, 'cetakPdf'])->name('nota_production.cetak_pdf');

    Route::get('/nota-produksi/terima/{job_id}', [NotaProduksiController::class, 'createHasilProduksi'])->name('nota_produksi.terima_hasil');
    Route::post('/nota-produksi/terima/store', [NotaProduksiController::class, 'storeHasilProduksi'])->name('hasil_produksi.store');

    Route::get('/hasil-produksi/create', [HasilProduksiController::class, 'create'])->name('hasil_produksi.create');
    Route::post('/hasil-produksi/store', [HasilProduksiController::class, 'store'])->name('hasil_produksi.store');
});


