@extends('layouts.auth')
@section('title', 'Terms & Conditions')

@section('content')
<div class="flex" style="min-height:calc(100vh / 0.9)">

    {{-- ── LEFT PANEL ── --}}
    <div class="hidden lg:flex lg:w-5/12 flex-col items-center justify-center"
         style="background: linear-gradient(160deg, #5BB8F5 0%, #2A7FD4 50%, #1C5EAF 100%);">
        <h1 class="text-white font-black text-5xl tracking-[0.25em] mb-10 drop-shadow-lg">SEÑAS</h1>
        <div class="bg-white rounded-[2.5rem] shadow-2xl flex items-end justify-center overflow-hidden"
             style="width: 200px; height: 270px; padding: 0 12px 0 12px;">
            <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya mascot"
                 class="w-full object-contain object-bottom" style="max-height: 260px;">
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="flex-1 flex items-start justify-center bg-white px-6 py-12 overflow-y-auto">
        <div class="w-full max-w-lg">

            {{-- Back + Title --}}
            <a href="{{ route('register') }}"
               class="inline-flex items-center text-gray-400 hover:text-gray-600 transition mb-4">
                <span class="material-symbols-outlined text-xl mr-1">arrow_back</span>
            </a>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-1">Terms & Conditions</h2>
            <p class="text-gray-400 text-sm mb-8">Please read before using <strong class="text-gray-600">SEÑAS</strong>.</p>

            <div class="prose prose-sm text-gray-600 space-y-5 text-sm leading-relaxed">

                <p>Welcome to <strong>SEÑAS</strong>! This app is designed to help students learn Filipino Sign Language with personalized lessons and interactive feedback. By using this app, you agree to the following terms:</p>

                <div>
                    <p class="font-bold text-gray-800">1. Student Access</p>
                    <ul class="list-disc list-inside mt-1 space-y-1 text-gray-500">
                        <li>Students can only access the app if enrolled by a teacher.</li>
                        <li>Each student will have a unique account assigned by their teacher.</li>
                        <li>There is no self-registration for students.</li>
                    </ul>
                </div>

                <div>
                    <p class="font-bold text-gray-800">2. Privacy & Data</p>
                    <ul class="list-disc list-inside mt-1 space-y-1 text-gray-500">
                        <li>SEÑAS does not collect personal data beyond what is necessary for learning.</li>
                        <li>Progress and quiz results are recorded only for educational purposes.</li>
                        <li>No information is shared outside the platform.</li>
                        <li>Camera usage is optional and only for gesture recognition during practice.</li>
                    </ul>
                </div>

                <div>
                    <p class="font-bold text-gray-800">3. Tracking & Progress</p>
                    <ul class="list-disc list-inside mt-1 space-y-1 text-gray-500">
                        <li>Students' learning progress is tracked within the app to provide personalized lessons.</li>
                        <li>Teachers can monitor student progress for instructional purposes only.</li>
                        <li>All data is kept secure and private.</li>
                    </ul>
                </div>

                <div>
                    <p class="font-bold text-gray-800">4. Using the App</p>
                    <ul class="list-disc list-inside mt-1 space-y-1 text-gray-500">
                        <li>SEÑAS provides interactive lessons, quizzes, and gesture recognition.</li>
                        <li>Students are encouraged to practice, but participation is optional, and they can skip lessons or features like camera usage.</li>
                    </ul>
                </div>

                <div>
                    <p class="font-bold text-gray-800">5. Disclaimers</p>
                    <ul class="list-disc list-inside mt-1 space-y-1 text-gray-500">
                        <li>The app is designed for educational purposes only.</li>
                        <li>SEÑAS and its developers are not responsible for misuse of the app.</li>
                    </ul>
                </div>

                <div>
                    <p class="font-bold text-gray-800">6. Acceptance</p>
                    <p class="text-gray-500 mt-1">By using SEÑAS, you agree to these terms. For questions, please contact your teacher or help support.</p>
                </div>

            </div>

            <div class="mt-8">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 py-3 px-8 rounded-full font-bold text-white text-sm tracking-wide transition hover:opacity-90 active:scale-95"
                   style="background: #1C3D7A;">
                    <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">arrow_back</span>
                    Back to Sign Up
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
