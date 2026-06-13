<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenyesuaianStokBahan extends Model
{
    use HasFactory;
    
    protected $table="penyesuaian_stok_bahan";
    protected $fillable =['bahan_baku_id','jumlah','tipe','keterangan'];

    public function bahan_baku(){
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
