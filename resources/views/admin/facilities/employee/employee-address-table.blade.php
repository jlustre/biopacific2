<div class="bg-white shadow rounded-lg p-4 mt-8">
    <h2 class="text-lg font-bold mb-4">Address History</h2>
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff. Date</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff. Seq</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Address 1</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Address 2</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">State</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ZIP</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Country</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Is Primary</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody>
            @if($employee->addresses)
            @foreach($employee->addresses->sortBy([['effdt', 'desc'], ['effseq', 'desc']]) as $addr)
            <tr class="border-b">
                <td class="px-3 py-2 text-xs">{{ $addr->effdt }}</td>
                <td class="px-3 py-2 text-sm">{{ $addr->effseq }}</td>
                <td class="px-3 py-2 text-sm">
                    @if($addr->address_type == 'h') Home @elseif($addr->address_type == 'w') Work
                    @elseif($addr->address_type == 'o') Other @else {{ $addr->address_type }} @endif
                </td>
                <td class="px-3 py-2 text-xs">{{ $addr->address1 }}</td>
                <td class="px-3 py-2 text-xs">{{ $addr->address2 }}</td>
                <td class="px-3 py-2 text-xs">{{ $addr->city }}</td>
                <td class="px-3 py-2 text-sm">{{ $addr->state }}</td>
                <td class="px-3 py-2 text-sm">{{ $addr->zip }}</td>
                <td class="px-3 py-2 text-sm">{{ $addr->country }}</td>
                <td class="px-3 py-2 text-sm">@if($addr->is_primary) Yes @else No @endif</td>
                <td class="px-3 py-2 text-sm">
                    <a href="#" class="text-blue-600 hover:underline" @click.prevent="setAddress({
                                address1: '{{ $addr->address1 }}',
                                address2: '{{ $addr->address2 }}',
                                city: '{{ $addr->city }}',
                                state: '{{ $addr->state }}',
                                zip: '{{ $addr->zip }}',
                                country: '{{ $addr->country }}',
                                is_primary: '{{ $addr->is_primary ? '1' : '0' }}',
                                address_type: '{{ $addr->address_type }}',
                                effdt: '{{ $addr->effdt }}',
                                effseq: '{{ $addr->effseq }}'
                            })">View/Edit</a>
                </td>
            </tr>
            @endforeach
            @else
            <tr>
                <td colspan="10" class="text-center text-gray-500 py-4">No address records found.</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>