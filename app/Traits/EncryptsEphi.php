<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait EncryptsEphi
{
    /**
     * Fields that should be encrypted when storing ePHI.
     */
    protected function getEncryptedFields(): array
    {
        return [
            'full_name',
            'email', 
            'phone',
            'message'
        ];
    }

    /**
     * Boot the trait.
     */
    public static function bootEncryptsEphi(): void
    {
        // Encrypt ePHI fields before saving
        static::saving(function ($model) {
            if ($model->shouldEncryptEphi()) {
                $model->encryptEphiFields();
            }
        });

        // Decrypt ePHI fields after retrieving
        static::retrieved(function ($model) {
            if ($model->isEncrypted()) {
                $model->decryptEphiFields();
            }
        });
    }

    /**
     * Determine if ePHI should be encrypted for this record.
     */
    protected function shouldEncryptEphi(): bool
    {
        // Always encrypt in production, or if explicitly requested
        return app()->environment('production') || 
               config('app.force_ephi_encryption', false) ||
               $this->getAttribute('is_encrypted') === true;
    }

    /**
     * Check if this record is encrypted.
     */
    public function isEncrypted(): bool
    {
        return $this->getAttribute('is_encrypted') === true;
    }

    /**
     * Encrypt ePHI fields.
     */
    protected function encryptEphiFields(): void
    {
        foreach ($this->getEncryptedFields() as $field) {
            if ($this->isDirty($field) && !empty($this->getAttribute($field))) {
                $value = $this->getAttribute($field);
                
                // Only encrypt if not already encrypted
                if (!$this->isFieldEncrypted($value)) {
                    $this->setAttribute($field, $this->encryptValue($value));
                }
            }
        }
        
        // Mark as encrypted
        $this->setAttribute('is_encrypted', true);
        $this->setAttribute('encryption_key_hint', $this->generateKeyHint());
    }

    /**
     * Decrypt ePHI fields.
     */
    protected function decryptEphiFields(): void
    {
        foreach ($this->getEncryptedFields() as $field) {
            $value = $this->getAttribute($field);
            
            if (!empty($value) && $this->isFieldEncrypted($value)) {
                try {
                    $this->setAttribute($field, $this->decryptValue($value));
                } catch (\Exception $e) {
                    // Log decryption failure but don't break the application
                    Log::warning("Failed to decrypt field {$field} for model " . get_class($this), [
                        'model_id' => $this->getKey(),
                        'error' => $e->getMessage()
                    ]);
                    
                    // Set a placeholder value
                    $this->setAttribute($field, '[ENCRYPTED - DECRYPTION FAILED]');
                }
            }
        }
    }

    /**
     * Encrypt a single value.
     */
    protected function encryptValue(string $value): string
    {
        return Crypt::encryptString($value);
    }

    /**
     * Decrypt a single value.
     */
    protected function decryptValue(string $value): string
    {
        return Crypt::decryptString($value);
    }

    /**
     * Check if a field value is encrypted.
     */
    protected function isFieldEncrypted(string $value): bool
    {
        // Laravel's encrypted values typically start with 'eyJpdiI6' (base64 encoded JSON)
        return str_starts_with($value, 'eyJpdiI6') || 
               str_starts_with($value, 'eyJpdiI6') ||
               (strlen($value) > 100 && preg_match('/^[A-Za-z0-9+\/]+=*$/', $value));
    }

    /**
     * Generate a hint about which encryption key was used.
     */
    protected function generateKeyHint(): string
    {
        $appKey = config('app.key');
        return substr(hash('sha256', $appKey), 0, 8);
    }

    /**
     * Generate a secure access token for this record.
     */
    public function generateSecureAccessToken(): string
    {
        $token = Str::random(64);
        $this->setAttribute('access_token', $token);
        $this->setAttribute('token_expires_at', now()->addHours(24)); // Token expires in 24 hours
        $this->save();
        
        return $token;
    }

    /**
     * Check if the access token is valid.
     */
    public function isValidAccessToken(string $token): bool
    {
        return $this->getAttribute('access_token') === $token &&
               $this->getAttribute('token_expires_at') &&
               now()->lt($this->getAttribute('token_expires_at'));
    }

    /**
     * Mark this inquiry as viewed.
     */
    public function markAsViewed(string $viewedBy = null): void
    {
        $this->setAttribute('is_viewed', true);
        $this->setAttribute('viewed_at', now());
        $this->setAttribute('viewed_by', $viewedBy);
        $this->save();
    }

    /**
     * Get safe data for email notifications (no ePHI).
     */
    public function getSafeDataForEmail(): array
    {
        return [
            'id' => $this->getKey(),
            'facility_name' => $this->facility->name ?? 'Unknown Facility',
            'created_at' => $this->created_at,
            'has_message' => !empty($this->getAttribute('message')),
            'consent_given' => $this->getAttribute('consent'),
            'no_phi_confirmed' => $this->getAttribute('no_phi'),
        ];
    }
}