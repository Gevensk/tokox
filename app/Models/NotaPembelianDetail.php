<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaPembelianDetail extends Model
{
    //
    protected $table='nota_pembelian_has_bahan_baku';
    protected $fillable =['nota_pembelian_id','bahan_baku_id','jumlah','harga_satuan','subtotal'];
    public $timestamps = false;

    public function bahanBaku(){
        return $this->belongsTo(BahanBaku::class,'bahan_baku_id');
    }
}
