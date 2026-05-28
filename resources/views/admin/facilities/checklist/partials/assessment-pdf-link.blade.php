@props([
    'href',
    'title' => 'View PDF',
    'ariaLabel' => null,
    'class' => 'relative inline-flex h-7 w-7 items-center justify-center rounded border border-slate-400 bg-white text-red-700 hover:bg-red-50',
])
<a
    href="{{ $href }}"
    data-assessment-pdf-link
    class="{{ $class }}"
    title="{{ $title }}"
    aria-label="{{ $ariaLabel ?? $title }}"
    role="button"
>
    <span data-pdf-icon class="inline-flex items-center justify-center" aria-hidden="true">
        <i class="fas fa-file-pdf text-sm"></i>
    </span>
    <span data-pdf-loader class="absolute inset-0 hidden items-center justify-center" aria-hidden="true">
        <i class="fas fa-spinner fa-spin text-sm text-slate-600"></i>
    </span>
</a>
