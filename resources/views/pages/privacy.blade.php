@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-12">
            <h1 class="text-3xl font-serif font-bold text-slate-900 mb-6">Privacy Policy</h1>
            <div class="prose prose-slate max-w-none text-slate-600">
                <p class="text-lg leading-relaxed mb-4">
                    This migrated version stores user-provided advertisement and contact data in MySQL.
                </p>
                <p class="text-lg leading-relaxed">
                    Only required operational data is stored, and OTP values are short-lived to ensure the security of your account and personal information.
                </p>
            </div>
        </section>
    </div>
@endsection
