@extends('layouts.default-template')

@section('title', 'Privacy Policy - ' . ($facility['name'] ?? 'Bio-Pacific'))

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Header Section -->
    @include('components.legal-header', ['legal_title' => 'Privacy Policy', 'facility' => $facility])

    <!-- Content Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-8 md:p-12">

            <div class="prose prose-lg max-w-none">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Our Commitment to Your Privacy</h2>

                <p class="text-slate-700 mb-6">
                    At {{ $facility['name'] ?? 'Bio-Pacific' }}, we are committed to protecting your privacy and
                    maintaining the confidentiality of your personal information. This Privacy Policy explains how we
                    collect, use, and protect your information when you visit our website or use our services.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Information We Collect</h3>
                <p class="text-slate-700 mb-4">We may collect the following types of information:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li>Personal identification information (name, email address, phone number)</li>
                    <li>Contact preferences and communication history</li>
                    <li>Website usage data and analytics</li>
                    <li>Information provided through contact forms or inquiries</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">How We Use Your Information</h3>
                <p class="text-slate-700 mb-4">We use collected information for the following purposes:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li>To provide and maintain our services</li>
                    <li>To communicate with you about our facilities and services</li>
                    <li>To respond to your inquiries and support requests</li>
                    <li>To improve our website and user experience</li>
                    <li>To comply with legal obligations and regulations</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Information Protection</h3>
                <p class="text-slate-700 mb-6">
                    We implement appropriate security measures to protect your personal information against unauthorized
                    access, alteration, disclosure, or destruction. We maintain physical, electronic, and procedural
                    safeguards that comply with federal regulations.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">HIPAA Compliance</h3>
                <p class="text-slate-700 mb-6">
                    As a healthcare facility, we comply with the Health Insurance Portability and Accountability Act
                    (HIPAA) and maintain strict confidentiality of all protected health information (PHI). Any
                    health-related information is handled according to HIPAA privacy and security rules.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Cookies and Tracking</h3>
                <p class="text-slate-700 mb-6">
                    Our website may use cookies and similar tracking technologies to enhance your browsing experience.
                    You can choose to disable cookies through your browser settings, though this may affect website
                    functionality.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Third-Party Services</h3>
                <p class="text-slate-700 mb-6">
                    We may use third-party services for analytics, communication, and other business purposes. These
                    services have their own privacy policies, and we encourage you to review them.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Your Rights</h3>
                <p class="text-slate-700 mb-4">You have the right to:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li>Request access to your personal information</li>
                    <li>Request correction of inaccurate information</li>
                    <li>Request deletion of your personal information</li>
                    <li>Opt-out of marketing communications</li>
                    <li>File a complaint with regulatory authorities</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Contact Information</h3>
                <p class="text-slate-700 mb-4">
                    If you have questions about this Privacy Policy or wish to exercise your privacy rights, please
                    contact us:
                </p>
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
                    <p class="text-slate-700">Phone: {{ $facility['phone'] ? '(' . substr($facility['phone'],0,3) . ') '
                        . substr($facility['phone'],3,3) . '-' . substr($facility['phone'],6,4) : 'N/A' }}</p>
                    @endif
                    @if(!empty($facility['email']))
                    <p class="text-slate-700">Email: {{ $facility['email'] }}</p>
                    @endif
                </div>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Changes to This Policy</h3>
                <p class="text-slate-700 mb-6">
                    We may update this Privacy Policy from time to time. Any changes will be posted on this page with an
                    updated revision date. We encourage you to review this policy periodically.
                </p>
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