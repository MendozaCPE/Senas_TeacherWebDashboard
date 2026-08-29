<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>SEÑAS Analytics Report</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    @page {
        margin: 28px 0 20px 0;
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
        background: linear-gradient(135deg, #071c3f 0%, #0d326b 60%, #1e4b8f 100%);
        padding: 44px 40px 14px;
        margin-top: -28px;
        color: #ffffff;
    }
    table.header-row { width: 100%; border-collapse: collapse; }
    table.header-row td { vertical-align: top; padding: 0; border: none; }
    table.brand-block { border-collapse: collapse; }
    table.brand-block td { vertical-align: middle; padding: 0; border: none; white-space: nowrap; }
    .brand-mascot-cell { padding-right: 14px; }
    .brand-mascot { width: 34px; height: 34px; object-fit: contain; }
    .brand { font-size: 15px; font-weight: 700; letter-spacing: 1.5px; color: #ffffff; }
    .brand-sub { font-size: 7px; color: rgba(255,255,255,0.5); letter-spacing: 1px; text-transform: uppercase; margin-top: 2px; }
    .header-meta-cell { text-align: right; font-size: 8.5px; color: rgba(255,255,255,0.85); line-height: 1.7; }
    .header-meta-cell strong { color: #ffffff; font-weight: 700; }
    .header-subrow { text-align: center; margin-top: 10px; font-size: 9px; color: rgba(255,255,255,0.75); }
    .header-title-inline { color: #ffffff; font-weight: 700; font-size: 15px; }
    .header-divider { color: rgba(255,255,255,0.3); margin: 0 6px; }

    /* ── SUMMARY STRIP ── */
    table.summary-strip {
        width: 100%;
        border-collapse: collapse;
        border-bottom: 2px solid #e2e8f0;
        table-layout: fixed;
    }
    table.summary-strip td.summary-item {
        text-align: center;
        padding: 12px 6px;
        border-right: 1px solid #f1f5f9;
        border-bottom: none;
    }
    table.summary-strip td.summary-item.last { border-right: none; }
    .summary-val { font-size: 18px; font-weight: 700; color: #0d326b; display: block; letter-spacing: -0.3px; }
    .summary-label { font-size: 7px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 2px; display: block; }

    /* ── CONTENT ── */
    .content { padding: 20px 40px 10px; }

    .section-label {
        font-size: 8px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 1.3px;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 2px solid #f1f5f9;
    }
    .section-block { margin-bottom: 24px; }

    /* ── INSIGHT BOX ── */
    .insight-box {
        background: #f0f9ff;
        border-left: 3px solid #3b82f6;
        padding: 11px 14px;
        border-radius: 6px;
        margin-bottom: 20px;
    }
    .insight-title { font-size: 9.5px; font-weight: 700; color: #0d326b; margin-bottom: 4px; }
    .insight-text { font-size: 8.5px; color: #475569; line-height: 1.6; }

    /* ── TWO-COLUMN LAYOUT ── */
    table.two-col { width: 100%; border-collapse: collapse; }
    table.two-col td.col { width: 50%; vertical-align: top; padding: 0; }
    table.two-col td.col.left { padding-right: 12px; }
    table.two-col td.col.right { padding-left: 12px; }

    /* ── BAR ROWS ── */
    .bar-row { padding: 6px 0; border-bottom: 1px solid #f8fafc; }
    .bar-row:last-child { border-bottom: none; }
    table.bar-row-table { width: 100%; border-collapse: collapse; }
    table.bar-row-table td { padding: 0; border: none; vertical-align: middle; font-size: 8.5px; }
    .bar-title { font-weight: 700; color: #1e293b; }
    .bar-meta { color: #64748b; font-size: 7.5px; text-align: right; }
    .bar-track { background: #eef2f7; border-radius: 10px; height: 6px; overflow: hidden; margin-top: 3px; }
    .bar-fill { height: 6px; border-radius: 10px; }

    /* ── LEGEND TABLE ── */
    table.legend-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    table.legend-table td { padding: 3px 0; font-size: 8.5px; color: #475569; border: none; }
    .legend-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 6px; }
    .legend-val { text-align: right; font-weight: 700; color: #0d326b; }

    /* ── FUNNEL BAR ── */
    table.funnel-bar { width: 100%; border-collapse: collapse; height: 14px; border-radius: 8px; overflow: hidden; margin-bottom: 8px; }
    table.funnel-bar td { padding: 0; height: 14px; border: none; }

    /* ── STUDENT RANKING TABLE ── */
    table.rank-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    table.rank-table thead { background: #0d326b; color: #ffffff; }
    table.rank-table thead th { padding: 7px 10px; font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; text-align: left; }
    table.rank-table tbody td { padding: 6px 10px; font-size: 9px; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    table.rank-table tbody tr:nth-child(even) { background: #fafcff; }
    .rank-badge { display: inline-block; width: 20px; height: 20px; border-radius: 50%; background: #e2e8f0; color: #64748b; font-size: 9px; font-weight: 700; text-align: center; line-height: 20px; vertical-align: middle; }
    .rank-badge.gold   { background: #facc15; color: #0d326b; }
    .rank-badge.silver { background: #94a3b8; color: #ffffff; }
    .rank-badge.bronze { background: #d97706; color: #ffffff; }

    /* ── SIGN RANKING TABLE ── */
    table.sign-table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 8.5px; }
    table.sign-table thead { background: #0d326b; color: #fff; }
    table.sign-table thead th { padding: 6px 8px; font-size: 7px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
    table.sign-table tbody td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; }
    table.sign-table tbody tr:nth-child(even) { background: #fafcff; }
    .sign-acc-bar-track { background: #eef2f7; border-radius: 4px; height: 5px; overflow: hidden; width: 50px; display: inline-block; vertical-align: middle; }
    .sign-acc-bar-fill { height: 5px; border-radius: 4px; display: inline-block; }

    /* ── SIGN SECTION HEADER ── */
    .sign-section-header {
        background: #0d326b;
        color: #ffffff;
        padding: 8px 12px;
        border-radius: 6px 6px 0 0;
        margin-top: 14px;
    }
    .sign-section-header-title { font-size: 10px; font-weight: 700; }
    .sign-section-header-sub { font-size: 8px; color: rgba(255,255,255,0.7); margin-top: 2px; }
    .sign-section-body { border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 6px 6px; padding: 10px 12px; background: #ffffff; }

    table.sign-grid { width: 100%; border-collapse: collapse; }
    table.sign-grid td { vertical-align: top; padding: 0; border: none; }
    table.sign-grid td.sg-col-left { width: 50%; padding-right: 10px; }
    table.sign-grid td.sg-col-right { width: 50%; padding-left: 10px; border-left: 1px solid #f1f5f9; }

    /* ── STUDENT NAME CHIP ── */
    .best-chip { background: #dcfce7; border: 1px solid #86efac; padding: 3px 7px; border-radius: 8px; display: inline-block; font-size: 8px; font-weight: 700; color: #166534; }
    .struggling-chip { background: #fee2e2; border: 1px solid #fca5a5; padding: 3px 7px; border-radius: 8px; display: inline-block; font-size: 8px; font-weight: 700; color: #991b1b; }

    /* ── STATUS PILL ── */
    .status-mastered { background: #dcfce7; color: #166534; padding: 2px 7px; border-radius: 8px; font-size: 7.5px; font-weight: 700; display: inline-block; }
    .status-practice { background: #fef3c7; color: #92400e; padding: 2px 7px; border-radius: 8px; font-size: 7.5px; font-weight: 700; display: inline-block; }

    /* ── SVG CHART WRAPPER ── */
    .progress-chart-box { background: #ffffff; border-radius: 8px; padding: 14px 12px 10px; border: 1px solid #f1f5f9; margin-top: 4px; width: 100%; }

    /* ── PAGE BREAK ── */
    .page-break { page-break-before: always; }

    /* ── EMPTY ── */
    .empty-note { color: #94a3b8; font-size: 9px; text-align: center; padding: 18px 0; }

    /* ── FOOTER ── */
    .footer { margin-top: 18px; padding-top: 12px; border-top: 1px solid #f1f5f9; font-size: 7.5px; color: #94a3b8; }
    table.footer-row { width: 100%; border-collapse: collapse; }
    table.footer-row td { border: none; padding: 0; }
    .footer-brand { color: #0d326b; font-weight: 700; }
    .footer-right { text-align: right; }

    /* ── PAGE DIVIDER ── */
    .page-section-divider { border: none; border-top: 2px solid #e2e8f0; margin: 4px 0 20px 0; }
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
                <div><strong>Generated:</strong> {{ $generatedAt }}</div>
                <div><strong>Teacher:</strong> {{ $teacherName }}</div>
                <div><strong>School:</strong> {{ $schoolName }}</div>
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

    {{-- Class Summary Insight --}}
    <div class="insight-box">
        <div class="insight-title">Class Summary</div>
        <div class="insight-text">
            As of {{ now()->format('F d, Y') }}, your class has <strong>{{ $totalStudents ?? 0 }} enrolled students</strong>.
            The average quiz score is <strong>{{ number_format($avgQuizScore ?? 0, 1) }}%</strong> and average gesture
            mastery is <strong>{{ number_format($avgMastery ?? 0, 1) }}%</strong>. Lesson completion rate stands at
            <strong>{{ number_format($completionRate ?? 0, 1) }}%</strong>.
        </div>
    </div>

    {{-- ══ Progress Over Time ══ --}}
    <div class="section-block">
        <div class="section-label">Class Progress Over Time</div>
        @php
            $chartW = 680; $chartH = 170;
            $padL = 36; $padR = 14; $padT = 16; $padB = 24;
            $plotW = $chartW - $padL - $padR;
            $plotH = $chartH - $padT - $padB;
            $progressItems = is_array($progressOverTime) ? $progressOverTime : (method_exists($progressOverTime, 'toArray') ? $progressOverTime->toArray() : []);
            $count = count($progressItems);
            $pts = [];
            foreach ($progressItems as $i => $item) {
                $val = max(0, min(100, (float)($item['value'] ?? 0)));
                $x = $count > 1 ? $padL + ($i / ($count - 1)) * $plotW : $padL + $plotW / 2;
                $y = $padT + $plotH - ($val / 100) * $plotH;
                $pts[] = ['x' => round($x, 1), 'y' => round($y, 1), 'val' => $val, 'label' => $item['label'] ?? ''];
            }
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
            $lastX  = count($pts) > 0 ? $pts[count($pts)-1]['x'] : ($padL + $plotW);
            $bottomY = round($padT + $plotH, 1);
            $areaPath = !empty($linePath) ? ($linePath . " L {$lastX} {$bottomY} L {$firstX} {$bottomY} Z") : '';
            $svgChart  = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $chartW . '" height="' . $chartH . '" viewBox="0 0 ' . $chartW . ' ' . $chartH . '">';
            foreach ([0, 25, 50, 75, 100] as $gv) {
                $gy = round($padT + $plotH - ($gv / 100) * $plotH, 1);
                $svgChart .= '<line x1="' . $padL . '" y1="' . $gy . '" x2="' . ($padL + $plotW) . '" y2="' . $gy . '" stroke="#e2e8f0" stroke-width="1" stroke-dasharray="3,3"/>';
                $svgChart .= '<text x="0" y="' . ($gy + 3) . '" font-family="DejaVu Sans, Arial, sans-serif" font-size="8.5" fill="#94a3b8" font-weight="600">' . $gv . '%</text>';
            }
            if (!empty($areaPath)) {
                $svgChart .= '<path d="' . $areaPath . '" fill="#dbeafe" opacity="0.55"/>';
            }
            if (!empty($linePath)) {
                $svgChart .= '<path d="' . $linePath . '" fill="none" stroke="#0d326b" stroke-width="2.5" stroke-linecap="round"/>';
            }
            $labelStep = max(1, (int) floor(count($pts) / 10));
            foreach ($pts as $i => $p) {
                $svgChart .= '<circle cx="' . $p['x'] . '" cy="' . $p['y'] . '" r="3.5" fill="#0d326b" stroke="#ffffff" stroke-width="1.5"/>';
                if ($i % $labelStep === 0 || $i === count($pts) - 1) {
                    $svgChart .= '<text x="' . $p['x'] . '" y="' . ($chartH - 3) . '" font-family="DejaVu Sans, Arial, sans-serif" font-size="8" fill="#94a3b8" font-weight="500" text-anchor="middle">' . htmlspecialchars($p['label']) . '</text>';
                }
            }
            $svgChart .= '</svg>';
            $chartB64 = base64_encode($svgChart);
        @endphp
        @if(empty($pts))
            <p class="empty-note">No progress data available yet.</p>
        @else
            <div class="progress-chart-box">
                <img src="data:image/svg+xml;base64,{{ $chartB64 }}" style="width:100%; height:auto; display:block;" alt="Class Progress Chart"/>
            </div>
        @endif
    </div>

    {{-- ══ Module Difficulty + Mastery Distribution ══ --}}
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
                                <td class="bar-title">{{ \Illuminate\Support\Str::limit($lesson['title'], 30) }}</td>
                                <td class="bar-meta" style="width:55px;">{{ $lesson['avg_score'] }}%</td>
                            </tr>
                        </table>
                        <div class="bar-track"><div class="bar-fill" style="width:{{ max(4, $lesson['avg_score']) }}%; background:#1e4b8f;"></div></div>
                    </div>
                    @endforeach
                @endif
            </td>
            <td class="col right">
                <div class="section-label">Mastery Level Distribution</div>
                @if($masteryTotal == 0)
                    <p class="empty-note">No mastery data available yet.</p>
                @else
                    @php $mColors = ['#dbeafe', '#93c5fd', '#1e4b8f', '#0d326b']; @endphp
                    <table class="funnel-bar" style="margin-top:4px;">
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
                            <td class="legend-val" style="width:80px;">{{ $seg['count'] }} ({{ $seg['pct'] }}%)</td>
                        </tr>
                        @endforeach
                    </table>
                @endif
            </td>
        </tr>
    </table>

    {{-- ══ Student Quiz Ranking ══ --}}
    <div class="page-break"></div>
    <div class="section-block">
        <div class="section-label">Student Quiz Ranking</div>
        @if(($studentRanking ?? collect())->isEmpty())
            <p class="empty-note">No quiz attempt data available yet.</p>
        @else
        <table class="rank-table">
            <thead>
                <tr>
                    <th style="width:38px;">Rank</th>
                    <th>Student Name</th>
                    <th style="width:65px; text-align:center;">Attempts</th>
                    <th style="width:130px;">Avg Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentRanking as $i => $s)
                @php
                    $rankClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : ''));
                @endphp
                <tr>
                    <td style="text-align:center;"><span class="rank-badge {{ $rankClass }}">{{ $i + 1 }}</span></td>
                    <td style="font-weight:700; color:#0d326b;">{{ $s['name'] }}</td>
                    <td style="text-align:center;">{{ $s['attempts'] }}</td>
                    <td>
                        <table class="bar-row-table" style="width:100%;">
                            <tr>
                                <td style="width:55px;"><div class="bar-track"><div class="bar-fill" style="width:{{ max(4, $s['avg_score']) }}%; background:#0d326b;"></div></div></td>
                                <td style="width:38px; text-align:right; font-weight:700; color:#0d326b; padding-left:8px;">{{ $s['avg_score'] }}%</td>
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
                <div class="section-label">Lesson Completion Funnel</div>
                @if($completionTotal == 0)
                    <p class="empty-note">No lesson assignments recorded yet.</p>
                @else
                    @php $funnelColors = ['#dbeafe', '#93c5fd', '#3b82f6', '#1e4b8f', '#0d326b']; @endphp
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
                <div class="section-label">Quiz Score Distribution</div>
                @php $scoreTotal = collect($scoreBuckets)->sum('count'); @endphp
                @if($scoreTotal == 0)
                    <p class="empty-note">No quiz attempt data available yet.</p>
                @else
                    @php $sColors = ['#ef4444', '#f59e0b', '#3b82f6', '#1e4b8f', '#0d326b']; @endphp
                    <table class="funnel-bar" style="margin-top:4px;">
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

    {{-- ══ GESTURE PERFORMANCE ANALYTICS ══ --}}
    <div class="page-break"></div>

    {{-- Section Header --}}
    <div class="section-block">
        <div class="section-label">Gesture Performance Analytics</div>

        {{-- High-level gesture summary --}}
        @php
            $overallAccuracy = $gesturePerformanceOverview['overall_accuracy'] ?? 0;
            $totalGestureAttempts = $gesturePerformanceOverview['total_attempts'] ?? 0;
        @endphp
        <div class="insight-box">
            <div class="insight-title">Gesture Overview</div>
            <div class="insight-text">
                Your class recorded <strong>{{ $totalGestureAttempts }} total gesture attempts</strong> in this period
                with an overall class accuracy of <strong>{{ $overallAccuracy }}%</strong>.
                <strong>{{ $masteredSignsCount }} sign(s)</strong> are mastered by the majority of students,
                while <strong>{{ $lowMasterySignsCount }} sign(s)</strong> still need class-wide practice.
            </div>
        </div>

        @if(empty($signsBreakdown) || count($signsBreakdown) === 0)
            <p class="empty-note">No student gesture practices recorded in this time period.</p>
        @else

        {{-- ── Top 5 Best & Worst Signs Overview ── --}}
        <table class="two-col" style="margin-bottom:18px;">
            <tr>
                <td class="col left">
                    <div style="font-size:8px; font-weight:700; color:#166534; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; padding-bottom:5px; border-bottom:2px solid #dcfce7;">
                        ✓ Best-Performing Signs (Top 5)
                    </div>
                    @foreach(collect($signsBreakdown)->sortByDesc('accuracy')->take(5) as $g)
                    <div class="bar-row">
                        <table class="bar-row-table">
                            <tr>
                                <td class="bar-title">{{ \Illuminate\Support\Str::limit($g['gesture_name'], 22) }}</td>
                                <td class="bar-meta" style="width:90px; text-align:right;">
                                    {{ number_format($g['accuracy'], 1) }}%
                                    ({{ $g['successful_attempts'] }}/{{ $g['total_attempts'] }})
                                </td>
                            </tr>
                        </table>
                        <div class="bar-track"><div class="bar-fill" style="width:{{ min(100, max(4, $g['accuracy'])) }}%; background:#10b981;"></div></div>
                        @if(!empty($g['best_student']))
                        <div style="font-size:7px; color:#166534; margin-top:3px;">
                            ⭐ Best: <strong>{{ $g['best_student']['name'] }}</strong> — {{ $g['best_student']['accuracy'] }}%
                        </div>
                        @endif
                    </div>
                    @endforeach
                </td>
                <td class="col right">
                    <div style="font-size:8px; font-weight:700; color:#991b1b; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; padding-bottom:5px; border-bottom:2px solid #fee2e2;">
                        ✗ Signs Needing Most Practice (Bottom 5)
                    </div>
                    @foreach(collect($signsBreakdown)->sortBy('accuracy')->take(5) as $g)
                    <div class="bar-row">
                        <table class="bar-row-table">
                            <tr>
                                <td class="bar-title">{{ \Illuminate\Support\Str::limit($g['gesture_name'], 22) }}</td>
                                <td class="bar-meta" style="width:90px; text-align:right;">
                                    {{ number_format($g['accuracy'], 1) }}%
                                    ({{ $g['successful_attempts'] }}/{{ $g['total_attempts'] }})
                                </td>
                            </tr>
                        </table>
                        <div class="bar-track"><div class="bar-fill" style="width:{{ min(100, max(4, $g['accuracy'])) }}%; background:#ef4444;"></div></div>
                        @if(!empty($g['struggling_student']))
                        <div style="font-size:7px; color:#991b1b; margin-top:3px;">
                            ⚠ Struggling: <strong>{{ $g['struggling_student']['name'] }}</strong> — {{ $g['struggling_student']['accuracy'] }}%
                        </div>
                        @endif
                    </div>
                    @endforeach
                </td>
            </tr>
        </table>

        {{-- ── Per-Sign Student Rankings ── --}}
        <div class="page-break"></div>
        <div style="font-size:8px; font-weight:700; color:#0d326b; text-transform:uppercase; letter-spacing:1.2px; margin-bottom:12px; padding-bottom:6px; border-bottom:2px solid #e2e8f0;">
            Per-Sign Student Breakdown &amp; Rankings
        </div>

        @foreach($signsBreakdown as $sign)
        @php
            $isMastered = $sign['status'] === 'mastered';
            $headerBg = $isMastered ? '#166534' : '#92400e';
            $statusClass = $isMastered ? 'status-mastered' : 'status-practice';
            $statusLabel = $isMastered ? 'Mastered by Majority' : 'Needs Practice';
            $barColor = $isMastered ? '#10b981' : '#f59e0b';
        @endphp

        {{-- Sign Block --}}
        <div style="margin-bottom:14px; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">

            {{-- Sign Header --}}
            <table style="width:100%; border-collapse:collapse; background:{{ $isMastered ? '#f0fdf4' : '#fffbeb' }}; border-bottom:1px solid #e2e8f0;">
                <tr>
                    <td style="padding:8px 12px; vertical-align:middle;">
                        <table style="border-collapse:collapse; width:100%;">
                            <tr>
                                <td style="vertical-align:middle;">
                                    <span style="font-size:11px; font-weight:700; color:#0d326b;">Sign: {{ $sign['gesture_name'] }}</span>
                                    &nbsp;&nbsp;
                                    <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td style="text-align:right; vertical-align:middle; white-space:nowrap;">
                                    <span style="font-size:11px; font-weight:700; color:{{ $isMastered ? '#166534' : '#92400e' }};">{{ $sign['accuracy'] }}% class accuracy</span>
                                    <span style="font-size:8px; color:#64748b; margin-left:6px;">{{ $sign['total_attempts'] }} attempts · {{ $sign['successful_attempts'] }} correct · {{ $sign['wrong_attempts'] }} wrong</span>
                                </td>
                            </tr>
                        </table>
                        <div style="margin-top:5px; background:#e2e8f0; border-radius:6px; height:5px; overflow:hidden;">
                            <div style="width:{{ min(100, max(2, $sign['accuracy'])) }}%; height:5px; background:{{ $barColor }}; border-radius:6px;"></div>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Best Student + Struggling Student --}}
            @if(!empty($sign['best_student']) || !empty($sign['struggling_student']))
            <table style="width:100%; border-collapse:collapse; background:#ffffff; border-bottom:1px solid #f1f5f9;">
                <tr>
                    <td style="width:50%; padding:7px 12px; vertical-align:top; border-right:1px solid #f1f5f9;">
                        <div style="font-size:7.5px; font-weight:700; color:#166534; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">⭐ Best Performing Student</div>
                        @if(!empty($sign['best_student']))
                        <span class="best-chip">{{ $sign['best_student']['name'] }}</span>
                        <span style="font-size:8px; color:#64748b; margin-left:6px;">{{ $sign['best_student']['accuracy'] }}% accuracy · {{ $sign['best_student']['attempts'] }} attempts</span>
                        @else
                        <span style="font-size:8px; color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td style="width:50%; padding:7px 12px; vertical-align:top;">
                        <div style="font-size:7.5px; font-weight:700; color:#991b1b; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">⚠ Struggling Student</div>
                        @if(!empty($sign['struggling_student']))
                        <span class="struggling-chip">{{ $sign['struggling_student']['name'] }}</span>
                        <span style="font-size:8px; color:#64748b; margin-left:6px;">{{ $sign['struggling_student']['accuracy'] }}% accuracy · {{ $sign['struggling_student']['wrong'] }} mistakes</span>
                        @else
                        <span style="font-size:8px; color:#94a3b8;">No struggling students identified</span>
                        @endif
                    </td>
                </tr>
            </table>
            @endif

            {{-- Full Student Ranking for this sign --}}
            @if(!empty($sign['students_ranking']) && count($sign['students_ranking']) > 0)
            <table class="sign-table">
                <thead>
                    <tr>
                        <th style="width:32px;">Rank</th>
                        <th>Student</th>
                        <th style="width:60px; text-align:center;">Attempts</th>
                        <th style="width:55px; text-align:center;">Correct</th>
                        <th style="width:55px; text-align:center;">Wrong</th>
                        <th style="width:100px;">Accuracy</th>
                        <th style="width:60px; text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sign['students_ranking'] as $sr)
                    @php
                        $srRankClass = $sr['rank'] === 1 ? 'gold' : ($sr['rank'] === 2 ? 'silver' : ($sr['rank'] === 3 ? 'bronze' : ''));
                        $srBarColor = $sr['is_mastered'] ? '#10b981' : ($sr['accuracy'] >= 50 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <tr>
                        <td style="text-align:center;"><span class="rank-badge {{ $srRankClass }}" style="width:16px; height:16px; font-size:8px; line-height:16px;">{{ $sr['rank'] }}</span></td>
                        <td style="font-weight:700; color:#0d326b;">{{ $sr['name'] }}</td>
                        <td style="text-align:center; color:#475569;">{{ $sr['attempts'] }}</td>
                        <td style="text-align:center; color:#166534; font-weight:700;">{{ $sr['successful_attempts'] }}</td>
                        <td style="text-align:center; color:#991b1b; font-weight:700;">{{ $sr['wrong_attempts'] }}</td>
                        <td>
                            <table style="border-collapse:collapse; width:100%;">
                                <tr>
                                    <td>
                                        <div style="background:#eef2f7; border-radius:4px; height:5px; overflow:hidden; width:48px; display:inline-block; vertical-align:middle;">
                                            <div style="width:{{ min(100, max(2, $sr['accuracy'])) }}%; height:5px; background:{{ $srBarColor }}; border-radius:4px;"></div>
                                        </div>
                                    </td>
                                    <td style="padding-left:5px; font-weight:700; color:#0d326b; font-size:8.5px; white-space:nowrap;">{{ $sr['accuracy'] }}%</td>
                                </tr>
                            </table>
                        </td>
                        <td style="text-align:center;">
                            @if($sr['is_mastered'])
                                <span class="status-mastered">Mastered</span>
                            @else
                                <span class="status-practice">Practicing</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="padding:10px 12px; font-size:8.5px; color:#94a3b8;">No individual student data for this sign.</div>
            @endif

        </div>
        @endforeach

        @endif {{-- end if signsBreakdown --}}
    </div>

    {{-- ══ Footer ══ --}}
    <div class="footer">
        <table class="footer-row">
            <tr>
                <td><span class="footer-brand">SEÑAS</span> Teacher Web Dashboard — Confidential Academic Report</td>
                <td style="text-align:right;">Generated on {{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>