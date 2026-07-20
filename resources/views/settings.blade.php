@extends('layouts.app')
@section('bg-class', 'bg-[#f8fafc]')
@section('title', 'Settings')
@section('content')

<style>
.set-nav-item { display:flex; align-items:center; gap:10px; padding:11px 16px; border-radius:12px; font-size:13.5px; font-weight:600; color:#94a3b8; cursor:pointer; transition:all .15s; }
.set-nav-item:hover { color:#0d326b; background:#f1f5f9; }
.set-nav-item.active { color:#0d326b; background:#eaf1fb; font-weight:700; }
.set-nav-item .mat { font-size:19px; }

.set-field-label { display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:8px; }
.set-input { width:100%; background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; padding:12px 16px; font-size:14px; font-weight:500; color:#1e293b; outline:none; transition:all .15s; }
.set-input:focus { border-color:#0d326b; box-shadow:0 0 0 3px rgba(13,50,107,.08); }
.set-input:read-only { background:#f8fafc; color:#94a3b8; cursor:not-allowed; }

.set-btn-dark { background:#0d326b; color:#fff; padding:11px 20px; border-radius:11px; font-size:13.5px; font-weight:700; transition:all .15s; }
.set-btn-dark:hover { background:#154188; }
.set-btn-outline { background:#fff; color:#334155; border:1.5px solid #e2e8f0; padding:11px 20px; border-radius:11px; font-size:13.5px; font-weight:700; transition:all .15s; }
.set-btn-outline:hover { border-color:#cbd5e1; background:#f8fafc; }
.set-btn-danger-text { color:#ef4444; font-size:13px; font-weight:600; }
.set-btn-danger-text:hover { text-decoration:underline; }

.set-section-title { font-size:22px; font-weight:800; color:#0d326b; margin-bottom:2px; }
.set-section-sub { font-size:13px; color:#94a3b8; font-weight:500; }
.set-divider { border-top:1px solid #eef2f6; margin:32px 0; }

.settings-tab-pane { display:none; }
.settings-tab-pane.active { display:block; animation:setFadeIn .2s ease; }
@keyframes setFadeIn { from{opacity:0; transform:translateY(4px);} to{opacity:1; transform:translateY(0);} }

.toggle-row { display:flex; align-items:center; justify-content:space-between; padding:16px 0; border-bottom:1px solid #f1f5f9; }
.toggle-row:last-child { border-bottom:none; }
</style>

<div class="pb-24">

    {{-- ══════════ HEADER ══════════ --}}
    <div class="mb-8">
        <p class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-1">Account</p>
        <h2 class="text-[32px] font-semibold text-[#0d326b] leading-tight">Settings</h2>
    </div>

    {{-- Flash / Validation messages --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-3 rounded-xl text-[13px] font-medium flex items-center space-x-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span><span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-3 rounded-xl text-[13px] font-medium flex items-center space-x-2">
            <span class="material-symbols-outlined text-[18px]">error</span><span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-3 rounded-xl text-[13px] font-medium">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ══════════ NAV + CONTENT (no outer container) ══════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-[200px_1fr] gap-10">

        {{-- LEFT: plain nav list --}}
        <nav class="space-y-1 md:sticky md:top-6 self-start">
            <div class="set-nav-item active" data-tab="profile">
                <span class="material-symbols-outlined mat">person</span><span>Profile</span>
            </div>
            @if($school)
            <div class="set-nav-item" data-tab="institution">
                <span class="material-symbols-outlined mat">school</span><span>Institution</span>
            </div>
            @endif
            <div class="set-nav-item" data-tab="security">
                <span class="material-symbols-outlined mat">lock</span><span>Security</span>
            </div>
            <div class="set-nav-item" data-tab="notifications">
                <span class="material-symbols-outlined mat">notifications</span><span>Notifications</span>
            </div>
        </nav>

        {{-- RIGHT: content --}}
        <div class="max-w-2xl">

            {{-- PROFILE TAB --}}
            <div class="settings-tab-pane active" id="tab-profile">
                <h3 class="set-section-title">Public Profile</h3>
                <p class="set-section-sub">This is how you'll appear across the teacher portal.</p>

                <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data" id="profileForm">
                    @csrf
                    @method('PATCH')

                    <div class="flex items-center gap-6 mt-7">
                        <img id="avatarPreview" src="{{ Auth::user()->avatarUrl() }}" alt="Profile Photo"
                             class="w-20 h-20 rounded-full object-cover bg-slate-100 border border-slate-200">
                        <div class="flex flex-col gap-2">
                            <button type="button" onclick="document.getElementById('profilePhotoInput').click()" class="set-btn-dark">Change picture</button>
                            @if(Auth::user()->profile_photo)
                            <button type="button" onclick="removeProfilePhoto()" class="set-btn-outline">Delete picture</button>
                            @endif
                        </div>
                        <input type="file" id="profilePhotoInput" name="profile_photo"
                               accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" onchange="previewProfilePhoto(this)">
                    </div>

                    <div class="grid grid-cols-2 gap-5 mt-8">
                        <div>
                            <label class="set-field-label">First name</label>
                            <input type="text" name="first_name" class="set-input" value="{{ old('first_name', $teacher?->first_name ?? '') }}" required/>
                        </div>
                        <div>
                            <label class="set-field-label">Last name</label>
                            <input type="text" name="last_name" class="set-input" value="{{ old('last_name', $teacher?->last_name ?? '') }}" required/>
                        </div>
                        <div class="col-span-2">
                            <label class="set-field-label">Academic email</label>
                            <input type="email" name="email" class="set-input" placeholder="name@deped.gov.ph" value="{{ old('email', $user->email ?? '') }}"/>
                        </div>
                        <div>
                            <label class="set-field-label">Specialization</label>
                            <div class="relative">
                                <select name="specialization" class="set-input appearance-none pr-10 cursor-pointer">
                                    <option value="SNED" {{ ($teacher?->specialization ?? 'SNED') === 'SNED' ? 'selected' : '' }}>SNED</option>
                                    <option value="Regular" {{ ($teacher?->specialization) === 'Regular' ? 'selected' : '' }}>Regular</option>
                                </select>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-400 pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        <div>
                            <label class="set-field-label">Username</label>
                            <input type="text" value="{{ $user->username }}" readonly class="set-input"/>
                        </div>
                    </div>

                    <div class="set-divider"></div>
                    <div class="flex justify-end">
                        <button type="submit" class="set-btn-dark">Save changes</button>
                    </div>
                </form>
            </div>

            {{-- INSTITUTION TAB --}}
            @if($school)
            <div class="settings-tab-pane" id="tab-institution">
                <h3 class="set-section-title">Institution</h3>
                <p class="set-section-sub">School and division details tied to your account.</p>

                <form method="POST" action="{{ route('settings.school') }}">
                    @csrf
                    @method('PATCH')

                    <div class="flex items-center gap-6 mt-7">
                        <img src="https://api.dicebear.com/7.x/identicon/svg?seed={{ urlencode($school->name) }}&backgroundColor=eef2f6&iconColor=0d326b"
                             alt="School Logo" class="w-20 h-20 rounded-full object-cover bg-slate-100 border border-slate-200 p-3"/>
                        <div>
                            <p class="text-[13.5px] font-bold text-[#1e293b]">School logo</p>
                            <p class="text-[12px] text-slate-400 font-medium">Auto-generated from school name</p>
                        </div>
                    </div>

                    <div class="space-y-5 mt-8">
                        <div>
                            <label class="set-field-label">School name</label>
                            <input type="text" name="school_name" class="set-input" value="{{ old('school_name', $school->name) }}" required/>
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="set-field-label">Address</label>
                                <input type="text" name="school_address" class="set-input" value="{{ old('school_address', $school->address) }}"/>
                            </div>
                            <div>
                                <label class="set-field-label">Region</label>
                                <input type="text" name="region" class="set-input" value="{{ old('region', $school->region) }}"/>
                            </div>
                            <div class="col-span-2">
                                <label class="set-field-label">Division</label>
                                <input type="text" name="division" class="set-input" value="{{ old('division', $school->division) }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="set-divider"></div>
                    <div class="flex justify-end">
                        <button type="submit" class="set-btn-dark">Save changes</button>
                    </div>
                </form>
            </div>
            @endif

            {{-- SECURITY TAB --}}
            <div class="settings-tab-pane" id="tab-security">
                <h3 class="set-section-title">Security</h3>
                <p class="set-section-sub">Change your password to keep your account safe.</p>

                <form method="POST" action="{{ route('settings.password') }}">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-5 mt-7">
                        <div>
                            <label class="set-field-label">Current password</label>
                            <input type="password" name="current_password" class="set-input" placeholder="••••••••"/>
                            @error('current_password')<p class="text-red-500 text-[12px] font-medium mt-1.5">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="set-field-label">New password</label>
                                <input type="password" name="password" class="set-input" placeholder="Minimum 8 characters"/>
                                @error('password')<p class="text-red-500 text-[12px] font-medium mt-1.5">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="set-field-label">Confirm password</label>
                                <input type="password" name="password_confirmation" class="set-input" placeholder="Re-enter password"/>
                            </div>
                        </div>
                    </div>

                    <div class="set-divider"></div>
                    <div class="flex items-center justify-between">
                        <p class="text-[12px] text-slate-400 font-medium">Use letters, numbers &amp; symbols for a strong password.</p>
                        <button type="submit" class="set-btn-dark">Update password</button>
                    </div>
                </form>
            </div>

            {{-- NOTIFICATIONS TAB --}}
            <div class="settings-tab-pane" id="tab-notifications">
                <h3 class="set-section-title">Notifications</h3>
                <p class="set-section-sub">Choose what you want to be notified about.</p>

                <div class="mt-7">
                    <div class="toggle-row">
                        <div class="flex items-center space-x-3">
                            <span class="material-symbols-outlined icon-outline text-[19px] text-slate-400">mail</span>
                            <div>
                                <span class="text-[13.5px] font-bold text-[#1e293b] block">Email alerts</span>
                                <p class="text-[12px] text-slate-400 font-medium">Lesson &amp; quiz updates via email</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0d326b]"></div>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div class="flex items-center space-x-3">
                            <span class="material-symbols-outlined icon-outline text-[19px] text-slate-400">smartphone</span>
                            <div>
                                <span class="text-[13.5px] font-bold text-[#1e293b] block">App notifications</span>
                                <p class="text-[12px] text-slate-400 font-medium">Push updates on student progress</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0d326b]"></div>
                        </label>
                    </div>
                </div>
            </div>

        </div>{{-- end right content --}}
    </div>{{-- end grid --}}

</div>

<script>
document.querySelectorAll('.set-nav-item').forEach(item => {
    item.addEventListener('click', () => {
        document.querySelectorAll('.set-nav-item').forEach(i => i.classList.remove('active'));
        document.querySelectorAll('.settings-tab-pane').forEach(p => p.classList.remove('active'));
        item.classList.add('active');
        document.getElementById('tab-' + item.dataset.tab).classList.add('active');
    });
});

function previewProfilePhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) {
            alert('Image must be under 5MB.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(file);
    }
}

function removeProfilePhoto() {
    if (!confirm('Remove profile photo?')) return;
    const token = document.querySelector('#profileForm input[name="_token"]').value;
    fetch("{{ route('settings.profile-photo.remove') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
        body: new URLSearchParams({ _token: token, _method: 'DELETE' })
    })
    .then(res => res.ok ? window.location.reload() : alert('Failed to remove photo. Please try again.'))
    .catch(() => alert('Network error while removing photo.'));
}
</script>

@endsection