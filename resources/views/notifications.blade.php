@extends('layouts.app')

@section('title', 'Notifications')

@section('content')

{{-- ── SKELETON ─────────────────────────────────────────────────────────── --}}
<div id="page-skeleton" class="max-w-4xl mx-auto py-6 space-y-4" aria-hidden="true">
    {{-- Header card --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="skeleton w-12 h-12 rounded-2xl flex-shrink-0"></div>
                <div class="flex flex-col gap-2">
                    <div class="skeleton h-5 rounded w-36"></div>
                    <div class="skeleton h-3 rounded w-48"></div>
                </div>
            </div>
            <div class="flex gap-2">
                <div class="skeleton h-8 rounded-xl w-32"></div>
                <div class="skeleton h-8 rounded-xl w-24"></div>
            </div>
        </div>
        <div class="flex gap-2 pt-4 border-t border-slate-100">
            @for($i=0;$i<3;$i++)<div class="skeleton h-8 rounded-xl w-20"></div>@endfor
        </div>
    </div>
    {{-- Notification rows --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden divide-y divide-slate-50">
        @for($i=0;$i<8;$i++)
        <div class="flex items-start gap-4 px-6 py-4">
            <div class="relative flex-shrink-0">
                <div class="skeleton skeleton-circle w-11 h-11"></div>
                <div class="skeleton skeleton-circle w-4 h-4 absolute -bottom-0.5 -right-0.5"></div>
            </div>
            <div class="flex-1 flex flex-col gap-2">
                <div class="skeleton h-3 rounded w-3/4"></div>
                <div class="skeleton h-2 rounded w-full"></div>
                <div class="skeleton h-2 rounded w-1/3"></div>
            </div>
            @if($i % 3 === 0)<div class="skeleton skeleton-circle w-2.5 h-2.5 mt-2 flex-shrink-0"></div>@endif
        </div>
        @endfor
    </div>
</div>
{{-- ── END SKELETON ─────────────────────────────────────────────────────── --}}
<script>document.addEventListener('DOMContentLoaded',function(){var s=document.getElementById('page-skeleton');if(s)s.style.display='none';});</script>
<div class="max-w-4xl mx-auto py-6">

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
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
                        class="flex items-center gap-1.5 px-4 py-2 text-[12.5px] font-bold text-[#0d326b] bg-blue-50/80 hover:bg-blue-100/80 border border-blue-200/50 rounded-xl transition-all shadow-sm {{ $unreadCount > 0 ? '' : 'hidden' }}">
                    <span class="material-symbols-outlined text-[16px]">done_all</span>
                    Mark all as read
                </button>
                <button onclick="confirmClearRead()"
                        class="flex items-center gap-1.5 px-3.5 py-2 text-[12.5px] font-semibold text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-xl transition-all shadow-sm"
                        title="Delete all read notifications">
                    <span class="material-symbols-outlined text-[16px] text-slate-500">delete_sweep</span>
                    Clear read
                </button>
                <a href="{{ route('settings') }}#notifications"
                   class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-[#0d326b] hover:bg-slate-100 rounded-xl border border-slate-200/80 transition-all shadow-sm"
                   title="Notification Settings">
                    <span class="material-symbols-outlined text-[18px]">settings</span>
                </a>
            </div>
        </div>

        {{-- ── Filter Tabs ──────────────────────────────────────────────── --}}
        <div class="flex items-center gap-1.5 mt-5 pt-4 border-t border-slate-100">
            @php
                $tabs = [
                    'all'    => ['label' => 'All', 'count' => $allCount ?? $notifications->total()],
                    'unread' => ['label' => 'Unread', 'count' => $unreadCount],
                    'read'   => ['label' => 'Read', 'count' => $readCount ?? (($allCount ?? 0) - $unreadCount)],
                ];
            @endphp
            @foreach($tabs as $key => $tab)
            <a href="{{ route('notifications.index', ['filter' => $key]) }}"
               class="px-4 py-1.5 rounded-xl text-[12.5px] font-bold transition-all duration-150 flex items-center gap-2
                      {{ $filter === $key
                         ? 'bg-[#0d326b] text-white shadow-sm'
                         : 'text-slate-500 hover:text-[#0d326b] hover:bg-slate-100/80' }}">
                <span>{{ $tab['label'] }}</span>
                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold
                             {{ $filter === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ── Notification Cards Feed (Uncluttered, clean list) ─────────────── --}}
    @if($notifications->isEmpty())
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm py-20 text-center px-6">
        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-slate-300 text-[32px]">notifications_off</span>
        </div>
        <p class="text-[15px] font-bold text-slate-700">No notifications found</p>
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
                'quiz_answered'           => ['bg' => '#EFF6FF', 'ring' => '#BFDBFE', 'text' => '#1D4ED8'],
                'module_passed'           => ['bg' => '#F5F3FF', 'ring' => '#DDD6FE', 'text' => '#6D28D9'],
                'checkpoint_passed'       => ['bg' => '#FFFBEB', 'ring' => '#FDE68A', 'text' => '#B45309'],
                'level_up'                => ['bg' => '#ECFDF5', 'ring' => '#A7F3D0', 'text' => '#047857'],
                'mastery_promoted'        => ['bg' => '#F5F3FF', 'ring' => '#DDD6FE', 'text' => '#6D28D9'],
                'help_request'            => ['bg' => '#FEF2F2', 'ring' => '#FECACA', 'text' => '#B91C1C'],
                'streak_milestone'        => ['bg' => '#FFF7ED', 'ring' => '#FED7AA', 'text' => '#C2410C'],
                'module_completed'        => ['bg' => '#F0FDF4', 'ring' => '#BBF7D0', 'text' => '#15803D'],
                'challenge_completed'     => ['bg' => '#F5F3FF', 'ring' => '#DDD6FE', 'text' => '#6D28D9'],
                'fingerspelling_completed'=> ['bg' => '#F0FDFA', 'ring' => '#99F6E4', 'text' => '#0D9488'],
                'new_school_year'         => ['bg' => '#EEF2FF', 'ring' => '#C7D2FE', 'text' => '#4338CA'],
                'hint_used'               => ['bg' => '#FFFBEB', 'ring' => '#FDE68A', 'text' => '#D97706'],
            ];
            $c = $colorMap[$notif->type] ?? ['bg' => '#F8FAFC', 'ring' => '#E2E8F0', 'text' => '#475569'];

            $studentId = $notif->data['student_id'] ?? ($notif->student?->student_id ?? null);
            $studentName = $notif->student_name ?? ($notif->student ? trim($notif->student->first_name . ' ' . $notif->student->last_name) : null);
            $initials = $notif->student ? $notif->student->initials : \App\Http\Controllers\NotificationsController::extractInitials($studentName);
            $fallbackUrl = $notif->student_fallback ?? ("https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45");
            $avatarUrl = $notif->student_avatar ?? ($notif->student ? $notif->student->avatarUrl() : $fallbackUrl);
            $hasStudent = !empty($studentId) || !empty($studentName);
            $actionUrl = $notif->action_url;
            if ($actionUrl && preg_match('#^/students/(\d+)$#', $actionUrl, $m)) {
                $actionUrl = '/reports?open_student=' . $m[1];
            } elseif (!$actionUrl && $studentId) {
                $actionUrl = '/reports?open_student=' . $studentId;
            }

            // Clean title of leading emojis for sleek typography
            $cleanTitle = preg_replace('/^[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F900}-\x{1F9FF}\x{1F018}-\x{1F270}\x{2388}\x{2B06}\x{2197}\x{FE0F}\s]+/u', '', $notif->title);
            if (empty($cleanTitle)) {
                $cleanTitle = $notif->title;
            }
        @endphp

        <div class="group relative px-6 py-4 transition-all duration-150 flex items-center gap-4 notif-card hover:bg-slate-50/70 {{ !$notif->is_read ? 'bg-blue-50/20' : '' }}"
             data-id="{{ $notif->id }}"
             data-read="{{ $notif->is_read ? '1' : '0' }}">

            {{-- Left: Student Profile Picture with mini type badge --}}
            <div class="flex-shrink-0 relative">
                @if($hasStudent)
                    <div class="relative w-11 h-11">
                        <img src="{{ $avatarUrl }}"
                             alt="{{ $studentName ?: 'Student' }}"
                             class="w-11 h-11 rounded-full object-cover shadow-sm ring-2 ring-slate-100 bg-[#0d326b]"
                             onerror="this.onerror=null;this.src='{{ $fallbackUrl }}';">
                        <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full flex items-center justify-center text-white shadow-sm ring-1.5 ring-white"
                             style="background: {{ $c['text'] }};"
                             title="{{ ucfirst(str_replace('_', ' ', $notif->type)) }}">
                            <span class="material-symbols-outlined text-[10px] leading-none">{{ $cfg['icon'] }}</span>
                        </div>
                    </div>
                @else
                    <div class="w-11 h-11 rounded-full flex items-center justify-center shadow-sm"
                         style="background: {{ $c['bg'] }}; outline: 1.5px solid {{ $c['ring'] }};">
                        <span class="material-symbols-outlined text-[18px]" style="color: {{ $c['text'] }};">{{ $cfg['icon'] }}</span>
                    </div>
                @endif
            </div>

            {{-- Center: Clean Text & Timestamp --}}
            <div class="flex-1 min-w-0">
                <p class="text-[13.5px] text-slate-800 leading-snug notif-title {{ !$notif->is_read ? 'font-bold' : 'font-medium' }}">
                    {{ $cleanTitle }}
                </p>

                @if($notif->type === 'module_completed')
                    @php
                        // 1. Extract Mastered letters
                        $masteredList = [];
                        if (!empty($notif->data['letters_mastered'])) {
                            $masteredList = is_array($notif->data['letters_mastered']) 
                                ? $notif->data['letters_mastered'] 
                                : explode(',', $notif->data['letters_mastered']);
                        } elseif (!empty($notif->message) && preg_match('/Letters Mastered:\s*([^.]+?)(?=\.\s*(?:Hints|Needs)|$)/i', $notif->message, $m)) {
                            $val = trim($m[1]);
                            if (strcasecmp($val, 'None') !== 0 && !empty($val)) {
                                $masteredList = preg_split('/[\s,]+/', $val, -1, PREG_SPLIT_NO_EMPTY);
                            }
                        }

                        // 2. Extract Hints
                        $hintsList = [];
                        $rawHints = $notif->data['hint_usage'] ?? $notif->data['hints_used'] ?? $notif->data['hints'] ?? null;
                        if (!empty($rawHints)) {
                            if (is_array($rawHints)) {
                                foreach ($rawHints as $k => $v) {
                                    if (is_array($v) && isset($v['letter'])) {
                                        $cnt = $v['count'] ?? 1;
                                        if ($cnt > 0) $hintsList[] = "{$v['letter']} ({$cnt}x)";
                                    } elseif (is_numeric($v) && $v > 0) {
                                        $hintsList[] = "{$k} ({$v}x)";
                                    } elseif (is_string($v) && !empty($v)) {
                                        $hintsList[] = $v;
                                    }
                                }
                            } elseif (is_string($rawHints)) {
                                $hintsList = [$rawHints];
                            }
                        }
                        // Fallback: parse from message text
                        if (empty($hintsList) && !empty($notif->message)) {
                            if (preg_match('/Hints used:\s*([^.]+?)(?=\.\s*(?:Needs|Letters)|$)/i', $notif->message, $hm)) {
                                $val = trim($hm[1]);
                                if (strcasecmp($val, 'None') !== 0 && !empty($val)) {
                                    $hintsList = array_map('trim', explode(',', $val));
                                }
                            } elseif (preg_match('/hints used on letters:\s*([^.]+?)(?=\.\s*|$)/i', $notif->message, $hm)) {
                                $val = trim($hm[1]);
                                if (!empty($val)) {
                                    $hintsList = array_map('trim', explode(',', $val));
                                }
                            }
                        }

                        // 3. Extract Practice letters
                        $practiceList = [];
                        if (!empty($notif->data['needs_practice'])) {
                            $practiceList = is_array($notif->data['needs_practice']) 
                                ? $notif->data['needs_practice'] 
                                : explode(',', $notif->data['needs_practice']);
                        } elseif (!empty($notif->message) && preg_match('/Needs practice on:\s*([^.]+?)(?=\.|$)/i', $notif->message, $pm)) {
                            $val = trim($pm[1]);
                            if (strcasecmp($val, 'None') !== 0 && !empty($val)) {
                                $practiceList = preg_split('/[\s,]+/', $val, -1, PREG_SPLIT_NO_EMPTY);
                            }
                        }
                    @endphp

                    @if(!empty($masteredList) || !empty($hintsList) || !empty($practiceList))
                        <div class="mt-1 flex items-center gap-2 flex-wrap text-[11.5px] text-slate-500">
                            @if(!empty($masteredList))
                                <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/50">
                                    <span class="material-symbols-outlined text-[12px]">verified</span>
                                    Mastered: {{ implode(', ', $masteredList) }}
                                </span>
                            @endif
                            @if(!empty($hintsList))
                                <span class="inline-flex items-center gap-1 font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200/50">
                                    <span class="material-symbols-outlined text-[12px]">lightbulb</span>
                                    Hints: {{ implode(', ', $hintsList) }}
                                </span>
                            @endif
                            @if(!empty($practiceList))
                                <span class="inline-flex items-center gap-1 font-semibold text-orange-700 bg-orange-50 px-2 py-0.5 rounded-full border border-orange-200/50">
                                    <span class="material-symbols-outlined text-[12px]">priority_high</span>
                                    Practice: {{ implode(', ', $practiceList) }}
                                </span>
                            @endif
                        </div>
                    @elseif(!empty($notif->message))
                        <p class="text-[12.5px] text-slate-500 mt-0.5 leading-snug line-clamp-1">{{ $notif->message }}</p>
                    @endif
                @elseif($notif->type === 'hint_used')
                    @php
                        $hintLetter = $notif->data['letter'] ?? null;
                        $hintModule = !empty($notif->data['module_name']) ? ucwords(str_replace('_', ' ', $notif->data['module_name'])) : null;
                    @endphp
                    @if(!empty($hintLetter))
                        <div class="mt-1 flex items-center gap-2 flex-wrap text-[11.5px] text-slate-500">
                            <span class="inline-flex items-center gap-1 font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200/50">
                                <span class="material-symbols-outlined text-[12px]">lightbulb</span>
                                Hint: {{ $hintLetter }}
                            </span>
                            @if(!empty($hintModule))
                                <span class="inline-flex items-center text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full text-[11px]">
                                    {{ $hintModule }}
                                </span>
                            @endif
                        </div>
                    @elseif(!empty($notif->message))
                        <p class="text-[12.5px] text-slate-500 mt-0.5 leading-snug line-clamp-1">{{ $notif->message }}</p>
                    @endif
                @elseif($notif->type === 'quiz_answered')
                    @php
                        $isLate = !empty($notif->data['is_late']) || str_contains($notif->message ?? '', 'Done Late');
                        $hasDeadline = !empty($notif->data['has_deadline']) || $isLate || str_contains($notif->message ?? '', 'On Time');
                        $cleanMessage = preg_replace('/\s*[\(—•-]?\s*(Done Late|On Time)\)?$/i', '', $notif->message ?? '');
                    @endphp
                    <div class="flex items-center gap-2 flex-wrap mt-0.5">
                        <p class="text-[12.5px] text-slate-500 leading-snug">{{ $cleanMessage }}</p>
                        @if($hasDeadline)
                            @if($isLate)
                                <span class="inline-flex items-center gap-1 font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200/80 text-[11px] shadow-xs">
                                    <span class="material-symbols-outlined text-[12px]">schedule</span>
                                    Done Late
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/80 text-[11px] shadow-xs">
                                    <span class="material-symbols-outlined text-[12px]">check_circle</span>
                                    On Time
                                </span>
                            @endif
                        @endif
                    </div>
                @elseif(!empty($notif->message))
                    <p class="text-[12.5px] text-slate-500 mt-0.5 leading-snug line-clamp-1">{{ $notif->message }}</p>
                @endif

                <p class="text-[11.5px] text-slate-400 font-medium mt-1">
                    {{ $notif->created_at->diffForHumans() }}
                </p>
            </div>

            {{-- Right: Clean Action & Hover Controls --}}
            <div class="flex-shrink-0 flex items-center gap-2.5">
                {{-- Actions revealed on hover --}}
                <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                    <button onclick="toggleNotifRead({{ $notif->id }})"
                            class="toggle-read-btn p-1.5 text-slate-400 hover:text-[#0d326b] hover:bg-slate-100 rounded-lg transition-colors"
                            title="{{ $notif->is_read ? 'Mark as unread' : 'Mark as read' }}">
                        <span class="material-symbols-outlined text-[18px]">
                            {{ $notif->is_read ? 'mark_email_unread' : 'mark_email_read' }}
                        </span>
                    </button>
                    <button onclick="deleteNotif({{ $notif->id }})"
                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            title="Delete">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>

                {{-- Primary Action Button --}}
                @if($notif->type === 'new_school_year')
                    @if($notif->action_status === 'completed')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="material-symbols-outlined text-[14px]">check_circle</span>
                            Transition Done
                        </span>
                    @else
                        <button type="button"
                                onclick="openTransitionModal({{ $notif->id }}, '{{ addslashes($notif->data['from_school_year'] ?? 'Previous') }}', '{{ addslashes($notif->data['target_school_year'] ?? 'New') }}')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-[12px] font-black uppercase tracking-wider text-white shadow-md hover:opacity-95 transition-all whitespace-nowrap"
                                style="background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);">
                            <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                            Start S.Y. {{ $notif->data['target_school_year'] ?? '' }}
                        </button>
                    @endif
                @elseif($actionUrl)
                <a href="{{ $actionUrl }}"
                   onclick="markOneRead({{ $notif->id }})"
                   class="px-3.5 py-1.5 rounded-xl text-[12px] font-semibold text-slate-700 bg-white hover:bg-slate-50 hover:text-[#0d326b] border border-slate-200/90 shadow-sm transition-all duration-150 whitespace-nowrap">
                    View
                </a>
                @endif

                {{-- Unread Dot Indicator (Orange dot) --}}
                <div class="w-2.5 h-2.5 flex items-center justify-center">
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
        card.classList.remove('bg-blue-50/20');
        const dot = card.querySelector('.unread-dot');
        if (dot) dot.classList.add('hidden');
        const title = card.querySelector('.notif-title');
        if (title) { title.classList.remove('font-bold'); title.classList.add('font-medium'); }
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
        card.classList.add('bg-blue-50/20');
        const dot = card.querySelector('.unread-dot');
        if (dot) dot.classList.remove('hidden');
        const title = card.querySelector('.notif-title');
        if (title) { title.classList.add('font-bold'); title.classList.remove('font-medium'); }
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
        card.classList.remove('bg-blue-50/20');
        const dot = card.querySelector('.unread-dot');
        if (dot) dot.classList.add('hidden');
        const title = card.querySelector('.notif-title');
        if (title) { title.classList.remove('font-bold'); title.classList.add('font-medium'); }
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

// ── Transition Modal Handlers ────────────────────────────────────────────────
let currentTransitionNotifId = null;

function openTransitionModal(notifId, fromSy, targetSy) {
    currentTransitionNotifId = notifId;
    const modal = document.getElementById('transition-modal');
    const box   = document.getElementById('transition-modal-box');
    const targetEl = document.getElementById('modalTargetSy');
    const fromEl   = document.getElementById('modalFromSy');

    if (targetEl) targetEl.textContent = targetSy || 'New';
    if (fromEl) fromEl.textContent = fromSy || 'Current';

    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0');
        box.classList.remove('scale-95');
    });
}

function closeTransitionModal() {
    const modal = document.getElementById('transition-modal');
    const box   = document.getElementById('transition-modal-box');
    if (!modal) return;
    modal.classList.add('opacity-0');
    box.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        currentTransitionNotifId = null;
    }, 200);
}

async function doConfirmTransition() {
    if (!currentTransitionNotifId) return;

    const btn = document.getElementById('btnConfirmTransition');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="inline-block animate-spin mr-1.5">⏳</span> Archiving & Starting New Year...';
    }

    try {
        const response = await fetch(`/notifications/${currentTransitionNotifId}/confirm-transition`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            alert(data.message || 'School year transition completed successfully!');
            window.location.href = '{{ route("dashboard") }}';
        } else {
            alert(data.message || 'The transition could not be completed. Please try again.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }
    } catch (err) {
        alert('A network error occurred. Please check your connection and try again.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }
}

document.getElementById('transition-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeTransitionModal();
});
</script>

{{-- ── School Year Transition Confirmation Modal ────────────────────────────── --}}
<div id="transition-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[999] hidden flex items-center justify-center opacity-0 transition-opacity duration-200 p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden border border-slate-100 transform scale-95 transition-transform duration-200" id="transition-modal-box">
        <!-- Header -->
        <div class="p-6 bg-gradient-to-br from-indigo-50 to-blue-50 border-b border-indigo-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-600/20 flex-shrink-0">
                <span class="material-symbols-outlined text-[26px]">calendar_month</span>
            </div>
            <div>
                <h3 class="text-[18px] font-black text-slate-800">Start New School Year</h3>
                <p class="text-[12px] text-slate-500 font-medium">Transition your classroom to School Year <span id="modalTargetSy" class="font-bold text-indigo-700">2026-2027</span></p>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
            <div class="bg-indigo-50/60 p-4 rounded-2xl border border-indigo-100 text-[13px] text-indigo-900 space-y-2">
                <p class="font-bold text-[13.5px]">What happens when you confirm:</p>
                <div class="space-y-2 mt-2 text-[12.5px] text-slate-700">
                    <div class="flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-[16px] text-emerald-600 mt-0.5 flex-shrink-0">inventory_2</span>
                        <span>Your classroom data for S.Y. <span id="modalFromSy" class="font-bold text-slate-900">Current</span> will be safely <strong>archived</strong>.</span>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-[16px] text-emerald-600 mt-0.5 flex-shrink-0">group_remove</span>
                        <span>Current students will be set to <strong>inactive</strong> in your classroom so you can re-enroll them or enroll new students for the new school year.</span>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-[16px] text-emerald-600 mt-0.5 flex-shrink-0">verified_user</span>
                        <span><strong>Permanent student accounts, XP, levels, and achievements are NOT deleted.</strong></span>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-[16px] text-emerald-600 mt-0.5 flex-shrink-0">history_edu</span>
                        <span>Curriculum lessons, activities, and past performance remain accessible via the School Year filter.</span>
                    </div>
                </div>
            </div>

            <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200/80 flex items-start gap-2.5 text-[12px] text-amber-800">
                <span class="material-symbols-outlined text-[18px] text-amber-600 flex-shrink-0 mt-0.5">warning</span>
                <span>This action will transition your active classroom to the new school year. Confirm when you are ready to conclude the current school year.</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
            <button type="button"
                    onclick="closeTransitionModal()"
                    class="px-5 py-2.5 rounded-xl text-[12px] font-bold text-slate-600 hover:bg-slate-200/70 transition-colors">
                Cancel
            </button>
            <button type="button"
                    id="btnConfirmTransition"
                    onclick="doConfirmTransition()"
                    class="px-5 py-2.5 rounded-xl text-[12px] font-black uppercase tracking-wider text-white shadow-md hover:opacity-95 transition-all"
                    style="background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);">
                Confirm & Start New School Year
            </button>
        </div>
    </div>
</div>
@endsection