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

    public function details()
    {
        return $this->hasMany(NotaPembelianDetail::class, 'nota_pembelian_id');
    }
}
