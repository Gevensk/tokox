<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\Penjahit;
use App\Models\NotaOrder;
use App\Models\NotaProduksi;
use App\Models\NotaPembelian;
use App\Models\NotaStokKeluar;
use App\Models\JobOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExploreRoutesTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $supplier;
    protected $customer;
    protected $bahanBaku;
    protected $produk;
    protected $varian;
    protected $penjahit;
    protected $notaOrder;
    protected $notaProduksi;
    protected $notaPembelian;
    protected $notaStokKeluar;
    protected $jobOrder;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Admin
        $this->admin = User::firstOrCreate(
            ['username' => 'admin_explorer'],
            [
                'name' => 'Explorer Admin',
                'email' => 'admin_explorer@tokox.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // 2. Create Supplier
        $this->supplier = Supplier::create([
            'nama' => 'Supplier Explorer',
            'alamat' => 'Alamat Supplier Explorer',
            'no_hp' => '0812345678',
        ]);

        // 3. Create Customer
        $this->customer = Customer::create([
            'nama' => 'Customer Explorer',
            'no_hp' => '0812345678',
            'alamat' => 'Alamat Customer Explorer',
        ]);

        // 4. Create Bahan Baku
        $this->bahanBaku = BahanBaku::create([
            'nama' => 'Bahan Baku Explorer',
            'satuan' => 'Meter',
            'stok_total' => 100.0,
            'harga_rata_rata' => 15000,
        ]);

        // 5. Create Produk
        $this->produk = Produk::create([
            'nama' => 'Produk Explorer',
            'satuan' => 'Pcs',
            'customer_id' => $this->customer->id,
        ]);

        // 6. Create Varian
        $this->varian = ProdukVarian::create([
            'produk_id' => $this->produk->id,
            'warna' => 'Merah',
            'ukuran' => 'M',
            'stok_tersedia' => 10,
            'stok_defect' => 0,
            'harga_avg_produk' => 50000,
        ]);

        // 7. Create Penjahit
        $this->penjahit = Penjahit::create([
            'nama' => 'Penjahit Explorer',
            'alamat' => 'Alamat Penjahit Explorer',
            'no_hp' => '0812345678',
        ]);

        // 8. Create Nota Order
        $this->notaOrder = NotaOrder::create([
            'no_order' => 'ORD-EXPLORER-01',
            'tipe_order' => 'Customer',
            'customer_id' => $this->customer->id,
            'tgl_order' => now(),
            'tgl_deadline' => now()->addDays(5),
            'total_harga' => 100000,
            'nominal_dp_masuk' => 30000,
            'status_bayar' => 'Belum Lunas',
            'status_order' => 'Valid',
        ]);

        // 9. Create Job Order
        $this->jobOrder = JobOrder::create([
            'nota_order_id' => $this->notaOrder->id,
            'produk_varian_id' => $this->varian->id,
            'jumlah' => 2,
            'biaya_jasa_satuan' => 0,
            'total_biaya_bahan' => 0,
            'status_pesanan' => 'Antrean',
            'is_custom' => 1,
            'total_hpp_job' => 0,
        ]);

        // 10. Create Nota Produksi
        $this->notaProduksi = NotaProduksi::create([
            'penjahit_id' => $this->penjahit->id,
            'no_surat_jalan' => 'SJ-EXPLORER-01',
            'tgl_order' => now(),
            'ongkir_kirim_bahan' => 5000,
            'status_nota' => 'Proses',
            'status_kirim' => 'Belum Dikirim',
        ]);

        // 11. Create Nota Pembelian
        $this->notaPembelian = NotaPembelian::create([
            'supplier_id' => $this->supplier->id,
            'tgl_beli' => now(),
            'total_harga_bahan' => 80000,
            'ongkir' => 10000,
            'total_bayar' => 90000,
        ]);

        // 12. Create Nota Stok Keluar
        $this->notaStokKeluar = NotaStokKeluar::create([
            'customer_id' => $this->customer->id,
            'nota_order_id' => $this->notaOrder->id,
            'tipe_keluar' => 'penjualan_order',
            'total_bayar' => 70000,
        ]);
    }

    public function test_all_pages_load_successfully(): void
    {
        $urls = [
            // Dashboard
            '/dashboard',

            // Supplier
            '/supplier',
            '/supplier/create',
            "/supplier/{$this->supplier->id}/edit",

            // Customer
            '/customer',
            '/customer/create',
            "/customer/{$this->customer->id}/edit",

            // Penjahit
            '/penjahit',
            '/penjahit/create',
            "/penjahit/{$this->penjahit->id}/edit",

            // Bahan Baku
            '/bahan_baku',
            '/bahan_baku/create',
            "/bahan_baku/{$this->bahanBaku->id}",
            "/bahan_baku/{$this->bahanBaku->id}/edit",
            "/bahan_baku/{$this->bahanBaku->id}/kartu_stok",

            // Produk
            '/produk',
            '/produk/create',
            "/produk/{$this->produk->id}",
            "/produk/{$this->produk->id}/edit",

            // Job Order
            '/job_order',
            "/job_order/{$this->jobOrder->id}",

            // Nota Pembelian
            '/nota_pembelian',
            '/nota_pembelian/create',
            "/nota_pembelian/{$this->notaPembelian->id}",

            // Nota Order
            '/nota_order',
            '/nota_order/create',
            "/nota_order/{$this->notaOrder->id}",
            "/nota_order/{$this->notaOrder->id}/edit",

            // Nota Produksi
            '/nota_produksi',
            '/nota_produksi/create',
            "/nota_produksi/{$this->notaProduksi->id}",
            "/nota_produksi/{$this->notaProduksi->id}/edit",

            // Nota Stok Keluar
            '/nota-stok-keluar',
            '/nota-stok-keluar/create',
            
            // Penyesuaian Stok
            '/penyesuaian_stok',
            '/penyesuaian_stok/create',

            // Print / PDF Views
            "/nota_production/{$this->notaProduksi->id}/cetak-pdf",
            "/nota_order/{$this->notaOrder->id}/print",
            "/nota-stok-keluar/{$this->notaStokKeluar->id}/print",
        ];

        foreach ($urls as $url) {
            $response = $this->actingAs($this->admin)->get($url);
            $this->assertEquals(
                200, 
                $response->getStatusCode(), 
                "Failed to load URL: {$url}. Status: {$response->getStatusCode()}"
            );
        }
    }
}
