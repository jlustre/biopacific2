<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPEmpTaxData extends Model
{
    protected $table = 'bp_emp_tax_data';

    protected $primaryKey = 'tax_id';

    public $timestamps = true;

    protected $fillable = [
        'employee_num',
        'effdt',
        'effseq',
        'fed_tax_data',
        'fed_withholding_allowance',
        'state_tax_data',
        'state_withholding_allowance1',
        'resident',
        'local_withholding_allowance',
        'locality',
        'county',
        'addl_withholding_percentage1',
        'addl_withholding_amount1',
        'addl_withholding_percentage2',
        'addl_withholding_amount2',
        'resident_state',
    ];

    protected $casts = [
        'effdt' => 'date',
        'effseq' => 'integer',
        'fed_withholding_allowance' => 'decimal:2',
        'state_withholding_allowance1' => 'decimal:2',
        'local_withholding_allowance' => 'decimal:2',
        'addl_withholding_percentage1' => 'decimal:2',
        'addl_withholding_amount1' => 'decimal:2',
        'addl_withholding_percentage2' => 'decimal:2',
        'addl_withholding_amount2' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(BPEmployee::class, 'employee_num', 'employee_num');
    }
}
