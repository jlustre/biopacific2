<div>
    <div style="background: #ff0; color: #000; padding: 10px; font-weight: bold;">Livewire COMPONENT RENDERED: If you see this, the Livewire view is being rendered.</div>
    @if(session('success'))
        <div class="p-3 mb-4 text-green-800 bg-green-100 border border-green-400 rounded">
            {{ session('success') }}
        </div>
    @endif
    <div style="background: #f00; color: #fff; padding: 8px; margin-bottom: 8px; font-weight: bold;">
        Debug: Current facility_id = {{ $facility_id }}
    </div>
    <form wire:submit.prevent="submit" class="grid grid-cols-2 gap-4 mb-6" enctype="multipart/form-data">
        <div class="col-span-1">
            <label for="facility-select" class="block mb-1 text-xs font-semibold">Select Facility <span class="text-red-600">*</span></label>
            <select id="facility-select" wire:model="facility_id" wire:key="facility-select" class="form-select w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600">
                <option value="">-- Choose Facility --</option>
                @foreach($facilities as $fac)
                    <option value="{{ (string)$fac->id }}">{{ $fac->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-1">
            <label for="employee-select" class="block mb-1 text-xs font-semibold">Employee <span class="text-red-600">*</span></label>
            <!-- Debug: Show count and IDs of employees -->
            <div style="background: #007; color: #fff; padding: 4px; font-size: 12px; margin-bottom: 4px;">
                Employees loaded: {{ count($employees) }}
                @if(count($employees) > 0)
                    [IDs: @foreach($employees as $e){{ $e->emp_id }}@if(!$loop->last), @endif@endforeach]
                @endif
            </div>
            <select id="employee-select" wire:model="employee_id" class="form-select w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600">
                <option value="">-- Select Employee --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->emp_id }}">{{ $employee->last_name }}, {{ $employee->first_name }} @if($employee->emp_id) [{{ $employee->emp_id }}]@endif</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-1">
            <label class="block mb-1 text-xs font-semibold">Upload Type <span class="text-red-600">*</span></label>
            <select wire:model="upload_type_id" class="form-select w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600">
                <option value="">Select Type</option>
                @foreach($uploadTypes as $type)
                    <option value="{{ $type->id }}" data-requires-expiry="{{ $type->requires_expiry ? '1' : '0' }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-1">
            <label class="block mb-1 text-xs font-semibold">File <span class="text-red-600">*</span></label>
            <input type="file" wire:model="file" class="form-input w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600">
        </div>
        <div class="col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block mb-1 text-xs font-semibold">Effective Start Date</label>
                <input type="date" wire:model="effective_start_date" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Effective End Date</label>
                <input type="date" wire:model="effective_end_date" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Expires At</label>
                <input type="date" wire:model="expires_at" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full">
            </div>
        </div>
        <div class="col-span-2 flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block mb-1 text-xs font-semibold">Comments</label>
                <textarea wire:model="comments" rows="2" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full min-w-[220px] resize-y" style="flex:1; min-width:220px; width:100%;"></textarea>
            </div>
            <div class="flex-shrink-0">
                <button type="submit" class="px-4 py-2 font-semibold text-white bg-teal-600 rounded cursor-pointer hover:bg-teal-700">Upload</button>
            </div>
        </div>
    </form>
</div>