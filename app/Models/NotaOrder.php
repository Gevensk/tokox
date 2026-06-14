<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotaOrder extends Model
{
    use HasFactory;

    protected $table = 'nota_order';

    protected $fillable = [
        'no_order',
        'tipe_order',
        'customer_id',
        'tgl_order',
        'tgl_deadline',
        'total_harga',        
        'nominal_dp_masuk',
        'harga_satuan_items', 
        'status_bayar',
        'status_order'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function job_orders()
    {
        return $this->hasMany(JobOrder::class, 'nota_order_id');
    }
}