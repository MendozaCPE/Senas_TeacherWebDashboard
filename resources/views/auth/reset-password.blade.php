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

/* ── Password strength meter (auth pages) ── */
.auth-pwd-bar { height: 4px; flex: 1; border-radius: 99px; background: #e8eef4; transition: background 0.3s; }
.auth-pwd-bar.weak   { background: #ef4444; }
.auth-pwd-bar.fair   { background: #f59e0b; }
.auth-pwd-bar.good   { background: #3b82f6; }
.auth-pwd-bar.strong { background: #10b981; }
.auth-pwd-req { display: flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 600; color: #9ca3af; transition: color 0.2s; }
.auth-pwd-req .auth-req-icon { font-size: 13px; transition: color 0.2s; }
.auth-pwd-req.met { color: #059669; }
.auth-pwd-req.met .auth-req-icon { color: #059669; }
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
                               placeholder="At least 10 characters" required autocomplete="new-password"
                               oninput="authEvalStrength(this.value)"
                               class="auth-input w-full rounded-2xl pl-12 pr-12 py-4 text-gray-800 placeholder-gray-400 text-sm focus:outline-none">
                        <button type="button" onclick="togglePwd('password','eye-reset')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#2979ff] transition-colors">
                            <span id="eye-reset" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>

                    {{-- Strength bar --}}
                    <div id="auth-strength-wrap" class="mt-2.5 hidden">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="flex gap-1 flex-1">
                                <div class="auth-pwd-bar" id="auth-bar-1"></div>
                                <div class="auth-pwd-bar" id="auth-bar-2"></div>
                                <div class="auth-pwd-bar" id="auth-bar-3"></div>
                                <div class="auth-pwd-bar" id="auth-bar-4"></div>
                            </div>
                            <span id="auth-strength-label" class="text-[11px] font-extrabold tracking-wide uppercase min-w-[46px] text-right"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                            <div class="auth-pwd-req" id="auth-req-len"><span class="material-symbols-outlined auth-req-icon">radio_button_unchecked</span><span>10+ characters</span></div>
                            <div class="auth-pwd-req" id="auth-req-upper"><span class="material-symbols-outlined auth-req-icon">radio_button_unchecked</span><span>Uppercase (A–Z)</span></div>
                            <div class="auth-pwd-req" id="auth-req-lower"><span class="material-symbols-outlined auth-req-icon">radio_button_unchecked</span><span>Lowercase (a–z)</span></div>
                            <div class="auth-pwd-req" id="auth-req-num"><span class="material-symbols-outlined auth-req-icon">radio_button_unchecked</span><span>Number (0–9)</span></div>
                            <div class="auth-pwd-req" id="auth-req-sym"><span class="material-symbols-outlined auth-req-icon">radio_button_unchecked</span><span>Special character</span></div>
                            <div class="auth-pwd-req" id="auth-req-nocommon"><span class="material-symbols-outlined auth-req-icon">radio_button_unchecked</span><span>Not a common password</span></div>
                        </div>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Confirm Password</label>
                    <div class="relative input-wrap">
                        <span class="input-icon material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl transition-colors duration-200">lock</span>
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               placeholder="Re-enter new password" required autocomplete="new-password"
                               oninput="authEvalConfirm()"
                               class="auth-input w-full rounded-2xl pl-12 pr-12 py-4 text-gray-800 placeholder-gray-400 text-sm focus:outline-none">
                        <button type="button" onclick="togglePwd('password_confirmation','eye-reset-confirm')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#2979ff] transition-colors">
                            <span id="eye-reset-confirm" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                    <p id="auth-match-msg" class="hidden mt-1.5 text-[12px] font-semibold flex items-center gap-1"></p>
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

const AUTH_COMMON_PASSWORDS = ['password','password1','12345678','123456789','qwerty123','letmein1','welcome1','admin1234','iloveyou1','sunshine1'];

function authEvalStrength(val) {
    const wrap  = document.getElementById('auth-strength-wrap');
    const label = document.getElementById('auth-strength-label');
    const bars  = [1,2,3,4].map(n => document.getElementById('auth-bar-' + n));
    if (!val) { wrap.classList.add('hidden'); authEvalConfirm(); return; }
    wrap.classList.remove('hidden');

    const checks = {
        'auth-req-len':      val.length >= 10,
        'auth-req-upper':    /[A-Z]/.test(val),
        'auth-req-lower':    /[a-z]/.test(val),
        'auth-req-num':      /[0-9]/.test(val),
        'auth-req-sym':      /[^A-Za-z0-9]/.test(val),
        'auth-req-nocommon': !AUTH_COMMON_PASSWORDS.includes(val.toLowerCase()),
    };
    Object.entries(checks).forEach(([id, met]) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('met', met);
        el.querySelector('.auth-req-icon').textContent = met ? 'check_circle' : 'radio_button_unchecked';
    });

    const score = Object.values(checks).filter(Boolean).length + (val.length >= 14 ? 1 : 0);
    const level = score <= 2 ? 1 : score <= 3 ? 2 : score <= 5 ? 3 : 4;
    const meta  = [null,
        { cls: 'weak',   color: '#ef4444', text: 'Weak'   },
        { cls: 'fair',   color: '#f59e0b', text: 'Fair'   },
        { cls: 'good',   color: '#3b82f6', text: 'Good'   },
        { cls: 'strong', color: '#10b981', text: 'Strong' },
    ];
    bars.forEach((bar, i) => {
        bar.className = 'auth-pwd-bar';
        if (i < level) bar.classList.add(meta[level].cls);
    });
    label.textContent = meta[level].text;
    label.style.color = meta[level].color;
    authEvalConfirm();
}

function authEvalConfirm() {
    const pw  = document.getElementById('password')?.value              || '';
    const cfm = document.getElementById('password_confirmation')?.value || '';
    const msg = document.getElementById('auth-match-msg');
    if (!msg) return;
    if (!cfm) { msg.classList.add('hidden'); return; }
    msg.classList.remove('hidden');
    if (pw === cfm) {
        msg.innerHTML = '<span class="material-symbols-outlined" style="font-size:13px;color:#059669;">check_circle</span><span style="color:#059669;">Passwords match</span>';
    } else {
        msg.innerHTML = '<span class="material-symbols-outlined" style="font-size:13px;color:#ef4444;">cancel</span><span style="color:#ef4444;">Passwords do not match</span>';
    }
}
</script>

@endsection
