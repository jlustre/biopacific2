@props([
'dismissible' => true,
'icon' => 'fas fa-check-circle',
'title' => null
])

<x-alert-message type="success" :dismissible="$dismissible" :icon="$icon" :title="$title" {{ $attributes }}>
    {{ $slot }}
</x-alert-message>