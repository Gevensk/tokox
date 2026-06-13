<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobOrder extends Model
{
    //
use HasFactory;

    protected $table = 'job_order';

    protected $fillable = [
        'nota_order_id',
        'nota_produksi_id',
        'produk_varian_id',
        'jumlah',
        'biaya_jasa_satuan',
        'is_custom',
        'catatan_custom',
        'foto_desain',
        'total_biaya_bahan',
        'total_hpp_job',
        'status_pesanan',
        'biaya_rework_tambahan',
        'estimasi_selesai',
        'tgl_selesai',
    ];

    public function nota_order()
    {
        return $this->belongsTo(NotaOrder::class, 'nota_order_id');
    }

    public function produk_varian()
    {
        return $this->belongsTo(ProdukVarian::class, 'produk_varian_id');
    }

    public function pemakaian_bahan()
    {
        // Hubungkan ke model PemakaianBahan dengan foreign key job_order_id
        return $this->hasMany(PemakaianBahan::class, 'job_order_id');
    }
}
