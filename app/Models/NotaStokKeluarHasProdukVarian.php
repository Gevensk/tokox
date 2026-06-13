<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaStokKeluarHasProdukVarian extends Model
{
    use HasFactory;

    protected $table = 'nota_stok_keluar_has_produk_varian';

    protected $fillable = [
        'nota_stok_keluar_id', 
        'produk_varian_id', 
        'qty', 
        'harga_satuan', 
        'subtotal'
    ];


    public function nota_keluar()
    {
        return $this->belongsTo(NotaStokKeluar::class, 'nota_stok_keluar_id');
    }

    // Relasi ke Varian Produk untuk tahu warna/ukuran barangnya
    public function produk_varian()
    {
        return $this->belongsTo(ProdukVarian::class, 'produk_varian_id');
    }
}