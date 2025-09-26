@extends('layouts.default-template')

@section('title', 'Terms of Service - ' . ($facility['name'] ?? 'Bio-Pacific'))

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Header Section -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Terms of Service</h1>
                <p class="text-lg text-slate-600">{{ $facility['name'] ?? 'Bio-Pacific' }}</p>
                <p class="text-sm text-slate-500 mt-2">Last updated: {{ date('F j, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-8 md:p-12">

            <div class="prose prose-lg max-w-none">
                <div class="bg-blue-50 rounded-xl p-6 mb-8">
                    <h2 class="text-xl font-bold text-blue-900 mb-2">Agreement to Terms</h2>
                    <p class="text-blue-800 text-sm">
                        By accessing and using our website and services, you agree to be bound by these Terms of
                        Service. Please read them carefully.
                    </p>
                </div>

                <h2 class="text-2xl font-bold text-slate-900 mb-6">Terms and Conditions</h2>

                <p class="text-slate-700 mb-6">
                    Welcome to {{ $facility['name'] ?? 'Bio-Pacific' }}. These terms and conditions outline the rules
                    and regulations for the use of our website and healthcare services.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Acceptance of Terms</h3>
                <p class="text-slate-700 mb-6">
                    By accessing this website and using our services, you accept these terms and conditions in full. If
                    you disagree with these terms and conditions or any part of these terms and conditions, you must not
                    use our website or services.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Healthcare Services</h3>
                <p class="text-slate-700 mb-4">Our healthcare services are provided subject to the following terms:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li>Services are provided by licensed healthcare professionals</li>
                    <li>All medical decisions are made in accordance with professional standards</li>
                    <li>Emergency situations will be handled according to established protocols</li>
                    <li>Patient care plans are developed individually based on medical assessment</li>
                    <li>Family involvement in care decisions is encouraged where appropriate</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Website Use</h3>
                <p class="text-slate-700 mb-4">You may use our website for the following purposes:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li>Accessing information about our facility and services</li>
                    <li>Contacting us for inquiries or appointments</li>
                    <li>Viewing educational content related to healthcare</li>
                    <li>Completing forms and applications as required</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Prohibited Uses</h3>
                <p class="text-slate-700 mb-4">You must not use our website or services for any of the following:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li>Any unlawful purpose or to solicit others to unlawful acts</li>
                    <li>To violate any international, federal, provincial, or state regulations, rules, laws, or local
                        ordinances</li>
                    <li>To infringe upon or violate our intellectual property rights or the intellectual property rights
                        of others</li>
                    <li>To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
                    <li>To submit false or misleading information</li>
                    <li>To interfere with or circumvent the security features of the service</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Patient Rights and Responsibilities</h3>
                <p class="text-slate-700 mb-4">As a healthcare facility, we recognize and uphold patient rights
                    including:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li>Right to quality care regardless of race, religion, or ability to pay</li>
                    <li>Right to privacy and confidentiality of medical information</li>
                    <li>Right to participate in care decisions</li>
                    <li>Right to voice complaints without fear of retaliation</li>
                    <li>Right to access medical records as permitted by law</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Intellectual Property</h3>
                <p class="text-slate-700 mb-6">
                    The service and its original content, features, and functionality are and will remain the exclusive
                    property of {{ $facility['name'] ?? 'Bio-Pacific' }} and its licensors. The service is protected by
                    copyright, trademark, and other laws.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Privacy and Confidentiality</h3>
                <p class="text-slate-700 mb-6">
                    Your privacy is critically important to us. We comply with all applicable healthcare privacy laws
                    including HIPAA. Please refer to our Privacy Policy and Notice of Privacy Practices for detailed
                    information about how we collect, use, and protect your information.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Limitation of Liability</h3>
                <p class="text-slate-700 mb-6">
                    While we strive to provide the highest quality healthcare services, we cannot guarantee specific
                    outcomes. Our liability is limited to the extent permitted by law, and we encourage all patients and
                    families to actively participate in care decisions.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Indemnification</h3>
                <p class="text-slate-700 mb-6">
                    You agree to defend, indemnify, and hold us harmless from and against any and all claims, damages,
                    obligations, losses, liabilities, costs or debt, and expenses (including but not limited to
                    attorney's fees).
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Termination</h3>
                <p class="text-slate-700 mb-6">
                    We may terminate or suspend your access to our services immediately, without prior notice or
                    liability, for any reason whatsoever, including without limitation if you breach the Terms.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Changes to Terms</h3>
                <p class="text-slate-700 mb-6">
                    We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a
                    revision is material, we will try to provide at least 30 days notice prior to any new terms taking
                    effect.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Contact Information</h3>
                <p class="text-slate-700 mb-4">
                    If you have any questions about these Terms of Service, please contact us:
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
                    <p class="text-slate-700">Phone: {{ $facility['phone'] }}</p>
                    @endif
                    @if(!empty($facility['email']))
                    <p class="text-slate-700">Email: {{ $facility['email'] }}</p>
                    @endif
                </div>

                <div class="bg-green-50 rounded-xl p-6 mt-8">
                    <p class="text-green-800 text-sm">
                        <strong>Note:</strong> These terms of service are designed for healthcare facilities. Please
                        consult with legal counsel to ensure compliance with applicable laws and regulations specific to
                        your facility and location.
                    </p>
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
                Back to {{ $facility['name'] ?? 'Home' }}
            </a>
        </div>
    </div>
</div>
@endsection