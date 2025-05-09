#License Guard

**License Guard** is a lightweight Laravel package that protects your application from unauthorized usage by validating a license key against a remote license server.

## 🚀 Features

- Validates license key and domain against your server
- Caches result to reduce server load
- Blocks unauthorized users with a `403 Forbidden` response
- Easily reusable across multiple Laravel projects

---

## 📦 Installation

```bash
composer require qbit/license-guard
````

> ⚠️ If your package isn't published to Packagist, include it as a path or VCS repository in your `composer.json`.

---

## ⚙️ Configuration

### 1. Publish the config file

```bash
php artisan vendor:publish --tag=license-guard-config
```

This will create `config/license-guard.php`:

```php
return [
    'verify_url' => env('LICENSE_GUARD_VERIFY_URL'),
    'license_key' => env('LICENSE_GUARD_KEY'),
];
```

### 2. Set environment variables in `.env`

```env
LICENSE_GUARD_VERIFY_URL=https://your-license-server.com/api/verify-license
LICENSE_GUARD_KEY=YOUR-LICENSE-KEY
```

---

## 🛡️ Middleware Usage

Register the middleware in your `app/Http/Kernel.php`:

```php
protected $middleware = [
    \Qbit\LicenseGuard\Middleware\LicenseGuard::class,
    // ...
];
```

Or apply it to specific routes or route groups:

```php
Route::middleware(['license.guard'])->group(function () {
    // Protected routes
});
```

---

## 🔍 How It Works

* On each request (or first within 12 hours), it sends a `GET` request to your license server with:

  * `key` – license key
  * `domain` – request domain

Example request:

```
GET https://your-license-server.com/api/verify-license?key=XXXX&domain=example.com
```

* The license server should return:

```json
{ "valid": true }
```

Or an error:

```json
{ "valid": false, "reason": "expired" }
```

If the license is **not valid**, the middleware aborts with HTTP 403.

---

## 🧪 License Server API Example

Example Laravel route:

```php
Route::get('/api/verify-license', function (Request $request) {
    $license = \App\Models\License::where('license_key', $request->get('key'))->first();

    if (! $license || $license->domain !== $request->get('domain')) {
        return response()->json(['valid' => false], 403);
    }

    return response()->json(['valid' => true]);
});
```

---

## 🧠 Caching Behavior

* The package caches a successful license check for 12 hours.
* This reduces license server hits.
* You can clear cache via:

```php
Cache::forget('license_check_result');
```

---

## 🛠️ Troubleshooting

* Ensure your license server route is accessible and responds with `application/json`.
* If using HTTPS, check SSL is valid.
* Use Laravel logs (`storage/logs/laravel.log`) for debugging request and response issues.

---

## 🧾 License

MIT License © [QBit Tech](https://qbit-tech.com)

```

---

Let me know if you'd like a `LicenseController` stub or to automate license creation from the server side.
```
