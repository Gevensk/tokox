<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaStokKeluar extends Model
{
    //
    use HasFactory;
    protected $table = 'nota_stok_keluar';
    protected $fillable = ['customer_id','nota_order_id','tipe_keluar','total_bayar'];

    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function nota_order(){
        return $this->belongsTo(NotaOrder::class,'nota_order_id');
    }

    public function nota_keluar()
    {
        return $this->hasMany(NotaStokKeluarHasProdukVarian::class, 'nota_stok_keluar_id');
    }
}
