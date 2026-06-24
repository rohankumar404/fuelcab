<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorScope
{
    /**
     * Inject the authenticated user's vendor_id into the request
     * so downstream code can reference it without re-querying.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->vendor_id) {
            $request->merge(['_vendor_id' => $request->user()->vendor_id]);
        }

        return $next($request);
    }
}
