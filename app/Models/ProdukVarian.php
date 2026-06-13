<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukVarian extends Model
{
    //
    protected $table = 'produk_varian';
    protected $fillable = ['produk_id','warna','ukuran','stok_tersedia','harga_avg_produk','is_defect'];

    public function produk(){
        return $this->belongsTo(Produk::class,'produk_id');
    }

    public function bill_of_materials()
    {
        return $this->hasMany(BillOfMaterial::class, 'produk_varian_id'); 
    }
}
