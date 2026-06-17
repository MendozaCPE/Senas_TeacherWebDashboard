<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>SEÑAS Progress Report</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 10px;
        color: #1e293b;
        background: #ffffff;
        line-height: 1.5;
    }

    /* ── COVER HEADER ── */
    .header {
        background: #0d326b;
        padding: 32px 40px 28px;
        color: #ffffff;
    }
    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    .brand {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 1px;
        color: #facc15;
    }
    .brand-sub {
        font-size: 10px;
        color: rgba(255,255,255,0.6);
        margin-top: 2px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .report-meta {
        text-align: right;
        font-size: 9px;
        color: rgba(255,255,255,0.65);
        line-height: 1.8;
    }
    .report-title {
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
        margin-top: 10px;
    }
    .report-subtitle {
        font-size: 10px;
        color: rgba(255,255,255,0.7);
        margin-top: 4px;
    }

    /* ── SUMMARY STRIP ── */
    .summary-strip {
        background: #facc15;
        padding: 14px 40px;
        display: flex;
        gap: 0;
    }
    .summary-item {
        flex: 1;
        text-align: center;
        border-right: 1px solid rgba(0,0,0,0.1);
        padding: 0 12px;
    }
    .summary-item:last-child { border-right: none; }
    .summary-val {
        font-size: 20px;
        font-weight: 700;
        color: #0d326b;
        display: block;
    }
    .summary-label {
        font-size: 8px;
        font-weight: 700;
        color: rgba(0,0,0,0.55);
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    /* ── BODY CONTENT ── */
    .content { padding: 28px 40px; }

    /* Section label */
    .section-label {
        font-size: 8px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid #f1f5f9;
    }

    /* ── TEACHER INFO ── */
    .teacher-info {
        background: #f8fafc;
        border-left: 3px solid #0d326b;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 24px;
        font-size: 10px;
        color: #475569;
    }
    .teacher-info strong { color: #0d326b; }

    /* ── TABLE ── */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 24px;
    }
    thead {
        background: #0d326b;
        color: #ffffff;
    }
    thead th {
        padding: 10px 12px;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        text-align: left;
    }
    tbody tr:nth-child(odd)  { background: #f8fafc; }
    tbody tr:nth-child(even) { background: #ffffff; }
    tbody td {
        padding: 9px 12px;
        font-size: 9.5px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    /* Status badges */
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 7.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-blue   { background: #dbeafe; color: #1d4ed8; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }

    /* Progress bar */
    .progress-wrap {
        background: #e2e8f0;
        border-radius: 10px;
        height: 6px;
        width: 80px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
        margin-right: 4px;
    }
    .progress-fill {
        height: 100%;
        border-radius: 10px;
        background: #0d326b;
    }
    .progress-fill.done { background: #22c55e; }

    /* ── LESSON BREAKDOWN ── */
    .lesson-grid { margin-bottom: 24px; }
    .lesson-row {
        display: flex;
        align-items: center;
        padding: 10px 14px;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 6px;
    }
    .lesson-order {
        width: 28px;
        height: 28px;
        background: #0d326b;
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        font-weight: 700;
        flex-shrink: 0;
        margin-right: 12px;
    }
    .lesson-title { flex: 1; font-size: 10px; font-weight: 700; color: #1e293b; }
    .lesson-pct   { font-size: 10px; font-weight: 700; color: #0d326b; margin-left: 12px; }

    /* ── FOOTER ── */
    .footer {
        margin-top: 30px;
        padding-top: 16px;
        border-top: 2px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        font-size: 8px;
        color: #94a3b8;
    }
    .footer-brand { color: #0d326b; font-weight: 700; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>

{{-- ══ HEADER ══ --}}
<div class="header">
    <div class="header-top">
        <div>
            <div class="brand">SEÑAS</div>
            <div class="brand-sub">Teacher Web Dashboard</div>
        </div>
        <div class="report-meta">
            <div><strong>Generated:</strong> {{ $generatedAt }}</div>
            <div><strong>Teacher:</strong> {{ $teacherName }}</div>
            <div><strong>School:</strong> {{ $schoolName }}</div>
        </div>
    </div>
    <div class="report-title">Student Progress Report</div>
    <div class="report-subtitle">
        Filter: {{ $selectedStudentName }} &nbsp;·&nbsp; {{ $selectedLessonName }}
    </div>
</div>

{{-- ══ SUMMARY STRIP ══ --}}
<div class="summary-strip">
    <div class="summary-item">
        <span class="summary-val">{{ $totalStudents }}</span>
        <span class="summary-label">Total Students</span>
    </div>
    <div class="summary-item">
        <span class="summary-val">{{ $totalProgress }}</span>
        <span class="summary-label">Progress Records</span>
    </div>
    <div class="summary-item">
        <span class="summary-val">{{ $totalCompleted }}</span>
        <span class="summary-label">Lessons Completed</span>
    </div>
    <div class="summary-item">
        <span class="summary-val">{{ $completionPct }}%</span>
        <span class="summary-label">Completion Rate</span>
    </div>
    <div class="summary-item">
        <span class="summary-val">{{ number_format($avgScore, 1) }}</span>
        <span class="summary-label">Avg Quiz Score</span>
    </div>
</div>

{{-- ══ MAIN CONTENT ══ --}}
<div class="content">

    {{-- Teacher info --}}
    <div class="teacher-info">
        <strong>Prepared by:</strong> {{ $teacherName }} &nbsp;·&nbsp;
        <strong>School:</strong> {{ $schoolName }} &nbsp;·&nbsp;
        <strong>Report Period:</strong> {{ now()->format('F Y') }}
    </div>

    {{-- Lesson Breakdown --}}
    @if($lessons->isNotEmpty())
    <div class="section-label">Lesson Overview</div>
    <div class="lesson-grid">
        @foreach($lessons as $lesson)
            @php
                $completed = $reportRows->where('lesson_id', $lesson->lesson_id)->where('lesson_completed', 1)->count();
                $total     = $reportRows->where('lesson_id', $lesson->lesson_id)->count();
                $pct       = $total > 0 ? round(($completed / $total) * 100) : 0;
            @endphp
            <div class="lesson-row">
                <div class="lesson-order">{{ str_pad($lesson->module_order, 2, '0', STR_PAD_LEFT) }}</div>
                <div class="lesson-title">{{ $lesson->title }}</div>
                <div style="width:120px; background:#e2e8f0; border-radius:10px; height:6px; overflow:hidden; margin-right:10px;">
                    <div style="width:{{ $pct }}%; background:#0d326b; height:100%; border-radius:10px;"></div>
                </div>
                <div class="lesson-pct">{{ $pct }}%</div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- Progress Table --}}
    <div class="section-label">Detailed Progress Records</div>
    @if($reportRows->isEmpty())
        <p style="color:#94a3b8; font-size:10px; text-align:center; padding:24px;">No records match the selected filters.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Lesson</th>
                <th>Difficulty</th>
                <th>Status</th>
                <th>Quiz Score</th>
                <th>Current Step</th>
                <th>Last Active</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportRows as $i => $row)
            <tr>
                <td style="color:#94a3b8; font-weight:700;">{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</td>
                <td style="font-weight:700;">{{ $row->studentName }}</td>
                <td>{{ Str::limit($row->lessonTitle, 28) }}</td>
                <td style="text-transform:capitalize;">{{ $row->difficulty }}</td>
                <td>
                    @if($row->lesson_completed)
                        <span class="badge badge-green">Completed</span>
                    @else
                        <span class="badge badge-blue">In Progress</span>
                    @endif
                </td>
                <td style="font-weight:700; color:#0d326b;">
                    @if($row->quiz_completed)
                        {{ $row->quiz_score }} pts
                    @else
                        <span style="color:#94a3b8;">Pending</span>
                    @endif
                </td>
                <td>
                    <div class="progress-wrap">
                        <div class="progress-fill {{ $row->lesson_completed ? 'done' : '' }}"
                             style="width: {{ min(100, round(($row->current_step / 7) * 100)) }}%"></div>
                    </div>
                    Step {{ $row->current_step }}/7
                </td>
                <td>{{ $row->lastAccessed }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <span><span class="footer-brand">SEÑAS</span> Teacher Web Dashboard — Confidential Academic Report</span>
        <span>Generated on {{ $generatedAt }}</span>
    </div>
</div>

</body>
</html>
