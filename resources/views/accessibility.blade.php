@extends('layouts.default-template')

@section('title', 'Accessibility - ' . ($facility['name'] ?? 'Bio-Pacific'))

@section('page')
<div class="min-h-screen bg-slate-50">
    <!-- Header Section -->
    @include('components.legal-header', ['legal_title' => 'Accessibility Statement', 'facility' => $facility])

    <!-- Content Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-8 md:p-12">

            <div class="prose prose-lg max-w-none">
                <div class="rounded-xl p-6 mb-8" style="background-color: {{ $accent }}">
                    <h2 class="text-xl font-bold mb-2" style="color: {{ $neutral_dark }}">Our Commitment to
                        Accessibility</h2>
                    <p class="text-slate-600 text-sm">
                        We are committed to ensuring digital accessibility for people with disabilities and providing
                        equal access to our website and healthcare services.
                    </p>
                </div>

                <h2 class="text-2xl font-bold text-slate-900 mb-6">Accessibility Commitment</h2>

                <p class="text-slate-700 mb-6">
                    {{ $facility['name'] ?? 'Bio-Pacific' }} is committed to providing healthcare services and digital
                    experiences that are accessible to all individuals, including those with disabilities. We strive to
                    comply with the Americans with Disabilities Act (ADA) and Web Content Accessibility Guidelines
                    (WCAG) 2.1 Level AA standards.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Website Accessibility Features</h3>
                <p class="text-slate-700 mb-4">Our website includes the following accessibility features:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li><strong>Keyboard Navigation:</strong> All interactive elements can be accessed using keyboard
                        navigation</li>
                    <li><strong>Screen Reader Support:</strong> Content is structured and labeled for screen reader
                        compatibility</li>
                    <li><strong>Alt Text:</strong> Images include descriptive alternative text</li>
                    <li><strong>Color Contrast:</strong> Text and background colors meet WCAG contrast requirements</li>
                    <li><strong>Responsive Design:</strong> Website adapts to different screen sizes and devices</li>
                    <li><strong>Clear Navigation:</strong> Consistent and logical page structure and navigation</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Facility Accessibility</h3>
                <p class="text-slate-700 mb-4">Our healthcare facility provides the following accessibility
                    accommodations:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li><strong>Physical Access:</strong> ADA-compliant entrances, ramps, and elevators</li>
                    <li><strong>Accessible Restrooms:</strong> ADA-compliant restroom facilities throughout the building
                    </li>
                    <li><strong>Accessible Parking:</strong> Designated accessible parking spaces near entrances</li>
                    <li><strong>Wide Hallways:</strong> Corridors designed to accommodate wheelchairs and mobility
                        devices</li>
                    <li><strong>Accessible Patient Rooms:</strong> Rooms equipped for patients with mobility impairments
                    </li>
                    <li><strong>Visual Aids:</strong> Clear signage with high contrast and appropriate font sizes</li>
                    <li><strong>Hearing Assistance:</strong> Assistive listening devices available upon request</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Communication Accessibility</h3>
                <p class="text-slate-700 mb-4">We provide various communication support services:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li><strong>Interpreters:</strong> Sign language interpreters available upon request</li>
                    <li><strong>Language Services:</strong> Translation services for non-English speaking patients</li>
                    <li><strong>Large Print Materials:</strong> Documents available in large print format</li>
                    <li><strong>Braille Materials:</strong> Important documents available in Braille upon request</li>
                    <li><strong>Audio Information:</strong> Verbal explanation of written materials when needed</li>
                    <li><strong>Alternative Formats:</strong> Electronic documents compatible with assistive
                        technologies</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Assistive Technology Support</h3>
                <p class="text-slate-700 mb-6">
                    Our website and digital services are designed to work with commonly used assistive technologies,
                    including screen readers, voice recognition software, and alternative input devices. We regularly
                    test our digital platforms with various assistive technologies to ensure compatibility.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Ongoing Improvements</h3>
                <p class="text-slate-700 mb-6">
                    We are continuously working to improve the accessibility of our website and facilities. This
                    includes regular accessibility audits, staff training, and updates based on user feedback and
                    evolving accessibility standards.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Accessibility Compliance</h3>
                <p class="text-slate-700 mb-4">We strive to comply with:</p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li><strong>Americans with Disabilities Act (ADA):</strong> Federal civil rights law prohibiting
                        discrimination based on disability</li>
                    <li><strong>Section 508:</strong> Federal accessibility requirements for electronic and information
                        technology</li>
                    <li><strong>WCAG 2.1 Level AA:</strong> International accessibility guidelines for web content</li>
                    <li><strong>State and Local Regulations:</strong> Applicable accessibility laws and regulations</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Third-Party Content</h3>
                <p class="text-slate-700 mb-6">
                    Some content on our website may be provided by third-party services. We work with our partners to
                    ensure their content meets accessibility standards, but we cannot guarantee the accessibility of all
                    third-party content.
                </p>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Feedback and Support</h3>
                <p class="text-slate-700 mb-4">
                    We welcome your feedback on the accessibility of our website and services. If you encounter
                    accessibility barriers or need assistance, please contact us:
                </p>
                <div class="bg-slate-50 rounded-xl p-6 mb-6">
                    <p class="text-slate-700"><strong>Accessibility Coordinator</strong></p>
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
                    <p class="text-slate-700 mt-2"><strong>Response Time:</strong> We aim to respond to accessibility
                        inquiries within 2 business days.</p>
                </div>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Alternative Access Methods</h3>
                <p class="text-slate-700 mb-4">
                    If you are unable to access any content or functionality on our website, we offer alternative
                    methods to obtain the same information and services:
                </p>
                <ul class="list-disc pl-6 mb-6 text-slate-700 space-y-2">
                    <li>Phone consultation with our staff</li>
                    <li>In-person assistance at our facility</li>
                    <li>Email communication for non-urgent matters</li>
                    <li>Written materials in accessible formats</li>
                </ul>

                <h3 class="text-xl font-semibold text-slate-900 mb-4">Assessment and Testing</h3>
                <p class="text-slate-700 mb-6">
                    We conduct regular accessibility assessments of our website using both automated tools and manual
                    testing with assistive technologies. We also engage users with disabilities to provide feedback on
                    our accessibility efforts.
                </p>

                <div class="bg-purple-50 rounded-xl p-6 mt-8">
                    <p class="text-purple-800 text-sm">
                        <strong>Commitment:</strong> We are dedicated to ensuring that everyone, regardless of ability,
                        can access our healthcare services and digital resources. Accessibility is an ongoing effort,
                        and we appreciate your patience and feedback as we continue to improve.
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
                Back to <span class="text-teal-600">{{ $facility['name'] ?? 'Home' }}</span>
            </a>
        </div>
    </div>
</div>
@endsection