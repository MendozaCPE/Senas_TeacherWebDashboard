@extends('layouts.auth')
@section('title', 'Sign Up')

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
    <div class="flex-1 flex items-center justify-center bg-white px-6 py-12 overflow-y-auto">
        <div class="w-full max-w-md">

            <a href="{{ route('login') }}"
               class="inline-flex items-center text-gray-400 hover:text-gray-600 transition mb-4">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>

            <h2 class="text-3xl font-extrabold text-gray-900 mb-1">Sign Up</h2>
            <p class="text-gray-400 text-sm mb-8">Welcome! Let's get your account ready to manage students and lessons.</p>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-600 rounded-xl px-4 py-3 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Name</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">person</span>
                        <input type="text" name="name" value="{{ old('name') }}"
                               placeholder="Full name" required autocomplete="name"
                               class="w-full bg-gray-100 rounded-2xl pl-12 pr-4 py-4 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">mail</span>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="name@deped.gov.ph" required autocomplete="email"
                               class="w-full bg-gray-100 rounded-2xl pl-12 pr-4 py-4 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Password</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">lock</span>
                        <input id="pwd" type="password" name="password"
                               placeholder="••••••••" required autocomplete="new-password"
                               class="w-full bg-gray-100 rounded-2xl pl-12 pr-12 py-4 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                        <button type="button" onclick="togglePwd('pwd','eye1')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span id="eye1" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Confirm Password</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">lock</span>
                        <input id="pwd2" type="password" name="password_confirmation"
                               placeholder="••••••••" required autocomplete="new-password"
                               class="w-full bg-gray-100 rounded-2xl pl-12 pr-12 py-4 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                        <button type="button" onclick="togglePwd('pwd2','eye2')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span id="eye2" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                {{-- Institution --}}
                <div>
                    <label class="block text-xs font-bold tracking-widest text-gray-500 uppercase mb-2">Institution</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">account_balance</span>
                        <select name="school_id" required
                                class="w-full bg-gray-100 rounded-2xl pl-12 pr-10 py-4 text-gray-700 text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-blue-400 transition cursor-pointer">
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
                                class="text-blue-500 hover:text-blue-700 font-semibold transition underline underline-offset-2">
                            Terms and Conditions
                        </button>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full py-4 rounded-full font-bold text-white text-sm tracking-wide transition hover:opacity-90 active:scale-95 flex items-center justify-center gap-2"
                        style="background:#1C3D7A;">
                    Sign Up
                    <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">arrow_forward</span>
                </button>
            </form>

            {{-- Divider --}}
            <div class="flex items-center my-5 gap-3">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-gray-400 text-xs">or</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <div class="flex items-center justify-center gap-2 text-sm text-gray-400">
                or sign in with
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

{{-- ── TERMS & CONDITIONS MODAL ── --}}
<div id="terms-modal"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col overflow-hidden">

        {{-- Modal header --}}
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

        {{-- Modal body (scrollable) --}}
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

        {{-- Modal footer --}}
        <div class="px-8 py-6 border-t border-gray-100 flex-shrink-0">
            <button id="agree-btn"
                    class="w-full py-4 rounded-full font-bold text-white text-sm tracking-wide flex items-center justify-center gap-2 transition hover:opacity-90 active:scale-95"
                    style="background:#1C3D7A;">
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

// Click backdrop to close
modal.addEventListener('click', (e) => {
    if (e.target === modal) modal.classList.add('hidden');
});

// "I Agree" button checks the checkbox and closes modal
agreeBtn.addEventListener('click', () => {
    termsCheck.checked = true;
    modal.classList.add('hidden');
});
</script>
@endsection
