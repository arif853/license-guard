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
        // Cache::remember will get 'license_check_result'. If it's missing or expired,
        // it will execute the closure, cache its return value for 12 hours, and then return it.
        $result = Cache::remember('license_check_result', now()->addHours(12), function () use ($request) {
            // Log::info('No valid license cache found. Making HTTP request...');

            $response = Http::timeout(3)->get(config('license-guard.verify_url'), [
                'key' => config('license-guard.license_key'),
                'domain' => $request->getHost(),
            ]);

            $licenseData = $response->json();

            // --- DEFENSIVE CHECK 1: Ensure the response is valid JSON ---
            // If $licenseData is not an array, the API response was not valid JSON.
            if (!is_array($licenseData)) {
                // Log::error('License server response was not valid JSON.', [
                //     'status' => $response->status(),
                //     'body' => $response->body(), // Log the raw body for debugging
                // ]);
                // Return a failure state so we don't try to access it as an array later.
                return ['valid' => false, 'reason' => 'invalid_response'];
            }

            // Log::info('License server response:', [
            //     'status' => $response->status(),
            //     'body' => $licenseData,
            // ]);

            // If the request failed or the license is not valid in the response body
            if (!$response->ok() || !($licenseData['valid'] ?? false)) {
                return [
                    'valid' => false,
                    'reason' => $licenseData['reason'] ?? 'invalid',
                ];
            }

            // If the license is valid, get the data
            $expiredAt = $licenseData['expire_date'] ?? null;
            $status = $licenseData['status'] ?? null;

            // Log::info('License is valid. Caching result.', [
            //     'expired_at' => $expiredAt,
            //     'status' => $status
            // ]);

            Cache::put('license_details', [
                'expired_at' => $expiredAt,
                'status' => $status,
            ], now()->addHours(12));

            return ['valid' => true];
        });

        // --- DEFENSIVE CHECK 2: Ensure the cached result is a valid array ---
        // This protects against a corrupted cache (e.g., if it holds a string).
        if (!is_array($result) || !isset($result['valid'])) {
            Log::error('Corrupt license data found in cache. Forcing re-check.', [
                'cached_value' => $result,
            ]);
            Cache::forget('license_check_result'); // Clear the bad cache entry
            abort(403, 'A server error occurred during license verification. Please try again.');
        }

        if ($result['valid']) {
            Log::info('License check passed (from cache or live request).');
            return $next($request);
        }

        // --- Handle Failure ---
        $reason = $result['reason'];
        $messages = [
            'expired' => 'Your license has expired. Please renew to continue using the software.',
            'not_found' => 'License not found. Please check your license key.',
            'domain_mismatch' => 'Invalid license key or domain mismatch.',
            'inactive' => 'Your license has been suspended. Contact support.',
            'invalid_response' => 'The license server returned an invalid response. Please contact support.', // New message
            'invalid' => 'Unauthorized software usage detected. Please contact support.',
        ];

        $message = 'Unauthorized software usage detected. ' . ($messages[$reason] ?? $messages['invalid']);

        Log::warning("License check failed ({$reason}). Blocking access.");

        abort(403, $message);
    }

}

