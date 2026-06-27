<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BahanBaku;
use App\Models\Supplier;
use App\Models\NotaPembelian;
use App\Models\NotaPembelianDetail;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\JobOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NotaPembelianTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $supplier;
    protected $bahan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_test'],
            [
                'name' => 'Admin Test',
                'email' => 'admin_test@tokox.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $this->supplier = Supplier::create([
            'nama' => 'Supplier Test',
            'alamat' => 'Alamat Supplier',
            'no_hp' => '081234567890',
        ]);

        $this->bahan = BahanBaku::create([
            'nama' => 'Kain Katun Test',
            'satuan' => 'Meter',
            'stok_total' => 5,
            'harga_rata_rata' => 10000,
        ]);
    }

    /**
     * Test deleting a purchase note successfully reverts stock and average HPP.
     */
    public function test_delete_nota_pembelian_reverts_average_and_stock(): void
    {
        // 1. Create a Nota Pembelian (adding 10 meters @ 20000)
        // Original: 5 @ 10000. New: 10 @ 20000.
        // Expected Avg = ((5 * 10000) + (10 * 20000)) / 15 = 250000 / 15 = 16666.67
        $nota = NotaPembelian::create([
            'supplier_id' => $this->supplier->id,
            'tgl_beli' => now(),
            'total_harga_bahan' => 200000,
            'ongkir' => 0,
            'total_bayar' => 200000,
        ]);

        $detail = NotaPembelianDetail::create([
            'nota_pembelian_id' => $nota->id,
            'bahan_baku_id' => $this->bahan->id,
            'jumlah' => 10,
            'harga_satuan' => 20000,
            'subtotal' => 200000,
        ]);

        $this->bahan->update([
            'stok_total' => 15,
            'harga_rata_rata' => 16666.67,
        ]);

        // 2. Perform delete action
        $response = $this->actingAs($this->admin)->delete(route('nota_pembelian.destroy', $nota->id));

        $response->assertRedirect(route('nota_pembelian.index'));
        $response->assertSessionHas('success');

        // 3. Verify stock and HPP reverted back to original (5 meters @ 10000)
        $this->bahan->refresh();
        $this->assertEquals(5, $this->bahan->stok_total);
        $this->assertEquals(10000, round($this->bahan->harga_rata_rata));

        // Verify database records deleted
        $this->assertDatabaseMissing('nota_pembelian', ['id' => $nota->id]);
        $this->assertDatabaseMissing('nota_pembelian_has_bahan_baku', ['id' => $detail->id]);
    }

    /**
     * Test deleting a purchase note fails if raw materials are already consumed.
     */
    public function test_delete_nota_pembelian_fails_if_stock_insufficient(): void
    {
        // 1. Create a Nota Pembelian (adding 10 meters @ 20000)
        $nota = NotaPembelian::create([
            'supplier_id' => $this->supplier->id,
            'tgl_beli' => now(),
            'total_harga_bahan' => 200000,
            'ongkir' => 0,
            'total_bayar' => 200000,
        ]);

        $detail = NotaPembelianDetail::create([
            'nota_pembelian_id' => $nota->id,
            'bahan_baku_id' => $this->bahan->id,
            'jumlah' => 10,
            'harga_satuan' => 20000,
            'subtotal' => 200000,
        ]);

        $this->bahan->update([
            'stok_total' => 15,
            'harga_rata_rata' => 16666.67,
        ]);

        // 2. Consume stock so total stock is less than 10 meters (e.g. reduce to 8 meters)
        $this->bahan->update(['stok_total' => 8]);

        // 3. Try to delete the purchase note
        $response = $this->actingAs($this->admin)->delete(route('nota_pembelian.destroy', $nota->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');

        // Verify database records are still there
        $this->assertDatabaseHas('nota_pembelian', ['id' => $nota->id]);
    }

    /**
     * Test graceful constraint handling when deleting a referenced customer.
     */
    public function test_delete_customer_fails_gracefully_when_referenced(): void
    {
        $customer = \App\Models\Customer::create([
            'nama' => 'Customer Test C',
            'alamat' => 'Alamat Customer',
            'no_hp' => '081234567890',
        ]);

        // Create a nota order tied to this customer
        \App\Models\NotaOrder::create([
            'no_order' => 'ORD-TEST-001',
            'tipe_order' => 'Customer',
            'customer_id' => $customer->id,
            'tgl_order' => now(),
            'tgl_deadline' => now()->addDays(5),
            'total_harga' => 100000,
            'nominal_dp_masuk' => 20000,
            'status_bayar' => 'Belum Lunas',
            'status_order' => 'Valid',
        ]);

        // Attempt to delete customer
        $response = $this->actingAs($this->admin)->delete("/customer/{$customer->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Verify customer was not deleted
        $this->assertDatabaseHas('customer', ['id' => $customer->id]);
    }
}
