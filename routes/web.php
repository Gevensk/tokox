<?php

use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PenjahitController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\NotaPembelianController;
use App\Http\Controllers\BillOfMaterialController;

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


