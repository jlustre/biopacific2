# BioPacific Security Quick Reference

## � Complete Security Workflow

### Visual Process Flow

```
1. USER SUBMITS FORM
   ↓ (ePHI entered)

2. DATA ENCRYPTION
   ↓ (All ePHI fields encrypted)

3. TOKEN GENERATION
   ↓ (64-char secure token + SHA-256 hash)

4. DATABASE STORAGE
   ↓ (Encrypted data + hashed token stored)

5. EMAIL NOTIFICATION
   ↓ (ZERO ePHI - only secure link sent)

6. STAFF RECEIVES EMAIL
   ↓ (Staff clicks secure link)

7. TOKEN VALIDATION
   ↓ (Hash verification + expiration check)

8. STAFF VERIFICATION
   ↓ (Staff email verification required)

9. EPHI ACCESS GRANTED
   ↓ (Data decrypted + displayed securely)

10. AUDIT LOGGING
    ↓ (Complete access trail recorded)

11. SECURITY MONITORING
    ↓ (Real-time anomaly detection)
```

### Process Timing

- **Form to Database**: 3-5 seconds
- **Email Delivery**: 5-30 seconds
- **Staff Verification**: 30 seconds - 5 minutes
- **Data Access**: 1-2 seconds
- **Audit Logging**: Continuous

## �🚀 Quick Start Commands

```bash
# Clean all test data
php artisan security:clean-test-data --confirm

# Verify security setup
php artisan security:verify-data-sources

# Test complete system
php artisan security:test

# Check HIPAA compliance
php artisan hipaa:check

# Generate security report
curl "https://biopacific.com/admin/security-monitoring/export"
```

## 🔐 Security Implementation Checklist

### New Secure Model Setup

- [ ] Add `EncryptsEphi` trait
- [ ] Define `$encryptedFields` array
- [ ] Add security columns (access_token, expires_at, audit_log, viewed_at)
- [ ] Create secure controller
- [ ] Add secure routes
- [ ] Create email notification class
- [ ] Add to security monitoring

### Required Security Columns

```php
Schema::table('your_table', function (Blueprint $table) {
    $table->string('access_token', 255)->unique()->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->json('audit_log')->nullable();
    $table->timestamp('viewed_at')->nullable();
    $table->string('staff_email')->nullable();
});
```

### Model Implementation

```php
use App\Traits\EncryptsEphi;

class YourModel extends Model
{
    use EncryptsEphi;

    protected $encryptedFields = [
        'first_name',
        'last_name',
        'email',
        'phone'
    ];

    protected $casts = [
        'audit_log' => 'array',
        'expires_at' => 'datetime',
        'viewed_at' => 'datetime'
    ];
}
```

## 📊 Security Monitoring Routes

| Route                                                | Purpose              | Access      |
| ---------------------------------------------------- | -------------------- | ----------- |
| `/admin/security-monitoring`                         | Main dashboard       | Admin only  |
| `/admin/security-monitoring/anomalies`               | Detailed anomalies   | Admin only  |
| `/admin/security-monitoring/incidents`               | Security incidents   | Admin only  |
| `/admin/security-monitoring/record-logs/{type}/{id}` | Record history       | Admin only  |
| `/secure/{type}/{token}`                             | Public secure access | Token-based |

## 🛡️ Access Status Codes

| Status                      | Meaning                   | Action Required |
| --------------------------- | ------------------------- | --------------- |
| `success`                   | Authorized access         | Monitor         |
| `unauthorized`              | Invalid token             | Investigate     |
| `expired`                   | Token expired             | Normal          |
| `invalid_token`             | Bad token format          | Alert           |
| `unauthorized_email`        | Staff verification failed | Review          |
| `staff_verification_failed` | Staff auth failed         | Alert           |

## 📧 Secure Email Templates

### Email Security Rules

- ✅ **Never include ePHI** in email content
- ✅ **Only secure links** allowed
- ✅ **Clear expiration times**
- ✅ **Security warnings** included

### Template Structure

```php
// emails/secure-{type}.blade.php
- Secure access link only
- Expiration time
- Security instructions
- No personal information
```

## 🔍 Audit Logging

### Required Log Fields

```php
SecureAccessLog::create([
    'access_time' => now(),
    'token_type' => $type,          // inquiry, tour_request, job_application
    'record_id' => $id,
    'access_status' => $status,     // success, unauthorized, expired, etc.
    'ip_address' => $request->ip(),
    'staff_email' => $staffEmail,   // if verified
    'facility_id' => $facilityId,
    'user_agent' => $request->userAgent(),
    'request_headers' => $request->headers->all()
]);
```

## 🚨 Security Incident Types

| Type                 | Trigger               | Severity |
| -------------------- | --------------------- | -------- |
| `multiple_failures`  | 3+ failed attempts    | High     |
| `suspicious_ip`      | Multi-facility access | Critical |
| `geographic_anomaly` | Unusual location      | Medium   |
| `token_abuse`        | Rapid token use       | High     |

## 🔧 Configuration Variables

```env
# Security Settings
SECURE_TOKEN_EXPIRY_HOURS=72
STAFF_VERIFICATION_REQUIRED=true
MAX_FAILED_ATTEMPTS=3
SECURITY_MONITORING_ENABLED=true

# Email Security
SECURE_EMAIL_FROM=security@biopacific.com
STAFF_VERIFICATION_REQUIRED=true
```

## 📱 Responsive Design Classes

### Desktop/Mobile Table Pattern

```html
<!-- Desktop -->
<div class="hidden md:block overflow-x-auto">
  <table class="min-w-full">
    ...
  </table>
</div>

<!-- Mobile -->
<div class="md:hidden space-y-4">
  <div class="bg-white border rounded-lg p-4">...</div>
</div>
```

## 🎯 Performance Tips

### Database Indexes

```sql
CREATE INDEX idx_secure_logs_type_record ON secure_access_logs(token_type, record_id);
CREATE INDEX idx_secure_logs_time ON secure_access_logs(access_time);
CREATE INDEX idx_secure_logs_ip ON secure_access_logs(ip_address);
```

### Query Optimization

```php
// Use eager loading
$logs = SecureAccessLog::with('facility')->get();

// Cache facilities
$facilities = Cache::remember('facilities', 3600, fn() =>
    Facility::orderBy('name')->get()
);
```

## 🚫 Security Don'ts

- ❌ Never log ePHI in plain text
- ❌ Don't send ePHI via email
- ❌ Never use predictable tokens
- ❌ Don't skip audit logging
- ❌ Never expose raw database IDs
- ❌ Don't forget token expiration
- ❌ Never skip staff verification

## ✅ Security Do's

- ✅ Always encrypt ePHI fields
- ✅ Use secure random tokens
- ✅ Log all access attempts
- ✅ Implement proper expiration
- ✅ Require staff verification
- ✅ Monitor for anomalies
- ✅ Regular security reviews

---

**Quick Reference Version**: 1.0  
**For Full Documentation**: See `HIPAA_SECURITY_DOCUMENTATION.md`
