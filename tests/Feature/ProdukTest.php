<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Produk;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProdukTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $staff;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->admin = User::firstOrCreate(
            ['username' => 'admin_test'],
            [
                'name' => 'Admin Test',
                'email' => 'admin_test@tokox.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $this->staff = User::firstOrCreate(
            ['username' => 'staff_test'],
            [
                'name' => 'Staff Test',
                'email' => 'staff_test@tokox.com',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );

        // Create a test customer
        $this->customer = Customer::create([
            'nama' => 'Customer Test',
            'no_hp' => '081234567890',
            'alamat' => 'Alamat Test',
        ]);
    }

    public function test_admin_can_access_create_product_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/produk/create');

        $response->assertStatus(200);
        $response->assertSee('Form Tambah Produk');
    }

    /**
     * Test staff cannot access create product page.
     */
    public function test_staff_cannot_access_create_product_page(): void
    {
        $response = $this->actingAs($this->staff)->get('/produk/create');

        $response->assertRedirect(route('dashboard.index'));
        $response->assertSessionHas('error');
    }

    /**
     * Test creating a standard product.
     */
    public function test_admin_can_create_standard_product(): void
    {
        $response = $this->actingAs($this->admin)->post('/produk', [
            'nama' => 'Standard Product A',
            'satuan' => 'Pcs',
            'customer_id' => '',
        ]);

        $response->assertRedirect('/produk');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('produk', [
            'nama' => 'Standard Product A',
            'satuan' => 'Pcs',
            'customer_id' => null,
        ]);
    }

    /**
     * Test creating a custom product.
     */
    public function test_admin_can_create_custom_product(): void
    {
        $response = $this->actingAs($this->admin)->post('/produk', [
            'nama' => 'Custom Product B',
            'satuan' => 'Lusin',
            'customer_id' => $this->customer->id,
        ]);

        $response->assertRedirect('/produk');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('produk', [
            'nama' => 'Custom Product B',
            'satuan' => 'Lusin',
            'customer_id' => $this->customer->id,
        ]);
    }

    /**
     * Test admin can access edit product page.
     */
    public function test_admin_can_access_edit_product_page(): void
    {
        $produk = Produk::create([
            'nama' => 'Product to Edit',
            'satuan' => 'Pcs',
            'customer_id' => null,
        ]);

        $response = $this->actingAs($this->admin)->get("/produk/{$produk->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Produk');
        $response->assertSee('Product to Edit');
    }

    /**
     * Test admin can update product.
     */
    public function test_admin_can_update_product(): void
    {
        $produk = Produk::create([
            'nama' => 'Product to Update',
            'satuan' => 'Pcs',
            'customer_id' => null,
        ]);

        $response = $this->actingAs($this->admin)->put("/produk/{$produk->id}", [
            'nama' => 'Product Updated',
            'satuan' => 'Set',
            'customer_id' => $this->customer->id,
        ]);

        $response->assertRedirect('/produk');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('produk', [
            'id' => $produk->id,
            'nama' => 'Product Updated',
            'satuan' => 'Set',
            'customer_id' => $this->customer->id,
        ]);
    }
}
