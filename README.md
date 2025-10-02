# CEITS Prisma Laravel - SSO Integration

Laravel 11 application dengan integrasi CEITS SSO (Single Sign-On).

## 🚀 Features

- ✅ **SSO Authentication** - Integrasi dengan CEITS SSO Backend
- ✅ **Role-Based Access Control** - Middleware untuk cek role user
- ✅ **Session Management** - Session persisten dengan cookie fallback
- ✅ **Docker Ready** - Nginx + PHP-FPM + Supervisor
- ✅ **Auto Redirect** - Otomatis redirect ke SSO login jika belum authenticated

## 📋 Tech Stack

- **Framework**: Laravel 11
- **PHP**: 8.2
- **Web Server**: Nginx
- **Process Manager**: Supervisor
- **SSO**: CEITS SSO (JWT-based)

## 📁 Project Structure

```
prisma-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php        # Base controller
│   │   │   ├── HomeController.php    # Home page
│   │   │   ├── DashboardController.php
│   │   │   └── AuthController.php    # Logout handler
│   │   └── Middleware/
│   │       ├── SsoAuth.php           # SSO authentication middleware
│   │       └── SsoRole.php           # Role-based access middleware
│   └── Services/
│       └── SsoClient.php             # SSO API client
├── config/
│   └── sso.php                       # SSO configuration
├── resources/
│   └── views/
│       ├── home.blade.php
│       └── dashboard.blade.php
├── routes/
│   └── web.php                       # Route definitions
├── docker/
│   ├── nginx/
│   │   └── default.conf              # Nginx configuration
│   └── supervisord.conf              # Supervisor configuration
├── Dockerfile
├── docker-compose.yml
└── .env                              # Environment configuration
```

## ⚙️ Installation

### 1. Install Dependencies

```bash
composer install
```

### 2. Configure Environment

Copy `.env.example` ke `.env`:

```bash
cp .env.example .env
```

Edit `.env` dan sesuaikan SSO configuration:

```env
APP_NAME="CEITS Prisma Laravel"
APP_KEY=base64:...  # Generate with: php artisan key:generate

# Database (jika diperlukan)
DB_CONNECTION=pgsql
DB_HOST=your_db_host
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# CEITS SSO Configuration
SSO_API_URL=http://host.docker.internal:3004  # For Docker
# SSO_API_URL=http://localhost:3004           # For local development
SSO_API_KEY=your_api_key_here
SSO_COOKIE_NAME=ceits_sso_token
SSO_FRONTEND_URL=http://localhost:3000
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

## 🐳 Docker Deployment

### Build and Run

```bash
docker-compose up -d --build
```

### Check Status

```bash
docker-compose ps
```

### View Logs

```bash
docker-compose logs -f prisma-laravel
```

### Stop Container

```bash
docker-compose down
```

## 🔐 SSO Authentication Flow

CEITS SSO menggunakan **middleware-based authentication** dengan pemisahan route callback untuk menghindari redirect loop.

### Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     User Browser                            │
└────────────┬────────────────────────────────────────────────┘
             │
             ├─ Step 1: Access Protected Route
             │  GET /dashboard
             │
             ▼
┌─────────────────────────────────────────────────────────────┐
│              Laravel App (localhost:8081)                   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐  │
│  │  SsoAuth Middleware (app/Http/Middleware/SsoAuth)   │  │
│  │                                                       │  │
│  │  1. Check session('sso_user') → NOT FOUND           │  │
│  │  2. Check cookie('ceits_prisma_token') → NOT FOUND  │  │
│  │  3. Save intended URL to session                    │  │
│  │  4. Redirect to SSO Login                           │  │
│  └─────────────────────────────────────────────────────┘  │
└────────────┬────────────────────────────────────────────────┘
             │
             ├─ Step 2: Redirect to SSO Login
             │  Location: http://localhost:3000/login?returnUrl=http://localhost:8081/auth/callback
             │
             ▼
┌─────────────────────────────────────────────────────────────┐
│              SSO Frontend (localhost:3000)                  │
│                                                             │
│  - User enters email & password                            │
│  - Verify credentials with SSO Backend                     │
│  - Receive JWT token                                       │
│  - Check returnUrl parameter                               │
│                                                             │
└────────────┬────────────────────────────────────────────────┘
             │
             ├─ Step 3: Redirect to Laravel Callback
             │  Location: http://localhost:8081/auth/callback?token=JWT_TOKEN
             │
             ▼
┌─────────────────────────────────────────────────────────────┐
│              Laravel App (localhost:8081)                   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐  │
│  │  AuthController::callback (NO MIDDLEWARE!)          │  │
│  │                                                       │  │
│  │  1. Extract token from query parameter              │  │
│  │  2. Get intended URL from session                   │  │
│  │  3. Verify token with SSO Backend API:              │  │
│  │     POST http://host.docker.internal:3004/api/auth/verify │
│  │     Headers: X-API-Key                              │  │
│  │     Body: { token: "..." }                          │  │
│  │  4. If valid:                                        │  │
│  │     - Save user data to session('sso_user')         │  │
│  │     - Save token to cookie('ceits_prisma_token')    │  │
│  │     - Clear intended_url from session               │  │
│  │     - session()->save() // Force save!              │  │
│  │  5. Redirect to intended URL                        │  │
│  └─────────────────────────────────────────────────────┘  │
└────────────┬────────────────────────────────────────────────┘
             │
             ├─ Step 4: Redirect to Protected Route
             │  Location: http://localhost:8081/dashboard
             │
             ▼
┌─────────────────────────────────────────────────────────────┐
│              Laravel App (localhost:8081)                   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐  │
│  │  SsoAuth Middleware                                  │  │
│  │                                                       │  │
│  │  1. Check session('sso_user') → FOUND! ✅           │  │
│  │  2. Share user data to views                        │  │
│  │  3. Continue to controller                          │  │
│  └─────────────────────────────────────────────────────┘  │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐  │
│  │  DashboardController::index()                        │  │
│  │  - Render dashboard.blade.php                       │  │
│  │  - Display user data                                │  │
│  └─────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Detailed Step-by-Step Flow

#### Step 1: Initial Request (User Belum Login)

```
Browser → GET http://localhost:8081/dashboard
           ↓
      [Route: /dashboard]
           ↓
    [Middleware: SsoAuth]
           ↓
    Check session('sso_user')? → NO
           ↓
    Check cookie('ceits_prisma_token')? → NO
           ↓
    Save session(['intended_url' => 'http://localhost:8081/dashboard'])
           ↓
    Redirect → http://localhost:3000/login?returnUrl=http://localhost:8081/auth/callback
```

#### Step 2: SSO Login Process

```
Browser → GET http://localhost:3000/login?returnUrl=...
           ↓
    [SSO Frontend Vue.js App]
           ↓
    Display Login Form
           ↓
    User submits email & password
           ↓
    POST http://localhost:3004/api/auth/login
           ↓
    [SSO Backend validates credentials]
           ↓
    Response: { success: true, token: "JWT_TOKEN", user: {...} }
           ↓
    Frontend saves token to localStorage
           ↓
    Frontend checks returnUrl parameter
           ↓
    Redirect → http://localhost:8081/auth/callback?token=JWT_TOKEN
```

#### Step 3: Laravel Callback Handler (NO MIDDLEWARE!)

```
Browser → GET http://localhost:8081/auth/callback?token=JWT_TOKEN
           ↓
      [Route: /auth/callback]  ← NO MIDDLEWARE APPLIED!
           ↓
    [AuthController::callback()]
           ↓
    Extract token from $_GET['token']
           ↓
    Get intended_url from session → 'http://localhost:8081/dashboard'
           ↓
    Verify token with SSO Backend:
    POST http://host.docker.internal:3004/api/auth/verify
    Headers: { X-API-Key: "..." }
    Body: { token: "JWT_TOKEN" }
           ↓
    Response: { success: true, valid: true, user: {...} }
           ↓
    Save to session:
    session(['sso_user' => [
        'id' => '...',
        'nrp' => '...',
        'nama' => '...',
        'email' => '...',
        'role' => '...',
        'departemen' => '...'
    ]])
           ↓
    session()->forget('intended_url')
           ↓
    session()->save()  ← FORCE SAVE BEFORE REDIRECT!
           ↓
    Set cookie: ceits_prisma_token (7 days)
           ↓
    Redirect → http://localhost:8081/dashboard
```

#### Step 4: Access Protected Route (Sudah Login)

```
Browser → GET http://localhost:8081/dashboard
           ↓
      [Route: /dashboard]
           ↓
    [Middleware: SsoAuth]
           ↓
    Check session('sso_user')? → YES! ✅
           ↓
    Share user data to views: view()->share('user', $userData)
           ↓
    Continue → DashboardController::index()
           ↓
    Render dashboard.blade.php with user data
           ↓
    Response → HTML page displayed
```

#### Step 5: Subsequent Requests (Session Active)

```
Browser → GET http://localhost:8081/dashboard
           ↓
    [Middleware: SsoAuth]
           ↓
    Check session('sso_user')? → YES! ✅
           ↓
    Immediately continue (no verification needed)
           ↓
    Display dashboard
```

#### Step 6: Session Expired, Cookie Still Valid

```
Browser → GET http://localhost:8081/dashboard
    (Cookie: ceits_prisma_token=JWT_TOKEN)
           ↓
    [Middleware: SsoAuth]
           ↓
    Check session('sso_user')? → NO
           ↓
    Check cookie('ceits_prisma_token')? → YES
           ↓
    Verify token with SSO Backend
           ↓
    If valid → Restore session(['sso_user' => ...])
           ↓
    Continue to controller
```

#### Step 7: Token Expired/Invalid

```
Browser → GET http://localhost:8081/dashboard
           ↓
    [Middleware: SsoAuth]
           ↓
    Check session('sso_user')? → NO
           ↓
    Check cookie('ceits_prisma_token')? → YES
           ↓
    Verify token with SSO Backend → INVALID ❌
           ↓
    Clear cookie: ceits_prisma_token
           ↓
    Save intended URL to session
           ↓
    Redirect to SSO login (back to Step 1)
```

### Key Components

#### 1. SsoAuth Middleware (`app/Http/Middleware/SsoAuth.php`)

**Responsibility**: Protect routes, check authentication status

```php
public function handle(Request $request, Closure $next): Response
{
    // Fast path: Check if already authenticated
    if (session()->has('sso_user')) {
        view()->share('user', session('sso_user'));
        return $next($request);
    }

    // Try to restore from cookie
    $token = $request->cookie('ceits_prisma_token');
    if ($token) {
        $userData = $this->ssoClient->verifyToken($token);
        if ($userData) {
            session(['sso_user' => $userData]);
            return $next($request);
        }
    }

    // Not authenticated, redirect to SSO
    session(['intended_url' => $request->fullUrl()]);
    session()->save();

    return redirect($this->ssoClient->getLoginUrl(url('/auth/callback')));
}
```

**Applied to routes via**:
```php
Route::middleware(['sso.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

#### 2. AuthController::callback (`app/Http/Controllers/AuthController.php`)

**Responsibility**: Handle SSO callback, verify token, save session

**IMPORTANT**: This route is **NOT protected** by middleware!

```php
Route::get('/auth/callback', [AuthController::class, 'callback'])
    ->name('auth.callback');
// ← NO ->middleware(['sso.auth'])
```

**Why?** Because if we apply middleware here, it will check authentication BEFORE token is saved, causing infinite redirect loop!

```php
public function callback(Request $request)
{
    $token = $request->query('token');
    $intendedUrl = session('intended_url', '/dashboard');

    if (!$token) {
        return redirect($this->ssoClient->getLoginUrl(url('/auth/callback')));
    }

    $userData = $this->ssoClient->verifyToken($token);

    if (!$userData) {
        return redirect($this->ssoClient->getLoginUrl(url('/auth/callback')));
    }

    // Save to session
    session(['sso_user' => $userData]);
    session()->forget('intended_url');
    session()->save(); // Force save before redirect!

    // Redirect with cookie
    return redirect($intendedUrl)->withCookie(
        cookie('ceits_prisma_token', $token, 60 * 24 * 7)
    );
}
```

#### 3. SsoClient Service (`app/Services/SsoClient.php`)

**Responsibility**: Communicate with SSO Backend API

```php
public function verifyToken(string $token): ?array
{
    $response = Http::withHeaders([
        'X-API-Key' => $this->apiKey,
    ])->timeout(10)->post("{$this->apiUrl}/api/auth/verify", [
        'token' => $token,
    ]);

    if ($response->successful() && $response->json('success')) {
        return $response->json('user');
    }

    return null;
}
```

### Why This Architecture Prevents Redirect Loop?

**Old Approach (WRONG - causes loop)**:
```
/dashboard (with middleware) → receives ?token=xxx
  ↓ middleware checks session → NOT FOUND
  ↓ tries to save session
  ↓ redirects to clean URL
  ↓ NEW REQUEST to /dashboard
  ↓ middleware checks session → STILL NOT FOUND (session not saved yet!)
  ↓ redirects to SSO
  ↓ LOOP! ❌
```

**New Approach (CORRECT - no loop)**:
```
/dashboard (with middleware) → no token
  ↓ middleware → redirect to SSO
  ↓
SSO → redirect to /auth/callback?token=xxx
  ↓
/auth/callback (NO middleware!) → receives token
  ↓ verify token
  ↓ save to session
  ↓ session()->save() ← FORCE SAVE!
  ↓ redirect to /dashboard
  ↓
/dashboard (with middleware)
  ↓ middleware checks session → FOUND! ✅
  ↓ continue to controller
  ↓ SUCCESS! ✅
```

### Session & Cookie Strategy

**Two-tier storage**:

1. **Session** (`sso_user`):
   - Primary storage
   - Fast access
   - Server-side
   - Expires after inactivity (default: 2 hours)

2. **Cookie** (`ceits_prisma_token`):
   - Fallback storage
   - Longer lifetime (7 days)
   - Client-side
   - Used to restore session if expired

**Why not use SSO cookie directly?**

SSO Frontend sets cookie `ceits_sso_token` on domain `localhost:3000`. Laravel runs on `localhost:8081`. Browsers **do not share cookies across different ports** (different origins). Solution: SSO sends token via URL parameter, Laravel saves it in its own cookie.

### Important Notes

1. **session()->save()** is CRITICAL before redirect in callback handler
2. **No middleware** on `/auth/callback` route
3. Use `host.docker.internal` in Docker, `localhost` for local development
4. Cookie domain issue only affects development (localhost:3000 vs localhost:8081)
5. In production with shared domain (e.g., `sso.ceits.id`, `app.ceits.id`), cookies can be shared

### SsoRole Middleware

Middleware untuk role-based access control:

```php
Route::middleware(['sso.auth', 'sso.role:DOSEN,TENDIK'])->group(function () {
    Route::get('/penilaian', [PenilaianController::class, 'index']);
});
```

### SsoClient Service

Service untuk komunikasi dengan SSO backend:

```php
// Verify token
$userData = $ssoClient->verifyToken($token);
// Returns: ['id', 'nrp', 'nama', 'email', 'role', 'departemen']

// Get SSO login URL
$loginUrl = $ssoClient->getLoginUrl('http://localhost:8081/dashboard');

// Get SSO logout URL
$logoutUrl = $ssoClient->getLogoutUrl();
```

## 🔒 Protected Routes

### Example Route Configuration

```php
// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Protected routes - require authentication
Route::middleware(['sso.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Role-based routes
Route::middleware(['sso.auth', 'sso.role:DOSEN,TENDIK'])->group(function () {
    Route::get('/penilaian', [PenilaianController::class, 'index']);
});

Route::middleware(['sso.auth', 'sso.role:SUPERADMIN'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});
```

## 🎯 Usage in Controllers

### Get User Data

```php
class DashboardController extends Controller
{
    public function index()
    {
        // User data automatically shared to all views via middleware
        // Access via: $user variable in blade templates

        // Or get from session:
        $user = session('sso_user');
        // ['id', 'nrp', 'nama', 'email', 'role', 'departemen']

        return view('dashboard');
    }
}
```

### Get User in Blade Views

```blade
<h1>Welcome, {{ $user['nama'] }}!</h1>
<p>Email: {{ $user['email'] }}</p>
<p>Role: {{ $user['role'] }}</p>
<p>Departemen: {{ $user['departemen'] }}</p>
```

## 🚪 Logout

```php
// Logout akan:
// 1. Clear session sso_user
// 2. Clear cookie ceits_prisma_token
// 3. Redirect ke SSO logout (clear ceits_sso_token cookie)

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
```

## 🔧 Configuration

### config/sso.php

```php
return [
    'api_url' => env('SSO_API_URL'),           // SSO backend URL
    'api_key' => env('SSO_API_KEY', ''),       // API key for verification
    'cookie_name' => env('SSO_COOKIE_NAME'),   // Cookie name from SSO frontend
    'frontend_url' => env('SSO_FRONTEND_URL'), // SSO frontend URL
];
```

## 📝 Important Notes

### Cookie Domain Issue (Development)

Di **localhost development**, cookie tidak bisa di-share antar port/subdomain:
- `localhost:3000` (SSO frontend) → cookie `ceits_sso_token`
- `localhost:8081` (Laravel app) → **tidak bisa baca** cookie di atas

**Solusi**:
- SSO redirect dengan `?token=xxx` di URL (sekali saja)
- Laravel simpan di cookie sendiri: `ceits_prisma_token`

### Production (Shared Domain)

Di **production** dengan domain yang sama:
- `sso.ceits.id` → set cookie domain `.ceits.id`
- `app.ceits.id` → bisa baca cookie `.ceits.id`
- Semua aplikasi bisa share cookie yang sama

### Docker vs Local Development

**Docker** (di dalam container):
```env
SSO_API_URL=http://host.docker.internal:3004
```

**Local** (tanpa Docker):
```env
SSO_API_URL=http://localhost:3004
```

### Session Storage

Laravel menggunakan **file-based session** by default:
- Session disimpan di `storage/framework/sessions/`
- Pastikan permission 775 untuk directory ini
- Session lifetime: 120 menit (2 jam) by default

### Rate Limiting

SSO backend memiliki rate limit:
- **Login**: 10 requests/min per IP
- **General API**: 100 requests/min per IP
- **API Key** (verify): 1000 requests/min per API key

## 🐛 Troubleshooting

### 1. Redirect Loop

**Symptom**: Terus redirect antara Laravel dan SSO login

**Cause**:
- Session tidak persist
- SSO backend tidak bisa diakses
- Token verification gagal

**Solution**:
```bash
# Check session permissions
chmod -R 775 storage/framework/sessions

# Check SSO backend connection (from container)
docker exec prisma-laravel wget -O- http://host.docker.internal:3004/health

# Check Laravel logs
docker exec prisma-laravel tail -f /var/www/html/storage/logs/laravel.log
```

### 2. Token Verification Failed

**Symptom**: Selalu redirect ke SSO login meskipun sudah login

**Cause**:
- API Key salah
- SSO backend URL salah
- Rate limit hit

**Solution**:
```bash
# Check .env
SSO_API_URL=http://host.docker.internal:3004  # For Docker
SSO_API_KEY=your_valid_api_key

# Test API key manually
curl -X POST http://localhost:3004/api/auth/verify \
  -H "X-API-Key: your_api_key" \
  -H "Content-Type: application/json" \
  -d '{"token":"your_jwt_token"}'
```

### 3. Session Not Persisting

**Symptom**: Harus login setiap kali refresh page

**Cause**:
- Session driver issue
- Cookie not set
- Permission issue

**Solution**:
```bash
# Check .env
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Clear old sessions
rm -rf storage/framework/sessions/*

# Fix permissions
chmod -R 775 storage
```

## 📚 API Reference

### SSO Backend Endpoints

#### POST /api/auth/verify

Verify JWT token dan get user data.

**Headers**:
```
X-API-Key: your_api_key_here
Content-Type: application/json
```

**Request Body**:
```json
{
  "token": "JWT_TOKEN_HERE"
}
```

**Response** (Success):
```json
{
  "success": true,
  "valid": true,
  "user": {
    "id": "uuid",
    "nrp": "5025211001",
    "nama": "John Doe",
    "email": "john@its.ac.id",
    "role": "MAHASISWA",
    "departemen": "Teknik Komputer"
  }
}
```

**Response** (Invalid Token):
```json
{
  "success": false,
  "message": "Invalid or expired token"
}
```

## 📞 Support

Untuk bug report atau feature request, silakan buat issue di repository ini.

---

**Generated with CEITS SSO System**
