<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Upload extends Model
{
    use HasFactory;

    public const EMPLOYEE_UPLOAD_ROOT = 'employee_documents';

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

    public function uploadType()
    {
        return $this->belongsTo(UploadType::class);
    }

    
}
