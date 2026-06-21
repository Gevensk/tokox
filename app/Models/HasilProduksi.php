<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilProduksi extends Model
{
    use HasFactory;

    // Sesuai dengan nama tabel di ERD kamu
    protected $table = 'hasil_produksi';

    // Daftarkan semua field dari gambar ERD agar bisa diisi (Mass Assignment)
    protected $fillable = [
        'job_order_id',
        'produk_varian_id',
        'tgl_terima',
        'ongkir',
        'qty_accepted',
        'qty_rejected_defect',
        'qty_rejected',
        'catatan_qc',
        'biaya_rework_tambahan',
    ];

    // Relasi balik ke Job Order (Opsional tapi sangat berguna untuk panggil data)
    public function job_order()
    {
        return $this->belongsTo(JobOrder::class, 'job_order_id');
    }

    // Relasi balik ke Produk Varian
    public function produk_varian()
    {
        return $this->belongsTo(ProdukVarian::class, 'produk_varian_id');
    }
}