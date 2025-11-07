# Secure ePHI Forms Implementation Guide

This document explains the HIPAA-compliant secure ePHI (Electronic Protected Health Information) handling system implemented for form submissions in the Bio-Pacific application.

## Overview

The system ensures that all form submissions containing potential PHI are:

- **Encrypted at rest** in the database
- **Never transmitted via email** in plain text
- **Accessed only via secure, time-limited tokens**
- **Audited for compliance** with full access logging

## Implementation Components

### 1. Database Encryption

#### EncryptsEphi Trait (`app/Traits/EncryptsEphi.php`)

- Automatically encrypts sensitive fields before database storage
- Decrypts data when retrieved (only in secure contexts)
- Generates secure access tokens for viewing
- Provides audit logging capabilities

**Encrypted Fields:**

- `full_name` - Contact's full name
- `email` - Email address
- `phone` - Phone number
- `message` - Form message content

#### Database Schema Changes

New fields added to `inquiries` table:

```sql
access_token VARCHAR(64) UNIQUE    -- Secure access token
token_expires_at TIMESTAMP         -- Token expiration
is_viewed BOOLEAN                  -- Viewing status
viewed_at TIMESTAMP                -- When accessed
viewed_by VARCHAR(255)             -- Who accessed it
is_encrypted BOOLEAN               -- Encryption status
encryption_key_hint TEXT           -- Key verification hint
```

### 2. Secure Email Notifications

#### SecureContactMail (`app/Mail/SecureContactMail.php`)

- Sends notifications without any PHI content
- Includes only secure access links
- Provides inquiry metadata (date, facility, consent status)
- Auto-generates time-limited access tokens

#### Email Template (`resources/views/emails/secure-contact-notification.blade.php`)

**What's INCLUDED in emails:**

- Facility name
- Inquiry received timestamp
- Consent/PHI confirmation status
- Secure access link (24-hour expiration)

**What's NEVER included:**

- Contact name, email, or phone
- Message content
- Any personal information

### 3. Secure Access System

#### SecureInquiryController

- **Token-based access**: Validates time-limited tokens
- **Audit logging**: Records all access attempts
- **Admin controls**: Token regeneration and management
- **Security checks**: IP logging, user tracking

#### Access Flow

1. Form submitted → Data encrypted → Token generated
2. Email sent with secure link (no PHI)
3. Recipient clicks link → Token validated
4. Access logged → Decrypted data displayed
5. Token expires after 24 hours

### 4. User Interface

#### Secure Inquiry View (`resources/views/secure/inquiry-view.blade.php`)

- HIPAA-compliant display of decrypted data
- Security notices and audit information
- Action buttons (reply, call)
- Token expiration warnings

#### Access Denied View (`resources/views/secure/inquiry-not-found.blade.php`)

- User-friendly error handling
- Security information
- Troubleshooting guidance

## Configuration

### Environment Variables

```bash
# Force ePHI encryption (recommended for production)
FORCE_EPHI_ENCRYPTION=true

# Required for encryption (Laravel app key)
APP_KEY=base64:your-app-key-here

# Email configuration for secure notifications
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### Application Configuration

In `config/app.php`:

```php
'force_ephi_encryption' => env('FORCE_EPHI_ENCRYPTION', false),
```

## Security Features

### 1. Encryption at Rest

- Uses Laravel's built-in encryption (AES-256-CBC)
- Automatic encryption/decryption via Eloquent model traits
- Key rotation support with hint tracking

### 2. Access Control

- Time-limited tokens (24-hour expiration)
- Single-use token option (configurable)
- Admin-only token regeneration
- Role-based access controls

### 3. Audit Logging

Every access is logged with:

- User identification
- IP address and user agent
- Timestamp and duration
- Facility and inquiry details
- Action taken (view, email, call)

### 4. Data Protection

- No PHI transmitted via email
- Encrypted database storage
- Secure HTTPS-only access
- CSRF protection on all forms

## Usage Examples

### For Developers

#### Creating a New Secure Form

```php
use App\Traits\EncryptsEphi;

class MySecureModel extends Model
{
    use EncryptsEphi;

    protected function getEncryptedFields(): array
    {
        return ['sensitive_field1', 'sensitive_field2'];
    }
}
```

#### Sending Secure Notifications

```php
use App\Mail\SecureContactMail;

// Create inquiry with encrypted data
$inquiry = Inquiry::create($validatedData);

// Send secure notification (no PHI in email)
Mail::to($recipient)->send(new SecureContactMail($inquiry));
```

### For Administrators

#### Viewing Secure Inquiries

1. Access Admin Dashboard
2. Navigate to "Secure Inquiries"
3. Click secure link to view encrypted data
4. Access is logged automatically

#### Regenerating Access Tokens

```php
// Via API or admin interface
$inquiry->generateSecureAccessToken();
```

## HIPAA Compliance Features

### 1. Administrative Safeguards

- **Assigned Security Responsibility**: Admin-only access controls
- **Workforce Training**: Documentation and procedures
- **Access Management**: Role-based permissions
- **Audit Controls**: Comprehensive logging

### 2. Physical Safeguards

- **Facility Access Controls**: Server-level security
- **Workstation Use**: Secure access terminals
- **Device/Media Controls**: Encrypted storage

### 3. Technical Safeguards

- **Access Control**: Unique user identification
- **Audit Controls**: Automatic access logging
- **Integrity**: Encryption prevents tampering
- **Transmission Security**: HTTPS-only communication

## Monitoring and Compliance

### Audit Reports

Access logs include:

```json
{
  "inquiry_id": 123,
  "facility_id": 1,
  "facility_name": "Example Facility",
  "accessed_by": "admin@example.com",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "access_time": "2025-11-06T10:30:00Z",
  "action": "viewed"
}
```

### Compliance Checks

The system automatically validates:

- ✅ Encryption implementation
- ✅ Secure email templates
- ✅ Access token system
- ✅ Audit logging functionality
- ✅ Route security

## Troubleshooting

### Common Issues

1. **Decryption Errors**

   - Check `APP_KEY` configuration
   - Verify encryption key consistency
   - Review error logs for details

2. **Token Expiration**

   - Tokens expire after 24 hours
   - Admin can regenerate new tokens
   - Check `token_expires_at` field

3. **Email Delivery**
   - Verify SMTP configuration
   - Check email queue processing
   - Review mail logs

### Development vs Production

**Development Environment:**

- Encryption optional (controlled by config)
- Extended token expiration for testing
- Additional debug logging

**Production Environment:**

- Encryption always enabled
- Strict token expiration (24 hours)
- Enhanced security logging

## Migration Guide

### From Unencrypted Forms

1. **Run Migration**

   ```bash
   php artisan migrate
   ```

2. **Update Form Controllers**
   Replace `ContactMail` with `SecureContactMail`

3. **Configure Environment**

   ```bash
   FORCE_EPHI_ENCRYPTION=true
   ```

4. **Test Functionality**
   - Submit test form
   - Verify secure email received
   - Confirm encrypted storage
   - Test secure access link

### Existing Data

Existing inquiries will continue to work but won't be encrypted until updated. Consider:

- Running data migration script
- Gradual encryption during normal operations
- Audit existing unencrypted records

## Best Practices

### For Developers

1. **Always use secure traits** for models containing PHI
2. **Never log decrypted data** in application logs
3. **Use secure email classes** for all notifications
4. **Implement proper error handling** for encryption failures

### For Administrators

1. **Regular audit log reviews** for compliance
2. **Monitor token usage patterns** for suspicious activity
3. **Maintain secure backup procedures** for encrypted data
4. **Document access procedures** for staff training

### For Security

1. **Rotate encryption keys** regularly
2. **Monitor failed access attempts**
3. **Review token expiration policies**
4. **Audit email configurations** for security

## Performance Considerations

### Database Impact

- Encrypted fields are larger (base64 encoding overhead)
- Indexing limitations on encrypted fields
- Consider separate tables for high-volume data

### Memory Usage

- Decryption occurs in memory
- Consider pagination for large datasets
- Monitor memory usage with encryption

### Caching

- Never cache decrypted PHI data
- Token validation caching safe
- Use encrypted field identifiers for caching keys

This implementation ensures full HIPAA compliance while maintaining usability and performance for the Bio-Pacific healthcare platform.
