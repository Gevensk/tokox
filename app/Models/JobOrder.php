<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobOrder extends Model
{
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
        'ongkir_terima_barang',
        'estimasi_selesai',
        'tgl_selesai',
    ];

    public function nota_produksi()
    {
        return $this->belongsTo(NotaProduksi::class, 'nota_produksi_id');
    }

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
        return $this->hasMany(PemakaianBahan::class, 'job_order_id');
    }

    /**
     * Mengambil data penjahit secara tidak langsung melalui Nota Produksi (Surat Jalan)
     */
    public function penjahit()
    {
        return $this->hasOneThrough(
            Penjahit::class,
            NotaProduksi::class,
            'id', // Foreign key di nota_produksi mendatangi job_order
            'id', // Foreign key di penjahit mendatangi nota_produksi
            'nota_produksi_id', // Local key di job_order
            'penjahit_id' // Local key di nota_produksi
        );
    }

    public function hasil_produksis()
    {
        return $this->hasMany(HasilProduksi::class, 'job_order_id'); 
    }
}