<?php

declare(strict_types=1);

namespace Tests\Feature\Notification;

use App\Enums\SalesChannel;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\User;
use App\Modules\Notification\Models\PushToken;
use App\Modules\Notification\Services\FcmService;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Notifications\OrderPlacedNotification;
use App\Modules\Payment\Events\PaymentVerified;
use App\Modules\Payment\Models\Payment;
use App\Modules\Vendor\Events\VendorApproved;
use App\Modules\Vendor\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Vendor $vendor;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::create([
            'name' => 'Test Push User',
            'email' => 'pushuser@test.com',
            'phone' => '+919999988888',
            'password' => bcrypt('password123'),
            'role_type' => UserRole::Customer,
        ]);

        // Create a default vendor for tests that need orders
        $companyId = Str::uuid()->toString();
        DB::table('companies')->insert([
            'id' => $companyId,
            'name' => 'Test Vendor Corp',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->vendor = Vendor::create([
            'company_id' => $companyId,
            'brand_name' => 'Mega Fuels',
            'status' => 'approved',
            'commission_rate' => 5.00,
        ]);

        // Create a default address
        $this->address = Address::create([
            'user_id' => $this->user->id,
            'addressable_type' => 'App\Models\User',
            'address_line_1' => '123 Test Street',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560001',
            'latitude' => 12.9716,
            'longitude' => 77.5946,
        ]);
    }

    /**
     * Test push token registration via API endpoint.
     */
    public function test_can_register_push_token_via_api(): void
    {
        Sanctum::actingAs($this->user, ['customer:*']);

        $response = $this->postJson(route('api.v1.notifications.push_token.store'), [
            'token' => 'fcm-token-xyz-123',
            'platform' => 'android',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.registered', true);

        $this->assertDatabaseHas('push_tokens', [
            'user_id' => $this->user->id,
            'token' => 'fcm-token-xyz-123',
            'platform' => 'android',
            'is_active' => true,
        ]);

        // Test upsert capability on the same token
        $response2 = $this->postJson(route('api.v1.notifications.push_token.store'), [
            'token' => 'fcm-token-xyz-123',
            'platform' => 'ios',
        ]);

        $response2->assertStatus(200);
        $this->assertDatabaseHas('push_tokens', [
            'user_id' => $this->user->id,
            'token' => 'fcm-token-xyz-123',
            'platform' => 'ios', // updated
        ]);
    }

    /**
     * Test FcmService legacy API delivery and deactivation on invalid tokens.
     */
    public function test_fcm_service_sends_payload_and_deactivates_stale_tokens(): void
    {
        // Add one good token and one bad token to DB
        $tokenGood = PushToken::create([
            'user_id' => $this->user->id,
            'token' => 'token-good',
            'platform' => 'android',
            'is_active' => true,
        ]);

        $tokenBad = PushToken::create([
            'user_id' => $this->user->id,
            'token' => 'token-bad',
            'platform' => 'ios',
            'is_active' => true,
        ]);

        config(['fuelcab.notifications.fcm.server_key' => 'fake-server-key']);

        // Mock Firebase legacy API response
        Http::fake([
            'https://fcm.googleapis.com/fcm/send' => Http::response([
                'multicast_id' => 12345,
                'success' => 1,
                'failure' => 1,
                'canonical_ids' => 0,
                'results' => [
                    ['message_id' => 'msg_001'],
                    ['error' => 'NotRegistered'],
                ],
            ], 200),
        ]);

        $service = app(FcmService::class);
        $result = $service->sendNotification(['token-good', 'token-bad'], 'Hello Title', 'Hello Body');

        $this->assertTrue($result);

        // Good token remains active
        $this->assertTrue((bool) $tokenGood->fresh()->is_active);

        // Bad token gets marked inactive
        $this->assertFalse((bool) $tokenBad->fresh()->is_active);
    }

    /**
     * Test notification dispatch invokes FcmChannel which routes to FCM HTTP API.
     */
    public function test_notification_dispatch_routes_to_fcm(): void
    {
        config(['fuelcab.notifications.fcm.server_key' => 'fake-server-key']);

        Http::fake([
            'https://fcm.googleapis.com/fcm/send' => Http::response([
                'success' => 1,
                'failure' => 0,
            ], 200),
        ]);

        // Register push token for user
        PushToken::create([
            'user_id' => $this->user->id,
            'token' => 'test-push-token-123',
            'platform' => 'android',
            'is_active' => true,
        ]);

        // Create a mock order
        $order = Order::create([
            'customer_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'delivery_address_id' => $this->address->id,
            'subtotal_amount' => 100.00,
            'delivery_fee' => 10.00,
            'tax_amount' => 19.80,
            'total_amount' => 129.80,
            'status' => OrderStatus::Pending,
            'channel' => SalesChannel::Direct,
        ]);

        $this->user->notify(new OrderPlacedNotification($order));

        Http::assertSent(function ($request) use ($order) {
            return $request->url() === 'https://fcm.googleapis.com/fcm/send'
                && $request['notification']['title'] === "Order Confirmed — #{$order->id}"
                && in_array('test-push-token-123', $request['registration_ids']);
        });
    }

    /**
     * Test Vendor Approval listener dispatches VendorApprovedNotification to vendor users.
     */
    public function test_vendor_approval_dispatches_push_notification_to_users(): void
    {
        config(['fuelcab.notifications.fcm.server_key' => 'fake-server-key']);

        Http::fake([
            'https://fcm.googleapis.com/fcm/send' => Http::response([
                'success' => 1,
                'failure' => 0,
            ], 200),
        ]);

        // Associate our test user with this vendor
        $this->user->update(['vendor_id' => $this->vendor->id]);

        // Register push token for vendor user
        PushToken::create([
            'user_id' => $this->user->id,
            'token' => 'vendor-push-token',
            'platform' => 'web',
            'is_active' => true,
        ]);

        // Simulate vendor approval
        $this->vendor->update(['status' => 'approved', 'vendor_code' => 'VEND009']);

        event(new VendorApproved($this->vendor->fresh()));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://fcm.googleapis.com/fcm/send'
                && $request['notification']['title'] === 'Vendor Account Approved 🎉'
                && in_array('vendor-push-token', $request['registration_ids'])
                && $request['data']['vendor_id'] === $this->vendor->id;
        });
    }

    /**
     * Test PaymentSuccessfulNotification gets triggered on PaymentVerified event.
     */
    public function test_payment_verified_dispatches_push_notification(): void
    {
        config(['fuelcab.notifications.fcm.server_key' => 'fake-server-key']);

        Http::fake([
            'https://fcm.googleapis.com/fcm/send' => Http::response([
                'success' => 1,
                'failure' => 0,
            ], 200),
        ]);

        // Register push token
        PushToken::create([
            'user_id' => $this->user->id,
            'token' => 'customer-payment-token',
            'platform' => 'ios',
            'is_active' => true,
        ]);

        $order = Order::create([
            'customer_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'delivery_address_id' => $this->address->id,
            'subtotal_amount' => 100.00,
            'delivery_fee' => 10.00,
            'tax_amount' => 19.80,
            'total_amount' => 129.80,
            'status' => OrderStatus::Pending,
            'channel' => SalesChannel::Direct,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_gateway' => 'razorpay',
            'gateway_transaction_id' => 'pay_abc123',
            'amount' => 129.80,
            'status' => 'completed',
        ]);

        event(new PaymentVerified($payment));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://fcm.googleapis.com/fcm/send'
                && $request['notification']['title'] === 'Payment Confirmed ✅'
                && in_array('customer-payment-token', $request['registration_ids'])
                && $request['data']['transaction_id'] === 'pay_abc123';
        });
    }
}
