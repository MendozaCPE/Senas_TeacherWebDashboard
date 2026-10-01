@extends('layouts.teacher-leader')
@section('title', 'School Dashboard')
@section('content')

{{-- ══════════════════════════════════════════════════════════════════════
     SKELETON LOADER (Matches Teacher Dashboard Layout)
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="page-skeleton" class="flex flex-col gap-4 w-full pt-4" aria-hidden="true">

    {{-- Main content + Right sidebar wrapper --}}
    <div class="flex flex-col lg:flex-row gap-4 w-full">

        {{-- Left / Center column --}}
        <div class="flex-1 min-w-0 flex flex-col space-y-4">

            {{-- Welcome banner + calendar --}}
            <div class="flex flex-col sm:flex-row gap-5">
                {{-- Banner --}}
                <div class="skeleton skeleton-card flex-1 min-h-[160px] rounded-[28px]"></div>
                {{-- Calendar widget --}}
                <div class="skeleton skeleton-card flex-shrink-0 w-full sm:w-[260px] min-h-[160px] rounded-[28px]"></div>
            </div>

            {{-- 4 KPI cards row --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                @for($i = 0; $i < 4; $i++)
                <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 border border-slate-100 shadow-sm flex flex-col gap-3 min-h-[168px]">
                    <div class="flex items-center gap-3">
                        <div class="skeleton skeleton-circle w-9 h-9 flex-shrink-0"></div>
                        <div class="skeleton h-3 rounded w-28"></div>
                    </div>
                    <div class="skeleton h-8 rounded w-16 mt-1"></div>
                    <div class="skeleton h-3 rounded w-24"></div>
                    <div class="skeleton rounded mt-auto h-[44px] w-full"></div>
                </div>
                @endfor
            </div>

            {{-- Activity Chart --}}
            <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-6">
                <div class="skeleton h-5 rounded w-48 mb-4"></div>
                <div class="skeleton rounded-2xl w-full" style="padding-bottom:38%;"></div>
            </div>

            {{-- 2 Bottom Cards: Top Classes & Needs Support --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @for($i=0;$i<2;$i++)
                <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-5">
                    <div class="skeleton h-4 rounded w-36 mb-4"></div>
                    @for($j=0;$j<4;$j++)
                    <div class="flex items-center gap-3 py-2.5">
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

        {{-- Right sidebar column --}}
        <div class="w-full lg:w-[340px] flex-shrink-0 flex flex-col space-y-4 lg:pl-2">
            {{-- Senya Insights widget --}}
            <div class="skeleton skeleton-card h-[140px] rounded-[28px]"></div>

            {{-- Recent Engagement card (mirrors My Students) --}}
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                <div class="px-7 pt-7 pb-4 flex items-center justify-between">
                    <div class="flex flex-col gap-2">
                        <div class="skeleton h-4 rounded w-28"></div>
                        <div class="skeleton h-3 rounded w-16"></div>
                    </div>
                    <div class="skeleton h-6 rounded w-16"></div>
                </div>
                <div class="mx-7 border-t border-slate-100"></div>
                @for($i = 0; $i < 5; $i++)
                <div class="flex items-center gap-4 px-7 py-4">
                    <div class="skeleton skeleton-circle w-11 h-11 flex-shrink-0"></div>
                    <div class="flex-1 flex flex-col gap-2">
                        <div class="skeleton h-3 rounded w-3/4"></div>
                        <div class="skeleton h-2 rounded w-full"></div>
                    </div>
                </div>
                @endfor
                <div class="px-7 py-3 border-t border-slate-100">
                    <div class="skeleton skeleton-card h-11 w-full rounded-xl"></div>
                </div>
            </div>
        </div>

    </div>

</div>

@php
/* ── Inline SVG bezier helper ── */
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

/* ── Sparkline helper (matches teacher dashboard KPI cards) ── */
$kpiSparkline = function (array $data, string $color, string $id, int $width = 240, int $height = 44) use ($sparkDates): string {
    if (count($data) < 2) { $data = array_fill(0, 7, $data[0] ?? 0); }
    $count = count($data);
    $max = max($data); $min = min($data);
    $padTop = 8; $padBottom = 8; $padX = 8;
    $plotW = $width - ($padX * 2); $plotH = $height - $padTop - $padBottom;
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
    $curvePath = "M {$pts[0]['x']},{$pts[0]['y']}";
    for ($i = 0; $i < count($pts) - 1; $i++) {
        $p0 = $pts[$i]; $p1 = $pts[$i + 1];
        $dx = ($p1['x'] - $p0['x']) / 2;
        $curvePath .= " C ".round($p0['x']+$dx,1).",{$p0['y']} ".round($p1['x']-$dx,1).",{$p1['y']} {$p1['x']},{$p1['y']}";
    }
    $lastPt = end($pts);
    $areaPath = $curvePath . " L {$lastPt['x']},{$height} L {$pts[0]['x']},{$height} Z";
    $gradId = 'tlKpiGrad_' . $id;

    $svgPoints = '';
    foreach ($pts as $i => $pt) {
        $isLast = ($i === $count - 1);
        $val = $pt['val'];
        $d = $sparkDates[$i] ?? ['day' => $isLast ? 'Today' : 'Day '.($i+1), 'date' => '', 'short' => ''];
        $svgPoints .= '<circle class="tl-kpi-halo" data-idx="'.$i.'" cx="'.$pt['x'].'" cy="'.$pt['y'].'" r="8.5" fill="'.e($color).'" fill-opacity="0" style="transition:fill-opacity 0.18s ease;pointer-events:none;"/>';
        if ($isLast) $svgPoints .= '<circle cx="'.$pt['x'].'" cy="'.$pt['y'].'" r="6" fill="'.e($color).'" fill-opacity="0.16" pointer-events="none"/>';
        $r = $isLast ? 3.5 : 3;
        $svgPoints .= '<circle class="tl-kpi-dot" data-idx="'.$i.'" cx="'.$pt['x'].'" cy="'.$pt['y'].'" r="'.$r.'" fill="'.e($color).'" stroke="#ffffff" stroke-width="2" style="transition:r 0.18s ease;pointer-events:none;"/>';
        $svgPoints .= '<circle class="tl-kpi-hit" data-idx="'.$i.'" cx="'.$pt['x'].'" cy="'.$pt['y'].'" r="18" fill="transparent" style="cursor:pointer;" data-val="'.e($val).'" data-day="'.e($d['day']).'" data-date="'.e($d['date']).'" data-color="'.e($color).'"/>';
    }

    return '<div class="tl-kpi-sparkline-wrap relative w-full h-[44px] select-none">'
        . '<div class="tl-kpi-tooltip pointer-events-none absolute z-30 opacity-0 scale-95 transition-all duration-150 -translate-x-1/2 -translate-y-full bg-[#0d326b] text-white rounded-xl shadow-xl px-2.5 py-1.5 whitespace-nowrap border border-blue-400/20 mb-2">'
        . '  <div class="flex items-center justify-between gap-3 text-[10px] leading-tight"><span class="font-bold text-white tracking-tight tl-tip-day">Today</span><span class="text-blue-200/90 text-[9px] font-medium tl-tip-date"></span></div>'
        . '  <div class="text-[12px] font-black text-white mt-1 flex items-center gap-1.5 leading-none"><span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:'.e($color).';"></span><span class="tl-tip-val font-black text-white text-[13px]"></span></div>'
        . '  <div class="tl-tip-arrow absolute left-1/2 -bottom-1 -translate-x-1/2 w-2 h-2 bg-[#0d326b] rotate-45 border-r border-b border-blue-400/20"></div>'
        . '</div>'
        . '<svg viewBox="0 0 '.$width.' '.$height.'" class="w-full h-[44px] overflow-visible" preserveAspectRatio="none" aria-hidden="true">'
        . '  <defs><linearGradient id="'.$gradId.'" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" stop-color="'.e($color).'" stop-opacity="0.22"/><stop offset="85%" stop-color="'.e($color).'" stop-opacity="0.03"/><stop offset="100%" stop-color="'.e($color).'" stop-opacity="0"/></linearGradient></defs>'
        . '  <path d="'.$areaPath.'" fill="url(#'.$gradId.')"/>'
        . '  <path d="'.$curvePath.'" fill="none" stroke="'.e($color).'" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>'
        . $svgPoints
        . '</svg></div>';
};

/* ── Activity Trend Chart coords ── */
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

/* ── Calendar dates calculation (matching Teacher Dashboard) ── */
$today = \Carbon\Carbon::now();
$startOfWeek = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
$weekDays = [];
for ($i = 0; $i < 21; $i++) {
    $weekDays[] = $startOfWeek->copy()->addDays($i);
}
$greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 17 ? 'Good afternoon' : 'Good evening');
$leaderFirstName = Auth::user()->teacher->first_name ?? explode(' ', Auth::user()->name)[0];

/* ── Senya Decision-making Insights (School-wide) ── */
$activeRate = $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100) : 0;
$supportCount = count($needsSupport->filter(fn($c) => $c['avg_score'] < 60)->all());

$insights = [];

// 1. Completion Rate Insight
if ($completionRate >= 80) {
    $insights[] = [
        'category' => 'CURRICULUM COMPLETION',
        'icon'     => 'check_circle',
        'text'     => "<strong>{$completionRate}%</strong> of assigned lessons completed school-wide. Classes are moving briskly through the curriculum!"
    ];
} elseif ($completionRate >= 50) {
    $insights[] = [
        'category' => 'CURRICULUM COMPLETION',
        'icon'     => 'task_alt',
        'text'     => "<strong>{$completionRate}%</strong> completion across all grades. Over <strong>".number_format($totalCompleted)."</strong> lessons completed school-wide."
    ];
} else {
    $insights[] = [
        'category' => 'CURRICULUM COMPLETION',
        'icon'     => 'assignment_late',
        'text'     => "Only <strong>{$completionRate}%</strong> of assigned lessons are completed (".number_format($totalCompleted)." of ".number_format($totalAssigned)."). Follow up with teachers on assignment pacing."
    ];
}

// 2. School-wide Quiz Accuracy
if ($avgQuizScore >= 75) {
    $insights[] = [
        'category' => 'ASSESSMENT MASTERY',
        'icon'     => 'psychology',
        'text'     => "School quiz average is high at <strong>{$avgQuizScore}%</strong>. Students demonstrate solid gesture retention across modules."
    ];
} elseif ($avgQuizScore >= 50) {
    $insights[] = [
        'category' => 'ASSESSMENT MASTERY',
        'icon'     => 'psychology_alt',
        'text'     => "School-wide quiz accuracy is <strong>{$avgQuizScore}%</strong>. Targeted review on tricky gestures could lift class scores into mastery."
    ];
} elseif ($avgQuizScore > 0) {
    $insights[] = [
        'category' => 'ASSESSMENT ALERT',
        'icon'     => 'warning',
        'text'     => "School-wide quiz average is <strong>{$avgQuizScore}%</strong>. Some teachers may need coaching on reinforcement and practice."
    ];
}

// 3. Active Engagement Trend
if ($activeRate >= 65) {
    $insights[] = [
        'category' => 'STUDENT ENGAGEMENT',
        'icon'     => 'bolt',
        'text'     => "High engagement: <strong>{$activeRate}%</strong> of students ({$activeStudents} of {$totalStudents}) actively practiced sign language this week."
    ];
} elseif ($activeRate < 40) {
    $insights[] = [
        'category' => 'ENGAGEMENT ALERT',
        'icon'     => 'person_off',
        'text'     => "Only <strong>{$activeRate}%</strong> of students were active this week ({$activeStudents} of {$totalStudents}). Consider school-wide practice reminders."
    ];
} else {
    $insights[] = [
        'category' => 'STUDENT ENGAGEMENT',
        'icon'     => 'group',
        'text'     => "<strong>{$activeStudents}</strong> active students ({$activeRate}%) this week across {$totalTeachers} monitored classrooms."
    ];
}

// 4. Top Performing Teacher Recognition
if ($topClasses->isNotEmpty() && $topClasses->first()['avg_score'] > 0) {
    $leadTeacher = $topClasses->first()['teacher'];
    $leadScore = $topClasses->first()['avg_score'];
    $insights[] = [
        'category' => 'TOP PERFORMER',
        'icon'     => 'military_tech',
        'text'     => "🏅 Teacher <strong>{$leadTeacher->first_name} {$leadTeacher->last_name}</strong>'s class leads the school with an average quiz score of <strong>{$leadScore}%</strong>!"
    ];
}

// 5. Classes Needing Support
if ($supportCount > 0) {
    $lowTeacher = $needsSupport->first()['teacher'];
    $lowScore = $needsSupport->first()['avg_score'];
    $insights[] = [
        'category' => 'SUPPORT RECOMMENDATION',
        'icon'     => 'school',
        'text'     => ($supportCount === 1 ? "1 class is" : "{$supportCount} classes are")." averaging below 60% (e.g. Teacher <strong>{$lowTeacher->first_name} {$lowTeacher->last_name}</strong> at <strong>{$lowScore}%</strong>). Check in with support strategies."
    ];
}

// Fallback if empty
if (empty($insights)) {
    $insights[] = [
        'category' => 'SCHOOL SUMMARY',
        'icon'     => 'lightbulb',
        'text'     => "School currently has <strong>{$totalTeachers}</strong> teachers and <strong>{$totalStudents}</strong> students with a <strong>{$completionRate}%</strong> completion rate."
    ];
}
@endphp

{{-- ══════════════════════════════════════════════════════════════════════
     REAL CONTENT (Matches Teacher Dashboard Layout)
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-col gap-2 w-full pt-4 skeleton-hide">

    <div class="flex flex-col lg:flex-row gap-4 w-full">

        <!-- ── Left / Center Column ────────────────────────────────────────── -->
        <div class="flex-1 min-w-0 flex flex-col space-y-4">

            <!-- Welcome Banner + Calendar Widget -->
            <div class="flex flex-col sm:flex-row gap-5">

                <!-- Welcome Banner -->
                <div class="flex-1 rounded-[28px] relative overflow-hidden min-h-[160px] flex items-center shadow-sm"
                     style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%)">
                    <!-- Decorative circles -->
                    <div class="absolute top-0 right-48 w-48 h-48 rounded-full opacity-10 bg-white pointer-events-none"></div>
                    <div class="absolute -bottom-10 left-1/3 w-36 h-36 rounded-full opacity-10 bg-white pointer-events-none"></div>

                    <!-- Text content -->
                    <div class="relative z-10 px-8 py-7 flex-1">
                        <h2 class="text-[26px] font-black text-white leading-tight mb-2">
                            {{ $greeting }}, Teacher Leader {{ $leaderFirstName }}!
                        </h2>
                        <p class="text-[13px] text-white/70 font-medium leading-relaxed mb-5">
                            Here is a summary of your school's<br>academic progress today.
                        </p>
                        <a href="{{ route('teacher-leader.lessons') }}"
                           class="inline-flex items-center space-x-2 text-[12px] font-black text-white/80 uppercase tracking-[0.1em] hover:text-white transition-colors">
                            <span>GO TO LESSONS</span>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>

                    <!-- Senya mascot -->
                    <div class="relative z-10 flex-shrink-0 pr-4 flex items-end self-end">
                        <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya"
                             class="h-[170px] w-auto object-contain drop-shadow-lg"
                             style="filter: drop-shadow(0 8px 24px rgba(0,0,0,0.3))"/>
                    </div>
                </div>

                <!-- Calendar Widget -->
                <div class="bg-white rounded-[28px] px-6 py-5 shadow-sm border border-slate-100 flex-shrink-0 w-full sm:w-[260px] flex flex-col justify-between">
                    <!-- Week nav -->
                    <div class="flex items-center justify-between mb-4">
                        <span id="week-label" class="text-[13px] font-black text-[#0d326b]">{{ $today->format('l, d F') }}</span>
                        <div class="flex items-center space-x-1">
                            <button id="week-prev" type="button" class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors text-slate-400">
                                <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                            </button>
                            <button id="week-next" type="button" class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors text-slate-400">
                                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                            </button>
                        </div>
                    </div>

                    <!-- Day labels -->
                    <div class="grid grid-cols-7 mb-2">
                        @foreach(['M','T','W','T','F','S','S'] as $d)
                        <div class="text-center text-[10px] font-bold text-slate-400 uppercase">{{ $d }}</div>
                        @endforeach
                    </div>

                    <!-- Week dates -->
                    <div id="week-days" class="grid grid-cols-7 gap-y-1">
                        @foreach($weekDays as $day)
                        <div class="flex items-center justify-center">
                            <div data-date="{{ $day->format('Y-m-d') }}"
                                 class="week-day w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-medium {{ $day->isToday() ? 'bg-[#1C3D7A] text-white' : 'text-slate-600 hover:bg-slate-100 cursor-pointer transition-colors' }}">
                                {{ $day->format('d') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Stats Row: 4 KPI Cards (matches Teacher Dashboard) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

                {{-- Total Teachers --}}
                <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-full bg-[#0d326b] flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">supervisor_account</span>
                        </div>
                        <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Total Teachers</h3>
                    </div>
                    <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $totalTeachers }}</p>
                    <p class="text-[12px] font-medium text-[#1a6fd4] mt-2 mb-4">in {{ $school->name ?? 'your school' }}</p>
                    <div class="mt-auto -mx-1">
                        {!! $kpiSparkline($sparkTeachers ?: array_fill(0, 7, 0), '#0d326b', 'teachers') !!}
                    </div>
                </div>

                {{-- Total Students --}}
                <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-full bg-[#1e4b8f] flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">group</span>
                        </div>
                        <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Total Students</h3>
                    </div>
                    <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $totalStudents }}</p>
                    <p class="text-[12px] font-medium text-[#1a6fd4] mt-2 mb-4">{{ $activeStudents }} active this week</p>
                    <div class="mt-auto -mx-1">
                        {!! $kpiSparkline($sparkStudents ?: array_fill(0, 7, 0), '#1e4b8f', 'students') !!}
                    </div>
                </div>

                {{-- Lesson Completion Rate --}}
                <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">task_alt</span>
                        </div>
                        <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Completion Rate</h3>
                    </div>
                    <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $completionRate }}%</p>
                    <p class="text-[12px] font-medium text-emerald-600 mt-2 mb-4">{{ number_format($totalCompleted) }} / {{ number_format($totalAssigned) }} lessons</p>
                    <div class="mt-auto -mx-1">
                        {!! $kpiSparkline($sparkLessons ?: array_fill(0, 7, 0), '#22c55e', 'lessons') !!}
                    </div>
                </div>

                {{-- Avg Quiz Score --}}
                <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-full bg-amber-500 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">insights</span>
                        </div>
                        <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Avg Quiz Score</h3>
                    </div>
                    <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $avgQuizScore }}%</p>
                    <p class="text-[12px] font-medium text-amber-600 mt-2 mb-4">school-wide average</p>
                    <div class="mt-auto -mx-1">
                        @php
                        $quizProxy = array_map(fn($v) => $v > 0 ? min(100, $avgQuizScore + rand(-8, 8)) : 0, $sparkStudents ?: array_fill(0, 7, 0));
                        if (!empty($quizProxy)) $quizProxy[count($quizProxy)-1] = $avgQuizScore;
                        @endphp
                        {!! $kpiSparkline($quizProxy, '#f59e0b', 'quizscore') !!}
                    </div>
                </div>

            </div>

            {{-- 14-Day School Activity Trend Chart --}}
            <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Trend</p>
                        <h3 class="text-[16px] font-bold text-[#0d326b]">School Activity — Last 14 Days</h3>
                        <p class="text-[12px] text-slate-400 mt-0.5">Lesson completions &amp; active students</p>
                    </div>
                    <div class="flex items-center gap-4 text-[11px] font-semibold flex-shrink-0">
                        <span class="flex items-center gap-1.5">
                            <span class="w-8 h-1.5 rounded-full inline-block" style="background:#0d326b;"></span>Completions
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-8 h-1.5 rounded-full inline-block" style="background:repeating-linear-gradient(90deg,#1a6fd4 0,#1a6fd4 4px,transparent 4px,transparent 7px)"></span>Students
                        </span>
                    </div>
                </div>

                <div class="bg-[#fafcff] rounded-2xl w-full relative" style="padding-bottom:38%">
                    <svg viewBox="0 0 {{ $W }} {{ $H }}" class="absolute inset-0 w-full h-full"
                         preserveAspectRatio="none" overflow="visible">
                        <defs>
                            <linearGradient id="tlGCFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#0d326b" stop-opacity=".20"/>
                                <stop offset="100%" stop-color="#0d326b" stop-opacity="0"/>
                            </linearGradient>
                            <linearGradient id="tlGSFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#1a6fd4" stop-opacity=".14"/>
                                <stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        {{-- Grid lines --}}
                        @foreach([0,25,50,75,100] as $gv)
                            @php $gy = round($pT + $plotH - ($gv/100)*$plotH, 1); @endphp
                            <line x1="{{ $pL }}" y1="{{ $gy }}" x2="{{ $pL+$plotW }}" y2="{{ $gy }}"
                                  stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                            <text x="{{ $pL - 4 }}" y="{{ $gy + 4 }}" font-size="9" fill="#94a3b8" text-anchor="end">{{ $gv }}</text>
                        @endforeach
                        {{-- Area fills --}}
                        <path d="{{ $cArea }}" fill="url(#tlGCFill)"/>
                        <path d="{{ $sArea }}" fill="url(#tlGSFill)"/>
                        {{-- Lines --}}
                        <path d="{{ $cLine }}" fill="none" stroke="#0d326b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="{{ $sLine }}" fill="none" stroke="#1a6fd4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="5,3"/>
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

            {{-- Bottom two cards: Top Classes + Needs Support --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Top Classes --}}
                <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
                        <div>
                            <h3 class="text-[15px] font-black text-[#0d326b]">Top Performing Classes</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">By avg quiz score</p>
                        </div>
                        <span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700">TOP 5</span>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @forelse($topClasses as $idx => $class)
                        <div class="flex items-center gap-3 px-6 py-3.5 hover:bg-slate-50/50 transition-colors">
                            <div class="w-7 h-7 rounded-full text-[11px] font-black flex items-center justify-center flex-shrink-0
                                {{ $idx===0?'bg-[#facc15] text-[#0d326b]':($idx===1?'bg-slate-200 text-slate-600':($idx===2?'bg-amber-100 text-amber-700':'bg-slate-100 text-slate-400')) }}">
                                {{ $idx + 1 }}
                            </div>
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-[11px] font-black text-white bg-[#0d326b]">
                                {{ strtoupper(substr($class['teacher']->first_name,0,1).substr($class['teacher']->last_name,0,1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-bold text-slate-800 truncate">
                                    {{ $class['teacher']->first_name }} {{ $class['teacher']->last_name }}
                                </p>
                                <p class="text-[11px] text-slate-400">{{ $class['student_count'] }} student{{ $class['student_count']==1?'':'s' }}</p>
                            </div>
                            <span class="text-[14px] font-black flex-shrink-0 {{ $class['avg_score']>=75?'text-emerald-600':($class['avg_score']>=50?'text-amber-600':'text-red-500') }}">
                                {{ $class['avg_score'] }}%
                            </span>
                        </div>
                        @empty
                        <div class="px-6 py-8 text-center">
                            <span class="material-symbols-outlined text-slate-200 text-[36px]">school</span>
                            <p class="text-[13px] text-slate-400 mt-2">No class data yet</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Classes Needing Support --}}
                <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500 text-[20px]">warning</span>
                        <div>
                            <h3 class="text-[15px] font-black text-[#0d326b]">Needs Support</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Lowest performing classes</p>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @forelse($needsSupport as $class)
                        <div class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/50 transition-colors">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-red-50">
                                <span class="material-symbols-outlined text-red-400 text-[18px]">person</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-bold text-slate-800 truncate">
                                    {{ $class['teacher']->first_name }} {{ $class['teacher']->last_name }}
                                </p>
                                <p class="text-[11px] text-slate-400">{{ $class['student_count'] }} students</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-[14px] font-black text-red-500">{{ $class['avg_score'] }}%</span>
                                <p class="text-[10px] text-slate-400">avg score</p>
                            </div>
                        </div>
                        @empty
                        <div class="px-6 py-8 text-center">
                            <span class="material-symbols-outlined text-emerald-300 text-[40px]">check_circle</span>
                            <p class="text-[13px] text-slate-400 mt-2">All classes performing well!</p>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

        <!-- ── Right Sidebar Column (matches Teacher Dashboard) ───────────── -->
        <div class="w-full lg:w-[340px] flex-shrink-0 flex flex-col space-y-4 lg:pl-2">

            <!-- ── Senya Insights Widget (Gold/amber gradient) ────────────── -->
            <div class="rounded-[28px] overflow-hidden shadow-sm"
                 style="background: linear-gradient(135deg, #f59e0b 0%, #facc15 60%, #fbbf24 100%);">

                <!-- Header bar -->
                <div class="px-5 pt-5 pb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-black/10 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-[18px] text-[#1e293b]">lightbulb</span>
                        </div>
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-[#1e293b] leading-none">Senya Insights</p>
                            <p class="text-[10px] font-semibold text-[#1e293b]/60 mt-0.5">Based on school data</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button id="insight-refresh"
                                type="button"
                                class="w-7 h-7 rounded-full bg-black/10 hover:bg-black/20 flex items-center justify-center transition-all active:scale-90 cursor-pointer"
                                title="Show another insight">
                            <span class="material-symbols-outlined text-[15px] text-[#1e293b]">refresh</span>
                        </button>
                    </div>
                </div>

                <!-- Insight card body -->
                @if(count($insights) > 0)
                <div class="px-5 pb-5" id="insight-body">
                    @foreach($insights as $idx => $insight)
                    <div class="insight-slide {{ $idx > 0 ? 'hidden' : '' }}"
                         data-category="{{ $insight['category'] }}"
                         data-idx="{{ $idx }}">
                        <p class="text-[10px] font-black uppercase tracking-wider mb-1 text-[#1e293b]/60">{{ $insight['category'] }}</p>
                        <p class="text-[12.5px] font-semibold text-[#1e293b] leading-relaxed">{!! $insight['text'] !!}</p>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-5 pb-5">
                    <p class="text-[12px] text-[#1e293b]/60 font-medium leading-relaxed">Insights will appear once students start completing lessons and quizzes.</p>
                </div>
                @endif
            </div>

            <!-- ── Teachers in School (styled like My Students panel) ─────── -->
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 flex flex-col overflow-hidden">
                <!-- Header -->
                <div class="px-7 pt-7 pb-4 flex items-center justify-between flex-shrink-0">
                    <div>
                        <h4 class="text-[15px] font-black text-[#0d326b]">Teachers</h4>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $totalTeachers }} in {{ $school->name ?? 'your school' }}</p>
                    </div>
                    <a href="{{ route('teacher-leader.analytics') }}"
                       class="px-3 py-1.5 rounded-lg text-[11px] font-black uppercase tracking-wider text-[#0d326b] hover:bg-[#e8eef8] transition-colors">
                        Analytics
                    </a>
                </div>

                <div class="mx-7 border-t border-slate-100 flex-shrink-0"></div>

                <!-- Scrollable teacher list -->
                <div class="overflow-y-auto divide-y divide-slate-50 flex-shrink-0" style="max-height: 460px">
                    @forelse($recentTeachers as $row)
                    @php
                        $t    = $row['teacher'];
                        $u    = $row['user'];
                        $avg  = $row['avg_score'];
                        $scoreColor = $avg >= 75 ? '#16a34a' : ($avg >= 50 ? '#d97706' : ($avg > 0 ? '#ef4444' : '#94a3b8'));
                        $scoreBg    = $avg >= 75 ? '#f0fdf4' : ($avg >= 50 ? '#fffbeb' : ($avg > 0 ? '#fef2f2' : '#f8fafc'));
                        $statusColor = ($u?->status === 'active') ? '#16a34a' : '#94a3b8';
                        $statusBg    = ($u?->status === 'active') ? '#f0fdf4' : '#f8fafc';
                    @endphp
                    <div class="flex items-center gap-4 px-7 py-4 hover:bg-slate-50 transition-colors">

                        <!-- Avatar with status dot -->
                        <div class="relative flex-shrink-0">
                            <img src="{{ $u?->avatarUrl() ?? 'https://ui-avatars.com/api/?name=T&background=0d326b&color=fff&size=128&bold=true&rounded=true' }}"
                                 class="w-11 h-11 rounded-full shadow-sm object-cover bg-[#0d326b]"
                                 alt="{{ $t->first_name }}"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(substr($t->first_name,0,1).substr($t->last_name,0,1)) }}&background=0d326b&color=fff&size=128&bold=true&rounded=true'">
                            <!-- Online status dot -->
                            <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white"
                                  style="background: {{ $statusColor }}"></span>
                        </div>

                        <!-- Teacher info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <p class="text-[13px] font-bold text-slate-800 truncate">
                                    {{ $t->first_name }} {{ $t->last_name }}
                                </p>
                                @if($avg > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black flex-shrink-0"
                                      style="background:{{ $scoreBg }};color:{{ $scoreColor }}">
                                    {{ $avg }}%
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold flex-shrink-0 bg-slate-100 text-slate-400">
                                    No data
                                </span>
                                @endif
                            </div>

                            <!-- Progress bar = avg quiz score -->
                            <div class="flex items-center gap-2 mb-1">
                                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500"
                                         style="width:{{ max(4, $avg) }}%;background:{{ $scoreColor }}"></div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-[10px] text-slate-400">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[11px] icon-outline">group</span>
                                    {{ $row['student_count'] }} {{ Str::plural('student', $row['student_count']) }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[11px] icon-outline">task_alt</span>
                                    {{ $row['lessons_done'] }} done
                                </span>
                                @if($row['active_students'] > 0)
                                <span class="flex items-center gap-1 text-emerald-500 font-semibold">
                                    <span class="material-symbols-outlined text-[11px]">bolt</span>
                                    {{ $row['active_students'] }} active
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center">
                        <span class="material-symbols-outlined text-slate-200 text-[36px]">school</span>
                        <p class="text-[13px] text-slate-400 mt-2">No teachers yet</p>
                    </div>
                    @endforelse
                </div>

                <!-- Footer link -->
                <div class="px-7 py-3 border-t border-slate-100 flex-shrink-0">
                    <a href="{{ route('teacher-leader.analytics') }}"
                       class="block w-full py-3 rounded-xl text-center text-[12px] font-black uppercase tracking-wider text-white transition-all hover:opacity-90 shadow-sm"
                       style="background:linear-gradient(135deg,#0d326b 0%,#1a6fd4 100%)">
                        View Full Analytics
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>{{-- /skeleton-hide --}}

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Calendar Navigation (matching Teacher Dashboard) ──────────────────────
    const weekLabel = document.getElementById('week-label');
    const prevButton = document.getElementById('week-prev');
    const nextButton = document.getElementById('week-next');
    const weekDays = Array.from(document.querySelectorAll('.week-day'));
    const today = new Date('{{ $today->format('Y-m-d') }}');

    function updateWeek(startDate) {
        const start = new Date(startDate);
        const monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"];
        const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

        if (weekLabel) {
            weekLabel.textContent = dayNames[start.getDay()] + ', ' + start.getDate() + ' ' + monthNames[start.getMonth()];
        }

        weekDays.forEach((cell, index) => {
            const date = new Date(start);
            date.setDate(start.getDate() + index);
            const dateStr = date.toISOString().slice(0, 10);
            cell.dataset.date = dateStr;
            cell.textContent = date.getDate();
            const isToday = dateStr === today.toISOString().slice(0, 10);
            cell.classList.toggle('bg-[#1C3D7A]', isToday);
            cell.classList.toggle('text-white', isToday);
            cell.classList.toggle('text-slate-600', !isToday);
            cell.classList.toggle('hover:bg-slate-100', !isToday);
            cell.classList.toggle('cursor-pointer', !isToday);
        });
    }

    let currentStart = new Date('{{ $startOfWeek->format('Y-m-d') }}');
    if (prevButton) {
        prevButton.addEventListener('click', function() {
            currentStart.setDate(currentStart.getDate() - 7);
            updateWeek(currentStart);
        });
    }
    if (nextButton) {
        nextButton.addEventListener('click', function() {
            currentStart.setDate(currentStart.getDate() + 7);
            updateWeek(currentStart);
        });
    }

    // ── Sparkline Tooltips ───────────────────────────────────────────────────
    document.querySelectorAll('.tl-kpi-sparkline-wrap').forEach(function(wrap) {
        const tooltip   = wrap.querySelector('.tl-kpi-tooltip');
        const tipDay    = wrap.querySelector('.tl-tip-day');
        const tipDate   = wrap.querySelector('.tl-tip-date');
        const tipVal    = wrap.querySelector('.tl-tip-val');
        const dots      = wrap.querySelectorAll('.tl-kpi-dot');
        const halos     = wrap.querySelectorAll('.tl-kpi-halo');
        const hits      = wrap.querySelectorAll('.tl-kpi-hit');

        hits.forEach(function(hit) {
            hit.addEventListener('mouseenter', function() {
                const idx   = parseInt(hit.dataset.idx);
                const val   = hit.dataset.val;
                const day   = hit.dataset.day;
                const date  = hit.dataset.date;
                const color = hit.dataset.color;

                if (tipDay)  tipDay.textContent  = day;
                if (tipDate) tipDate.textContent = date;
                if (tipVal)  tipVal.textContent  = val;

                const svgEl = wrap.querySelector('svg');
                const svgRect = svgEl.getBoundingClientRect();
                const cx = parseFloat(hit.getAttribute('cx'));
                const cy = parseFloat(hit.getAttribute('cy'));
                const vb = svgEl.viewBox.baseVal;
                const scaleX = svgRect.width  / vb.width;
                const scaleY = svgRect.height / vb.height;
                const px = cx * scaleX;

                tooltip.style.left = px + 'px';
                tooltip.style.top  = '0px';
                tooltip.classList.remove('opacity-0', 'scale-95');
                tooltip.classList.add('opacity-100', 'scale-100');

                dots.forEach(function(d, i) {
                    d.setAttribute('r', i === idx ? '5' : '2.5');
                    d.style.strokeWidth = i === idx ? '2.5' : '2';
                });
                halos.forEach(function(h, i) {
                    h.style.fillOpacity = i === idx ? '0.18' : '0';
                });
            });
            hit.addEventListener('mouseleave', function() {
                tooltip.classList.add('opacity-0', 'scale-95');
                tooltip.classList.remove('opacity-100', 'scale-100');
                dots.forEach(function(d, i) {
                    d.setAttribute('r', i === dots.length - 1 ? '3.5' : '3');
                    d.style.strokeWidth = '2';
                });
                halos.forEach(function(h) { h.style.fillOpacity = '0'; });
            });
        });
    });

    // ── Senya Insights Slider (matches teacher dashboard) ────────────────────
    (function () {
        const slides  = Array.from(document.querySelectorAll('.insight-slide'));
        const btn     = document.getElementById('insight-refresh');
        const total   = slides.length;
        if (!total) return;

        let current = 0;

        function showSlide(idx) {
            slides[current].classList.add('hidden');
            current = idx;
            slides[current].classList.remove('hidden');
        }

        function pickRandom() {
            if (total <= 1) return;
            let next;
            do { next = Math.floor(Math.random() * total); } while (next === current);
            showSlide(next);
        }

        if (btn) {
            btn.addEventListener('click', function () {
                const icon = btn.querySelector('.material-symbols-outlined');
                if (icon) {
                    icon.style.transition = 'transform 0.4s ease';
                    icon.style.transform = 'rotate(360deg)';
                    setTimeout(function () {
                        icon.style.transition = 'none';
                        icon.style.transform = 'rotate(0deg)';
                    }, 420);
                }
                pickRandom();
            });
        }

        // Swipe support
        const body = document.getElementById('insight-body');
        if (body) {
            let startX = 0;
            body.addEventListener('touchstart', function(e) { startX = e.touches[0].clientX; }, { passive: true });
            body.addEventListener('touchend', function(e) {
                const dx = e.changedTouches[0].clientX - startX;
                if (Math.abs(dx) > 40) pickRandom();
            }, { passive: true });
        }
    })();
});
</script>
@endsection
