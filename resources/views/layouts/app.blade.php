<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Ceylon Ads' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col {{ request()->routeIs('home') ? 'home-page bg-white' : '' }}">
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-200/60 shadow-sm transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 sm:h-20">
                <!-- Brand -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="font-serif text-2xl font-bold tracking-wide text-brand-900 hover:text-brand-600 transition-colors">
                        Ceylon Ads
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="hidden md:flex space-x-6 lg:space-x-8 items-center">
                    <a href="{{ route('home') }}" class="text-slate-600 hover:text-brand-600 font-medium text-sm transition-colors">Browse</a>
                    
                    @if (in_array(session('application_user_role'), ['ads_agent', 'admin', 'super_admin'], true))
                        <a href="{{ route('ads.create') }}" class="text-slate-600 hover:text-brand-600 font-medium text-sm transition-colors">Publish</a>
                    @endif
                    
                    <a href="{{ route('pages.about') }}" class="text-slate-600 hover:text-brand-600 font-medium text-sm transition-colors">About</a>
                    <a href="{{ route('pages.privacy') }}" class="text-slate-600 hover:text-brand-600 font-medium text-sm transition-colors">Privacy</a>
                    <a href="{{ route('pages.terms') }}" class="text-slate-600 hover:text-brand-600 font-medium text-sm transition-colors">Terms</a>
                </nav>

                <!-- Auth/Actions -->
                <div class="flex items-center space-x-4">
                    @if (session('application_user_id'))
                        <form method="POST" action="{{ route('user.logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-slate-200 rounded-full text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all shadow-sm">
                                Logout {{ session('application_user_mobile') }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('user.login.form') }}" class="inline-flex items-center justify-center px-5 py-2 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all hover:shadow-md transform hover:-translate-y-0.5">
                            User Login
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        @if (session('status'))
            <div class="mb-8 rounded-xl bg-brand-50 border border-brand-200 p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-brand-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-brand-800">{{ session('status') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-slate-500 font-medium">
                &copy; {{ date('Y') }} Ceylon Ads. All rights reserved. queenslanka.
            </p>
        </div>
    </footer>
</body>
</html>
