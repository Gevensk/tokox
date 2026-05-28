<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    //
    protected $table='produk';
    protected $fillable=['nama','satuan','harga_jual'];

    public function varians(){
        return $this->hasMany(ProdukVarian::class,'produk_id');
    }
}
