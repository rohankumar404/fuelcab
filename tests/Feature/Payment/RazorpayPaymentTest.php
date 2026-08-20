<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Enums\SalesChannel;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\User;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Helpers\InvoicePdfGenerator;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Actions\InitiatePaymentAction;
use App\Modules\Payment\Actions\RefundPaymentAction;
use App\Modules\Payment\Actions\VerifyPaymentAction;
use App\Modules\Payment\Events\PaymentFailed;
use App\Modules\Payment\Events\PaymentVerified;
use App\Modules\Payment\Gateways\RazorpayGateway;
use App\Modules\Payment\Models\Payment;
use App\Modules\Vendor\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RazorpayPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Vendor $vendor;

    private Address $address;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->customer = User::create([
            'name' => 'Test Customer',
            'email' => 'paycust@test.com',
            'phone' => '+919876543210',
            'password' => bcrypt('password123'),
            'role_type' => UserRole::Customer,
        ]);

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
            'brand_name' => 'Fuel Express',
            'status' => 'approved',
            'commission_rate' => 5.00,
        ]);

        $this->address = Address::create([
            'user_id' => $this->customer->id,
            'addressable_type' => 'App\Models\User',
            'address_line_1' => '123 Test Lane',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560001',
            'latitude' => 12.9716,
            'longitude' => 77.5946,
        ]);

        $this->order = Order::create([
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'delivery_address_id' => $this->address->id,
            'subtotal_amount' => 1000.00,
            'delivery_fee' => 50.00,
            'tax_amount' => 180.00,
            'total_amount' => 1230.00,
            'status' => OrderStatus::Pending,
            'channel' => SalesChannel::Direct,
        ]);
    }

    /**
     * Test RazorpayGateway::initiate() creates an order via Razorpay API.
     */
    public function test_razorpay_gateway_initiates_payment_order(): void
    {
        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_testABC123',
                'amount' => 123000,
                'currency' => 'INR',
                'receipt' => $this->order->id,
                'status' => 'created',
            ], 200),
        ]);

        $gateway = new RazorpayGateway('rzp_test_key', 'rzp_test_secret');
        $result = $gateway->initiate([
            'order_id' => $this->order->id,
            'amount' => 1230.00,
        ]);

        $this->assertEquals('order_testABC123', $result['id']);
        $this->assertEquals(1230.00, $result['amount']);
        $this->assertEquals('INR', $result['currency']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'razorpay.com/v1/orders')
                && $request['amount'] === 123000
                && $request['currency'] === 'INR';
        });
    }

    /**
     * Test RazorpayGateway::verify() validates HMAC signature and fetches capture status.
     */
    public function test_razorpay_gateway_verifies_valid_signature(): void
    {
        $secret = 'rzp_test_secret';
        $orderId = 'order_testABC123';
        $paymentId = 'pay_testPAY456';
        $signature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id' => $paymentId,
                'status' => 'captured',
                'amount' => 123000,
            ], 200),
        ]);

        $gateway = new RazorpayGateway('rzp_test_key', $secret);
        $verified = $gateway->verify([
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);

        $this->assertTrue($verified);
    }

    /**
     * Test RazorpayGateway::verify() rejects tampered signatures.
     */
    public function test_razorpay_gateway_rejects_invalid_signature(): void
    {
        $gateway = new RazorpayGateway('rzp_test_key', 'rzp_test_secret');
        $verified = $gateway->verify([
            'razorpay_order_id' => 'order_testABC123',
            'razorpay_payment_id' => 'pay_testPAY456',
            'razorpay_signature' => 'tampered_bad_signature',
        ]);

        $this->assertFalse($verified);
    }

    /**
     * Test RazorpayGateway captures an 'authorized' payment automatically.
     */
    public function test_razorpay_gateway_captures_authorized_payment(): void
    {
        $secret = 'rzp_test_secret';
        $orderId = 'order_testABC123';
        $paymentId = 'pay_testPAY456';
        $signature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id' => $paymentId,
                'status' => 'authorized',
                'amount' => 123000,
            ], 200),
            "https://api.razorpay.com/v1/payments/{$paymentId}/capture" => Http::response([
                'id' => $paymentId,
                'status' => 'captured',
                'amount' => 123000,
            ], 200),
        ]);

        $gateway = new RazorpayGateway('rzp_test_key', $secret);
        $verified = $gateway->verify([
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);

        $this->assertTrue($verified);

        Http::assertSent(fn ($req) => str_contains($req->url(), '/capture'));
    }

    /**
     * Test RazorpayGateway::refund() calls the Razorpay refund endpoint.
     */
    public function test_razorpay_gateway_processes_refund(): void
    {
        Http::fake([
            'https://api.razorpay.com/v1/payments/pay_testPAY456/refund' => Http::response([
                'id' => 'rfnd_testRFND789',
                'payment_id' => 'pay_testPAY456',
                'amount' => 123000,
                'status' => 'processed',
            ], 200),
        ]);

        $gateway = new RazorpayGateway('rzp_test_key', 'rzp_test_secret');
        $result = $gateway->refund([
            'payment_id' => 'pay_testPAY456',
            'amount' => 1230.00,
        ]);

        $this->assertEquals('rfnd_testRFND789', $result['id']);
        $this->assertEquals('pay_testPAY456', $result['payment_id']);
        $this->assertEquals('processed', $result['status']);

        Http::assertSent(fn ($req) => str_contains($req->url(), '/refund'));
    }

    /**
     * Test InitiatePaymentAction creates a DB record and calls the gateway.
     */
    public function test_initiate_payment_action_creates_pending_payment(): void
    {
        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_GW001',
                'amount' => 123000,
                'status' => 'created',
            ], 200),
        ]);

        $action = app(InitiatePaymentAction::class);
        $dto = $action->execute($this->order->id, 'razorpay', 1230.00);

        $this->assertEquals('order_GW001', $dto->gatewayOrderId);
        $this->assertEquals(1230.00, $dto->amount);
        $this->assertEquals('razorpay', $dto->gateway);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'status' => 'pending',
            'gateway_transaction_id' => 'order_GW001',
        ]);
    }

    /**
     * Test VerifyPaymentAction completes payment and transitions order to Accepted.
     */
    public function test_verify_payment_action_marks_order_accepted_on_success(): void
    {
        Event::fake([PaymentVerified::class]);

        $secret = config('fuelcab.payment.gateways.razorpay.secret', 'rzp_test_secret');
        $orderId = 'order_GW002';
        $paymentId = 'pay_GW002PAY';
        $signature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        // Create a pending payment record
        $payment = Payment::create([
            'order_id' => $this->order->id,
            'payment_gateway' => 'razorpay',
            'gateway_transaction_id' => $orderId,
            'amount' => 1230.00,
            'status' => 'pending',
        ]);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id' => $paymentId,
                'status' => 'captured',
                'amount' => 123000,
            ], 200),
        ]);

        $action = app(VerifyPaymentAction::class);
        $verified = $action->execute([
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);

        $this->assertTrue($verified);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'accepted',
        ]);

        Event::assertDispatched(PaymentVerified::class);
    }

    /**
     * Test VerifyPaymentAction marks payment failed on bad signature.
     */
    public function test_verify_payment_action_marks_failed_on_bad_signature(): void
    {
        Event::fake([PaymentFailed::class]);

        $payment = Payment::create([
            'order_id' => $this->order->id,
            'payment_gateway' => 'razorpay',
            'gateway_transaction_id' => 'order_BAD001',
            'amount' => 1230.00,
            'status' => 'pending',
        ]);

        $action = app(VerifyPaymentAction::class);
        $verified = $action->execute([
            'razorpay_order_id' => 'order_BAD001',
            'razorpay_payment_id' => 'pay_BAD001',
            'razorpay_signature' => 'tampered_signature',
        ]);

        $this->assertFalse($verified);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'failed',
        ]);

        Event::assertDispatched(PaymentFailed::class);
    }

    /**
     * Test RefundPaymentAction refunds a completed payment via Razorpay.
     */
    public function test_refund_payment_action_marks_payment_refunded(): void
    {
        Http::fake([
            'https://api.razorpay.com/v1/payments/pay_COMPL001/refund' => Http::response([
                'id' => 'rfnd_RFD001',
                'payment_id' => 'pay_COMPL001',
                'amount' => 123000,
                'status' => 'processed',
            ], 200),
        ]);

        $payment = Payment::create([
            'order_id' => $this->order->id,
            'payment_gateway' => 'razorpay',
            'gateway_transaction_id' => 'pay_COMPL001',
            'amount' => 1230.00,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $action = app(RefundPaymentAction::class);
        $result = $action->execute($payment->id);

        $this->assertEquals('rfnd_RFD001', $result['id']);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded',
        ]);
    }

    /**
     * Test payment initiation API endpoint.
     */
    public function test_payment_initiate_api_endpoint(): void
    {
        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_API001',
                'amount' => 123000,
                'status' => 'created',
            ], 200),
        ]);

        Sanctum::actingAs($this->customer, ['customer:*']);

        $response = $this->postJson(route('api.v1.payments.initiate'), [
            'order_id' => $this->order->id,
            'payment_method' => 'razorpay',
            'amount' => 1230.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.gateway_order_id', 'order_API001')
            ->assertJsonPath('data.gateway', 'razorpay');
    }

    /**
     * Test payment history API endpoint returns paginated results.
     */
    public function test_payment_history_api_endpoint(): void
    {
        Payment::create([
            'order_id' => $this->order->id,
            'payment_gateway' => 'razorpay',
            'gateway_transaction_id' => 'pay_HIST001',
            'amount' => 1230.00,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        Sanctum::actingAs($this->customer, ['customer:*']);

        $response = $this->getJson(route('api.v1.payments.history'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['data' => [['id', 'order_id', 'status', 'amount', 'payment_gateway']]],
            ]);
    }

    /**
     * Test webhook handler verifies signature and processes payment.captured event.
     */
    public function test_webhook_processes_payment_captured(): void
    {
        Event::fake([PaymentVerified::class]);

        $payment = Payment::create([
            'order_id' => $this->order->id,
            'payment_gateway' => 'razorpay',
            'gateway_transaction_id' => 'order_WBHK001',
            'amount' => 1230.00,
            'status' => 'pending',
        ]);

        $webhookPayload = json_encode([
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_WBHK001',
                        'order_id' => 'order_WBHK001',
                        'amount' => 123000,
                        'status' => 'captured',
                    ],
                ],
            ],
        ]);

        $webhookSecret = config('fuelcab.payment.webhook.secret', 'webhook_secret_123');
        $signature = hash_hmac('sha256', $webhookPayload, $webhookSecret);

        $response = $this->postJson(route('api.v1.payments.webhook'), json_decode($webhookPayload, true), [
            'X-Razorpay-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200)
            ->assertJson(['received' => true]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'accepted',
        ]);

        Event::assertDispatched(PaymentVerified::class);
    }

    /**
     * Test webhook rejects requests with invalid signature.
     */
    public function test_webhook_rejects_invalid_signature(): void
    {
        $response = $this->postJson(route('api.v1.payments.webhook'), ['event' => 'payment.captured'], [
            'X-Razorpay-Signature' => 'invalid_sig_here',
        ]);

        $response->assertStatus(400);
    }

    /**
     * Test webhook processes payment.failed event.
     */
    public function test_webhook_processes_payment_failed(): void
    {
        Event::fake([PaymentFailed::class]);

        $payment = Payment::create([
            'order_id' => $this->order->id,
            'payment_gateway' => 'razorpay',
            'gateway_transaction_id' => 'order_WBHK_FAIL',
            'amount' => 1230.00,
            'status' => 'pending',
        ]);

        $webhookPayload = json_encode([
            'event' => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_WBHK_FAIL',
                        'order_id' => 'order_WBHK_FAIL',
                        'error_description' => 'Your card was declined.',
                        'status' => 'failed',
                    ],
                ],
            ],
        ]);

        $webhookSecret = config('fuelcab.payment.webhook.secret', 'webhook_secret_123');
        $signature = hash_hmac('sha256', $webhookPayload, $webhookSecret);

        $response = $this->postJson(route('api.v1.payments.webhook'), json_decode($webhookPayload, true), [
            'X-Razorpay-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'failed',
        ]);

        Event::assertDispatched(PaymentFailed::class);
    }

    /**
     * Test InvoicePdfGenerator produces an invoice file with correct GST breakdown.
     */
    public function test_invoice_pdf_generator_creates_file(): void
    {
        Storage::fake('public');

        $this->order->load(['customer', 'vendor']);

        $generator = new InvoicePdfGenerator;
        $path = $generator->generate($this->order);

        Storage::disk('public')->assertExists("invoices/{$this->order->id}.pdf");

        // Verify GST content is present in invoice
        $content = Storage::disk('public')->get("invoices/{$this->order->id}.pdf");
        $this->assertStringContainsString('TAX INVOICE', $content);
        $this->assertStringContainsString('CGST', $content);
        $this->assertStringContainsString('SGST', $content);
        $this->assertStringContainsString('GRAND TOTAL', $content);
    }
}
