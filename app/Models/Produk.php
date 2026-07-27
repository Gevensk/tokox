<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    //
    protected $table='produk';
    protected $fillable=['nama','satuan','customer_id','harga_jual','foto','total'];

    public function varians(){
        return $this->hasMany(ProdukVarian::class,'produk_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }
}
