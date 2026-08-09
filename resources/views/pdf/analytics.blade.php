<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>SEÑAS Analytics Report</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

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

    /* ── HEADER ── */
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

    /* ── SUMMARY STRIP ── */
    table.summary-strip {
        width: 100%;
        border-collapse: collapse;
        border-bottom: 1px solid #e2e8f0;
        table-layout: fixed;
    }
    table.summary-strip td.summary-item {
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

    /* ── CONTENT ── */
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
    .section-block { margin-bottom: 26px; }

    /* ── TWO COLUMN LAYOUT ── */
    table.two-col {
        width: 100%;
        border-collapse: collapse;
    }
    table.two-col td.col {
        width: 50%;
        vertical-align: top;
        padding: 0;
    }
    table.two-col td.col.left { padding-right: 14px; }
    table.two-col td.col.right { padding-left: 14px; }

    /* ── BAR ROWS ── */
    .bar-row {
        padding: 7px 0;
        border-bottom: 1px solid #f8fafc;
    }
    .bar-row:last-child { border-bottom: none; }
    table.bar-row-table {
        width: 100%;
        border-collapse: collapse;
    }
    table.bar-row-table td {
        padding: 0;
        border: none;
        vertical-align: middle;
        font-size: 8.5px;
    }
    .bar-title {
        font-weight: 700;
        color: #1e293b;
    }
    .bar-meta {
        color: #94a3b8;
        font-size: 7.5px;
        text-align: right;
    }
    .bar-track {
        background: #eef2f7;
        border-radius: 10px;
        height: 6px;
        overflow: hidden;
        margin-top: 4px;
    }
    .bar-fill {
        height: 6px;
        border-radius: 10px;
        background: #1e4b8f;
    }

    /* ── LEGEND TABLES ── */
    table.legend-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    table.legend-table td {
        padding: 3px 0;
        font-size: 8.5px;
        color: #475569;
        border: none;
    }
    .legend-dot {
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .legend-val {
        text-align: right;
        font-weight: 700;
        color: #0d326b;
    }

    /* ── FUNNEL BAR ── */
    table.funnel-bar {
        width: 100%;
        border-collapse: collapse;
        height: 14px;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 10px;
    }
    table.funnel-bar td {
        padding: 0;
        height: 14px;
        border: none;
    }

    /* ── RANKING TABLE ── */
    table.rank-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }
    table.rank-table thead {
        background: #0d326b;
        color: #ffffff;
    }
    table.rank-table thead th {
        padding: 8px 12px;
        font-size: 7.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        text-align: left;
    }
    table.rank-table tbody td {
        padding: 7px 12px;
        font-size: 9px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    table.rank-table tbody tr:nth-child(even) {
        background: #fafcff;
    }
    .rank-badge {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        font-size: 9px;
        font-weight: 700;
        text-align: center;
        line-height: 20px;
        vertical-align: middle;
        padding: 0;
    }
    .rank-badge.gold   { background: #facc15; color: #0d326b; }
    .rank-badge.silver { background: #94a3b8; color: #ffffff; }
    .rank-badge.bronze { background: #d97706; color: #ffffff; }

    /* ── CHART WRAPPER & HTML PROGRESS BARS ── */
    .progress-chart-box {
        background: #ffffff;
        border-radius: 8px;
        padding: 14px 12px 10px;
        border: 1px solid #f1f5f9;
        margin-top: 4px;
        width: 100%;
    }
    table.progress-cols-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    table.progress-cols-table td.col-cell {
        vertical-align: bottom;
        text-align: center;
        padding: 0 3px;
        border: none;
    }
    .col-val-text {
        font-size: 8px;
        font-weight: 700;
        color: #0d326b;
        margin-bottom: 6px;
        height: 12px;
        line-height: 12px;
    }
    .col-val-text.muted {
        color: #cbd5e1;
        font-weight: 400;
    }
    .col-bar-track {
        height: 105px;
        background: #f8fafc;
        border-radius: 5px;
        position: relative;
        overflow: hidden;
        border: 1px solid #f1f5f9;
    }
    .col-bar-fill {
        width: 100%;
        background: #0d326b;
        border-radius: 3px 3px 0 0;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
    }
    .col-bar-fill.empty-bar {
        background: #e2e8f0;
        height: 2px !important;
    }
    .col-x-label {
        font-size: 7.5px;
        font-weight: 600;
        color: #64748b;
        margin-top: 6px;
        white-space: nowrap;
    }
    .page-break {
        page-break-before: always;
    }

    /* ── INSIGHT BOX ── */
    .insight-box {
        background: #f0f9ff;
        border-left: 3px solid #3b82f6;
        padding: 12px 14px;
        border-radius: 6px;
        margin-bottom: 22px;
    }
    .insight-title {
        font-size: 9.5px;
        font-weight: 700;
        color: #0d326b;
        margin-bottom: 4px;
    }
    .insight-text {
        font-size: 9px;
        color: #475569;
        line-height: 1.6;
    }

    .empty-note {
        color: #94a3b8;
        font-size: 9px;
        text-align: center;
        padding: 20px 0;
    }

    /* ── FOOTER ── */
    .footer {
        margin-top: 18px;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
        font-size: 7.5px;
        color: #94a3b8;
    }
    table.footer-row {
        width: 100%;
        border-collapse: collapse;
    }
    table.footer-row td {
        border: none;
        padding: 0;
    }
    .footer-brand { color: #0d326b; font-weight: 700; }
    .footer-right { text-align: right; }
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
        <span class="header-title-inline">Class Analytics Report</span>
        <span class="header-divider">|</span>
        <span>{{ ucfirst(request('period', 'weekly')) }} performance
        @if(request('period') === 'monthly' || request('period') === 'quarterly')
            for {{ date('F', mktime(0, 0, 0, request('month', date('n')), 1)) }}
        @endif
        {{ request('year', date('Y')) }}</span>
    </div>
</div>

{{-- ══ SUMMARY STRIP ══ --}}
<table class="summary-strip">
    <tr>
        @foreach($classSummary as $i => $stat)
        <td class="summary-item {{ $i === count($classSummary) - 1 ? 'last' : '' }}" style="width: {{ round(100 / max(count($classSummary), 1)) }}%;">
            <span class="summary-val">{{ $stat['value'] }}</span>
            <span class="summary-label">{{ $stat['title'] }}</span>
        </td>
        @endforeach
    </tr>
</table>

{{-- ══ CONTENT ══ --}}
<div class="content">

    {{-- Insight summary --}}
    <div class="insight-box">
        <div class="insight-title">Class Summary</div>
        <div class="insight-text">
            As of {{ now()->format('F d, Y') }}, your class has <strong>{{ $totalStudents ?? 0 }} enrolled students</strong>.
            The average quiz score is <strong>{{ number_format($avgQuizScore ?? 0, 1) }}%</strong> and average gesture
            mastery is <strong>{{ number_format($avgMastery ?? 0, 1) }}%</strong>. Lesson completion rate stands at
            <strong>{{ number_format($completionRate ?? 0, 1) }}%</strong>.
        </div>
    </div>

    {{-- ══ Progress Over Time (Smooth Line Chart) ══ --}}
    <div class="section-block">
        <div class="section-label">Class Progress Over Time</div>
        @php
            $chartW = 680;
            $chartH = 180;
            $padL = 36;
            $padR = 14;
            $padT = 18;
            $padB = 24;
            $plotW = $chartW - $padL - $padR;
            $plotH = $chartH - $padT - $padB;
            
            $progressItems = is_array($progressOverTime) ? $progressOverTime : (method_exists($progressOverTime, 'toArray') ? $progressOverTime->toArray() : []);
            $count = count($progressItems);

            $pts = [];
            foreach ($progressItems as $i => $item) {
                $val = max(0, min(100, (float)($item['value'] ?? 0)));
                $x = $count > 1 ? $padL + ($i / ($count - 1)) * $plotW : $padL + $plotW / 2;
                $y = $padT + $plotH - ($val / 100) * $plotH;
                $pts[] = [
                    'x' => round($x, 1),
                    'y' => round($y, 1),
                    'val' => $val,
                    'label' => $item['label'] ?? ''
                ];
            }

            // Build smooth Bezier curve path
            $linePath = '';
            if (count($pts) > 0) {
                $linePath = "M {$pts[0]['x']} {$pts[0]['y']}";
                for ($i = 0; $i < count($pts) - 1; $i++) {
                    $p0 = $pts[max(0, $i - 1)];
                    $p1 = $pts[$i];
                    $p2 = $pts[$i + 1];
                    $p3 = $pts[min(count($pts) - 1, $i + 2)];

                    $cp1x = round($p1['x'] + ($p2['x'] - $p0['x']) / 6, 1);
                    $cp1y = round($p1['y'] + ($p2['y'] - $p0['y']) / 6, 1);
                    $cp2x = round($p2['x'] - ($p3['x'] - $p1['x']) / 6, 1);
                    $cp2y = round($p2['y'] - ($p3['y'] - $p1['y']) / 6, 1);

                    $linePath .= " C {$cp1x} {$cp1y}, {$cp2x} {$cp2y}, {$p2['x']} {$p2['y']}";
                }
            }

            $firstX = count($pts) > 0 ? $pts[0]['x'] : $padL;
            $lastX = count($pts) > 0 ? $pts[count($pts) - 1]['x'] : ($padL + $plotW);
            $bottomY = round($padT + $plotH, 1);
            $areaPath = !empty($linePath) ? ($linePath . " L {$lastX} {$bottomY} L {$firstX} {$bottomY} Z") : '';

            $svgChart = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $chartW . '" height="' . $chartH . '" viewBox="0 0 ' . $chartW . ' ' . $chartH . '">';

            // Gridlines & Y-axis labels
            foreach ([0, 25, 50, 75, 100] as $gv) {
                $gy = round($padT + $plotH - ($gv / 100) * $plotH, 1);
                $svgChart .= '<line x1="' . $padL . '" y1="' . $gy . '" x2="' . ($padL + $plotW) . '" y2="' . $gy . '" stroke="#e2e8f0" stroke-width="1" stroke-dasharray="3,3"/>';
                $svgChart .= '<text x="0" y="' . ($gy + 3) . '" font-family="DejaVu Sans, Arial, sans-serif" font-size="8.5" fill="#94a3b8" font-weight="600">' . $gv . '%</text>';
            }

            // Translucent area fill
            if (!empty($areaPath)) {
                $svgChart .= '<path d="' . $areaPath . '" fill="#e0edff" opacity="0.65"/>';
            }

            // Smooth curved line
            if (!empty($linePath)) {
                $svgChart .= '<path d="' . $linePath . '" fill="none" stroke="#0d326b" stroke-width="3" stroke-linecap="round"/>';
            }

            // Plot dots & X-axis labels
            $labelStep = max(1, (int) floor(count($pts) / 10));
            foreach ($pts as $i => $p) {
                $svgChart .= '<circle cx="' . $p['x'] . '" cy="' . $p['y'] . '" r="4" fill="#0d326b" stroke="#ffffff" stroke-width="2"/>';
                if ($i % $labelStep === 0 || $i === count($pts) - 1) {
                    $svgChart .= '<text x="' . $p['x'] . '" y="' . ($chartH - 3) . '" font-family="DejaVu Sans, Arial, sans-serif" font-size="8" fill="#94a3b8" font-weight="500" text-anchor="middle">' . htmlspecialchars($p['label']) . '</text>';
                }
            }

            $svgChart .= '</svg>';
            $chartB64 = base64_encode($svgChart);
        @endphp

        @if(empty($pts) || count($pts) == 0)
            <p class="empty-note">No progress data available yet.</p>
        @else
            <div class="progress-chart-box">
                <img src="data:image/svg+xml;base64,{{ $chartB64 }}" style="width: 100%; height: auto; display: block;" alt="Class Progress Chart" />
            </div>
        @endif
    </div>

    {{-- ══ Module Difficulty Ranking + Mastery Distribution ══ --}}
    <table class="two-col section-block">
        <tr>
            <td class="col left">
                <div class="section-label">Module Difficulty Ranking</div>
                @if($lessonDifficulty->isEmpty())
                    <p class="empty-note">No lesson score data available yet.</p>
                @else
                    @foreach($lessonDifficulty as $lesson)
                        <div class="bar-row">
                            <table class="bar-row-table">
                                <tr>
                                    <td class="bar-title">{{ \Illuminate\Support\Str::limit($lesson['title'], 28) }}</td>
                                    <td class="bar-meta" style="width:60px;">{{ $lesson['avg_score'] }}%</td>
                                </tr>
                            </table>
                            <div class="bar-track"><div class="bar-fill" style="width:{{ max(4, $lesson['avg_score']) }}%;"></div></div>
                        </div>
                    @endforeach
                @endif
            </td>
            <td class="col right">
                <div class="section-label">Mastery Level Distribution</div>
                @if($masteryTotal == 0)
                    <p class="empty-note">No mastery data available yet.</p>
                @else
                    @php
                        $mColors = ['#dbeafe', '#93c5fd', '#1e4b8f', '#0d326b'];
                    @endphp
                    <table class="funnel-bar" style="margin-top:6px;">
                        <tr>
                        @foreach($masteryDistribution as $i => $seg)
                            @php $w = $seg['pct']; @endphp
                            @if($w > 0)
                            <td style="width:{{ $w }}%; background:{{ $mColors[$i % count($mColors)] }};"></td>
                            @endif
                        @endforeach
                        </tr>
                    </table>
                    <table class="legend-table">
                        @foreach($masteryDistribution as $i => $seg)
                        <tr>
                            <td><span class="legend-dot" style="background:{{ $mColors[$i % count($mColors)] }};"></span>{{ $seg['label'] }}</td>
                            <td class="legend-val" style="width:70px;">{{ $seg['count'] }} ({{ $seg['pct'] }}%)</td>
                        </tr>
                        @endforeach
                    </table>
                @endif
            </td>
        </tr>
    </table>

    {{-- ══ Student Ranking ══ --}}
    <div class="page-break"></div>
    <div class="section-block">
        <div class="section-label">Student Ranking</div>
        @if(($studentRanking ?? collect())->isEmpty())
            <p class="empty-note">No quiz attempt data available yet.</p>
        @else
        <table class="rank-table">
            <thead>
                <tr>
                    <th style="width:40px;">Rank</th>
                    <th>Student Name</th>
                    <th style="width:70px; text-align:center;">Attempts</th>
                    <th style="width:140px;">Avg Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentRanking as $i => $s)
                    @php
                        $rankClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : ''));
                        $rankNum = $i + 1;
                    @endphp
                    <tr>
                        <td style="text-align:center;">
                            <span class="rank-badge {{ $rankClass }}">{{ $rankNum }}</span>
                        </td>
                        <td style="font-weight:700; color:#0d326b;">{{ $s['name'] }}</td>
                        <td style="text-align:center;">{{ $s['attempts'] }}</td>
                        <td>
                            <table class="bar-row-table" style="width:100%;">
                                <tr>
                                    <td style="width:60px;"><div class="bar-track"><div class="bar-fill" style="width:{{ max(4, $s['avg_score']) }}%;"></div></div></td>
                                    <td style="width:34px; text-align:right; font-weight:700; color:#0d326b; padding-left:8px;">{{ $s['avg_score'] }}%</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- ══ Completion Funnel + Score Distribution ══ --}}
    <table class="two-col section-block">
        <tr>
            <td class="col left">
                <div class="section-label">Completion Funnel</div>
                @if($completionTotal == 0)
                    <p class="empty-note">No lesson assignments recorded yet.</p>
                @else
                    @php
                        $funnelColors = ['#dbeafe', '#93c5fd', '#3b82f6', '#1e4b8f', '#0d326b'];
                    @endphp
                    <table class="funnel-bar">
                        <tr>
                        @foreach($completionFunnel as $i => $step)
                            @php $w = $completionTotal > 0 ? max(2, round(($step['count'] / $completionTotal) * 100)) : 0; @endphp
                            @if($w > 0)
                            <td style="width:{{ $w }}%; background:{{ $funnelColors[$i % count($funnelColors)] }};"></td>
                            @endif
                        @endforeach
                        </tr>
                    </table>
                    <table class="legend-table">
                        @foreach($completionFunnel as $i => $step)
                        <tr>
                            <td><span class="legend-dot" style="background:{{ $funnelColors[$i % count($funnelColors)] }};"></span>{{ $step['label'] }}</td>
                            <td class="legend-val" style="width:50px;">{{ $step['count'] }}</td>
                        </tr>
                        @endforeach
                    </table>
                @endif
            </td>
            <td class="col right">
                <div class="section-label">Score Distribution</div>
                @php $scoreTotal = collect($scoreBuckets)->sum('count'); @endphp
                @if($scoreTotal == 0)
                    <p class="empty-note">No quiz attempt data available yet.</p>
                @else
                    @php
                        $sColors = ['#ef4444', '#f59e0b', '#3b82f6', '#1e4b8f', '#0d326b'];
                    @endphp
                    <table class="funnel-bar" style="margin-top:6px;">
                        <tr>
                        @foreach($scoreBuckets as $i => $bucket)
                            @php $pct = $scoreTotal > 0 ? round(($bucket['count'] / $scoreTotal) * 100) : 0; @endphp
                            @if($pct > 0)
                            <td style="width:{{ $pct }}%; background:{{ $sColors[$i % count($sColors)] }};"></td>
                            @endif
                        @endforeach
                        </tr>
                    </table>
                    <table class="legend-table">
                        @foreach($scoreBuckets as $i => $bucket)
                        @php $pct = $scoreTotal > 0 ? round(($bucket['count'] / $scoreTotal) * 100) : 0; @endphp
                        <tr>
                            <td><span class="legend-dot" style="background:{{ $sColors[$i % count($sColors)] }};"></span>{{ $bucket['label'] }}%</td>
                            <td class="legend-val" style="width:70px;">{{ $bucket['count'] }} ({{ $pct }}%)</td>
                        </tr>
                        @endforeach
                    </table>
                @endif
            </td>
        </tr>
    </table>

    {{-- ══ DEDICATED GESTURE PERFORMANCE ANALYTICS (STRICTLY FROM gesture_performances) ══ --}}
    <div class="page-break"></div>
    <div class="section-block">
        <div class="section-label">Gesture Performance Analytics (Source: gesture_performances)</div>
        
        <table class="two-col section-block">
            <tr>
                <td class="col left">
                    <div style="font-weight: 700; color: #0d326b; margin-bottom: 6px;">Best-Performing Gestures</div>
                    @if(empty($topPerformingGestures) || count($topPerformingGestures) === 0)
                        <p class="empty-note">No gesture performance records available.</p>
                    @else
                        @foreach($topPerformingGestures as $g)
                            <div class="bar-row">
                                <table class="bar-row-table">
                                    <tr>
                                        <td class="bar-title">{{ \Illuminate\Support\Str::limit($g['gesture_name'], 24) }}</td>
                                        <td class="bar-meta" style="width:70px;">{{ number_format($g['accuracy'], 1) }}% ({{ $g['successful_attempts'] }}/{{ $g['attempts'] }})</td>
                                    </tr>
                                </table>
                                <div class="bar-track"><div class="bar-fill" style="width:{{ min(100, max(4, $g['accuracy'])) }}%; background: #10b981;"></div></div>
                            </div>
                        @endforeach
                    @endif
                </td>
                <td class="col right">
                    <div style="font-weight: 700; color: #0d326b; margin-bottom: 6px;">Lowest-Performing / Struggling Gestures</div>
                    @if(empty($lowestPerformingGestures) || count($lowestPerformingGestures) === 0)
                        <p class="empty-note">No gesture performance records available.</p>
                    @else
                        @foreach($lowestPerformingGestures as $g)
                            <div class="bar-row">
                                <table class="bar-row-table">
                                    <tr>
                                        <td class="bar-title">{{ \Illuminate\Support\Str::limit($g['gesture_name'], 24) }}</td>
                                        <td class="bar-meta" style="width:70px;">{{ number_format($g['accuracy'], 1) }}% ({{ $g['successful_attempts'] }}/{{ $g['attempts'] }})</td>
                                    </tr>
                                </table>
                                <div class="bar-track"><div class="bar-fill" style="width:{{ min(100, max(4, $g['accuracy'])) }}%; background: #ef4444;"></div></div>
                            </div>
                        @endforeach
                    @endif
                </td>
            </tr>
        </table>

    </div>

    {{-- ══ Footer ══ --}}
    <div class="footer">
        <table class="footer-row">
            <tr>
                <td><span class="footer-brand">SEÑAS</span> Teacher Web Dashboard — Confidential Academic Report</td>
                <td class="footer-right">Generated on {{ $generatedAt }}</td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>