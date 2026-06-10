@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<div class="flex min-h-screen">

    {{-- ── LEFT PANEL ── --}}
    <div class="hidden lg:flex lg:w-5/12 flex-col items-center justify-center"
         style="background: linear-gradient(180deg, #62C8F8 0%, #3A9EE4 45%, #2260C4 100%);">

        <h1 class="text-white font-black text-5xl tracking-[0.25em] mb-8 drop-shadow-lg select-none">SEÑAS</h1>

        {{-- Mascot overlapping the white card --}}
        <div class="relative" style="width: 220px; height: 310px;">
            {{-- White phone-shaped card --}}
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 bg-white rounded-[2.5rem] shadow-2xl"
                 style="width: 195px; height: 265px;"></div>
            {{-- Senya overflows above the card --}}
            <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya mascot"
                 class="absolute bottom-0 left-1/2 -translate-x-1/2 z-10 w-full object-contain object-bottom select-none"
                 style="height: 310px;" draggable="false">
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

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-600 rounded-xl px-4 py-3 text-sm">
                    {{ $errors->first() }}
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
                    <a href="#" class="text-sm text-blue-500 hover:text-blue-700 font-medium transition">Forgot password?</a>
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
                <span class="text-gray-400 text-xs">or</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <p class="text-center text-sm text-gray-400 mb-4">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-blue-500 hover:text-blue-700 font-semibold transition">Create one</a>
            </p>

            <div class="flex items-center justify-center gap-2 text-sm text-gray-400">
                or log in with
                <button type="button" class="ml-1 w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center hover:shadow transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M23.745 12.27c0-.79-.07-1.54-.19-2.27h-11.3v4.51h6.47c-.29 1.48-1.14 2.73-2.4 3.58v3h3.86c2.26-2.09 3.56-5.17 3.56-8.82z"/>
                        <path fill="#34A853" d="M12.255 24c3.24 0 5.95-1.08 7.93-2.91l-3.86-3c-1.08.72-2.45 1.16-4.07 1.16-3.13 0-5.78-2.11-6.73-4.96h-3.98v3.09C3.515 21.3 7.615 24 12.255 24z"/>
                        <path fill="#FBBC05" d="M5.525 14.29c-.25-.72-.38-1.49-.38-2.29s.14-1.57.38-2.29V6.62h-3.98a11.86 11.86 0 000 10.76l3.98-3.09z"/>
                        <path fill="#EA4335" d="M12.255 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C18.205 1.19 15.495 0 12.255 0c-4.64 0-8.74 2.7-10.71 6.62l3.98 3.09c.95-2.85 3.6-4.96 6.73-4.96z"/>
                    </svg>
                </button>
            </div>

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
</script>
@endsection
