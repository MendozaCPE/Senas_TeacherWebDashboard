@extends('layouts.auth')
@section('title', 'Reset Password')

@section('content')
<div class="flex min-h-screen">

    {{-- ── LEFT PANEL ── --}}
    <div class="hidden lg:flex lg:w-5/12 flex-col items-center justify-center"
         style="background: linear-gradient(180deg, #62C8F8 0%, #3A9EE4 45%, #2260C4 100%);">

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

            <a href="{{ route('login') }}"
               class="inline-flex items-center text-gray-400 hover:text-gray-600 transition mb-4">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>

            <h2 class="text-3xl font-extrabold text-gray-900 mb-1">Reset your password</h2>
            <p class="text-gray-400 text-sm mb-8">Choose a new password to regain access to your account.</p>

            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-600 rounded-xl px-4 py-3 text-sm">
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

                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">mail</span>
                        <input id="email" type="email" name="email" value="{{ old('email', $email ?? '') }}"
                               placeholder="name@deped.gov.ph" required autocomplete="email"
                               class="w-full bg-gray-100 rounded-2xl pl-12 pr-4 py-4 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">New Password</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">lock</span>
                        <input id="password" type="password" name="password"
                               placeholder="••••••••" required autocomplete="new-password"
                               class="w-full bg-gray-100 rounded-2xl pl-12 pr-12 py-4 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                        <button type="button" onclick="togglePwd('password','eye-reset')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span id="eye-reset" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Confirm Password</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">lock</span>
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               placeholder="••••••••" required autocomplete="new-password"
                               class="w-full bg-gray-100 rounded-2xl pl-12 pr-12 py-4 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                        <button type="button" onclick="togglePwd('password_confirmation','eye-reset-confirm')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span id="eye-reset-confirm" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-4 rounded-full font-bold text-white text-sm tracking-wide transition hover:opacity-90 active:scale-95 flex items-center justify-center gap-2"
                        style="background:#1C3D7A;">
                    Reset Password
                    <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">lock_reset</span>
                </button>
            </form>

            <p class="text-center text-sm text-gray-400 mt-6">
                Remembered your password?
                <a href="{{ route('login') }}" class="text-blue-500 hover:text-blue-700 font-semibold transition">Log in</a>
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
