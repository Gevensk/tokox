<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaPembelian extends Model
{
    //
    protected $table ='nota_pembelian';
    protected $fillable =['supplier_id','tgl_beli','total_harga_bahan','ongkir','total_bayar'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Relasi ke Detail (Anak)
     * Untuk menampilkan: "Nota ini berisi Bahan Baku apa saja"
     */
    public function details()
    {
        // Pastikan kolom di tabel nota_pembelian_has_bahan_baku adalah nota_pembelian_id
        return $this->hasMany(NotaPembelianDetail::class, 'nota_pembelian_id');
    }
}
