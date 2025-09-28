@extends('layouts.default-template')

@section('title', 'Notice of Privacy Practices - ' . ($facility['name'] ?? 'Bio-Pacific'))

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Header Section -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Notice of Privacy Practices</h1>
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
                    <h2 class="text-xl font-bold text-blue-900 mb-2">Your Health Information Rights</h2>
                    <p class="text-blue-800 text-sm">
                        This notice describes how medical information about you may be used and disclosed and how you
                        can get access to this information. Please review it carefully.
                    </p>
                </div>

                <h2 class="text-2xl font-bold text-slate-900 mb-6">Our Commitment to Your Privacy</h2>

                <p class="text-slate-700 mb-6">
                    {{ $facility['name'] ?? 'Bio-Pacific' }} is required by law to maintain the privacy of your health
                    information and to provide you with this notice of our legal duties and privacy practices with
                    respect to your health information. We are also required to abide by the terms of the notice
                    currently in effect.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">How We May Use and Disclose Your Health
                    Information</h3>

                <h4 class="text-lg font-semibold text-slate-900 mb-3">For Treatment</h4>
                <p class="text-slate-700 mb-4">
                    We may use your health information to provide you with medical treatment or services. We may
                    disclose health information about you to doctors, nurses, technicians, or other personnel who are
                    involved in taking care of you.
                </p>

                <h4 class="text-lg font-semibold text-slate-900 mb-3">For Payment</h4>
                <p class="text-slate-700 mb-4">
                    We may use and disclose your health information to obtain payment for services we provide to you.
                    For example, we may give your health plan information about you so that they will pay for your
                    treatment.
                </p>

                <h4 class="text-lg font-semibold text-slate-900 mb-3">For Health Care Operations</h4>
                <p class="text-slate-700 mb-6">
                    We may use and disclose your health information for health care operations. These activities include
                    quality assessment and improvement activities, reviewing the competence or qualifications of health
                    care professionals, and conducting training programs.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Other Uses and Disclosures</h3>
                <p class="text-slate-700 mb-4">We may also use or disclose your health information in the following
                    situations:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li><strong>As Required by Law:</strong> We will disclose health information about you when required
                        to do so by federal, state, or local law.</li>
                    <li><strong>Public Health:</strong> We may disclose health information about you for public health
                        activities, such as preventing or controlling disease, injury, or disability.</li>
                    <li><strong>Health Oversight Activities:</strong> We may disclose health information to a health
                        oversight agency for activities authorized by law.</li>
                    <li><strong>Lawsuits and Disputes:</strong> If you are involved in a lawsuit or a dispute, we may
                        disclose health information about you in response to a court or administrative order.</li>
                    <li><strong>Law Enforcement:</strong> We may release health information if asked to do so by a law
                        enforcement official in certain circumstances.</li>
                    <li><strong>Emergency Situations:</strong> We may use or disclose your health information in
                        emergency treatment situations.</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Your Individual Rights</h3>
                <p class="text-slate-700 mb-4">You have the following rights regarding your health information:</p>

                <h4 class="text-lg font-semibold text-slate-900 mb-3">Right to Inspect and Copy</h4>
                <p class="text-slate-700 mb-4">
                    You have the right to inspect and copy your health information. To inspect and copy your health
                    information, you must submit your request in writing. We may charge a fee for the costs of copying,
                    mailing, or other supplies associated with your request.
                </p>

                <h4 class="text-lg font-semibold text-slate-900 mb-3">Right to Amend</h4>
                <p class="text-slate-700 mb-4">
                    If you feel that health information we have about you is incorrect or incomplete, you may ask us to
                    amend the information. You have the right to request an amendment for as long as the information is
                    kept by or for our facility.
                </p>

                <h4 class="text-lg font-semibold text-slate-900 mb-3">Right to an Accounting of Disclosures</h4>
                <p class="text-slate-700 mb-4">
                    You have the right to request an accounting of disclosures of your health information made by us for
                    certain purposes for six years prior to the date you request the accounting.
                </p>

                <h4 class="text-lg font-semibold text-slate-900 mb-3">Right to Request Restrictions</h4>
                <p class="text-slate-700 mb-4">
                    You have the right to request a restriction or limitation on the health information we use or
                    disclose about you for treatment, payment, or health care operations. We are not required to agree
                    to your request.
                </p>

                <h4 class="text-lg font-semibold text-slate-900 mb-3">Right to Request Confidential Communications</h4>
                <p class="text-slate-700 mb-6">
                    You have the right to request that we communicate with you about medical matters in a certain way or
                    at a certain location. We will accommodate all reasonable requests.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Complaints</h3>
                <p class="text-slate-700 mb-4">
                    If you believe your privacy rights have been violated, you may file a complaint with our facility or
                    with the Secretary of the Department of Health and Human Services. To file a complaint with our
                    facility, contact:
                </p>

                <div class="bg-slate-50 rounded-xl p-6 mb-6">
                    <p class="text-slate-700"><strong>Privacy Officer</strong></p>
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

                <p class="text-slate-700 mb-6">
                    You will not be penalized for filing a complaint. All complaints must be submitted in writing.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Changes to This Notice</h3>
                <p class="text-slate-700 mb-6">
                    We reserve the right to change this notice. We reserve the right to make the revised or changed
                    notice effective for health information we already have about you as well as any information we
                    receive in the future. We will post a copy of the current notice in our facility and on our website.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Effective Date</h3>
                <p class="text-slate-700 mb-6">
                    This notice is effective as of {{ date('F j, Y') }}.
                </p>

                <div class="bg-amber-50 rounded-xl p-6 mt-8">
                    <p class="text-amber-800 text-sm">
                        <strong>Important:</strong> This Notice of Privacy Practices is required by HIPAA and describes
                        how your medical information may be used and disclosed. For questions about your privacy rights
                        or this notice, please contact our Privacy Officer.
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