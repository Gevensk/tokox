<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class BillOfMaterial extends Model
{
    //
    use HasFactory;
    protected $table = 'bill_of_materials';
    protected $fillable = ['bahan_baku_id','produk_varian_id','jumlah'];

    public function bahan_baku(){
        return $this->belongsTo(BahanBaku::class,'bahan_baku_id');
    }

    public function varian(){
        return $this->belongsTo(ProdukVarian::class,'produk_varian_id');
    }
}
