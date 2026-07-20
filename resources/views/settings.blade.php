@extends('layouts.app')
@section('bg-class', 'bg-[#f8fafc]')
@section('title', 'Settings')
@section('content')

<div class="w-full pb-24">

    <div class="mb-8">
        <h3 class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-2">ACCOUNT</h3>
        <h2 class="text-[36px] font-medium text-[#0d326b] leading-tight">Settings</h2>
    </div>

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

    {{-- ══ TWO COLUMN LAYOUT ══ --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 items-start">

    {{-- LEFT COLUMN --}}
    <div class="flex flex-col gap-8">

    {{-- ══ PERSONAL INFORMATION ══ --}}
    <div>
        <h2 class="text-[20px] font-semibold text-[#1e4b8f] leading-tight mb-4">Personal Information</h2>
        <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data" id="profileForm">
                @csrf
                @method('PATCH')
                <div class="flex items-start space-x-10">

                    {{-- Avatar Upload --}}
                    <div class="flex flex-col items-center flex-shrink-0" style="min-width:96px;">
                        {{-- Current avatar --}}
                        <div class="relative group mb-3" style="width:96px;height:96px;">
                            <img id="avatarPreview"
                                 src="{{ Auth::user()->avatarUrl() }}"
                                 alt="Profile Photo"
                                 class="w-24 h-24 rounded-full border-4 border-white shadow-md object-cover bg-slate-100"
                                 style="width:96px;height:96px;object-fit:cover;">

                            {{-- Hover overlay --}}
                            <label for="profilePhotoInput"
                                   class="absolute inset-0 rounded-full flex items-center justify-center cursor-pointer transition-all duration-200"
                                   style="background:rgba(13,50,107,0);transition:background 0.2s;"
                                   onmouseover="this.style.background='rgba(13,50,107,0.55)'; this.querySelector('.cam-icon').style.opacity='1';"
                                   onmouseout="this.style.background='rgba(13,50,107,0)'; this.querySelector('.cam-icon').style.opacity='0';">
                                <span class="cam-icon material-symbols-outlined text-white text-2xl" style="opacity:0;transition:opacity 0.2s;">photo_camera</span>
                            </label>
                            <input type="file" id="profilePhotoInput" name="profile_photo"
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   class="hidden"
                                   onchange="previewProfilePhoto(this)">
                        </div>

                        <button type="button"
                                onclick="document.getElementById('profilePhotoInput').click()"
                                class="text-[11px] font-bold text-[#0d326b] hover:underline mb-1">
                            Change Photo
                        </button>

                        @if(Auth::user()->profile_photo)
                        <form method="POST" action="{{ route('settings.profile-photo.remove') }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Remove profile photo?')"
                                    class="text-[11px] font-medium text-red-400 hover:text-red-600 hover:underline">
                                Remove
                            </button>
                        </form>
                        @endif

                        <span class="text-[10px] text-slate-400 mt-1 text-center leading-tight">JPG, PNG, GIF<br>Max 5MB</span>
                    </div>

                    {{-- Fields --}}
                    <div class="flex-1 grid grid-cols-2 gap-x-6 gap-y-5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">First Name</label>
                            <input type="text" name="first_name"
                                   value="{{ old('first_name', $teacher?->first_name ?? '') }}"
                                   class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 font-medium"
                                   required/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Last Name</label>
                            <input type="text" name="last_name"
                                   value="{{ old('last_name', $teacher?->last_name ?? '') }}"
                                   class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 font-medium"
                                   required/>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Academic Email</label>
                            <input type="email" name="email"
                                   value="{{ old('email', $user->email ?? '') }}"
                                   placeholder="name@deped.gov.ph"
                                   class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 font-medium"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Specialization</label>
                            <div class="relative">
                                <select name="specialization"
                                        class="appearance-none w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 pr-10 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 font-medium cursor-pointer">
                                    <option value="SNED" {{ ($teacher?->specialization ?? 'SNED') === 'SNED' ? 'selected' : '' }}>SNED</option>
                                    <option value="Regular" {{ ($teacher?->specialization) === 'Regular' ? 'selected' : '' }}>Regular</option>
                                </select>
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-400 pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Username</label>
                            <input type="text" value="{{ $user->username }}" readonly
                                   class="w-full bg-[#f8fafc] border border-slate-200 rounded-xl py-3.5 px-4 text-[14px] text-slate-400 outline-none font-medium cursor-not-allowed"/>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-6 pt-6 border-t border-slate-100">
                    <button type="submit"
                            class="text-white px-7 py-3 rounded-xl text-[13px] font-bold transition-all duration-300 shadow-sm hover:shadow-md"
                            style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function previewProfilePhoto(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 5 * 1024 * 1024) {
                alert('Image must be under 5MB.');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
    </script>

    {{-- ══ INSTITUTION ══ --}}
    @if($school)
    <div>
        <h2 class="text-[20px] font-semibold text-[#1e4b8f] leading-tight mb-4">Institution</h2>
        <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            <form method="POST" action="{{ route('settings.school') }}">
                @csrf
                @method('PATCH')
                <div class="flex items-start space-x-10">
                    {{-- Logo --}}
                    <div class="flex flex-col items-center flex-shrink-0">
                        <div class="w-24 h-24 rounded-full border-4 border-white shadow-sm overflow-hidden bg-slate-100 flex items-center justify-center mb-4">
                            <img src="https://api.dicebear.com/7.x/identicon/svg?seed={{ urlencode($school->name) }}&backgroundColor=eef2f6&iconColor=0d326b"
                                 alt="School Logo" class="w-16 h-16 object-cover rounded-full"/>
                        </div>
                        <span class="text-[11px] font-medium text-slate-400">School Logo</span>
                    </div>
                    {{-- Fields --}}
                    <div class="flex-1 space-y-5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">School Name</label>
                            <input type="text" name="school_name" value="{{ old('school_name', $school->name) }}"
                                   class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 font-medium"
                                   required/>
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Address</label>
                                <input type="text" name="school_address" value="{{ old('school_address', $school->address) }}"
                                       class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 font-medium"/>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Region</label>
                                <input type="text" name="region" value="{{ old('region', $school->region) }}"
                                       class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 font-medium"/>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Division</label>
                                <input type="text" name="division" value="{{ old('division', $school->division) }}"
                                       class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 font-medium"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-6 pt-6 border-t border-slate-100">
                    <button type="submit"
                            class="px-7 py-3 rounded-xl text-white text-[13px] font-bold shadow-sm transition-all duration-300"
                            style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);">
                        Save Institution
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    </div>{{-- end left column --}}

    {{-- RIGHT COLUMN --}}
    <div class="flex flex-col gap-8">

    {{-- ══ CHANGE PASSWORD ══ --}}
    <div>
        <h2 class="text-[20px] font-semibold text-[#1e4b8f] leading-tight mb-4">Change Password</h2>
        <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            <form method="POST" action="{{ route('settings.password') }}">
                @csrf
                @method('PATCH')
                <div class="space-y-5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Current Password</label>
                        <input type="password" name="current_password"
                               class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 font-medium"
                               placeholder="••••••••"/>
                        @error('current_password')
                            <p class="text-red-500 text-[12px] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">New Password</label>
                            <input type="password" name="password"
                                   class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 font-medium"
                                   placeholder="Minimum 8 characters"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                   class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 font-medium"
                                   placeholder="Re-enter new password"/>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-6 pt-6 border-t border-slate-100">
                    <div class="flex items-center space-x-3 text-slate-400">
                        <span class="material-symbols-outlined icon-outline text-[20px]">lock_reset</span>
                        <p class="text-[12px] font-medium">Use a strong password with letters, numbers &amp; symbols.</p>
                    </div>
                    <button type="submit"
                            class="px-7 py-3 rounded-xl text-white text-[13px] font-bold shadow-sm transition-all duration-300"
                            style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ NOTIFICATION PREFERENCES ══ --}}
    <div class="mb-10">
        <h2 class="text-[20px] font-semibold text-[#1e4b8f] leading-tight mb-4">Notifications</h2>
        <div class="bg-white rounded-[32px] p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)] space-y-2">
            <div class="flex items-center justify-between p-4 rounded-xl hover:bg-[#f8fafc] transition-colors">
                <div class="flex items-center space-x-4">
                    <span class="material-symbols-outlined icon-outline text-[22px] text-slate-400">mail</span>
                    <div>
                        <span class="text-[14px] font-bold text-[#1e293b]">Email Alerts</span>
                        <p class="text-[11px] text-slate-400 font-medium">Receive lesson and quiz notifications via email</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0d326b]"></div>
                </label>
            </div>
            <hr class="border-slate-100">
            <div class="flex items-center justify-between p-4 rounded-xl hover:bg-[#f8fafc] transition-colors">
                <div class="flex items-center space-x-4">
                    <span class="material-symbols-outlined icon-outline text-[22px] text-slate-400">smartphone</span>
                    <div>
                        <span class="text-[14px] font-bold text-[#1e293b]">App Notifications</span>
                        <p class="text-[11px] text-slate-400 font-medium">Push notifications for student progress updates</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0d326b]"></div>
                </label>
            </div>
        </div>
    </div>

    </div>{{-- end right column --}}
    </div>{{-- end 2-col grid --}}

</div>
@endsection
