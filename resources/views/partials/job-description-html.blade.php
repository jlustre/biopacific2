@props(['content' => ''])

@php
    $html = $content ?? '';
    if ($html && ! str_contains($html, '<') && str_contains($html, '&lt;')) {
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
@endphp

@once
<style>
    .job-description-content h1,
    .job-description-content h2,
    .job-description-content h3,
    .job-description-content h4,
    .job-description-content h5,
    .job-description-content h6 {
        font-weight: bold;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .job-description-content h1 { font-size: 1.875rem; }
    .job-description-content h2 { font-size: 1.5rem; }
    .job-description-content h3 { font-size: 1.25rem; }

    .job-description-content p,
    .job-description-content .MsoNormal {
        margin-bottom: 0.75rem;
        line-height: 1.6;
    }

    .job-description-content ul {
        list-style-type: disc;
        margin-left: 1.5rem;
        margin-bottom: 0.75rem;
        padding-left: 0.5rem;
    }

    .job-description-content ol {
        list-style-type: decimal;
        margin-left: 1.5rem;
        margin-bottom: 0.75rem;
        padding-left: 0.5rem;
    }

    .job-description-content li {
        margin-bottom: 0.25rem;
    }

    .job-description-content strong,
    .job-description-content b {
        font-weight: 700;
    }

    .job-description-content em,
    .job-description-content i {
        font-style: italic;
    }

    .job-description-content u {
        text-decoration: underline;
    }

    .job-description-content a {
        color: #0d9488;
        text-decoration: underline;
    }

    .job-description-content table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0.75rem;
    }

    .job-description-content table td,
    .job-description-content table th {
        border: 1px solid #d1d5db;
        padding: 0.5rem;
    }
</style>
@endonce

@if(trim(strip_tags($html)) !== '')
<div {{ $attributes->merge(['class' => 'job-description-content prose prose-sm max-w-none bg-gray-50 p-4 rounded border text-gray-700']) }}>
    {!! $html !!}
</div>
@else
<p class="text-slate-500 italic">No description provided.</p>
@endif
