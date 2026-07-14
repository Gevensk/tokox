<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotaProduksi extends Model
{
    //
    use HasFactory;
    protected $table ='nota_produksi';
    protected $fillable = ['penjahit_id','no_surat_jalan','tgl_order','tgl_kirim','status_nota','status_kirim','ongkir_kirim_bahan','catatan'];

    public function penjahit()
    {
        return $this->belongsTo(Penjahit::class, 'penjahit_id');
    }

    public function jobOrders()
    {
        return $this->hasMany(JobOrder::class, 'nota_produksi_id');
    }
}
