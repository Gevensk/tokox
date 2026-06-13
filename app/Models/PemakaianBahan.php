<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemakaianBahan extends Model
{
    use HasFactory;

    protected $table = 'pemakaian_bahan'; // Sesuai nama tabel di ERD

    protected $fillable = [
        'bahan_baku_id',
        'job_order_id',
        'tgl_keluar',
        'jumlah',
        'harga_rata_saat_ini'
    ];

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function job_order()
    {
        return $this->belongsTo(JobOrder::class, 'job_order_id');
    }
}