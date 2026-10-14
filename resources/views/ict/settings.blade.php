@extends('layouts.ict')
@section('title', 'Settings')
@section('content')

<div class="ict-settings-page flex flex-col gap-5 w-full pt-4 pb-6">

    {{-- ── PAGE HEADER ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[.12em] text-[#1a6fd4]">Account</p>
            <h1 class="text-[26px] font-black text-[#0d326b] leading-tight">Settings</h1>
        </div>
        <span class="hidden sm:inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-[12px] font-bold text-[#0d326b]">
            <span class="material-symbols-outlined text-[17px]">admin_panel_settings</span>ICT Coordinator
        </span>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-5 py-3.5 bg-emerald-50 border border-emerald-100 rounded-2xl">
        <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
        <p class="text-[13px] font-semibold text-emerald-700">{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 px-5 py-3.5 bg-red-50 border border-red-100 rounded-2xl">
        <span class="material-symbols-outlined text-red-500 text-[20px]">error</span>
        <p class="text-[13px] font-semibold text-red-600">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Profile summary follows the teacher settings layout; editable fields remain in their own forms below. --}}
    <section class="ict-settings-hero relative overflow-hidden rounded-[20px] px-6 py-6 sm:px-7">
        <div class="absolute -top-20 right-8 h-52 w-52 rounded-full bg-white/[.06]"></div>
        <div class="absolute -bottom-24 right-1/3 h-40 w-40 rounded-full bg-amber-400/[.10]"></div>
        <div class="relative z-10 flex flex-wrap items-end justify-between gap-6">
            <div class="flex min-w-0 items-center gap-4">
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="h-[72px] w-[72px] shrink-0 rounded-full border-[3px] border-white/90 object-cover shadow-lg"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=facc15&color=0d326b&size=128&bold=true&rounded=true'">
                <div class="min-w-0">
                    <p class="mb-1 text-[10px] font-bold uppercase tracking-[.12em] text-white/60">Account overview</p>
                    <h2 class="truncate text-[20px] font-extrabold leading-tight text-white">{{ trim(($ictProfile->first_name ?? '').' '.($ictProfile->last_name ?? '')) ?: $user->name }}</h2>
                    <p class="mt-1 flex items-center gap-1.5 truncate text-[12px] font-medium text-white/70"><span class="material-symbols-outlined text-[15px]">alternate_email</span>{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <div class="min-w-[112px] rounded-[14px] border border-white/15 bg-white/[.08] px-4 py-2.5">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-white/55">Member Since</p>
                    <p class="mt-1 flex items-center gap-1.5 text-[13px] font-extrabold text-white"><span class="material-symbols-outlined text-[15px]">calendar_month</span>{{ optional($user->created_at)->format('M Y') ?? '—' }}</p>
                </div>
                @if($school)
                <div class="min-w-[112px] rounded-[14px] border border-white/15 bg-white/[.08] px-4 py-2.5">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-white/55">Institution</p>
                    <p class="mt-1 flex items-center gap-1.5 text-[13px] font-extrabold text-white"><span class="material-symbols-outlined text-[15px]">apartment</span>{{ \Illuminate\Support\Str::limit($school->name, 18) }}</p>
                </div>
                @endif
                <div class="min-w-[112px] rounded-[14px] border border-white/15 bg-white/[.08] px-4 py-2.5">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-white/55">Sign-in Method</p>
                    <p class="mt-1 flex items-center gap-1.5 text-[13px] font-extrabold text-white"><span class="material-symbols-outlined text-[15px]">{{ $user->google_id ? 'g_translate' : 'password' }}</span>{{ $user->google_id ? 'Google' : 'Password' }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_288px] gap-5 items-start">

        {{-- ── LEFT COLUMN: forms ─────────────────────────────────────── --}}
        <div class="flex min-w-0 flex-col">

            <nav class="ict-settings-tabs" role="tablist" aria-label="Settings sections"
                 data-settings-initial="{{ $errors->has('school_name') ? 'school' : ($errors->has('first_name') || $errors->has('last_name') ? 'profile' : ($errors->has('current_password') || $errors->has('password') ? 'security' : 'profile')) }}">
                <button type="button" role="tab" class="ict-settings-tab active" data-settings-tab="profile" aria-selected="true"><span class="material-symbols-outlined">person</span>Profile</button>
                <button type="button" role="tab" class="ict-settings-tab" data-settings-tab="school" aria-selected="false"><span class="material-symbols-outlined">apartment</span>School</button>
                <button type="button" role="tab" class="ict-settings-tab" data-settings-tab="security" aria-selected="false"><span class="material-symbols-outlined">shield</span>Security</button>
            </nav>

            {{-- ── 1. SCHOOL PROFILE ───────────────────────────────────── --}}
            <div class="ict-settings-pane" data-settings-pane="school"><div class="bg-white rounded-[20px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center gap-3">
                    <div class="w-[38px] h-[38px] rounded-xl flex items-center justify-center flex-shrink-0 bg-[#eaf1fb] text-[#0d326b]">
                        <span class="material-symbols-outlined text-[19px]">business</span>
                    </div>
                    <div>
                        <p class="text-[17px] font-extrabold text-[#0d326b]">School Profile</p>
                        <p class="text-[12px] font-medium text-slate-500">Update your school's registration details</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('ict.settings.school') }}" class="p-6 flex flex-col gap-4">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">School Name</label>
                            <input type="text" name="school_name" value="{{ old('school_name', $school->name ?? '') }}" required
                                   class="w-full px-4 py-2.5 rounded-2xl border text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition
                                   {{ $errors->has('school_name') ? 'border-red-300' : 'border-slate-200' }}">
                            @error('school_name')<p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Address</label>
                            <input type="text" name="school_address" value="{{ old('school_address', $school->address ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Region</label>
                            <input type="text" name="region" value="{{ old('region', $school->region ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Division</label>
                            <input type="text" name="division" value="{{ old('division', $school->division ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
                        </div>
                    </div>
                    <div class="flex justify-end pt-1">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl text-[13px] font-bold text-white transition"
                                style="background:linear-gradient(135deg,#0d326b,#1e4b8f)">
                            <span class="material-symbols-outlined text-[16px]">save</span> Save School Details
                        </button>
                    </div>
                </form>
            </div></div>

            {{-- ── 2. PERSONAL PROFILE ─────────────────────────────────── --}}
            <div class="ict-settings-pane active" data-settings-pane="profile"><div class="bg-white rounded-[20px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center gap-3">
                    <div class="w-[38px] h-[38px] rounded-xl flex items-center justify-center flex-shrink-0 bg-[#eaf1fb] text-[#0d326b]">
                        <span class="material-symbols-outlined text-[19px]">person</span>
                    </div>
                    <div>
                        <p class="text-[17px] font-extrabold text-[#0d326b]">Personal Profile</p>
                        <p class="text-[12px] font-medium text-slate-500">Update your name and profile photo</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('ict.settings.profile') }}" enctype="multipart/form-data" class="p-6 flex flex-col gap-4">
                    @csrf @method('PATCH')

                    {{-- Avatar --}}
                    <div class="flex items-center gap-5">
                        <img id="avatarPreview"
                             src="{{ $user->avatarUrl() }}"
                             alt="Avatar"
                             class="w-16 h-16 rounded-full object-cover border-2 border-slate-200 flex-shrink-0"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0d326b&color=fff&size=128&bold=true&rounded=true'">
                        <div class="flex-1">
                            <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Profile Photo</label>
                            <label class="inline-flex items-center gap-2 cursor-pointer px-4 py-2 rounded-2xl border border-slate-200 text-[12px] font-semibold text-slate-600 bg-[#f8fafc] hover:bg-slate-100 transition">
                                <span class="material-symbols-outlined text-[15px]">upload</span> Choose Photo
                                <input type="file" name="profile_photo" accept="image/*" class="hidden"
                                       onchange="previewAvatar(this)">
                            </label>
                            <p class="text-[10px] text-slate-400 mt-1">JPG, PNG, GIF, WebP — max 5MB</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $ictProfile->first_name ?? '') }}" required
                                   class="w-full px-4 py-2.5 rounded-2xl border text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition
                                   {{ $errors->has('first_name') ? 'border-red-300' : 'border-slate-200' }}">
                            @error('first_name')<p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $ictProfile->last_name ?? '') }}" required
                                   class="w-full px-4 py-2.5 rounded-2xl border text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition
                                   {{ $errors->has('last_name') ? 'border-red-300' : 'border-slate-200' }}">
                            @error('last_name')<p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Email (read-only) --}}
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">
                            Email Address <span class="text-slate-300 font-normal normal-case">(cannot be changed)</span>
                        </label>
                        <div class="flex items-center gap-2 px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50">
                            <span class="material-symbols-outlined text-slate-300 text-[16px]">mail</span>
                            <span class="text-[13px] text-slate-500">{{ $user->email }}</span>
                            <span class="ml-auto material-symbols-outlined text-slate-300 text-[16px]">lock</span>
                        </div>
                    </div>

                    <div class="flex justify-end pt-1">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl text-[13px] font-bold text-white transition"
                                style="background:linear-gradient(135deg,#0d326b,#1e4b8f)">
                            <span class="material-symbols-outlined text-[16px]">save</span> Save Profile
                        </button>
                    </div>
                </form>
            </div></div>

            {{-- ── 3. CHANGE PASSWORD ──────────────────────────────────── --}}
            <div class="ict-settings-pane" data-settings-pane="security"><div class="bg-white rounded-[20px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center gap-3">
                    <div class="w-[38px] h-[38px] rounded-xl flex items-center justify-center flex-shrink-0 bg-[#eaf1fb] text-[#0d326b]">
                        <span class="material-symbols-outlined text-[19px]">lock</span>
                    </div>
                    <div>
                        <p class="text-[17px] font-extrabold text-[#0d326b]">Change Password</p>
                        <p class="text-[12px] font-medium text-slate-500">Must be at least 10 characters with uppercase, lowercase, number &amp; special character</p>
                    </div>
                </div>

                @if($user->google_id && empty($user->password))
                <div class="p-6 flex items-center gap-3 bg-blue-50 m-4 rounded-2xl border border-blue-100">
                    <span class="material-symbols-outlined text-[#1a6fd4] text-[20px]">info</span>
                    <p class="text-[13px] text-[#1a6fd4] font-semibold">Your account uses Google Sign-In. Manage your password through Google account settings.</p>
                </div>
                @else
                <form method="POST" action="{{ route('ict.settings.password') }}" class="p-6 flex flex-col gap-4">
                    @csrf @method('PATCH')
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Current Password</label>
                        <input type="password" name="current_password" required
                               class="w-full px-4 py-2.5 rounded-2xl border text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition
                               {{ $errors->has('current_password') ? 'border-red-300' : 'border-slate-200' }}">
                        @error('current_password')<p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">New Password</label>
                            <input type="password" name="password" id="pwField" required
                                   class="w-full px-4 py-2.5 rounded-2xl border text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition
                                   {{ $errors->has('password') ? 'border-red-300' : 'border-slate-200' }}"
                                   oninput="checkPwStrength(this.value)">
                            @error('password')<p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Confirm New Password</label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
                        </div>
                    </div>

                    {{-- Strength bar --}}
                    <div id="pwStrengthBar" class="h-1.5 rounded-full w-full bg-slate-100 transition-all hidden">
                        <div id="pwStrengthFill" class="h-1.5 rounded-full transition-all" style="width:0%;background:#ef4444"></div>
                    </div>
                    <div id="pwChecklist" class="grid grid-cols-2 gap-1.5 hidden">
                        @foreach(['pwLen'=>'10+ characters','pwUpper'=>'Uppercase letter','pwLower'=>'Lowercase letter','pwNum'=>'Number','pwSpecial'=>'Special character'] as $id => $label)
                        <div id="{{ $id }}" class="flex items-center gap-1.5 text-[11px] text-slate-400">
                            <span class="material-symbols-outlined text-[13px]">radio_button_unchecked</span>{{ $label }}
                        </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end pt-1">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl text-[13px] font-bold text-white transition"
                                style="background:linear-gradient(135deg,#0d326b,#1e4b8f)">
                            <span class="material-symbols-outlined text-[16px]">lock_reset</span> Update Password
                        </button>
                    </div>
                </form>
                @endif
            </div></div>

        </div>{{-- /left col --}}

        {{-- ── RIGHT COLUMN: profile summary ──────────────────────────── --}}
        <div class="flex flex-col gap-4">

            {{-- Profile card --}}
            <div class="bg-white rounded-[18px] shadow-sm border border-slate-100 p-5 text-center">
                <img src="{{ $user->avatarUrl() }}"
                     alt="Avatar"
                     class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-md mx-auto"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0d326b&color=fff&size=128&bold=true&rounded=true'">
                <p class="text-[16px] font-black text-[#0d326b] mt-3">{{ $user->name }}</p>
                <p class="text-[12px] text-slate-400 mt-0.5">{{ $user->email }}</p>
                <span class="inline-block mt-2 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#facc15] text-[#0d326b]">
                    ICT Coordinator
                </span>
            </div>

            {{-- School info card --}}
            @if($school)
            <div class="bg-white rounded-[18px] shadow-sm border border-slate-100 p-5 flex flex-col gap-3">
                <p class="text-[12px] font-black uppercase tracking-wider text-slate-400">Assigned School</p>
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-blue-50">
                        <span class="material-symbols-outlined text-[#0d326b] text-[18px]">business</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[13px] font-bold text-slate-800">{{ $school->name }}</p>
                        @if($school->address)
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $school->address }}</p>
                        @endif
                        @if($school->region)
                        <p class="text-[11px] text-slate-400">Region {{ $school->region }}{{ $school->division ? ' — '.$school->division : '' }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Quick links --}}
            <div class="bg-white rounded-[18px] shadow-sm border border-slate-100 p-5 flex flex-col gap-2">
                <p class="text-[12px] font-black uppercase tracking-wider text-slate-400 mb-1">Quick Links</p>
                <a href="{{ route('ict.dashboard') }}" class="flex items-center gap-2 text-[13px] font-semibold text-[#0d326b] hover:underline">
                    <span class="material-symbols-outlined text-[15px]">grid_view</span> Dashboard
                </a>
                <a href="{{ route('ict.accounts') }}" class="flex items-center gap-2 text-[13px] font-semibold text-[#0d326b] hover:underline">
                    <span class="material-symbols-outlined text-[15px]">manage_accounts</span> Account Management
                </a>
            </div>

        </div>{{-- /right col --}}
    </div>
</div>

<style>
    .ict-settings-hero { background:linear-gradient(125deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%); }
    .ict-settings-tabs { display:flex; gap:6px; margin:0 0 18px; overflow-x:auto; scrollbar-width:thin; }
    .ict-settings-tab { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border:1.5px solid #e2e8f0; border-radius:14px; background:#fff; color:#64748b; font-size:13px; font-weight:700; white-space:nowrap; transition:.2s; }
    .ict-settings-tab .material-symbols-outlined { color:#94a3b8; font-size:17px; }
    .ict-settings-tab:hover { border-color:#bfdbfe; color:#0d326b; }
    .ict-settings-tab.active { border-color:transparent; background:linear-gradient(135deg,#0d326b,#1a6fd4); color:#fff; box-shadow:0 4px 14px rgba(13,50,107,.2); }
    .ict-settings-tab.active .material-symbols-outlined { color:#fff; }
    .ict-settings-pane { display:none; }
    .ict-settings-pane.active { display:block; animation:ictSettingsFade .2s ease; }
    @keyframes ictSettingsFade { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:translateY(0); } }
    @media (max-width:640px) { .ict-settings-hero { padding:20px; } .ict-settings-tabs { margin-bottom:14px; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabBar = document.querySelector('.ict-settings-tabs');
    if (!tabBar) return;
    const tabs = tabBar.querySelectorAll('[data-settings-tab]');
    const panes = document.querySelectorAll('[data-settings-pane]');
    function activateSettingsTab(name) {
        tabs.forEach(tab => {
            const active = tab.dataset.settingsTab === name;
            tab.classList.toggle('active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        panes.forEach(pane => pane.classList.toggle('active', pane.dataset.settingsPane === name));
    }
    tabs.forEach(tab => tab.addEventListener('click', () => activateSettingsTab(tab.dataset.settingsTab)));
    activateSettingsTab(tabBar.dataset.settingsInitial || 'profile');
});

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { document.getElementById('avatarPreview').src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
    }
}

function checkPwStrength(val) {
    const bar      = document.getElementById('pwStrengthBar');
    const fill     = document.getElementById('pwStrengthFill');
    const list     = document.getElementById('pwChecklist');
    if (!val) { bar.classList.add('hidden'); list.classList.add('hidden'); return; }
    bar.classList.remove('hidden'); list.classList.remove('hidden');

    const checks = {
        pwLen:    val.length >= 10,
        pwUpper:  /[A-Z]/.test(val),
        pwLower:  /[a-z]/.test(val),
        pwNum:    /[0-9]/.test(val),
        pwSpecial:/[^A-Za-z0-9]/.test(val),
    };
    const passed = Object.values(checks).filter(Boolean).length;

    Object.entries(checks).forEach(([id, ok]) => {
        const el = document.getElementById(id);
        el.querySelector('span').textContent = ok ? 'check_circle' : 'radio_button_unchecked';
        el.style.color = ok ? '#16a34a' : '#94a3b8';
    });

    const pct    = (passed / 5) * 100;
    const colors = ['#ef4444','#f97316','#f59e0b','#84cc16','#22c55e'];
    fill.style.width      = pct + '%';
    fill.style.background = colors[passed - 1] || '#e2e8f0';
}
</script>
@endsection
