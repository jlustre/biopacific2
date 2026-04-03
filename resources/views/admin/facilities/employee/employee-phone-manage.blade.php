
<!-- Debug: Show employee ID if available -->
@if(!empty($employee) && !empty($employee->emp_id))
    <span style="display:none" id="debug-empid">{{ $employee->emp_id }}</span>
@endif
<div x-show="showPhoneModal" style="display: none;" class="fixed inset-0 flex items-center justify-center z-50">
    <div class="fixed inset-0 bg-black opacity-40" @click="showPhoneModal = false"></div>
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl z-10">
        <h3 class="text-lg font-bold mb-4">Manage Phones</h3>

        @if(!empty($employee) && !empty($employee->emp_id))
            <!-- Add Phone Form -->
            <form x-show="addPhone" method="POST" action="{{ route('admin.employees.phones.add', $employee->emp_id) }}" class="mb-4">
                @csrf
                <div class="flex gap-2 mb-2">
                    <select name="phone_type" class="form-select border rounded px-2 py-1" required>
                        <option value="">Type</option>
                        <option value="M">Mobile</option>
                        <option value="H">Home</option>
                        <option value="W">Work</option>
                    </select>
                    <input type="text" name="phone_number" class="form-input border rounded px-2 py-1" placeholder="Phone Number" required>
                    <label class="flex items-center text-xs ml-2">
                        <input type="checkbox" name="is_primary" class="mr-1"> Primary
                    </label>
                    <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">Save</button>
                    <button type="button" @click="addPhone = false" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 ml-2">Cancel</button>
                </div>
            </form>

            <!-- Edit Phone Form -->
            <template x-if="phoneAction === 'edit' && editPhone">
                <form method="POST"
                    :action="editPhone ? `{{ url('admin/employees') }}/{{ $employee->emp_id }}/phones/${editPhone.phone_id}/update` : ''"
                    class="mb-4">
                    @csrf
                    @method('PUT')
                    <div class="flex gap-2 mb-2">
                        <select name="phone_type" class="form-select border rounded px-2 py-1" x-model="editPhone.phone_type" required>
                            <option value="">Type</option>
                            <option value="M">Mobile</option>
                            <option value="H">Home</option>
                            <option value="W">Work</option>
                        </select>
                        <input type="text" name="phone_number" class="form-input border rounded px-2 py-1" x-model="editPhone.phone_number" required>
                        <label class="flex items-center text-xs ml-2">
                            <input type="checkbox" name="is_primary" x-model="editPhone.is_primary" class="mr-1"> Primary
                        </label>
                        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
                        <button type="button" @click="phoneAction = ''; editPhone = null" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 ml-2">Cancel</button>
                    </div>
                </form>
            </template>

            <!-- Delete Phone Confirmation -->
            <form x-show="phoneAction === 'delete' && deletePhoneId" method="POST"
                :action="`{{ url('admin/employees/phones/delete') }}/${deletePhoneId}`" class="mb-4">
                @csrf
                @method('DELETE')
                <div class="flex items-center gap-2 mb-2">
                    <span>Are you sure you want to delete this phone?</span>
                    <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                    <button type="button" @click="phoneAction = ''; deletePhoneId = null" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 ml-2">Cancel</button>
                </div>
            </form>

            <!-- Phone Table -->
            <table class="min-w-full divide-y divide-gray-200 mb-4">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Number</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Primary</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($employee->phones) && count($employee->phones))
                        @foreach($employee->phones as $phone)
                        <tr>
                            <td class="px-3 py-2">{{ $phone->phone_type }}</td>
                            <td class="px-3 py-2">{{ $phone->phone_number }}</td>
                            <td class="px-3 py-2">@if($phone->is_primary) Yes @else No @endif</td>
                            <td class="px-3 py-2">
                                <button type="button" class="text-blue-600 hover:underline mr-2"
                                    @click="addPhone = false; phoneAction = 'edit'; editPhone = { phone_id: {{ $phone->phone_id }}, phone_type: '{{ $phone->phone_type }}', phone_number: '{{ $phone->phone_number }}', is_primary: {{ $phone->is_primary ? 'true' : 'false' }} }">Edit</button>
                                <button type="button" class="text-red-600 hover:underline"
                                    @click="phoneAction = 'delete'; deletePhoneId = {{ $phone->phone_id }}">Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="4" class="text-center text-gray-400">No phones found.</td></tr>
                    @endif
                </tbody>
            </table>
            <button type="button" @click="addPhone = true; phoneAction = ''; editPhone = null; deletePhoneId = null" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 mb-4">Add Phone</button>
        @else
            <div class="text-gray-500 mb-4">You must save the employee before adding phone numbers.</div>
        @endif

        <div class="mt-6 text-right">
            <button @click="showPhoneModal = false" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Close</button>
        </div>
    </div>
</div>