<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\BahanBaku;
use App\Models\Penjahit;
use App\Models\JobOrder;
use App\Models\NotaOrder;
use App\Models\PemakaianBahan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NotaProduksiTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $penjahit;
    protected $customer;
    protected $produk;
    protected $varian;
    protected $bahanA;
    protected $bahanB;
    protected $notaOrder;
    protected $jobOrder;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Admin User
        $this->admin = User::firstOrCreate(
            ['username' => 'admin_test'],
            [
                'name' => 'Admin Test',
                'email' => 'admin_test@tokox.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // 2. Create Penjahit
        $this->penjahit = Penjahit::create([
            'nama' => 'Penjahit Test',
            'alamat' => 'Alamat Penjahit Test',
            'no_hp' => '0812345678',
        ]);

        // 3. Create Customer
        $this->customer = Customer::create([
            'nama' => 'Customer Test',
            'no_hp' => '081234567890',
            'alamat' => 'Alamat Customer Test',
        ]);

        // 4. Create Product & Varian
        $this->produk = Produk::create([
            'nama' => 'Pakaian Custom Test',
            'satuan' => 'Pcs',
            'customer_id' => $this->customer->id,
        ]);

        $this->varian = ProdukVarian::create([
            'produk_id' => $this->produk->id,
            'warna' => 'Hitam',
            'ukuran' => 'L',
            'stok_tersedia' => 0,
            'stok_defect' => 0,
            'harga_avg_produk' => 0,
        ]);

        // 5. Create Extra Bahan Baku
        $this->bahanA = BahanBaku::create([
            'nama' => 'Kain Tambahan A',
            'stok_total' => 10.0,
            'satuan' => 'Meter',
            'harga_rata_rata' => 20000,
        ]);

        $this->bahanB = BahanBaku::create([
            'nama' => 'Aksesoris Tambahan B',
            'stok_total' => 5.0,
            'satuan' => 'Pcs',
            'harga_rata_rata' => 5000,
        ]);

        // 6. Create Nota Order
        $this->notaOrder = NotaOrder::create([
            'no_order' => 'ORD-TEST-0001',
            'tipe_order' => 'Customer',
            'customer_id' => $this->customer->id,
            'tgl_order' => now(),
            'tgl_deadline' => now()->addDays(7),
            'total_harga' => 150000,
            'nominal_dp_masuk' => 50000,
            'status_bayar' => 'Belum Lunas',
            'status_order' => 'Valid',
        ]);

        // 7. Create Job Order
        $this->jobOrder = JobOrder::create([
            'nota_order_id' => $this->notaOrder->id,
            'produk_varian_id' => $this->varian->id,
            'jumlah' => 10,
            'biaya_jasa_satuan' => 0,
            'total_biaya_bahan' => 0,
            'total_hpp_job' => 0,
            'is_custom' => 1,
            'catatan_custom' => 'Tambah panjang 5cm',
            'status_pesanan' => 'Antrean',
        ]);
    }

    public function test_can_create_nota_produksi_with_multiple_extra_materials(): void
    {
        // Assert initial stocks
        $this->assertEquals(10.0, $this->bahanA->fresh()->stok_total);
        $this->assertEquals(5.0, $this->bahanB->fresh()->stok_total);

        // Post request to create production note with multiple extra materials
        $response = $this->actingAs($this->admin)->post('/nota_produksi', [
            'penjahit_id' => $this->penjahit->id,
            'job_order_ids' => [$this->jobOrder->id],
            'biaya_jasa' => [
                $this->jobOrder->id => 12000 // Upah per pcs
            ],
            'ongkir_kirim_bahan' => 15000,
            'ekstra_bahan_id' => [
                $this->jobOrder->id => [
                    $this->bahanA->id,
                    $this->bahanB->id
                ]
            ],
            'ekstra_jumlah' => [
                $this->jobOrder->id => [
                    2.5, // 2.5 Meter
                    3.0  // 3 Pcs
                ]
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert job order status updated to 'Diproduksi'
        $updatedJobOrder = $this->jobOrder->fresh();
        $this->assertEquals('Diproduksi', $updatedJobOrder->status_pesanan);
        $this->assertEquals(12000, $updatedJobOrder->biaya_jasa_satuan);

        // Assert stocks are decremented
        // Kain Tambahan A: 10.0 - 2.5 = 7.5
        $this->assertEquals(7.5, $this->bahanA->fresh()->stok_total);
        // Aksesoris Tambahan B: 5.0 - 3.0 = 2.0
        $this->assertEquals(2.0, $this->bahanB->fresh()->stok_total);

        // Assert PemakaianBahan records are created
        $this->assertDatabaseHas('pemakaian_bahan', [
            'job_order_id' => $this->jobOrder->id,
            'bahan_baku_id' => $this->bahanA->id,
            'jumlah' => 2.5,
            'harga_rata_saat_ini' => 20000,
            'tipe' => 'ekstra',
        ]);

        $this->assertDatabaseHas('pemakaian_bahan', [
            'job_order_id' => $this->jobOrder->id,
            'bahan_baku_id' => $this->bahanB->id,
            'jumlah' => 3.0,
            'harga_rata_saat_ini' => 5000,
            'tipe' => 'ekstra',
        ]);

        // Verify calculations:
        // total_biaya_bahan = (2.5 * 20000) + (3 * 5000) = 50000 + 15000 = 65000
        // total_jasa_jahit = 12000 * 10 = 120000
        // porsi_ongkir = 15000 (since it is the only job order, it gets the full amount)
        // total_hpp_job = 65000 + 120000 + 15000 = 200000
        $this->assertEquals(65000, $updatedJobOrder->total_biaya_bahan);
        $this->assertEquals(200000, $updatedJobOrder->total_hpp_job);

        // Assert HPP Audit page renders the correct raw material cost from the static database column
        $auditResponse = $this->actingAs($this->admin)->get("/job_order/{$this->jobOrder->id}");
        $auditResponse->assertStatus(200);
        $auditResponse->assertSee('Rp 65.000');
    }

    public function test_receiving_partial_and_full_production_updates_job_status(): void
    {
        // 1. Create the Nota Produksi first
        $this->actingAs($this->admin)->post('/nota_produksi', [
            'penjahit_id' => $this->penjahit->id,
            'job_order_ids' => [$this->jobOrder->id],
            'biaya_jasa' => [
                $this->jobOrder->id => 12000
            ],
            'ongkir_kirim_bahan' => 15000,
        ]);

        $this->assertEquals('Diproduksi', $this->jobOrder->fresh()->status_pesanan);

        // 2. Submit partial QC results: 4 accepted + 1 defect = 5 pcs (target is 10)
        $response = $this->actingAs($this->admin)->post('/nota-produksi/terima/store', [
            'job_order_id' => $this->jobOrder->id,
            'qty_accepted' => 4,
            'qty_rejected_defect' => 1,
            'qty_rejected' => 0,
            'ongkir_hasil_produksi' => 2000,
            'biaya_rework_tambahan' => 0,
            'catatan_qc' => 'Partial setoran 1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertEquals('Selesai Parsial', $this->jobOrder->fresh()->status_pesanan);

        // 3. Submit remaining QC results: 4 accepted + 1 rejected = 5 pcs (total cumulative 10)
        $response = $this->actingAs($this->admin)->post('/nota-produksi/terima/store', [
            'job_order_id' => $this->jobOrder->id,
            'qty_accepted' => 4,
            'qty_rejected_defect' => 0,
            'qty_rejected' => 1,
            'ongkir_hasil_produksi' => 2000,
            'biaya_rework_tambahan' => 0,
            'catatan_qc' => 'Final setoran',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertEquals('Selesai', $this->jobOrder->fresh()->status_pesanan);
    }

    public function test_receiving_production_calculates_ongkir_and_rework_costs(): void
    {
        // 1. Create the Nota Produksi first
        $this->actingAs($this->admin)->post('/nota_produksi', [
            'penjahit_id' => $this->penjahit->id,
            'job_order_ids' => [$this->jobOrder->id],
            'biaya_jasa' => [
                $this->jobOrder->id => 12000
            ],
            'ongkir_kirim_bahan' => 15000,
        ]);

        // 2. Submit partial QC results: 4 accepted + 1 defect = 5 pcs (target is 10)
        // With shipping = 3000, rework = 10000
        $response = $this->actingAs($this->admin)->post('/nota-produksi/terima/store', [
            'job_order_id' => $this->jobOrder->id,
            'qty_accepted' => 4,
            'qty_rejected_defect' => 1,
            'qty_rejected' => 0,
            'ongkir_hasil_produksi' => 3000,
            'biaya_rework_tambahan' => 10000,
            'catatan_qc' => 'Partial setoran 1',
        ]);
        $response->assertSessionHasNoErrors();

        // Check accumulated values on parent job_order
        $job = $this->jobOrder->fresh();
        $this->assertEquals(3000, $job->ongkir_terima_barang);
        $this->assertEquals(10000, $job->biaya_rework_tambahan);

        // 3. Submit remaining QC results: 4 accepted + 1 rejected = 5 pcs (total cumulative 10)
        // With shipping = 4000, rework = 15000
        $response = $this->actingAs($this->admin)->post('/nota-produksi/terima/store', [
            'job_order_id' => $this->jobOrder->id,
            'qty_accepted' => 4,
            'qty_rejected_defect' => 0,
            'qty_rejected' => 1,
            'ongkir_hasil_produksi' => 4000,
            'biaya_rework_tambahan' => 15000,
            'catatan_qc' => 'Final setoran',
        ]);
        $response->assertSessionHasNoErrors();

        // Check final accumulated values
        $job = $this->jobOrder->fresh();
        $this->assertEquals(7000, $job->ongkir_terima_barang); // 3000 + 4000
        $this->assertEquals(25000, $job->biaya_rework_tambahan); // 10000 + 15000
        $this->assertEquals('Selesai', $job->status_pesanan);

        // 4. Assert the HPP Audit page displays these costs and calculates HPP correctly
        $auditResponse = $this->actingAs($this->admin)->get("/job_order/{$this->jobOrder->id}");
        $auditResponse->assertStatus(200);

        // Verify the view calculations:
        // totalLayakJual = accepted (4+4) + defect (1+0) = 9
        // targetPcs = 10
        // total_hpp_job = 120000 + 15000 = 135000
        // porsiModalDiserap = (9 / 10) * 135000 = 121500
        // totalModalAktual = porsiModalDiserap (121500) + totalOngkirBalik (7000) + biaya_rework_tambahan (25000) = 153500
        // hppAktualSatuan = 153500 / 9 = 17056
        $auditResponse->assertSee('Rp 153.500'); // Total HPP
        $auditResponse->assertSee('Rp 17.056'); // Average HPP / Pcs
        $auditResponse->assertSee('Total Biaya Rework Tambahan'); // Rework cost section
    }

    public function test_creating_nota_produksi_updates_preexisting_bom_pemakaian_records(): void
    {
        // Create a pre-existing BOM pemakaian_bahan record with tgl_keluar = null
        // (representing a BOM requirement added during order placement)
        $bomBahan = BahanBaku::create([
            'nama' => 'Kain Utama BOM',
            'stok_total' => 20.0,
            'satuan' => 'Meter',
            'harga_rata_rata' => 15000,
        ]);

        $bomPemakaian = PemakaianBahan::create([
            'job_order_id' => $this->jobOrder->id,
            'bahan_baku_id' => $bomBahan->id,
            'jumlah' => 15.0,
            'harga_rata_saat_ini' => 15000,
            'tgl_keluar' => null, // Not yet released
        ]);

        // Also add the BOM relation to the job order varian
        // (so that it loops through this material in bill_of_materials)
        $this->varian->bill_of_materials()->create([
            'bahan_baku_id' => $bomBahan->id,
            'jumlah' => 1.5, // 1.5 meter per pcs * 10 pcs = 15.0 total
        ]);

        // Post request to create production note with both pre-existing BOM and extra materials
        $response = $this->actingAs($this->admin)->post('/nota_produksi', [
            'penjahit_id' => $this->penjahit->id,
            'job_order_ids' => [$this->jobOrder->id],
            'biaya_jasa' => [
                $this->jobOrder->id => 12000
            ],
            'ongkir_kirim_bahan' => 10000,
            'ekstra_bahan_id' => [
                $this->jobOrder->id => [
                    $this->bahanA->id
                ]
            ],
            'ekstra_jumlah' => [
                $this->jobOrder->id => [
                    2.0
                ]
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert that the pre-existing BOM record's tgl_keluar has been updated to now()
        $this->assertNotNull($bomPemakaian->fresh()->tgl_keluar);
        
        // Assert that the BOM stock was decremented: 20.0 - 15.0 = 5.0
        $this->assertEquals(5.0, $bomBahan->fresh()->stok_total);

        // Assert that the extra material PemakaianBahan was created and stock decremented
        $this->assertDatabaseHas('pemakaian_bahan', [
            'job_order_id' => $this->jobOrder->id,
            'bahan_baku_id' => $this->bahanA->id,
            'jumlah' => 2.0,
            'harga_rata_saat_ini' => 20000,
            'tipe' => 'ekstra',
        ]);
        $this->assertNotNull(PemakaianBahan::where('job_order_id', $this->jobOrder->id)->where('bahan_baku_id', $this->bahanA->id)->first()->tgl_keluar);
        $this->assertEquals(8.0, $this->bahanA->fresh()->stok_total);
    }

    public function test_partial_stock_release_in_nota_stok_keluar(): void
    {
        // 1. Create a Nota Produksi and complete a partial QC setoran (3 pcs out of 10)
        $this->actingAs($this->admin)->post('/nota_produksi', [
            'penjahit_id' => $this->penjahit->id,
            'job_order_ids' => [$this->jobOrder->id],
            'biaya_jasa' => [
                $this->jobOrder->id => 12000
            ],
            'ongkir_kirim_bahan' => 15000,
        ]);

        $this->actingAs($this->admin)->post('/nota-produksi/terima/store', [
            'job_order_id' => $this->jobOrder->id,
            'qty_accepted' => 3,
            'qty_rejected_defect' => 0,
            'qty_rejected' => 0,
            'ongkir_hasil_produksi' => 2000,
            'biaya_rework_tambahan' => 0,
            'catatan_qc' => 'First partial production completed',
        ]);

        // 2. Try to release more than completed custom stock (e.g. 4 pcs) -> should throw exception/error
        $response = $this->actingAs($this->admin)->post('/nota-stok-keluar', [
            'customer_id' => $this->customer->id,
            'tipe_keluar' => 'penjualan_order',
            'nota_order_id' => $this->notaOrder->id,
            'total_bayar' => 100000, // Sisa pelunasan is 100k (150k total - 50k dp)
            'items' => [
                [
                    'produk_varian_id' => $this->varian->id,
                    'qty' => 4,
                    'harga_satuan' => 15000,
                ]
            ]
        ]);
        $response->assertSessionHas('error'); // Error because sisaStokCustom is 3, but trying to take 4.

        // 3. Release valid partial stock (2 pcs) with full payment (100000)
        $response = $this->actingAs($this->admin)->post('/nota-stok-keluar', [
            'customer_id' => $this->customer->id,
            'tipe_keluar' => 'penjualan_order',
            'nota_order_id' => $this->notaOrder->id,
            'total_bayar' => 100000,
            'items' => [
                [
                    'produk_varian_id' => $this->varian->id,
                    'qty' => 2,
                    'harga_satuan' => 15000,
                ]
            ]
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Assert payment is now Lunas
        $this->assertEquals('Lunas', $this->notaOrder->fresh()->status_bayar);

        // 4. Try to release remaining but not yet completed custom stock (e.g. trying to release 2 pcs, but only 3 completed and 2 already taken, so only 1 left ready)
        $response = $this->actingAs($this->admin)->post('/nota-stok-keluar', [
            'customer_id' => $this->customer->id,
            'tipe_keluar' => 'penjualan_order',
            'nota_order_id' => $this->notaOrder->id,
            'total_bayar' => 0, // Already lunas, so 0 is valid
            'items' => [
                [
                    'produk_varian_id' => $this->varian->id,
                    'qty' => 2,
                    'harga_satuan' => 15000,
                ]
            ]
        ]);
        $response->assertSessionHas('error'); // Error because only 1 pc is ready in stock (3 completed - 2 taken)

        // 5. Release valid remaining stock (1 pc) with 0 payment
        $response = $this->actingAs($this->admin)->post('/nota-stok-keluar', [
            'customer_id' => $this->customer->id,
            'tipe_keluar' => 'penjualan_order',
            'nota_order_id' => $this->notaOrder->id,
            'total_bayar' => 0,
            'items' => [
                [
                    'produk_varian_id' => $this->varian->id,
                    'qty' => 1,
                    'harga_satuan' => 15000,
                ]
            ]
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }
}
