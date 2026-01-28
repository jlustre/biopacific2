<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden</title>
    <link rel="icon" href="{{ asset('images/bplogo.png') }}" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        @media (max-width: 640px) {
            .animate-fade-in {
                animation: fadeIn 0.7s ease;
            }

            .max-w-md {
                max-width: 95vw;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center px-4"
    style="background-image: url('{{ asset('images/auth_background.jpg') }}'); background-size: cover; background-position: center;">
    <div
        class="w-full max-w-md bg-white shadow-2xl rounded-2xl p-8 text-center flex flex-col items-center animate-fade-in">
        @php
        $user = auth()->user();
        $roles = $user ? $user->getRoleNames() : collect();
        @endphp
        <img src="{{ asset('images/403.png') }}" alt="403 Forbidden"
            class="mb-6 w-48 h-48 md:w-56 md:h-56 lg:w-64 lg:h-64 object-contain drop-shadow-lg">

        <p class="text-base md:text-lg text-gray-700 mb-6">Sorry, you do not have permission to view this page.<br
                class="hidden md:inline"> If you believe this is a mistake, please contact admin/support or return to
            your
            dashboard.</p>
        <a href="{{ route('dashboard.index') }}"
            class="inline-block w-full md:w-auto px-6 py-3 bg-teal-500 text-teal-600 rounded-lg font-bold shadow-lg hover:scale-105 hover:bg-teal-600 transition-all duration-200">Go
            to User Dashboard</a>
    </div>
</body>

</html>