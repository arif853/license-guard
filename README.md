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
composer require arif853/license-guard:v0.1.2
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
php artisan vendor:publish --tag=config
```

#### Step 4: Add Environment Variables

Add the following to your `.env` file:

```env
LICENSE_KEY=YOUR-LICENSE-KEY-HERE
LICENSE_VERIFY_URL=https://license-server.arifhossen.info/api/verify-license
```

---

### 🔒 Usage

#### Option A: Apply Middleware Globally

In `app/Http/Kernel.php`, under `$middlewareGroups`, add:

```php
protected $middlewareGroups = [
    'web' => [
        // other middleware...
        'license.guard',
    ],
];
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

---

## 🛡️ License

This package is proprietary. Unauthorized distribution or use is strictly prohibited.

---

## 🧑‍💻 Maintainer & Author

Developed and maintained by [Arif Hossen](https://github.com/arif853).
