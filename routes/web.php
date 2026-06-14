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

Route::get('/', function () {
    return view('welcome');
});

Route::resource('/supplier',SupplierController::class);
Route::resource('/bahan_baku',BahanBakuController::class);
Route::resource('/penjahit',PenjahitController::class);
Route::resource('/customer',CustomerController::class);
Route::resource('/produk', ProdukController::class);

Route::get('/nota_pembelian', [NotaPembelianController::class, 'index'])->name('nota_pembelian.index');
Route::get('/nota_pembelian/create',[NotaPembelianController::class,'create'])->name('nota_pembelian.create');
Route::post('/nota_pembelian',[NotaPembelianController::class,'store'])->name('nota_pembelian.store');
Route::get('/nota_pembelian/{id}', [NotaPembelianController::class, 'show'])->name('nota_pembelian.show');

Route::get('produk/{id}',[ProdukController::class,'show']);
Route::post('produk_varian', [ProdukController::class, 'storeVarian'])->name('produk_varian.store');
Route::delete('/produk_varian/{id}',[ProdukController::class,'destroyVarian']);

Route::resource('bill_of_material',BillOfMaterialController::class);

Route::post('nota_produksi/hitung_bom', [NotaProduksiController::class, 'hitungBom'])->name('nota_produksi.hitung_bom');
Route::resource('nota_produksi', NotaProduksiController::class);

Route::resource('job_order',JobOrderController::class);

Route::get('/api/produk/{id}/varian', [App\Http\Controllers\NotaOrderController::class, 'getVarianByProduk']);

// Route CRUD Nota Order
Route::resource('nota_order', NotaOrderController::class);

// Route khusus untuk mencetak Nota ke PDF / halaman print khusus
Route::get('nota_order/{id}/print', [NotaOrderController::class, 'cetakPdf'])->name('nota_order.print');

Route::resource('nota_production', App\Http\Controllers\NotaProduksiController::class);
Route::post('nota_production/{id}/finish', [App\Http\Controllers\NotaProduksiController::class, 'finish'])->name('nota_production.finish');
Route::post('nota_production/{id}/kirim-bahan', [App\Http\Controllers\NotaProduksiController::class, 'kirimBahan'])->name('nota_production.kirim_bahan');
// Route Detail Nota
Route::get('nota_production/{id}', [App\Http\Controllers\NotaProduksiController::class, 'show'])->name('nota_production.show');
Route::post('nota_production/{id}/kirim-bahan', [App\Http\Controllers\NotaProduksiController::class, 'kirimBahan'])->name('nota_production.kirim_bahan');
// Route untuk mencetak PDF Surat Jalan Bahan Baku
Route::get('nota_production/{id}/cetak-pdf', [App\Http\Controllers\NotaProduksiController::class, 'cetakPdf'])->name('nota_production.cetak_pdf');

// Route untuk menampilkan halaman form terima hasil & QC
Route::get('/nota-produksi/terima/{job_id}', [NotaProduksiController::class, 'createHasilProduksi'])->name('hasil_produksi.create');

// Route untuk memproses simpan data QC & penerimaan barang
Route::post('/nota-produksi/terima/store', [NotaProduksiController::class, 'storeHasilProduksi'])->name('hasil_produksi.store');

Route::prefix('penyesuaian_stok')->name('penyesuaian_stok.')->group(function () {
    Route::get('/', [PenyesuaianStokBahanController::class, 'index'])->name('index');
    Route::get('/create', [PenyesuaianStokBahanController::class, 'create'])->name('create');
    Route::post('/store', [PenyesuaianStokBahanController::class, 'store'])->name('store');
});

Route::resource('nota-stok-keluar', NotaStokKeluarController::class)->names('nota_keluar');


