<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Upload extends Model
{
    use HasFactory;

    public const EMPLOYEE_UPLOAD_ROOT = 'employee_documents';

    public const EXPIRY_NOTIFICATION_WINDOW_DAYS = 120;

    public const EXPIRY_NOTIFICATION_URGENT_DAYS = 30;

    protected static function booted(): void
    {
        static::deleting(function (self $upload) {
            $upload->deleteStoredFile();
        });
    }

    public static function employeeDirectory(string $employeeNum): string
    {
        return static::EMPLOYEE_UPLOAD_ROOT . '/' . trim($employeeNum);
    }

    public static function storeEmployeeFile(UploadedFile $file, string $employeeNum, string $disk = 'public'): string
    {
        $directory = static::employeeDirectory($employeeNum);
        Storage::disk($disk)->makeDirectory($directory);

        return $file->store($directory, $disk);
    }

    public function deleteStoredFile(string $disk = 'public'): void
    {
        if (!$this->file_path) {
            return;
        }

        $storage = Storage::disk($disk);

        if ($storage->exists($this->file_path)) {
            $storage->delete($this->file_path);
        }

        $directory = dirname($this->file_path);
        if ($directory === '.' || $directory === static::EMPLOYEE_UPLOAD_ROOT) {
            return;
        }

        if (empty($storage->files($directory)) && empty($storage->directories($directory))) {
            $storage->deleteDirectory($directory);
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'facility_id',
        'employee_num',
        'user_id',
        'upload_type_id',
        'file_path',
        'original_filename',
        'file_size',
        'uploaded_at',
        'expires_at',
        'effective_start_date',
        'comments',
        'submission_reason',
        'verification_status',
        'submitted_for_review_at',
        'verified_by_user_id',
        'verified_at',
        'verification_notes',
    ];

    public const VERIFICATION_PENDING = 'pending';

    public const VERIFICATION_APPROVED = 'approved';

    public const VERIFICATION_REJECTED = 'rejected';

    protected $casts = [
        'uploaded_at' => 'datetime',
        'expires_at' => 'date',
        'effective_start_date' => 'date',
        'submitted_for_review_at' => 'datetime',
        'verified_at' => 'datetime',
    ];
    /**
     * Get the employee that owns this upload.
     */
    public function employee()
    {
        return $this->belongsTo(BPEmployee::class, 'employee_num', 'employee_num');
    }

    
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOwnedBy(?User $user): bool
    {
        if ($user === null || $this->user_id === null) {
            return false;
        }

        return (int) $this->user_id === (int) $user->id;
    }

    public function uploadType()
    {
        return $this->belongsTo(UploadType::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function submissionReasonLabel(): ?string
    {
        return \App\Support\UploadSubmissionReason::label($this->submission_reason);
    }

    public function verificationStatusLabel(): ?string
    {
        return match ($this->verification_status) {
            self::VERIFICATION_PENDING => 'Pending review',
            self::VERIFICATION_APPROVED => 'Approved',
            self::VERIFICATION_REJECTED => 'Rejected',
            default => null,
        };
    }

    public function canSubmitForVerification(): bool
    {
        return in_array($this->verification_status, [null, '', self::VERIFICATION_REJECTED], true);
    }

    public function isPendingVerification(): bool
    {
        return $this->verification_status === self::VERIFICATION_PENDING;
    }

    public function canVerifyDocument(?User $user): bool
    {
        if ($user === null || ! $this->isPendingVerification()) {
            return false;
        }

        return $user->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd']);
    }

    /**
     * Expiry tier for notification emails: expired, urgent (≤30 days), soon (31–120 days), or null if not eligible.
     */
    public function expiryNotificationTier(): ?string
    {
        if (!$this->expires_at) {
            return null;
        }

        $expires = Carbon::parse($this->expires_at)->startOfDay();
        $today = now()->startOfDay();
        $windowEnd = $today->copy()->addDays(self::EXPIRY_NOTIFICATION_WINDOW_DAYS);

        if ($expires->gt($windowEnd)) {
            return null;
        }

        if ($expires->lt($today)) {
            return 'expired';
        }

        $urgentEnd = $today->copy()->addDays(self::EXPIRY_NOTIFICATION_URGENT_DAYS);
        if ($expires->lte($urgentEnd)) {
            return 'urgent';
        }

        return 'soon';
    }

    public function canSendExpiryNotification(): bool
    {
        return $this->expiryNotificationTier() !== null;
    }
}
