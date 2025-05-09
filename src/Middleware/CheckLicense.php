<?php
namespace LicenseGuard\Middleware;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckLicense
{
    public function handle($request, Closure $next)
    {
        Log::info('License check middleware triggered.');

        $cached = Cache::get('license_check_result');
        Log::info('Cache result:', ['value' => $cached]);

        if ($cached !== 'valid') {
            Log::info('No valid cache found. Making HTTP request...');

            Log::info('📡 Calling license server', [
                'url' => config('license-guard.verify_url'),
                'params' => [
                    'key' => config('license-guard.license_key'),
                    'domain' => $request->getHost(),
                ],
            ]);

            $response = Http::timeout(3)->get(config('license-guard.verify_url'), [
                'key' => config('license-guard.license_key'),
                'domain' => $request->getHost(),
            ]);
            // dd($response);
            Log::info('License server response:', [
                'ok' => $response->ok(),
                'body' => $response->json(),
            ]);

            if (! $response->ok() || ! $response->json('valid')) {
                Log::warning('License check failed. Blocking access.');
                abort(403, 'Unauthorized software usage detected.');
            }

            Log::info('License is valid. Caching result for 12 hours.');
            Cache::put('license_check_result', 'valid', now()->addHours(12));
        } else {
            Log::info('License cache hit. No request needed.');
        }

        return $next($request);
    }

}

