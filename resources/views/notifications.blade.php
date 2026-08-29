@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="max-w-4xl mx-auto py-6">

    {{-- ── Page Header (Clean & modern feed style) ──────────────────────── --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-md flex-shrink-0"
                     style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 100%);">
                    <span class="material-symbols-outlined text-white text-[24px]">notifications</span>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-[22px] font-black text-[#0d326b] leading-tight tracking-tight">Notification</h1>
                        <span id="header-unread-badge"
                              class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $unreadCount > 0 ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $unreadCount }} new
                        </span>
                    </div>
                    <p class="text-[13px] text-slate-400 font-medium mt-0.5" id="header-status-text">
                        @if($unreadCount > 0)
                            You have {{ $unreadCount }} unread notification{{ $unreadCount !== 1 ? 's' : '' }}
                        @else
                            You're all caught up!
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button id="page-mark-all-btn"
                        onclick="markAllRead()"
                        class="flex items-center gap-1.5 px-4 py-2.5 text-[13px] font-bold text-[#0d326b] bg-blue-50/80 hover:bg-blue-100/80 border border-blue-200/50 rounded-xl transition-all shadow-sm {{ $unreadCount > 0 ? '' : 'hidden' }}">
                    <span class="material-symbols-outlined text-[17px]">done_all</span>
                    Mark all as read
                </button>
                <button onclick="confirmClearRead()"
                        class="flex items-center gap-1.5 px-3.5 py-2.5 text-[13px] font-semibold text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-xl transition-all shadow-sm"
                        title="Delete all read notifications">
                    <span class="material-symbols-outlined text-[17px] text-slate-500">delete_sweep</span>
                    Clear read
                </button>
                <a href="{{ route('settings') }}#notifications"
                   class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-[#0d326b] hover:bg-slate-100 rounded-xl border border-slate-200/80 transition-all shadow-sm"
                   title="Notification Settings">
                    <span class="material-symbols-outlined text-[20px]">settings</span>
                </a>
            </div>
        </div>

        {{-- ── Filter Tabs ──────────────────────────────────────────────── --}}
        <div class="flex items-center gap-1.5 mt-6 pt-5 border-t border-slate-100">
            @php
                $tabs = [
                    'all'    => ['label' => 'All', 'count' => $allCount ?? $notifications->total()],
                    'unread' => ['label' => 'Unread', 'count' => $unreadCount],
                    'read'   => ['label' => 'Read', 'count' => $readCount ?? (($allCount ?? 0) - $unreadCount)],
                ];
            @endphp
            @foreach($tabs as $key => $tab)
            <a href="{{ route('notifications.index', ['filter' => $key]) }}"
               class="px-4 py-2 rounded-xl text-[13px] font-bold transition-all duration-150 flex items-center gap-2
                      {{ $filter === $key
                         ? 'bg-[#0d326b] text-white shadow-sm'
                         : 'text-slate-500 hover:text-[#0d326b] hover:bg-slate-100/80' }}">
                <span>{{ $tab['label'] }}</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold
                             {{ $filter === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ── Notification Cards Feed ─────────────────────────────────────── --}}
    @if($notifications->isEmpty())
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm py-20 text-center px-6">
        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-slate-300 text-[32px]">notifications_off</span>
        </div>
        <p class="text-[16px] font-bold text-slate-700">No notifications found</p>
        <p class="text-[13px] text-slate-400 mt-1.5 max-w-sm mx-auto leading-relaxed">
            @if($filter === 'unread')
                You have no unread notifications. You're completely up to date!
            @elseif($filter === 'read')
                No read notifications found in your history.
            @else
                Student activities, quiz attempts, module promotions, and alerts will appear here.
            @endif
        </p>
    </div>
    @else

    <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden divide-y divide-slate-100" id="notif-page-list">
        @foreach($notifications as $notif)
        @php
            $cfg = \App\Models\TeacherNotification::typeConfig($notif->type);
            $colorMap = [
                'quiz_answered'           => ['bg' => '#EFF6FF', 'ring' => '#BFDBFE', 'text' => '#1D4ED8', 'label_bg' => 'bg-blue-100',   'label_text' => 'text-blue-700'],
                'module_passed'           => ['bg' => '#F5F3FF', 'ring' => '#DDD6FE', 'text' => '#6D28D9', 'label_bg' => 'bg-purple-100', 'label_text' => 'text-purple-700'],
                'checkpoint_passed'       => ['bg' => '#FFFBEB', 'ring' => '#FDE68A', 'text' => '#B45309', 'label_bg' => 'bg-amber-100',  'label_text' => 'text-amber-700'],
                'level_up'                => ['bg' => '#ECFDF5', 'ring' => '#A7F3D0', 'text' => '#047857', 'label_bg' => 'bg-emerald-100','label_text' => 'text-emerald-700'],
                'mastery_promoted'        => ['bg' => '#F5F3FF', 'ring' => '#DDD6FE', 'text' => '#6D28D9', 'label_bg' => 'bg-purple-100', 'label_text' => 'text-purple-700'],
                'help_request'            => ['bg' => '#FEF2F2', 'ring' => '#FECACA', 'text' => '#B91C1C', 'label_bg' => 'bg-red-100',    'label_text' => 'text-red-700'],
                'streak_milestone'        => ['bg' => '#FFF7ED', 'ring' => '#FED7AA', 'text' => '#C2410C', 'label_bg' => 'bg-orange-100', 'label_text' => 'text-orange-700'],
                'module_completed'        => ['bg' => '#F0FDF4', 'ring' => '#BBF7D0', 'text' => '#15803D', 'label_bg' => 'bg-emerald-100', 'label_text' => 'text-emerald-700'],
                'challenge_completed'     => ['bg' => '#F5F3FF', 'ring' => '#DDD6FE', 'text' => '#6D28D9', 'label_bg' => 'bg-purple-100', 'label_text' => 'text-purple-700'],
                'fingerspelling_completed'=> ['bg' => '#F0FDFA', 'ring' => '#99F6E4', 'text' => '#0D9488', 'label_bg' => 'bg-teal-100',   'label_text' => 'text-teal-700'],
            ];
            $c = $colorMap[$notif->type] ?? ['bg' => '#F8FAFC', 'ring' => '#E2E8F0', 'text' => '#475569', 'label_bg' => 'bg-slate-100', 'label_text' => 'text-slate-600'];

            $studentId = $notif->data['student_id'] ?? ($notif->student?->student_id ?? null);
            $studentName = $notif->student_name ?? ($notif->student ? trim($notif->student->first_name . ' ' . $notif->student->last_name) : null);
            $avatarUrl = $notif->student_avatar ?? ($notif->student ? $notif->student->avatarUrl() : null);
            $fallbackUrl = $notif->student_fallback ?? "https://ui-avatars.com/api/?name=" . urlencode($studentName ?: 'Student') . "&background=0d326b&color=fff&size=128&bold=true&rounded=true";
            $hasStudent = !empty($studentId) || !empty($studentName);
            $actionUrl = $notif->action_url ?? ($studentId ? '/reports?open_student=' . $studentId : null);
        @endphp

        <div class="group relative px-6 py-4.5 transition-all duration-200 flex items-start gap-4 notif-card hover:bg-slate-50/80 {{ !$notif->is_read ? 'bg-blue-50/25' : '' }}"
             data-id="{{ $notif->id }}"
             data-read="{{ $notif->is_read ? '1' : '0' }}">

            {{-- Left: Student Profile Picture with type badge --}}
            <div class="flex-shrink-0 relative">
                @if($hasStudent)
                    <div class="relative w-12 h-12">
                        <img src="{{ $avatarUrl ?: $fallbackUrl }}"
                             alt="{{ $studentName ?: 'Student' }}"
                             class="w-12 h-12 rounded-full object-cover shadow-sm ring-2 ring-slate-100 bg-slate-100"
                             onerror="this.onerror=null;this.src='{{ $fallbackUrl }}';">
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center text-white shadow-sm ring-2 ring-white"
                             style="background: {{ $c['text'] }};"
                             title="{{ ucfirst(str_replace('_', ' ', $notif->type)) }}">
                            <span class="material-symbols-outlined text-[12px] leading-none">{{ $cfg['icon'] }}</span>
                        </div>
                    </div>
                @else
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm"
                         style="background: {{ $c['bg'] }}; outline: 1.5px solid {{ $c['ring'] }};">
                        <span class="material-symbols-outlined text-[22px]" style="color: {{ $c['text'] }};">{{ $cfg['icon'] }}</span>
                    </div>
                @endif
            </div>

            {{-- Center: Title, Message, Breakdown, Time --}}
            <div class="flex-1 min-w-0 pr-2">
                <div class="flex items-baseline gap-2 flex-wrap">
                    <p class="text-[14px] text-slate-800 leading-snug notif-title {{ !$notif->is_read ? 'font-bold' : 'font-semibold' }}">
                        {{ $notif->title }}
                    </p>
                </div>

                @if($notif->type === 'module_completed' && !empty($notif->data))
                    <div class="mt-2 space-y-1.5">
                        @if(!empty($notif->data['letters_mastered']))
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px] text-emerald-600">verified</span>
                                Mastered:
                            </span>
                            @foreach($notif->data['letters_mastered'] as $letter)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200/70">
                                    {{ $letter }}
                                </span>
                            @endforeach
                        </div>
                        @endif

                        @if(!empty($notif->data['needs_practice']))
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px] text-orange-600">priority_high</span>
                                Needs practice:
                            </span>
                            @foreach($notif->data['needs_practice'] as $letter)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700 border border-orange-200/70">
                                    {{ $letter }}
                                </span>
                            @endforeach
                        </div>
                        @endif

                        @if(!empty($notif->data['hint_usage']))
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px] text-amber-600">lightbulb</span>
                                Hints:
                            </span>
                            @foreach($notif->data['hint_usage'] as $hint)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200/70">
                                    {{ $hint['letter'] }} ×{{ $hint['count'] }}
                                </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                @else
                    <p class="text-[13px] text-slate-500 mt-1 leading-relaxed line-clamp-2">{{ $notif->message }}</p>
                @endif

                <div class="flex items-center gap-3 mt-2">
                    <span class="text-[11.5px] font-semibold text-slate-400 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">schedule</span>
                        {{ $notif->created_at->diffForHumans() }}
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md {{ $c['label_bg'] }} {{ $c['label_text'] }}">
                        {{ ucwords(str_replace('_', ' ', $notif->type)) }}
                    </span>
                </div>
            </div>

            {{-- Right: Actions & Unread Indicator Dot --}}
            <div class="flex-shrink-0 flex items-center gap-3 self-center">
                {{-- Action button --}}
                @if($actionUrl)
                <a href="{{ $actionUrl }}"
                   onclick="markOneRead({{ $notif->id }})"
                   class="px-3.5 py-1.5 rounded-xl text-[12px] font-bold text-[#0d326b] bg-slate-100 hover:bg-[#0d326b] hover:text-white border border-slate-200/70 transition-all duration-150 flex items-center gap-1 shadow-sm">
                    <span>View Student</span>
                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                </a>
                @endif

                {{-- Toggle Read/Unread button --}}
                <button onclick="toggleNotifRead({{ $notif->id }})"
                        class="toggle-read-btn p-2 text-slate-400 hover:text-[#0d326b] hover:bg-slate-100 rounded-xl transition-all"
                        title="{{ $notif->is_read ? 'Mark as unread' : 'Mark as read' }}">
                    <span class="material-symbols-outlined text-[18px]">
                        {{ $notif->is_read ? 'mark_email_unread' : 'mark_email_read' }}
                    </span>
                </button>

                {{-- Delete button --}}
                <button onclick="deleteNotif({{ $notif->id }})"
                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all"
                        title="Delete notification">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                </button>

                {{-- Unread Dot Indicator (Orange dot matching Image 1) --}}
                <div class="w-3 h-3 flex items-center justify-center">
                    <span class="unread-dot w-2.5 h-2.5 rounded-full bg-orange-500 shadow-sm {{ $notif->is_read ? 'hidden' : '' }}"></span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Pagination ───────────────────────────────────────────────────── --}}
    @if($notifications->hasPages())
    <div class="mt-8 flex items-center justify-center">
        <div class="flex items-center gap-1.5 bg-white border border-slate-100 p-1.5 rounded-2xl shadow-sm">
            {{-- Previous --}}
            @if($notifications->onFirstPage())
                <span class="px-4 py-2 text-[13px] font-semibold text-slate-300 rounded-xl bg-slate-50 cursor-not-allowed">
                    ← Prev
                </span>
            @else
                <a href="{{ $notifications->previousPageUrl() }}"
                   class="px-4 py-2 text-[13px] font-semibold text-slate-600 rounded-xl hover:bg-slate-100 hover:text-[#0d326b] transition-colors">
                    ← Prev
                </a>
            @endif

            {{-- Pages --}}
            @foreach($notifications->getUrlRange(max(1,$notifications->currentPage()-2), min($notifications->lastPage(),$notifications->currentPage()+2)) as $page => $url)
                @if($page == $notifications->currentPage())
                    <span class="w-9 h-9 flex items-center justify-center text-[13px] font-bold text-white rounded-xl shadow-sm"
                          style="background: linear-gradient(135deg, #0d326b, #1e4b8f);">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}"
                       class="w-9 h-9 flex items-center justify-center text-[13px] font-semibold text-slate-600 rounded-xl hover:bg-slate-100 hover:text-[#0d326b] transition-colors">
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- Next --}}
            @if($notifications->hasMorePages())
                <a href="{{ $notifications->nextPageUrl() }}"
                   class="px-4 py-2 text-[13px] font-semibold text-slate-600 rounded-xl hover:bg-slate-100 hover:text-[#0d326b] transition-colors">
                    Next →
                </a>
            @else
                <span class="px-4 py-2 text-[13px] font-semibold text-slate-300 rounded-xl bg-slate-50 cursor-not-allowed">
                    Next →
                </span>
            @endif
        </div>
    </div>
    @endif

    @endif
</div>

{{-- ── Clear-read confirm modal ─────────────────────────────────────── --}}
<div id="clear-modal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-[999] hidden flex items-center justify-center opacity-0 transition-opacity duration-200">
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-200" id="clear-modal-box">
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-red-500 text-2xl">delete_sweep</span>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Clear read notifications?</h3>
            <p class="text-slate-500 text-sm leading-relaxed">This will permanently delete all notifications you have already read. Unread ones will stay.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="closeClearModal()" class="flex-1 py-3 border border-slate-200 rounded-2xl text-slate-600 font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
            <button onclick="doClearRead()" class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-2xl transition-colors shadow-sm">Clear</button>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function markOneRead(id, stayOnPage = true) {
    const card = document.querySelector(`.notif-card[data-id="${id}"]`);
    if (card && card.getAttribute('data-read') === '1') return;

    try {
        await fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
    } catch(e) {}

    if (card) {
        card.setAttribute('data-read', '1');
        card.classList.remove('bg-blue-50/25');
        const dot = card.querySelector('.unread-dot');
        if (dot) dot.classList.add('hidden');
        const title = card.querySelector('.notif-title');
        if (title) { title.classList.remove('font-bold'); title.classList.add('font-semibold'); }
        const btn = card.querySelector('.toggle-read-btn');
        if (btn) {
            btn.title = 'Mark as unread';
            btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">mark_email_unread</span>';
        }
    }
    updateCounts();
}

async function markOneUnread(id) {
    const card = document.querySelector(`.notif-card[data-id="${id}"]`);
    try {
        await fetch(`/notifications/${id}/unread`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
    } catch(e) {}

    if (card) {
        card.setAttribute('data-read', '0');
        card.classList.add('bg-blue-50/25');
        const dot = card.querySelector('.unread-dot');
        if (dot) dot.classList.remove('hidden');
        const title = card.querySelector('.notif-title');
        if (title) { title.classList.add('font-bold'); title.classList.remove('font-semibold'); }
        const btn = card.querySelector('.toggle-read-btn');
        if (btn) {
            btn.title = 'Mark as read';
            btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">mark_email_read</span>';
        }
    }
    updateCounts();
}

function toggleNotifRead(id) {
    const card = document.querySelector(`.notif-card[data-id="${id}"]`);
    if (!card) return;
    const isRead = card.getAttribute('data-read') === '1';
    if (isRead) {
        markOneUnread(id);
    } else {
        markOneRead(id);
    }
}

async function markAllRead() {
    try {
        await fetch('{{ route("notifications.read-all") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
    } catch(e) {}

    document.querySelectorAll('.notif-card').forEach(card => {
        card.setAttribute('data-read', '1');
        card.classList.remove('bg-blue-50/25');
        const dot = card.querySelector('.unread-dot');
        if (dot) dot.classList.add('hidden');
        const title = card.querySelector('.notif-title');
        if (title) { title.classList.remove('font-bold'); title.classList.add('font-semibold'); }
        const btn = card.querySelector('.toggle-read-btn');
        if (btn) {
            btn.title = 'Mark as unread';
            btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">mark_email_unread</span>';
        }
    });

    const markAllBtn = document.getElementById('page-mark-all-btn');
    if (markAllBtn) markAllBtn.classList.add('hidden');
    updateCounts();
}

async function deleteNotif(id) {
    try {
        await fetch(`/notifications/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
    } catch(e) {}

    const card = document.querySelector(`.notif-card[data-id="${id}"]`);
    if (card) {
        card.style.transition = 'all 0.25s ease';
        card.style.opacity = '0';
        card.style.transform = 'translateX(20px)';
        card.style.maxHeight = card.offsetHeight + 'px';
        setTimeout(() => {
            card.remove();
            updateCounts();
            const remaining = document.querySelectorAll('.notif-card').length;
            if (remaining === 0) {
                location.reload();
            }
        }, 250);
    }
}

function confirmClearRead() {
    const modal = document.getElementById('clear-modal');
    const box   = document.getElementById('clear-modal-box');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => { modal.classList.remove('opacity-0'); box.classList.remove('scale-95'); });
}

function closeClearModal() {
    const modal = document.getElementById('clear-modal');
    const box   = document.getElementById('clear-modal-box');
    modal.classList.add('opacity-0'); box.classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 200);
}

async function doClearRead() {
    try {
        await fetch('{{ route("notifications.clear-read") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
    } catch(e) {}
    closeClearModal();
    setTimeout(() => location.reload(), 200);
}

function updateCounts() {
    const unreadCards = document.querySelectorAll('.notif-card[data-read="0"]').length;
    const badge = document.getElementById('header-unread-badge');
    const statusText = document.getElementById('header-status-text');
    const markAllBtn = document.getElementById('page-mark-all-btn');

    if (badge) {
        badge.textContent = `${unreadCards} new`;
        if (unreadCards > 0) {
            badge.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-orange-100 text-orange-700';
        } else {
            badge.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500';
        }
    }
    if (statusText) {
        statusText.textContent = unreadCards > 0
            ? `You have ${unreadCards} unread notification${unreadCards !== 1 ? 's' : ''}`
            : "You're all caught up!";
    }
    if (markAllBtn) {
        if (unreadCards > 0) markAllBtn.classList.remove('hidden');
        else markAllBtn.classList.add('hidden');
    }

    // Also update header topbar bell badge if present
    const bellBadge = document.getElementById('notif-badge');
    if (bellBadge) {
        if (unreadCards > 0) {
            bellBadge.textContent = unreadCards > 99 ? '99+' : unreadCards;
            bellBadge.classList.remove('hidden');
        } else {
            bellBadge.classList.add('hidden');
        }
    }
}

document.getElementById('clear-modal').addEventListener('click', function(e) {
    if (e.target === this) closeClearModal();
});
</script>
@endsection