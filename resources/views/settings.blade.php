@extends('layouts.app')
@section('bg-class', 'bg-[#f8fafc]')
@section('title', 'Settings')
@section('content')

<style>
:root {
    --navy-950: #071c3f;
    --navy-900: #0d326b;
    --navy-700: #1e4b8f;
    --navy-500: #1a6fd4;
    --navy-400: #3b82f6;
    --navy-300: #93c5fd;
    --navy-200: #bfdbfe;
    --navy-100: #dbeafe;
    --navy-50:  #eff6ff;
}

/* ── Navigation ──────────────────────────────────────────────────────────── */
.set-nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 600;
    color: #94a3b8;
    cursor: pointer;
    transition: all .2s;
    border: 1px solid transparent;
}
.set-nav-item:hover {
    color: #0d326b;
    background: #f1f5f9;
    border-color: #e2e8f0;
}
.set-nav-item.active {
    color: #0d326b;
    background: #eaf1fb;
    font-weight: 700;
    border-color: #dbeafe;
    box-shadow: 0 1px 3px rgba(13,50,107,.06);
}
.set-nav-item .mat { font-size: 20px; }
.set-nav-item .nav-badge {
    margin-left: auto;
    background: #dbeafe;
    color: #0d326b;
    font-size: 9px;
    font-weight: 700;
    padding: 2px 10px;
    border-radius: 9999px;
    letter-spacing: 0.05em;
}

/* ── Form Elements ───────────────────────────────────────────────────────── */
.set-field-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.set-input {
    width: 100%;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    outline: none;
    transition: all .2s;
}
.set-input:focus {
    border-color: #0d326b;
    box-shadow: 0 0 0 3px rgba(13,50,107,.08);
}
.set-input:read-only {
    background: #f8fafc;
    color: #94a3b8;
    cursor: not-allowed;
}
.set-input-dark {
    width: 100%;
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    outline: none;
    transition: all .2s;
}
.set-input-dark:focus {
    border-color: #0d326b;
    box-shadow: 0 0 0 3px rgba(13,50,107,.08);
}

.set-btn-primary {
    background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);
    color: #fff;
    padding: 12px 28px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    transition: all .25s;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.set-btn-primary:hover {
    opacity: .92;
    box-shadow: 0 4px 20px rgba(13,50,107,.3);
    transform: translateY(-2px);
}
.set-btn-primary:active {
    transform: translateY(0);
}

.set-btn-outline {
    background: #fff;
    color: #475569;
    border: 1.5px solid #e2e8f0;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    transition: all .2s;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.set-btn-outline:hover {
    border-color: #0d326b;
    background: #f8fafc;
    color: #0d326b;
}

.set-section-title {
    font-size: 20px;
    font-weight: 800;
    color: #0d326b;
    margin-bottom: 4px;
}
.set-section-sub {
    font-size: 13px;
    color: #94a3b8;
    font-weight: 500;
}
.set-divider {
    border-top: 1px solid #f1f5f9;
    margin: 28px 0;
}

.settings-tab-pane { display: none; }
.settings-tab-pane.active {
    display: block;
    animation: setFadeIn .25s ease;
}
@keyframes setFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ── Toggle Switch ──────────────────────────────────────────────────────── */
.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    border-bottom: 1px solid #f1f5f9;
}
.toggle-row:last-child { border-bottom: none; }

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 28px;
    cursor: pointer;
    flex-shrink: 0;
}
.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.toggle-slider {
    position: absolute;
    inset: 0;
    background: #cbd5e1;
    border-radius: 9999px;
    transition: all .35s cubic-bezier(.4,0,.2,1);
}
.toggle-slider::before {
    content: '';
    position: absolute;
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: all .35s cubic-bezier(.4,0,.2,1);
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
.toggle-switch input:checked + .toggle-slider {
    background: #0d326b;
}
.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(22px);
}

/* ── Account Summary Card (Sticky) ─────────────────────────────────────── */
.account-summary-wrapper {
    position: sticky;
    top: 0;
    z-index: 40;
    padding-top: 4px;
    padding-bottom: 4px;
    background: #f8fafc;
}

.account-summary {
    background: #fff;
    border-radius: 20px;
    border: 1px solid #f1f5f9;
    padding: 20px 28px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 2px 12px rgba(13,50,107,.06);
    transition: all .25s;
}
.account-summary:hover {
    box-shadow: 0 8px 32px rgba(13,50,107,.1);
}

.account-summary-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
    border: 3px solid #e2e8f0;
    background: #f1f5f9;
}
.account-summary-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.account-summary-avatar .avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #0d326b, #1a6fd4);
}
.account-summary-avatar .status-dot {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #22c55e;
    border: 3px solid #fff;
    z-index: 2;
}

.account-summary-info {
    flex: 1;
    min-width: 0;
}
.account-summary-info h4 {
    font-size: 17px;
    font-weight: 700;
    color: #0d326b;
    margin-bottom: 1px;
}
.account-summary-info .email {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}
.account-summary-info .role {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    color: #1a6fd4;
    background: #eff6ff;
    padding: 2px 14px;
    border-radius: 9999px;
    margin-top: 3px;
}
.account-summary-info .role .material-symbols-outlined {
    font-size: 14px;
}

.account-summary-meta {
    text-align: right;
    flex-shrink: 0;
}
.account-summary-meta .label {
    font-size: 10px;
    color: #94a3b8;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.account-summary-meta .value {
    font-size: 14px;
    font-weight: 700;
    color: #0d326b;
}

/* ── Security Tips ──────────────────────────────────────────────────────── */
.security-tips {
    background: #f8fafc;
    border-radius: 16px;
    padding: 20px 24px;
    margin-top: 20px;
    border: 1px solid #eef2f6;
}
.security-tips .tip-title {
    font-size: 12px;
    font-weight: 700;
    color: #0d326b;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.security-tips ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 24px;
}
.security-tips li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 0;
    font-size: 13px;
    color: #475569;
    font-weight: 500;
}
.security-tips li .material-symbols-outlined {
    font-size: 18px;
    color: #1a6fd4;
}

/* ── Layout ─────────────────────────────────────────────────────────────── */
.settings-layout {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 32px;
    margin-top: 20px;
}

.settings-nav {
    position: sticky;
    top: 100px;
    align-self: start;
    background: #fff;
    border-radius: 16px;
    border: 1px solid #f1f5f9;
    padding: 8px;
    box-shadow: 0 1px 2px rgba(13,50,107,.04);
}
.settings-nav .nav-label {
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 10px 14px 6px;
}

.settings-content {
    min-width: 0;
}

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .settings-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    .settings-nav {
        position: relative;
        top: 0;
        display: flex;
        overflow-x: auto;
        gap: 4px;
        padding: 6px;
    }
    .settings-nav .nav-label { display: none; }
    .set-nav-item {
        white-space: nowrap;
        padding: 8px 14px;
        font-size: 13px;
    }
    .set-nav-item .nav-badge { display: none; }
    .account-summary {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    .account-summary-meta { text-align: center; }
    .security-tips ul {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="pb-20">

    {{-- Flash / Validation messages --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-3.5 rounded-xl text-[13px] font-medium flex items-center space-x-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-xl text-[13px] font-medium flex items-center space-x-2">
            <span class="material-symbols-outlined text-[18px]">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-xl text-[13px] font-medium">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ══════════ ACCOUNT SUMMARY CARD (STICKY) ══════════ --}}
    <div class="account-summary-wrapper">
        <div class="account-summary">
            <div class="account-summary-avatar">
                @if(Auth::user() && Auth::user()->avatarUrl() && Auth::user()->avatarUrl() !== 'https://ui-avatars.com/api/...')
                    <img src="{{ Auth::user()->avatarUrl() }}" alt="Profile Photo">
                @else
                    <div class="avatar-placeholder">
                        {{ $teacher?->first_name ? $teacher->first_name[0] : 'U' }}{{ $teacher?->last_name ? $teacher->last_name[0] : '' }}
                    </div>
                @endif
                <span class="status-dot"></span>
            </div>
            <div class="account-summary-info">
                <h4>{{ $teacher?->first_name ?? 'User' }} {{ $teacher?->last_name ?? '' }}</h4>
                <div class="email">{{ $user->email ?? 'No email set' }}</div>
                <div class="role">
                    <span class="material-symbols-outlined">verified</span>
                    {{ $teacher?->specialization ?? 'SNED' }} Teacher
                </div>
            </div>
            <div class="account-summary-meta">
                <div class="label">Member since</div>
                <div class="value">{{ $user?->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</div>
            </div>
        </div>
    </div>

    {{-- ══════════ NAV + CONTENT ══════════ --}}
    <div class="settings-layout">

        {{-- LEFT: Nav --}}
        <div class="settings-nav">
            <div class="nav-label">Settings</div>
            <div class="set-nav-item active" data-tab="profile">
                <span class="material-symbols-outlined mat">person</span>
                <span>Profile</span>
            </div>
            @if($school)
            <div class="set-nav-item" data-tab="institution">
                <span class="material-symbols-outlined mat">school</span>
                <span>Institution</span>
            </div>
            @endif
            <div class="set-nav-item" data-tab="security">
                <span class="material-symbols-outlined mat">lock</span>
                <span>Security</span>
                <span class="nav-badge">Secure</span>
            </div>
            <div class="set-nav-item" data-tab="notifications">
                <span class="material-symbols-outlined mat">notifications</span>
                <span>Notifications</span>
                <span class="nav-badge">2</span>
            </div>
        </div>

        {{-- RIGHT: Content --}}
        <div class="settings-content">

            {{-- ── PROFILE TAB ─────────────────────────────────────────────── --}}
            <div class="settings-tab-pane active" id="tab-profile">
                <h3 class="set-section-title">Profile Settings</h3>
                <p class="set-section-sub">Update your personal information and how you appear to others.</p>

                <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data" id="profileForm">
                    @csrf
                    @method('PATCH')

                    <div class="flex items-center gap-6 mt-6">
                        <img id="avatarPreview" src="{{ Auth::user()->avatarUrl() }}" alt="Profile Photo"
                             class="w-20 h-20 rounded-full object-cover bg-slate-100 border-2 border-slate-200">
                        <div class="flex flex-col gap-2">
                            <button type="button" onclick="document.getElementById('profilePhotoInput').click()" class="set-btn-primary">
                                <span class="material-symbols-outlined" style="font-size:18px;">photo_camera</span>
                                Change Photo
                            </button>
                            @if(Auth::user()->profile_photo)
                            <button type="button" onclick="removeProfilePhoto()" class="set-btn-outline">
                                <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                Remove Photo
                            </button>
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
                            <label class="set-field-label">Email address</label>
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
                            <input type="text" value="{{ $user->username }}" readonly class="set-input-dark"/>
                        </div>
                    </div>

                    <div class="set-divider"></div>
                    <div class="flex justify-end">
                        <button type="submit" class="set-btn-primary">
                            <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── INSTITUTION TAB ─────────────────────────────────────────── --}}
            @if($school)
            <div class="settings-tab-pane" id="tab-institution">
                <h3 class="set-section-title">Institution Details</h3>
                <p class="set-section-sub">Manage your school and division information.</p>

                <form method="POST" action="{{ route('settings.school') }}">
                    @csrf
                    @method('PATCH')

                    <div class="flex items-center gap-6 mt-6">
                        <img src="https://api.dicebear.com/7.x/identicon/svg?seed={{ urlencode($school->name) }}&backgroundColor=eef2f6&iconColor=0d326b"
                             alt="School Logo" class="w-20 h-20 rounded-full object-cover bg-slate-100 border-2 border-slate-200 p-3"/>
                        <div>
                            <p class="text-[14px] font-bold text-[#1e293b]">School Logo</p>
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
                        <button type="submit" class="set-btn-primary">
                            <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- ── SECURITY TAB ───────────────────────────────────────────── --}}
            <div class="settings-tab-pane" id="tab-security">
                <h3 class="set-section-title">Security</h3>
                <p class="set-section-sub">Change your password and manage security settings.</p>

                <form method="POST" action="{{ route('settings.password') }}">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-5 mt-6">
                        <div>
                            <label class="set-field-label">Current password</label>
                            <input type="password" name="current_password" class="set-input" placeholder="Enter your current password"/>
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
                                <input type="password" name="password_confirmation" class="set-input" placeholder="Re-enter new password"/>
                            </div>
                        </div>
                    </div>

                    {{-- Security Tips --}}
                    <div class="security-tips">
                        <div class="tip-title">
                            <span class="material-symbols-outlined" style="font-size:18px;">shield</span>
                            Password Requirements
                        </div>
                        <ul>
                            <li><span class="material-symbols-outlined">check_circle</span> At least 8 characters</li>
                            <li><span class="material-symbols-outlined">check_circle</span> Uppercase &amp; lowercase letters</li>
                            <li><span class="material-symbols-outlined">check_circle</span> Numbers &amp; special characters</li>
                            <li><span class="material-symbols-outlined">check_circle</span> No common words or personal info</li>
                        </ul>
                    </div>

                    <div class="set-divider"></div>
                    <div class="flex justify-end">
                        <button type="submit" class="set-btn-primary">
                            <span class="material-symbols-outlined" style="font-size:18px;">lock_reset</span>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── NOTIFICATIONS TAB ───────────────────────────────────────── --}}
            <div class="settings-tab-pane" id="tab-notifications">
                <h3 class="set-section-title">Notification Preferences</h3>
                <p class="set-section-sub">Control how and when you receive updates.</p>

                <div class="mt-6">
                    <div class="toggle-row">
                        <div class="flex items-center space-x-3">
                            <span class="material-symbols-outlined text-[20px] text-slate-400">mail</span>
                            <div>
                                <span class="text-[14px] font-bold text-[#1e293b] block">Email Alerts</span>
                                <p class="text-[12px] text-slate-400 font-medium">Receive lesson and quiz updates via email</p>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div class="flex items-center space-x-3">
                            <span class="material-symbols-outlined text-[20px] text-slate-400">smartphone</span>
                            <div>
                                <span class="text-[14px] font-bold text-[#1e293b] block">Push Notifications</span>
                                <p class="text-[12px] text-slate-400 font-medium">Get real-time updates on student progress</p>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div class="flex items-center space-x-3">
                            <span class="material-symbols-outlined text-[20px] text-slate-400">celebration</span>
                            <div>
                                <span class="text-[14px] font-bold text-[#1e293b] block">Achievement Alerts</span>
                                <p class="text-[12px] text-slate-400 font-medium">Celebrate when students reach milestones</p>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div class="flex items-center space-x-3">
                            <span class="material-symbols-outlined text-[20px] text-slate-400">analytics</span>
                            <div>
                                <span class="text-[14px] font-bold text-[#1e293b] block">Weekly Digest</span>
                                <p class="text-[12px] text-slate-400 font-medium">Class performance summary every Monday</p>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="set-divider"></div>
                <div class="flex justify-end">
                    <button class="set-btn-primary" onclick="alert('Preferences saved successfully!')">
                        <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                        Save Preferences
                    </button>
                </div>
            </div>

        </div>{{-- end content --}}
    </div>{{-- end layout --}}

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
    if (!confirm('Remove your profile photo?')) return;
    const token = document.querySelector('#profileForm input[name="_token"]').value;
    fetch("{{ route('settings.profile-photo.remove') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ _token: token, _method: 'DELETE' })
    })
    .then(res => res.ok ? window.location.reload() : alert('Failed to remove photo. Please try again.'))
    .catch(() => alert('Network error while removing photo.'));
}
</script>

@endsection