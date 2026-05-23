@php
$states = \App\Models\State::orderBy('name')->get(['abbreviation', 'name']);
@endphp
<div x-show="tab === 'tax-data'" x-cloak data-employee-tab-panel="tax-data" x-data="taxForm()" x-init="initTax()">
    @if(isset($isAddMode) && $isAddMode)
        <div class="p-6 mb-6 bg-white rounded shadow text-gray-600">
            <div class="mb-2 p-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded">
                <strong>Notice:</strong> Please complete and save the Personal tab form before adding tax data.
            </div>
            <em>Save the employee record before adding tax data.</em>
        </div>
    @else
    <div class="flex justify-end items-center mb-4 space-x-4">
        <button type="button" @click="clearTax()"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 cursor-pointer">
            Add New Tax Data
        </button>
        <template x-if="isLatestRecord()">
            <span class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 rounded text-sm font-semibold">Latest Record</span>
        </template>
    </div>
    <form method="POST" action="{{ route('admin.employees.tax.update', $employee->id) }}" @submit="confirmTaxSubmit($event)">
        @csrf
        @method('PUT')

        <div class="bg-white shadow rounded-lg p-4 mb-6 space-y-5">
            {{-- Effective dating --}}
            <section class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
                <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-200 pb-2 mb-4">Effective Dating</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl">
                    <div>
                        <label class="block text-sm font-medium mb-1">Effective Date</label>
                        <input type="date" name="effdt" x-model="currentTax.effdt" required
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                        @error('effdt')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Effective Sequence</label>
                        <input type="number" name="effseq" x-model="currentTax.effseq" readonly
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1 bg-gray-100 cursor-not-allowed">
                    </div>
                </div>
            </section>

            {{-- Federal tax --}}
            <section class="rounded-lg border border-slate-200 bg-white p-4">
                <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-200 pb-2 mb-4">Federal Tax</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Federal Tax Data</label>
                        <select name="fed_tax_data" x-model="currentTax.fed_tax_data"
                            class="form-select w-full border border-teal-300 rounded-lg px-2 py-1">
                            <option value="">-- Select --</option>
                            <option value="1">Single</option>
                            <option value="2">Married</option>
                        </select>
                        @error('fed_tax_data')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Federal Withholding Allowance</label>
                        <input type="number" name="fed_withholding_allowance" step="0.01" min="0"
                            x-model="currentTax.fed_withholding_allowance"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                        @error('fed_withholding_allowance')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            {{-- State tax --}}
            <section class="rounded-lg border border-slate-200 bg-white p-4">
                <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-200 pb-2 mb-4">State Tax</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">State Tax Data</label>
                        <select name="state_tax_data" x-model="currentTax.state_tax_data"
                            class="form-select w-full border border-teal-300 rounded-lg px-2 py-1">
                            <option value="">-- Select --</option>
                            <option value="1">Single</option>
                            <option value="2">Married</option>
                        </select>
                        @error('state_tax_data')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">State Withholding Allowance</label>
                        <input type="number" name="state_withholding_allowance1" step="0.01" min="0"
                            x-model="currentTax.state_withholding_allowance1"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                        @error('state_withholding_allowance1')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Resident State</label>
                        <select name="resident_state" x-model="currentTax.resident_state"
                            class="form-select w-full border border-teal-300 rounded-lg px-2 py-1">
                            @foreach($states as $state)
                                <option value="{{ $state->abbreviation }}">{{ $state->abbreviation }} — {{ $state->name }}</option>
                            @endforeach
                        </select>
                        @error('resident_state')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Resident</label>
                        <select name="resident" x-model="currentTax.resident"
                            class="form-select w-full border border-teal-300 rounded-lg px-2 py-1">
                            <option value="">-- Select --</option>
                            <option value="Y">Yes — Resident</option>
                            <option value="N">No — Non-Resident</option>
                        </select>
                        @error('resident')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            {{-- Local tax --}}
            <section class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
                <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-200 pb-2 mb-4">Local Tax</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Local Withholding Allowance</label>
                        <input type="number" name="local_withholding_allowance" step="0.01" min="0"
                            x-model="currentTax.local_withholding_allowance"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                        @error('local_withholding_allowance')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Locality</label>
                        <input type="text" name="locality" maxlength="100" x-model="currentTax.locality"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                        @error('locality')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">County</label>
                        <input type="text" name="county" maxlength="100" x-model="currentTax.county"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                        @error('county')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            {{-- Additional withholding --}}
            <section class="rounded-lg border border-slate-200 bg-white p-4">
                <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-200 pb-2 mb-4">Additional Withholding</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-md border border-slate-100 bg-slate-50/80 p-3">
                        <p class="text-xs font-medium text-slate-600 mb-3 uppercase tracking-wide">Allowance 1</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Percentage</label>
                                <input type="number" name="addl_withholding_percentage1" step="0.01" min="0" max="100"
                                    x-model="currentTax.addl_withholding_percentage1"
                                    class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Amount</label>
                                <input type="number" name="addl_withholding_amount1" step="0.01" min="0"
                                    x-model="currentTax.addl_withholding_amount1"
                                    class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                            </div>
                        </div>
                    </div>
                    <div class="rounded-md border border-slate-100 bg-slate-50/80 p-3">
                        <p class="text-xs font-medium text-slate-600 mb-3 uppercase tracking-wide">Allowance 2</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Percentage</label>
                                <input type="number" name="addl_withholding_percentage2" step="0.01" min="0" max="100"
                                    x-model="currentTax.addl_withholding_percentage2"
                                    class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Amount</label>
                                <input type="number" name="addl_withholding_amount2" step="0.01" min="0"
                                    x-model="currentTax.addl_withholding_amount2"
                                    class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex justify-between pt-2">
                <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 cursor-pointer">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer">
                    Save Tax Data
                </button>
            </div>
        </div>
    </form>
    @endif

    @include('admin.facilities.employee.employee-tax-data-table')
</div>
