# HTTPS + HSTS Implementation Guide

This document explains the HIPAA-compliant HTTPS + HSTS implementation for the Bio-Pacific application.

## Overview

The application now automatically:

- Forces HTTPS redirects in production
- Sets HSTS headers for enhanced security
- Prevents mixed content issues
- Adds comprehensive security headers

## Components Implemented

### 1. Middleware

#### ForceHttps Middleware (`app/Http/Middleware/ForceHttps.php`)

- Automatically redirects HTTP requests to HTTPS in production
- Returns 301 permanent redirects for SEO benefits
- Only active in production environment

#### SecurityHeaders Middleware (`app/Http/Middleware/SecurityHeaders.php`)

- Adds HSTS header (Strict-Transport-Security) in production
- Sets security headers (X-Content-Type-Options, X-Frame-Options, etc.)
- Configures Content Security Policy (CSP)

#### TrustProxies Middleware (`app/Http/Middleware/TrustProxies.php`)

- Properly detects HTTPS when behind load balancers/proxies
- Trusts common proxy headers (X-Forwarded-Proto, etc.)

### 2. Secure Asset Helpers

#### SecureAssetHelper (`app/Helpers/SecureAssetHelper.php`)

Provides methods to ensure all assets use HTTPS:

- `secureAsset()` - Secure asset URLs
- `secureUrl()` - Secure general URLs
- `secureRoute()` - Secure route URLs
- `fixMixedContent()` - Fix mixed content in HTML

#### Blade Directives

New Blade directives available:

- `@secureAsset('path/to/asset')` - Generate secure asset URLs
- `@secureUrl('some/path')` - Generate secure URLs
- `@secureRoute('route.name')` - Generate secure route URLs

### 3. Service Provider

#### HttpsServiceProvider (`app/Providers/HttpsServiceProvider.php`)

- Forces HTTPS scheme for all URLs in production
- Registers custom Blade directives
- Automatically loads on application boot

## Configuration

### Environment Variables

For production, set these in your `.env` file:

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
FORCE_HTTPS=true
```

### Application Configuration

The `config/app.php` now includes:

- Automatic HTTPS URL detection in production
- `force_https` configuration option

## HSTS Configuration

The HSTS header is configured with:

- `max-age=31536000` (1 year)
- `includeSubDomains` - Applies to all subdomains
- `preload` - Eligible for browser preload lists

## Content Security Policy

The CSP header allows:

- Self-hosted assets
- Google Fonts (fonts.googleapis.com, fonts.gstatic.com)
- CDN resources (unpkg.com, cdn.jsdelivr.net)
- Inline styles and scripts (with 'unsafe-inline')

You can customize the CSP in `SecurityHeaders` middleware.

## Testing HTTPS Implementation

### Local Development

- HTTPS enforcement is disabled in local environment
- Use Laravel Valet or similar for local HTTPS testing

### Production Testing

1. Verify HTTPS redirect: `curl -I http://yourdomain.com`
2. Check HSTS header: `curl -I https://yourdomain.com`
3. Test mixed content: Check browser console for mixed content warnings
4. Validate headers: Use online tools like securityheaders.com

## Mixed Content Prevention

The implementation prevents mixed content by:

1. Forcing HTTPS scheme for all Laravel-generated URLs
2. Using secure asset helpers in Blade templates
3. Automatic protocol replacement in production
4. CSP headers that block insecure requests

## Best Practices

### For Developers

1. Always use `@secureAsset()` instead of `asset()` in templates
2. Use `@secureUrl()` for generating URLs
3. Test with production environment settings locally
4. Validate external URLs support HTTPS before linking

### For Deployment

1. Ensure SSL certificate is properly installed
2. Configure web server to redirect HTTP to HTTPS
3. Set up HSTS at web server level for additional security
4. Monitor for mixed content issues after deployment

## Web Server Configuration

### Nginx Example

```nginx
server {
    listen 443 ssl http2;

    # SSL Configuration
    ssl_certificate /path/to/certificate.pem;
    ssl_certificate_key /path/to/private-key.pem;

    # HSTS (handled by Laravel middleware, but can be doubled here)
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    # Security Headers (Laravel middleware handles these too)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    return 301 https://$server_name$request_uri;
}
```

### Apache Example

```apache
<VirtualHost *:80>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private-key.key

    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
</VirtualHost>
```

## HIPAA Compliance Notes

This implementation addresses HIPAA requirements by:

- Ensuring all data transmission is encrypted (HTTPS)
- Preventing downgrade attacks (HSTS)
- Blocking mixed content that could leak information
- Adding security headers to prevent XSS and clickjacking
- Forcing secure connections for all facility data

## Troubleshooting

### Common Issues

1. **Mixed Content Warnings**: Update templates to use secure asset helpers
2. **HTTPS Not Detected**: Check TrustProxies configuration
3. **Assets Not Loading**: Verify CDN/external resources support HTTPS
4. **HSTS Not Working**: Ensure middleware is loaded before response

### Debug Commands

```bash
# Check middleware loading
php artisan route:list --middleware

# Clear config cache
php artisan config:clear

# Test HTTPS locally with Valet
valet secure your-app-name
```

## Security Headers Explanation

- **Strict-Transport-Security**: Forces HTTPS and prevents downgrade attacks
- **X-Content-Type-Options**: Prevents MIME type sniffing attacks
- **X-Frame-Options**: Prevents clickjacking attacks
- **X-XSS-Protection**: Enables XSS filtering in older browsers
- **Content-Security-Policy**: Controls which resources can be loaded
- **Referrer-Policy**: Controls how much referrer information is sent

This implementation ensures your Bio-Pacific application meets HIPAA security requirements for data transmission encryption and prevents common web security vulnerabilities.
