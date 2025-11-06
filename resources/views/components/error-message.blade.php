@props([
'dismissible' => true,
'icon' => 'fas fa-exclamation-circle',
'title' => null
])

<x-alert-message type="error" :dismissible="$dismissible" :icon="$icon" :title="$title" {{ $attributes }}>
    {{ $slot }}
</x-alert-message>