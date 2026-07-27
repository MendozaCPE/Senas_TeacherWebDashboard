@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<div class="flex min-h-screen">

    {{-- ── LEFT PANEL ── --}}
    <div class="hidden lg:flex lg:w-5/12 flex-col items-center justify-center"
         style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);">

        <h1 class="text-white font-black text-5xl tracking-[0.25em] mb-8 drop-shadow-lg select-none">SEÑAS</h1>

        {{-- Mascot overlapping the white card --}}
        <div class="relative" style="width: 350px; height: 500px;">
            {{-- White phone-shaped card --}}
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 bg-white rounded-[3.5rem] shadow-2xl"
                 style="width: 310px; height: 420px;"></div>
            {{-- Senya overflows above the card --}}
            <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya mascot"
                 class="absolute bottom-0 left-1/2 -translate-x-1/2 z-10 w-full object-contain object-bottom select-none"
                 style="height: 500px;" draggable="false">
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="flex-1 flex items-center justify-center bg-white px-6 py-12">
        <div class="w-full max-w-md">

            {{-- Back arrow --}}
            <button onclick="history.back()"
                    class="inline-flex items-center text-gray-400 hover:text-gray-600 transition mb-4">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </button>

            <h2 class="text-3xl font-extrabold text-gray-900 mb-1">Welcome, Teacher</h2>
            <p class="text-gray-400 text-sm mb-8">Login in to continue</p>

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

            @if(!config('services.auth.developer_mode'))
            <div class="mb-5 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] shrink-0">shield</span>
                <span>Access restricted to <strong>@{{ config('services.auth.allowed_email_domain') }}</strong> accounts only.</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">mail</span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               placeholder="name@deped.gov.ph" required autocomplete="email"
                               class="w-full bg-gray-100 rounded-2xl pl-12 pr-4 py-4 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Password</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">lock</span>
                        <input id="password" type="password" name="password"
                               placeholder="••••••••" required autocomplete="current-password"
                               class="w-full bg-gray-100 rounded-2xl pl-12 pr-12 py-4 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                        <button type="button" onclick="togglePwd('password','eye-login')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span id="eye-login" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                {{-- Remember + Forgot --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded accent-blue-500">
                        <span class="text-sm text-gray-500">Remember Me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-500 hover:text-blue-700 font-medium transition">Forgot password?</a>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full py-4 rounded-full font-bold text-white text-sm tracking-wide transition hover:opacity-90 active:scale-95 flex items-center justify-center gap-2"
                        style="background:#1C3D7A;">
                    Log In
                    <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">arrow_forward</span>
                </button>
            </form>

            {{-- Divider --}}
            <div class="flex items-center my-6 gap-3">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-gray-400 text-xs">or continue with</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            {{-- Google Sign-In --}}
            <a href="{{ route('auth.google', ['intent' => 'login']) }}"
               class="w-full flex items-center justify-center gap-3 py-3.5 px-4 rounded-2xl border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm font-semibold text-gray-700 text-sm">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M23.745 12.27c0-.79-.07-1.54-.19-2.27h-11.3v4.51h6.47c-.29 1.48-1.14 2.73-2.4 3.58v3h3.86c2.26-2.09 3.56-5.17 3.56-8.82z"/>
                    <path fill="#34A853" d="M12.255 24c3.24 0 5.95-1.08 7.93-2.91l-3.86-3c-1.08.72-2.45 1.16-4.07 1.16-3.13 0-5.78-2.11-6.73-4.96h-3.98v3.09C3.515 21.3 7.615 24 12.255 24z"/>
                    <path fill="#FBBC05" d="M5.525 14.29c-.25-.72-.38-1.49-.38-2.29s.14-1.57.38-2.29V6.62h-3.98a11.86 11.86 0 000 10.76l3.98-3.09z"/>
                    <path fill="#EA4335" d="M12.255 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C18.205 1.19 15.495 0 12.255 0c-4.64 0-8.74 2.7-10.71 6.62l3.98 3.09c.95-2.85 3.6-4.96 6.73-4.96z"/>
                </svg>
                Sign in with Google
            </a>

            <p class="text-center text-sm text-gray-400 mt-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-blue-500 hover:text-blue-700 font-semibold transition">Create one</a>
            </p>

        </div>
    </div>
</div>

<!-- Full-Screen Interactive Loading Overlay -->
<div id="login-loading-overlay" class="fixed inset-0 z-50 flex items-center justify-center hidden" style="background:rgba(13,50,107,0.65);backdrop-filter:blur(8px);">
    <div class="bg-white rounded-3xl px-10 py-10 max-w-xs w-full shadow-2xl text-center flex flex-col items-center gap-4">

        {{-- Spinner ring + lock icon --}}
        <div class="relative w-20 h-20 flex items-center justify-center mb-1">
            <div class="absolute inset-0 rounded-full border-[5px] border-slate-100 border-t-[#0d326b]"
                 style="animation:spin-loader 0.9s linear infinite;"></div>
            <span class="material-symbols-outlined text-3xl text-[#0d326b]" style="font-variation-settings:'FILL' 1;">shield_person</span>
        </div>

        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Signing In</p>
            <h3 id="loading-teacher-name" class="text-[18px] font-extrabold text-[#0d326b] leading-snug">Logging in...</h3>
        </div>

        <div class="flex gap-1.5 mt-1">
            <span class="w-2 h-2 rounded-full bg-[#0d326b] opacity-100" style="animation:dot-bounce 1.2s infinite 0s;"></span>
            <span class="w-2 h-2 rounded-full bg-[#0d326b] opacity-100" style="animation:dot-bounce 1.2s infinite 0.2s;"></span>
            <span class="w-2 h-2 rounded-full bg-[#0d326b] opacity-100" style="animation:dot-bounce 1.2s infinite 0.4s;"></span>
        </div>
    </div>
</div>

<style>
@keyframes spin-loader { to { transform: rotate(360deg); } }
@keyframes dot-bounce {
    0%, 80%, 100% { transform: translateY(0); opacity:.3; }
    40% { transform: translateY(-6px); opacity:1; }
}
</style>

<script>
function togglePwd(inputId, iconId) {
    const el = document.getElementById(inputId);
    const ic = document.getElementById(iconId);
    el.type = el.type === 'password' ? 'text' : 'password';
    ic.textContent = el.type === 'password' ? 'visibility' : 'visibility_off';
}

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form[action="{{ route('login') }}"]');
    const loadingOverlay = document.getElementById('login-loading-overlay');
    const loadingTeacherName = document.getElementById('loading-teacher-name');

    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const emailInput = document.getElementById('email').value.trim();

            loadingTeacherName.textContent = 'Logging in...';
            loadingOverlay.classList.remove('hidden');

            if (emailInput) {
                try {
                    const res = await fetch("{{ route('api.teacher-name') }}?email=" + encodeURIComponent(emailInput));
                    const data = await res.json();
                    if (data && data.name) {
                        loadingTeacherName.textContent = 'Logging in as Teacher ' + data.name;
                    } else {
                        loadingTeacherName.textContent = 'Logging in...';
                    }
                } catch (_) {
                    loadingTeacherName.textContent = 'Logging in...';
                }
            }

            setTimeout(function() { loginForm.submit(); }, 800);
        });
    }

    const googleLink = document.querySelector('a[href*="auth.google"]');
    if (googleLink) {
        googleLink.addEventListener('click', function() {
            loadingTeacherName.textContent = 'Connecting to Google...';
            loadingOverlay.classList.remove('hidden');
        });
    }
});
</script>
@endsection
