# Production Deployment Guide: Multi-Tenant Facility System

## Overview

This guide explains how to deploy the Bio-Pacific facility management system to production, supporting multiple facility domains, secure admin separation, and HIPAA-compliant features (including Book a Tour and CMS). The system maintains a single codebase serving all facilities, with admin functionality isolated and secure.

## Table of Contents

1. [Current Architecture](#current-architecture)
2. [Implementation Strategy](#implementation-strategy)
3. [Domain Configuration](#domain-configuration)
4. [Route Structure Changes](#route-structure-changes)
5. [Create Public Controller](#create-public-controller)
6. [Update Tenant Resolution](#update-tenant-resolution)
7. [Create Public Views](#create-public-views)
8. [Environment Configuration](#environment-configuration)
9. [DNS Configuration](#dns-configuration)
10. [Web Server Configuration](#web-server-configuration)
11. [Update Navigation](#update-navigation)
12. [Deployment Steps](#deployment-steps)
13. [Admin Access](#admin-access)
14. [Security Considerations](#security-considerations)

## Current Architecture

Your system includes:

- **Tenant Resolution**: `ResolveTenant` middleware resolves facilities by domain/subdomain
- **Facility Model**: Domain/subdomain fields, `findByDomain()` method, and multi-tenant isolation
- **Public Routes**: Facility-specific routes, no authentication required
- **Admin Routes**: Isolated under admin domain, protected by authentication and permissions
- **Book a Tour System**: Secure, encrypted, token-based access for ePHI
- **CMS Integration**: Admin dashboard for blog/article management

## Implementation Strategy

### System Overview

- Single Laravel application serves multiple facility domains and subdomains
- Public facility routes require no authentication
- Admin routes are separated under a dedicated admin domain and protected by authentication/permissions
- Each facility maintains its branding, content, and layout configuration
- Book a Tour and contact forms use secure, encrypted, token-based access for ePHI
- CMS is available for authorized admin users only

### Domain Structure

```
Public Facility Domains:
├── valehealthcare.com → Vale Health Care Center
├── pacificmanorcare.com → Pacific Manor Care
├── sunrisegardens.com → Sunrise Gardens Healthcare
├── mountainviewal.com → Mountain View Assisted Living
├── oakwoodsenior.com → Oakwood Senior Community
└── [other configured facilities]

Admin Domain:
└── admin.biopacific.com → Administrative Interface (CMS, Book a Tour management, audit logs)
```

## Domain Configuration

Each facility has its own domain/subdomain pointing to the same Laravel application. The admin domain is used for all administrative and CMS functions.

| Facility                      | Domain                 | Purpose               |
| ----------------------------- | ---------------------- | --------------------- |
| Vale Health Care Center       | `valehealthcare.com`   | Public website        |
| Pacific Manor Care            | `pacificmanorcare.com` | Public website        |
| Sunrise Gardens Healthcare    | `sunrisegardens.com`   | Public website        |
| Mountain View Assisted Living | `mountainviewal.com`   | Public website        |
| Oakwood Senior Community      | `oakwoodsenior.com`    | Public website        |
| [Other facilities]            | [domain]               | Public website        |
| Admin Dashboard               | `admin.biopacific.com` | Admin/CMS interface   |
| Creekside Health Care Center  | `creeksidehcc.com`     | Public website        |
| Admin Interface               | `admin.biopacific.com` | Administrative access |

## Route Structure Changes

### 1. Update `routes/web.php`

Create separate route groups for public and admin access:

```php
<?php

use App\Http\Middleware\ResolveTenant;
use App\Http\Controllers\FacilityPublicController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;

// Public facility routes (NO authentication required)
Route::middleware([ResolveTenant::class])->group(function () {
    // Home page for each facility
    Route::get('/', [FacilityPublicController::class, 'index'])->name('facility.home');

    // Public pages (no admin in URL)
    Route::get('/services', [FacilityPublicController::class, 'services'])->name('facility.services');
    Route::get('/rooms', [FacilityPublicController::class, 'rooms'])->name('facility.rooms');
    Route::get('/about', [FacilityPublicController::class, 'about'])->name('facility.about');
    Route::get('/contact', [FacilityPublicController::class, 'contact'])->name('facility.contact');
    Route::get('/tour', [FacilityPublicController::class, 'tour'])->name('facility.tour');
    Route::get('/careers', [FacilityPublicController::class, 'careers'])->name('facility.careers');
    Route::get('/privacy-policy', [FacilityPublicController::class, 'privacy'])->name('facility.privacy');

    // Public forms
    Route::post('/contact', [FacilityPublicController::class, 'submitContact'])->name('facility.contact.submit');
    Route::post('/tour-request', [FacilityPublicController::class, 'requestTour'])->name('facility.tour.request');
});

// Admin routes (authentication required) - accessed via main domain
Route::domain(config('app.admin_domain', 'admin.biopacific.com'))
    ->middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('facilities', AdminFacilityController::class);
        Route::get('/facilities/{facility}/content', [AdminFacilityController::class, 'editContent'])->name('facilities.content');
        Route::put('/facilities/{facility}/content', [AdminFacilityController::class, 'updateContent'])->name('facilities.content.update');
        // Add other admin routes as needed
    });
```

### 2. Route Testing

Test your routes after implementation:

```bash
php artisan route:list | findstr facility
php artisan route:list | findstr admin
```

## Create Public Controller

Create `app/Http/Controllers/FacilityPublicController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Faq;
use App\Mail\ContactFormSubmission;
use App\Mail\TourRequestSubmission;

class FacilityPublicController extends Controller
{
    public function index()
    {
        $facility = app('current_facility');

        // Get facility's active web content
        $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
        $sections = $this->getSections($activeWebContent);
        $layoutTemplate = $activeWebContent->layout_template ?? 'default-template';

        // Get additional content
        $testimonials = $facility->testimonials()->where('is_active', true)->get();
        $services = $facility->services()->where('is_active', true)->limit(6)->get();
        $galleryImages = $facility->galleryImages()->where('is_active', true)->limit(8)->get();
        $faqs = Faq::limit(5)->get();

        return view('facility.home', compact(
            'facility',
            'sections',
            'layoutTemplate',
            'testimonials',
            'services',
            'galleryImages',
            'faqs'
        ));
    }

    public function services()
    {
        $facility = app('current_facility');
        $services = $facility->services()->where('is_active', true)->get();

        return view('facility.services', compact('facility', 'services'));
    }

    public function rooms()
    {
        $facility = app('current_facility');
        $rooms = $facility->rooms ?? collect(); // Implement room model if needed

        return view('facility.rooms', compact('facility', 'rooms'));
    }

    public function about()
    {
        $facility = app('current_facility');
        $testimonials = $facility->testimonials()->where('is_active', true)->get();

        return view('facility.about', compact('facility', 'testimonials'));
    }

    public function contact(Request $request)
    {
        $facility = app('current_facility');

        if ($request->isMethod('post')) {
            return $this->submitContact($request);
        }

        return view('facility.contact', compact('facility'));
    }

    public function submitContact(Request $request)
    {
        $facility = app('current_facility');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Send email to facility
            if ($facility->email) {
                Mail::to($facility->email)->send(new ContactFormSubmission($validated, $facility));
            }

            // Optional: Save to database for record keeping
            // ContactSubmission::create([...]);

            return back()->with('success', 'Thank you for your message. We\'ll be in touch soon!');
        } catch (\Exception $e) {
            return back()->with('error', 'Sorry, there was an error sending your message. Please try again or call us directly.');
        }
    }

    public function tour()
    {
        $facility = app('current_facility');
        return view('facility.tour', compact('facility'));
    }

    public function requestTour(Request $request)
    {
        $facility = app('current_facility');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'preferred_date' => 'required|date|after:today',
            'preferred_time' => 'required|string|max:20',
            'message' => 'nullable|string|max:500',
        ]);

        try {
            // Send email to facility
            if ($facility->email) {
                Mail::to($facility->email)->send(new TourRequestSubmission($validated, $facility));
            }

            return back()->with('success', 'Thank you for your tour request. We\'ll contact you soon to confirm your appointment!');
        } catch (\Exception $e) {
            return back()->with('error', 'Sorry, there was an error processing your tour request. Please try again or call us directly.');
        }
    }

    public function careers()
    {
        $facility = app('current_facility');
        $openings = []; // Implement job openings if needed

        return view('facility.careers', compact('facility', 'openings'));
    }

    public function privacy()
    {
        $facility = app('current_facility');
        return view('facility.privacy', compact('facility'));
    }

    private function getSections($activeWebContent)
    {
        $sections = ['topbar']; // Always include navigation

        if ($activeWebContent && $activeWebContent->sections) {
            if (is_string($activeWebContent->sections)) {
                $additionalSections = json_decode($activeWebContent->sections, true) ?? [];
            } else {
                $additionalSections = $activeWebContent->sections;
            }

            if (!empty($additionalSections)) {
                $sections = array_merge($sections, $additionalSections);
            }
        }

        return $sections;
    }
}
```

## Update Tenant Resolution

Update `app/Http/Middleware/ResolveTenant.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\Facility;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $domain = $request->getHost();
        $domain = preg_replace('/^www\./', '', $domain); // Remove www prefix

        $facility = null;

        // Production environment - resolve by exact domain match
        if (app()->environment('production')) {
            $facility = Facility::findByDomain($domain);

            if (!$facility) {
                // Log the failed domain resolution
                \Log::warning("No facility found for domain: {$domain}");
                abort(404, 'Facility not found');
            }
        }
        // Development environment - existing logic
        else {
            if (strpos($domain, '.localhost') !== false || strpos($domain, '.test') !== false) {
                // Extract subdomain for local development
                $subdomain = explode('.', $domain)[0];
                $facility = Facility::where('subdomain', $subdomain)
                    ->where('is_active', true)
                    ->first();
            } else {
                // Fallback for local development without subdomain
                $facility = Facility::where('is_active', true)->first();
            }

            if (!$facility) {
                abort(404, 'No active facilities found. Please seed facilities first.');
            }
        }

        // Set the current facility in the service container
        app()->instance('current_facility', $facility);

        // Share facility data with all views
        View::share('facility', $facility->toArray());

        // Share theme colors for consistent styling
        View::share('colors', [
            'primary' => $facility->primary_color ?? '#047857',
            'secondary' => $facility->secondary_color ?? '#1f2937',
            'accent' => $facility->accent_color ?? '#06b6d4'
        ]);

        return $next($request);
    }
}
```

## Create Public Views

### 1. Main Layout Template

Create `resources/views/layouts/facility-public.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $facility['description'] ?? $facility['headline'] ?? 'Quality healthcare facility' }}">
    <title>{{ $facility['name'] ?? 'Bio-Pacific' }} - {{ $title ?? 'Healthcare Excellence' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom CSS Variables for Theming -->
    <style>
        :root {
            --primary-color: {{ $colors['primary'] ?? '#047857' }};
            --secondary-color: {{ $colors['secondary'] ?? '#1f2937' }};
            --accent-color: {{ $colors['accent'] ?? '#06b6d4' }};
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
```

### 2. Home Page Template

Create `resources/views/facility/home.blade.php`:

```blade
@extends('layouts.facility-public')

@section('content')
    @foreach($sections as $section)
        @if($section === 'topbar')
            @include('partials.topbar.default')
        @elseif($section === 'hero')
            @include('partials.hero.default')
        @elseif($section === 'about')
            @include('partials.about.default')
        @elseif($section === 'services')
            @include('partials.services.default')
        @elseif($section === 'rooms')
            @include('partials.rooms.default')
        @elseif($section === 'testimonials')
            @include('partials.testimonials.default')
        @elseif($section === 'gallery')
            @include('partials.gallery.default')
        @elseif($section === 'contact')
            @include('partials.contact.default')
        @elseif($section === 'footer')
            @include('partials.footer.default')
        @endif
    @endforeach
@endsection
```

### 3. Additional Page Templates

Create templates for other pages:

- `resources/views/facility/services.blade.php`
- `resources/views/facility/rooms.blade.php`
- `resources/views/facility/about.blade.php`
- `resources/views/facility/contact.blade.php`
- `resources/views/facility/tour.blade.php`
- `resources/views/facility/careers.blade.php`
- `resources/views/facility/privacy.blade.php`

## Environment Configuration

### 1. Update `.env` for Production

```bash
# Application Environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admin.biopacific.com

# Admin Domain Configuration
ADMIN_DOMAIN=admin.biopacific.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=your-production-host
DB_PORT=3306
DB_DATABASE=your-production-database
DB_USERNAME=your-db-username
DB_PASSWORD=your-secure-password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-mail-username
MAIL_PASSWORD=your-mail-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@biopacific.com
MAIL_FROM_NAME="Bio-Pacific System"

# Cache and Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Update `config/app.php`

Add admin domain configuration:

```php
<?php

return [
    // ... existing configuration ...

    /*
    |--------------------------------------------------------------------------
    | Admin Domain
    |--------------------------------------------------------------------------
    |
    | This value is the domain used for administrative access.
    | In production, this should be a subdomain of your main domain.
    |
    */

    'admin_domain' => env('ADMIN_DOMAIN', 'admin.biopacific.com'),

    // ... rest of configuration ...
];
```

## DNS Configuration

Set up DNS A records for each domain:

```
# Facility Domains
valehealthcare.com        A    YOUR_SERVER_IP
www.valehealthcare.com    A    YOUR_SERVER_IP

pacificmanor.com          A    YOUR_SERVER_IP
www.pacificmanor.com      A    YOUR_SERVER_IP

creeksidehcc.com          A    YOUR_SERVER_IP
www.creeksidehcc.com      A    YOUR_SERVER_IP

# Admin Domain
admin.biopacific.com      A    YOUR_SERVER_IP
```

## Web Server Configuration

### Nginx Configuration

Create `/etc/nginx/sites-available/biopacific-facilities`:

```nginx
server {
    listen 80;
    server_name *.com *.net *.org admin.biopacific.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name *.com *.net *.org admin.biopacific.com;

    root /var/www/biopacific/public;
    index index.php index.html;

    # SSL Configuration (use Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/your-domain/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;

        # Security
        fastcgi_hide_header X-Powered-By;
    }

    # Static Assets Caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }

    # Security - Hide sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ ^/(\.env|composer\.(json|lock)|package\.json|artisan) {
        deny all;
    }
}
```

### Apache Configuration (Alternative)

Create a virtual host configuration:

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/biopacific/public
    ServerName biopacific.com
    ServerAlias *.com *.net *.org admin.biopacific.com

    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    DocumentRoot /var/www/biopacific/public
    ServerName biopacific.com
    ServerAlias *.com *.net *.org admin.biopacific.com

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/your-domain/cert.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/your-domain/privkey.pem
    SSLCertificateChainFile /etc/letsencrypt/live/your-domain/chain.pem

    <Directory /var/www/biopacific/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/biopacific_error.log
    CustomLog ${APACHE_LOG_DIR}/biopacific_access.log combined
</VirtualHost>
```

## Update Navigation

Update `resources/views/partials/topbar/default.blade.php`:

```blade
<nav class="bg-white shadow-lg sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <!-- Logo -->
      <div class="flex items-center">
        <a href="{{ route('facility.home') }}" class="flex items-center space-x-3">
          @if(!empty($facility['logo_url']))
            <img src="{{ asset('images/' . $facility['logo_url']) }}"
                 alt="{{ $facility['name'] }} Logo"
                 class="h-10 w-auto">
          @else
            <div class="h-10 w-10 bg-primary rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-lg">
                {{ substr($facility['name'] ?? 'B', 0, 1) }}
              </span>
            </div>
          @endif
          <div class="hidden sm:block">
            <div class="font-bold text-gray-900">{{ $facility['name'] ?? 'Bio-Pacific' }}</div>
            <div class="text-xs text-gray-500">{{ $facility['tagline'] ?? 'Healthcare Excellence' }}</div>
          </div>
        </a>
      </div>

      <!-- Desktop Navigation -->
      <div class="hidden md:flex space-x-8">
        <a href="{{ route('facility.home') }}"
           class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors">
          Home
        </a>
        <a href="{{ route('facility.services') }}"
           class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors">
          Services
        </a>
        <a href="{{ route('facility.rooms') }}"
           class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors">
          Rooms
        </a>
        <a href="{{ route('facility.about') }}"
           class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors">
          About
        </a>
        <a href="{{ route('facility.contact') }}"
           class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors">
          Contact
        </a>
        <a href="{{ route('facility.tour') }}"
           class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">
          Book Tour
        </a>
      </div>

      <!-- Mobile menu button -->
      <div class="md:hidden">
        <button @click="mobileMenuOpen = !mobileMenuOpen"
                class="text-gray-700 hover:text-primary p-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Navigation -->
    <div x-show="mobileMenuOpen"
         x-transition
         class="md:hidden border-t border-gray-200">
      <div class="py-2 space-y-1">
        <a href="{{ route('facility.home') }}"
           class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary">
          Home
        </a>
        <a href="{{ route('facility.services') }}"
           class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary">
          Services
        </a>
        <a href="{{ route('facility.rooms') }}"
           class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary">
          Rooms
        </a>
        <a href="{{ route('facility.about') }}"
           class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary">
          About
        </a>
        <a href="{{ route('facility.contact') }}"
           class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary">
          Contact
        </a>
        <a href="{{ route('facility.tour') }}"
           class="block mx-4 my-2 px-4 py-2 bg-primary text-white rounded-lg text-center">
          Book Tour
        </a>
      </div>
    </div>
  </div>
</nav>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('navigation', () => ({
        mobileMenuOpen: false
    }))
})
</script>
```

## Deployment Steps

### 1. Update Database

First, update your facility records with production domains:

```sql
-- Update facilities with production domains
UPDATE facilities SET domain = 'valehealthcare.com' WHERE id = 1;
UPDATE facilities SET domain = 'pacificmanor.com' WHERE id = 2;
UPDATE facilities SET domain = 'creeksidehcc.com' WHERE id = 3;

-- Verify the update
SELECT id, name, domain, subdomain, is_active FROM facilities;
```

### 2. Server Preparation

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip php8.1-gd redis-server

# Install Composer globally
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js and npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### 3. Deploy Application

```bash
# Clone repository to server
cd /var/www
sudo git clone https://github.com/yourusername/biopacific.git
sudo chown -R www-data:www-data biopacific

# Install dependencies
cd biopacific
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Set up environment
sudo cp .env.example .env
sudo nano .env  # Configure production settings

# Generate application key
php artisan key:generate

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Clear and optimize caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set proper permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 4. SSL Certificate Setup

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificates for all domains
sudo certbot --nginx -d valehealthcare.com -d www.valehealthcare.com
sudo certbot --nginx -d pacificmanor.com -d www.pacificmanor.com
sudo certbot --nginx -d creeksidehcc.com -d www.creeksidehcc.com
sudo certbot --nginx -d admin.biopacific.com

# Set up automatic renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 5. Final Testing

```bash
# Test application
php artisan tinker
>>> App\Models\Facility::findByDomain('valehealthcare.com')

# Test routes
php artisan route:list | grep facility
php artisan route:list | grep admin

# Test web server
sudo nginx -t
sudo systemctl reload nginx

# Monitor logs
tail -f /var/log/nginx/access.log
tail -f storage/logs/laravel.log
```

## Admin Access

Administrators access the system through the dedicated admin domain:

### URLs Structure

```
Admin Dashboard:
https://admin.biopacific.com/admin/dashboard

Facility Management:
https://admin.biopacific.com/admin/facilities
https://admin.biopacific.com/admin/facilities/create
https://admin.biopacific.com/admin/facilities/1/edit
https://admin.biopacific.com/admin/facilities/1/content

User Management:
https://admin.biopacific.com/admin/users
https://admin.biopacific.com/admin/roles
```

### Authentication

Admins log in at: `https://admin.biopacific.com/admin/login`

## Security Considerations

### 1. Input Validation & Sanitization

```php
// Always validate user inputs
$validated = $request->validate([
    'name' => 'required|string|max:255|regex:/^[\p{L}\s\-\.\']+$/u',
    'email' => 'required|email:rfc,dns|max:255',
    'phone' => 'nullable|string|max:20|regex:/^[\+]?[1-9][\d\s\-\(\)]{7,15}$/',
    'message' => 'required|string|max:1000',
]);

// Sanitize output
echo e($userInput); // Laravel's escape function
```

### 2. Rate Limiting

Add to your routes:

```php
// Apply rate limiting to contact forms
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/contact', [FacilityPublicController::class, 'submitContact']);
    Route::post('/tour-request', [FacilityPublicController::class, 'requestTour']);
});
```

### 3. CSRF Protection

Ensure all forms include CSRF tokens:

```blade
<form method="POST" action="{{ route('facility.contact.submit') }}">
    @csrf
    <!-- form fields -->
</form>
```

### 4. Security Headers

Configure in your web server (shown in Nginx config above):

```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://unpkg.com; style-src 'self' 'unsafe-inline';" always;
```

### 5. Database Security

```php
// Always use parameter binding (Eloquent does this automatically)
$facilities = Facility::where('domain', $domain)->get(); // ✅ Safe

// Never use raw queries with user input like this:
// DB::raw("SELECT * FROM facilities WHERE domain = '{$domain}'"); // ❌ Dangerous
```

### 6. Error Handling

```php
// In production, log errors but don't expose details
try {
    // risky operation
} catch (\Exception $e) {
    \Log::error('Contact form error', [
        'error' => $e->getMessage(),
        'facility' => $facility->id,
        'user_ip' => $request->ip()
    ]);

    return back()->with('error', 'Sorry, there was a technical issue. Please try again or call us directly.');
}
```

## Final Verification Checklist

- [ ] All facility domains resolve correctly
- [ ] SSL certificates are installed and working
- [ ] Public pages load without authentication
- [ ] Admin pages require proper authentication
- [ ] No "admin" URLs visible to public users
- [ ] Contact forms work and send emails
- [ ] Each facility shows correct branding/content
- [ ] Mobile responsiveness works across all pages
- [ ] SEO meta tags are properly set
- [ ] Error pages are user-friendly
- [ ] Performance is optimized (caching, compression)
- [ ] Security headers are configured
- [ ] Backups are configured and tested

## Support & Maintenance

### Regular Maintenance Tasks

```bash
# Weekly maintenance script
#!/bin/bash
cd /var/www/biopacific

# Update dependencies (test first)
# composer update --no-dev
# npm update && npm run build

# Clear and rebuild caches
php artisan config:clear && php artisan config:cache
php artisan route:clear && php artisan route:cache
php artisan view:clear && php artisan view:cache

# Check for updates
php artisan queue:restart
php artisan schedule:run

# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

### Monitoring

Set up monitoring for:

- Website uptime for all domains
- SSL certificate expiration
- Server resources (CPU, memory, disk)
- Application errors in logs
- Database performance

This deployment strategy ensures that your Bio-Pacific facility system runs efficiently in production with proper security, performance, and maintainability.
