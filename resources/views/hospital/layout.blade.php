<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Hospital Customer Service & Inquiries') - CareDesk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        hospital: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col antialiased">
    <!-- Top Emergency Banner -->
    <div class="bg-red-700 text-white text-xs sm:text-sm py-2 px-4 shadow-sm">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-300 animate-ping"></span>
                <span class="font-bold">24/7 Medical Emergency Hotline:</span>
                <a href="tel:+15550199111" class="underline font-semibold hover:text-red-100">+1 (555) 019-9111</a>
            </div>
            <div class="text-xs text-red-100">
                Main Hospital Campus &bull; North Wing Ambulance Entrance
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            <div class="flex items-center gap-3">
                <a href="{{ route('hospital.home') }}" class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-lg bg-teal-600 flex items-center justify-center text-white font-black text-xl shadow">
                        +
                    </div>
                    <div>
                        <span class="font-extrabold text-slate-900 text-lg tracking-tight">CareDesk</span>
                        <span class="block text-xs text-teal-700 font-medium">Hospital Patient & Guest Service</span>
                    </div>
                </a>
            </div>

            <nav class="hidden md:flex items-center gap-1 font-medium text-sm text-slate-600">
                <a href="{{ route('hospital.home') }}" class="px-3 py-2 rounded-md hover:text-teal-700 hover:bg-teal-50 transition {{ request()->routeIs('hospital.home') ? 'text-teal-700 bg-teal-50 font-semibold' : '' }}">Home</a>
                <a href="{{ route('hospital.inquiry.create') }}" class="px-3 py-2 rounded-md hover:text-teal-700 hover:bg-teal-50 transition {{ request()->routeIs('hospital.inquiry.create') ? 'text-teal-700 bg-teal-50 font-semibold' : '' }}">Submit Inquiry</a>
                <a href="{{ route('hospital.track') }}" class="px-3 py-2 rounded-md hover:text-teal-700 hover:bg-teal-50 transition {{ request()->routeIs('hospital.track') ? 'text-teal-700 bg-teal-50 font-semibold' : '' }}">Track Ticket</a>
                <a href="{{ route('hospital.departments') }}" class="px-3 py-2 rounded-md hover:text-teal-700 hover:bg-teal-50 transition {{ request()->routeIs('hospital.departments') ? 'text-teal-700 bg-teal-50 font-semibold' : '' }}">Departments</a>
                <a href="{{ route('hospital.faq') }}" class="px-3 py-2 rounded-md hover:text-teal-700 hover:bg-teal-50 transition {{ request()->routeIs('hospital.faq') ? 'text-teal-700 bg-teal-50 font-semibold' : '' }}">FAQs</a>
                <a href="{{ route('hospital.desk.index') }}" class="ml-3 px-3 py-1.5 rounded-lg border border-teal-600 text-teal-700 bg-teal-50/50 hover:bg-teal-600 hover:text-white transition text-xs font-bold uppercase tracking-wider">
                    Staff Desk &rarr;
                </a>
            </nav>

            <div class="md:hidden flex items-center gap-2">
                <a href="{{ route('hospital.track') }}" class="text-xs bg-slate-100 px-2.5 py-1.5 rounded text-slate-700 font-semibold">Track</a>
                <a href="{{ route('hospital.inquiry.create') }}" class="text-xs bg-teal-600 px-3 py-1.5 rounded text-white font-semibold">+ Help</a>
                <a href="{{ route('hospital.desk.index') }}" class="text-xs bg-slate-800 px-2.5 py-1.5 rounded text-white font-semibold">Desk</a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        <!-- Flash Notifications -->
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-teal-50 border border-teal-200 p-4 text-teal-900 flex items-start gap-3 shadow-sm">
                <svg class="w-5 h-5 text-teal-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-red-900 shadow-sm">
                <div class="text-sm font-semibold mb-1">Please correct the following:</div>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-12 py-8 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded bg-teal-600 text-white flex items-center justify-center font-bold text-xs">+</div>
                <span class="font-bold text-slate-700">CareDesk Hospital Customer Service System</span>
                <span>&bull; St. Jude Medical Center</span>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('hospital.home') }}" class="hover:underline">Patient Portal</a>
                <a href="{{ route('hospital.departments') }}" class="hover:underline">Departments</a>
                <a href="{{ route('hospital.faq') }}" class="hover:underline">FAQ</a>
                <a href="{{ route('hospital.desk.index') }}" class="text-teal-700 font-semibold hover:underline">Customer Service Desk</a>
            </div>
        </div>
    </footer>
</body>
</html>