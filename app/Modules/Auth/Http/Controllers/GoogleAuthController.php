<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\DTOs\GoogleLoginDTO;
use App\Modules\Auth\Http\Resources\AuthTokenResource;
use App\Modules\Auth\Services\GoogleAuthService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected readonly GoogleAuthService $googleAuthService
    ) {}

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return JsonResponse
     */
    public function redirectToGoogle(): JsonResponse
    {
        try {
            // For a single page app / mobile app, we can either return the URL or do a direct redirect
            $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
            return $this->success(['redirect_url' => $url], 'Redirect URL generated');
        } catch (Exception $e) {
            return $this->error('Failed to generate Google redirect URL', $e->getMessage(), 500);
        }
    }

    /**
     * Obtain the user information from Google.
     *
     * @return JsonResponse
     */
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            // Retrieve stateless user from Socialite
            $googleUser = Socialite::driver('google')->stateless()->user();

            $dto = GoogleLoginDTO::fromArray([
                'google_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar(),
                'token' => $googleUser->token,
                'role_type' => request('role_type'), // Dynamic role selection if requested during signup
            ]);

            $result = $this->googleAuthService->handleGoogleUser($dto);

            return $this->success(
                new AuthTokenResource($result),
                'Successfully authenticated with Google'
            );
        } catch (Exception $e) {
            return $this->error('Google authentication failed', $e->getMessage(), 400);
        }
    }
}
