<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestSignature
{
    /**
     * Verify HMAC-SHA256 signature on incoming webhook requests.
     *
     * Header: X-Signature: sha256=<hex_digest>
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret    = config('fuelcab.payment.webhook.secret');
        $tolerance = (int) config('fuelcab.payment.webhook.tolerance', 300);
        $signature = $request->header('X-Signature');

        if (! $signature || ! $secret) {
            throw new UnauthorizedException('Missing webhook signature.');
        }

        $payload  = $request->getContent();
        $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expected, $signature)) {
            throw new UnauthorizedException('Invalid webhook signature.');
        }

        // Optional: timestamp tolerance check
        $timestamp = (int) $request->header('X-Timestamp', 0);
        if ($timestamp && abs(time() - $timestamp) > $tolerance) {
            throw new UnauthorizedException('Webhook timestamp out of tolerance window.');
        }

        return $next($request);
    }
}
