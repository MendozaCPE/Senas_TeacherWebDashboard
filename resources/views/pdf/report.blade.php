<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>SEÑAS Progress Report</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    /* Uniform page margin on ALL pages (this is what actually gives page 2+
       breathing room at the top in dompdf — the ":first" pseudo-class is
       unreliable there). The header then cancels it out with a negative
       top margin so it still sits flush with the edge on page 1. */
    @page {
        margin: 30px 0 24px 0;
    }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 10px;
        color: #334155;
        background: #ffffff;
        line-height: 1.5;
    }

    /* ── HEADER (brand + stacked meta on row 1, centered title on row 2) ── */
    .header {
        background: #0d326b;
        padding: 48px 40px 16px;
        margin-top: -30px;
        color: #ffffff;
    }
    table.header-row {
        width: 100%;
        border-collapse: collapse;
    }
    table.header-row td {
        vertical-align: top;
        padding: 0;
        border: none;
    }
    table.brand-block {
        border-collapse: collapse;
    }
    table.brand-block td {
        vertical-align: middle;
        padding: 0;
        border: none;
        white-space: nowrap;
    }
    .brand-mascot-cell { padding-right: 16px; }
    .brand-mascot {
        width: 32px;
        height: 32px;
        object-fit: contain;
        margin-right: 5px;
    }
    .brand {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 1.2px;
        color: #ffffff;
    }
    .brand-sub {
        font-size: 7px;
        color: rgba(255,255,255,0.5);
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-top: 2px;
    }
    .header-meta-cell {
        text-align: right;
        font-size: 8.5px;
        color: rgba(255,255,255,0.85);
        line-height: 1.6;
    }
    .header-meta-cell strong {
        color: #ffffff;
        font-weight: 700;
    }
    .header-subrow {
        text-align: center;
        margin-top: 10px;
        font-size: 9px;
        color: rgba(255,255,255,0.75);
    }
    .header-title-inline {
        color: #ffffff;
        font-weight: 700;
        font-size: 15px;
    }
    .header-divider {
        color: rgba(255,255,255,0.3);
        margin: 0 6px;
    }

    /* ── SUMMARY STRIP — a real <table> so the 5 columns always sit in
         one row regardless of the PDF engine's flexbox support ── */
    table.summary-strip {
        width: 100%;
        border-collapse: collapse;
        border-bottom: 1px solid #e2e8f0;
        table-layout: fixed;
    }
    table.summary-strip td.summary-item {
        width: 20%;
        text-align: center;
        padding: 14px 8px;
        border-right: 1px solid #f1f5f9;
        border-bottom: none;
    }
    table.summary-strip td.summary-item.last { border-right: none; }
    .summary-val {
        font-size: 18px;
        font-weight: 700;
        color: #0d326b;
        display: block;
        letter-spacing: -0.3px;
    }
    .summary-label {
        font-size: 7.5px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 3px;
        display: block;
    }

    /* ── BODY CONTENT ── */
    .content { padding: 22px 40px 10px; }

    .section-label {
        font-size: 8px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 1.3px;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid #f1f5f9;
    }

    /* ── STUDENT-GROUPED TABLE ── */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    thead {
        background: #0d326b;
        color: #ffffff;
    }
    thead th {
        padding: 8px 12px;
        font-size: 7.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        text-align: left;
    }
    tbody td {
        padding: 7px 12px;
        font-size: 9px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    tbody tr.lesson-row:nth-child(even) { background: #fafcff; }

    /* Student divider row — groups lessons under one clean band, no repeated name per row */
    tr.student-divider-row { page-break-inside: avoid; }
    tr.student-divider-row td {
        background: #eef3fb;
        padding: 7px 12px;
        border-bottom: 1px solid #dbe6f5;
    }
    .student-divider {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .student-divider-name {
        font-size: 10px;
        font-weight: 700;
        color: #0d326b;
    }
    .student-divider-grade {
        font-size: 8px;
        font-weight: 400;
        color: #7d93b8;
        margin-left: 6px;
    }
    .student-divider-stats {
        display: flex;
        gap: 12px;
        font-size: 7.5px;
        font-weight: 700;
        color: #4a6fa5;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Status badges — navy scale only */
    .badge {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 7px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .badge-completed   { background: #0d326b; color: #ffffff; }
    .badge-in-progress { background: #dbeafe; color: #0d326b; }

    /* ── FOOTER ── */
    .footer {
        margin-top: 18px;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        font-size: 7.5px;
        color: #94a3b8;
    }
    .footer-brand { color: #0d326b; font-weight: 700; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>

{{-- ══ HEADER ══ --}}
<div class="header">
    <table class="header-row">
        <tr>
            <td style="width:1%;">
                <table class="brand-block">
                    <tr>
                        <td class="brand-mascot-cell">
                            <img src="{{ public_path('images/wavingSenya.png') }}" class="brand-mascot" alt="Senya"/>
                        </td>
                        <td>
                            <div class="brand">SEÑAS</div>
                            <div class="brand-sub">Teacher Web Dashboard</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="header-meta-cell">
                <div><strong>Generated</strong> {{ $generatedAt }}</div>
                <div><strong>Teacher</strong> {{ $teacherName }}</div>
                <div><strong>School</strong> {{ $schoolName }}</div>
            </td>
        </tr>
    </table>

    <div class="header-subrow">
        <span class="header-title-inline">Student Progress Report</span>
        <span class="header-divider">|</span>
        <span>{{ $selectedStudentName }} &middot; {{ $selectedLessonName }}</span>
    </div>
</div>

{{-- ══ SUMMARY STRIP ══ --}}
<table class="summary-strip">
    <tr>
        <td class="summary-item">
            <span class="summary-val">{{ $totalStudents }}</span>
            <span class="summary-label">Total Students</span>
        </td>
        <td class="summary-item">
            <span class="summary-val">{{ $totalProgress }}</span>
            <span class="summary-label">Progress Records</span>
        </td>
        <td class="summary-item">
            <span class="summary-val">{{ $totalCompleted }}</span>
            <span class="summary-label">Lessons Completed</span>
        </td>
        <td class="summary-item">
            <span class="summary-val">{{ $completionPct }}%</span>
            <span class="summary-label">Completion Rate</span>
        </td>
        <td class="summary-item last">
            <span class="summary-val">{{ number_format($avgScore, 1) }}</span>
            <span class="summary-label">Avg Quiz Score</span>
        </td>
    </tr>
</table>

{{-- ══ MAIN CONTENT ══ --}}
<div class="content">

    {{-- Student-grouped breakdown — one clean band per student, lessons nested under it --}}
    <div class="section-label">Student Progress Breakdown</div>

    @if($studentReports->isEmpty())
        <p style="color:#94a3b8; font-size:10px; text-align:center; padding:24px;">No records match the selected filters.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>Lesson</th>
                <th style="width:90px;">Difficulty</th>
                <th style="width:100px;">Status</th>
                <th style="width:80px;">Quiz Score</th>
                <th style="width:90px;">Last Active</th>
            </tr>
        </thead>
        <tbody>
            @foreach($studentReports as $student)
            <tr class="student-divider-row">
                <td colspan="5">
                    <div class="student-divider">
                        <div>
                            <span class="student-divider-name">{{ $student['studentName'] }}</span>
                            <span class="student-divider-grade">{{ $student['gradeLevel'] }}</span>
                        </div>
                        <div class="student-divider-stats">
                            <span>{{ $student['completedLessons'] }}/{{ $student['totalLessons'] }} Lessons</span>
                            <span>{{ $student['overallPct'] }}% Complete</span>
                            <span>{{ $student['quizzesTaken'] }} Quiz{{ $student['quizzesTaken'] === 1 ? '' : 'zes' }}</span>
                            <span>Avg {{ $student['avgScore'] }} pts</span>
                        </div>
                    </div>
                </td>
            </tr>
            @forelse($student['lessons'] as $lesson)
            <tr class="lesson-row">
                <td style="font-weight:700; color:#1e293b;">{{ $lesson['lessonTitle'] }}</td>
                <td style="text-transform:capitalize; color:#64748b;">{{ $lesson['difficulty'] }}</td>
                <td>
                    @if($lesson['completed'])
                        <span class="badge badge-completed">Completed</span>
                    @else
                        <span class="badge badge-in-progress">In Progress</span>
                    @endif
                </td>
                <td style="font-weight:700; color:#0d326b;">
                    @if($lesson['quizCompleted'])
                        {{ $lesson['quizScore'] }} pts
                    @else
                        <span style="color:#94a3b8; font-weight:400;">Pending</span>
                    @endif
                </td>
                <td style="color:#64748b;">{{ $lesson['lastAccessed'] }}</td>
            </tr>
            @empty
            <tr class="lesson-row">
                <td colspan="5" style="color:#94a3b8; font-style:italic; text-align:center;">No lesson activity recorded.</td>
            </tr>
            @endforelse
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