<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UnitOfMeasure;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Company;
use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Fuel\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private Cart $cartA;

    private Cart $cartB;

    private CartItem $itemA;

    private CartItem $itemB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create two users
        $this->userA = User::create([
            'name' => 'User A',
            'email' => 'usera@fuelcab.com',
            'password' => bcrypt('password'),
            'role_type' => UserRole::Customer,
        ]);
        $this->userA->assignRole(UserRole::Customer->value);

        $this->userB = User::create([
            'name' => 'User B',
            'email' => 'userb@fuelcab.com',
            'password' => bcrypt('password'),
            'role_type' => UserRole::Customer,
        ]);
        $this->userB->assignRole(UserRole::Customer->value);

        // Create carts
        $this->cartA = Cart::create([
            'user_id' => $this->userA->id,
        ]);

        $this->cartB = Cart::create([
            'user_id' => $this->userB->id,
        ]);

        // Create a Category and Vendor for Product references
        $category = Category::create(['name' => 'Fuel Category', 'slug' => 'fuel-cat']);
        $company = Company::create(['name' => 'Company X', 'tax_number' => 'TAX1123', 'status' => 'active']);
        $vendor = Vendor::create([
            'company_id' => $company->id,
            'brand_name' => 'Vendor X',
            'status' => 'approved',
            'contact_email' => 'vendorx@example.com',
        ]);

        // Create product
        $product = Product::create([
            'id' => Str::uuid()->toString(),
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'name' => 'Regular Diesel',
            'slug' => 'regular-diesel',
            'sku' => 'DSL-REG',
            'price_per_unit' => 90.00,
            'unit_of_measure' => UnitOfMeasure::Litres,
            'is_active' => true,
            'ordering_enabled' => true,
            'min_order_quantity' => 100.0,
        ]);

        // Create cart items
        $this->itemA = CartItem::create([
            'cart_id' => $this->cartA->id,
            'product_id' => $product->id,
            'quantity' => 150,
            'price_per_unit' => 90.00,
        ]);

        $this->itemB = CartItem::create([
            'cart_id' => $this->cartB->id,
            'product_id' => $product->id,
            'quantity' => 200,
            'price_per_unit' => 90.00,
        ]);
    }

    /**
     * TEST: User A cannot update User B's cart item (IDOR Prevention)
     */
    public function test_user_cannot_update_another_users_cart_item(): void
    {
        Sanctum::actingAs($this->userA);

        $response = $this->patchJson("/api/v1/cart/items/{$this->itemB->id}", [
            'quantity' => 250,
        ]);

        // Expect authorization failure
        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthorized.');

        // Assert DB quantity has NOT changed
        $this->assertEquals(200, $this->itemB->fresh()->quantity);
    }

    /**
     * TEST: User A cannot delete User B's cart item (IDOR Prevention)
     */
    public function test_user_cannot_delete_another_users_cart_item(): void
    {
        Sanctum::actingAs($this->userA);

        $response = $this->deleteJson("/api/v1/cart/items/{$this->itemB->id}");

        // Expect authorization failure
        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthorized.');

        // Assert item still exists in database
        $this->assertDatabaseHas('cart_items', [
            'id' => $this->itemB->id,
        ]);
    }

    /**
     * TEST: User A can successfully update their own cart item
     */
    public function test_user_can_update_own_cart_item(): void
    {
        Sanctum::actingAs($this->userA);

        $response = $this->patchJson("/api/v1/cart/items/{$this->itemA->id}", [
            'quantity' => 180,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Assert DB quantity has changed
        $this->assertEquals(180, $this->itemA->fresh()->quantity);
    }

    /**
     * TEST: User A can successfully delete their own cart item
     */
    public function test_user_can_delete_own_cart_item(): void
    {
        Sanctum::actingAs($this->userA);

        $response = $this->deleteJson("/api/v1/cart/items/{$this->itemA->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Assert item is soft-deleted (CartItem uses SoftDeletes)
        $this->assertSoftDeleted('cart_items', [
            'id' => $this->itemA->id,
        ]);
    }
}
