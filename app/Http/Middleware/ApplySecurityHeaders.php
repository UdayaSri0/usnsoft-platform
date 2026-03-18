<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySecurityHeaders
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! (bool) config('security.headers.enabled', true)) {
            return $response;
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', (string) config('security.headers.frame_options', 'SAMEORIGIN'));
        $response->headers->set('Referrer-Policy', (string) config('security.headers.referrer_policy', 'strict-origin-when-cross-origin'));
        $response->headers->set('Permissions-Policy', (string) config('security.headers.permissions_policy', 'camera=(), geolocation=(), microphone=(), payment=(), usb=()'));

        $cspReportOnly = config('security.headers.csp_report_only');
        if (is_string($cspReportOnly) && trim($cspReportOnly) !== '') {
            $response->headers->set('Content-Security-Policy-Report-Only', trim($cspReportOnly));
        }

        if (
            (bool) config('security.headers.hsts_enabled', false)
            && $request->isSecure()
        ) {
            $value = 'max-age='.(int) config('security.headers.hsts_max_age', 31536000);

            if ((bool) config('security.headers.hsts_include_subdomains', true)) {
                $value .= '; includeSubDomains';
            }

            if ((bool) config('security.headers.hsts_preload', false)) {
                $value .= '; preload';
            }

            $response->headers->set('Strict-Transport-Security', $value);
        }

        return $response;
    }
}
