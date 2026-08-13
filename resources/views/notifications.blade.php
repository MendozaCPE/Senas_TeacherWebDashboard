@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="max-w-4xl mx-auto py-8">

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm"
                 style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 100%);">
                <span class="material-symbols-outlined text-white text-[22px]">notifications</span>
            </div>
            <div>
                <h1 class="text-[22px] font-black text-[#0d326b] leading-tight">Notifications</h1>
                <p class="text-[13px] text-slate-500 font-medium mt-0.5">
                    @if($unreadCount > 0)
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                            {{ $unreadCount }} unread notification{{ $unreadCount !== 1 ? 's' : '' }}
                        </span>
                    @else
                        You're all caught up!
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($unreadCount > 0)
            <button onclick="markAllRead()"
                    class="flex items-center gap-2 px-4 py-2.5 text-[13px] font-semibold text-[#0d326b] border border-[#0d326b]/20 rounded-xl hover:bg-[#0d326b]/5 transition-colors">
                <span class="material-symbols-outlined text-[16px]">done_all</span>
                Mark all as read
            </button>
            @endif
            <button onclick="confirmClearRead()"
                    class="flex items-center gap-2 px-4 py-2.5 text-[13px] font-semibold text-slate-500 border border-slate-200 rounded-xl hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-[16px]">delete_sweep</span>
                Clear read
            </button>
        </div>
    </div>

    {{-- ── Filter Tabs ──────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-1 mb-6 bg-white border border-slate-100 rounded-2xl p-1.5 w-fit shadow-sm">
        @foreach(['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $key => $label)
        <a href="{{ route('notifications.index', ['filter' => $key]) }}"
           class="px-5 py-2 rounded-xl text-[13px] font-semibold transition-all duration-150
                  {{ $filter === $key
                     ? 'bg-[#0d326b] text-white shadow-sm'
                     : 'text-slate-500 hover:text-[#0d326b] hover:bg-slate-50' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- ── Notification Cards ───────────────────────────────────────────── --}}
    @if($notifications->isEmpty())
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm py-20 text-center">
        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-slate-300 text-[30px]">notifications_off</span>
        </div>
        <p class="text-[15px] font-bold text-slate-500">No notifications yet</p>
        <p class="text-[13px] text-slate-400 mt-1.5 max-w-xs mx-auto leading-relaxed">
            @if($filter === 'unread') You have no unread notifications.
            @elseif($filter === 'read') No read notifications found.
            @else Student activities like quiz answers, level-ups, and help requests will appear here.
            @endif
        </p>
    </div>
    @else

    <div class="space-y-2" id="notif-page-list">
        @foreach($notifications as $notif)
        @php
    $cfg = \App\Models\TeacherNotification::typeConfig($notif->type);
    $colorMap = [
        'quiz_answered'     => ['bg' => '#EFF6FF', 'ring' => '#BFDBFE', 'text' => '#1D4ED8', 'label_bg' => 'bg-blue-100',   'label_text' => 'text-blue-700'],
        'module_passed'     => ['bg' => '#F5F3FF', 'ring' => '#DDD6FE', 'text' => '#6D28D9', 'label_bg' => 'bg-purple-100', 'label_text' => 'text-purple-700'],
        'checkpoint_passed' => ['bg' => '#FFFBEB', 'ring' => '#FDE68A', 'text' => '#B45309', 'label_bg' => 'bg-amber-100',  'label_text' => 'text-amber-700'],
        'level_up'          => ['bg' => '#ECFDF5', 'ring' => '#A7F3D0', 'text' => '#047857', 'label_bg' => 'bg-emerald-100','label_text' => 'text-emerald-700'],
        'mastery_promoted'  => ['bg' => '#F5F3FF', 'ring' => '#DDD6FE', 'text' => '#6D28D9', 'label_bg' => 'bg-purple-100', 'label_text' => 'text-purple-700'],
        'help_request'      => ['bg' => '#FEF2F2', 'ring' => '#FECACA', 'text' => '#B91C1C', 'label_bg' => 'bg-red-100',    'label_text' => 'text-red-700'],
        'streak_milestone'  => ['bg' => '#FFF7ED', 'ring' => '#FED7AA', 'text' => '#C2410C', 'label_bg' => 'bg-orange-100', 'label_text' => 'text-orange-700'],
        'module_completed'  => ['bg' => '#F0FDF4', 'ring' => '#BBF7D0', 'text' => '#15803D', 'label_bg' => 'bg-emerald-100', 'label_text' => 'text-emerald-700'], // ✅ ADD THIS LINE
    ];
    $c = $colorMap[$notif->type] ?? ['bg' => '#F8FAFC', 'ring' => '#E2E8F0', 'text' => '#475569', 'label_bg' => 'bg-slate-100', 'label_text' => 'text-slate-600'];
    
    $typeLabels = [
        'quiz_answered'    => 'Quiz Answered',
        'module_passed'    => 'Module Passed',
        'checkpoint_passed'=> 'Checkpoint Exam',
        'level_up'         => 'Level Up',
        'mastery_promoted' => 'Promotion',
        'help_request'     => 'Help Request',
        'streak_milestone' => 'Streak',
        'module_completed' => 'Module Completed',
    ];
@endphp
        <div class="group bg-white rounded-2xl border transition-all duration-150 flex items-start gap-4 px-5 py-4 shadow-[0_1px_4px_rgba(0,0,0,0.04)] hover:shadow-[0_4px_16px_rgba(0,0,0,0.07)] notif-card
                    {{ !$notif->is_read ? 'border-blue-200/70 bg-blue-50/30' : 'border-slate-100' }}"
             data-id="{{ $notif->id }}">

            {{-- Icon --}}
            <div class="flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center shadow-sm mt-0.5"
                 style="background: {{ $c['bg'] }}; outline: 1.5px solid {{ $c['ring'] }};">
                <span class="material-symbols-outlined text-[20px]" style="color: {{ $c['text'] }};">{{ $cfg['icon'] }}</span>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-[13.5px] font-{{ !$notif->is_read ? 'bold' : 'semibold' }} text-slate-800 leading-snug">
                                {{ $notif->title }}
                            </p>
                            @if(!$notif->is_read)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                NEW
                            </span>
                            @endif
                        </div>
                        <p class="text-[12.5px] text-slate-500 mt-1 leading-relaxed">{{ $notif->message }}</p>
                    {{-- ✅ PUT THE HINT USAGE DISPLAY RIGHT HERE --}}
            @if($notif->type === 'module_completed' && !empty($notif->data['hint_usage']))
            <div class="mt-2 flex items-center gap-2 flex-wrap">
                <span class="text-[11px] font-semibold text-slate-500 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">bulb</span>
                    Hints used:
                </span>
                @foreach($notif->data['hint_usage'] as $hint)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                        {{ $hint['letter'] }} ×{{ $hint['count'] }}
                    </span>
                @endforeach
                @if($notif->data['hint_count'] === 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                        ✅ No hints used
                    </span>
                @endif
            </div>
            @endif
        </div>
                    {{-- Type badge + time --}}
                    <div class="flex-shrink-0 flex flex-col items-end gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $c['label_bg'] }} {{ $c['label_text'] }}">
                            {{ $typeLabels[$notif->type] ?? $notif->type }}
                        </span>
                        <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap">
                            {{ $notif->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>

                {{-- Action row --}}
                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-100/80">
                    @if($notif->action_url)
                    <a href="{{ $notif->action_url }}"
                       onclick="markOneRead({{ $notif->id }})"
                       class="flex items-center gap-1.5 text-[12px] font-semibold text-[#0d326b] hover:text-[#1e4b8f] transition-colors">
                        <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                        View student
                    </a>
                    @endif
                    @if(!$notif->is_read)
                    <button onclick="markOneRead({{ $notif->id }}, true)"
                            class="flex items-center gap-1.5 text-[12px] font-medium text-slate-400 hover:text-slate-600 transition-colors">
                        <span class="material-symbols-outlined text-[14px]">check</span>
                        Mark read
                    </button>
                    @endif
                    <button onclick="deleteNotif({{ $notif->id }})"
                            class="flex items-center gap-1.5 text-[12px] font-medium text-slate-400 hover:text-red-500 transition-colors ml-auto opacity-0 group-hover:opacity-100">
                        <span class="material-symbols-outlined text-[14px]">delete</span>
                        Delete
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Pagination ───────────────────────────────────────────────────── --}}
    @if($notifications->hasPages())
    <div class="mt-8 flex items-center justify-center">
        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if($notifications->onFirstPage())
                <span class="px-4 py-2.5 text-[13px] font-semibold text-slate-300 border border-slate-100 rounded-xl bg-white cursor-not-allowed">
                    ← Prev
                </span>
            @else
                <a href="{{ $notifications->previousPageUrl() }}"
                   class="px-4 py-2.5 text-[13px] font-semibold text-slate-600 border border-slate-200 rounded-xl bg-white hover:bg-slate-50 hover:text-[#0d326b] transition-colors">
                    ← Prev
                </a>
            @endif

            {{-- Pages --}}
            @foreach($notifications->getUrlRange(max(1,$notifications->currentPage()-2), min($notifications->lastPage(),$notifications->currentPage()+2)) as $page => $url)
                @if($page == $notifications->currentPage())
                    <span class="w-10 h-10 flex items-center justify-center text-[13px] font-bold text-white rounded-xl shadow-sm"
                          style="background: linear-gradient(135deg, #0d326b, #1e4b8f);">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}"
                       class="w-10 h-10 flex items-center justify-center text-[13px] font-semibold text-slate-600 border border-slate-200 rounded-xl bg-white hover:bg-slate-50 hover:text-[#0d326b] transition-colors">
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- Next --}}
            @if($notifications->hasMorePages())
                <a href="{{ $notifications->nextPageUrl() }}"
                   class="px-4 py-2.5 text-[13px] font-semibold text-slate-600 border border-slate-200 rounded-xl bg-white hover:bg-slate-50 hover:text-[#0d326b] transition-colors">
                    Next →
                </a>
            @else
                <span class="px-4 py-2.5 text-[13px] font-semibold text-slate-300 border border-slate-100 rounded-xl bg-white cursor-not-allowed">
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
            <p class="text-slate-500 text-sm leading-relaxed">This will permanently delete all notifications you have already read. Unread ones stay.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="closeClearModal()" class="flex-1 py-3 border border-slate-200 rounded-2xl text-slate-600 font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
            <button onclick="doClearRead()" class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-2xl transition-colors">Clear</button>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function markOneRead(id, stayOnPage = false) {
    await fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        credentials: 'same-origin',
    });
    if (!stayOnPage) return;
    // Update card visually
    const card = document.querySelector(`.notif-card[data-id="${id}"]`);
    if (card) {
        card.classList.remove('border-blue-200/70', 'bg-blue-50/30');
        card.classList.add('border-slate-100');
        card.querySelectorAll('.bg-blue-100.text-blue-700').forEach(el => el.remove());
        card.querySelectorAll('button[onclick*="markOneRead"]').forEach(el => el.remove());
    }
    updatePageUnreadLabel();
}

async function markAllRead() {
    await fetch('{{ route("notifications.read-all") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        credentials: 'same-origin',
    });
    location.reload();
}

async function deleteNotif(id) {
    await fetch(`/notifications/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        credentials: 'same-origin',
    });
    const card = document.querySelector(`.notif-card[data-id="${id}"]`);
    if (card) {
        card.style.transition = 'all 0.25s ease';
        card.style.opacity = '0';
        card.style.transform = 'translateX(20px)';
        setTimeout(() => card.remove(), 250);
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
    await fetch('{{ route("notifications.clear-read") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        credentials: 'same-origin',
    });
    closeClearModal();
    setTimeout(() => location.reload(), 200);
}

function updatePageUnreadLabel() {
    const unreadCards = document.querySelectorAll('.notif-card.border-blue-200\\/70').length;
    // no-op for now; full label update on reload
}

document.getElementById('clear-modal').addEventListener('click', function(e) {
    if (e.target === this) closeClearModal();
});
</script>
@endsection
