@extends('layouts.auth')
@section('title', 'Verify Email')

@section('content')
<div class="flex min-h-screen">

    {{-- ── LEFT PANEL ── --}}
    <div class="hidden lg:flex lg:w-5/12 flex-col items-center justify-center"
         style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);">

        <h1 class="text-white font-black text-5xl tracking-[0.25em] mb-8 drop-shadow-lg select-none">SEÑAS</h1>

        <div class="relative" style="width: 350px; height: 500px;">
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 bg-white rounded-[3.5rem] shadow-2xl"
                 style="width: 310px; height: 420px;"></div>
            <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya mascot"
                 class="absolute bottom-0 left-1/2 -translate-x-1/2 z-10 w-full object-contain object-bottom select-none"
                 style="height: 500px;" draggable="false">
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="flex-1 flex items-center justify-center bg-white px-6 py-12">
        <div class="w-full max-w-md">

            <a href="{{ route('register') }}"
               class="inline-flex items-center text-gray-400 hover:text-gray-600 transition mb-4">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>

            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0d326b] mb-4 shadow-sm">
                <span class="material-symbols-outlined text-2xl">mark_email_unread</span>
            </div>

            <h2 class="text-3xl font-extrabold text-gray-900 mb-1">Verify Email Address</h2>
            <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                We sent a 6-digit verification code to <strong class="text-gray-800">{{ $email }}</strong>. Please enter the code below to complete your account creation.
            </p>

            {{-- Status Messages --}}
            @if (session('status'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] shrink-0">check_circle</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-600 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] shrink-0">error</span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('register.verify-otp') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-3 text-center">6-Digit Verification Code</label>
                    <input type="text" name="otp" id="otp-input" maxlength="6" pattern="\d{6}" autofocus required
                           placeholder="000000"
                           class="w-full text-center tracking-[12px] font-mono text-3xl font-black py-4 bg-gray-100 border border-transparent rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition text-[#0d326b]" />
                </div>

                <button type="submit"
                        class="w-full py-4 rounded-full font-bold text-white text-sm tracking-wide transition hover:opacity-90 active:scale-95 flex items-center justify-center gap-2"
                        style="background:#1C3D7A;">
                    Verify & Create Account
                    <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">check_circle</span>
                </button>
            </form>

            <form method="POST" action="{{ route('register.resend-otp') }}" class="mt-6 text-center">
                @csrf
                <p class="text-sm text-gray-400">
                    Didn't receive the code?
                    <button type="submit" class="text-blue-500 hover:text-blue-700 font-semibold transition underline-offset-2 hover:underline ml-1">Resend Code</button>
                </p>
            </form>

        </div>
    </div>
</div>
@endsection
