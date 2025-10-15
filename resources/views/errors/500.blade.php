<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Server Error - Bio-Pacific Healthcare</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-image: url('{{ asset(' images/auth_background.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .animate-fade-in {
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div
            class="w-full max-w-md bg-white/90 shadow-2xl rounded-2xl p-8 text-center flex flex-col items-center animate-fade-in">
            <img src="{{ asset('images/500.png') }}" alt="500 Server Error"
                class="mb-6 w-48 h-48 md:w-56 md:h-56 lg:w-64 lg:h-64 object-contain drop-shadow-lg">
            <h1 class="text-4xl md:text-5xl font-extrabold text-teal-500 mb-2">500</h1>
            <h2 class="text-xl md:text-2xl font-semibold text-teal-500 mb-4">Server Error</h2>
            <p class="text-base md:text-lg text-gray-700 mb-6">Oops! Something went wrong on our end.<br
                    class="hidden md:inline"> Please try again later or return to your dashboard.</p>
            <a href="{{ route('dashboard.index') }}"
                class="inline-block w-full md:w-auto px-6 py-3 bg-teal-500 text-white rounded-lg font-bold shadow-lg hover:scale-105 hover:bg-teal-600 transition-all duration-200">Go
                to User Dashboard</a>
        </div>
    </div>
</body>

</html>