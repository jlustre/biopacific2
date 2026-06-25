<?php

namespace App\Models;

use App\Helpers\PhoneHelper;
use Illuminate\Database\Eloquent\Model;

class BPEmpPhone extends Model
{
    public const PRIMARY_YES = 'Y';

    public const PRIMARY_NO = 'N';

    protected $table = 'bp_emp_phones';
    protected $primaryKey = 'phone_id';
    public $timestamps = true;

    protected $fillable = [
        'employee_num',
        'phone_type',
        'effdt',
        'effseq',
        'phone_number',
        'is_primary',
    ];

    protected $casts = [
        'effdt' => 'date',
        'effseq' => 'integer',
    ];

    public function setPhoneNumberAttribute(?string $value): void
    {
        $this->attributes['phone_number'] = PhoneHelper::normalizeForStorage($value);
    }
}
