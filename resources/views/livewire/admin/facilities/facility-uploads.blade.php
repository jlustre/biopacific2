<div>
    @if(session('success'))
        <div class="p-3 mb-4 text-green-800 bg-green-100 border border-green-400 rounded">
            {{ session('success') }}
        </div>
    @endif
    <form wire:submit.prevent="submit" class="grid grid-cols-2 gap-4 mb-6" enctype="multipart/form-data">
        <div class="col-span-1">
            <label class="block mb-1 text-xs font-semibold">Select Facility <span class="text-red-600">*</span></label>
            <select wire:model="facility_id" class="form-select w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600">
                <option value="">-- Choose Facility --</option>
                @foreach($facilities as $fac)
                    <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-1">
            <label class="block mb-1 text-xs font-semibold">Employee <span class="text-red-600">*</span></label>
            <select wire:model="employee_id" class="form-select w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600">
                <option value="">-- Select Employee --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->employee_num }}">{{ $employee->last_name }}, {{ $employee->first_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-1">
            <label class="block mb-1 text-xs font-semibold">Upload Type <span class="text-red-600">*</span></label>
            <select wire:model="upload_type_id" class="form-select w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600">
                <option value="">Select Type</option>
                @foreach($uploadTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
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
                <label class="block mb-1 text-xs font-semibold">Expires At</label>
                <input type="date" wire:model="expires_at" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full">
            </div>
        </div>
        <div class="col-span-2 flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block mb-1 text-xs font-semibold">Comments</label>
                <textarea wire:model="comments" rows="2" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full min-w-[220px] resize-y"></textarea>
            </div>
            <div class="flex-shrink-0">
                <button type="submit" class="px-4 py-2 font-semibold text-white bg-teal-600 rounded cursor-pointer hover:bg-teal-700">Upload</button>
            </div>
        </div>
    </form>
</div>
