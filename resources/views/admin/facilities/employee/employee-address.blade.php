<div x-show="tab === 'address'">
    @php
    $latestAddr = $employee->addresses ? $employee->addresses->sortBy([['effdt', 'desc'], ['effseq', 'desc']])->first()
    :
    null;
    $latestAddrEffdt = $latestAddr->effdt ?? '';
    $latestAddrEffseq = $latestAddr->effseq ?? '';
    $homeAddress = \App\Models\BPEmpAddress::where('emp_id', $employee->emp_id)
    ->where('address_type', 'h')
    ->orderByDesc('effdt')
    ->orderByDesc('effseq')
    ->first();
    @endphp
    <div x-data="addressForm()" x-init="initAddress()">
        <div class="flex justify-end items-center mb-4 space-x-4">
            <button type="button" @click="clearAddress()"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Add New Address
            </button>
            <template x-if="isLatestRecord()">
                <span class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 rounded text-sm font-semibold">Latest
                    Record</span>
            </template>
        </div>
        <form method="POST" action="{{ route('admin.employees.address.update', $employee->emp_id) }}">
            @csrf
            @method('PUT')
            <div class="bg-white shadow rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="mb-2 lg:col-span-2">
                        <label class="block text-sm font-medium mb-1">Address 1</label>
                        <input type="text" name="address1" x-model="currentAddress.address1"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                    </div>
                    <div class="mb-2 lg:col-span-2">
                        <label class="block text-sm font-medium mb-1">Address 2</label>
                        <input type="text" name="address2" x-model="currentAddress.address2"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-medium mb-1">City</label>
                        <input type="text" name="city" x-model="currentAddress.city"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-medium mb-1">State</label>
                        <input type="text" name="state" x-model="currentAddress.state"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-medium mb-1">ZIP</label>
                        <input type="text" name="zip" x-model="currentAddress.zip"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-medium mb-1">Country</label>
                        <input type="text" name="country" x-model="currentAddress.country"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-medium mb-1">Is Primary</label>
                        <select name="is_primary" class="form-select w-full border border-teal-300 rounded-lg px-2 py-1"
                            x-model="currentAddress.is_primary">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-medium mb-1">Type</label>
                        <select name="address_type"
                            class="form-select w-full border border-teal-300 rounded-lg px-2 py-1"
                            x-model="currentAddress.address_type">
                            <option value="h">Home</option>
                            <option value="w">Work</option>
                            <option value="o">Other</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-medium mb-1">Effective Date</label>
                        <input type="date" name="effdt" x-model="currentAddress.effdt"
                            :min="(new Date()).toISOString().split('T')[0]"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-medium mb-1">Effective Seq</label>
                        <input type="number" name="effseq" x-model="currentAddress.effseq" readonly
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1 bg-gray-100 cursor-not-allowed">
                    </div>
                </div>
                <div class="flex justify-between mt-6 md:col-span-2 lg:col-span-4">
                    <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save
                        Address</button>
                </div>
            </div>
        </form>
        <!-- Address History Table -->
        @include('admin.facilities.employee.employee-address-table')
        <script>
            function addressForm() {
                    return {
                        tab: 'address',
                        showAddAddress: false,
                        currentAddress: {
                            address1: @json(old('address1', $latestAddr->address1 ?? '')),
                            address2: @json(old('address2', $latestAddr->address2 ?? '')),
                            city: @json(old('city', $latestAddr->city ?? '')),
                            state: @json(old('state', $latestAddr->state ?? '')),
                            zip: @json(old('zip', $latestAddr->zip ?? '')),
                            country: @json(old('country', $latestAddr->country ?? '')),
                            is_primary: @json(old('is_primary', isset($latestAddr) ? ($latestAddr->is_primary ? '1' : '0') : '0')),
                            address_type: @json(old('address_type', $latestAddr->address_type ?? 'h')),
                            effdt: @json(old('effdt', $latestAddr->effdt ?? '')),
                            effseq: @json(old('effseq', $latestAddr->effseq ?? '')),
                        },
                        latestEffdt: @json($latestAddrEffdt),
                        latestEffseq: @json($latestAddrEffseq),
                        setAddress(addr) {
                            this.currentAddress = Object.assign({address1: '', address2: '', city: '', state: '', zip: '', country: '', is_primary: '0', address_type: 'h', effdt: '', effseq: ''}, addr);
                            this.showAddAddress = false;
                        },
                        clearAddress() {
                            this.currentAddress = {address1: '', address2: '', city: '', state: '', zip: '', country: '', is_primary: '0', address_type: 'h', effdt: '', effseq: ''};
                            this.showAddAddress = true;
                        },
                        isLatestRecord() {
                            return this.currentAddress.effdt == this.latestEffdt && String(this.currentAddress.effseq) == String(this.latestEffseq);
                        },
                        initAddress() {
                            // Optionally set initial address if needed
                        }
                    }
                }
        </script>
    </div>
</div>