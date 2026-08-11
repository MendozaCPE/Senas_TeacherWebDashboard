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
}

.stat-card { border-radius: 20px; padding: 20px; position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(13,50,107,.12); }

/* ── KPI Ready to Promote (golden gradient) ────────────────────────────── */
.kpi-ready {
    background: linear-gradient(135deg, #f59e0b 0%, #facc15 50%, #fbbf24 100%);
}

/* ── Filter styles (matching Reports page) ──────────────────────────────── */
.filter-container {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 2px rgba(13,50,107,.04);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-select {
    appearance: none;
    background: #f1f5f9;
    color: #1e293b;
    font-size: 12px;
    font-weight: 600;
    padding: 8px 32px 8px 14px;
    border-radius: 9999px;
    border: none;
    outline: none;
    cursor: pointer;
    transition: all .2s;
    position: relative;
}
.filter-select:hover { background: #e2e8f0; }
.filter-select:focus { ring: 2px solid #0d326b; }

.filter-wrap {
    position: relative;
    display: inline-block;
}
.filter-wrap .material-symbols-outlined {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    color: #94a3b8;
    pointer-events: none;
}

.filter-btn {
    background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);
    color: #fff;
    padding: 8px 20px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all .2s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.filter-btn:hover { opacity: .9; box-shadow: 0 4px 12px rgba(13,50,107,.25); }

.filter-reset {
    padding: 8px 16px;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
    display: inline-block;
}
.filter-reset:hover { background: #f8fafc; border-color: #cbd5e1; }

.export-btn {
    background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);
    color: #fff;
    padding: 9px 22px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all .25s;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 3px 12px rgba(13,50,107,.22);
    position: relative;
    overflow: hidden;
}
.export-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,.12) 0%, transparent 100%);
    opacity: 0;
    transition: opacity .2s;
}
.export-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(13,50,107,.32); }
.export-btn:hover::before { opacity: 1; }
.export-btn:active { transform: translateY(0); }

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
    font-size: 10px;
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

<div class="space-y-6">


    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-3.5 rounded-xl text-[13px] font-medium flex items-center space-x-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ══════════ STAT CARDS (TOP) ══════════ --}}
    @php
        $totalStudentsShown = $studentReports->count();
        $fullyCompleted     = $studentReports->where('overallPct', 100)->count();
        $totalQuizzesTaken  = $studentReports->sum('quizzesTaken');
        $scoredReports      = $studentReports->filter(fn($r) => $r['quizzesTaken'] > 0);
        $avgScoreOverall    = $scoredReports->isNotEmpty() ? $scoredReports->avg('avgScore') : 0;

        // Additional analytics-style stats
        $totalLessonsAssigned = $studentReports->sum('totalLessons');
        $totalLessonsCompleted = $studentReports->sum('completedLessons');
        $classCompletionRate  = $totalLessonsAssigned > 0
            ? round(($totalLessonsCompleted / $totalLessonsAssigned) * 100, 1)
            : 0;

        $gestureStudents = $studentReports->filter(fn($r) => ($r['gestureAttempts'] ?? 0) > 0);
        $avgGestureAccuracy = $gestureStudents->isNotEmpty()
            ? round($gestureStudents->avg('gestureAccuracy'), 1)
            : 0;

        $summaryCards = [
            [
                'title'     => 'Total Students',
                'value'     => $totalStudentsShown,
                'detail'    => 'in current view',
                'icon'      => 'group',
                'hero'      => true,
            ],
            [
                'title'     => 'Completion Rate',
                'value'     => number_format($classCompletionRate, 1) . '%',
                'detail'    => $totalLessonsCompleted . ' of ' . $totalLessonsAssigned . ' lessons',
                'icon'      => 'menu_book',
                'hero'      => false,
                'iconColor' => 'text-[#1e4b8f]',
            ],
            [
                'title'     => 'Fully Completed',
                'value'     => $fullyCompleted,
                'detail'    => 'student' . ($fullyCompleted !== 1 ? 's' : '') . ' at 100%',
                'icon'      => 'check_circle',
                'hero'      => false,
                'iconColor' => 'text-emerald-600',
            ],
            [
                'title'     => 'Quizzes Taken',
                'value'     => $totalQuizzesTaken,
                'detail'    => 'total attempts',
                'icon'      => 'quiz',
                'hero'      => false,
                'iconColor' => 'text-[#1a6fd4]',
            ],
            [
                'title'     => 'Avg Quiz Score',
                'value'     => number_format($avgScoreOverall, 1),
                'detail'    => 'points per attempt',
                'icon'      => 'insights',
                'hero'      => false,
                'iconColor' => 'text-[#1e4b8f]',
            ],
            [
                'title'     => 'Gesture Accuracy',
                'value'     => number_format($avgGestureAccuracy, 1) . '%',
                'detail'    => $gestureStudents->count() . ' student' . ($gestureStudents->count() !== 1 ? 's' : '') . ' with data',
                'icon'      => 'back_hand',
                'hero'      => false,
                'iconColor' => 'text-amber-600',
                'golden'    => true,
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6 gap-4">
        @foreach($summaryCards as $card)
            @if($card['hero'] ?? false)
                {{-- Hero navy gradient card --}}
                <div class="stat-card text-white xl:col-span-1"
                     style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%)">
                    <div class="absolute -top-7 -right-7 w-28 h-28 bg-white/5 rounded-full"></div>
                    <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white/5 rounded-full"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-[18px]">{{ $card['icon'] }}</span>
                            </div>
                            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest">{{ $card['title'] }}</p>
                        </div>
                        <p class="text-[36px] font-black text-white leading-none">{{ $card['value'] }}</p>
                        <p class="text-[11px] font-semibold text-[#facc15] mt-2">{{ $card['detail'] }}</p>
                    </div>
                </div>
            @elseif($card['golden'] ?? false)
                {{-- Golden KPI card --}}
                <div class="stat-card kpi-ready">
                    <div class="absolute -top-7 -right-7 w-28 h-28 bg-[#0d326b]/5 rounded-full"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-800/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-amber-700 text-[18px]">{{ $card['icon'] }}</span>
                            </div>
                            <p class="text-[10px] font-bold text-amber-800/70 uppercase tracking-widest">{{ $card['title'] }}</p>
                        </div>
                        <p class="text-[36px] font-black text-[#92400e] leading-none">{{ $card['value'] }}</p>
                        <p class="text-[11px] font-semibold text-amber-800 mt-2">{{ $card['detail'] }}</p>
                    </div>
                </div>
            @else
                {{-- Standard white card --}}
                <div class="stat-card bg-white border border-slate-100 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-9 h-9 rounded-xl bg-[#e8eef8] flex items-center justify-center">
                            <span class="material-symbols-outlined {{ $card['iconColor'] ?? 'text-[#1e4b8f]' }} text-[18px]">{{ $card['icon'] }}</span>
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $card['title'] }}</p>
                    </div>
                    <p class="text-[36px] font-black text-[#0d326b] leading-none">{{ $card['value'] }}</p>
                    <p class="text-[11px] text-slate-400 font-medium mt-2">{{ $card['detail'] }}</p>
                </div>
            @endif
        @endforeach
    </div>

    {{-- ══════════ FILTERS (matching Analytics/Students) ══════════ --}}
    @php $rf = session('reports_filters', []); @endphp
    <form method="POST" action="{{ route('reports.filter') }}" id="filterForm">
        @csrf
        <div class="filter-container">
            <div class="filter-group">
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
                        <option value="all" {{ ($rf['lesson_id'] ?? 'all') === 'all' ? 'selected' : '' }}>All Lessons</option>
                        @foreach($lessons as $l)
                            <option value="{{ $l->lesson_id }}" {{ ($rf['lesson_id'] ?? '') == $l->lesson_id ? 'selected' : '' }}>
                                {{ $l->title }}
                            </option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider shrink-0 ml-2">
                    {{ $studentReports->count() }} student{{ $studentReports->count() !== 1 ? 's' : '' }}
                </span>

                <a href="{{ route('reports') }}" onclick="event.preventDefault(); fetch('{{ route('reports.filter') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}, body:JSON.stringify({student_id:'all',lesson_id:'all'})}).then(()=>window.location.href='{{ route('reports') }}')" class="filter-reset">Clear</a>
                <button type="submit" class="filter-btn">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                    Apply Filter
                </button>
            </div>

            {{-- Export PDF button (opens premium modal) --}}
            <button type="button" id="openExportModal" class="export-btn" onclick="openPdfModal()">
                <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                Export Report
            </button>
        </div>
    </form>

    {{-- ══════════ REPORT TABLE ══════════ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <h3 class="text-[14px] font-bold text-[#0d326b]">Student Progress Report</h3>
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
                            <th style="width:90px;">Quizzes</th>
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
                                        <div class="w-9 h-9 rounded-full bg-[#0d326b] text-white flex items-center justify-center text-[11px] font-bold flex-shrink-0">
                                            {{ $row['initials'] }}
                                        </div>
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
                                    <span class="text-[13px] font-bold text-[#1e293b]">{{ $row['quizzesTaken'] }}</span>
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
                <div id="modalInitials" class="w-12 h-12 rounded-full bg-[#0d326b] text-white flex items-center justify-center text-[14px] font-bold flex-shrink-0"></div>
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
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Quizzes</p>
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

            <!-- STUDENT PERFORMANCE PER GESTURE BREAKDOWN -->
            <div>
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

            <!-- LESSON BREAKDOWN -->
            <div>
                <h4 class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-3">Lesson Breakdown</h4>
                <div id="modalLessonList" class="space-y-2"></div>
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

        document.getElementById('modalInitials').textContent = data.initials;
        document.getElementById('modalStudentName').textContent = data.studentName;
        document.getElementById('modalGradeLevel').textContent = data.gradeLevel;

        document.getElementById('modalOverallPct').textContent = data.overallPct + '%';
        document.getElementById('modalLessonsCount').textContent = data.completedLessons + ' / ' + data.totalLessons;
        document.getElementById('modalQuizzesCount').textContent = data.quizzesTaken;
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

                const creatorChip = lesson.ai_generated
                    ? `<span style="display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:700;color:#7c3aed;background:#f3e8ff;border-radius:9999px;padding:2px 8px;letter-spacing:.04em;">
                           <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>
                           AI Generated
                       </span>`
                    : `<span style="display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:700;color:#0d326b;background:#e0f2fe;border-radius:9999px;padding:2px 8px;letter-spacing:.04em;">
                           <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                           By Teacher
                       </span>`;

                const row = document.createElement('div');
                row.style.cssText = rowBg + ';border-radius:16px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;';
                row.innerHTML = `
                    <div style="flex:1;min-width:0;padding-right:16px;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:3px;">
                            <p style="font-size:13px;font-weight:700;${titleColor};line-height:1.3;margin:0;">${lesson.lessonTitle}</p>
                            ${creatorChip}
                        </div>
                        <p style="font-size:11px;font-weight:500;${metaColor};margin:0 0 6px;">${lesson.lessonType ? lesson.lessonType + ' · ' : ''}${lesson.lastAccessed}</p>
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
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeStudentModal();
    });
</script>

@endsection