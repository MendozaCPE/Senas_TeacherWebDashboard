@extends('layouts.admin')
@section('title', 'Account Management')
@section('content')

<div class="flex flex-col gap-4 pt-4">

    <!-- Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-5">

        {{-- Card 1: Total Accounts — navy gradient --}}
        <div class="text-white" style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%);">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Total Accounts</span>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px] text-white">person</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-white tracking-tight">{{ $totalAccounts }}</p>
            <p class="text-[12px] text-white/70 font-medium">all registered users</p>
        </div>

        {{-- Card 2: Admins — white --}}
        <div style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:#fff;">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Admins</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ $adminCount }}</p>
            <p class="text-[12px] text-slate-400 font-medium">with admin privileges</p>
        </div>

        {{-- Card 3: Teachers — white --}}
        <div style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:#fff;">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Teachers</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1a6fd4] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">school</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ $teacherCount }}</p>
            <p class="text-[12px] text-[#1a6fd4] font-medium">active teacher accounts</p>
        </div>

        {{-- Card 4: Active — emerald white --}}
        <div style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #d1fae5;background:#f0fdf4;">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Active</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-emerald-700 tracking-tight">{{ $activeCount }}</p>
            <p class="text-[12px] text-emerald-600 font-medium">accounts enabled</p>
        </div>

        {{-- Card 5: Inactive — amber gradient --}}
        <div class="text-amber-950" style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border:1px solid rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22);">
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

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5 flex items-center gap-3 flex-wrap">
        <form method="GET" action="{{ route('admin.accounts') }}" class="flex items-center gap-2 flex-wrap w-full">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search by name, email, or username..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-full text-[13px] font-medium bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/30 outline-none text-slate-700"/>
            </div>
            <div class="relative">
                <select name="role" class="appearance-none bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 pr-8 py-2.5 rounded-full border-none outline-none cursor-pointer">
                    <option value="all" {{ $roleFilter==='all'?'selected':'' }}>All Roles</option>
                    <option value="admin" {{ $roleFilter==='admin'?'selected':'' }}>Admin</option>
                    <option value="teacher" {{ $roleFilter==='teacher'?'selected':'' }}>Teacher</option>
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-[15px] text-slate-400 pointer-events-none">expand_more</span>
            </div>
            <div class="relative">
                <select name="status" class="appearance-none bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 pr-8 py-2.5 rounded-full border-none outline-none cursor-pointer">
                    <option value="all" {{ $statusFilter==='all'?'selected':'' }}>All Status</option>
                    <option value="active" {{ $statusFilter==='active'?'selected':'' }}>Active</option>
                    <option value="inactive" {{ $statusFilter==='inactive'?'selected':'' }}>Inactive</option>
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-[15px] text-slate-400 pointer-events-none">expand_more</span>
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-full text-[12px] font-bold text-white flex items-center gap-1.5 transition-all hover:opacity-90"
                    style="background: linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
                <span class="material-symbols-outlined text-[14px]">search</span>Search
            </button>
            @if($search || $roleFilter !== 'all' || $statusFilter !== 'all')
            <a href="{{ route('admin.accounts') }}" class="px-4 py-2.5 rounded-full border border-slate-200 text-[12px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Clear</a>
            @endif
        </form>
    </div>

    <!-- Accounts Table -->
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
            <h3 class="text-[15px] font-black text-[#0d326b]">Accounts
                <span class="ml-2 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[12px] font-bold">{{ $accounts->total() }}</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="border-b border-slate-100 bg-[#f8fafc]">
                        <th class="px-6 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Account</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Email</th>
                        <th class="px-6 py-3.5 text-center text-[11px] font-black uppercase tracking-wider text-slate-400">Role</th>
                        <th class="px-6 py-3.5 text-center text-[11px] font-black uppercase tracking-wider text-slate-400">Status</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">School</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Joined</th>
                        <th class="px-6 py-3.5 text-center text-[11px] font-black uppercase tracking-wider text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $account->avatarUrl() }}"
                                     class="w-8 h-8 rounded-full object-cover border border-slate-100 flex-shrink-0"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($account->name) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
                                <div>
                                    <p class="font-bold text-slate-800">{{ $account->name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ '@' . $account->username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $account->email ?? '—' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider
                                {{ $account->role === 'admin' ? 'bg-[#0d326b] text-white' : 'bg-[#dbeafe] text-[#0d326b]' }}">
                                {{ $account->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider
                                {{ $account->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                                {{ $account->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 text-[12px]">
                            {{ $account->teacher?->school?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-[12px]">
                            {{ $account->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="openAccountModal({{ $account->id }}, '{{ addslashes($account->name) }}', '{{ $account->role }}', '{{ $account->status }}')"
                                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-[#0d326b] hover:text-white flex items-center justify-center transition-colors mx-auto"
                                    title="Manage Account">
                                <span class="material-symbols-outlined icon-outline text-[17px]">more_horiz</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <span class="material-symbols-outlined text-slate-200 text-[48px]">manage_accounts</span>
                            <p class="text-[14px] text-slate-400 font-semibold mt-3">No accounts found</p>
                            <p class="text-[12px] text-slate-300 mt-1">Try adjusting your search or filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($accounts->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <p class="text-[12px] text-slate-400 font-medium">
                Showing {{ $accounts->firstItem() }}–{{ $accounts->lastItem() }} of {{ $accounts->total() }} accounts
            </p>
            <div class="flex items-center gap-1">
                @if($accounts->onFirstPage())
                <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-300"><span class="material-symbols-outlined text-[16px]">chevron_left</span></span>
                @else
                <a href="{{ $accounts->previousPageUrl() }}" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors"><span class="material-symbols-outlined text-[16px] text-slate-600">chevron_left</span></a>
                @endif
                @foreach($accounts->getUrlRange(max(1,$accounts->currentPage()-2), min($accounts->lastPage(),$accounts->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-bold transition-colors {{ $page == $accounts->currentPage() ? 'bg-[#0d326b] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $page }}</a>
                @endforeach
                @if($accounts->hasMorePages())
                <a href="{{ $accounts->nextPageUrl() }}" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors"><span class="material-symbols-outlined text-[16px] text-slate-600">chevron_right</span></a>
                @else
                <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-300"><span class="material-symbols-outlined text-[16px]">chevron_right</span></span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>

<!-- Account Management Modal -->
<div id="accountModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] z-[999] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-300" id="accountModalBox">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-[18px] font-black text-[#0d326b]">Manage Account</h3>
                <p id="modal-user-name" class="text-[13px] text-slate-400 font-medium mt-0.5"></p>
            </div>
            <button onclick="closeAccountModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-[18px] text-slate-600">close</span>
            </button>
        </div>

        <div id="modal-feedback" class="hidden mb-4 px-4 py-3 rounded-xl text-[13px] font-semibold"></div>

        <!-- Status Toggle -->
        <div class="mb-5">
            <p class="text-[12px] font-black uppercase tracking-wider text-slate-400 mb-2">Account Status</p>
            <div class="flex gap-2">
                <button onclick="updateStatus('active')"
                        id="btn-status-active"
                        class="flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>Active
                </button>
                <button onclick="updateStatus('inactive')"
                        id="btn-status-inactive"
                        class="flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">cancel</span>Inactive
                </button>
            </div>
        </div>

        <!-- Role Toggle -->
        <div class="mb-5" id="role-section">
            <p class="text-[12px] font-black uppercase tracking-wider text-slate-400 mb-2">Role</p>
            <div class="flex gap-2">
                <button onclick="updateRole('teacher')"
                        id="btn-role-teacher"
                        class="flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">school</span>Teacher
                </button>
                <button onclick="updateRole('admin')"
                        id="btn-role-admin"
                        class="flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">admin_panel_settings</span>Admin
                </button>
            </div>
        </div>

        <!-- Reset Password -->
        <div>
            <p class="text-[12px] font-black uppercase tracking-wider text-slate-400 mb-2">Reset Password</p>
            <div class="space-y-2">
                <input type="password" id="new-password" placeholder="New password (min 8 characters)"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-[13px] focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/30 outline-none"/>
                <input type="password" id="confirm-password" placeholder="Confirm new password"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-[13px] focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/30 outline-none"/>
                <button onclick="resetPassword()"
                        class="w-full py-2.5 rounded-xl text-[13px] font-bold text-[#0d326b] border-2 border-[#0d326b] hover:bg-[#0d326b] hover:text-white transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">lock_reset</span>Reset Password
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentAccountId = null;
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function openAccountModal(id, name, role, status) {
    currentAccountId = id;
    document.getElementById('modal-user-name').textContent = name;
    document.getElementById('modal-feedback').classList.add('hidden');
    document.getElementById('new-password').value = '';
    document.getElementById('confirm-password').value = '';

    // Highlight current status
    updateStatusButtons(status);
    updateRoleButtons(role);

    const isSelf = id === {{ Auth::id() }};
    document.getElementById('role-section').style.opacity = isSelf ? '0.4' : '1';
    document.getElementById('role-section').style.pointerEvents = isSelf ? 'none' : 'auto';

    const modal = document.getElementById('accountModal');
    const box   = document.getElementById('accountModalBox');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => { modal.classList.remove('opacity-0'); box.classList.remove('scale-95'); });
}

function closeAccountModal() {
    const modal = document.getElementById('accountModal');
    const box   = document.getElementById('accountModalBox');
    modal.classList.add('opacity-0'); box.classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function updateStatusButtons(status) {
    const active   = document.getElementById('btn-status-active');
    const inactive = document.getElementById('btn-status-inactive');
    if (status === 'active') {
        active.className   = 'flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2 border-emerald-500 bg-emerald-50 text-emerald-700';
        inactive.className = 'flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2 border-slate-200 text-slate-400 hover:border-slate-300';
    } else {
        inactive.className = 'flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2 border-red-400 bg-red-50 text-red-600';
        active.className   = 'flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2 border-slate-200 text-slate-400 hover:border-slate-300';
    }
}

function updateRoleButtons(role) {
    const teacher = document.getElementById('btn-role-teacher');
    const admin   = document.getElementById('btn-role-admin');
    if (role === 'admin') {
        admin.className   = 'flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2 border-[#0d326b] bg-[#dbeafe] text-[#0d326b]';
        teacher.className = 'flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2 border-slate-200 text-slate-400 hover:border-slate-300';
    } else {
        teacher.className = 'flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2 border-[#0d326b] bg-[#dbeafe] text-[#0d326b]';
        admin.className   = 'flex-1 py-2.5 rounded-xl text-[13px] font-bold border-2 transition-all flex items-center justify-center gap-2 border-slate-200 text-slate-400 hover:border-slate-300';
    }
}

function showFeedback(msg, isError = false) {
    const fb = document.getElementById('modal-feedback');
    fb.textContent = msg;
    fb.className = `mb-4 px-4 py-3 rounded-xl text-[13px] font-semibold ${isError ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700'}`;
    fb.classList.remove('hidden');
    setTimeout(() => fb.classList.add('hidden'), 4000);
}

async function updateStatus(status) {
    try {
        const res = await fetch(`/admin/accounts/${currentAccountId}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ status }),
        });
        const data = await res.json();
        if (data.success) { updateStatusButtons(data.status); showFeedback('Status updated successfully.'); }
        else showFeedback(data.message || 'Failed to update status.', true);
    } catch { showFeedback('An error occurred.', true); }
}

async function updateRole(role) {
    try {
        const res = await fetch(`/admin/accounts/${currentAccountId}/role`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ role }),
        });
        const data = await res.json();
        if (data.success) { updateRoleButtons(data.role); showFeedback('Role updated successfully.'); }
        else showFeedback(data.message || 'Failed to update role.', true);
    } catch { showFeedback('An error occurred.', true); }
}

async function resetPassword() {
    const pw  = document.getElementById('new-password').value;
    const pw2 = document.getElementById('confirm-password').value;
    if (!pw || pw.length < 8) { showFeedback('Password must be at least 8 characters.', true); return; }
    if (pw !== pw2) { showFeedback('Passwords do not match.', true); return; }
    try {
        const res = await fetch(`/admin/accounts/${currentAccountId}/reset-password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ password: pw, password_confirmation: pw2 }),
        });
        const data = await res.json();
        if (data.success) { document.getElementById('new-password').value = ''; document.getElementById('confirm-password').value = ''; showFeedback('Password reset successfully.'); }
        else showFeedback(data.message || 'Failed to reset password.', true);
    } catch { showFeedback('An error occurred.', true); }
}

document.getElementById('accountModal').addEventListener('click', function(e) {
    if (e.target === this) closeAccountModal();
});
</script>
@endsection
