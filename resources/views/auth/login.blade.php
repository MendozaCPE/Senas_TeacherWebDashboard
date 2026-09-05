@extends('layouts.auth')
@section('title', 'Login')

@section('content')

<style>
/* ── Gradient button ── */
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

/* ── Input styles ── */
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

/* ── Floating blobs ── */
@keyframes blob-move {
    0%, 100% { transform: translate(0,0) scale(1); }
    33%       { transform: translate(30px,-25px) scale(1.08); }
    66%       { transform: translate(-22px,18px) scale(0.94); }
}
.blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(90px);
    opacity: 0.11;
    animation: blob-move 14s ease-in-out infinite;
    pointer-events: none;
}
.blob-1 { width:420px; height:420px; background:#2979ff; top:-100px; right:-80px; animation-delay:0s; }
.blob-2 { width:320px; height:320px; background:#0d326b; bottom:40px;  right:20px;  animation-delay:-6s; }
.blob-3 { width:260px; height:260px; background:#3A9EE4; top:40%; left:-50px; animation-delay:-10s; }

/* ── Particles (subtle floating dots) ── */
@keyframes float-up {
    0%   { transform: translateY(0) scale(1);   opacity: 0; }
    10%  { opacity: 0.5; }
    90%  { opacity: 0.3; }
    100% { transform: translateY(-120px) scale(0.6); opacity: 0; }
}
.particle {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    animation: float-up linear infinite;
}

/* ── Card slide-up ── */
@keyframes card-in {
    from { opacity:0; transform:translateY(28px); }
    to   { opacity:1; transform:translateY(0); }
}

/* ── Spinner / dots ── */
@keyframes spin-loader { to { transform: rotate(360deg); } }
@keyframes dot-bounce {
    0%, 80%, 100% { transform: translateY(0); opacity:.3; }
    40%            { transform: translateY(-7px); opacity:1; }
}

/* ── Left panel shimmer title ── */
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

/* ── Input icon color on focus ── */
.input-wrap:focus-within .input-icon { color: #2979ff; }

/* ── Google btn hover ── */
.google-btn {
    transition: box-shadow 0.2s, border-color 0.2s, transform 0.15s;
}
.google-btn:hover {
    box-shadow: 0 4px 18px rgba(0,0,0,0.09);
    border-color: #c5d3e8;
    transform: translateY(-1px);
}
</style>

<div class="flex min-h-screen">

    {{-- ── LEFT PANEL ── --}}
    <div class="hidden lg:flex lg:w-5/12 flex-col items-center justify-start relative py-14 gap-10"
         style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);">

        {{-- Decorative corner circles (clipped inside their own wrapper) --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[-90px] left-[-90px] w-80 h-80 rounded-full opacity-10"
                 style="background:radial-gradient(circle,#fff,transparent);"></div>
            <div class="absolute bottom-[-70px] right-[-70px] w-72 h-72 rounded-full opacity-10"
                 style="background:radial-gradient(circle,#fff,transparent);"></div>
            <div class="absolute top-1/2 left-[-50px] w-40 h-40 rounded-full opacity-[0.07]"
                 style="background:#a8d4ff;"></div>
        </div>

        {{-- Logo block (top) --}}
        <div class="flex flex-col items-center relative z-10">
            <h1 class="senas-title font-black text-6xl tracking-[0.30em] mb-3 drop-shadow-lg select-none" style="padding-top:0.4em; line-height:1.3; overflow:visible;">SEÑAS</h1>
            <p class="text-blue-200 text-xs tracking-[0.2em] uppercase font-semibold">Teacher Portal</p>
        </div>

        {{-- Mascot card (bottom-anchored) --}}
        <div class="relative z-10 flex items-end justify-center" style="width: 400px; height: 500px; margin-bottom: -56px;">
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 bg-white rounded-[3.5rem] shadow-2xl"
                 style="width:340px; height:440px; box-shadow: 0 32px 80px rgba(0,0,0,0.35);"></div>
            <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya mascot"
                 class="absolute bottom-0 left-1/2 -translate-x-1/2 z-10 w-full object-contain object-bottom select-none"
                 style="height:520px; filter: drop-shadow(0 16px 32px rgba(0,0,0,0.2));" draggable="false">
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="flex-1 flex items-center justify-center bg-white px-10 py-12 overflow-y-auto">

        <div class="w-full max-w-xl relative z-10">

            {{-- Back --}}
            <a href="{{ route('home') }}"
               class="inline-flex items-center text-gray-400 hover:text-[#2979ff] transition-colors mb-7 group">
                <span class="material-symbols-outlined text-xl mr-1.5 group-hover:-translate-x-0.5 transition-transform">arrow_back</span>
                <span class="text-sm font-medium">Back to Home</span>
            </a>

            {{-- Header --}}
            <div class="mb-8">
                <h2 class="text-4xl font-extrabold text-gray-900 mb-2 leading-tight">Welcome back</h2>
                <p class="text-gray-400 text-base">Sign in to your account to continue</p>
            </div>

            {{-- Status --}}
            @if (session('status'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl px-5 py-4 text-sm flex items-center gap-2.5">
                <span class="material-symbols-outlined text-[20px] shrink-0" style="font-variation-settings:'FILL' 1;">check_circle</span>
                <span>{{ session('status') }}</span>
            </div>
            @endif

            {{-- Errors --}}
            @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 text-red-600 rounded-2xl px-5 py-4 text-sm flex items-center gap-2.5">
                <span class="material-symbols-outlined text-[20px] shrink-0" style="font-variation-settings:'FILL' 1;">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            @if(!config('services.auth.developer_mode'))
            <div class="mb-5 bg-blue-50 border border-blue-200 text-blue-700 rounded-2xl px-5 py-4 text-sm flex items-center gap-2.5">
                <span class="material-symbols-outlined text-[20px] shrink-0" style="font-variation-settings:'FILL' 1;">shield</span>
                <span>Access restricted to <strong>@{{ config('services.auth.allowed_email_domain') }}</strong> accounts only.</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Email Address</label>
                    <div class="relative input-wrap">
                        <span class="input-icon material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl transition-colors duration-200">mail</span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               placeholder="name@deped.gov.ph" required autocomplete="email"
                               class="auth-input w-full rounded-2xl pl-12 pr-4 py-4 text-gray-800 placeholder-gray-400 text-sm focus:outline-none">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Password</label>
                    <div class="relative input-wrap">
                        <span class="input-icon material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl transition-colors duration-200">lock</span>
                        <input id="password" type="password" name="password"
                               placeholder="••••••••" required autocomplete="current-password"
                               class="auth-input w-full rounded-2xl pl-12 pr-12 py-4 text-gray-800 placeholder-gray-400 text-sm focus:outline-none">
                        <button type="button" onclick="togglePwd('password','eye-login')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#2979ff] transition-colors">
                            <span id="eye-login" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                {{-- Remember + Forgot --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="remember"
                               class="w-4 h-4 rounded accent-blue-600 cursor-pointer">
                        <span class="text-sm text-gray-500">Remember Me</span>
                    </label>
                    <a href="{{ route('password.request') }}"
                       class="text-sm text-[#2979ff] hover:text-[#0d326b] font-semibold transition-colors">
                        Forgot password?
                    </a>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="btn-gradient w-full py-4 rounded-full font-bold text-white text-sm tracking-widest uppercase flex items-center justify-center gap-2 mt-2">
                    Sign In
                    <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">arrow_forward</span>
                </button>
            </form>

            {{-- Divider --}}
            <div class="flex items-center my-7 gap-3">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-gray-400 text-xs font-semibold tracking-wide">OR CONTINUE WITH</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            {{-- Google --}}
            <a href="{{ route('auth.google', ['intent' => 'login']) }}"
               class="google-btn w-full flex items-center justify-center gap-3 py-4 px-4 rounded-2xl border border-gray-200 bg-white font-semibold text-gray-700 text-sm">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M23.745 12.27c0-.79-.07-1.54-.19-2.27h-11.3v4.51h6.47c-.29 1.48-1.14 2.73-2.4 3.58v3h3.86c2.26-2.09 3.56-5.17 3.56-8.82z"/>
                    <path fill="#34A853" d="M12.255 24c3.24 0 5.95-1.08 7.93-2.91l-3.86-3c-1.08.72-2.45 1.16-4.07 1.16-3.13 0-5.78-2.11-6.73-4.96h-3.98v3.09C3.515 21.3 7.615 24 12.255 24z"/>
                    <path fill="#FBBC05" d="M5.525 14.29c-.25-.72-.38-1.49-.38-2.29s.14-1.57.38-2.29V6.62h-3.98a11.86 11.86 0 000 10.76l3.98-3.09z"/>
                    <path fill="#EA4335" d="M12.255 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C18.205 1.19 15.495 0 12.255 0c-4.64 0-8.74 2.7-10.71 6.62l3.98 3.09c.95-2.85 3.6-4.96 6.73-4.96z"/>
                </svg>
                Sign in with Google
            </a>

            <p class="text-center text-sm text-gray-400 mt-7">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-[#2979ff] hover:text-[#0d326b] font-bold transition-colors">Create one</a>
            </p>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
<div id="login-loading-overlay" class="fixed inset-0 z-50 flex items-center justify-center hidden"
     style="background:rgba(10,37,80,0.7);backdrop-filter:blur(10px);">
    <div class="bg-white rounded-3xl px-12 py-10 max-w-xs w-full shadow-2xl text-center flex flex-col items-center gap-4">
        <div class="relative w-20 h-20 flex items-center justify-center mb-1">
            <div class="absolute inset-0 rounded-full border-[5px] border-slate-100 border-t-[#2979ff]"
                 style="animation:spin-loader 0.9s linear infinite;"></div>
            <span class="material-symbols-outlined text-3xl text-[#1C3D7A]" style="font-variation-settings:'FILL' 1;">shield_person</span>
        </div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Signing In</p>
            <h3 id="loading-teacher-name" class="text-[18px] font-extrabold text-[#0d326b] leading-snug">Logging in...</h3>
        </div>
        <div class="flex gap-1.5 mt-1">
            <span class="w-2 h-2 rounded-full bg-[#2979ff]" style="animation:dot-bounce 1.2s infinite 0s;"></span>
            <span class="w-2 h-2 rounded-full bg-[#2979ff]" style="animation:dot-bounce 1.2s infinite 0.2s;"></span>
            <span class="w-2 h-2 rounded-full bg-[#2979ff]" style="animation:dot-bounce 1.2s infinite 0.4s;"></span>
        </div>
    </div>
</div>

<script>
function togglePwd(inputId, iconId) {
    const el = document.getElementById(inputId);
    const ic = document.getElementById(iconId);
    el.type = el.type === 'password' ? 'text' : 'password';
    ic.textContent = el.type === 'password' ? 'visibility' : 'visibility_off';
}

document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.querySelector('form[action="{{ route('login') }}"]');
    const loadingOverlay = document.getElementById('login-loading-overlay');
    const loadingTeacherName = document.getElementById('loading-teacher-name');

    if (loginForm) {
        loginForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const emailInput = document.getElementById('email').value.trim();
            loadingTeacherName.textContent = 'Logging in...';
            loadingOverlay.classList.remove('hidden');

            if (emailInput) {
                try {
                    const res = await fetch("{{ route('api.teacher-name') }}?email=" + encodeURIComponent(emailInput));
                    const data = await res.json();
                    if (data && data.name) {
                        const label = data.role === 'admin' ? 'Admin' : 'Teacher';
                        loadingTeacherName.textContent = 'Welcome, ' + label + ' ' + data.name;
                    }
                } catch (_) {}
            }
            setTimeout(function () { loginForm.submit(); }, 800);
        });
    }

    const googleLink = document.querySelector('a[href*="auth.google"]');
    if (googleLink) {
        googleLink.addEventListener('click', function () {
            loadingTeacherName.textContent = 'Connecting to Google...';
            loadingOverlay.classList.remove('hidden');
        });
    }
});
</script>
@endsection
