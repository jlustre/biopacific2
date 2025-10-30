@props(['type', 'message'])

<div class="p-4 mb-4 rounded-lg {{ $type === 'success' ? 'text-green-800 bg-green-200' : 'text-red-800 bg-red-200' }}">
    {{ $message }}
</div>