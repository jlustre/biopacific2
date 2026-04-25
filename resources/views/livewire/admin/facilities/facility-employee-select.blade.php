<div>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="col-span-1">
            <label for="facility-select" class="block mb-1 text-xs font-semibold">Select Facility <span class="text-red-600">*</span></label>
            <select id="facility-select" wire:model="facility_id" class="form-select w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600">
                <option value="">-- Choose Facility --</option>
                @foreach($facilities as $fac)
                    <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-1">
            <label for="employee-select" class="block mb-1 text-xs font-semibold">Employee <span class="text-red-600">*</span></label>
            <select id="employee-select" wire:model="employee_id" class="form-select w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600">
                <option value="">-- Select Employee --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->last_name }}, {{ $employee->first_name }} @if($employee->employee_num) [{{ $employee->employee_num }}]@endif</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
