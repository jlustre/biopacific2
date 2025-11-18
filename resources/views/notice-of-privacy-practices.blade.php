@extends('layouts.default-template')

@section('title', 'Notice of Privacy Practices - ' . ($facility['name'] ?? 'Bio-Pacific'))

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Header Section -->
    @include('components.legal-header', ['legal_title' => 'Notice of Privacy Practices', 'facility' => $facility])

    <!-- Content Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-8 md:p-12">
            <div class="prose prose-lg max-w-none">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Notice of Privacy Practices</h2>
                <p class="text-slate-700 mb-6">
                    This Notice describes how medical information about you may be used and disclosed and how you can
                    get access to this information. Please review it carefully.
                </p>
                <h3 class="text-xl font-semibold text-slate-900 mb-4">Your Rights</h3>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li>Get a copy of your paper or electronic medical record</li>
                    <li>Correct your medical record</li>
                    <li>Request confidential communication</li>
                    <li>Ask us to limit the information we share</li>
                    <li>Get a list of those with whom we've shared information</li>
                    <li>Get a copy of this privacy notice</li>
                    <li>Choose someone to act for you</li>
                    <li>File a complaint if you believe your rights are violated</li>
                </ul>
                <h3 class="text-xl font-semibold text-slate-900 mb-4">Our Uses and Disclosures</h3>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li>Treat you</li>
                    <li>Run our organization</li>
                    <li>Bill for your services</li>
                    <li>Help with public health and safety issues</li>
                    <li>Comply with the law</li>
                    <li>Respond to lawsuits and legal actions</li>
                </ul>
                <h3 class="text-xl font-semibold text-slate-900 mb-4">Our Responsibilities</h3>
                <p class="text-slate-700 mb-6">
                    We are required by law to maintain the privacy and security of your protected health information. We
                    will let you know promptly if a breach occurs that may have compromised the privacy or security of
                    your information.
                </p>
                <h3 class="text-xl font-semibold text-slate-900 mb-4">Contact Information</h3>
                <div class="bg-slate-50 rounded-xl p-6 mb-6">
                    <p class="text-slate-700"><strong>{{ $facility['name'] ?? 'Bio-Pacific' }}</strong></p>
                    @if(!empty($facility['address']))
                    <p class="text-slate-700">{{ $facility['address'] }}</p>
                    @endif
                    @if(!empty($facility['city']) || !empty($facility['state']) || !empty($facility['zip']))
                    <p class="text-slate-700">
                        {{ $facility['city'] ?? '' }}@if(!empty($facility['city']) && (!empty($facility['state']) ||
                        !empty($facility['zip']))), @endif
                        {{ $facility['state'] ?? '' }}@if(!empty($facility['state']) && !empty($facility['zip'])) @endif
                        {{ $facility['zip'] ?? '' }}
                    </p>
                    @endif
                    @if(!empty($facility['phone']))
                    <p class="text-slate-700">Phone: {{ $facility['phone'] }}</p>
                    @endif
                    @if(!empty($facility['email']))
                    <p class="text-slate-700">Email: {{ $facility['email'] }}</p>
                    @endif
                </div>
            </div>
        </div>
        <!-- Back to Home -->
        <div class="text-center mt-8">
            <a href="{{ route('admin.dashboard.facility', $facility['id']) }}"
                class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to <span class="text-teal-600">{{ $facility['name'] ?? 'Home' }}</span>
            </a>
        </div>
    </div>
</div>
@endsection