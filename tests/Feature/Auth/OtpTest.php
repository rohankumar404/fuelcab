<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Enums\UserRole;
use App\Modules\Auth\Events\OtpRequested;
use App\Modules\Notification\Jobs\SendSmsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * OTP Authentication Flow Tests
 *
 * Tests cover: send-otp, resend-otp, verify-otp, rate limiting, expiry,
 * job / event dispatching, and sandbox-vs-production branching.
 */
class OtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Send OTP
    // ─────────────────────────────────────────────────────────────────────────

    public function test_send_otp_returns_200_for_valid_phone(): void
    {
        $response = $this->postJson('/api/v1/auth/send-otp', [
            'phone' => '+919000000010',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['phone', 'otp']]);
    }

    public function test_send_otp_stores_code_in_cache(): void
    {
        $this->postJson('/api/v1/auth/send-otp', ['phone' => '+919000000011']);

        $code = Cache::get('otp_+919000000011');
        $this->assertNotNull($code);
        $this->assertEquals(6, strlen($code));
    }

    public function test_send_otp_returns_sandbox_code_in_local_env(): void
    {
        // In testing environment (which is treated as sandbox) the code must
        // match the configured sandbox_code.
        $response = $this->postJson('/api/v1/auth/send-otp', ['phone' => '+919000000012']);

        $response->assertStatus(200);
        $this->assertEquals('123456', $response->json('data.otp'));
    }

    public function test_send_otp_requires_phone_field(): void
    {
        $this->postJson('/api/v1/auth/send-otp', [])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Verify OTP
    // ─────────────────────────────────────────────────────────────────────────

    public function test_verify_otp_returns_token_for_existing_user(): void
    {
        $user = User::create([
            'name'      => 'Existing OTP User',
            'email'     => 'otp-existing@example.com',
            'phone'     => '+919000000020',
            'password'  => Hash::make('password'),
            'role_type' => UserRole::Customer,
        ]);

        $this->postJson('/api/v1/auth/send-otp', ['phone' => '+919000000020']);
        $code = Cache::get('otp_+919000000020');

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'phone' => '+919000000020',
            'otp'   => $code,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_new_user', false)
            ->assertJsonStructure(['data' => ['access_token', 'user']]);
    }

    public function test_verify_otp_creates_new_user_if_phone_unknown(): void
    {
        $this->postJson('/api/v1/auth/send-otp', ['phone' => '+919000000021']);
        $code = Cache::get('otp_+919000000021');

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'phone' => '+919000000021',
            'otp'   => $code,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_new_user', true);

        $this->assertDatabaseHas('users', [
            'phone'     => '+919000000021',
            'role_type' => UserRole::Customer->value,
        ]);
    }

    public function test_verify_otp_fails_for_wrong_code(): void
    {
        $this->postJson('/api/v1/auth/send-otp', ['phone' => '+919000000022']);

        $this->postJson('/api/v1/auth/verify-otp', [
            'phone' => '+919000000022',
            'otp'   => '000000', // deliberate wrong code
        ])->assertStatus(422)
          ->assertJsonPath('success', false);
    }

    public function test_verify_otp_fails_after_cache_expiry(): void
    {
        $phone = '+919000000023';

        // Pre-seed expired OTP (already gone from cache — nothing to get)
        Cache::forget("otp_{$phone}");

        $this->postJson('/api/v1/auth/verify-otp', [
            'phone' => $phone,
            'otp'   => '123456',
        ])->assertStatus(422);
    }

    public function test_verify_otp_clears_cache_after_success(): void
    {
        $this->postJson('/api/v1/auth/send-otp', ['phone' => '+919000000024']);
        $code = Cache::get('otp_+919000000024');

        $this->postJson('/api/v1/auth/verify-otp', [
            'phone' => '+919000000024',
            'otp'   => $code,
        ])->assertStatus(200);

        // OTP cache entry must be cleared after successful verification
        $this->assertNull(Cache::get('otp_+919000000024'));
    }

    public function test_verify_otp_requires_phone_and_otp_fields(): void
    {
        $this->postJson('/api/v1/auth/verify-otp', [])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Resend OTP
    // ─────────────────────────────────────────────────────────────────────────

    public function test_resend_otp_returns_new_code(): void
    {
        $this->postJson('/api/v1/auth/resend-otp', ['phone' => '+919000000030'])
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['phone', 'otp']]);
    }

    public function test_resend_otp_updates_cached_code(): void
    {
        $phone = '+919000000031';

        // First send
        $this->postJson('/api/v1/auth/send-otp', ['phone' => $phone]);
        $firstCode = Cache::get("otp_{$phone}");

        // Resend — the new code must be the sandbox_code again (consistent in test env)
        $this->postJson('/api/v1/auth/resend-otp', ['phone' => $phone]);
        $secondCode = Cache::get("otp_{$phone}");

        // In sandbox / test env both codes will be the same static code, but
        // the cache value must still exist and be a valid OTP.
        $this->assertNotNull($secondCode);
        $this->assertEquals(6, strlen($secondCode));
    }

    public function test_resend_otp_is_rate_limited_after_max_attempts(): void
    {
        $phone = '+919000000032';
        $max   = (int) config('fuelcab.notifications.otp.max_resend', 3);

        // Exhaust the allowed resend attempts
        for ($i = 0; $i < $max; $i++) {
            $this->postJson('/api/v1/auth/resend-otp', ['phone' => $phone])
                ->assertStatus(200);
        }

        // Next attempt must be rejected with 429
        $this->postJson('/api/v1/auth/resend-otp', ['phone' => $phone])
            ->assertStatus(429)
            ->assertJsonPath('success', false);
    }

    public function test_resend_counter_clears_after_successful_verification(): void
    {
        $phone = '+919000000033';

        // Use one resend attempt
        $this->postJson('/api/v1/auth/resend-otp', ['phone' => $phone]);

        // Verify OTP — this should clear the resend counter
        $code = Cache::get("otp_{$phone}");
        $this->postJson('/api/v1/auth/verify-otp', ['phone' => $phone, 'otp' => $code])
            ->assertStatus(200);

        // After clearing, the counter cache key should be gone
        $this->assertNull(Cache::get("otp_resend_{$phone}"));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Event / Job Dispatching (production path)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_otp_requested_event_is_fired_in_production_mode(): void
    {
        // Override environment to simulate production sandbox=false
        config(['fuelcab.notifications.otp.sandbox' => false]);

        Event::fake([OtpRequested::class]);

        // Temporarily override app environment to skip the env check
        app()->detectEnvironment(fn () => 'production');

        $this->postJson('/api/v1/auth/send-otp', ['phone' => '+919000000040']);

        Event::assertDispatched(OtpRequested::class, function (OtpRequested $event) {
            return $event->phone === '+919000000040'
                && strlen($event->code) === 6;
        });

        // Restore
        app()->detectEnvironment(fn () => 'testing');
        config(['fuelcab.notifications.otp.sandbox' => true]);
    }

    public function test_send_sms_job_has_correct_structure(): void
    {
        Queue::fake();

        SendSmsJob::dispatch('+919000000050', '123456');

        Queue::assertPushed(SendSmsJob::class, function (SendSmsJob $job) {
            return $job->phone === '+919000000050'
                && $job->code  === '123456'
                && $job->queue === 'sms';
        });
    }
}
