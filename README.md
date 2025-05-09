Thanks! Since your package is now available at [https://github.com/arif853/license-guard](https://github.com/arif853/license-guard), here is a complete **README.md** you can add to your repository to help others (or yourself) install and use it in any Laravel project:

---

## 🚫 License Guard for Laravel

**License Guard** is a simple Laravel package to restrict unauthorized usage of your Laravel application by validating license keys via a remote license server.

---

### 📦 Installation

#### Step 1: Add GitHub Repo to Composer

In your Laravel project’s `composer.json`, add the following under `"repositories"`:

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/arif853/license-guard"
  }
]
```

#### Step 2: Require the Package

```bash
composer require license-guard:dev-main
```

> If you get a stability error, add these to your Laravel project’s `composer.json`:

```json
"minimum-stability": "dev",
"prefer-stable": true
```

Then run:

```bash
composer update
```

---

### ⚙️ Configuration

#### Step 3: Publish Config File (optional)

```bash
php artisan vendor:publish --tag=license-guard-config
```

#### Step 4: Add Environment Variables

Add the following to your `.env` file:

```env
LICENSE_GUARD_VERIFY_URL=https://your-license-server.com/api/verify-license
LICENSE_GUARD_KEY=YOUR-LICENSE-KEY-HERE
```

---

### 🔒 Usage

#### Option A: Apply Middleware Globally

In `app/Http/Kernel.php`, under `$middleware`, add:

```php
\Qbit\LicenseGuard\Middleware\LicenseGuard::class,
```

#### Option B: Apply Middleware to Routes

In `Kernel.php`, add to `$routeMiddleware`:

```php
'license.guard' => \Qbit\LicenseGuard\Middleware\LicenseGuard::class,
```

Then use it in your routes:

```php
Route::middleware(['license.guard'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'));
});
```

---

### ✅ License Server Response Format

Your license server (e.g., Laravel API) must return:

```json
{
  "valid": true
}
```

On failure, return:

```json
{
  "valid": false,
  "reason": "expired|not_found|domain_mismatch|inactive"
}
```

With proper HTTP status (`403`, `404`, etc.).

---

### 🧠 Features

* License key validation via HTTP
* Domain matching support
* Cache for improved performance
* Middleware-based enforcement

---

### 👨‍💻 Author

**[Arif Hossen](https://github.com/arif853)** — QBit Tech

---

Let me know if you want a logo or badge added at the top of the README too!
