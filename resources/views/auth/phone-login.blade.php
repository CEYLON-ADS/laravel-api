@extends('layouts.app')

@section('content')
    <div class="max-w-md mx-auto w-full">
        <section class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200 p-8 sm:p-10">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-serif font-bold text-slate-900 mb-2">Welcome Back</h1>
                <p class="text-slate-500 font-medium">Please enter your email and password to log in.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">{{ $errors->first() }}</h3>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('user.login.submit') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required
                        class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm px-4 py-3 bg-slate-50 hover:bg-white transition-colors"
                    >
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                    <input id="password" name="password" type="password" required
                        class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm px-4 py-3 bg-slate-50 hover:bg-white transition-colors"
                    >
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all transform hover:-translate-y-0.5 hover:shadow-md">
                        Log In
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
