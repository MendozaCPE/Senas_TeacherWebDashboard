<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>SEÑAS Analytics Report</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 10px;
        color: #1e293b;
        background: #ffffff;
        line-height: 1.5;
    }

    /* ── HEADER ── */
    .header { background: #0d326b; padding: 32px 40px 28px; color: #fff; }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    .brand      { font-size: 22px; font-weight: 700; letter-spacing: 1px; color: #facc15; }
    .brand-sub  { font-size: 10px; color: rgba(255,255,255,0.6); margin-top: 2px; letter-spacing: 1px; text-transform: uppercase; }
    .report-meta { text-align: right; font-size: 9px; color: rgba(255,255,255,0.65); line-height: 1.8; }
    .report-title    { font-size: 18px; font-weight: 700; color: #fff; margin-top: 10px; }
    .report-subtitle { font-size: 10px; color: rgba(255,255,255,0.7); margin-top: 4px; }

    /* ── SUMMARY STRIP ── */
    .summary-strip { background: #facc15; padding: 14px 40px; display: flex; }
    .summary-item { flex: 1; text-align: center; border-right: 1px solid rgba(0,0,0,0.1); padding: 0 10px; }
    .summary-item:last-child { border-right: none; }
    .summary-val   { font-size: 20px; font-weight: 700; color: #0d326b; display: block; }
    .summary-label { font-size: 8px; font-weight: 700; color: rgba(0,0,0,0.55); text-transform: uppercase; letter-spacing: 0.8px; }

    /* ── CONTENT ── */
    .content { padding: 28px 40px; }
    .section-label {
        font-size: 8px; font-weight: 700; color: #94a3b8;
        text-transform: uppercase; letter-spacing: 1.5px;
        margin-bottom: 12px; padding-bottom: 6px;
        border-bottom: 1px solid #f1f5f9;
    }
    .two-col { display: flex; gap: 20px; margin-bottom: 24px; }
    .col-half { flex: 1; }

    /* ── LESSON COMPLETION BARS ── */
    .lesson-row {
        display: flex; align-items: center;
        padding: 10px 14px; background: #f8fafc;
        border-radius: 8px; margin-bottom: 6px;
    }
    .lesson-num {
        width: 26px; height: 26px; background: #0d326b; color: #fff;
        border-radius: 50%; display: flex; align-items: center;
        justify-content: center; font-size: 9px; font-weight: 700;
        flex-shrink: 0; margin-right: 10px;
    }
    .lesson-title { flex: 1; font-size: 9.5px; font-weight: 700; color: #1e293b; }
    .lesson-pct   { font-size: 10px; font-weight: 700; color: #0d326b; width: 36px; text-align: right; margin-left: 10px; }
    .bar-wrap { width: 100px; background: #e2e8f0; border-radius: 10px; height: 6px; overflow: hidden; }
    .bar-fill { height: 100%; border-radius: 10px; background: #0d326b; }

    /* ── STUDENT TABLE ── */
    table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    thead { background: #0d326b; color: #fff; }
    thead th { padding: 9px 12px; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; text-align: left; }
    tbody tr:nth-child(odd)  { background: #f8fafc; }
    tbody tr:nth-child(even) { background: #ffffff; }
    tbody td { padding: 9px 12px; font-size: 9.5px; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

    .rank-badge {
        display: inline-block; width: 20px; height: 20px;
        background: #0d326b; color: #fff; border-radius: 50%;
        font-size: 8px; font-weight: 700; text-align: center; line-height: 20px;
    }
    .rank-badge.gold   { background: #facc15; color: #0d326b; }
    .rank-badge.silver { background: #94a3b8; color: #fff; }
    .rank-badge.bronze { background: #d97706; color: #fff; }

    /* ── INSIGHT BOX ── */
    .insight-box {
        background: #f0f9ff; border-left: 3px solid #3b82f6;
        padding: 14px 16px; border-radius: 8px; margin-bottom: 24px;
    }
    .insight-title { font-size: 10px; font-weight: 700; color: #0d326b; margin-bottom: 4px; }
    .insight-text  { font-size: 9.5px; color: #475569; line-height: 1.6; }

    /* ── FOOTER ── */
    .footer { margin-top: 30px; padding-top: 14px; border-top: 2px solid #f1f5f9; display: flex; justify-content: space-between; font-size: 8px; color: #94a3b8; }
    .footer-brand { color: #0d326b; font-weight: 700; }
</style>
</head>
<body>

{{-- HEADER --}}
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
    <div class="report-title">Class Analytics Report</div>
    <div class="report-subtitle">Overall performance summary for {{ now()->format('F Y') }}</div>
</div>

{{-- SUMMARY STRIP --}}
<div class="summary-strip">
    <div class="summary-item">
        <span class="summary-val">{{ $totalStudents }}</span>
        <span class="summary-label">Students</span>
    </div>
    <div class="summary-item">
        <span class="summary-val">{{ $totalProgress }}</span>
        <span class="summary-label">Progress Records</span>
    </div>
    <div class="summary-item">
        <span class="summary-val">{{ $totalCompleted }}</span>
        <span class="summary-label">Completions</span>
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

{{-- CONTENT --}}
<div class="content">

    {{-- Insight box --}}
    <div class="insight-box">
        <div class="insight-title">Class Summary</div>
        <div class="insight-text">
            As of {{ now()->format('F d, Y') }}, your class has
            <strong>{{ $totalStudents }} enrolled students</strong> across
            <strong>{{ $lessonCompletion->count() }} published lessons</strong>.
            The overall completion rate is <strong>{{ $completionPct }}%</strong>
            @if($completionPct >= 70)
                — an excellent result!
            @elseif($completionPct >= 40)
                — good progress, keep encouraging participation.
            @else
                — consider reviewing pacing and student engagement.
            @endif
            The average quiz score is <strong>{{ number_format($avgScore, 1) }} points</strong>.
        </div>
    </div>

    {{-- Two-column: lesson bars + student performance --}}
    <div class="two-col">

        {{-- Lesson Completion --}}
        <div class="col-half">
            <div class="section-label">Lesson Completion Rates</div>
            @foreach($lessonCompletion as $lesson)
                <div class="lesson-row">
                    <div class="lesson-num">{{ str_pad($lesson->module_order, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="lesson-title">{{ Str::limit($lesson->title, 22) }}</div>
                    <div class="bar-wrap">
                        <div class="bar-fill" style="width: {{ $lesson->completionPct }}%"></div>
                    </div>
                    <div class="lesson-pct">{{ $lesson->completionPct }}%</div>
                </div>
            @endforeach
            @if($lessonCompletion->isEmpty())
                <p style="color:#94a3b8; font-size:10px;">No lessons found.</p>
            @endif
        </div>

        {{-- Difficulty breakdown (simple derived stat) --}}
        <div class="col-half">
            <div class="section-label">Lesson Difficulty Breakdown</div>
            @php
                $byDiff = $lessonCompletion->groupBy(fn($l) => $l->difficulty);
            @endphp
            @foreach(['beginner','intermediate','advanced'] as $diff)
                @php
                    $group = $byDiff->get($diff, collect());
                    $avgPct = $group->isNotEmpty() ? round($group->avg('completionPct')) : 0;
                    $diffColor = $diff === 'beginner' ? '#22c55e' : ($diff === 'intermediate' ? '#3b82f6' : '#ef4444');
                @endphp
                <div class="lesson-row">
                    <div style="flex:1; font-size:9.5px; font-weight:700; color:#1e293b; text-transform:capitalize;">
                        {{ ucfirst($diff) }}
                    </div>
                    <div style="font-size:9px; color:#94a3b8; margin-right:10px;">{{ $group->count() }} lesson(s)</div>
                    <div class="bar-wrap">
                        <div style="height:100%; border-radius:10px; background:{{ $diffColor }}; width:{{ $avgPct }}%"></div>
                    </div>
                    <div class="lesson-pct" style="color:{{ $diffColor }}">{{ $avgPct }}%</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Student Performance Table --}}
    <div class="section-label">Student Performance Ranking</div>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student Name</th>
                <th>Grade Level</th>
                <th>Program Type</th>
                <th>Lessons Completed</th>
                <th>Avg Quiz Score</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($studentPerformance as $i => $s)
                @php
                    $rankClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : ''));
                @endphp
                <tr>
                    <td>
                        <span class="rank-badge {{ $rankClass }}">{{ $i + 1 }}</span>
                    </td>
                    <td style="font-weight:700;">{{ $s->first_name }} {{ $s->last_name }}</td>
                    <td>{{ $s->grade_level ?? 'N/A' }}</td>
                    <td>{{ $s->program_type ?? 'N/A' }}</td>
                    <td style="font-weight:700; color:#0d326b; text-align:center;">{{ $s->completedLessons }}</td>
                    <td style="font-weight:700; color:{{ $s->avgScore >= 4 ? '#22c55e' : ($s->avgScore >= 2 ? '#f59e0b' : '#ef4444') }}; text-align:center;">
                        {{ $s->avgScore > 0 ? $s->avgScore . ' pts' : '—' }}
                    </td>
                    <td>
                        @if($s->completedLessons > 0)
                            <span style="background:#dcfce7; color:#166534; padding:2px 8px; border-radius:20px; font-size:7.5px; font-weight:700;">Active</span>
                        @else
                            <span style="background:#f1f5f9; color:#64748b; padding:2px 8px; border-radius:20px; font-size:7.5px; font-weight:700;">Not Started</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <span><span class="footer-brand">SEÑAS</span> Teacher Web Dashboard — Confidential Academic Report</span>
        <span>Generated on {{ $generatedAt }}</span>
    </div>
</div>

</body>
</html>
