@extends('layouts.ict')
@section('title', 'Account Management')
@section('content')

{{-- ══════════════════════════════════════════════════════════════════════
     SKELETON
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="page-skeleton" class="flex flex-col gap-4 pt-4 w-full min-w-0" aria-hidden="true">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-5">
        <div class="skeleton skeleton-card h-[120px]"></div>
        @for($i=0;$i<3;$i++)
        <div class="bg-white rounded-[24px] p-[22px] border border-slate-100 shadow-sm flex flex-col gap-3">
            <div class="flex items-center justify-between"><div class="skeleton h-3 rounded w-24"></div><div class="skeleton w-10 h-10 rounded-xl"></div></div>
            <div class="skeleton h-9 rounded w-16"></div>
            <div class="skeleton h-3 rounded w-28"></div>
        </div>
        @endfor
        <div class="skeleton skeleton-card h-[120px]"></div>
    </div>
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5 flex items-center gap-3">
        <div class="skeleton h-10 rounded-full flex-1"></div>
        <div class="skeleton h-10 rounded-full w-28"></div>
        <div class="skeleton h-10 rounded-full w-28"></div>
        <div class="skeleton h-10 rounded-full w-24 ml-auto"></div>
    </div>
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50"><div class="skeleton h-5 rounded w-32"></div></div>
        @for($i=0;$i<8;$i++)
        <div class="flex items-center gap-4 px-6 py-4">
            <div class="skeleton skeleton-circle w-8 h-8 flex-shrink-0"></div>
            <div class="flex-1 flex flex-col gap-2"><div class="skeleton h-3 rounded w-36"></div><div class="skeleton h-2 rounded w-24"></div></div>
            <div class="skeleton h-3 rounded w-40 hidden sm:block"></div>
            <div class="skeleton h-6 rounded-full w-20 mx-auto"></div>
            <div class="skeleton h-6 rounded-full w-16 mx-auto"></div>
            <div class="skeleton skeleton-circle w-8 h-8 mx-auto"></div>
        </div>
        @endfor
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     REAL CONTENT
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-col gap-4 pt-4 skeleton-hide w-full min-w-0">

    {{-- ── KPI Cards ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-5">

        <div class="text-white" style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%)">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Total Accounts</span>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px] text-white">person</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-white tracking-tight">{{ $totalAccounts }}</p>
            <p class="text-[12px] text-white/70 font-medium">in {{ $school->name ?? 'your school' }}</p>
        </div>

        <div style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:#fff">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Teachers</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1a6fd4] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">school</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ $teacherCount }}</p>
            <p class="text-[12px] text-[#1a6fd4] font-medium">classroom teachers</p>
        </div>

        <div style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:#fff">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Grade Leaders</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">verified</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ $teacherLeaderCount }}</p>
            <p class="text-[12px] text-slate-400 font-medium">leader accounts</p>
        </div>

        <div style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #d1fae5;background:#f0fdf4">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Active</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-emerald-700 tracking-tight">{{ $activeCount }}</p>
            <p class="text-[12px] text-emerald-600 font-medium">accounts enabled</p>
        </div>

        <div class="text-amber-950" style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border:1px solid rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22)">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Inactive</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 text-amber-950 flex items-center justify-center backdrop-blur-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">cancel</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-amber-950 tracking-tight">{{ $inactiveCount }}</p>
            <p class="text-[12px] text-amber-950/80 font-bold">accounts disabled</p>
        </div>

    </div>

    {{-- ── Filter + Add bar ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5 flex items-center gap-3 flex-wrap">
        <form method="GET" action="{{ route('ict.accounts') }}" class="flex items-center gap-2 flex-wrap flex-1">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search by name, email, or username…"
                       class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
            </div>

            <div class="relative inline-flex items-center">
                <select name="role" onchange="this.form.submit()"
                        style="appearance:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:9px 34px 9px 14px;font-size:13px;font-weight:600;color:#0d326b;cursor:pointer;outline:none;">
                    <option value="all"          {{ $roleFilter==='all'?'selected':'' }}>All Roles</option>
                    <option value="teacher"       {{ $roleFilter==='teacher'?'selected':'' }}>Teacher</option>
                    <option value="grade_leader"{{ $roleFilter==='grade_leader'?'selected':'' }}>Grade Leader</option>
                </select>
                <span class="material-symbols-outlined absolute right-2.5 pointer-events-none text-[#0d326b] text-[18px]">expand_more</span>
            </div>

            <div class="relative inline-flex items-center">
                <select name="status" onchange="this.form.submit()"
                        style="appearance:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:9px 34px 9px 14px;font-size:13px;font-weight:600;color:#0d326b;cursor:pointer;outline:none;">
                    <option value="all"      {{ $statusFilter==='all'?'selected':'' }}>All Status</option>
                    <option value="active"   {{ $statusFilter==='active'?'selected':'' }}>Active</option>
                    <option value="inactive" {{ $statusFilter==='inactive'?'selected':'' }}>Inactive</option>
                </select>
                <span class="material-symbols-outlined absolute right-2.5 pointer-events-none text-[#0d326b] text-[18px]">expand_more</span>
            </div>

            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-2xl text-[13px] font-bold text-white"
                    style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
                <span class="material-symbols-outlined text-[16px]">search</span> Search
            </button>
            @if($search || $roleFilter!=='all' || $statusFilter!=='all')
            <a href="{{ route('ict.accounts') }}"
               class="inline-flex items-center gap-1 px-4 py-2.5 rounded-2xl text-[13px] font-semibold text-slate-500 border border-slate-200 bg-white hover:bg-slate-50 transition">
                <span class="material-symbols-outlined text-[15px]">close</span> Clear
            </a>
            @endif
        </form>

        <button type="button" onclick="openAddModal()"
                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-2xl text-[13px] font-bold text-white flex-shrink-0"
                style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
            <span class="material-symbols-outlined text-[16px]">person_add</span> Add Account
        </button>
    </div>

    {{-- ── Accounts Table ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-[15px] font-black text-[#0d326b]">School Accounts</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Teachers &amp; Grade Leaders — {{ $school->name ?? '' }}</p>
            </div>
            <span class="text-[12px] font-semibold text-slate-400">
                {{ $accounts->total() }} {{ Str::plural('account', $accounts->total()) }}
            </span>
        </div>

        @if($accounts->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-6 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Account</th>
                        <th class="text-left px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400 hidden sm:table-cell">Email</th>
                        <th class="text-center px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Role</th>
                        <th class="text-center px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Status</th>
                        <th class="text-center px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $account)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <img src="{{ $account->avatarUrl() }}"
                                     class="w-8 h-8 rounded-full object-cover border border-slate-100 flex-shrink-0"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($account->name) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800 truncate">{{ $account->name }}</p>
                                    <p class="text-[11px] text-slate-400">@{{ $account->username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-slate-500 hidden sm:table-cell">{{ $account->email }}</td>
                        <td class="px-4 py-3.5 text-center">
                            @php
                                $roleStyle = $account->role==='grade_leader'
                                    ? 'background:#eff6ff;color:#1d4ed8'
                                    : 'background:#f8fafc;color:#475569';
                            @endphp
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full" style="{{ $roleStyle }}">
                                {{ $account->role==='grade_leader'?'Grade Leader':'Teacher' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full
                                {{ $account->status==='active'?'bg-emerald-50 text-emerald-700':'bg-slate-100 text-slate-500' }}">
                                {{ ucfirst($account->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <button type="button"
                                    onclick="openManageModal({{ $account->id }}, '{{ addslashes($account->name) }}', '{{ $account->email }}', '{{ $account->role }}', '{{ $account->status }}')"
                                    class="w-8 h-8 rounded-full flex items-center justify-center mx-auto transition"
                                    style="background:#f1f5f9"
                                    onmouseover="this.style.background='#e2e8f0'"
                                    onmouseout="this.style.background='#f1f5f9'">
                                <span class="material-symbols-outlined text-[#0d326b] text-[17px]">more_horiz</span>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($accounts->hasPages())
        <div class="px-6 py-4 border-t border-slate-50">
            {{ $accounts->withQueryString()->links() }}
        </div>
        @endif

        @else
        <div class="px-6 py-16 text-center">
            <span class="material-symbols-outlined text-[56px]" style="color:#e2e8f0">manage_accounts</span>
            <p class="text-[14px] text-slate-400 font-semibold mt-3">
                @if($search || $roleFilter!=='all' || $statusFilter!=='all')
                No accounts match your filters.
                @else
                No accounts yet. Add the first one.
                @endif
            </p>
        </div>
        @endif
    </div>

</div>{{-- /skeleton-hide --}}

{{-- ══════════════════════════════════════════════════════════════════════
     ADD ACCOUNT MODAL
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="addAccountModal"
     class="fixed inset-0 z-[999] items-center justify-center p-4"
     style="display:none;background:rgba(15,23,42,.55);backdrop-filter:blur(3px)">
    <div class="bg-white rounded-[28px] shadow-2xl w-full max-w-lg flex flex-col overflow-hidden">
        <div class="px-7 py-5 border-b border-slate-100 flex items-center justify-between flex-shrink-0"
             style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 100%)">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-white text-[22px]">person_add</span>
                <p class="text-white font-black text-[15px]">Add New Account</p>
            </div>
            <button onclick="closeAddModal()" class="w-8 h-8 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.15)">
                <span class="material-symbols-outlined text-white text-[18px]">close</span>
            </button>
        </div>
        <form id="addAccountForm" class="p-7 flex flex-col gap-4">
            @csrf
            <div id="addFormError" class="hidden text-[13px] text-red-600 bg-red-50 border border-red-100 rounded-2xl px-4 py-3"></div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">First Name</label>
                    <input type="text" name="first_name" required
                           class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
                </div>
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Last Name</label>
                    <input type="text" name="last_name" required
                           class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Email Address</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
            </div>
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Role</label>
                <div class="relative">
                    <select name="role" required
                            style="appearance:none;width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:10px 34px 10px 16px;font-size:13px;font-weight:600;color:#0d326b;outline:none;">
                        <option value="teacher">Classroom Teacher</option>
                        <option value="grade_leader">Grade Leader</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[#0d326b] text-[18px]">expand_more</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Password</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
                </div>
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeAddModal()"
                        class="flex-1 py-3 border border-slate-200 rounded-2xl text-slate-600 font-semibold hover:bg-slate-50 transition-colors text-[13px]">
                    Cancel
                </button>
                <button type="submit" id="addSubmitBtn"
                        class="flex-1 py-3 text-white font-bold rounded-2xl text-[13px] flex items-center justify-center gap-2 transition"
                        style="background:linear-gradient(135deg,#0d326b,#1e4b8f)">
                    <span class="material-symbols-outlined text-[16px]">person_add</span> Create Account
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     MANAGE ACCOUNT MODAL
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="manageModal"
     class="fixed inset-0 z-[999] items-center justify-center p-4"
     style="display:none;background:rgba(15,23,42,.55);backdrop-filter:blur(3px)">
    <div class="bg-white rounded-[28px] shadow-2xl w-full max-w-md flex flex-col overflow-hidden">
        <div class="px-7 py-5 border-b border-slate-100 flex items-center justify-between flex-shrink-0"
             style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 100%)">
            <div>
                <p class="text-white/60 text-[10px] font-black uppercase tracking-wider">Manage Account</p>
                <p id="modalName" class="text-white font-black text-[15px]">—</p>
            </div>
            <button onclick="closeManageModal()" class="w-8 h-8 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.15)">
                <span class="material-symbols-outlined text-white text-[18px]">close</span>
            </button>
        </div>

        <div class="p-7 flex flex-col gap-5">
            <div id="manageMsg" class="hidden text-[13px] font-semibold px-4 py-3 rounded-2xl"></div>

            {{-- Status Toggle --}}
            <div>
                <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2.5">Account Status</p>
                <div class="flex gap-2">
                    <button onclick="setStatus('active')"
                            class="manage-status-btn flex-1 py-2.5 rounded-2xl text-[13px] font-bold border transition"
                            data-val="active"
                            style="border-color:#d1fae5;background:#f0fdf4;color:#15803d">
                        <span class="material-symbols-outlined text-[14px] align-middle mr-1">check_circle</span>Active
                    </button>
                    <button onclick="setStatus('inactive')"
                            class="manage-status-btn flex-1 py-2.5 rounded-2xl text-[13px] font-bold border transition"
                            data-val="inactive"
                            style="border-color:#e2e8f0;background:#f8fafc;color:#64748b">
                        <span class="material-symbols-outlined text-[14px] align-middle mr-1">cancel</span>Inactive
                    </button>
                </div>
            </div>

            {{-- Role Toggle --}}
            <div>
                <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2.5">Role</p>
                <div class="flex gap-2">
                    <button onclick="setRole('teacher')"
                            class="manage-role-btn flex-1 py-2.5 rounded-2xl text-[13px] font-bold border transition"
                            data-val="teacher"
                            style="border-color:#e2e8f0;background:#f8fafc;color:#64748b">
                        <span class="material-symbols-outlined text-[14px] align-middle mr-1">school</span>Teacher
                    </button>
                    <button onclick="setRole('grade_leader')"
                            class="manage-role-btn flex-1 py-2.5 rounded-2xl text-[13px] font-bold border transition"
                            data-val="grade_leader"
                            style="border-color:#e2e8f0;background:#f8fafc;color:#64748b">
                        <span class="material-symbols-outlined text-[14px] align-middle mr-1">verified</span>Leader
                    </button>
                </div>
            </div>

            {{-- Password Reset --}}
            <div>
                <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2.5">Reset Password</p>
                <div class="flex flex-col gap-2">
                    <input type="password" id="newPassword" placeholder="New password (min. 8 chars)"
                           class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
                    <input type="password" id="newPasswordConfirm" placeholder="Confirm new password"
                           class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
                    <button onclick="doResetPassword()"
                            class="w-full py-2.5 rounded-2xl text-[13px] font-bold text-white transition"
                            style="background:linear-gradient(135deg,#0d326b,#1e4b8f)">
                        <span class="material-symbols-outlined text-[14px] align-middle mr-1">lock_reset</span>Set New Password
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let currentId = null;

/* ── ADD MODAL ─────────────────────────────────────────────────────────── */
function openAddModal() { document.getElementById('addAccountModal').style.display = 'flex'; }
function closeAddModal() {
    document.getElementById('addAccountModal').style.display = 'none';
    document.getElementById('addAccountForm').reset();
    document.getElementById('addFormError').classList.add('hidden');
}

document.getElementById('addAccountForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn   = document.getElementById('addSubmitBtn');
    const errEl = document.getElementById('addFormError');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span> Creating…';

    const data = new FormData(this);

    fetch('{{ route("ict.accounts.add") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: data
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            closeAddModal();
            window.location.reload();
        } else {
            const msgs = res.errors ? Object.values(res.errors).flat().join(' ') : (res.message || 'An error occurred.');
            errEl.textContent = msgs;
            errEl.classList.remove('hidden');
        }
    })
    .catch(() => { errEl.textContent = 'Request failed. Please try again.'; errEl.classList.remove('hidden'); })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-[16px]">person_add</span> Create Account';
    });
});

/* ── MANAGE MODAL ──────────────────────────────────────────────────────── */
function openManageModal(id, name, email, role, status) {
    currentId = id;
    document.getElementById('modalName').textContent = name;
    document.getElementById('manageMsg').classList.add('hidden');
    document.getElementById('newPassword').value = '';
    document.getElementById('newPasswordConfirm').value = '';

    // Highlight current status
    document.querySelectorAll('.manage-status-btn').forEach(btn => {
        const active = btn.dataset.val === status;
        btn.style.background = active ? (status==='active'?'#f0fdf4':'#fff7ed') : '#f8fafc';
        btn.style.borderColor = active ? (status==='active'?'#6ee7b7':'#fcd34d') : '#e2e8f0';
        btn.style.color       = active ? (status==='active'?'#15803d':'#b45309') : '#64748b';
        if (active) btn.style.fontWeight = '800';
    });

    // Highlight current role
    document.querySelectorAll('.manage-role-btn').forEach(btn => {
        const active = btn.dataset.val === role;
        btn.style.background  = active ? '#eff6ff' : '#f8fafc';
        btn.style.borderColor = active ? '#93c5fd' : '#e2e8f0';
        btn.style.color       = active ? '#1d4ed8' : '#64748b';
        if (active) btn.style.fontWeight = '800';
    });

    document.getElementById('manageModal').style.display = 'flex';
}
function closeManageModal() { document.getElementById('manageModal').style.display = 'none'; currentId = null; }

function showMsg(text, ok) {
    const el = document.getElementById('manageMsg');
    el.textContent = text;
    el.className = 'text-[13px] font-semibold px-4 py-3 rounded-2xl ' + (ok ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100');
    el.classList.remove('hidden');
}

function setStatus(val) {
    fetch(`/ict/accounts/${currentId}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ status: val })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showMsg('Status updated to ' + val + '.', true);
            // Update button highlight
            document.querySelectorAll('.manage-status-btn').forEach(btn => {
                const active = btn.dataset.val === val;
                btn.style.background = active ? (val==='active'?'#f0fdf4':'#fff7ed') : '#f8fafc';
                btn.style.borderColor = active ? (val==='active'?'#6ee7b7':'#fcd34d') : '#e2e8f0';
                btn.style.color       = active ? (val==='active'?'#15803d':'#b45309') : '#64748b';
            });
        } else { showMsg(res.message || 'Failed to update status.', false); }
    })
    .catch(() => showMsg('Request failed. Please try again.', false));
}

function setRole(val) {
    fetch(`/ict/accounts/${currentId}/role`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ role: val })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showMsg('Role updated to ' + (val==='grade_leader'?'Grade Leader':'Teacher') + '.', true);
            document.querySelectorAll('.manage-role-btn').forEach(btn => {
                const active = btn.dataset.val === val;
                btn.style.background  = active ? '#eff6ff' : '#f8fafc';
                btn.style.borderColor = active ? '#93c5fd' : '#e2e8f0';
                btn.style.color       = active ? '#1d4ed8' : '#64748b';
            });
        } else { showMsg(res.message || 'Failed to update role.', false); }
    })
    .catch(() => showMsg('Request failed. Please try again.', false));
}

function doResetPassword() {
    const pw  = document.getElementById('newPassword').value;
    const pw2 = document.getElementById('newPasswordConfirm').value;
    if (!pw || pw.length < 8) { showMsg('Password must be at least 8 characters.', false); return; }
    if (pw !== pw2)            { showMsg('Passwords do not match.', false); return; }

    fetch(`/ict/accounts/${currentId}/reset-password`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ password: pw, password_confirmation: pw2 })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showMsg('Password reset successfully.', true);
            document.getElementById('newPassword').value = '';
            document.getElementById('newPasswordConfirm').value = '';
        } else { showMsg(res.message || 'Failed to reset password.', false); }
    })
    .catch(() => showMsg('Request failed. Please try again.', false));
}

/* Close modals on backdrop click or Escape */
['addAccountModal','manageModal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) { if(e.target===this) this.style.display='none'; });
});
document.addEventListener('keydown', function(e) {
    if (e.key==='Escape') {
        document.getElementById('addAccountModal').style.display='none';
        document.getElementById('manageModal').style.display='none';
    }
});
</script>
@endsection

