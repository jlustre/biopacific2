{{-- Reusable Contact Form Component (Livewire Version) --}}
@livewire('contact-form', [
'facility' => $facility,
'primary' => $primary ?? '#0EA5E9',
'secondary' => $secondary ?? '#1E293B',
'accent' => $accent ?? '#F59E0B',
'neutral_dark' => $neutral_dark ?? '#1e293b'
])