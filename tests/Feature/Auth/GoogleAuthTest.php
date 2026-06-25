<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /**
     * Test that redirect endpoint returns the correct redirection URL.
     */
    public function test_google_redirect_endpoint(): void
    {
        $mockProvider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $mockProvider->shouldReceive('stateless')->andReturnSelf();
        $mockProvider->shouldReceive('redirect')->andReturnSelf();
        $mockProvider->shouldReceive('getTargetUrl')->andReturn('https://accounts.google.com/o/oauth2/auth?test=true');

        Socialite::shouldReceive('driver')->with('google')->andReturn($mockProvider);

        $response = $this->getJson(route('api.v1.auth.google'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'redirect_url'
                ]
            ]);
    }

    /**
     * Test Google callback registers a new user if one does not exist.
     */
    public function test_google_callback_registers_new_user(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-id-123');
        $socialiteUser->shouldReceive('getEmail')->andReturn('newuser@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://avatar.url/image.jpg');
        $socialiteUser->token = 'mock-google-token';

        $mockProvider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $mockProvider->shouldReceive('stateless')->andReturnSelf();
        $mockProvider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($mockProvider);

        $response = $this->getJson(route('api.v1.auth.google.callback'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role_type',
                        'google_avatar',
                    ],
                    'access_token',
                    'token_type'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'google_id' => 'google-id-123',
        ]);
    }

    /**
     * Test Google callback links to an existing user with same email.
     */
    public function test_google_callback_links_existing_user(): void
    {
        // Pre-create user with same email but no Google ID
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'google_id' => null,
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-id-456');
        $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://avatar.url/image.jpg');
        $socialiteUser->token = 'mock-google-token-456';

        $mockProvider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $mockProvider->shouldReceive('stateless')->andReturnSelf();
        $mockProvider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($mockProvider);

        $response = $this->getJson(route('api.v1.auth.google.callback'));

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'existing@example.com',
            'google_id' => 'google-id-456',
        ]);
    }
}
