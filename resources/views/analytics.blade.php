@extends('layouts.app')
@section('bg-class', 'bg-[#f8fafc]')
@section('title', 'Analytics')
@section('content')

{{-- ── SKELETON ─────────────────────────────────────────────────────────── --}}
<div id="page-skeleton" class="space-y-6 pb-12" aria-hidden="true">
    {{-- Filter toolbar --}}
    <div class="bg-white rounded-[22px] border border-slate-100 shadow-sm p-4 flex gap-3 flex-wrap">
        @for($i=0;$i<3;$i++)<div class="skeleton h-9 rounded-[14px] w-36"></div>@endfor
        <div class="skeleton h-9 rounded-[14px] w-24"></div>
        <div class="ml-auto skeleton h-9 rounded-[14px] w-36"></div>
    </div>
    {{-- 4 KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="skeleton skeleton-card h-[130px]"></div>
        @for($i=0;$i<2;$i++)
        <div class="bg-white rounded-[24px] p-6 border border-slate-100 shadow-sm flex flex-col gap-3">
            <div class="flex justify-between"><div class="skeleton h-3 rounded w-32"></div><div class="skeleton w-10 h-10 rounded-xl"></div></div>
            <div class="skeleton h-9 rounded w-20"></div>
            <div class="skeleton h-3 rounded w-36"></div>
        </div>
        @endfor
        <div class="skeleton skeleton-card h-[130px]"></div>
    </div>
    {{-- Insight banner --}}
    <div class="skeleton rounded-[18px] h-16 w-full"></div>
    {{-- Main grid: 7 + 5 cols --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 bg-white rounded-[26px] border border-slate-100 shadow-sm p-6 flex flex-col gap-4">
            <div class="flex justify-between pb-3 border-b border-slate-100">
                <div class="flex flex-col gap-2"><div class="skeleton h-5 rounded w-40"></div><div class="skeleton h-3 rounded w-56"></div></div>
                <div class="skeleton h-9 rounded-[14px] w-48"></div>
            </div>
            @for($i=0;$i<6;$i++)
            <div class="flex items-center gap-3">
                <div class="skeleton w-8 h-8 rounded-lg flex-shrink-0"></div>
                <div class="skeleton skeleton-circle w-9 h-9 flex-shrink-0"></div>
                <div class="flex-1 flex flex-col gap-1.5"><div class="skeleton h-3 rounded w-32"></div><div class="skeleton h-2 rounded w-24"></div></div>
                <div class="skeleton h-4 rounded w-14 ml-auto"></div>
            </div>
            @endfor
        </div>
        <div class="lg:col-span-5 flex flex-col gap-6">
            <div class="bg-white rounded-[26px] border border-slate-100 shadow-sm p-6 flex flex-col gap-4">
                <div class="skeleton h-4 rounded w-40"></div>
                <div class="skeleton rounded-full w-32 h-32 mx-auto"></div>
                @for($i=0;$i<3;$i++)<div class="skeleton h-3 rounded w-full"></div>@endfor
            </div>
            <div class="bg-white rounded-[26px] border border-slate-100 shadow-sm p-6 flex flex-col gap-3">
                <div class="skeleton h-4 rounded w-36"></div>
                @for($i=0;$i<4;$i++)
                <div class="flex items-center gap-2">
                    <div class="skeleton h-3 rounded w-12"></div>
                    <div class="skeleton h-5 rounded flex-1" style="opacity:{{ 1 - $i*0.2 }}"></div>
                </div>
                @endfor
            </div>
        </div>
    </div>
</div>
{{-- ── END SKELETON ─────────────────────────────────────────────────────── --}}
<script>document.addEventListener('DOMContentLoaded',function(){var s=document.getElementById('page-skeleton');if(s)s.style.display='none';});</script>

<style>
:root {
    --navy-950: #071c3f;
    --navy-900: #0d326b;
    --navy-700: #1e4b8f;
    --navy-500: #1a6fd4;
    --navy-400: #3b82f6;
    --navy-200: #bfdbfe;
    --navy-100: #dbeafe;
    --navy-50:  #eff6ff;

    --gold-600: #b45309;
    --gold-500: #d97706;
    --gold-400: #f59e0b;
    --gold-100: #fef3c7;
    --gold-50:  #fffbeb;
}

/* ── Panels & Cards (Dashboard Design System) ── */
.analytics-panel {
    background: #ffffff;
    border-radius: 26px;
    border: 1px solid #edf2f7;
    box-shadow: 0 4px 20px rgba(13, 50, 107, 0.03);
    padding: 24px;
    transition: box-shadow .25s ease;
}
.analytics-panel:hover {
    box-shadow: 0 10px 30px rgba(13, 50, 107, 0.06);
}

.stat-kpi-card {
    border-radius: 24px;
    padding: 22px 24px;
    position: relative;
    overflow: hidden;
    transition: transform .2s ease, box-shadow .2s ease;
    border: 1px solid #f1f5f9;
}
.stat-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 26px rgba(13, 50, 107, 0.08);
}

/* ── Filter Toolbar ── */
.filter-container {
    background: #ffffff;
    border-radius: 22px;
    border: 1px solid #edf2f7;
    box-shadow: 0 2px 10px rgba(13, 50, 107, 0.03);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
}
.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.filter-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.filter-select {
    appearance: none;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 8px 34px 8px 14px;
    font-size: 13px;
    font-weight: 600;
    color: #0d326b;
    cursor: pointer;
    outline: none;
    transition: border-color .15s, background-color .15s;
}
.filter-select:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.filter-wrap .material-symbols-outlined {
    position: absolute;
    right: 10px;
    pointer-events: none;
    font-size: 18px;
    color: #0d326b;
}
.filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #0d326b;
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    padding: 8px 18px;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    transition: background .2s, transform .15s;
}
.filter-btn:hover {
    background: #1a6fd4;
}
.filter-reset {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    padding: 8px 14px;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition: background .15s, color .15s;
}
.filter-reset:hover {
    background: #f1f5f9;
    color: #0d326b;
}

.export-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 100%);
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    padding: 9px 20px;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(13, 50, 107, 0.18);
    transition: transform .15s, box-shadow .2s, background .2s;
}
.export-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(13, 50, 107, 0.25);
    background: linear-gradient(135deg, #1e4b8f 0%, #1a6fd4 100%);
}

/* ── Senya Gold Insight Containers (Always Gold & Emoji Free) ── */
.senya-insight-gold {
    background: linear-gradient(135deg, #fffdf8 0%, #fefce8 100%);
    border: 1.5px solid #fbbf24;
    border-radius: 20px;
    padding: 16px 20px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.08);
}
.senya-insight-gold-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f59e0b 0%, #facc15 50%, #fbbf24 100%);
    color: #78350f;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.25);
}
.senya-insight-gold-title {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #b45309;
    margin-bottom: 2px;
}
.senya-insight-gold-text {
    font-size: 13px;
    font-weight: 500;
    color: #78350f;
    line-height: 1.55;
}

/* ── Interactive Leaderboard / Student Ranking ── */
.rank-item-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid #f1f5f9;
    transition: background .15s, border-color .15s;
}
.rank-item-row:hover {
    background: #f8fafc;
    border-color: #e2e8f0;
}

.rank-badge-podium {
    width: 30px;
    height: 30px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 800;
    flex-shrink: 0;
}
.rank-badge-gold {
    background: linear-gradient(135deg, #f59e0b 0%, #facc15 50%, #fbbf24 100%);
    color: #78350f;
    border: 1px solid #f59e0b;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.25);
}
.rank-badge-silver {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.rank-badge-bronze {
    background: #ffedd5;
    color: #c2410c;
    border: 1px solid #fed7aa;
}
.rank-badge-default {
    background: #f8fafc;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

/* ── Gesture Master-Detail Split Component ── */
.gesture-sign-card {
    border-radius: 18px;
    border: 1px solid #f1f5f9;
    background: #ffffff;
    padding: 16px 18px;
    cursor: pointer;
    transition: all .2s ease;
    text-align: left;
    width: 100%;
}
.gesture-sign-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    transform: translateX(3px);
}
.gesture-sign-card.active {
    border-color: #1a6fd4;
    background: #eff6ff;
    box-shadow: 0 4px 14px rgba(26, 111, 212, 0.08);
}

.gesture-status-pill {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.gesture-status-mastered {
    background: #dbeafe;
    color: #0d326b;
    border: 1px solid #bfdbfe;
}
.gesture-status-practice {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

/* ── Tooltips & Charts ── */
.chart-wrapper {
    position: relative;
    width: 100%;
}
.chart-tooltip {
    position: absolute;
    pointer-events: none;
    background: #071c3f;
    color: #ffffff;
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 11.5px;
    font-weight: 700;
    box-shadow: 0 4px 14px rgba(0,0,0,0.25);
    white-space: nowrap;
    opacity: 0;
    transform: translate(-50%, -100%);
    transition: opacity .15s ease, transform .15s ease;
    z-index: 40;
}
.chart-tooltip.visible {
    opacity: 1;
}

/* ── PDF Modal ── */
.pdf-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(7, 28, 63, 0.45);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s ease;
}
.pdf-modal-overlay.open {
    opacity: 1;
    pointer-events: auto;
}
.pdf-modal {
    background: #ffffff;
    border-radius: 28px;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 24px 60px rgba(7, 28, 63, 0.25);
    overflow: hidden;
}

/* ── Sign Type Filter Tabs ── */
.sign-type-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    border: 1.5px solid #e2e8f0;
    background: #f8fafc;
    color: #64748b;
    cursor: pointer;
    transition: all .15s ease;
}
.sign-type-tab:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0d326b;
}
.sign-type-tab.active {
    background: #0d326b;
    border-color: #0d326b;
    color: #ffffff;
}
.sign-type-tab[data-type="dynamic"].active {
    background: #7c3aed;
    border-color: #7c3aed;
    color: #ffffff;
}
</style>

<div class="space-y-6 pb-12">

    {{-- ══════════ 1. TOOLBAR: FILTER + EXPORT ══════════ --}}
    @php $af = session('analytics_filters', []); @endphp
    <form method="POST" action="{{ route('analytics.filter') }}" id="filterForm">
        @csrf
        <div class="filter-container">
            <div class="filter-group">
                <div class="flex items-center gap-2 mr-2">
                    <span class="material-symbols-outlined text-[#0d326b] text-[22px]">tune</span>
                    <span class="text-[13px] font-bold text-[#0d326b] uppercase tracking-wider">Filter Period</span>
                </div>

                <div class="filter-wrap">
                    <select name="period" id="periodSelect" class="filter-select">
                        <option value="weekly" {{ ($af['period'] ?? 'weekly') === 'weekly' ? 'selected' : '' }}>Weekly Trend</option>
                        <option value="monthly" {{ ($af['period'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ ($af['period'] ?? '') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ ($af['period'] ?? '') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <div class="filter-wrap">
                    <select name="year" class="filter-select">
                        @php $currentYear = date('Y'); @endphp
                        @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                        <option value="{{ $y }}" {{ ($af['year'] ?? $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <div class="filter-wrap {{ in_array($af['period'] ?? 'weekly', ['monthly', 'quarterly']) ? '' : 'hidden' }}" id="monthFilterWrap">
                    <select name="month" class="filter-select">
                        @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m => $name)
                        <option value="{{ $m + 1 }}" {{ ($af['month'] ?? date('n')) == ($m + 1) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <a href="{{ route('analytics') }}" onclick="event.preventDefault(); fetch('{{ route('analytics.filter') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}, body:JSON.stringify({period:'weekly',year:{{ date('Y') }},month:{{ date('n') }}})}).then(()=>window.location.href='{{ route('analytics') }}')" class="filter-reset">Reset</a>
                <button type="submit" class="filter-btn">
                    <span class="material-symbols-outlined text-[16px]">refresh</span>
                    Apply
                </button>
            </div>

            {{-- Export PDF Button --}}
            <button type="button" class="export-btn" onclick="openAnalyticsPdfModal()">
                <span class="material-symbols-outlined text-[19px]">picture_as_pdf</span>
                Export Report
            </button>
        </div>
    </form>

    {{-- ══════════ 2. CLASS SUMMARY KPI CARDS ══════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        {{-- Card 1: Avg Quiz Score --}}
        <div class="stat-kpi-card text-white" style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 55%, #1a6fd4 100%);">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Avg Quiz Score</span>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px] text-white">quiz</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-white tracking-tight">{{ number_format($avgQuizScore, 1) }}%</p>
            <p class="text-[12px] text-white/70 font-medium">Overall quiz score average</p>
        </div>

        {{-- Card 2: Gesture Mastery --}}
        <div class="stat-kpi-card bg-white">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Gesture Mastery</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">military_tech</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ number_format($avgMastery, 1) }}%</p>
            <p class="text-[12px] text-slate-400 font-medium">Signs marked as mastered</p>
        </div>

        {{-- Card 3: Lesson Completion --}}
        <div class="stat-kpi-card bg-white">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Lesson Completion</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1a6fd4] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ number_format($completionRate, 1) }}%</p>
            <p class="text-[12px] text-slate-400 font-medium">Completed vs assigned lessons</p>
        </div>

        {{-- Card 4: Active Engagement (1 day / X days) --}}
        <div class="stat-kpi-card text-amber-950" style="background: linear-gradient(135deg, #f59e0b 0%, #facc15 50%, #fbbf24 100%); border-color: rgba(245, 158, 11, 0.5); box-shadow: 0 4px 16px rgba(245, 158, 11, 0.22);">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Active Streak</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 text-amber-950 flex items-center justify-center backdrop-blur-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">bolt</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-amber-950 tracking-tight">
                {{ $avgStreakDays }} {{ $avgStreakDays === 1 ? 'day' : 'days' }}
            </p>
            <p class="text-[12px] text-amber-950/80 font-bold">{{ $activeLast7Pct }}% active in last 7 days</p>
        </div>
    </div>

    {{-- Top Senya Pulse Insight Banner --}}
    <div class="senya-insight-gold">
        <div class="senya-insight-gold-icon">
            <span class="material-symbols-outlined text-[20px]">lightbulb</span>
        </div>
        <div>
            <div class="senya-insight-gold-title">Senya Class Overview Insight</div>
            <div class="senya-insight-gold-text">{!! $senyaInsights['kpi'] ?? '' !!}</div>
        </div>
    </div>

    {{-- ══════════ 3. TOP SECTION: STUDENT RANKING + GESTURE MASTERY / SCORE DISTRIBUTIONS ══════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT 7 COLS: STUDENT RANKING (WITH PER-LESSON SWITCHER) --}}
        <div class="lg:col-span-7 analytics-panel flex flex-col justify-between space-y-5">
            <div>
                {{-- Header & Selector --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#0d326b] text-[22px]">workspace_premium</span>
                            <h3 class="text-[18px] font-black text-[#0d326b]">Student Ranking</h3>
                        </div>
                        <p class="text-[12px] text-slate-400 font-medium mt-0.5" id="leaderboardModeSubtitle">
                            Overall class score average across completed quizzes
                        </p>
                    </div>

                    {{-- Lesson / Checkpoint Exam Selector Dropdown --}}
                    <div class="filter-wrap shrink-0">
                        <select id="leaderboardLessonSelector" class="filter-select max-w-[280px]">
                            @php
                                $hasGroups = collect($availableLessonsList)->contains(fn($item) => !empty($item['group']));
                            @endphp
                            @if($hasGroups)
                                <option value="all">All Lessons (Overall Class)</option>
                                @php
                                    $lessonGroupItems = collect($availableLessonsList)->where('group', 'Lessons');
                                    $examGroupItems = collect($availableLessonsList)->where('group', 'Checkpoint Exams');
                                @endphp
                                @if($lessonGroupItems->isNotEmpty())
                                    <optgroup label="Lessons">
                                        @foreach($lessonGroupItems as $lItem)
                                            <option value="{{ $lItem['id'] }}">{{ $lItem['title'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if($examGroupItems->isNotEmpty())
                                    <optgroup label="Checkpoint Exams">
                                        @foreach($examGroupItems as $eItem)
                                            <option value="{{ $eItem['id'] }}">{{ $eItem['title'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @else
                                @foreach($availableLessonsList as $lItem)
                                    <option value="{{ $lItem['id'] }}">{{ $lItem['title'] }}</option>
                                @endforeach
                            @endif
                        </select>
                        <span class="material-symbols-outlined">expand_more</span>
                    </div>
                </div>

                {{-- Leaderboard Panes Container --}}
                <div class="mt-4 space-y-2.5 max-h-[460px] overflow-y-auto pr-1">
                    @foreach($lessonLeaderboards as $lId => $lData)
                        <div class="leaderboard-table-pane {{ $lId !== 'all' ? 'hidden' : '' }} space-y-2.5" data-lesson-id="{{ $lId }}">
                            @if(empty($lData['rankings']) || count($lData['rankings']) === 0)
                                <div class="text-center py-12 text-slate-400">
                                    <span class="material-symbols-outlined text-[36px] text-slate-300 block mb-1">sentiment_dissatisfied</span>
                                    <p class="text-[13px] font-semibold">
                                        @if(!empty($lData['is_exam']))
                                            No student attempts recorded for this checkpoint exam yet.
                                        @else
                                            No quiz attempts recorded for this lesson yet.
                                        @endif
                                    </p>
                                </div>
                            @else
                                @foreach($lData['rankings'] as $st)
                                    @php
                                        $badgeClass = $st['rank'] === 1 ? 'rank-badge-gold' : ($st['rank'] === 2 ? 'rank-badge-silver' : ($st['rank'] === 3 ? 'rank-badge-bronze' : 'rank-badge-default'));
                                    @endphp
                                    <div class="rank-item-row">
                                        <div class="flex items-center gap-3.5 min-w-0">
                                            <div class="rank-badge-podium {{ $badgeClass }}">
                                                @if($st['rank'] === 1)
                                                    <span class="material-symbols-outlined text-[16px]">workspace_premium</span>
                                                @elseif($st['rank'] === 2)
                                                    <span class="material-symbols-outlined text-[16px]">military_tech</span>
                                                @elseif($st['rank'] === 3)
                                                    <span class="material-symbols-outlined text-[16px]">star</span>
                                                @else
                                                    {{ $st['rank'] }}
                                                @endif
                                            </div>

                                            <img src="{{ $st['avatar_url'] ?? ('https://ui-avatars.com/api/?name=' . urlencode($st['initials'] ?? 'ST') . '&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45') }}"
                                                 alt="{{ $st['name'] }}"
                                                 class="w-9 h-9 rounded-full object-cover shadow-sm bg-[#0d326b] shrink-0"
                                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($st['initials'] ?? 'ST') }}&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45';" />

                                            <div class="min-w-0">
                                                <h4 class="text-[13.5px] font-bold text-[#0d326b] truncate">{{ $st['name'] }}</h4>
                                                @if($lId === 'all')
                                                    <p class="text-[11.5px] text-slate-400 font-medium">
                                                        {{ $st['quizzes_count'] ?? 1 }} {{ ($st['quizzes_count'] ?? 1) === 1 ? 'quiz' : 'quizzes' }} · {{ $st['total_attempts'] ?? 1 }} total {{ ($st['total_attempts'] ?? 1) === 1 ? 'attempt' : 'attempts' }}
                                                    </p>
                                                @else
                                                    <p class="text-[11.5px] text-slate-400 font-medium">
                                                        Achieved on attempt #{{ $st['attempts_to_achieve'] }}
                                                        @if(($st['total_attempts'] ?? 1) > 1)
                                                            · ({{ $st['total_attempts'] }} total tries)
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="text-right shrink-0 pl-3">
                                            <span class="text-[16px] font-black text-[#0d326b]">
                                                {{ number_format($st['best_score'], 1) }}%
                                            </span>
                                            <div class="w-20 bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                                                <div class="bg-[#0d326b] h-1.5 rounded-full" style="width: {{ min(100, max(5, $st['best_score'])) }}%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Leaderboard Senya Insight --}}
            <div class="senya-insight-gold mt-3">
                <div class="senya-insight-gold-icon">
                    <span class="material-symbols-outlined text-[19px]">insights</span>
                </div>
                <div>
                    <div class="senya-insight-gold-title">Student Ranking Insight</div>
                    <div class="senya-insight-gold-text">{!! $senyaInsights['leaderboard'] ?? '' !!}</div>
                </div>
            </div>
        </div>

        {{-- RIGHT 5 COLS: GESTURE MASTERY DISTRIBUTION (SIGN PRACTICE) & QUIZ SCORE DISTRIBUTION --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- Gesture Mastery Distribution (Sign Practice) --}}
            <div class="analytics-panel space-y-4">
                <div>
                    <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Sign Practice</span>
                    <h3 class="text-[17px] font-black text-[#0d326b]">Gesture Mastery Distribution</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Breakdown of student gesture practice proficiency</p>
                </div>

                @php
                    $mColors = ['#93c5fd', '#60a5fa', '#1e4b8f', '#0d326b'];
                    $mGradients = [
                        ['from' => '#bfdbfe', 'to' => '#93c5fd'],
                        ['from' => '#93c5fd', 'to' => '#60a5fa'],
                        ['from' => '#3b82f6', 'to' => '#1e4b8f'],
                        ['from' => '#1e4b8f', 'to' => '#0d326b'],
                    ];

                    $masteryActive = [];
                    if ($masteryTotal > 0) {
                        foreach ($masteryDistribution as $i => $seg) {
                            if (($seg['count'] ?? 0) > 0) {
                                $masteryActive[] = [
                                    'index' => $i,
                                    'label' => $seg['label'],
                                    'key' => $seg['key'] ?? '',
                                    'count' => $seg['count'],
                                    'pct' => $seg['pct'],
                                    'color' => $mColors[$i % count($mColors)],
                                    'grad_id' => 'masteryGrad' . $i,
                                    'grad_from' => $mGradients[$i % count($mGradients)]['from'],
                                    'grad_to' => $mGradients[$i % count($mGradients)]['to'],
                                ];
                            }
                        }
                    }

                    $activeCount = count($masteryActive);
                    $maxSegCount = $activeCount > 0 ? max(1, ...array_map(fn($s) => $s['count'], $masteryActive)) : 1;

                    $masteryPaths = [];
                    $cx = 80;
                    $cy = 80;
                    $innerR = 40;
                    $gapAngle = ($activeCount > 1) ? 0.08 : 0; // ~4.5 deg gap between slices
                    $currentAngle = -M_PI / 2; // Start from 12 o'clock

                    foreach ($masteryActive as $seg) {
                        $fraction = $seg['count'] / $masteryTotal;
                        $angleSpan = $fraction * (2 * M_PI);

                        // Variable radius: Outer radius scales from 56px to 74px based on count
                        $outerR = round(56 + (18 * ($seg['count'] / $maxSegCount)), 1);

                        if ($activeCount === 1) {
                            $segStart = $currentAngle;
                            $segEnd = $currentAngle + (2 * M_PI) - 0.001;
                        } else {
                            $segStart = $currentAngle + ($gapAngle / 2);
                            $segEnd = $currentAngle + $angleSpan - ($gapAngle / 2);
                            if ($segEnd <= $segStart) {
                                $segStart = $currentAngle;
                                $segEnd = $currentAngle + $angleSpan;
                            }
                        }

                        $x1 = round($cx + $outerR * cos($segStart), 2);
                        $y1 = round($cy + $outerR * sin($segStart), 2);
                        $x2 = round($cx + $outerR * cos($segEnd), 2);
                        $y2 = round($cy + $outerR * sin($segEnd), 2);
                        $x3 = round($cx + $innerR * cos($segEnd), 2);
                        $y3 = round($cy + $innerR * sin($segEnd), 2);
                        $x4 = round($cx + $innerR * cos($segStart), 2);
                        $y4 = round($cy + $innerR * sin($segStart), 2);

                        $largeArc = ($segEnd - $segStart > M_PI) ? 1 : 0;
                        $pathD = "M {$x1} {$y1} A {$outerR} {$outerR} 0 {$largeArc} 1 {$x2} {$y2} L {$x3} {$y3} A {$innerR} {$innerR} 0 {$largeArc} 0 {$x4} {$y4} Z";

                        $masteryPaths[] = array_merge($seg, [
                            'd' => $pathD,
                            'outerR' => $outerR,
                        ]);

                        $currentAngle += $angleSpan;
                    }
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-1">
                    {{-- Variable-Radius Donut SVG (Shades of Blue only) --}}
                    <div class="relative flex items-center justify-center">
                        <div id="masteryDonutTooltip" class="chart-tooltip">
                            <span id="masteryDonutLabel"></span>: <span id="masteryDonutValue"></span>
                        </div>

                        <svg viewBox="0 0 160 160" class="w-36 h-36 overflow-visible">
                            <defs>
                                <filter id="masterySliceShadow" x="-10%" y="-10%" width="120%" height="120%">
                                    <feDropShadow dx="0" dy="1.5" stdDeviation="1.5" flood-opacity="0.08"/>
                                </filter>
                                @foreach($masteryPaths as $seg)
                                <linearGradient id="{{ $seg['grad_id'] }}" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="{{ $seg['grad_from'] }}"/>
                                    <stop offset="100%" stop-color="{{ $seg['grad_to'] }}"/>
                                </linearGradient>
                                @endforeach
                            </defs>

                            <!-- Background subtle track ring -->
                            <circle cx="80" cy="80" r="54" fill="none" stroke="#f1f5f9" stroke-width="26" opacity="0.6"></circle>

                            @if($masteryTotal > 0)
                                @foreach($masteryPaths as $seg)
                                <path class="mastery-donut-hit cursor-pointer transition-all duration-200 hover:opacity-90 hover:brightness-110"
                                      d="{{ $seg['d'] }}"
                                      fill="url(#{{ $seg['grad_id'] }})"
                                      stroke="#ffffff"
                                      stroke-width="2"
                                      stroke-linejoin="round"
                                      filter="url(#masterySliceShadow)"
                                      data-label="{{ $seg['label'] }}"
                                      data-value="{{ $seg['count'] }} ({{ $seg['pct'] }}%)"></path>
                                @endforeach

                                <!-- Center cutout circle with elevation -->
                                <circle cx="80" cy="80" r="39" fill="#ffffff" filter="url(#masterySliceShadow)"></circle>
                            @endif
                        </svg>

                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                            <span class="text-2xl font-black text-[#0d326b] leading-none">{{ $masteryTotal }}</span>
                            <span class="text-[8.5px] font-extrabold uppercase tracking-widest text-slate-400 mt-0.5">Attempts</span>
                        </div>
                    </div>

                    {{-- Legend --}}
                    <div class="space-y-2">
                        @foreach($masteryDistribution as $i => $seg)
                        <div class="flex items-center justify-between text-[12px]">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $mColors[$i % count($mColors)] }};"></span>
                                <span class="font-bold text-[#0d326b] truncate">{{ $seg['label'] }}</span>
                            </div>
                            <span class="font-semibold text-slate-500 shrink-0">{{ $seg['count'] }} ({{ $seg['pct'] }}%)</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Gesture Mastery Insight --}}
                <div class="senya-insight-gold mt-2">
                    <div class="senya-insight-gold-icon">
                        <span class="material-symbols-outlined text-[19px]">sign_language</span>
                    </div>
                    <div>
                        <div class="senya-insight-gold-title">Mastery Insight</div>
                        <div class="senya-insight-gold-text">{!! $senyaInsights['mastery'] ?? '' !!}</div>
                    </div>
                </div>
            </div>

            {{-- Score Distribution --}}
            <div class="analytics-panel space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-black text-[#0d326b]">Quiz Score Ranges</h3>
                        <p class="text-[11.5px] text-slate-400">Class score frequencies</p>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 text-[18px]">bar_chart</span>
                </div>

                @php $scoreTotal = collect($scoreBuckets)->sum('count'); @endphp
                <div class="space-y-2 pt-1">
                    @foreach($scoreBuckets as $sb)
                    @php $sbPct = $scoreTotal > 0 ? round(($sb['count'] / $scoreTotal) * 100) : 0; @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-[11.5px] font-bold">
                            <span class="text-[#0d326b]">{{ $sb['label'] }}%</span>
                            <span class="text-slate-500 font-semibold">{{ $sb['count'] }} ({{ $sbPct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-[#0d326b] h-1.5 rounded-full" style="width: {{ $sbPct }}%;"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

    {{-- ══════════ 4. SECOND SECTION: PROGRESS CHART + LESSON DIFFICULTY ══════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Class Progress Over Time --}}
        <div class="analytics-panel flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Performance Trend</span>
                        <h3 class="text-[17px] font-black text-[#0d326b]">Class Progress Over Time</h3>
                        <p class="text-[12px] text-slate-400 mt-0.5">Average quiz score progression across {{ request('period', 'weekly') }} intervals</p>
                    </div>
                    <span class="text-[11px] font-bold text-[#0d326b] bg-blue-50 px-3 py-1.5 rounded-full border border-blue-100 shrink-0">
                        {{ count($progressOverTime) }} data points
                    </span>
                </div>

                @php
                    $chartW = 600; $chartH = 190;
                    $padL = 34; $padR = 12; $padT = 20; $padB = 26;
                    $plotW = $chartW - $padL - $padR;
                    $plotH = $chartH - $padT - $padB;
                    $count = count($progressOverTime);

                    $pts = [];
                    foreach ($progressOverTime as $idx => $item) {
                        $x = $count > 1 ? $padL + ($idx / ($count - 1)) * $plotW : $padL + $plotW / 2;
                        $y = $padT + $plotH - (($item['value']) / 100) * $plotH;
                        $pts[] = ['x' => round($x, 2), 'y' => round($y, 2), 'value' => $item['value'], 'label' => $item['label']];
                    }

                    $curvePath = '';
                    $areaPath = '';
                    if (count($pts) > 0) {
                        $curvePath = "M {$pts[0]['x']},{$pts[0]['y']}";
                        for ($i = 0; $i < count($pts) - 1; $i++) {
                            $p0 = $pts[$i];
                            $p1 = $pts[$i + 1];
                            $dx = ($p1['x'] - $p0['x']) / 2;
                            $c1x = $p0['x'] + $dx; $c1y = $p0['y'];
                            $c2x = $p1['x'] - $dx; $c2y = $p1['y'];
                            $curvePath .= " C {$c1x},{$c1y} {$c2x},{$c2y} {$p1['x']},{$p1['y']}";
                        }
                        $areaPath = $curvePath . " L " . ($padL + $plotW) . "," . ($padT + $plotH) . " L " . $padL . "," . ($padT + $plotH) . " Z";
                    }
                @endphp

                <div class="chart-wrapper">
                    <div id="lineChartTooltip" class="chart-tooltip">
                        <span id="lineTooltipLabel"></span>: <span id="lineTooltipValue"></span>%
                    </div>

                    @if(empty($pts))
                        <p class="text-slate-400 text-[13px] text-center py-12">No progress data recorded yet.</p>
                    @else
                        <div class="w-full">
                            <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="w-full h-auto">
                                <defs>
                                    <linearGradient id="progFill" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#1a6fd4" stop-opacity="0.20" />
                                        <stop offset="100%" stop-color="#1a6fd4" stop-opacity="0.0" />
                                    </linearGradient>
                                    <linearGradient id="progLine" x1="0" y1="0" x2="100%" y2="0">
                                        <stop offset="0%" stop-color="#1e4b8f" />
                                        <stop offset="100%" stop-color="#0d326b" />
                                    </linearGradient>
                                </defs>

                                @foreach([0, 25, 50, 75, 100] as $gv)
                                @php $gy = round($padT + $plotH - ($gv / 100) * $plotH, 1); @endphp
                                <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $padL + $plotW }}" y2="{{ $gy }}" stroke="#f1f5f9" stroke-width="0.8" stroke-dasharray="3,3"/>
                                <text x="4" y="{{ $gy + 3.5 }}" font-size="8.5" fill="#94a3b8" font-weight="600">{{ $gv }}%</text>
                                @endforeach

                                @if(!empty($areaPath))
                                    <path d="{{ $areaPath }}" fill="url(#progFill)"/>
                                @endif
                                @if(!empty($curvePath))
                                    <path d="{{ $curvePath }}" fill="none" stroke="url(#progLine)" stroke-width="2.5" stroke-linecap="round"/>
                                @endif

                                @foreach($pts as $p)
                                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4" fill="#0d326b" stroke="#ffffff" stroke-width="2" class="cursor-pointer"/>
                                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="12" fill="transparent" class="line-point-hit cursor-pointer" data-label="{{ $p['label'] }}" data-value="{{ $p['value'] }}"/>
                                    <text x="{{ $p['x'] }}" y="{{ $chartH - 8 }}" font-size="8" fill="#94a3b8" font-weight="600" text-anchor="middle">{{ $p['label'] }}</text>
                                @endforeach
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Progress Senya Insight --}}
            <div class="senya-insight-gold mt-2">
                <div class="senya-insight-gold-icon">
                    <span class="material-symbols-outlined text-[19px]">trending_up</span>
                </div>
                <div>
                    <div class="senya-insight-gold-title">Trend Insight</div>
                    <div class="senya-insight-gold-text">{!! $senyaInsights['progress'] ?? '' !!}</div>
                </div>
            </div>
        </div>

        {{-- Lesson Difficulty Ranking --}}
        <div class="analytics-panel flex flex-col justify-between space-y-4">
            <div>
                <div class="mb-3">
                    <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Focus Areas</span>
                    <h3 class="text-[17px] font-black text-[#0d326b]">Lesson Difficulty Ranking</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Sorted from lowest average score to highest</p>
                </div>

                @if($lessonDifficulty->isEmpty())
                    <p class="text-slate-400 text-[13px] text-center py-12">No lesson score data recorded yet.</p>
                @else
                    <div class="space-y-3 max-h-[260px] overflow-y-auto pr-1">
                        @foreach($lessonDifficulty as $l)
                        <div class="p-3 rounded-2xl bg-slate-50/70 border border-slate-100 space-y-1.5">
                            <div class="flex items-center justify-between text-[12.5px]">
                                <span class="font-bold text-[#0d326b] truncate max-w-[70%]">{{ $l['title'] }}</span>
                                <span class="font-black text-[#0d326b]">{{ $l['avg_score'] }}% avg</span>
                            </div>
                            <div class="w-full bg-slate-200/70 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full bg-[#0d326b]" style="width: {{ min(100, max(5, $l['avg_score'])) }}%;"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Difficulty Senya Insight --}}
            <div class="senya-insight-gold mt-2">
                <div class="senya-insight-gold-icon">
                    <span class="material-symbols-outlined text-[19px]">psychology</span>
                </div>
                <div>
                    <div class="senya-insight-gold-title">Lesson Difficulty Recommendation</div>
                    <div class="senya-insight-gold-text">{!! $senyaInsights['difficulty'] ?? '' !!}</div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════ 5. GESTURE PERFORMANCE ANALYTICS: MASTER-DETAIL INTERACTIVE VIEW ══════════ --}}
    <div class="analytics-panel space-y-6">

        {{-- Header & High-Level Summary Chips --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Gesture Practice Tracking</span>
                <h3 class="text-[19px] font-black text-[#0d326b]">Gesture Performance Analytics</h3>
                <p class="text-[12.5px] text-slate-400 mt-0.5">Select a sign from the list to view its complete student practice breakdown</p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <div class="px-4 py-2 rounded-2xl bg-blue-50 border border-blue-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-[#0d326b]">verified</span>
                    <span class="text-[12px] font-bold text-[#0d326b]">{{ $masteredSignsCount }} Signs Mastered by Majority</span>
                </div>
                <div class="px-4 py-2 rounded-2xl flex items-center gap-2" style="background: linear-gradient(135deg, #f59e0b 0%, #facc15 50%, #fbbf24 100%); border: 1px solid #f59e0b; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);">
                    <span class="material-symbols-outlined text-[18px] text-amber-950">help</span>
                    <span class="text-[12px] font-black text-amber-950">{{ $lowMasterySignsCount }} Signs Need Practice</span>
                </div>
                <div class="px-4 py-2 rounded-2xl bg-slate-100 text-slate-700 flex items-center gap-2 font-bold text-[12px]">
                    Accuracy: {{ $gesturePerformanceOverview['overall_accuracy'] ?? 0 }}%
                </div>
            </div>
        </div>

        @if(empty($signsBreakdown) || count($signsBreakdown) === 0)
            <div class="text-center py-16 text-slate-400">
                <span class="material-symbols-outlined text-[42px] text-slate-300 block mb-2">front_hand</span>
                <p class="text-[14px] font-semibold">No student gesture practices recorded in this time period.</p>
            </div>
        @else

            {{-- Sign Type Filter Tabs --}}
            @php
                $hasStatic  = collect($signsBreakdown)->contains('sign_type', 'static');
                $hasDynamic = collect($signsBreakdown)->contains('sign_type', 'dynamic');
            @endphp
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" class="sign-type-tab active" data-type="all" onclick="filterSignType('all', this)">
                    <span class="material-symbols-outlined text-[15px]">apps</span>
                    All Signs <span class="ml-1 px-1.5 py-0.5 rounded-full bg-white/30 text-[10px] font-black">{{ count($signsBreakdown) }}</span>
                </button>
                @if($hasDynamic)
                <button type="button" class="sign-type-tab" data-type="dynamic" onclick="filterSignType('dynamic', this)">
                    <span class="material-symbols-outlined text-[15px]">waving_hand</span>
                    Moving Signs <span class="ml-1 px-1.5 py-0.5 rounded-full bg-white/30 text-[10px] font-black">{{ collect($signsBreakdown)->where('sign_type','dynamic')->count() }}</span>
                </button>
                @endif
                @if($hasStatic)
                <button type="button" class="sign-type-tab" data-type="static" onclick="filterSignType('static', this)">
                    <span class="material-symbols-outlined text-[15px]">back_hand</span>
                    Static Signs <span class="ml-1 px-1.5 py-0.5 rounded-full bg-white/30 text-[10px] font-black">{{ collect($signsBreakdown)->where('sign_type','static')->count() }}</span>
                </button>
                @endif
            </div>

            {{-- Master-Detail Split Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- Left Master List (5 Cols): Clickable Sign Cards --}}
                <div class="lg:col-span-5 space-y-3 max-h-[580px] overflow-y-auto pr-1" id="signsMasterList">
                    @foreach($signsBreakdown as $idx => $s)
                        <div class="gesture-sign-card {{ $idx === 0 ? 'active' : '' }}"
                             data-sign-id="{{ $s['gesture_id'] }}"
                             data-sign-type="{{ $s['sign_type'] ?? 'static' }}"
                             onclick="selectGestureSign('{{ $s['gesture_id'] }}', this)">
                            
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    {{-- Sign type badge --}}
                                    @if(($s['sign_type'] ?? 'static') === 'dynamic')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-100 text-violet-700 border border-violet-200 shrink-0">
                                            <span class="material-symbols-outlined text-[12px]">waving_hand</span> Moving
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200 shrink-0">
                                            <span class="material-symbols-outlined text-[12px]">back_hand</span> Static
                                        </span>
                                    @endif
                                    <span class="text-[14px] font-black text-[#0d326b] truncate">{{ $s['gesture_name'] }}</span>
                                </div>
                                <span class="gesture-status-pill {{ $s['status'] === 'mastered' ? 'gesture-status-mastered' : 'gesture-status-practice' }} shrink-0">
                                    @if($s['status'] === 'mastered')
                                        <span class="material-symbols-outlined text-[13px]">check</span> Mastered
                                    @else
                                        <span class="material-symbols-outlined text-[13px]">priority_high</span> Needs Practice
                                    @endif
                                </span>
                            </div>

                            @if(!empty($s['module_name']))
                                <p class="text-[10.5px] text-slate-400 font-medium mb-1.5">{{ $s['module_name'] }}</p>
                            @endif

                            <div class="space-y-1.5 mb-2.5">
                                <div class="flex items-center justify-between text-[11.5px]">
                                    <span class="text-slate-400 font-medium">{{ $s['total_attempts'] }} attempts ({{ $s['successful_attempts'] }} correct / {{ $s['wrong_attempts'] }} mistakes)</span>
                                    <span class="font-black text-[#0d326b]">{{ $s['accuracy'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ ($s['sign_type'] ?? 'static') === 'dynamic' ? 'bg-violet-500' : 'bg-[#0d326b]' }}" style="width: {{ min(100, max(4, $s['accuracy'])) }}%;"></div>
                                </div>
                            </div>

                            {{-- Student Highlights in Pill Format --}}
                            <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-100 text-[11px]">
                                <div class="flex items-center gap-1.5 truncate text-[#0d326b]">
                                    <span class="material-symbols-outlined text-[14px] text-amber-500">star</span>
                                    <span class="truncate font-semibold">{{ $s['best_student']['name'] ?? 'None' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 truncate text-slate-500">
                                    <span class="material-symbols-outlined text-[14px] text-slate-400">help</span>
                                    <span class="truncate font-semibold">{{ $s['struggling_student']['name'] ?? 'None' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Right Detail View (7 Cols): Dynamic Student Ranking for Selected Sign --}}
                <div class="lg:col-span-7 bg-[#f8fafc] rounded-2xl border border-slate-200/80 p-5 flex flex-col justify-between" id="signDetailContainer">
                    <div>
                        {{-- Detail Top Summary Banner --}}
                        <div class="bg-white rounded-xl p-4 border border-slate-200/70 mb-4 shadow-sm">
                            <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#0d326b] text-[22px]" id="detailSignIcon">front_hand</span>
                                    <h4 class="text-[17px] font-black text-[#0d326b]" id="detailSignTitle">
                                        {{ $signsBreakdown[0]['gesture_name'] ?? '' }}
                                    </h4>
                                    <span id="detailSignTypeBadge" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border"></span>
                                </div>
                                <span class="text-[14px] font-black text-[#0d326b]" id="detailSignAccuracy">
                                    {{ $signsBreakdown[0]['accuracy'] ?? 0 }}% Class Accuracy
                                </span>
                            </div>
                            <p class="text-[12px] text-slate-400 font-medium" id="detailSignSubtitle">
                                {{ $signsBreakdown[0]['total_attempts'] ?? 0 }} total attempts ({{ $signsBreakdown[0]['successful_attempts'] ?? 0 }} correct · {{ $signsBreakdown[0]['wrong_attempts'] ?? 0 }} mistakes)
                            </p>
                            @if(!empty($signsBreakdown[0]['module_name']))
                                <p class="text-[11px] text-slate-400 font-medium mt-0.5" id="detailModuleName">Module: {{ $signsBreakdown[0]['module_name'] }}</p>
                            @else
                                <p class="text-[11px] text-slate-400 font-medium mt-0.5 hidden" id="detailModuleName"></p>
                            @endif
                        </div>

                        {{-- Students List for Selected Gesture --}}
                        <div class="space-y-2 max-h-[400px] overflow-y-auto pr-1" id="detailStudentsList">
                            {{-- Populated dynamically via JS from signsData payload --}}
                        </div>
                    </div>
                </div>

            </div>
        @endif

        {{-- Gesture Coaching Senya Insight --}}
        <div class="senya-insight-gold mt-2">
            <div class="senya-insight-gold-icon">
                <span class="material-symbols-outlined text-[19px]">support_agent</span>
            </div>
            <div>
                <div class="senya-insight-gold-title">Gesture Coaching Insight</div>
                <div class="senya-insight-gold-text">{!! $senyaInsights['gestures'] ?? '' !!}</div>
            </div>
        </div>

    </div>

</div>

{{-- ══════════ EXPORT PDF MODAL ══════════ --}}
@php
    $af = session('analytics_filters', []);
    $aPeriod = ucfirst($af['period'] ?? 'Weekly');
    $aYear   = $af['year'] ?? date('Y');
    $aMonth  = isset($af['month']) ? date('F', mktime(0,0,0,$af['month'],1)) : null;
    $aPeriodLabel = $aPeriod . ($aMonth ? ' — ' . $aMonth . ' ' . $aYear : ' — ' . $aYear);
@endphp

<div id="analyticsPdfModalOverlay" class="pdf-modal-overlay" onclick="if(event.target===this)closeAnalyticsPdfModal()">
    <div class="pdf-modal">
        <div class="p-6 bg-gradient-to-br from-[#071c3f] via-[#0d326b] to-[#1e4b8f] text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">picture_as_pdf</span>
                </div>
                <button type="button" onclick="closeAnalyticsPdfModal()" class="text-white/60 hover:text-white transition">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <h3 class="text-[19px] font-black text-white">Export Analytics Report</h3>
            <p class="text-[12px] text-white/70 mt-1">Download a clean, printable PDF report for your classroom.</p>
        </div>

        <div class="p-6 space-y-4">
            <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-100 text-xs text-[#0d326b] space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    Selected Period: <span class="text-[#0d326b] font-black">{{ $aPeriodLabel }}</span>
                </div>
                <p class="text-slate-600 pt-1">Includes 6 Class KPIs, Performance Insights, Quiz Progress Trends, Module Difficulty, and Student Rankings.</p>
            </div>

            <form id="analyticsPdfForm" method="POST" action="{{ route('analytics.export-pdf.post') }}" target="_blank">
                @csrf
                {{-- Pass active filter values so the PDF matches the page --}}
                <input type="hidden" name="period" value="{{ $af['period'] ?? 'weekly' }}">
                <input type="hidden" name="year"   value="{{ $af['year']   ?? date('Y') }}">
                @if(!empty($af['month']))
                <input type="hidden" name="month"  value="{{ $af['month'] }}">
                @endif
                <div class="space-y-3 pt-1">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Document Settings</div>

                    <div class="flex items-center justify-between py-2 border-b border-slate-100 text-xs">
                        <span class="font-semibold text-slate-600">Paper Size</span>
                        <select name="paper_size" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#0d326b]">
                            <option value="A4" selected>A4 (210 × 297 mm)</option>
                            <option value="A3">A3 (297 × 420 mm)</option>
                            <option value="Letter">Letter (215.9 × 279.4 mm)</option>
                            <option value="Legal">Legal (215.9 × 355.6 mm)</option>
                            <option value="A5">A5 (148 × 210 mm)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b border-slate-100 text-xs">
                        <span class="font-semibold text-slate-600">Orientation</span>
                        <span class="font-bold text-slate-500 text-xs">Portrait</span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b border-slate-100 text-xs">
                        <span class="font-semibold text-slate-600">Running Header</span>
                        <select name="running_header" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#0d326b]">
                            <option value="first" selected>First page only</option>
                            <option value="every">Every page</option>
                            <option value="none">None</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b border-slate-100 text-xs">
                        <span class="font-semibold text-slate-600">Page Numbers</span>
                        <select name="page_numbers" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#0d326b]">
                            <option value="footer" selected>Footer — Page N of M</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeAnalyticsPdfModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                       class="px-5 py-2.5 rounded-xl bg-[#0d326b] hover:bg-[#1a6fd4] text-white font-bold text-xs flex items-center gap-2 shadow-md transition"
                       onclick="setTimeout(closeAnalyticsPdfModal, 500)">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        Download PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JSON payload of signs with their ranked students
const signsData = @json($signsBreakdown ?? []);

document.addEventListener('DOMContentLoaded', function() {

    // ── 0. Period Filter Month Toggle ──────────────────────────────────────
    const periodSelect = document.getElementById('periodSelect');
    const monthFilterWrap = document.getElementById('monthFilterWrap');
    if (periodSelect && monthFilterWrap) {
        periodSelect.addEventListener('change', function() {
            if (this.value === 'monthly' || this.value === 'quarterly') {
                monthFilterWrap.classList.remove('hidden');
            } else {
                monthFilterWrap.classList.add('hidden');
            }
        });
    }

    // ── 1. Interactive Leaderboard Lesson Switcher ────────────────────────
    const selector = document.getElementById('leaderboardLessonSelector');
    const modeSubtitle = document.getElementById('leaderboardModeSubtitle');

    if (selector) {
        selector.addEventListener('change', function() {
            const selectedLessonId = this.value;
            const panes = document.querySelectorAll('.leaderboard-table-pane');
            panes.forEach(pane => {
                if (pane.getAttribute('data-lesson-id') === selectedLessonId) {
                    pane.classList.remove('hidden');
                } else {
                    pane.classList.add('hidden');
                }
            });

            if (selectedLessonId === 'all') {
                if (modeSubtitle) modeSubtitle.textContent = 'Overall class score average across completed quizzes';
            } else if (selectedLessonId.startsWith('exam_')) {
                if (modeSubtitle) modeSubtitle.textContent = 'Ranked by highest checkpoint exam score in fewest attempts';
            } else {
                if (modeSubtitle) modeSubtitle.textContent = 'Ranked by highest score in fewest attempts';
            }
        });
    }

    // ── 2. Line Chart Tooltip ──────────────────────────────────────────────
    (function() {
        const wrap = document.querySelector('.chart-wrapper');
        const tip = document.getElementById('lineChartTooltip');
        if (!wrap || !tip) return;
        const tipLabel = document.getElementById('lineTooltipLabel');
        const tipValue = document.getElementById('lineTooltipValue');

        wrap.querySelectorAll('.line-point-hit').forEach(function(hit) {
            hit.addEventListener('mouseenter', function() {
                const rect = hit.getBoundingClientRect();
                const wrapRect = wrap.getBoundingClientRect();
                tip.style.left = (rect.left - wrapRect.left + rect.width / 2) + 'px';
                tip.style.top = (rect.top - wrapRect.top - 6) + 'px';
                tipLabel.textContent = hit.dataset.label;
                tipValue.textContent = hit.dataset.value;
                tip.classList.add('visible');
            });
            hit.addEventListener('mouseleave', function() {
                tip.classList.remove('visible');
            });
        });
    })();

    // ── 3. Mastery Donut Tooltip ──────────────────────────────────────────
    (function() {
        const tip = document.getElementById('masteryDonutTooltip');
        if (!tip) return;
        const tipLabel = document.getElementById('masteryDonutLabel');
        const tipValue = document.getElementById('masteryDonutValue');

        document.querySelectorAll('.mastery-donut-hit').forEach(function(seg) {
            seg.addEventListener('mouseenter', function() {
                const rect = seg.getBoundingClientRect();
                const parentRect = seg.closest('.relative').getBoundingClientRect();
                tip.style.left = (rect.left - parentRect.left + rect.width / 2) + 'px';
                tip.style.top = (rect.top - parentRect.top - 8) + 'px';
                tipLabel.textContent = seg.dataset.label;
                tipValue.textContent = seg.dataset.value;
                tip.classList.add('visible');
            });
            seg.addEventListener('mouseleave', function() {
                tip.classList.remove('visible');
            });
        });
    })();

    // ── 4. Initialize Gesture Sign Detail View ─────────────────────────────
    if (signsData && signsData.length > 0) {
        renderSignDetail(signsData[0]);
    }
});

// Function to select and render gesture sign details
function selectGestureSign(signId, element) {
    document.querySelectorAll('.gesture-sign-card').forEach(card => card.classList.remove('active'));
    if (element) element.classList.add('active');

    const sign = signsData.find(s => String(s.gesture_id) === String(signId));
    if (sign) {
        renderSignDetail(sign);
    }
}

function renderSignDetail(sign) {
    const titleEl = document.getElementById('detailSignTitle');
    const accEl   = document.getElementById('detailSignAccuracy');
    const subEl   = document.getElementById('detailSignSubtitle');
    const listEl  = document.getElementById('detailStudentsList');
    const iconEl  = document.getElementById('detailSignIcon');
    const badgeEl = document.getElementById('detailSignTypeBadge');
    const moduleEl = document.getElementById('detailModuleName');

    const isDynamic = sign.sign_type === 'dynamic';

    if (titleEl) titleEl.textContent = sign.gesture_name;
    if (accEl) accEl.textContent = sign.accuracy + '% Class Accuracy';
    if (subEl) subEl.textContent = sign.total_attempts + ' total attempts (' + sign.successful_attempts + ' correct · ' + sign.wrong_attempts + ' mistakes)';

    // Update icon and badge based on sign type
    if (iconEl) iconEl.textContent = isDynamic ? 'waving_hand' : 'back_hand';
    if (badgeEl) {
        if (isDynamic) {
            badgeEl.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border bg-violet-100 text-violet-700 border-violet-200';
            badgeEl.innerHTML = '<span class="material-symbols-outlined text-[11px]">waving_hand</span> Moving Sign';
        } else {
            badgeEl.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border bg-slate-100 text-slate-500 border-slate-200';
            badgeEl.innerHTML = '<span class="material-symbols-outlined text-[11px]">back_hand</span> Static Sign';
        }
    }
    if (moduleEl) {
        if (sign.module_name) {
            moduleEl.textContent = 'Module: ' + sign.module_name;
            moduleEl.classList.remove('hidden');
        } else {
            moduleEl.classList.add('hidden');
        }
    }

    if (!listEl) return;
    listEl.innerHTML = '';

    if (!sign.students_ranking || sign.students_ranking.length === 0) {
        listEl.innerHTML = '<div class="text-center py-10 text-slate-400"><p class="text-[13px] font-semibold">No student attempts recorded for this sign.</p></div>';
        return;
    }

    const barColor = isDynamic ? '#7c3aed' : '#0d326b';

    sign.students_ranking.forEach(st => {
        let badgeIcon = '';
        let badgeBg = 'bg-slate-100 text-slate-600';

        if (st.rank === 1) {
            badgeIcon = '<span class="material-symbols-outlined text-[15px] text-amber-700">workspace_premium</span>';
            badgeBg = 'bg-amber-100 text-amber-800 border border-amber-200';
        } else if (st.rank === 2) {
            badgeIcon = '<span class="material-symbols-outlined text-[15px] text-slate-600">military_tech</span>';
            badgeBg = 'bg-slate-200 text-slate-700 border border-slate-300';
        } else if (st.rank === 3) {
            badgeIcon = '<span class="material-symbols-outlined text-[15px] text-amber-800">star</span>';
            badgeBg = 'bg-orange-100 text-orange-800 border border-orange-200';
        } else {
            badgeIcon = '<span class="text-[12px] font-black">' + st.rank + '</span>';
        }

        const fallbackUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(st.initials || 'ST')}&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45`;
        const avatarSrc = st.avatar_url || fallbackUrl;

        const row = document.createElement('div');
        row.className = 'flex items-center justify-between p-3 rounded-xl bg-white border border-slate-100 transition hover:border-slate-200';
        row.innerHTML = `
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 ${badgeBg}">
                    ${badgeIcon}
                </div>
                <img src="${avatarSrc}" alt="${st.name}" class="w-8 h-8 rounded-full object-cover shadow-sm bg-[#0d326b] shrink-0" onerror="this.onerror=null;this.src='${fallbackUrl}';" />
                <div class="min-w-0">
                    <h5 class="text-[13px] font-bold text-[#0d326b] truncate">${st.name}</h5>
                    <p class="text-[11px] text-slate-400 font-medium">
                        ${st.successful_attempts} / ${st.attempts} correct (${st.wrong_attempts} mistakes)
                    </p>
                </div>
            </div>
            <div class="text-right shrink-0 pl-3">
                <span class="text-[14px] font-black" style="color:${barColor}">${st.accuracy}%</span>
                <div class="w-16 bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                    <div class="h-1.5 rounded-full" style="width: ${Math.min(100, Math.max(5, st.accuracy))}%; background:${barColor}"></div>
                </div>
            </div>
        `;
        listEl.appendChild(row);
    });
}

// ── Sign Type Filter ──────────────────────────────────────────────────────
function filterSignType(type, btn) {
    // Update tab states
    document.querySelectorAll('.sign-type-tab').forEach(t => t.classList.remove('active'));
    if (btn) btn.classList.add('active');

    // Filter sign cards
    const cards = document.querySelectorAll('.gesture-sign-card');
    let firstVisible = null;
    cards.forEach(card => {
        const cardType = card.dataset.signType || 'static';
        const visible = type === 'all' || cardType === type;
        card.style.display = visible ? '' : 'none';
        if (visible && !firstVisible) firstVisible = card;
    });

    // Auto-select first visible card and render its details
    if (firstVisible) {
        document.querySelectorAll('.gesture-sign-card').forEach(c => c.classList.remove('active'));
        firstVisible.classList.add('active');
        const signId = firstVisible.dataset.signId;
        const sign = signsData.find(s => String(s.gesture_id) === String(signId));
        if (sign) renderSignDetail(sign);
    }
}

// ── Export Analytics PDF Modal ─────────────────────────────────────────────
function openAnalyticsPdfModal() {
    const overlay = document.getElementById('analyticsPdfModalOverlay');
    if (overlay) {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeAnalyticsPdfModal() {
    const overlay = document.getElementById('analyticsPdfModalOverlay');
    if (overlay) {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAnalyticsPdfModal();
});
</script>

@endsection