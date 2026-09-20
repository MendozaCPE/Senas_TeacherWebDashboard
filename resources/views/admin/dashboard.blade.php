@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('content')

{{-- ═══════════════════════════════════════════════════════════════════════
     SKELETON — mirrors the real dashboard layout.
     ═══════════════════════════════════════════════════════════════════════ --}}
<div id="page-skeleton" class="flex flex-col gap-5 w-full pt-4" aria-hidden="true">

    {{-- Welcome banner --}}
    <div class="skeleton skeleton-card w-full" style="min-height:130px;border-radius:28px;"></div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @for($i=0;$i<4;$i++)
        <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col gap-3 min-h-[130px]">
            <div class="flex items-center justify-between">
                <div class="skeleton h-3 rounded w-28"></div>
                <div class="skeleton w-10 h-10 rounded-xl"></div>
            </div>
            <div class="skeleton h-9 rounded w-16 mt-1"></div>
            <div class="skeleton h-3 rounded w-24"></div>
            <div class="skeleton rounded h-[38px] w-full mt-auto opacity-60"></div>
        </div>
        @endfor
    </div>

    {{-- Main grid: chart (2/3) + side stats (1/3) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
        <div class="lg:col-span-2 bg-white rounded-[22px] shadow-sm border border-slate-100 p-6 flex flex-col gap-4">
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <div class="flex flex-col gap-2"><div class="skeleton h-5 rounded w-36"></div><div class="skeleton h-3 rounded w-56"></div></div>
                <div class="skeleton h-5 rounded w-40"></div>
            </div>
            <div class="skeleton rounded-2xl w-full" style="padding-bottom:40%;"></div>
        </div>
        <div class="flex flex-col gap-4">
            <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-5 flex flex-col gap-4">
                <div class="skeleton h-4 rounded w-32"></div>
                @for($i=0;$i<2;$i++)
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <div class="skeleton h-3 rounded w-24"></div>
                        <div class="skeleton h-5 rounded w-8"></div>
                    </div>
                    <div class="skeleton h-2 rounded-full w-full"></div>
                </div>
                @endfor
            </div>
            <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-5 flex flex-col gap-4">
                <div class="skeleton h-4 rounded w-32"></div>
                <div class="grid grid-cols-2 gap-3">
                    @for($i=0;$i<4;$i++)
                    <div class="bg-[#f8fafc] rounded-2xl p-3 flex flex-col items-center gap-2">
                        <div class="skeleton w-6 h-6 rounded skeleton-circle"></div>
                        <div class="skeleton h-5 rounded w-12"></div>
                        <div class="skeleton h-2 rounded w-16"></div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom grid: 3 cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        @for($i=0;$i<3;$i++)
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
                <div class="flex flex-col gap-2"><div class="skeleton h-4 rounded w-28"></div><div class="skeleton h-3 rounded w-20"></div></div>
                <div class="skeleton h-3 rounded w-16"></div>
            </div>
            @for($j=0;$j<4;$j++)
            <div class="flex items-center gap-3 px-6 py-3.5">
                <div class="skeleton skeleton-circle w-9 h-9 flex-shrink-0"></div>
                <div class="flex-1 flex flex-col gap-2">
                    <div class="skeleton h-3 rounded w-3/4"></div>
                    <div class="skeleton h-2 rounded w-1/2"></div>
                </div>
            </div>
            @endfor
        </div>
        @endfor
    </div>

</div>
{{-- ═══════════════════════════════════════════════════════════════════ --}}

@php
/* ── Inline sparkline helper with interactive points and tooltips ──── */
$spark = function (array $data, string $color, string $metric = '', string $unit = '', array $dates = [], string $id = '', bool $isDarkCard = false, int $width = 240, int $height = 38): string {
    if (count($data) < 2) {
        $data = array_fill(0, 7, $data[0] ?? 0);
    }
    $count = count($data);
    if (empty($dates) || count($dates) < $count) {
        $dates = [];
        for ($i = $count - 1; $i >= 0; $i--) {
            $d = \Carbon\Carbon::now()->subDays($i);
            $dates[] = [
                'day'   => $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : $d->format('l')),
                'date'  => $d->format('M j, Y'),
                'short' => $d->format('M j'),
            ];
        }
    }

    $max = max($data);
    $min = min($data);
    $padTop = 6;
    $padBottom = 6;
    $padX = 8;
    $plotW = $width - ($padX * 2);
    $plotH = $height - $padTop - $padBottom;

    $pts = [];
    foreach ($data as $i => $value) {
        $x = round($padX + ($count > 1 ? ($i / ($count - 1)) * $plotW : $plotW / 2), 1);
        if ($max === $min) {
            $y = $max == 0 ? round($height - $padBottom, 1) : round($height / 2, 1);
        } else {
            $y = round(($height - $padBottom) - (($value - $min) / ($max - $min)) * $plotH, 1);
        }
        $pts[] = ['x' => $x, 'y' => $y, 'val' => $value];
    }

    // Smooth bezier curve
    $curvePath = "M {$pts[0]['x']},{$pts[0]['y']}";
    for ($i = 0; $i < count($pts) - 1; $i++) {
        $p0 = $pts[$i];
        $p1 = $pts[$i + 1];
        $dx = ($p1['x'] - $p0['x']) / 2;
        $c1x = round($p0['x'] + $dx, 1);
        $c1y = $p0['y'];
        $c2x = round($p1['x'] - $dx, 1);
        $c2y = $p1['y'];
        $curvePath .= " C {$c1x},{$c1y} {$c2x},{$c2y} {$p1['x']},{$p1['y']}";
    }
    $lastPt = end($pts);
    $areaPath = $curvePath . " L {$lastPt['x']},{$height} L {$pts[0]['x']},{$height} Z";

    $gradId = 'admSparkGrad_' . $id . '_' . substr(md5($color . ($isDarkCard ? 'dark' : 'light')), 0, 6);

    $svgPoints = '';
    foreach ($pts as $i => $pt) {
        $isLast = ($i === $count - 1);
        $val = $pt['val'];
        $d = $dates[$i] ?? [
            'day'   => $isLast ? 'Today' : 'Day ' . ($i + 1),
            'date'  => '',
            'short' => '',
        ];

        $formattedUnit = $unit;
        if ($unit === 'Teachers') {
            $formattedUnit = $val === 1 ? 'Teacher' : 'Teachers';
        } elseif ($unit === 'Students') {
            $formattedUnit = $val === 1 ? 'Student' : 'Students';
        } elseif ($unit === 'Completions') {
            $formattedUnit = $val === 1 ? 'Completion' : 'Completions';
        }

        // Crosshair vertical line
        $crosshairColor = $isDarkCard ? '#ffffff' : $color;
        $svgPoints .= '<line class="kpi-crosshair-line" data-idx="' . $i . '" x1="' . $pt['x'] . '" y1="0" x2="' . $pt['x'] . '" y2="' . $height . '" stroke="' . e($crosshairColor) . '" stroke-width="1.2" stroke-dasharray="2 2" stroke-opacity="0" style="transition: stroke-opacity 0.15s ease; pointer-events: none;"/>';

        // Outer glowing halo
        $haloColor = $isDarkCard ? '#ffffff' : $color;
        $svgPoints .= '<circle class="kpi-point-halo" data-idx="' . $i . '" cx="' . $pt['x'] . '" cy="' . $pt['y'] . '" r="8" fill="' . e($haloColor) . '" fill-opacity="0" style="transition: fill-opacity 0.18s ease, r 0.18s ease; pointer-events: none;"/>';

        // Indicator pulse for current day
        if ($isLast) {
            $svgPoints .= '<circle cx="' . $pt['x'] . '" cy="' . $pt['y'] . '" r="5.5" fill="' . e($haloColor) . '" fill-opacity="' . ($isDarkCard ? '0.25' : '0.16') . '" pointer-events="none"/>';
        }

        // Trend line dot
        $dotFill = $isDarkCard ? '#ffffff' : $color;
        $dotStroke = $isDarkCard ? '#0d326b' : '#ffffff';
        $r = $isLast ? 3.5 : 3;
        $svgPoints .= '<circle class="kpi-point-dot" data-idx="' . $i . '" data-is-last="' . ($isLast ? '1' : '0') . '" cx="' . $pt['x'] . '" cy="' . $pt['y'] . '" r="' . $r . '" fill="' . e($dotFill) . '" stroke="' . e($dotStroke) . '" stroke-width="2" style="transition: r 0.18s ease, stroke-width 0.18s ease; pointer-events: none;"/>';

        // Hit target
        $svgPoints .= '<circle class="kpi-point-hit" data-idx="' . $i . '" cx="' . $pt['x'] . '" cy="' . $pt['y'] . '" r="16" fill="transparent" style="cursor: pointer;" '
            . 'data-val="' . e(number_format($val)) . '" '
            . 'data-unit="' . e($formattedUnit) . '" '
            . 'data-day="' . e($d['day']) . '" '
            . 'data-date="' . e($d['date']) . '" '
            . 'data-short="' . e($d['short']) . '" '
            . 'data-color="' . e($color) . '" '
            . 'data-dark="' . ($isDarkCard ? '1' : '0') . '" '
            . 'data-metric="' . e($metric) . '"/>';
    }

    // Tooltip styling based on dark or light card
    if ($isDarkCard) {
        $tipHtml = '<div class="kpi-sparkline-tooltip pointer-events-none absolute z-30 opacity-0 scale-95 transition-all duration-150 -translate-x-1/2 -translate-y-full bg-white text-[#0d326b] rounded-xl shadow-2xl px-2.5 py-1.5 whitespace-nowrap border border-white/40 mb-2">'
            . '  <div class="flex items-center justify-between gap-3 text-[10px] leading-tight">'
            . '    <span class="font-extrabold text-[#0d326b] tracking-tight kpi-tip-day">Today</span>'
            . '    <span class="text-slate-400 text-[9px] font-medium kpi-tip-date"></span>'
            . '  </div>'
            . '  <div class="text-[12px] font-black text-[#0d326b] mt-1 flex items-center gap-1.5 leading-none">'
            . '    <span class="w-1.5 h-1.5 rounded-full kpi-tip-dot flex-shrink-0 bg-[#0d326b]"></span>'
            . '    <span class="kpi-tip-val font-black text-[#0d326b] text-[13px]"></span>'
            . '    <span class="font-bold text-slate-600 text-[10.5px] kpi-tip-unit"></span>'
            . '  </div>'
            . '  <div class="kpi-tip-arrow absolute left-1/2 -bottom-1 -translate-x-1/2 w-2 h-2 bg-white rotate-45 border-r border-b border-slate-200/80"></div>'
            . '</div>';
    } else {
        $tipHtml = '<div class="kpi-sparkline-tooltip pointer-events-none absolute z-30 opacity-0 scale-95 transition-all duration-150 -translate-x-1/2 -translate-y-full bg-[#0d326b] text-white rounded-xl shadow-xl px-2.5 py-1.5 whitespace-nowrap border border-blue-400/20 mb-2">'
            . '  <div class="flex items-center justify-between gap-3 text-[10px] leading-tight">'
            . '    <span class="font-bold text-white tracking-tight kpi-tip-day">Today</span>'
            . '    <span class="text-blue-200/90 text-[9px] font-medium kpi-tip-date"></span>'
            . '  </div>'
            . '  <div class="text-[12px] font-black text-white mt-1 flex items-center gap-1.5 leading-none">'
            . '    <span class="w-1.5 h-1.5 rounded-full kpi-tip-dot flex-shrink-0" style="background-color: ' . e($color) . ';"></span>'
            . '    <span class="kpi-tip-val font-black text-white text-[13px]"></span>'
            . '    <span class="font-semibold text-blue-100 text-[10.5px] kpi-tip-unit"></span>'
            . '  </div>'
            . '  <div class="kpi-tip-arrow absolute left-1/2 -bottom-1 -translate-x-1/2 w-2 h-2 bg-[#0d326b] rotate-45 border-r border-b border-blue-400/20"></div>'
            . '</div>';
    }

    $areaColor = $isDarkCard ? '#ffffff' : $color;
    $lineColor = $isDarkCard ? '#ffffff' : $color;

    return '<div class="kpi-sparkline-container relative w-full h-[38px] select-none">'
        . $tipHtml
        . '<svg viewBox="0 0 ' . $width . ' ' . $height . '" class="w-full h-[38px] overflow-visible" preserveAspectRatio="none" aria-hidden="true">'
        . '  <defs>'
        . '    <linearGradient id="' . $gradId . '" x1="0%" y1="0%" x2="0%" y2="100%">'
        . '      <stop offset="0%" stop-color="' . e($areaColor) . '" stop-opacity="' . ($isDarkCard ? '0.28' : '0.22') . '"/>'
        . '      <stop offset="85%" stop-color="' . e($areaColor) . '" stop-opacity="' . ($isDarkCard ? '0.05' : '0.03') . '"/>'
        . '      <stop offset="100%" stop-color="' . e($areaColor) . '" stop-opacity="0"/>'
        . '    </linearGradient>'
        . '  </defs>'
        . '  <path d="' . $areaPath . '" fill="url(#' . $gradId . ')"/>'
        . '  <path d="' . $curvePath . '" fill="none" stroke="' . e($lineColor) . '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>'
        .    $svgPoints
        . '</svg>'
        . '</div>';
};

/* ── Smooth bezier path builder ────────────────────────────────────── */
$bezier = function (array $pts, float $bot, bool $area = false): string {
    if (empty($pts)) return '';
    $d = "M {$pts[0]['x']},{$pts[0]['y']}";
    for ($i = 0; $i < count($pts) - 1; $i++) {
        $dx = ($pts[$i+1]['x'] - $pts[$i]['x']) / 2;
        $d .= " C ".($pts[$i]['x']+$dx).",{$pts[$i]['y']} ".($pts[$i+1]['x']-$dx).",{$pts[$i+1]['y']} {$pts[$i+1]['x']},{$pts[$i+1]['y']}";
    }
    if ($area) $d .= " L {$pts[count($pts)-1]['x']},{$bot} L {$pts[0]['x']},{$bot} Z";
    return $d;
};

/* ── Chart coords ───────────────────────────────────────────────────── */
$W = 600; $H = 240; $pL = 28; $pR = 28; $pT = 16; $pB = 28;
$plotW = $W - $pL - $pR; $plotH = $H - $pT - $pB; $bot = $pT + $plotH;
$n = count($activityTrend);
$peak = max(1, collect($activityTrend)->max(fn($d) => max($d['completions'], $d['students'])));

$cPts = []; $sPts = [];
foreach ($activityTrend as $i => $d) {
    $x = $n > 1 ? $pL + ($i / ($n - 1)) * $plotW : $pL + $plotW / 2;
    $cPts[] = ['x' => round($x,2), 'y' => round($pT + $plotH - ($d['completions'] / $peak) * $plotH, 2)];
    $sPts[] = ['x' => round($x,2), 'y' => round($pT + $plotH - ($d['students']    / $peak) * $plotH, 2)];
}
$cLine = $bezier($cPts, $bot); $cArea = $bezier($cPts, $bot, true);
$sLine = $bezier($sPts, $bot); $sArea = $bezier($sPts, $bot, true);
@endphp

<div class="flex flex-col gap-5 w-full pt-4 skeleton-hide">

    {{-- ── WELCOME BANNER ──────────────────────────────────────────────── --}}
    <div class="rounded-[28px] relative overflow-hidden flex items-center"
         style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%);min-height:130px">
        <div class="absolute top-0 right-44 w-44 h-44 rounded-full opacity-10 bg-white"></div>
        <div class="absolute -bottom-8 left-1/3 w-32 h-32 rounded-full opacity-10 bg-white"></div>
        <div class="relative z-10 px-10 py-7 flex-1">
    <h2 class="text-[24px] font-black text-white leading-tight mb-1">System Overview</h2>
    <p class="text-[13px] text-white/70 font-medium">Welcome back, <span class="text-white font-bold">{{ Auth::user()->name }}</span>. Here's a live summary of the SEÑAS platform.</p>

    <a href="{{ route('admin.testing.alphabet') }}"
       class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 rounded-full bg-white/15 hover:bg-white/25 border border-white/30 text-white text-[12px] font-bold transition-colors">
        <span class="material-symbols-outlined text-[16px]">science</span>
        Test Environment
    </a>
</div>
        <div class="relative z-10 flex-shrink-0 pr-10 hidden lg:flex items-center gap-8">
            @foreach([['label'=>'Teachers','val'=>$totalTeachers],['label'=>'Students','val'=>$totalStudents],['label'=>'Lessons','val'=>$totalLessons]] as $bi)
            @if(!$loop->first)<div class="w-px h-10 bg-white/20"></div>@endif
            <div class="text-center">
                <p class="text-[30px] font-black text-white leading-none">{{ $bi['val'] }}</p>
                <p class="text-[10px] font-semibold text-white/60 uppercase tracking-wider mt-0.5">{{ $bi['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── KPI CARDS ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- Card 1: Total Teachers — navy gradient --}}
        <div class="stat-kpi-card text-white" style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%);">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Total Teachers</span>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px] text-white">school</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-white tracking-tight">{{ $totalTeachers }}</p>
            <p class="text-[12px] text-white/70 font-medium">↑ {{ $newTeachersWeek }} this week</p>
            @if(!empty($sparkTeachers))
            <div class="mt-3 relative kpi-sparkline-wrap">{!! $spark($sparkTeachers, '#ffffff', 'Total Teachers', 'Teachers', $sparkDates ?? [], 'teachers', true) !!}</div>
            @endif
        </div>

        {{-- Card 2: Total Students — white --}}
        <div style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:#fff;">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Students</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">group</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ $totalStudents }}</p>
            <p class="text-[12px] text-[#1a6fd4] font-medium">↑ {{ $newStudentsWeek }} this week</p>
            @if(!empty($sparkStudents))
            <div class="mt-3 relative kpi-sparkline-wrap">{!! $spark($sparkStudents, '#1e4b8f', 'Total Students', 'Students', $sparkDates ?? [], 'students', false) !!}</div>
            @endif
        </div>

        {{-- Card 3: Lessons Completed — white --}}
        <div style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:#fff;">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Lessons Completed</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1a6fd4] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">menu_book</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ number_format($totalLessonsCompleted) }}</p>
            <p class="text-[12px] text-slate-400 font-medium">{{ $publishedLessons }} published lessons</p>
            @if(!empty($sparkLessons))
            <div class="mt-3 relative kpi-sparkline-wrap">{!! $spark($sparkLessons, '#1a6fd4', 'Lessons Completed', 'Completions', $sparkDates ?? [], 'lessons', false) !!}</div>
            @endif
        </div>

        {{-- Card 4: Escalated Concerns — amber gradient --}}
        <div class="text-amber-950" style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border:1px solid rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22);">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Escalated Concerns</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 text-amber-950 flex items-center justify-center backdrop-blur-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">flag</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-amber-950 tracking-tight">{{ $pendingReports }}</p>
            <p class="text-[12px] text-amber-950/80 font-bold">{{ $resolvedReports }} resolved of {{ $totalReports }} total</p>
            <div class="mt-3">
                <a href="{{ route('admin.reports') }}" class="inline-flex items-center gap-1 text-[12px] font-bold text-amber-950/80 hover:text-amber-950 transition-colors">
                    View all escalated concerns <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </a>
            </div>
        </div>

    </div>

    {{-- ── MAIN GRID: chart (2/3) + side stats (1/3) ─────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

        {{-- Chart card --}}
        <div class="lg:col-span-2 bg-white rounded-[22px] shadow-sm border border-slate-100 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Trend</p>
                    <h3 class="text-[16px] font-bold text-[#0d326b]">Platform Activity</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Lesson completions & active students — last 14 days</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-semibold flex-shrink-0">
                    <span class="flex items-center gap-1.5"><span class="w-8 h-1.5 rounded-full bg-[#0d326b] inline-block"></span>Completions</span>
                    <span class="flex items-center gap-1.5"><span class="w-8 h-1.5 rounded-full bg-[#1a6fd4] inline-block" style="background:repeating-linear-gradient(90deg,#1a6fd4 0,#1a6fd4 4px,transparent 4px,transparent 7px)"></span>Active Students</span>
                </div>
            </div>

            <div class="bg-[#fafcff] rounded-2xl w-full relative" style="padding-bottom:40%">
                <svg viewBox="0 0 {{ $W }} {{ $H }}" class="absolute inset-0 w-full h-full" preserveAspectRatio="none" overflow="visible">
                    <defs>
                        <linearGradient id="gCFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#0d326b" stop-opacity=".20"/>
                            <stop offset="100%" stop-color="#0d326b" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="gSFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#1a6fd4" stop-opacity=".14"/>
                            <stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="gCLine" x1="0" y1="0" x2="100%" y2="0">
                            <stop offset="0%" stop-color="#1e4b8f"/>
                            <stop offset="100%" stop-color="#071c3f"/>
                        </linearGradient>
                        <linearGradient id="gSLine" x1="0" y1="0" x2="100%" y2="0">
                            <stop offset="0%" stop-color="#3b82f6"/>
                            <stop offset="100%" stop-color="#1a6fd4"/>
                        </linearGradient>
                    </defs>

                    {{-- Grid lines --}}
                    @foreach([0,25,50,75,100] as $gv)
                        @php $gy = round($pT + $plotH - ($gv/100)*$plotH, 1); @endphp
                        <line x1="{{ $pL }}" y1="{{ $gy }}" x2="{{ $pL+$plotW }}" y2="{{ $gy }}" stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                    @endforeach

                    {{-- Area fills --}}
                    <path d="{{ $cArea }}" fill="url(#gCFill)"/>
                    <path d="{{ $sArea }}" fill="url(#gSFill)"/>

                    {{-- Lines --}}
                    <path d="{{ $cLine }}" fill="none" stroke="url(#gCLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="{{ $sLine }}" fill="none" stroke="url(#gSLine)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="5,3"/>

                    {{-- Dots --}}
                    @foreach($cPts as $i => $p)
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}"
                                r="{{ $i===count($cPts)-1 ? 4.5 : 3 }}"
                                fill="{{ $i===count($cPts)-1 ? '#0d326b' : '#1e4b8f' }}"
                                stroke="white" stroke-width="2"/>
                    @endforeach

                    {{-- X labels --}}
                    @foreach($activityTrend as $i => $d)
                        @if($i % 2 === 0 || $i === count($activityTrend)-1)
                            <text x="{{ $cPts[$i]['x'] }}" y="{{ $H - 10 }}"
                                  font-size="10" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $d['label'] }}</text>
                        @endif
                    @endforeach
                </svg>
            </div>
        </div>

        {{-- Side stats --}}
        <div class="flex flex-col gap-4">

            {{-- Active this week --}}
            <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-5">
                <h4 class="text-[13px] font-black text-[#0d326b] mb-4">Active This Week</h4>
                @foreach([['icon'=>'school','label'=>'Teachers','val'=>$activeTeachers,'total'=>$totalTeachers,'color'=>'#0d326b'],['icon'=>'group','label'=>'Students','val'=>$activeStudents,'total'=>$totalStudents,'color'=>'#1e4b8f']] as $row)
                <div class="{{ $loop->first ? 'mb-4' : '' }}">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-[#dbeafe] flex items-center justify-center">
                                <span class="material-symbols-outlined text-[#0d326b] text-[14px]">{{ $row['icon'] }}</span>
                            </div>
                            <span class="text-[13px] font-semibold text-slate-700">{{ $row['label'] }}</span>
                        </div>
                        <span class="text-[16px] font-black text-[#0d326b]">{{ $row['val'] }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all" style="background:{{ $row['color'] }};width:{{ $row['total']>0?round(($row['val']/$row['total'])*100):0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Platform totals --}}
            <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-5">
                <h4 class="text-[13px] font-black text-[#0d326b] mb-4">Platform Totals</h4>
                <div class="grid grid-cols-2 gap-3">
                    @php $qs = [
                        ['icon'=>'quiz',          'label'=>'Quiz Attempts', 'val'=>number_format($totalQuizAttempts)],
                        ['icon'=>'view_module',   'label'=>'Modules',      'val'=>number_format($totalModules)],
                        ['icon'=>'check_circle',  'label'=>'Resolved',     'val'=>number_format($resolvedReports)],
                        ['icon'=>'verified_user', 'label'=>'Admins',       'val'=>\App\Models\User::where('role','admin')->count()],
                    ]; @endphp
                    @foreach($qs as $q)
                    <div class="bg-[#f8fafc] rounded-2xl p-3 text-center">
                        <span class="material-symbols-outlined text-[#0d326b] text-[20px]">{{ $q['icon'] }}</span>
                        <p class="text-[18px] font-black text-[#0d326b] leading-none mt-1">{{ $q['val'] }}</p>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mt-0.5">{{ $q['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- ── BOTTOM GRID ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Top Teachers --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Top Teachers</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">By student count</p>
                </div>
                <a href="{{ route('admin.accounts') }}" class="text-[11px] font-black uppercase tracking-wider text-[#0d326b] hover:underline">View All</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($topTeachers as $i => $t)
                <div class="flex items-center gap-3 px-6 py-3.5">
                    <div class="w-7 h-7 rounded-full text-[11px] font-black flex items-center justify-center flex-shrink-0
                        {{ $i===0?'bg-[#facc15] text-[#0d326b]':($i===1?'bg-slate-200 text-slate-600':($i===2?'bg-amber-100 text-amber-700':'bg-slate-100 text-slate-400')) }}">{{ $i+1 }}</div>
                    <img src="{{ $t->user->avatarUrl() }}" class="w-8 h-8 rounded-full object-cover border border-slate-100 flex-shrink-0"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($t->first_name.'+'. $t->last_name) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate">{{ trim($t->first_name.' '.$t->last_name) }}</p>
                        <p class="text-[11px] text-slate-400">{{ $t->students_count }} student{{ $t->students_count==1?'':'s' }}</p>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center">
                    <span class="material-symbols-outlined text-slate-200 text-[36px]">school</span>
                    <p class="text-[13px] text-slate-400 mt-2">No teachers yet</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Escalated Concerns --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Escalated Concerns</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Concerns escalated by teachers</p>
                </div>
                <a href="{{ route('admin.reports') }}" class="text-[11px] font-black uppercase tracking-wider text-[#0d326b] hover:underline">View All</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($recentReports as $report)
                @php
                    $sm = [
                        'escalated' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Escalated'],
                        'closed'    => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Resolved'],
                    ];
                    $sc = $sm[$report->status] ?? ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Escalated'];
                    $teacherName = $report->escalator?->name ?? ($report->teacher ? trim($report->teacher->first_name . ' ' . $report->teacher->last_name) : null);
                @endphp
                <div class="flex items-start gap-3 px-6 py-3.5">
                    <div class="w-8 h-8 rounded-full bg-[#0d326b] flex items-center justify-center text-white text-[11px] font-black flex-shrink-0">
                        {{ strtoupper(substr($report->student->first_name??'S',0,1).substr($report->student->last_name??'?',0,1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <p class="text-[13px] font-bold text-slate-800 truncate">{{ trim(($report->student->first_name??'').(' ').($report->student->last_name??'Unknown Student')) }}</p>
                            @if($teacherName)
                                <span class="text-[10px] text-slate-400 font-medium truncate">• via {{ $teacherName }}</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $report->escalation_reason ?: Str::limit($report->message, 50) }}</p>
                    </div>
                    <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">{{ $sc['label'] }}</span>
                </div>
                @empty
                <div class="px-6 py-8 text-center">
                    <span class="material-symbols-outlined text-slate-200 text-[36px]">flag</span>
                    <p class="text-[13px] text-slate-400 mt-2">No escalated concerns yet</p>
                    <p class="text-[11px] text-slate-300 mt-0.5">Concerns escalated by teachers will appear here</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Ratings Summary --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-black text-[#0d326b]">App Ratings</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Overall score &amp; latest feedback</p>
                </div>
                <a href="{{ route('admin.ratings') }}"
                   class="text-[11px] font-bold text-[#1a6fd4] hover:underline flex items-center gap-1">
                    View all <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                </a>
            </div>

            <div class="p-6 space-y-5">

                {{-- Overall average --}}
                <div class="flex items-center gap-4 p-4 rounded-2xl"
                     style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 100%)">
                    @php
                        $stars  = round($overallAvgRating * 2) / 2; // nearest 0.5
                        $full   = floor($stars);
                        $half   = ($stars - $full) >= 0.5 ? 1 : 0;
                        $empty  = 5 - $full - $half;
                    @endphp
                    <div class="text-center shrink-0">
                        <p class="text-[36px] font-black text-white leading-none">{{ number_format($overallAvgRating, 1) }}</p>
                        <div class="flex items-center gap-0.5 mt-1 justify-center">
                            @for($s = 0; $s < $full;  $s++)<span class="material-symbols-outlined text-[14px] text-amber-400" style="font-variation-settings:'FILL' 1">star</span>@endfor
                            @if($half)<span class="material-symbols-outlined text-[14px] text-amber-400" style="font-variation-settings:'FILL' 1">star_half</span>@endif
                            @for($s = 0; $s < $empty; $s++)<span class="material-symbols-outlined text-[14px] text-white/30" style="font-variation-settings:'FILL' 1">star</span>@endfor
                        </div>
                        <p class="text-[10px] text-white/60 font-semibold mt-1">{{ $totalRatingsDash }} reviews</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] font-bold text-white/80 mb-2">out of 5.0</p>
                        @foreach([5,4,3,2,1] as $star)
                        @php
                            $cnt = \App\Models\TeacherRating::where('rating',$star)->count()
                                 + \App\Models\StudentRating::where('rating',$star)->count();
                            $pct = $totalRatingsDash > 0 ? round(($cnt / $totalRatingsDash) * 100) : 0;
                        @endphp
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="text-[9px] font-bold text-white/60 w-2 shrink-0">{{ $star }}</span>
                            <div class="flex-1 bg-white/15 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full bg-amber-400" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 3 newest comments --}}
                <div class="space-y-3">
                    @forelse($newestRatings as $nr)
                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-black text-white shrink-0"
                                     style="background:linear-gradient(135deg,#0d326b,#1a6fd4)">
                                    {{ strtoupper(substr($nr['name'], 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[12px] font-bold text-[#0d326b] truncate">{{ $nr['name'] }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $nr['role'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-0.5 shrink-0">
                                @for($s = 1; $s <= 5; $s++)
                                    <span class="material-symbols-outlined text-[13px] {{ $s <= $nr['rating'] ? 'text-amber-400' : 'text-slate-200' }}"
                                          style="font-variation-settings:'FILL' 1">star</span>
                                @endfor
                            </div>
                        </div>
                        @if(!empty($nr['comment']))
                            <p class="text-[11.5px] text-slate-600 leading-relaxed line-clamp-2">
                                "{{ $nr['comment'] }}"
                            </p>
                        @else
                            <p class="text-[11px] text-slate-300 italic">No comment left.</p>
                        @endif
                        <p class="text-[10px] text-slate-400 mt-1.5">
                            {{ \Carbon\Carbon::parse($nr['date'])->diffForHumans() }}
                        </p>
                    </div>
                    @empty
                    <div class="py-4 text-center">
                        <span class="material-symbols-outlined text-slate-200 text-[36px]">star</span>
                        <p class="text-[13px] text-slate-400 mt-2">No ratings yet</p>
                    </div>
                    @endforelse
                </div>

            </div>
        </div>

    </div>

</div>{{-- end skeleton-hide --}}

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── KPI Sparklines: hover tooltip & point effects ──
    (function () {
        const wraps = document.querySelectorAll('.kpi-sparkline-wrap');
        wraps.forEach(function (wrap) {
            const tip = wrap.querySelector('.kpi-sparkline-tooltip');
            if (!tip) return;

            const tipDay = tip.querySelector('.kpi-tip-day');
            const tipDate = tip.querySelector('.kpi-tip-date');
            const tipVal = tip.querySelector('.kpi-tip-val');
            const tipUnit = tip.querySelector('.kpi-tip-unit');
            const tipDot = tip.querySelector('.kpi-tip-dot');
            const tipArrow = tip.querySelector('.kpi-tip-arrow');

            const hits = wrap.querySelectorAll('.kpi-point-hit');
            let hideTimer = null;

            hits.forEach(function (hit) {
                const idx = hit.dataset.idx;
                const dot = wrap.querySelector('.kpi-point-dot[data-idx="' + idx + '"]');
                const halo = wrap.querySelector('.kpi-point-halo[data-idx="' + idx + '"]');
                const crosshair = wrap.querySelector('.kpi-crosshair-line[data-idx="' + idx + '"]');

                function activate() {
                    if (hideTimer) {
                        clearTimeout(hideTimer);
                        hideTimer = null;
                    }

                    // Reset all other points in this sparkline
                    wrap.querySelectorAll('.kpi-point-dot').forEach(function (d) {
                        if (d !== dot) {
                            const isL = d.dataset.isLast === '1';
                            d.setAttribute('r', isL ? '3.5' : '3');
                            d.setAttribute('stroke-width', '2');
                        }
                    });
                    wrap.querySelectorAll('.kpi-point-halo').forEach(function (h) {
                        if (h !== halo) {
                            h.setAttribute('r', '8');
                            h.setAttribute('fill-opacity', '0');
                        }
                    });
                    wrap.querySelectorAll('.kpi-crosshair-line').forEach(function (c) {
                        if (c !== crosshair) {
                            c.setAttribute('stroke-opacity', '0');
                        }
                    });

                    // Update tooltip content
                    if (tipDay) tipDay.textContent = hit.dataset.day || '';
                    if (tipDate) tipDate.textContent = hit.dataset.date || '';
                    if (tipVal) tipVal.textContent = hit.dataset.val || '0';
                    if (tipUnit) tipUnit.textContent = hit.dataset.unit || '';
                    if (tipDot && hit.dataset.dark !== '1') {
                        tipDot.style.backgroundColor = hit.dataset.color || '#0d326b';
                    }

                    // Highlight hovered point elements
                    if (dot) {
                        dot.setAttribute('r', '5.5');
                        dot.setAttribute('stroke-width', '2.5');
                    }
                    if (halo) {
                        halo.setAttribute('r', '9.5');
                        halo.setAttribute('fill-opacity', hit.dataset.dark === '1' ? '0.35' : '0.25');
                    }
                    if (crosshair) {
                        crosshair.setAttribute('stroke-opacity', '0.35');
                    }

                    // Calculate position
                    const hitRect = hit.getBoundingClientRect();
                    const wrapRect = wrap.getBoundingClientRect();
                    const tipW = tip.offsetWidth || 110;
                    const pointCenterX = hitRect.left - wrapRect.left + (hitRect.width / 2);
                    const pointCenterY = hitRect.top - wrapRect.top;

                    // Clamp horizontally so tooltip stays neatly inside card bounds
                    const minLeft = (tipW / 2) + 2;
                    const maxLeft = wrapRect.width - (tipW / 2) - 2;
                    const clampedLeft = Math.max(minLeft, Math.min(maxLeft, pointCenterX));

                    tip.style.left = clampedLeft + 'px';
                    tip.style.top = (pointCenterY - 8) + 'px';

                    // Position arrow
                    if (tipArrow) {
                        const arrowOffset = pointCenterX - clampedLeft;
                        tipArrow.style.transform = 'translateX(calc(-50% + ' + arrowOffset + 'px)) rotate(45deg)';
                    }

                    tip.classList.remove('opacity-0', 'scale-95');
                    tip.classList.add('opacity-100', 'scale-100');
                }

                function deactivate() {
                    if (dot) {
                        const isL = dot.dataset.isLast === '1';
                        dot.setAttribute('r', isL ? '3.5' : '3');
                        dot.setAttribute('stroke-width', '2');
                    }
                    if (halo) {
                        halo.setAttribute('r', '8');
                        halo.setAttribute('fill-opacity', '0');
                    }
                    if (crosshair) {
                        crosshair.setAttribute('stroke-opacity', '0');
                    }

                    hideTimer = setTimeout(function () {
                        tip.classList.remove('opacity-100', 'scale-100');
                        tip.classList.add('opacity-0', 'scale-95');
                    }, 50);
                }

                hit.addEventListener('mouseenter', activate);
                hit.addEventListener('mouseleave', deactivate);
                hit.addEventListener('touchstart', function (e) {
                    activate();
                }, { passive: true });
            });

            // Hide when tapping elsewhere on touch devices
            document.addEventListener('touchstart', function (e) {
                if (!wrap.contains(e.target)) {
                    tip.classList.remove('opacity-100', 'scale-100');
                    tip.classList.add('opacity-0', 'scale-95');
                }
            }, { passive: true });
        });
    })();
});
</script>
@endsection
