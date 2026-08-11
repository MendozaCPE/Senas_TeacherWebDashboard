@extends('layouts.auth')
@section('title', 'Sign Up')

@section('content')

<style>
/* ── Shared auth styles (same as login) ── */
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

@keyframes blob-move {
    0%, 100% { transform: translate(0,0) scale(1); }
    33%       { transform: translate(28px,-22px) scale(1.07); }
    66%       { transform: translate(-20px,16px) scale(0.94); }
}
.blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(90px);
    opacity: 0.10;
    animation: blob-move 14s ease-in-out infinite;
    pointer-events: none;
}
.blob-1 { width:400px;height:400px;background:#2979ff;top:-90px;right:-70px;animation-delay:0s; }
.blob-2 { width:300px;height:300px;background:#0d326b;bottom:60px;right:30px;animation-delay:-6s; }
.blob-3 { width:240px;height:240px;background:#3A9EE4;top:38%;left:-45px;animation-delay:-10s; }

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

@keyframes card-in {
    from { opacity:0; transform:translateY(28px); }
    to   { opacity:1; transform:translateY(0); }
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

        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[-90px] left-[-90px] w-80 h-80 rounded-full opacity-10"
                 style="background:radial-gradient(circle,#fff,transparent);"></div>
            <div class="absolute bottom-[-70px] right-[-70px] w-72 h-72 rounded-full opacity-10"
                 style="background:radial-gradient(circle,#fff,transparent);"></div>
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

            <a href="{{ route('login') }}"
               class="inline-flex items-center text-gray-400 hover:text-[#2979ff] transition-colors mb-7 group">
                <span class="material-symbols-outlined text-xl mr-1.5 group-hover:-translate-x-0.5 transition-transform">arrow_back</span>
                <span class="text-sm font-medium">Back to Login</span>
            </a>

            {{-- Header --}}
            <div class="mb-8">
                <h2 class="text-4xl font-extrabold text-gray-900 mb-2 leading-tight">Create Account</h2>
                <p class="text-gray-400 text-base">Let's get your teacher account set up.</p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 text-red-600 rounded-2xl px-5 py-4 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!config('services.auth.developer_mode'))
            <div class="mb-5 bg-blue-50 border border-blue-200 text-blue-700 rounded-2xl px-5 py-4 text-sm flex items-center gap-2.5">
                <span class="material-symbols-outlined text-[20px] shrink-0" style="font-variation-settings:'FILL' 1;">shield</span>
                <span>Registration restricted to <strong>@{{ config('services.auth.allowed_email_domain') }}</strong> accounts only.</span>
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Full Name</label>
                    <div class="relative input-wrap">
                        <span class="input-icon material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl transition-colors duration-200">person</span>
                        <input type="text" name="name" value="{{ old('name') }}"
                               placeholder="Your full name" required autocomplete="name"
                               class="auth-input w-full rounded-2xl pl-12 pr-4 py-4 text-gray-800 placeholder-gray-400 text-sm focus:outline-none">
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Email Address</label>
                    <div class="relative input-wrap">
                        <span class="input-icon material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl transition-colors duration-200">mail</span>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="name@deped.gov.ph" required autocomplete="email"
                               class="auth-input w-full rounded-2xl pl-12 pr-4 py-4 text-gray-800 placeholder-gray-400 text-sm focus:outline-none">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Password</label>
                    <div class="relative input-wrap">
                        <span class="input-icon material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl transition-colors duration-200">lock</span>
                        <input id="pwd" type="password" name="password"
                               placeholder="••••••••" required autocomplete="new-password"
                               class="auth-input w-full rounded-2xl pl-12 pr-12 py-4 text-gray-800 placeholder-gray-400 text-sm focus:outline-none">
                        <button type="button" onclick="togglePwd('pwd','eye1')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#2979ff] transition-colors">
                            <span id="eye1" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Confirm Password</label>
                    <div class="relative input-wrap">
                        <span class="input-icon material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl transition-colors duration-200">lock</span>
                        <input id="pwd2" type="password" name="password_confirmation"
                               placeholder="••••••••" required autocomplete="new-password"
                               class="auth-input w-full rounded-2xl pl-12 pr-12 py-4 text-gray-800 placeholder-gray-400 text-sm focus:outline-none">
                        <button type="button" onclick="togglePwd('pwd2','eye2')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#2979ff] transition-colors">
                            <span id="eye2" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                {{-- Institution --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Institution</label>
                    <div class="relative input-wrap">
                        <span class="input-icon material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl transition-colors duration-200 z-10">account_balance</span>
                        <select name="school_id" required
                                class="auth-input w-full rounded-2xl pl-12 pr-10 py-4 text-gray-800 text-sm appearance-none focus:outline-none cursor-pointer">
                            <option value="" disabled {{ old('school_id') ? '' : 'selected' }}>Select your school</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                </div>

                {{-- Terms Checkbox --}}
                <div class="flex items-start gap-3 pt-1">
                    <input type="checkbox" id="terms" name="terms" required
                           class="mt-0.5 w-4 h-4 rounded accent-blue-600 cursor-pointer flex-shrink-0">
                    <label for="terms" class="text-sm text-gray-500 cursor-pointer leading-snug select-none">
                        I agree to the
                        <button type="button" id="open-terms"
                                class="text-[#2979ff] hover:text-[#0d326b] font-bold transition underline underline-offset-2">
                            Terms and Conditions
                        </button>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="btn-gradient w-full py-4 rounded-full font-bold text-white text-sm tracking-widest uppercase flex items-center justify-center gap-2">
                    Create Account
                    <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">arrow_forward</span>
                </button>
            </form>

            {{-- Divider --}}
            <div class="flex items-center my-6 gap-3">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-gray-400 text-xs font-semibold tracking-wide">OR CONTINUE WITH</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            {{-- Google Sign-Up --}}
            <a href="{{ route('auth.google', ['intent' => 'register']) }}"
               class="google-btn w-full flex items-center justify-center gap-3 py-4 px-4 rounded-2xl border border-gray-200 bg-white font-semibold text-gray-700 text-sm">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M23.745 12.27c0-.79-.07-1.54-.19-2.27h-11.3v4.51h6.47c-.29 1.48-1.14 2.73-2.4 3.58v3h3.86c2.26-2.09 3.56-5.17 3.56-8.82z"/>
                    <path fill="#34A853" d="M12.255 24c3.24 0 5.95-1.08 7.93-2.91l-3.86-3c-1.08.72-2.45 1.16-4.07 1.16-3.13 0-5.78-2.11-6.73-4.96h-3.98v3.09C3.515 21.3 7.615 24 12.255 24z"/>
                    <path fill="#FBBC05" d="M5.525 14.29c-.25-.72-.38-1.49-.38-2.29s.14-1.57.38-2.29V6.62h-3.98a11.86 11.86 0 000 10.76l3.98-3.09z"/>
                    <path fill="#EA4335" d="M12.255 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C18.205 1.19 15.495 0 12.255 0c-4.64 0-8.74 2.7-10.71 6.62l3.98 3.09c.95-2.85 3.6-4.96 6.73-4.96z"/>
                </svg>
                Sign up with Google
            </a>

            <p class="text-center text-sm text-gray-400 mt-6">
                Already have an account?
                <a href="{{ route('login') }}" class="text-[#2979ff] hover:text-[#0d326b] font-bold transition-colors">Sign in</a>
            </p>
        </div>
    </div>
</div>

{{-- ── TERMS & CONDITIONS MODAL ── --}}
<div id="terms-modal"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col overflow-hidden">

        <div class="flex items-center justify-between px-8 pt-8 pb-4 border-b border-gray-100 flex-shrink-0">
            <div>
                <h3 class="text-2xl font-extrabold text-gray-900">Terms & Conditions</h3>
                <p class="text-xs text-gray-400 mt-0.5">Please read before using <strong>SEÑAS</strong>.</p>
            </div>
            <button id="close-terms"
                    class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <div class="overflow-y-auto px-8 py-6 text-sm text-gray-600 leading-relaxed space-y-5 flex-1">
            <p>Welcome to <strong>SEÑAS</strong>! This app is designed to help students learn Filipino Sign Language with personalized lessons and interactive feedback. By using this app, you agree to the following terms:</p>
            <div>
                <p class="font-bold text-gray-800 mb-1">1. Student Access</p>
                <ul class="list-disc list-inside text-gray-500 space-y-1">
                    <li>Students can only access the app if enrolled by a teacher.</li>
                    <li>Each student will have a unique account assigned by their teacher.</li>
                    <li>There is no self-registration for students.</li>
                </ul>
            </div>
            <div>
                <p class="font-bold text-gray-800 mb-1">2. Privacy & Data</p>
                <ul class="list-disc list-inside text-gray-500 space-y-1">
                    <li>SEÑAS does not collect personal data beyond what is necessary for learning.</li>
                    <li>Progress and quiz results are recorded only for educational purposes.</li>
                    <li>No information is shared outside the platform.</li>
                    <li>Camera usage is optional and only for gesture recognition during practice.</li>
                </ul>
            </div>
            <div>
                <p class="font-bold text-gray-800 mb-1">3. Tracking & Progress</p>
                <ul class="list-disc list-inside text-gray-500 space-y-1">
                    <li>Students' learning progress is tracked within the app to provide personalized lessons.</li>
                    <li>Teachers can monitor student progress for instructional purposes only.</li>
                    <li>All data is kept secure and private.</li>
                </ul>
            </div>
            <div>
                <p class="font-bold text-gray-800 mb-1">4. Using the App</p>
                <ul class="list-disc list-inside text-gray-500 space-y-1">
                    <li>SEÑAS provides interactive lessons, quizzes, and gesture recognition.</li>
                    <li>Students are encouraged to practice, but participation is optional.</li>
                </ul>
            </div>
            <div>
                <p class="font-bold text-gray-800 mb-1">5. Disclaimers</p>
                <ul class="list-disc list-inside text-gray-500 space-y-1">
                    <li>The app is designed for educational purposes only.</li>
                    <li>SEÑAS and its developers are not responsible for misuse of the app.</li>
                </ul>
            </div>
            <div>
                <p class="font-bold text-gray-800 mb-1">6. Acceptance</p>
                <p class="text-gray-500">By using SEÑAS, you agree to these terms. For questions, please contact your teacher or help support.</p>
            </div>
        </div>

        <div class="px-8 py-6 border-t border-gray-100 flex-shrink-0">
            <button id="agree-btn"
                    class="btn-gradient w-full py-4 rounded-full font-bold text-white text-sm tracking-widest uppercase flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">check_circle</span>
                I Agree
            </button>
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

const modal      = document.getElementById('terms-modal');
const openBtn    = document.getElementById('open-terms');
const closeBtn   = document.getElementById('close-terms');
const agreeBtn   = document.getElementById('agree-btn');
const termsCheck = document.getElementById('terms');

openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.add('hidden'); });
agreeBtn.addEventListener('click', () => {
    termsCheck.checked = true;
    modal.classList.add('hidden');
});
</script>
@endsection
