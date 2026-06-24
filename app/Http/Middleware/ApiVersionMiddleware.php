<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersionMiddleware
{
    /**
     * Deprecated versions that still function but return a warning header.
     */
    private const DEPRECATED_VERSIONS = [];

    /**
     * Versions that are fully sunset and blocked.
     */
    private const SUNSET_VERSIONS = [];

    public function handle(Request $request, Closure $next): Response
    {
        // Extract version from URL prefix (e.g. /api/v1/... → "v1")
        $version = $request->segment(2) ?? 'v1';

        if (in_array($version, self::SUNSET_VERSIONS)) {
            return response()->json([
                'success' => false,
                'message' => "API version [{$version}] has been sunset. Please upgrade to a supported version.",
                'data'    => null,
                'errors'  => null,
            ], 410);
        }

        /** @var Response $response */
        $response = $next($request);

        if (in_array($version, self::DEPRECATED_VERSIONS)) {
            $response->headers->set('X-API-Deprecated', 'true');
            $response->headers->set('X-API-Version', $version);
        }

        $response->headers->set('X-API-Version', $version);

        return $response;
    }
}
