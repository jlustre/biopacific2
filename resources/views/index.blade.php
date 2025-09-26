<!DOCTYPE< /head>

    <body class="bg-gray-50 font-sans antialiased">="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <title>Bio Pacific Healthcare</title>
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        </head>

        <body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen">
            <div class="min-h-screen flex items-center justify-center px-4">
                <div class="max-w-md w-full">
                    <!-- Logo/Header -->
                    <div class="text-center mb-8">
                        <div
                            class="bg-gradient-to-br from-primary to-green-600 p-4 rounded-2xl shadow-lg inline-block mb-4">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Bio-Pacific Healthcare</h1>
                        <p class="text-gray-600">Multi-tenant site management</p>
                    </div>

                    <!-- Quick Access Cards -->
                    <div class="space-y-4">
                        <!-- Dashboard Access -->
                        <a href="/dashboard"
                            class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-200 p-6 group">
                            <div class="flex items-center gap-4">
                                <div class="bg-primary/10 p-3 rounded-lg group-hover:bg-primary/20 transition-colors">
                                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 group-hover:text-primary transition-colors">
                                        Site
                                        Directory</h3>
                                    <p class="text-sm text-gray-600">View and access all facilities</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>

                        <!-- Sample Facility Preview -->
                        <a href="/admin/facility/1/preview"
                            class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-200 p-6 group">
                            <div class="flex items-center gap-4">
                                <div class="bg-accent/10 p-3 rounded-lg group-hover:bg-accent/20 transition-colors">
                                    <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 group-hover:text-accent transition-colors">
                                        Preview
                                        Sample Site</h3>
                                    <p class="text-sm text-gray-600">View first facility website</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-accent transition-colors" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>

                        <!-- Local Development Info -->
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h4 class="font-medium text-amber-800 mb-1">Development Mode</h4>
                                    <p class="text-sm text-amber-700">You're in local development. The system will
                                        automatically
                                        handle tenant resolution for testing.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-8 text-sm text-gray-500">
                        <p>&copy; {{ date('Y') }} Bio-Pacific Healthcare Network</p>
                        <p class="mt-1">Laravel {{ app()->version() }} Multi-tenant System</p>
                    </div>
                </div>
            </div>
        </body>

        </html>