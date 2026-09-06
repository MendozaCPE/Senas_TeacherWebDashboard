@extends('layouts.auth')
@section('title', 'Reset Password')

@section('content')

<style>
.btn-gradient {
    background: linear-gradient(135deg, #2979ff 0%, #1a6fd4 45%, #1C3D7A 100%);
    transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.15s ease;
    box-shadow: 0 6px 24px rgba(26,111,212,0.45);
}
.btn-gradient:hover {
    background: linear-gradient(135deg, #448aff 0%, #1e7fe8 45%, #22489a 100%);
    box-shadow: 0 10px 36px rgba(26,111,212,0.55);
    transform: translateY(-1px);
}
.btn-gradient:active { transform: scale(0.97) translateY(0); }

.auth-input {
    transition: box-shadow 0.25s ease, border-color 0.25s ease, background 0.25s ease;
    border: 2px solid transparent;
    background: #f1f5fb;
}
.auth-input:focus {
    background: #fff !important;
    border-color: #2979ff !important;
    box-shadow: 0 0 0 5px rgba(41,121,255,0.10);
    outline: none;
}
.auth-input:not(:placeholder-shown) {
    background: #fff;
    border-color: #e2e8f0;
}

@keyframes shimmer {
    0%   { background-position: 200% center; }
    100% { background-position: -200% center; }
}
.senas-title {
    background: linear-gradient(90deg, #fff 25%, #a8d4ff 50%, #fff 75%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: shimmer 4s linear infinite;
}
.input-wrap:focus-within .input-icon { color: #2979ff; }
</style>

<div class="flex" style="min-height:calc(100vh / 0.9)">

    {{-- ── LEFT PANEL ── --}}
    <div class="hidden lg:flex lg:w-5/12 flex-col items-center justify-start relative py-14 gap-10"
         style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);">

        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[-90px] left-[-90px] w-80 h-80 rounded-full opacity-10"
                 style="background:radial-gradient(circle,#fff,transparent);"></div>
            <div class="absolute bottom-[-70px] right-[-70px] w-72 h-72 rounded-full opacity-10"
                 style="background:radial-gradient(circle,#fff,transparent);"></div>
            <div class="absolute top-1/2 left-[-50px] w-40 h-40 rounded-full opacity-[0.07]"
                 style="background:#a8d4ff;"></div>
        </div>

        <div class="flex flex-col items-center relative z-10">
            <h1 class="senas-title font-black text-6xl tracking-[0.30em] mb-3 drop-shadow-lg select-none"
                style="padding-top:0.4em; line-height:1.3; overflow:visible;">SEÑAS</h1>
            <p class="text-blue-200 text-xs tracking-[0.2em] uppercase font-semibold">Teacher Portal</p>
        </div>

        <div class="relative z-10 flex items-end justify-center" style="width:400px;height:500px;margin-bottom:-56px;">
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 bg-white rounded-[3.5rem] shadow-2xl"
                 style="width:340px;height:440px;box-shadow:0 32px 80px rgba(0,0,0,0.35);"></div>
            <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya mascot"
                 class="absolute bottom-0 left-1/2 -translate-x-1/2 z-10 w-full object-contain object-bottom select-none"
                 style="height:520px;filter:drop-shadow(0 16px 32px rgba(0,0,0,0.2));" draggable="false">
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="flex-1 flex items-center justify-center bg-white px-10 py-12 overflow-y-auto">
        <div class="w-full max-w-xl relative z-10">

            {{-- Back --}}
            <a href="{{ route('login') }}"
               class="inline-flex items-center text-gray-400 hover:text-[#2979ff] transition-colors mb-7 group">
                <span class="material-symbols-outlined text-xl mr-1.5 group-hover:-translate-x-0.5 transition-transform">arrow_back</span>
                <span class="text-sm font-medium">Back to Login</span>
            </a>

            {{-- Header --}}
            <div class="mb-8">
                <h2 class="text-4xl font-extrabold text-gray-900 mb-2 leading-tight">Reset your password</h2>
                <p class="text-gray-400 text-base">Choose a strong new password to regain access to your account.</p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-600 rounded-2xl px-5 py-4 text-sm flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1;">error</span>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Email Address</label>
                    <div class="relative input-wrap">
                        <span class="input-icon material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl transition-colors duration-200">mail</span>
                        <input id="email" type="email" name="email"
                               value="{{ old('email', $email ?? '') }}"
                               placeholder="name@deped.gov.ph" required autocomplete="email"
                               class="auth-input w-full rounded-2xl pl-12 pr-4 py-4 text-gray-800 placeholder-gray-400 text-sm focus:outline-none">
                    </div>
                </div>

                {{-- New Password --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">New Password</label>
                    <div class="relative input-wrap">
                        <span class="input-icon material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl transition-colors duration-200">lock</span>
                        <input id="password" type="password" name="password"
                               placeholder="Minimum 8 characters" required autocomplete="new-password"
                               class="auth-input w-full rounded-2xl pl-12 pr-12 py-4 text-gray-800 placeholder-gray-400 text-sm focus:outline-none">
                        <button type="button" onclick="togglePwd('password','eye-reset')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#2979ff] transition-colors">
                            <span id="eye-reset" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Confirm Password</label>
                    <div class="relative input-wrap">
                        <span class="input-icon material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl transition-colors duration-200">lock</span>
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               placeholder="Re-enter new password" required autocomplete="new-password"
                               class="auth-input w-full rounded-2xl pl-12 pr-12 py-4 text-gray-800 placeholder-gray-400 text-sm focus:outline-none">
                        <button type="button" onclick="togglePwd('password_confirmation','eye-reset-confirm')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#2979ff] transition-colors">
                            <span id="eye-reset-confirm" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="btn-gradient w-full py-4 rounded-full font-bold text-white text-sm tracking-widest uppercase flex items-center justify-center gap-2 mt-2">
                    Reset Password
                    <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">lock_reset</span>
                </button>
            </form>

            <p class="text-center text-sm text-gray-400 mt-7">
                Remembered your password?
                <a href="{{ route('login') }}" class="text-[#2979ff] hover:text-[#0d326b] font-bold transition-colors">Sign in</a>
            </p>

        </div>
    </div>
</div>

<script>
function togglePwd(inputId, iconId) {
    const el = document.getElementById(inputId);
    const ic = document.getElementById(iconId);
    if (!el || !ic) return;
    el.type = el.type === 'password' ? 'text' : 'password';
    ic.textContent = el.type === 'password' ? 'visibility' : 'visibility_off';
}
</script>

@endsection
