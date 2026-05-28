<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    //
    protected $table ='bahan_baku';
    protected $fillable =['nama','satuan','stok_total','harga_rata_rata'];

}
