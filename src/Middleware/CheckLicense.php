<?php
namespace Arif853\LicenseGuard\Middleware;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckLicense
{
    public function handle($request, Closure $next)
    {
        // Use ONE cache key to store all license details.
        $licenseDetails = Cache::remember('license_details', now()->addHours(12), function () use ($request) {
            
            $response = Http::timeout(3)->get(config('license-guard.verify_url'), [
                'key' => config('license-guard.license_key'),
                'domain' => $request->getHost(),
            ]);

            $responseData = $response->json();

            // If response is not valid JSON, return a failure state.
            if (!is_array($responseData)) {
                return ['valid' => false, 'reason' => 'invalid_response'];
            }

            // If request failed or license is marked invalid, return failure state.
            if (!$response->ok() || !($responseData['valid'] ?? false)) {
                return [
                    'valid' => false, 
                    'reason' => $responseData['reason'] ?? 'invalid'
                ];
            }

            // --- Success ---
            // On success, return the WHOLE valid license payload.
            // This array will be cached by Cache::remember.
            return [
                'valid'      => true,
                'reason'     => 'valid',
                'expired_at' => $responseData['expire_date'] ?? null, // Corrected key from your code
                'status'     => $responseData['status'] ?? null,
            ];
        });

        // Now, just check the result from the single cache entry.
        if ($licenseDetails['valid']) {
            return $next($request);
        }

        // --- Handle Failure ---
        $reason = $licenseDetails['reason'];
        $messages = [
            'expired'         => 'Your license has expired. Please renew to continue.',
            'not_found'       => 'License not found. Please check your license key.',
            'domain_mismatch' => 'Invalid license key or domain mismatch.',
            'inactive'        => 'Your license has been suspended. Contact support.',
            'invalid_response'=> 'The license server returned an invalid response.',
            'invalid'         => 'Unauthorized software usage detected.',
        ];

        $message = 'Unauthorized software usage detected. ' . ($messages[$reason] ?? $messages['invalid']);
        
        Cache::flush();
        Log::warning("License check failed ({$reason}). Blocking access.");
        abort(403, $message);
    }

}

