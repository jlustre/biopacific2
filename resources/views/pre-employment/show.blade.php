<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pre-Employment Portal - Bio-Pacific</title>
    <link rel="icon" type="image/png" href="{{ asset('images/bplogo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <nav
            class="fixed top-0 left-0 right-0 bg-gradient-to-r from-teal-700 via-teal-600 to-teal-700 text-white shadow-lg z-50 h-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2 hover:opacity-90 transition">
                        <img src="{{ asset('images/bplogo.png') }}" alt="Bio-Pacific"
                            class="w-8 h-8 filter brightness-0 invert">
                        <span class="text-lg font-bold hidden sm:inline">Bio-Pacific</span>
                    </a>
                </div>
                <div class="flex items-center gap-6">
                    @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="bg-teal-500 hover:bg-teal-400 text-white px-4 py-2 rounded-lg transition text-sm font-medium">
                            Logout
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login', $applicantCode ? ['code' => $applicantCode] : []) }}"
                        class="text-teal-100 hover:text-white transition text-sm font-medium">Login</a>
                    @if (!empty($hasAccount))
                    <span class="bg-teal-500/60 text-white px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed"
                        aria-disabled="true">
                        Already Registered
                    </span>
                    @else
                    <a href="{{ route('register', $applicantCode ? ['code' => $applicantCode] : []) }}"
                        class="bg-teal-500 hover:bg-teal-400 text-white px-4 py-2 rounded-lg transition text-sm font-medium">Register</a>
                    @endif
                    @endauth
                </div>
            </div>
        </nav>

        <main class="pt-20 flex-1 relative z-10 w-full">
            <div class="bg-gradient-to-br from-teal-50 via-white to-teal-50 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mx-auto">
                    <div class="text-center mb-8">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4 shadow-lg border-2 border-teal-600">
                            <img src="{{ asset('images/bplogo.png') }}" alt="Bio-Pacific Logo" class="w-12 h-12">
                        </div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-2">Pre-Employment Portal</h1>
                        <p class="text-teal-600 text-lg font-semibold">Complete your onboarding in minutes</p>
                        @if (!empty($hasAccount) && !empty($applicantName))
                        <p class="mt-3 text-gray-700 text-base font-medium">
                            Welcome back, {{ $applicantName }}. Please log in to continue.
                        </p>
                        @endif
                    </div>

                    <div class="bg-teal-50 rounded-xl p-6 mb-8 border-l-4 border-teal-600">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">You're Almost There!</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Congratulations! We're excited to see you move forward in our hiring process. To access your
                            pre-employment forms and complete the next steps, please create an account or log in.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-8 mb-8 border border-gray-200 shadow-lg">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2 text-center">Create Your Account</h3>
                        <p class="text-gray-600 text-center mb-8">Register now to access your secure pre-employment
                            dashboard</p>
                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-teal-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Quick Setup</p>
                                    <p class="text-sm text-gray-600">2 minutes</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-teal-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
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
                                    <svg class="h-6 w-6 text-teal-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
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
                                    <svg class="h-6 w-6 text-teal-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
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

                        @if (!empty($hasAccount))
                        <span
                            class="block w-full text-center bg-teal-600/60 text-white font-bold py-4 px-6 rounded-lg shadow-lg mb-4 cursor-not-allowed"
                            aria-disabled="true">
                            Already Registered
                        </span>
                        @else
                        <a href="{{ route('register', $applicantCode ? ['code' => $applicantCode] : []) }}"
                            class="block w-full text-center bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white font-bold py-4 px-6 rounded-lg transition shadow-lg hover:shadow-xl mb-4">
                            Create Account Now
                        </a>
                        @endif

                        <p class="text-center text-gray-600">
                            Already have an account?
                            <a href="{{ route('login', $applicantCode ? ['code' => $applicantCode] : []) }}"
                                class="text-teal-600 font-bold hover:text-teal-700">Sign in
                                instead</a>
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-8 border border-gray-200 shadow-lg">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">Pre-Employment Process</h3>
                        <div class="space-y-4">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-teal-600 text-white text-sm font-bold">
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
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-teal-600 text-white text-sm font-bold">
                                        2</div>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Application Form</p>
                                    <p class="text-sm text-gray-600">Complete your required application documents</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-teal-600 text-white text-sm font-bold">
                                        3</div>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Confidential Reference Check</p>
                                    <p class="text-sm text-gray-600">Authorize our background check process</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-teal-600 text-white text-sm font-bold">
                                        4</div>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Medical Examination</p>
                                    <p class="text-sm text-gray-600">Schedule and complete your medical check</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-teal-600 text-white text-sm font-bold">
                                        5</div>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Submit References</p>
                                    <p class="text-sm text-gray-600">Provide professional references</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-teal-600 text-white text-sm font-bold">
                                        6</div>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Compliance Forms</p>
                                    <p class="text-sm text-gray-600">Complete all required documentation</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t text-center">
                        <p class="text-gray-600 text-sm">
                            <strong>Need help?</strong> Contact our HR department at
                            <a href="mailto:hr@biopacific.com"
                                class="text-teal-600 font-semibold hover:underline">hr@biopacific.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-gray-900 text-gray-400 py-8 w-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
                <p>&copy; {{ date('Y') }} Bio-Pacific. All rights reserved.</p>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>

</html>