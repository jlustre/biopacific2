<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="relative text-gray-600 hover:text-primary focus:outline-none">
        <i class="fas fa-bell fa-lg"></i>
        @if($unreadCount > 0)
        <span
            class="absolute -top-1 -right-2 inline-flex items-center justify-center px-1 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">{{$unreadCount}}</span>
        @endif
    </button>
    <div x-show="open" @click.away="open = false"
        class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded shadow-lg z-50"
        style="display: none;">
        <div class="p-4 border-b font-semibold text-slate-700">Webmaster Messages</div>
        <ul class="max-h-72 overflow-y-auto divide-y divide-slate-100">
            @forelse($latestContacts as $contact)
            <li class="p-4 flex flex-col gap-1 {{ !$contact->is_read ? 'bg-blue-50' : '' }}">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-slate-800 text-sm">{{ $contact->subject }}</span>
                    @if($contact->urgent)
                    <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-semibold">Urgent</span>
                    @endif
                </div>
                <div class="text-xs text-slate-500">From: {{ $contact->name }} &bull; {{
                    $contact->created_at->diffForHumans() }}</div>
                <a href="{{ route('admin.webmaster.contacts.show', $contact) }}"
                    class="text-blue-600 hover:underline text-xs mt-1">View Message</a>
            </li>
            @empty
            <li class="p-4 text-slate-400 text-sm text-center">No recent messages.</li>
            @endforelse
        </ul>
        <div class="p-2 text-center border-t">
            <a href="{{ route('admin.webmaster.contacts.index') }}" class="text-blue-600 hover:underline text-xs">View
                all messages</a>
        </div>
    </div>
</div>