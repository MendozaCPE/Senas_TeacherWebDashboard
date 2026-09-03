@extends('layouts.app')
@section('bg-class', 'bg-[#f8fafc]')
@section('title', 'Reports')
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
    cursor: pointer;
    display: inline-flex;
    align-items: center;
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
    text-decoration: none;
}
.export-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(13, 50, 107, 0.25);
    background: linear-gradient(135deg, #1e4b8f 0%, #1a6fd4 100%);
}

/* ── Senya Gold Insight Containers ── */
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

/* ── Export PDF Modal ─────────────────────────────────────────────────────── */
.pdf-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(7,28,63,.55);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    opacity: 0;
    pointer-events: none;
    transition: opacity .25s;
}
.pdf-modal-overlay.open {
    opacity: 1;
    pointer-events: all;
}
.pdf-modal {
    background: #ffffff;
    border-radius: 28px;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 32px 80px rgba(7,28,63,.3), 0 0 0 1px rgba(13,50,107,.06);
    overflow: hidden;
    transform: translateY(24px) scale(0.97);
    transition: transform .3s cubic-bezier(.34,1.56,.64,1), opacity .25s;
    opacity: 0;
}
.pdf-modal-overlay.open .pdf-modal {
    transform: translateY(0) scale(1);
    opacity: 1;
}
.pdf-modal-header {
    background: linear-gradient(135deg, #071c3f 0%, #0d326b 60%, #1e4b8f 100%);
    padding: 28px 28px 24px;
    position: relative;
    overflow: hidden;
}
.pdf-modal-header::after {
    content: '';
    position: absolute;
    top: -30px; right: -30px;
    width: 120px; height: 120px;
    background: rgba(255,255,255,.05);
    border-radius: 50%;
}
.pdf-modal-icon {
    width: 52px; height: 52px;
    background: rgba(255,255,255,.12);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
}
.pdf-modal-title {
    font-size: 20px;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.2;
    margin-bottom: 4px;
}
.pdf-modal-subtitle {
    font-size: 12px;
    color: rgba(255,255,255,.6);
    font-weight: 500;
}
.pdf-modal-body {
    padding: 24px 28px;
}
.pdf-preview-card {
    background: linear-gradient(135deg, #f8faff 0%, #eff6ff 100%);
    border: 1px solid #dbeafe;
    border-radius: 16px;
    padding: 16px 18px;
    margin-bottom: 20px;
}
.pdf-preview-title {
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 10px;
}
.pdf-preview-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
.pdf-preview-row:last-child { margin-bottom: 0; }
.pdf-preview-dot {
    width: 6px; height: 6px;
    background: #0d326b;
    border-radius: 50%;
    flex-shrink: 0;
}
.pdf-preview-text {
    font-size: 12px;
    color: #334155;
    font-weight: 600;
}
.pdf-preview-text span {
    color: #0d326b;
    font-weight: 700;
}
.pdf-options {
    margin-bottom: 20px;
}
.pdf-options-title {
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 12px;
}
.pdf-option-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 8px;
    border: 1px solid #f1f5f9;
}
.pdf-option-label {
    font-size: 12px;
    font-weight: 600;
    color: #1e293b;
}
.pdf-option-value {
    font-size: 11px;
    color: #64748b;
    font-weight: 500;
    background: #e2e8f0;
    padding: 2px 10px;
    border-radius: 999px;
}
.pdf-option-select {
    font-size: 11px;
    color: #1e293b;
    font-weight: 500;
    background: #e2e8f0;
    border: none;
    border-radius: 999px;
    padding: 3px 10px;
    cursor: pointer;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2364748b'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
    padding-right: 22px;
    transition: background-color .15s;
}
.pdf-option-select:hover { background-color: #cbd5e1; }
.pdf-option-select:focus { box-shadow: 0 0 0 2px rgba(13,50,107,.25); background-color: #dde4ef; }
.pdf-modal-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.pdf-download-btn {
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);
    color: #ffffff;
    border: none;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
    transition: all .2s;
    box-shadow: 0 4px 16px rgba(13,50,107,.25);
    position: relative;
    overflow: hidden;
}
.pdf-download-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(13,50,107,.35); }
.pdf-download-btn.loading { pointer-events: none; opacity: .85; }
.pdf-download-btn .btn-text { transition: opacity .2s; }
.pdf-download-btn .btn-spinner {
    display: none;
    position: absolute;
    width: 20px; height: 20px;
    border: 2px solid rgba(255,255,255,.4);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin .7s linear infinite;
}
.pdf-download-btn.loading .btn-text { opacity: 0; }
.pdf-download-btn.loading .btn-spinner { display: block; }
@keyframes spin { to { transform: rotate(360deg); } }
.pdf-cancel-btn {
    width: 100%;
    padding: 11px 20px;
    background: transparent;
    color: #64748b;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.pdf-cancel-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
.pdf-success-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    font-size: 12px;
    color: #15803d;
    font-weight: 600;
    margin-bottom: 10px;
    opacity: 0;
    transition: opacity .3s;
}
.pdf-success-badge.visible { opacity: 1; }

/* ── Table Styles ────────────────────────────────────────────────────────── */
.table-wrap {
    overflow-x: auto;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.report-table thead th {
    padding: 14px 16px;
    text-align: left;
    font-size: 10.5px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border-bottom: 1px solid #f1f5f9;
    background: #fafcff;
}

.report-table tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #f8fafc;
    vertical-align: middle;
}

.report-table tbody tr {
    transition: background .15s;
    cursor: pointer;
}
.report-table tbody tr:hover {
    background: #f8fafc;
}

/* ── Status Badge ────────────────────────────────────────────────────────── */
.badge-status {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 9999px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.badge-status.completed { background: #0d326b; color: #fff; }
.badge-status.in-progress { background: #dbeafe; color: #0d326b; }
.badge-status.pending { background: #f1f5f9; color: #64748b; }
</style>

<div class="space-y-6 pb-12">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-3.5 rounded-xl text-[13px] font-medium flex items-center space-x-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ══════════ 1. TOOLBAR: FILTER + EXPORT (UNIFORM WITH ANALYTICS) ══════════ --}}
    @php $rf = session('reports_filters', []); @endphp
    <form method="POST" action="{{ route('reports.filter') }}" id="filterForm">
        @csrf
        <div class="filter-container">
            <div class="filter-group">
                <div class="flex items-center gap-2 mr-2">
                    <span class="material-symbols-outlined text-[#0d326b] text-[22px]">tune</span>
                    <span class="text-[13px] font-bold text-[#0d326b] uppercase tracking-wider">Filter Reports</span>
                </div>

                <div class="filter-wrap">
                    <select name="student_id" class="filter-select">
                        <option value="all" {{ ($rf['student_id'] ?? 'all') === 'all' ? 'selected' : '' }}>All Students</option>
                        @foreach($students as $s)
                            <option value="{{ $s->student_id }}" {{ ($rf['student_id'] ?? '') == $s->student_id ? 'selected' : '' }}>
                                {{ $s->first_name }} {{ $s->last_name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <div class="filter-wrap">
                    <select name="lesson_id" class="filter-select">
                        <option value="all" {{ ($rf['lesson_id'] ?? 'all') === 'all' ? 'selected' : '' }}>All Content</option>
                        @if($lessons->isNotEmpty())
                            <optgroup label="Lessons">
                                @foreach($lessons as $l)
                                    <option value="lesson_{{ $l->lesson_id }}" {{ ($rf['lesson_id'] ?? '') == ('lesson_' . $l->lesson_id) || ($rf['lesson_id'] ?? '') == (string)$l->lesson_id ? 'selected' : '' }}>
                                        {{ $l->title }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if(!empty($checkpointExams) && $checkpointExams->isNotEmpty())
                            <optgroup label="Checkpoint Exams">
                                @foreach($checkpointExams as $e)
                                    <option value="exam_{{ $e->exam_id }}" {{ ($rf['lesson_id'] ?? '') == ('exam_' . $e->exam_id) ? 'selected' : '' }}>
                                        {{ $e->title }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <a href="{{ route('reports') }}" onclick="event.preventDefault(); fetch('{{ route('reports.filter') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}, body:JSON.stringify({student_id:'all',lesson_id:'all'})}).then(()=>window.location.href='{{ route('reports') }}')" class="filter-reset">Reset</a>
                <button type="submit" class="filter-btn">
                    <span class="material-symbols-outlined text-[16px]">refresh</span>
                    Apply
                </button>
            </div>

            {{-- Export PDF button (opens modal) --}}
            <button type="button" id="openExportModal" class="export-btn" onclick="openPdfModal()">
                <span class="material-symbols-outlined text-[19px]">picture_as_pdf</span>
                Export Report
            </button>
        </div>
    </form>

    {{-- ══════════ 2. CLASS SUMMARY KPI CARDS ══════════ --}}
    @php
        $totalStudentsShown = $studentReports->count();
        $fullyCompleted     = $studentReports->where('overallPct', 100)->count();
        $totalQuizzesTaken  = $studentReports->sum('quizzesTaken');
        $totalQuizzesPassed = $studentReports->sum('quizzesPassed');
        $quizPassRate       = $totalQuizzesTaken > 0 ? round(($totalQuizzesPassed / $totalQuizzesTaken) * 100, 1) : 0;
        $scoredReports      = $studentReports->filter(fn($r) => $r['quizzesTaken'] > 0);
        $avgScoreOverall    = $scoredReports->isNotEmpty() ? $scoredReports->avg('avgScore') : 0;

        // Additional stats
        $totalLessonsAssigned = $studentReports->sum('totalLessons');
        $totalLessonsCompleted = $studentReports->sum('completedLessons');
        $classCompletionRate  = $totalLessonsAssigned > 0
            ? round(($totalLessonsCompleted / $totalLessonsAssigned) * 100, 1)
            : 0;

        $gestureStudents = $studentReports->filter(fn($r) => ($r['gestureAttempts'] ?? 0) > 0);
        $avgGestureAccuracy = $gestureStudents->isNotEmpty()
            ? round($gestureStudents->avg('gestureAccuracy'), 1)
            : 0;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-5">
        {{-- Card 1: Total Students (Hero navy gradient) --}}
        <div class="stat-kpi-card text-white" style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 55%, #1a6fd4 100%);">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Total Students</span>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px] text-white">group</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-white tracking-tight">{{ $totalStudentsShown }}</p>
            <p class="text-[12px] text-white/70 font-medium">in current view</p>
        </div>

        {{-- Card 2: Completion Rate --}}
        <div class="stat-kpi-card bg-white">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Completion Rate</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1e4b8f] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">menu_book</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ number_format($classCompletionRate, 1) }}%</p>
            <p class="text-[12px] text-slate-400 font-medium truncate">{{ $totalLessonsCompleted }} of {{ $totalLessonsAssigned }} lessons</p>
        </div>

        {{-- Card 3: Fully Completed --}}
        <div class="stat-kpi-card bg-white">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Fully Completed</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ $fullyCompleted }}</p>
            <p class="text-[12px] text-slate-400 font-medium">student{{ $fullyCompleted !== 1 ? 's' : '' }} at 100%</p>
        </div>

        {{-- Card 4: Quizzes Passed / Taken --}}
        <div class="stat-kpi-card bg-white">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Quizzes Passed</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1a6fd4] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">quiz</span>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5 mb-1">
                <span class="text-[36px] font-black leading-none text-[#0d326b] tracking-tight">{{ $totalQuizzesPassed }}</span>
                <span class="text-[20px] font-bold text-slate-400 leading-none">/ {{ $totalQuizzesTaken }}</span>
            </div>
            @if($totalQuizzesTaken > 0)
                <p class="text-[12px] font-bold text-emerald-600 truncate">{{ number_format($quizPassRate, 1) }}% passing rate</p>
            @else
                <p class="text-[12px] text-slate-400 font-medium">0 attempts</p>
            @endif
        </div>

        {{-- Card 5: Avg Quiz Score --}}
        <div class="stat-kpi-card bg-white">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Avg Quiz Score</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">insights</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ number_format($avgScoreOverall, 1) }}</p>
            <p class="text-[12px] text-slate-400 font-medium">points per attempt</p>
        </div>

        {{-- Card 6: Gesture Accuracy (Golden Highlight) --}}
        <div class="stat-kpi-card text-amber-950" style="background: linear-gradient(135deg, #f59e0b 0%, #facc15 50%, #fbbf24 100%); border-color: rgba(245, 158, 11, 0.5); box-shadow: 0 4px 16px rgba(245, 158, 11, 0.22);">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Gesture Accuracy</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 text-amber-950 flex items-center justify-center backdrop-blur-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">front_hand</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-amber-950 tracking-tight">{{ number_format($avgGestureAccuracy, 1) }}%</p>
            <p class="text-[12px] text-amber-950/80 font-bold truncate">{{ $gestureStudents->count() }} {{ $gestureStudents->count() !== 1 ? 'students' : 'student' }} with data</p>
        </div>
    </div>

    {{-- ══════════ 3. SENYA CLASS PROGRESS INSIGHT BANNER ══════════ --}}
    <div class="senya-insight-gold">
        <div class="senya-insight-gold-icon">
            <span class="material-symbols-outlined text-[20px]">lightbulb</span>
        </div>
        <div>
            <div class="senya-insight-gold-title">Senya Class Progress Insight</div>
            <div class="senya-insight-gold-text">
                @if($totalStudentsShown === 0)
                    No student records found with current filters. Adjust your student or lesson filter to see progress.
                @elseif($fullyCompleted > 0)
                    <strong>Outstanding achievement:</strong> {{ $fullyCompleted }} student{{ $fullyCompleted !== 1 ? 's have' : ' has' }} achieved 100% completion rate across assigned lessons. Overall quiz score average is {{ number_format($avgScoreOverall, 1) }} pts.
                @else
                    <strong>Class Overview:</strong> {{ $totalStudentsShown }} student{{ $totalStudentsShown !== 1 ? 's are' : ' is' }} actively progressing with a {{ number_format($classCompletionRate, 1) }}% overall completion rate and {{ number_format($avgGestureAccuracy, 1) }}% gesture accuracy.
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════ 4. STUDENT PROGRESS REPORT PANEL ══════════ --}}
    <div class="analytics-panel space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[#0d326b] text-[22px]">assignment</span>
                <div>
                    <h3 class="text-[18px] font-black text-[#0d326b]">Student Progress Report</h3>
                    <p class="text-[12px] text-slate-400 font-medium mt-0.5">Comprehensive breakdown of student lesson progress, quiz scores, and gesture accuracy</p>
                </div>
            </div>
            <span class="text-[12px] font-bold text-[#0d326b] bg-blue-50 px-3.5 py-1.5 rounded-full border border-blue-100 shrink-0 self-start sm:self-auto">
                {{ $studentReports->count() }} {{ $studentReports->count() !== 1 ? 'students' : 'student' }}
            </span>
        </div>

        @if($studentReports->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                <span class="material-symbols-outlined icon-outline text-[56px] mb-4">description</span>
                <p class="text-[15px] font-bold mb-1">No data found</p>
                <p class="text-[13px]">Try adjusting the filters above.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Overall Progress</th>
                            <th style="width:100px;">Lessons</th>
                            <th style="width:110px;">Quizzes (Pass/Taken)</th>
                            <th style="width:95px;">Avg Score</th>
                            <th style="width:130px; text-align:center;">Gesture Accuracy</th>
                            <th style="width:130px;">Last Active</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentReports as $index => $row)
                            <tr onclick="openStudentModal({{ $index }})">
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <img src="{{ $row['avatar_url'] ?? '' }}" alt="{{ $row['studentName'] }}"
                                             class="w-9 h-9 rounded-full object-cover shadow-sm bg-[#0d326b] flex-shrink-0"
                                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($row['initials']) }}&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45';" />
                                        <div>
                                            <p class="text-[13px] font-bold text-[#0d326b] leading-tight">{{ $row['studentName'] }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium">{{ $row['gradeLevel'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-28 h-1.5 bg-[#f1f5f9] rounded-full overflow-hidden">
                                            <div class="h-full rounded-full {{ $row['overallPct'] >= 100 ? 'bg-[#0d326b]' : 'bg-[#1a6fd4]' }}"
                                                 style="width: {{ $row['overallPct'] }}%"></div>
                                        </div>
                                        <span class="text-[11px] font-bold text-slate-500">{{ $row['overallPct'] }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-[13px] font-bold text-[#1e293b]">{{ $row['completedLessons'] }}</span>
                                    <span class="text-[12px] text-slate-400 font-medium">/ {{ $row['totalLessons'] }}</span>
                                </td>
                                <td>
                                    <span class="text-[13px] font-bold text-[#1e293b]">{{ $row['quizzesPassed'] ?? 0 }}</span>
                                    <span class="text-[12px] text-slate-400 font-medium">/ {{ $row['quizzesTaken'] }}</span>
                                    @if($row['quizzesTaken'] > 0)
                                        <div class="text-[10px] font-semibold {{ ($row['quizPassRate'] ?? 0) >= 60 ? 'text-emerald-600' : 'text-amber-600' }}">
                                            {{ $row['quizPassRate'] ?? 0 }}% pass
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($row['quizzesTaken'] > 0)
                                        <span class="text-[13px] font-bold text-[#0d326b]">{{ $row['avgScore'] }} pts</span>
                                    @else
                                        <span class="text-[12px] text-slate-400 font-medium">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(($row['gestureAttempts'] ?? 0) > 0)
                                        <span class="text-[13px] font-black text-emerald-600">{{ number_format($row['gestureAccuracy'], 1) }}%</span>
                                        <div class="text-[10px] text-slate-400 font-semibold">{{ $row['gestureSuccess'] }}/{{ $row['gestureAttempts'] }} correct</div>
                                    @else
                                        <span class="text-[12px] text-slate-400 font-medium">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-[12px] font-medium text-slate-500">{{ $row['lastAccessed'] }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="material-symbols-outlined icon-outline text-[18px] text-slate-300">chevron_right</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ═══════════ EXPORT PDF MODAL ═══════════ --}}
@php
    $rf = session('reports_filters', []);
    $activeStudent = 'All Students';
    $activeLesson  = 'All Lessons';
    if (!empty($rf['student_id']) && $rf['student_id'] !== 'all') {
        $fs = $students->firstWhere('student_id', $rf['student_id']);
        if ($fs) $activeStudent = $fs->first_name . ' ' . $fs->last_name;
    }
    if (!empty($rf['lesson_id']) && $rf['lesson_id'] !== 'all') {
        $fl = $lessons->firstWhere('lesson_id', $rf['lesson_id']);
        if ($fl) $activeLesson = $fl->title;
    }
    $exportBaseUrl = route('reports.export-pdf.post');
    $hiddenStudent = ($rf['student_id'] ?? 'all') !== 'all' ? $rf['student_id'] : null;
    $hiddenLesson  = ($rf['lesson_id']  ?? 'all') !== 'all' ? $rf['lesson_id']  : null;
@endphp

<div id="pdfModalOverlay" class="pdf-modal-overlay" onclick="if(event.target===this)closePdfModal()">
    <div class="pdf-modal">

        {{-- Header --}}
        <div class="pdf-modal-header">
            <div class="pdf-modal-icon">
                <span class="material-symbols-outlined text-white text-[26px]">picture_as_pdf</span>
            </div>
            <div class="pdf-modal-title">Export Progress Report</div>
            <div class="pdf-modal-subtitle">Download a professionally formatted PDF</div>
        </div>

        {{-- Body --}}
        <div class="pdf-modal-body">

            {{-- Preview card --}}
            <div class="pdf-preview-card">
                <div class="pdf-preview-title">What will be included</div>
                <div class="pdf-preview-row">
                    <div class="pdf-preview-dot"></div>
                    <div class="pdf-preview-text">Student: <span>{{ $activeStudent }}</span></div>
                </div>
                <div class="pdf-preview-row">
                    <div class="pdf-preview-dot"></div>
                    <div class="pdf-preview-text">Lesson: <span>{{ $activeLesson }}</span></div>
                </div>
                <div class="pdf-preview-row">
                    <div class="pdf-preview-dot"></div>
                    <div class="pdf-preview-text">Records: <span>{{ $studentReports->count() }} student{{ $studentReports->count() !== 1 ? 's' : '' }}</span></div>
                </div>
                <div class="pdf-preview-row">
                    <div class="pdf-preview-dot"></div>
                    <div class="pdf-preview-text">Sections: <span>Summary strip, per-student lesson breakdown</span></div>
                </div>
            </div>

            {{-- Document Settings Form --}}
            <form id="reportsPdfForm" method="POST" action="{{ $exportBaseUrl }}" target="_blank">
                @csrf
                @if($hiddenStudent)
                    <input type="hidden" name="student_id" value="{{ $hiddenStudent }}">
                @endif
                @if($hiddenLesson)
                    <input type="hidden" name="lesson_id" value="{{ $hiddenLesson }}">
                @endif

                <div class="pdf-options">
                    <div class="pdf-options-title">Document Settings</div>

                    <div class="pdf-option-row">
                        <span class="pdf-option-label">Paper Size</span>
                        <select name="paper_size" class="pdf-option-select">
                            <option value="A4" selected>A4 (210 × 297 mm)</option>
                            <option value="A3">A3 (297 × 420 mm)</option>
                            <option value="Letter">Letter (215.9 × 279.4 mm)</option>
                            <option value="Legal">Legal (215.9 × 355.6 mm)</option>
                            <option value="A5">A5 (148 × 210 mm)</option>
                        </select>
                    </div>

                    <div class="pdf-option-row">
                        <span class="pdf-option-label">Orientation</span>
                        <span class="pdf-option-value">Portrait</span>
                    </div>

                    <div class="pdf-option-row">
                        <span class="pdf-option-label">Running Header</span>
                        <select name="running_header" class="pdf-option-select">
                            <option value="first" selected>First page only</option>
                            <option value="every">Every page</option>
                            <option value="none">None</option>
                        </select>
                    </div>

                    <div class="pdf-option-row">
                        <span class="pdf-option-label">Page Numbers</span>
                        <select name="page_numbers" class="pdf-option-select">
                            <option value="footer" selected>Footer — Page N of M</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="pdf-modal-actions">
                    <div id="pdfSuccessBadge" class="pdf-success-badge">
                        <span class="material-symbols-outlined text-[16px]">check_circle</span>
                        Your PDF is ready — check your Downloads folder.
                    </div>
                    <button id="pdfDownloadBtn" type="submit" class="pdf-download-btn" onclick="handlePdfDownload(event, this)">
                        <span class="btn-text" style="display:flex;align-items:center;gap:8px;">
                            <span class="material-symbols-outlined text-[18px]">download</span>
                            Download PDF
                        </span>
                        <span class="btn-spinner"></span>
                    </button>
                    <button type="button" class="pdf-cancel-btn" onclick="closePdfModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPdfModal() {
    document.getElementById('pdfModalOverlay').classList.add('open');
}
function closePdfModal() {
    const overlay = document.getElementById('pdfModalOverlay');
    overlay.classList.remove('open');
    const btn = document.getElementById('pdfDownloadBtn');
    if (btn) btn.classList.remove('loading');
    document.getElementById('pdfSuccessBadge').classList.remove('visible');
}
function handlePdfDownload(e, btn) {
    // Let the form submit normally (target="_blank"), just show loading state
    btn.classList.add('loading');
    const badge = document.getElementById('pdfSuccessBadge');
    setTimeout(() => {
        btn.classList.remove('loading');
        badge.classList.add('visible');
        setTimeout(() => closePdfModal(), 2800);
    }, 2500);
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePdfModal();
});
</script>

{{-- ================= Student Performance Modal ================= --}}
<div id="studentModalOverlay"
     class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] z-50 hidden items-center justify-center p-6"
     onclick="if(event.target===this) closeStudentModal()">
    <div class="bg-white rounded-[28px] w-full max-w-3xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col">

        <div class="flex items-start justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/60">
            <div class="flex items-center space-x-4">
                <img id="modalAvatar" src="" alt="" class="w-12 h-12 rounded-full object-cover shadow-sm bg-[#0d326b] flex-shrink-0"
                     onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(window._modalInitials || 'S') + '&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45';" />
                <div>
                    <p id="modalStudentName" class="text-[18px] font-bold text-[#0d326b] leading-tight"></p>
                    <p id="modalGradeLevel" class="text-[12px] text-slate-400 font-medium"></p>
                </div>
            </div>
            <button onclick="closeStudentModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined icon-outline text-[24px]">close</span>
            </button>
        </div>

        <div class="overflow-y-auto px-8 py-6 space-y-6">

            <!-- OVERALL PERFORMANCE SECTION -->
            <div>
                <h4 class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-3">Overall Performance</h4>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <div class="bg-[#f1f5f9] rounded-2xl p-3.5">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Progress</p>
                        <p id="modalOverallPct" class="text-[20px] font-black text-[#0d326b]"></p>
                    </div>
                    <div class="bg-[#f1f5f9] rounded-2xl p-3.5">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Lessons</p>
                        <p id="modalLessonsCount" class="text-[20px] font-black text-[#1e293b]"></p>
                    </div>
                    <div class="bg-[#f1f5f9] rounded-2xl p-3.5">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Quizzes Passed</p>
                        <p id="modalQuizzesCount" class="text-[20px] font-black text-[#1a6fd4]"></p>
                    </div>
                    <div class="bg-[#f1f5f9] rounded-2xl p-3.5">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Avg Score</p>
                        <p id="modalAvgScore" class="text-[20px] font-black text-[#0d326b]"></p>
                    </div>
                    <div class="bg-emerald-50/60 border border-emerald-100 rounded-2xl p-3.5">
                        <p class="text-[10px] font-bold text-emerald-700 tracking-[0.08em] uppercase mb-1">Gesture Acc.</p>
                        <p id="modalGestureAccuracy" class="text-[20px] font-black text-emerald-800"></p>
                    </div>
                </div>
                <div class="mt-3 flex items-center space-x-2">
                    <div class="flex-1 h-2 bg-[#f1f5f9] rounded-full overflow-hidden">
                        <div id="modalOverallBar" class="h-full rounded-full bg-[#1a6fd4]"></div>
                    </div>
                    <span id="modalLastActive" class="text-[11px] font-medium text-slate-400 whitespace-nowrap"></span>
                </div>
            </div>

            <!-- TAB NAV -->
            <div class="flex items-center gap-1 border-b border-slate-100 pb-0">
                <button id="tab-btn-gestures" onclick="switchModalTab('gestures')"
                    class="modal-tab-btn px-4 py-2.5 text-[12px] font-bold rounded-t-xl transition-colors">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px]">front_hand</span>Gestures
                    </span>
                </button>
                <button id="tab-btn-lessons" onclick="switchModalTab('lessons')"
                    class="modal-tab-btn px-4 py-2.5 text-[12px] font-bold rounded-t-xl transition-colors">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px]">menu_book</span>Lessons
                    </span>
                </button>
                <button id="tab-btn-reports" onclick="switchModalTab('reports')"
                    class="modal-tab-btn px-4 py-2.5 text-[12px] font-bold rounded-t-xl transition-colors">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px]">flag</span>Reports
                        <span id="modal-reports-badge" class="hidden bg-red-500 text-white text-[9px] font-black rounded-full w-4 h-4 flex items-center justify-center">!</span>
                    </span>
                </button>
            </div>

            <!-- TAB: GESTURE BREAKDOWN -->
            <div id="tab-gestures" class="modal-tab-panel">
                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                    <h4 class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase">Student Performance per Gesture</h4>
                    <div class="flex items-center gap-2">
                        <input type="text" id="modalGestureSearch" oninput="filterModalGestures()" placeholder="Search sign..." class="text-[11px] bg-slate-100 px-3 py-1 rounded-full outline-none w-36 focus:bg-white focus:ring-1 focus:ring-[#0d326b]" />
                        <select id="modalGestureMastery" onchange="filterModalGestures()" class="text-[11px] bg-slate-100 px-2.5 py-1.5 rounded-full outline-none">
                            <option value="all">All Levels</option>
                            <option value="mastered">Mastered</option>
                            <option value="proficient">Proficient</option>
                            <option value="developing">Developing</option>
                            <option value="needs_practice">Needs Practice</option>
                        </select>
                    </div>
                </div>
                <div id="modalGestureContainer" class="max-h-[260px] overflow-y-auto rounded-xl border border-slate-100 bg-slate-50/40 p-2.5 space-y-2">
                    <div id="modalGestureList" class="space-y-2"></div>
                </div>
            </div>

            <!-- TAB: LESSON BREAKDOWN -->
            <div id="tab-lessons" class="modal-tab-panel hidden">
                <h4 class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-3">Lesson Breakdown</h4>
                <div id="modalLessonList" class="space-y-2"></div>
            </div>

            <!-- TAB: STUDENT REPORTS -->
            <div id="tab-reports" class="modal-tab-panel hidden">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase">Student Reports</h4>
                    <span id="modal-reports-count" class="text-[11px] font-semibold text-slate-400"></span>
                </div>

                <!-- Reports loading spinner -->
                <div id="modal-reports-loading" class="py-8 text-center hidden">
                    <span class="material-symbols-outlined text-slate-300 text-[30px] animate-spin">refresh</span>
                    <p class="text-[12px] text-slate-400 mt-2">Loading reports...</p>
                </div>

                <!-- Reports list -->
                <div id="modal-reports-list" class="space-y-3 max-h-[340px] overflow-y-auto pr-1"></div>

                <!-- Report detail panel (inline) -->
                <div id="modal-report-detail" class="hidden mt-4 border border-slate-100 rounded-2xl overflow-hidden">
                    <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-100">
                        <button onclick="closeReportDetail()" class="flex items-center gap-1.5 text-[12px] font-semibold text-slate-500 hover:text-[#0d326b] transition-colors">
                            <span class="material-symbols-outlined text-[16px]">arrow_back</span>Back to list
                        </button>
                        <span id="detail-status-badge" class="px-2.5 py-1 rounded-full text-[10px] font-bold"></span>
                    </div>
                    <div class="p-4 space-y-3 max-h-[300px] overflow-y-auto">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Student's Message</p>
                            <div class="bg-slate-50 rounded-xl p-3">
                                <p id="detail-message" class="text-[13px] text-slate-700 leading-relaxed"></p>
                            </div>
                            <p id="detail-date" class="text-[11px] text-slate-400 font-medium mt-1.5"></p>
                        </div>
                        <div id="detail-teacher-response-wrap" class="hidden">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Teacher's Response</p>
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                                <p id="detail-teacher-response" class="text-[13px] text-blue-800 leading-relaxed"></p>
                                <p id="detail-teacher-date" class="text-[11px] text-blue-400 font-medium mt-1"></p>
                            </div>
                        </div>
                        <div id="detail-escalation-wrap" class="hidden">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Escalation Reason</p>
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                                <p id="detail-escalation-reason" class="text-[13px] text-amber-800 leading-relaxed font-medium"></p>
                                <p id="detail-escalation-date" class="text-[11px] text-amber-500 font-medium mt-1"></p>
                            </div>
                        </div>
                        <div id="detail-admin-response-wrap" class="hidden">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Admin Response</p>
                            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3">
                                <p id="detail-admin-response" class="text-[13px] text-emerald-800 leading-relaxed"></p>
                            </div>
                        </div>
                        <!-- Teacher action area -->
                        <div id="detail-action-area">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Your Response</p>
                            <textarea id="detail-teacher-note" rows="3" placeholder="Write a response to the student..."
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-[13px] text-slate-700 focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/30 outline-none resize-none leading-relaxed"></textarea>
                            <div class="flex items-center gap-2 mt-2">
                                <button onclick="submitTeacherReview('under_review')"
                                    class="px-3 py-2 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">hourglass_top</span>Mark Under Review
                                </button>
                                <button onclick="submitTeacherReview('resolved')"
                                    class="px-3 py-2 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">check_circle</span>Resolve
                                </button>
                                <button onclick="openEscalatePanel()"
                                    class="ml-auto px-3 py-2 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">flag</span>Raise to Admin
                                </button>
                            </div>
                        </div>
                        <!-- Escalate panel -->
                        <div id="detail-escalate-panel" class="hidden border border-amber-200 bg-amber-50 rounded-xl p-3 mt-2">
                            <p class="text-[11px] font-bold text-amber-700 mb-2">Reason for escalating to Admin:</p>
                            <textarea id="detail-escalation-input" rows="3" placeholder="Describe why this needs admin attention..."
                                class="w-full px-3 py-2 rounded-xl border border-amber-200 bg-white text-[13px] text-slate-700 focus:ring-2 focus:ring-amber-400/30 outline-none resize-none leading-relaxed"></textarea>
                            <div class="flex items-center gap-2 mt-2">
                                <button onclick="submitEscalation()"
                                    class="flex-1 px-4 py-2 rounded-full text-[12px] font-bold text-white flex items-center justify-center gap-1.5 transition-all hover:opacity-90"
                                    style="background: linear-gradient(135deg,#b45309 0%,#d97706 100%)">
                                    <span class="material-symbols-outlined text-[14px]">flag</span>Confirm Escalation
                                </button>
                                <button onclick="document.getElementById('detail-escalate-panel').classList.add('hidden')"
                                    class="px-4 py-2 rounded-full text-[12px] font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition-colors">Cancel</button>
                            </div>
                        </div>
                        <div id="detail-feedback" class="hidden px-3 py-2.5 rounded-xl text-[12px] font-semibold mt-2"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const studentReportData = @json($studentReports->values());
    let currentModalGestureData = [];

    // ── Auto-open from notification click (?open_student=ID) ─────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const params   = new URLSearchParams(window.location.search);
        const openId   = params.get('open_student');
        if (openId) {
            const idx = studentReportData.findIndex(s => String(s.student_id) === String(openId));
            if (idx !== -1) {
                openStudentModal(idx);
            }
            // Clean the URL without reloading so refreshing doesn't re-open
            const cleanUrl = window.location.pathname;
            history.replaceState(null, '', cleanUrl);
        }
    });

    function openStudentModal(index) {
        const data = studentReportData[index];
        if (!data) return;

        window._modalInitials = data.initials || 'S';
        const modalAvatar = document.getElementById('modalAvatar');
        if (modalAvatar) {
            modalAvatar.src = data.avatar_url || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(data.initials || 'S') + '&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45');
        }
        document.getElementById('modalStudentName').textContent = data.studentName;
        document.getElementById('modalGradeLevel').textContent = data.gradeLevel;

        document.getElementById('modalOverallPct').textContent = data.overallPct + '%';
        document.getElementById('modalLessonsCount').textContent = data.completedLessons + ' / ' + data.totalLessons;
        document.getElementById('modalQuizzesCount').textContent = (data.quizzesPassed || 0) + ' / ' + data.quizzesTaken;
        document.getElementById('modalAvgScore').textContent = data.quizzesTaken > 0 ? data.avgScore + ' pts' : '—';
        document.getElementById('modalGestureAccuracy').textContent = (data.gestureAttempts && data.gestureAttempts > 0) ? data.gestureAccuracy + '%' : '—';
        document.getElementById('modalLastActive').textContent = 'Last active ' + data.lastAccessed;

        const bar = document.getElementById('modalOverallBar');
        bar.style.width = data.overallPct + '%';
        bar.className = 'h-full rounded-full ' + (data.overallPct >= 100 ? 'bg-[#0d326b]' : 'bg-[#1a6fd4]');

        // Store & Filter Gesture Performance
        currentModalGestureData = data.gestureBreakdown || [];
        const searchInput = document.getElementById('modalGestureSearch');
        const filterSelect = document.getElementById('modalGestureMastery');
        if (searchInput) searchInput.value = '';
        if (filterSelect) filterSelect.value = 'all';
        filterModalGestures();

        // Render Lessons Breakdown
        renderModalLessons(data.lessons);

        const overlay = document.getElementById('studentModalOverlay');
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function filterModalGestures() {
        const query = (document.getElementById('modalGestureSearch')?.value || '').toLowerCase().trim();
        const mastery = document.getElementById('modalGestureMastery')?.value || 'all';
        const gListEl = document.getElementById('modalGestureList');

        if (!gListEl) return;
        gListEl.innerHTML = '';

        const filtered = currentModalGestureData.filter(function(g) {
            const matchesSearch = !query || g.gestureName.toLowerCase().includes(query);
            const matchesMastery = mastery === 'all' || (g.masteryLevel || '').toLowerCase() === mastery.toLowerCase();
            return matchesSearch && matchesMastery;
        });

        if (filtered.length === 0) {
            gListEl.innerHTML = '<p class="text-[12px] text-slate-400 font-medium py-4 text-center">No gesture performance records available.</p>';
            return;
        }

        filtered.forEach(function(g) {
            const badgeClasses = {
                'mastered': 'background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;',
                'proficient': 'background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe;',
                'developing': 'background:#fef3c7;color:#92400e;border:1px solid #fde68a;',
                'needs_practice': 'background:#ffe4e6;color:#9f1239;border:1px solid #fecdd3;',
            };
            const mLevel = (g.masteryLevel || '').toLowerCase();
            const badgeStyle = badgeClasses[mLevel] || 'background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;';
            const mLabel = (g.masteryLevel || 'needs_practice').replace('_', ' ').toUpperCase();

            const gRow = document.createElement('div');
            gRow.className = 'bg-white rounded-xl p-3 border border-slate-100 shadow-2xs flex items-center justify-between gap-3';
            gRow.innerHTML = `
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px;">
                        <span style="font-size:13px;font-weight:700;color:#0d326b;">${g.gestureName}</span>
                        <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:9px;font-weight:800;${badgeStyle}">${mLabel}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;font-size:11px;font-weight:500;color:#64748b;">
                        <span>Attempts: <strong>${g.attempts}</strong></span>
                        <span style="color:#059669;">Correct: <strong>${g.successfulAttempts}</strong></span>
                        <span style="color:#e11d48;">Incorrect: <strong>${g.wrongAttempts}</strong></span>
                        <span style="color:#94a3b8;font-size:10px;">${g.lastAttemptAt || ''}</span>
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <span style="font-size:15px;font-weight:900;color:#0d326b;">${Number(g.accuracy).toFixed(1)}%</span>
                </div>
            `;
            gListEl.appendChild(gRow);
        });
    }

    function renderModalLessons(lessons) {
        const listEl = document.getElementById('modalLessonList');
        if (!listEl) return;
        listEl.innerHTML = '';

        if (!lessons || lessons.length === 0) {
            listEl.innerHTML = '<p class="text-[13px] text-slate-400 font-medium py-4 text-center">No lessons available.</p>';
            return;
        }

        const modulesMap = {};
        lessons.forEach(function (lesson) {
            const modName = lesson.moduleTitle || 'Unassigned Lessons';
            if (!modulesMap[modName]) modulesMap[modName] = [];
            modulesMap[modName].push(lesson);
        });

        Object.keys(modulesMap).forEach(function (modTitle) {
            const modHeader = document.createElement('div');
            modHeader.className = 'flex items-center gap-2 pt-3 pb-1.5 border-b border-slate-200 mt-3 mb-2.5';
            modHeader.innerHTML = `
                <span class="material-symbols-outlined text-[16px] text-[#0d326b]">folder</span>
                <h5 class="text-[11px] font-extrabold text-[#0d326b] uppercase tracking-wider">${modTitle}</h5>
                <span class="text-[10px] font-semibold text-slate-400">(${modulesMap[modTitle].length} lesson${modulesMap[modTitle].length !== 1 ? 's' : ''})</span>
            `;
            listEl.appendChild(modHeader);

            modulesMap[modTitle].forEach(function (lesson) {
                let statusBadge, quizBadge, barColor, rowBg, titleColor, metaColor;

                if (lesson.is_exam) {
                    if (!lesson.started) {
                        statusBadge = '<span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;color:#94a3b8;background:#f1f5f9;border-radius:9999px;padding:3px 10px;letter-spacing:.04em;">NOT STARTED</span>';
                        quizBadge   = '<span style="font-size:11px;color:#cbd5e1;font-weight:500;">—</span>';
                        barColor    = 'background:#e2e8f0';
                        rowBg       = 'background:#f8fafc;opacity:.75';
                        titleColor  = 'color:#94a3b8';
                        metaColor   = 'color:#cbd5e1';
                    } else if (lesson.completed) {
                        statusBadge = '<span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;color:#065f46;background:#d1fae5;border-radius:9999px;padding:3px 10px;letter-spacing:.04em;">✓ PASSED</span>';
                        quizBadge   = '<span style="font-size:12px;font-weight:800;color:#065f46;">' + Number(lesson.quizScore).toFixed(1) + '%</span>';
                        barColor    = 'background:#059669';
                        rowBg       = 'background:#f0fdf4';
                        titleColor  = 'color:#065f46';
                        metaColor   = 'color:#64748b';
                    } else if (lesson.failed) {
                        statusBadge = '<span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;color:#991b1b;background:#fee2e2;border-radius:9999px;padding:3px 10px;letter-spacing:.04em;">✕ FAILED</span>';
                        quizBadge   = '<span style="font-size:12px;font-weight:800;color:#e11d48;">' + Number(lesson.quizScore).toFixed(1) + '%</span>';
                        barColor    = 'background:#ef4444';
                        rowBg       = 'background:#fef2f2';
                        titleColor  = 'color:#991b1b';
                        metaColor   = 'color:#64748b';
                    } else {
                        statusBadge = '<span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;color:#1a6fd4;background:#eff6ff;border-radius:9999px;padding:3px 10px;letter-spacing:.04em;">IN PROGRESS</span>';
                        quizBadge   = '<span style="font-size:11px;color:#94a3b8;font-weight:500;">In Progress</span>';
                        barColor    = 'background:#1a6fd4';
                        rowBg       = 'background:#f8fafc';
                        titleColor  = 'color:#1e293b';
                        metaColor   = 'color:#94a3b8';
                    }
                } else {
                    if (!lesson.started) {
                        statusBadge = '<span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;color:#94a3b8;background:#f1f5f9;border-radius:9999px;padding:3px 10px;letter-spacing:.04em;">NOT STARTED</span>';
                        quizBadge   = '<span style="font-size:11px;color:#cbd5e1;font-weight:500;">—</span>';
                        barColor    = 'background:#e2e8f0';
                        rowBg       = 'background:#f8fafc;opacity:.75';
                        titleColor  = 'color:#94a3b8';
                        metaColor   = 'color:#cbd5e1';
                    } else if (lesson.completed) {
                        statusBadge = '<span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;color:#0d326b;background:#dbeafe;border-radius:9999px;padding:3px 10px;letter-spacing:.04em;">✓ COMPLETED</span>';
                        quizBadge   = lesson.quizCompleted
                            ? '<span style="font-size:12px;font-weight:700;color:#0d326b;">' + lesson.quizScore + ' pts</span>'
                            : '<span style="font-size:11px;color:#94a3b8;font-weight:500;">Quiz Pending</span>';
                        barColor    = 'background:#0d326b';
                        rowBg       = 'background:#f8fafc';
                        titleColor  = 'color:#1e293b';
                        metaColor   = 'color:#94a3b8';
                    } else {
                        statusBadge = '<span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;color:#1a6fd4;background:#eff6ff;border-radius:9999px;padding:3px 10px;letter-spacing:.04em;">IN PROGRESS</span>';
                        quizBadge   = lesson.quizCompleted
                            ? '<span style="font-size:12px;font-weight:700;color:#0d326b;">' + lesson.quizScore + ' pts</span>'
                            : '<span style="font-size:11px;color:#94a3b8;font-weight:500;">Quiz Pending</span>';
                        barColor    = 'background:#1a6fd4';
                        rowBg       = 'background:#f8fafc';
                        titleColor  = 'color:#1e293b';
                        metaColor   = 'color:#94a3b8';
                    }
                }

                const creatorChip = lesson.is_exam
                    ? `<span style="display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:700;color:#0369a1;background:#e0f2fe;border-radius:9999px;padding:2px 8px;letter-spacing:.04em;">
                           <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                           Checkpoint Exam
                       </span>`
                    : (lesson.ai_generated
                        ? `<span style="display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:700;color:#7c3aed;background:#f3e8ff;border-radius:9999px;padding:2px 8px;letter-spacing:.04em;">
                               <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>
                               AI Generated
                           </span>`
                        : `<span style="display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:700;color:#0d326b;background:#e0f2fe;border-radius:9999px;padding:2px 8px;letter-spacing:.04em;">
                               <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                               By Teacher
                           </span>`);

                const row = document.createElement('div');
                row.style.cssText = rowBg + ';border-radius:16px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;';
                row.innerHTML = `
                    <div style="flex:1;min-width:0;padding-right:16px;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:3px;">
                            <p style="font-size:13px;font-weight:700;${titleColor};line-height:1.3;margin:0;">${lesson.lessonTitle}</p>
                            ${creatorChip}
                        </div>
                        <p style="font-size:11px;font-weight:500;${metaColor};margin:0 0 6px;">${lesson.is_exam ? 'Checkpoint Exam · ' : (lesson.lessonType ? lesson.lessonType + ' · ' : '')}${lesson.lastAccessed}</p>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:80px;height:5px;background:#e2e8f0;border-radius:9999px;overflow:hidden;">
                                <div style="height:100%;border-radius:9999px;${barColor};width:${lesson.stepPct}%;"></div>
                            </div>
                            <span style="font-size:10px;font-weight:700;color:#94a3b8;">${lesson.stepPct}%</span>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0;">
                        ${statusBadge}
                        ${quizBadge}
                    </div>
                `;
                listEl.appendChild(row);
            });
        });
    }

    function closeStudentModal() {
        const overlay = document.getElementById('studentModalOverlay');
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        document.body.style.overflow = '';
        currentReportDetailId = null;
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeStudentModal();
    });

    // ──────────────────────────────────────────────────────────────────────
    // MODAL TAB SWITCHING
    // ──────────────────────────────────────────────────────────────────────
    let _currentModalStudentId = null;

    function switchModalTab(tab) {
        ['gestures', 'lessons', 'reports'].forEach(t => {
            document.getElementById('tab-' + t).classList.toggle('hidden', t !== tab);
            const btn = document.getElementById('tab-btn-' + t);
            if (t === tab) {
                btn.classList.add('border-b-2', 'border-[#0d326b]', 'text-[#0d326b]');
                btn.classList.remove('text-slate-400');
            } else {
                btn.classList.remove('border-b-2', 'border-[#0d326b]', 'text-[#0d326b]');
                btn.classList.add('text-slate-400');
            }
        });

        if (tab === 'reports' && _currentModalStudentId) {
            loadStudentReports(_currentModalStudentId);
        }
    }

    // Override openStudentModal to wire up tabs + student ID
    const _origOpenStudentModal = openStudentModal;
    openStudentModal = function(index) {
        _origOpenStudentModal(index);
        const data = studentReportData[index];
        if (!data) return;
        _currentModalStudentId = data.student_id;
        // Reset to gestures tab
        switchModalTab('gestures');
        // Reset reports panel
        document.getElementById('modal-reports-list').innerHTML = '';
        document.getElementById('modal-report-detail').classList.add('hidden');
        document.getElementById('modal-reports-loading').classList.add('hidden');
        document.getElementById('modal-reports-badge').classList.add('hidden');
        document.getElementById('modal-reports-count').textContent = '';
    };

    // ──────────────────────────────────────────────────────────────────────
    // STUDENT REPORTS TAB — LOAD & RENDER
    // ──────────────────────────────────────────────────────────────────────
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let currentReportDetailId = null;
    let _reportsLoaded = {};

    const reportStatusConfig = {
        pending:      { label: 'Pending',      bg: 'bg-slate-100',   text: 'text-slate-600' },
        under_review: { label: 'Under Review',  bg: 'bg-blue-100',    text: 'text-blue-700'  },
        resolved:     { label: 'Resolved',      bg: 'bg-emerald-100', text: 'text-emerald-700'},
        escalated:    { label: 'Escalated',     bg: 'bg-amber-100',   text: 'text-amber-700' },
        closed:       { label: 'Closed',        bg: 'bg-slate-200',   text: 'text-slate-500' },
    };

    async function loadStudentReports(studentId) {
        const listEl    = document.getElementById('modal-reports-list');
        const loadingEl = document.getElementById('modal-reports-loading');

        listEl.innerHTML = '';
        loadingEl.classList.remove('hidden');

        try {
            const res  = await fetch(`/reports/help-requests/${studentId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await res.json();
            loadingEl.classList.add('hidden');

            const reports = data.reports || [];
            document.getElementById('modal-reports-count').textContent =
                reports.length + ' report' + (reports.length !== 1 ? 's' : '');

            const hasPending = reports.some(r => r.status === 'pending');
            const badge = document.getElementById('modal-reports-badge');
            if (hasPending) badge.classList.remove('hidden');
            else            badge.classList.add('hidden');

            if (reports.length === 0) {
                listEl.innerHTML = `
                    <div class="py-10 text-center">
                        <span class="material-symbols-outlined text-slate-200 text-[40px]">mark_email_read</span>
                        <p class="text-[13px] text-slate-400 font-semibold mt-3">No reports submitted</p>
                        <p class="text-[11px] text-slate-300 mt-1">This student hasn't submitted any concerns</p>
                    </div>`;
                return;
            }

            reports.forEach(report => {
                const sc = reportStatusConfig[report.status] || reportStatusConfig.pending;
                const card = document.createElement('div');
                card.className = 'bg-white border border-slate-100 rounded-2xl p-4 cursor-pointer hover:bg-slate-50 transition-colors';
                card.onclick = () => openReportDetail(report.help_request_id);
                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <p class="text-[13px] font-semibold text-slate-700 leading-snug line-clamp-2 flex-1">${report.message}</p>
                        <span class="flex-shrink-0 px-2.5 py-1 rounded-full text-[10px] font-bold ${sc.bg} ${sc.text}">${sc.label}</span>
                    </div>
                    <div class="flex items-center gap-3 text-[11px] text-slate-400 font-medium flex-wrap">
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[13px]">schedule</span>${report.created_at}</span>
                        ${report.escalated_at ? '<span class="flex items-center gap-1 text-amber-600 font-semibold"><span class="material-symbols-outlined text-[13px]">flag</span>Escalated</span>' : ''}
                        ${report.teacher_response ? '<span class="flex items-center gap-1 text-emerald-600"><span class="material-symbols-outlined text-[13px]">mark_chat_read</span>You responded</span>' : ''}
                    </div>
                `;
                listEl.appendChild(card);
            });
        } catch (e) {
            loadingEl.classList.add('hidden');
            listEl.innerHTML = '<p class="text-[12px] text-red-500 font-medium py-4 text-center">Failed to load reports.</p>';
        }
    }

    async function openReportDetail(reportId) {
        currentReportDetailId = reportId;
        const detailEl  = document.getElementById('modal-report-detail');
        const listEl    = document.getElementById('modal-reports-list');

        listEl.classList.add('hidden');
        detailEl.classList.remove('hidden');

        // Reset
        ['detail-teacher-response-wrap','detail-escalation-wrap','detail-admin-response-wrap']
            .forEach(id => document.getElementById(id).classList.add('hidden'));
        document.getElementById('detail-teacher-note').value = '';
        document.getElementById('detail-feedback').classList.add('hidden');
        document.getElementById('detail-escalate-panel').classList.add('hidden');
        document.getElementById('detail-message').textContent = 'Loading…';

        try {
            const res  = await fetch(`/reports/help-requests/${reportId}/detail`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const d = await res.json();
            renderReportDetail(d);
        } catch {
            document.getElementById('detail-message').textContent = 'Failed to load report.';
        }
    }

    function renderReportDetail(d) {
        const sc = reportStatusConfig[d.status] || reportStatusConfig.pending;
        const badge = document.getElementById('detail-status-badge');
        badge.textContent = sc.label;
        badge.className   = `px-2.5 py-1 rounded-full text-[10px] font-bold ${sc.bg} ${sc.text}`;

        document.getElementById('detail-message').textContent = d.message;
        document.getElementById('detail-date').textContent    = 'Submitted ' + d.created_at;

        if (d.teacher_response) {
            document.getElementById('detail-teacher-response').textContent = d.teacher_response;
            document.getElementById('detail-teacher-date').textContent = d.teacher_responded_at ? 'Responded ' + d.teacher_responded_at : '';
            document.getElementById('detail-teacher-response-wrap').classList.remove('hidden');
        }

        if (d.escalation_reason) {
            document.getElementById('detail-escalation-reason').textContent = d.escalation_reason;
            document.getElementById('detail-escalation-date').textContent   = d.escalated_at ? 'Escalated ' + d.escalated_at : '';
            document.getElementById('detail-escalation-wrap').classList.remove('hidden');
        }

        if (d.admin_response) {
            document.getElementById('detail-admin-response').textContent = d.admin_response;
            document.getElementById('detail-admin-response-wrap').classList.remove('hidden');
        }

        // Hide action area if report is already escalated or closed
        const actionArea = document.getElementById('detail-action-area');
        if (d.status === 'escalated' || d.status === 'closed') {
            actionArea.style.display = 'none';
        } else {
            actionArea.style.display = 'block';
        }
    }

    function closeReportDetail() {
        document.getElementById('modal-report-detail').classList.add('hidden');
        document.getElementById('modal-reports-list').classList.remove('hidden');
        currentReportDetailId = null;
        // Reload list to reflect any updates
        if (_currentModalStudentId) loadStudentReports(_currentModalStudentId);
    }

    async function submitTeacherReview(status) {
        if (!currentReportDetailId) return;
        const note     = document.getElementById('detail-teacher-note').value.trim();
        const feedback = document.getElementById('detail-feedback');

        try {
            const res  = await fetch(`/reports/help-requests/${currentReportDetailId}/review`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ status, teacher_response: note }),
            });
            const data = await res.json();

            if (data.success) {
                feedback.textContent = status === 'resolved' ? 'Report marked as resolved.' : 'Status updated to Under Review.';
                feedback.className   = 'px-3 py-2.5 rounded-xl text-[12px] font-semibold bg-emerald-50 text-emerald-700 mt-2';
                feedback.classList.remove('hidden');
                const badge = document.getElementById('detail-status-badge');
                const sc = reportStatusConfig[data.status] || reportStatusConfig.pending;
                badge.textContent = sc.label;
                badge.className   = `px-2.5 py-1 rounded-full text-[10px] font-bold ${sc.bg} ${sc.text}`;
                if (note) {
                    document.getElementById('detail-teacher-response').textContent = note;
                    document.getElementById('detail-teacher-response-wrap').classList.remove('hidden');
                }
                if (status === 'resolved') {
                    document.getElementById('detail-action-area').style.display = 'none';
                }
                setTimeout(closeReportDetail, 1200);
            } else {
                feedback.textContent = data.message || 'Failed to update.';
                feedback.className   = 'px-3 py-2.5 rounded-xl text-[12px] font-semibold bg-red-50 text-red-600 mt-2';
                feedback.classList.remove('hidden');
            }
        } catch {
            const feedback = document.getElementById('detail-feedback');
            feedback.textContent = 'An error occurred.';
            feedback.className   = 'px-3 py-2.5 rounded-xl text-[12px] font-semibold bg-red-50 text-red-600 mt-2';
            feedback.classList.remove('hidden');
        }
    }

    function openEscalatePanel() {
        document.getElementById('detail-escalate-panel').classList.remove('hidden');
        document.getElementById('detail-escalation-input').focus();
    }

    async function submitEscalation() {
        if (!currentReportDetailId) return;
        const reason   = document.getElementById('detail-escalation-input').value.trim();
        const feedback = document.getElementById('detail-feedback');

        if (!reason) {
            feedback.textContent = 'Please provide a reason for escalating.';
            feedback.className   = 'px-3 py-2.5 rounded-xl text-[12px] font-semibold bg-red-50 text-red-600 mt-2';
            feedback.classList.remove('hidden');
            return;
        }

        try {
            const res  = await fetch(`/reports/help-requests/${currentReportDetailId}/escalate`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ escalation_reason: reason }),
            });
            const data = await res.json();

            if (data.success) {
                feedback.textContent = 'Concern escalated to Admin successfully.';
                feedback.className   = 'px-3 py-2.5 rounded-xl text-[12px] font-semibold bg-amber-50 text-amber-700 mt-2';
                feedback.classList.remove('hidden');
                document.getElementById('detail-escalate-panel').classList.add('hidden');
                document.getElementById('detail-action-area').style.display = 'none';

                const badge = document.getElementById('detail-status-badge');
                badge.textContent = 'Escalated';
                badge.className   = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700';

                document.getElementById('detail-escalation-reason').textContent = reason;
                document.getElementById('detail-escalation-date').textContent   = 'Just now';
                document.getElementById('detail-escalation-wrap').classList.remove('hidden');

                setTimeout(closeReportDetail, 1500);
            } else {
                feedback.textContent = data.message || 'Failed to escalate.';
                feedback.className   = 'px-3 py-2.5 rounded-xl text-[12px] font-semibold bg-red-50 text-red-600 mt-2';
                feedback.classList.remove('hidden');
            }
        } catch {
            const feedback = document.getElementById('detail-feedback');
            feedback.textContent = 'An error occurred.';
            feedback.className   = 'px-3 py-2.5 rounded-xl text-[12px] font-semibold bg-red-50 text-red-600 mt-2';
            feedback.classList.remove('hidden');
        }
    }
</script>

<style>
.modal-tab-btn {
    color: #94a3b8;
    border-bottom: 2px solid transparent;
    transition: color .15s, border-color .15s;
}
.modal-tab-btn:hover { color: #0d326b; }
</style>

@endsection