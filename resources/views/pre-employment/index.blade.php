@if (auth()->check())
@extends('layouts.app')

@section('body')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4 shadow-lg border-2 border-teal-600">
                <img src="{{ asset('images/bplogo.png') }}" alt="Bio-Pacific Logo" class="w-12 h-12">
            </div>
            <h1 class="text-5xl font-bold text-gray-900 mb-2">Pre-Employment Process</h1>
            <p class="text-xl text-gray-600">Welcome, <span class="font-semibold text-blue-600">{{ auth()->user()->name
                    }}</span>!</p>
        </div>

        <!-- Introduction Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-blue-100 p-8 mb-12">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-blue-100 text-blue-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Next Steps</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Congratulations on being selected to move forward in our hiring process! Please complete the
                        following pre-employment requirements to finalize your employment offer.
                    </p>
                </div>
            </div>
        </div>

        <!-- Steps Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
            <!-- Step 1 -->
            <div
                class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200">
                <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex-shrink-0">
                            <div
                                class="flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 text-blue-600 font-bold text-lg">
                                1</div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Background Check</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Provide authorization for us to conduct a comprehensive background check to ensure compliance
                        with legal requirements and organizational standards.
                    </p>
                </div>
            </div>

            <!-- Step 2 -->
            <div
                class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200">
                <div class="h-1 bg-gradient-to-r from-purple-500 to-purple-600"></div>
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex-shrink-0">
                            <div
                                class="flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 text-purple-600 font-bold text-lg">
                                2</div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Medical Clearance</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Complete required medical examinations and health clearance assessments to verify fitness for
                        your position and workplace safety.
                    </p>
                </div>
            </div>

            <!-- Step 3 -->
            <div
                class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200">
                <div class="h-1 bg-gradient-to-r from-green-500 to-green-600"></div>
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex-shrink-0">
                            <div
                                class="flex items-center justify-center h-12 w-12 rounded-full bg-green-100 text-green-600 font-bold text-lg">
                                3</div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Reference Verification</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Provide professional references that will be contacted to verify your employment history,
                        qualifications, and professional conduct.
                    </p>
                </div>
            </div>

            <!-- Step 4 -->
            <div
                class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200">
                <div class="h-1 bg-gradient-to-r from-orange-500 to-orange-600"></div>
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex-shrink-0">
                            <div
                                class="flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 text-orange-600 font-bold text-lg">
                                4</div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Compliance Forms</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Complete all necessary compliance documents and legal paperwork required by our organization and
                        applicable regulations.
                    </p>
                </div>
            </div>
        </div>

        <!-- Important Information -->
        <div
            class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl shadow-md border border-yellow-200 p-8 mb-12">
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Important Information</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-700">All information provided will be kept confidential and
                                secure</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-700">Processing typically takes <strong>5-10 business
                                    days</strong></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-700">You'll receive <strong>email updates</strong> on each step's
                                progress</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-700">Questions? Contact HR at <a href="mailto:hr@biopacific.com"
                                    class="text-blue-600 font-semibold hover:underline">hr@biopacific.com</a></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-between">
            <a href="{{ route('dashboard.index') }}"
                class="flex items-center justify-center gap-2 px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition font-semibold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
            <button onclick="startPreEmployment()"
                class="flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-bold shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Start Pre-Employment Process
            </button>
        </div>
    </div>
</div>

<script>
    function startPreEmployment() {
            alert('Pre-Employment process form coming soon!');
            // TODO: Implement the actual pre-employment form
        }
</script>
@endsection

@else
@extends('layouts.guest')

@section('content')
<div class="py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4 shadow-lg border-2 border-teal-600">
                <img src="{{ asset('images/bplogo.png') }}" alt="Bio-Pacific Logo" class="w-12 h-12">
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Pre-Employment Portal2</h1>
            <p class="text-blue-600 text-lg font-semibold">Complete your onboarding in minutes</p>
        </div>

        <!-- Welcome Message -->
        <div class="bg-blue-50 rounded-xl p-6 mb-8 border-l-4 border-blue-600">
            <h2 class="text-xl font-bold text-gray-900 mb-2">You're Almost There!</h2>
            <p class="text-gray-700 leading-relaxed">
                Congratulations! We're excited to see you move forward in our hiring process. To access your
                pre-employment forms and complete the next steps, please create an account or log in.
            </p>
        </div>

        <!-- Registration Section -->
        <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-8 mb-8 border border-gray-200">
            <h3 class="text-2xl font-bold text-gray-900 mb-2 text-center">Create Your Account</h3>
            <p class="text-gray-600 text-center mb-8">
                Register now to access your secure pre-employment dashboard
            </p>

            <!-- Benefits -->
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Quick Setup</p>
                        <p class="text-sm text-gray-600">2 minutes</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Secure</p>
                        <p class="text-sm text-gray-600">Encrypted</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Instant Access</p>
                        <p class="text-sm text-gray-600">Right away</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m7 8a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Mobile Friendly</p>
                        <p class="text-sm text-gray-600">Any device</p>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <a href="{{ route('register') }}"
                class="block w-full text-center bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-6 rounded-lg transition shadow-lg hover:shadow-xl mb-4">
                Create Account Now
            </a>

            <p class="text-center text-gray-600">
                Already have an account?
                <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:text-blue-700">Sign in instead</a>
            </p>
        </div>

        <!-- Process Steps -->
        <div class="border-t pt-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Pre-Employment Process</h3>
            <div class="space-y-3">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div
                            class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-bold">
                            1</div>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Create Account</p>
                        <p class="text-sm text-gray-600">Register with your email and password</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div
                            class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-bold">
                            2</div>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Confidential Reference Check</p>
                        <p class="text-sm text-gray-600">Authorize our confidential reference check process</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div
                            class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-bold">
                            3</div>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Medical Examination</p>
                        <p class="text-sm text-gray-600">Schedule and complete your medical check</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div
                            class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-bold">
                            4</div>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Submit References</p>
                        <p class="text-sm text-gray-600">Provide professional references</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div
                            class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-bold">
                            5</div>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Compliance Forms</p>
                        <p class="text-sm text-gray-600">Complete all required documentation</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="max-w-2xl mx-auto mt-8 pt-6 border-t text-center">
        <p class="text-gray-600 text-sm">
            <strong>Need help?</strong> Contact our HR department at
            <a href="mailto:hr@biopacific.com" class="text-blue-600 font-semibold hover:underline">hr@biopacific.com</a>
        </p>
    </div>
</div>
@endsection
@endif