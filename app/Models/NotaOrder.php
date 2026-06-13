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
        'harga_deal',
        'nominal_dp_masuk',
        'status_bayar',
        'status_order'
    ];

    /**
     * TAMBAHKAN INI: Relasi ke tabel Customer (Sesuai ERD)
     */
    public function customer()
    {
        // Menyatakan bahwa nota_order dimiliki oleh satu customer melalui customer_id
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * PASTIKAN INI JUGA ADA: Relasi ke detail Job Order
     */
    public function job_orders()
    {
        // Menyatakan bahwa satu nota_order memiliki banyak baris job_order
        return $this->hasMany(JobOrder::class, 'nota_order_id');
    }
}