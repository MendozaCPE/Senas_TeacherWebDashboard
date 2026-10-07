@extends('layouts.grade-leader')
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

            <div class="skeleton skeleton-card h-[92px] rounded-2xl"></div>

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

$rawMax = max($peak, 10);
if ($rawMax <= 10) {
    $maxVal = 10;
    $ticks = [0, 2, 5, 8, 10];
} elseif ($rawMax <= 25) {
    $maxVal = 25;
    $ticks = [0, 5, 10, 15, 20, 25];
} elseif ($rawMax <= 50) {
    $maxVal = 50;
    $ticks = [0, 10, 20, 30, 40, 50];
} elseif ($rawMax <= 100) {
    $maxVal = 100;
    $ticks = [0, 25, 50, 75, 100];
} else {
    $step = (int) ceil($rawMax / 4 / 10) * 10;
    $maxVal = $step * 4;
    $ticks = range(0, $maxVal, $step);
}

$cPts = []; $sPts = [];
foreach ($activityTrend as $i => $d) {
    $x = $n > 1 ? $pL + ($i / ($n - 1)) * $plotW : $pL + $plotW / 2;
    $cPts[] = ['x' => round($x,2), 'y' => round($pT + $plotH - ($d['completions'] / $maxVal) * $plotH, 2)];
    $sPts[] = ['x' => round($x,2), 'y' => round($pT + $plotH - ($d['students']    / $maxVal) * $plotH, 2)];
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
$supportCount = $moreDataNeeded->count();

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
        'text'     => "<strong>{$activeStudents}</strong> active students ({$activeRate}%) this week across {$activeClassrooms} active classrooms."
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

// 5. FSL Mastery Insight
$advCount = (int) ($fslMasteryData->firstWhere('label', 'Advanced')['count'] ?? 0);
$intCount = (int) ($fslMasteryData->firstWhere('label', 'Intermediate')['count'] ?? 0);
$progressedCount = $advCount + $intCount;
if ($progressedCount > 0) {
    $progressedPct = $totalStudents > 0 ? round(($progressedCount / $totalStudents) * 100) : 0;
    $insights[] = [
        'category' => 'SIGN LANGUAGE MASTERY',
        'icon'     => 'sign_language',
        'text'     => "<strong>{$progressedCount}</strong> students ({$progressedPct}%) have advanced beyond Beginner in Filipino Sign Language proficiency!"
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

            <!-- Academic Session -->
            <div class="flex items-center justify-between gap-4 bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                        <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                    </span>
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Academic Session</span>
                        <div class="flex flex-wrap items-center gap-2 mt-0.5">
                            <span class="text-[14px] font-black text-slate-800">School Year {{ $activeSyName }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-400 block mt-0.5">{{ \Carbon\Carbon::parse($activeSyStartDate)->format('M d, Y') }} &ndash; Present</span>
                    </div>
                </div>
                @if($totalTeachers > 0)
                <button type="button"
                        onclick="openNotifyTransitionModal(null, 'All Teachers')"
                        class="flex-shrink-0 inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 rounded-xl text-[10px] sm:text-[11px] font-black uppercase tracking-wide text-white shadow-sm hover:opacity-95 transition-all max-w-[132px] sm:max-w-none"
                        style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
                    <span class="material-symbols-outlined text-[15px]">calendar_month</span>
                    <span class="text-center leading-tight">Notify for S.Y.<br class="sm:hidden"> {{ $targetSyName }}</span>
                </button>
                @endif
            </div>

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
                            {{ $greeting }}, Grade Leader {{ $leaderFirstName }}!
                        </h2>
                        <p class="text-[13px] text-white/70 font-medium leading-relaxed mb-5">
                            Here is a summary of your school's<br>academic progress today.
                        </p>
                        <a href="{{ route('grade-leader.lessons') }}"
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

            <!-- Stats Row: 4 KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

                {{-- Active Classrooms --}}
                <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-full bg-[#0d326b] flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">meeting_room</span>
                        </div>
                        <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Active Classrooms</h3>
                    </div>
                    <div>
                        <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $activeClassrooms }}</p>
                        <p class="text-[12px] font-medium text-[#1a6fd4] mt-2">with enrolled students</p>
                    </div>
                </div>

                {{-- Total Teachers --}}
                <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-full bg-[#1e4b8f] flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">supervisor_account</span>
                        </div>
                        <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Teachers</h3>
                    </div>
                    <div>
                        <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $totalTeachers }}</p>
                        <p class="text-[12px] font-medium text-[#1a6fd4] mt-2 whitespace-nowrap">All school teachers</p>
                    </div>
                </div>

                {{-- Total Students --}}
                <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-full bg-[#1a6fd4] flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">group</span>
                        </div>
                        <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Enrolled Students</h3>
                    </div>
                    <div>
                        <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $totalStudents }}</p>
                        <p class="text-[12px] font-medium text-[#1a6fd4] mt-2">{{ $activeStudents }} active this week</p>
                    </div>
                </div>

                {{-- School Performance --}}
                <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">school</span>
                        </div>
                        <h3 class="text-[14px] font-semibold text-slate-700 leading-none">School Performance</h3>
                    </div>
                    <div>
                        <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ number_format($avgQuizScore, 1) }}%</p>
                        <p class="text-[11px] font-medium text-[#1a6fd4] mt-2 whitespace-nowrap">Quiz average</p>
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

                <div class="bg-[#fafcff] rounded-2xl w-full relative z-10" id="actChartWrap" style="padding-bottom:38%">
                    <!-- Floating Tooltip (matching Teacher Dashboard style) -->
                    <div id="actChartTooltip"
                         class="pointer-events-none absolute z-30 opacity-0 scale-95 transition-all duration-150 -translate-x-1/2 -translate-y-full bg-[#0d326b] text-white text-[11px] rounded-xl shadow-xl px-3.5 py-2.5 whitespace-nowrap border border-blue-400/20 mb-2">
                        <div class="flex items-center justify-between gap-3 text-[11px] leading-tight pb-1.5 border-b border-white/10">
                            <span class="font-extrabold text-white text-[11.5px] tracking-wide" id="actTipDate">Oct 1</span>
                            <span class="text-blue-200/80 text-[10px] font-medium" id="actTipDay">Today</span>
                        </div>
                        <div class="mt-1.5 flex flex-col gap-1.5">
                            <div class="flex items-center justify-between gap-3">
                                <span class="flex items-center gap-1.5 text-blue-200 text-[10.5px]">
                                    <span class="w-2 h-2 rounded-full bg-white"></span>
                                    Lessons Completed
                                </span>
                                <span class="text-[12px] font-black text-white" id="actTipCompletions">0</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="flex items-center gap-1.5 text-blue-200 text-[10.5px]">
                                    <span class="w-2 h-2 rounded-full bg-[#60a5fa]"></span>
                                    Active Students
                                </span>
                                <span class="text-[12px] font-black text-[#93c5fd]" id="actTipStudents">0</span>
                            </div>
                        </div>
                        <div class="act-tip-arrow absolute left-1/2 -bottom-1 -translate-x-1/2 w-2 h-2 bg-[#0d326b] rotate-45 border-r border-b border-blue-400/20"></div>
                    </div>

                    <svg id="activityChartSvg" viewBox="0 0 {{ $W }} {{ $H }}" class="absolute inset-0 w-full h-full overflow-visible"
                         preserveAspectRatio="none">
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
                        {{-- Grid lines & Y-axis labels --}}
                        @foreach($ticks as $gv)
                            @php $gy = round($pT + $plotH - ($gv / $maxVal) * $plotH, 1); @endphp
                            <line x1="{{ $pL }}" y1="{{ $gy }}" x2="{{ $pL + $plotW }}" y2="{{ $gy }}"
                                  stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                            <text x="{{ $pL - 6 }}" y="{{ $gy + 3.5 }}" font-size="9" fill="#94a3b8" text-anchor="end" font-weight="500">{{ $gv }}</text>
                        @endforeach

                        {{-- Area fills --}}
                        <path d="{{ $cArea }}" fill="url(#tlGCFill)"/>
                        <path d="{{ $sArea }}" fill="url(#tlGSFill)"/>

                        {{-- Lines --}}
                        <path d="{{ $cLine }}" fill="none" stroke="#0d326b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="{{ $sLine }}" fill="none" stroke="#1a6fd4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="5,3"/>

                        {{-- Interactive crosshairs & glowing halos --}}
                        @foreach($activityTrend as $i => $d)
                            {{-- Vertical Crosshair Line --}}
                            <line class="act-crosshair act-ch-{{ $i }}" data-idx="{{ $i }}"
                                  x1="{{ $cPts[$i]['x'] }}" y1="{{ $pT }}"
                                  x2="{{ $cPts[$i]['x'] }}" y2="{{ $pT + $plotH }}"
                                  stroke="#0d326b" stroke-width="1.2" stroke-dasharray="3,3" stroke-opacity="0"
                                  style="transition: stroke-opacity 0.15s ease; pointer-events: none;"/>

                            {{-- Active Students Halo --}}
                            <circle class="act-s-halo act-s-halo-{{ $i }}" data-idx="{{ $i }}"
                                    cx="{{ $sPts[$i]['x'] }}" cy="{{ $sPts[$i]['y'] }}"
                                    r="8" fill="#1a6fd4" fill-opacity="0"
                                    style="transition: fill-opacity 0.18s ease, r 0.18s ease; pointer-events: none;"/>

                            {{-- Lessons Completed Halo --}}
                            <circle class="act-c-halo act-c-halo-{{ $i }}" data-idx="{{ $i }}"
                                    cx="{{ $cPts[$i]['x'] }}" cy="{{ $cPts[$i]['y'] }}"
                                    r="9" fill="#0d326b" fill-opacity="0"
                                    style="transition: fill-opacity 0.18s ease, r 0.18s ease; pointer-events: none;"/>

                            {{-- Active Students Dot --}}
                            <circle class="act-s-dot act-s-dot-{{ $i }}" data-idx="{{ $i }}"
                                    data-is-last="{{ $i === count($sPts) - 1 ? '1' : '0' }}"
                                    cx="{{ $sPts[$i]['x'] }}" cy="{{ $sPts[$i]['y'] }}"
                                    r="{{ $i === count($sPts) - 1 ? 4 : 2.5 }}"
                                    fill="#1a6fd4" stroke="white" stroke-width="1.8"
                                    style="transition: r 0.18s ease, stroke-width 0.18s ease; pointer-events: none;"/>

                            {{-- Lessons Completed Dot --}}
                            <circle class="act-c-dot act-c-dot-{{ $i }}" data-idx="{{ $i }}"
                                    data-is-last="{{ $i === count($cPts) - 1 ? '1' : '0' }}"
                                    cx="{{ $cPts[$i]['x'] }}" cy="{{ $cPts[$i]['y'] }}"
                                    r="{{ $i === count($cPts) - 1 ? 4.5 : 3 }}"
                                    fill="{{ $i === count($cPts) - 1 ? '#0d326b' : '#1e4b8f' }}"
                                    stroke="white" stroke-width="2"
                                    style="transition: r 0.18s ease, stroke-width 0.18s ease; pointer-events: none;"/>
                        @endforeach

                        {{-- X-axis labels --}}
                        @foreach($activityTrend as $i => $d)
                            @if($i % 2 === 0 || $i === count($activityTrend)-1)
                                <text x="{{ $cPts[$i]['x'] }}" y="{{ $H - 8 }}"
                                      font-size="10" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $d['label'] }}</text>
                            @endif
                        @endforeach

                        {{-- Interactive Hit Columns (transparent click/hover overlays) --}}
                        @foreach($activityTrend as $i => $d)
                            @php
                                $colW = $n > 1 ? $plotW / ($n - 1) : $plotW;
                                $hitX = $n > 1 ? max(0, $cPts[$i]['x'] - $colW / 2) : $pL;
                                $hitW = $colW;
                            @endphp
                            <rect class="act-point-hit cursor-pointer" data-idx="{{ $i }}"
                                  x="{{ $hitX }}" y="0" width="{{ $hitW }}" height="{{ $H }}"
                                  fill="transparent"
                                  data-label="{{ $d['label'] }}"
                                  data-day="{{ $d['day'] ?? '' }}"
                                  data-date="{{ $d['date'] ?? $d['label'] }}"
                                  data-completions="{{ $d['completions'] }}"
                                  data-students="{{ $d['students'] }}"
                                  data-cx="{{ $cPts[$i]['x'] }}"
                                  data-cy-completions="{{ $cPts[$i]['y'] }}"
                                  data-cy-students="{{ $sPts[$i]['y'] }}"/>
                        @endforeach
                    </svg>
                </div>
            </div>
        </div>

        <!-- ── Right Sidebar Column (matches Teacher Dashboard) ───────────── -->
        <div class="w-full lg:w-[340px] flex-shrink-0 flex flex-col space-y-4 lg:pl-2">

            <!-- ── Senya Insights Widget (Gold/amber gradient) ────────────── -->
            <div class="rounded-[28px] overflow-hidden shadow-sm flex-shrink-0"
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
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 flex flex-col overflow-hidden flex-1">
                <!-- Header -->
                <div class="px-7 pt-7 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 flex-shrink-0">
                    <div>
                        <h4 class="text-[15px] font-black text-[#0d326b]">Teachers</h4>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Active S.Y. <span class="font-bold text-slate-700">{{ $activeSyName ?? '2025-2026' }}</span></p>
                    </div>
                    <a href="{{ route('grade-leader.reports') }}"
                       class="px-3 py-1.5 rounded-lg text-[11px] font-black uppercase tracking-wider text-[#0d326b] hover:bg-[#e8eef8] transition-colors">
                        View All
                    </a>
                </div>

                <div class="mx-7 border-t border-slate-100 flex-shrink-0"></div>

                <!-- Scrollable teacher list (flex-1 expands to bottom of row) -->
                <div class="overflow-y-auto divide-y divide-slate-50 flex-1 min-h-0">
                    @forelse($recentTeachers as $row)
                    @php
                        $t    = $row['teacher'];
                        $u    = $row['user'];
                        $avg  = $row['avg_score'];
                        $scoreColor = $avg >= 75 ? '#1a6fd4' : ($avg >= 50 ? '#1e4b8f' : ($avg > 0 ? '#0d326b' : '#94a3b8'));
                        $scoreBg    = $avg >= 75 ? '#eff6ff' : ($avg >= 50 ? '#dbeafe' : ($avg > 0 ? '#bfdbfe' : '#f8fafc'));
                        $statusColor = ($u?->status === 'active') ? '#1a6fd4' : '#94a3b8';
                        $statusBg    = ($u?->status === 'active') ? '#eff6ff' : '#f8fafc';
                        $transStatus = $row['transition_status'] ?? 'none';
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
                            <div class="flex flex-col gap-1 mb-1">
                                <p class="text-[13px] font-bold text-slate-800 leading-snug break-words">
                                    {{ $t->first_name }} {{ $t->last_name }}
                                </p>
                            </div>

                            <!-- Progress bar = avg quiz score -->
                            <div class="flex items-center gap-2 mb-1">
                                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500"
                                         style="width:{{ max(4, $avg) }}%;background:linear-gradient(90deg,#1e4b8f 0%,#1a6fd4 100%)"></div>
                                </div>
                                @if($avg > 0)
                                <span class="inline-flex items-center justify-center min-w-[52px] px-2.5 py-1 rounded-full text-[12px] font-black text-[#1a6fd4] bg-blue-50 flex-shrink-0">
                                    {{ $avg }}%
                                </span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2 text-[10px] text-slate-400">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[11px] icon-outline">group</span>
                                    {{ $row['student_count'] }} {{ Str::plural('student', $row['student_count']) }}
                                </span>
                                @if($transStatus === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="material-symbols-outlined text-[11px]">check_circle</span>
                                        S.Y. {{ $targetSyName }} Active
                                    </span>
                                @elseif($transStatus === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="material-symbols-outlined text-[11px]">schedule</span>
                                        S.Y. {{ $targetSyName }} Pending
                                    </span>
                                @else
                                    <button type="button"
                                            onclick="openNotifyTransitionModal({{ $t->id }}, '{{ addslashes($t->first_name . ' ' . $t->last_name) }}')"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wide text-white shadow-sm hover:opacity-90 transition-opacity"
                                            style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
                                        <span class="material-symbols-outlined text-[11px]">send</span>
                                        Notify S.Y. {{ $targetSyName }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="flex-1 flex flex-col items-center justify-center px-6 py-8 text-center">
                        <span class="material-symbols-outlined text-slate-200 text-[36px]">school</span>
                        <p class="text-[13px] text-slate-400 mt-2">No teachers yet</p>
                    </div>
                    @endforelse
                </div>

                <!-- Footer link -->
                <div class="px-7 py-3 border-t border-slate-100 flex-shrink-0 mt-auto">
                    <a href="{{ route('grade-leader.reports') }}"
                       class="block w-full py-3 rounded-xl text-center text-[12px] font-black uppercase tracking-wider text-white transition-all hover:opacity-90 shadow-sm"
                       style="background:linear-gradient(135deg,#0d326b 0%,#1a6fd4 100%)">
                        View Full Reports
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- Bottom Row: Top Performing Classes | More Data Needed | Student Program Type (full width across the row, matching Teacher Dashboard) -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 pt-4">

        <!-- Top Performing Classes -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-[#0d326b]">Top Performing Classes</h3>
                        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">By avg quiz score</p>
                    </div>
                    <span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-[#eff6ff] text-[#0d326b]">TOP 5</span>
                </div>
                <div class="divide-y divide-slate-50 mt-1">
                    @forelse($topClasses as $idx => $class)
                    <div class="flex items-center gap-3 py-3 hover:bg-slate-50/50 transition-colors">
                        <div class="w-7 h-7 rounded-full text-[11px] font-black flex items-center justify-center flex-shrink-0
                            {{ $idx===0?'bg-[#0d326b] text-white':($idx===1?'bg-[#1e4b8f] text-white':($idx===2?'bg-[#1a6fd4] text-white':'bg-[#eff6ff] text-[#0d326b]')) }}">
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
                        <span class="text-[14px] font-black flex-shrink-0 {{ $class['avg_score']>=75?'text-[#1a6fd4]':($class['avg_score']>=50?'text-[#1e4b8f]':'text-[#0d326b]') }}">
                            {{ $class['avg_score'] }}%
                        </span>
                    </div>
                    @empty
                    <div class="py-8 text-center">
                        <span class="material-symbols-outlined text-slate-200 text-[36px]">school</span>
                        <p class="text-[13px] text-slate-400 mt-2">No quiz data yet</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- FSL Mastery Distribution -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-36 h-36 bg-blue-50/50 rounded-full opacity-60 pointer-events-none"></div>

            <div>
                <!-- Header -->
                <div class="flex items-start justify-between pb-4 border-b border-slate-100 relative z-10">
                    <div>
                        <h3 class="text-base font-bold text-[#0d326b]">FSL Mastery Distribution</h3>
                        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">School-wide proficiency breakdown</p>
                    </div>
                    <span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-[#eff6ff] text-[#0d326b] flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px] text-[#1a6fd4]">sign_language</span>
                        {{ $totalStudents }} STUDENTS
                    </span>
                </div>

                <!-- Individual Tier Rows -->
                <div class="mt-4 space-y-3 relative z-10">
                    @foreach($fslMasteryData as $tier)
                    <div class="p-2.5 rounded-2xl hover:bg-slate-50/80 transition-colors border border-slate-50">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0"
                                     style="background: {{ $tier['badge_bg'] }}; color: {{ $tier['badge_text'] }};">
                                    <span class="material-symbols-outlined text-[14px]">{{ $tier['icon'] }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[12.5px] font-bold text-slate-800 leading-none truncate">{{ $tier['label'] }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">{{ $tier['sublabel'] }}</p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-[13px] font-black text-[#0d326b]">{{ $tier['count'] }}</span>
                                <span class="text-[10px] font-bold text-slate-400">({{ $tier['pct'] }}%)</span>
                            </div>
                        </div>
                        <!-- Individual Progress Track -->
                        <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700"
                                 style="width: {{ $tier['pct'] }}%; background: linear-gradient(90deg, {{ $tier['bar_from'] }} 0%, {{ $tier['bar_to'] }} 100%);"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Bottom Insight Pill -->
            @php
                $mTotal = $fslMasteryData->sum('count');
                $advIntCount = ($fslMasteryData->firstWhere('label', 'Advanced')['count'] ?? 0) + ($fslMasteryData->firstWhere('label', 'Intermediate')['count'] ?? 0);
                $advIntPct   = $mTotal > 0 ? round(($advIntCount / $mTotal) * 100) : 0;
            @endphp
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-medium relative z-10">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#1a6fd4]"></span>
                    Progression beyond Beginner:
                </span>
                <span class="font-extrabold text-[#0d326b]">{{ $advIntCount }} students ({{ $advIntPct }}%)</span>
            </div>
        </div>

        <!-- Student Program Type -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 relative overflow-hidden flex flex-col justify-between">
            <div class="absolute -top-10 -right-10 w-36 h-36 bg-blue-50/50 rounded-full opacity-60 pointer-events-none"></div>

            <div class="mb-4 relative z-10">
                <h3 class="text-base font-bold text-[#0d326b]">Student Program Type</h3>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">Breakdown across all enrolled students</p>
            </div>

            @php
                $ptTotal  = $programDonut->sum('count');
                $ptActive = $programDonut->count();
                $ptGap    = $ptActive > 1 ? 0.08 : 0;
                $ptCurAngle = -M_PI / 2;
                $ptCx = 80; $ptCy = 80; $ptInnerR = 40;
                $ptMaxCount = $ptActive > 0 ? max(1, $programDonut->max('count')) : 1;
                $ptPaths = [];
                foreach ($programDonut as $seg) {
                    $fraction  = $ptTotal > 0 ? $seg['count'] / $ptTotal : 0;
                    $angleSpan = $fraction * 2 * M_PI;
                    $outerR    = round(56 + 18 * ($seg['count'] / $ptMaxCount), 1);
                    if ($ptActive === 1) {
                        $segStart = $ptCurAngle;
                        $segEnd   = $ptCurAngle + 2 * M_PI - 0.001;
                    } else {
                        $segStart = $ptCurAngle + $ptGap / 2;
                        $segEnd   = $ptCurAngle + $angleSpan - $ptGap / 2;
                        if ($segEnd <= $segStart) { $segStart = $ptCurAngle; $segEnd = $ptCurAngle + $angleSpan; }
                    }
                    $x1 = round($ptCx + $outerR * cos($segStart), 2);
                    $y1 = round($ptCy + $outerR * sin($segStart), 2);
                    $x2 = round($ptCx + $outerR * cos($segEnd), 2);
                    $y2 = round($ptCy + $outerR * sin($segEnd), 2);
                    $x3 = round($ptCx + $ptInnerR * cos($segEnd), 2);
                    $y3 = round($ptCy + $ptInnerR * sin($segEnd), 2);
                    $x4 = round($ptCx + $ptInnerR * cos($segStart), 2);
                    $y4 = round($ptCy + $ptInnerR * sin($segStart), 2);
                    $largeArc = ($segEnd - $segStart > M_PI) ? 1 : 0;
                    $ptPaths[] = array_merge((array) $seg, [
                        'd'      => "M {$x1} {$y1} A {$outerR} {$outerR} 0 {$largeArc} 1 {$x2} {$y2} L {$x3} {$y3} A {$ptInnerR} {$ptInnerR} 0 {$largeArc} 0 {$x4} {$y4} Z",
                        'outerR' => $outerR,
                    ]);
                    $ptCurAngle += $angleSpan;
                }
            @endphp

            @if($ptTotal === 0)
                <div class="flex-1 flex items-center justify-center text-sm text-slate-400 italic py-12 relative z-10">No students enrolled yet</div>
            @else
            <div class="flex items-center gap-6 relative z-10 my-auto">
                {{-- Donut --}}
                <div class="relative w-36 h-36 flex-shrink-0 flex items-center justify-center">
                    <div id="ptDonutTooltip" class="pointer-events-none absolute z-30 opacity-0 transition-opacity duration-150 left-1/2 top-0 -translate-x-1/2 -translate-y-[110%] bg-[#0d326b] text-white text-[11px] font-bold px-3 py-1.5 rounded-lg shadow-lg whitespace-nowrap">
                        <span id="ptDonutLabel"></span>: <span id="ptDonutValue"></span>
                        <div class="absolute left-1/2 -bottom-1 -translate-x-1/2 w-2 h-2 bg-[#0d326b] rotate-45"></div>
                    </div>
                    <svg class="w-full h-full overflow-visible" viewBox="0 0 160 160">
                        <defs>
                            <filter id="ptSliceShadow" x="-10%" y="-10%" width="120%" height="120%">
                                <feDropShadow dx="0" dy="1.5" stdDeviation="1.5" flood-opacity="0.08"/>
                            </filter>
                            @foreach($ptPaths as $seg)
                            <linearGradient id="{{ $seg['grad_id'] }}" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="{{ $seg['grad_from'] }}"/>
                                <stop offset="100%" stop-color="{{ $seg['grad_to'] }}"/>
                            </linearGradient>
                            @endforeach
                        </defs>
                        <circle cx="80" cy="80" r="54" fill="none" stroke="#f1f5f9" stroke-width="26" opacity="0.6"/>
                        @foreach($ptPaths as $seg)
                        <path class="pt-donut-seg cursor-pointer transition-all duration-200 hover:opacity-90 hover:brightness-110"
                              d="{{ $seg['d'] }}"
                              fill="url(#{{ $seg['grad_id'] }})"
                              stroke="#ffffff" stroke-width="2" stroke-linejoin="round"
                              filter="url(#ptSliceShadow)"
                              data-label="{{ $seg['label'] }}"
                              data-value="{{ $seg['count'] }} students ({{ $seg['pct'] }}%)"></path>
                        @endforeach
                        <circle cx="80" cy="80" r="39" fill="#ffffff" filter="url(#ptSliceShadow)"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                        <span class="text-2xl font-black text-[#0d326b] leading-none">{{ $ptTotal }}</span>
                        <span class="text-[8.5px] font-extrabold uppercase tracking-widest text-slate-400 mt-0.5">Students</span>
                    </div>
                </div>
                {{-- Legend --}}
                <div class="space-y-3 flex-1 min-w-0">
                    @foreach($ptPaths as $seg)
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $seg['color'] }}"></div>
                            <span class="text-[12px] font-semibold text-slate-600 truncate">{{ $seg['label'] }}</span>
                        </div>
                        <span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $seg['count'] }} <span class="text-[10px] font-semibold text-slate-400">({{ $seg['pct'] }}%)</span></span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
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

    // ── Program Type Donut Tooltip ────────────────────────────────────────────
    (function () {
        const tip   = document.getElementById('ptDonutTooltip');
        const label = document.getElementById('ptDonutLabel');
        const value = document.getElementById('ptDonutValue');
        if (!tip) return;
        document.querySelectorAll('.pt-donut-seg').forEach(function (seg) {
            seg.addEventListener('mouseenter', function () {
                if (label) label.textContent = seg.dataset.label;
                if (value) value.textContent = seg.dataset.value;
                tip.classList.remove('opacity-0');
                tip.classList.add('opacity-100');
            });
            seg.addEventListener('mouseleave', function () {
                tip.classList.add('opacity-0');
                tip.classList.remove('opacity-100');
            });
        });
    })();

    // ── School Activity 14-Day Line Chart Hover Tooltip ────────────────────────
    (function () {
        const wrap = document.getElementById('actChartWrap');
        const tip  = document.getElementById('actChartTooltip');
        const svg  = document.getElementById('activityChartSvg');
        if (!wrap || !tip || !svg) return;

        const tipDate        = document.getElementById('actTipDate');
        const tipDay         = document.getElementById('actTipDay');
        const tipCompletions = document.getElementById('actTipCompletions');
        const tipStudents    = document.getElementById('actTipStudents');
        const tipArrow       = tip.querySelector('.act-tip-arrow');

        const svgW = {{ $W }};
        const svgH = {{ $H }};
        let hideTimer = null;

        const hits = wrap.querySelectorAll('.act-point-hit');
        hits.forEach(function (hit) {
            const idx       = hit.dataset.idx;
            const crosshair = wrap.querySelector('.act-ch-' + idx);
            const cHalo     = wrap.querySelector('.act-c-halo-' + idx);
            const sHalo     = wrap.querySelector('.act-s-halo-' + idx);
            const cDot      = wrap.querySelector('.act-c-dot-' + idx);
            const sDot      = wrap.querySelector('.act-s-dot-' + idx);

            function activate() {
                if (hideTimer) {
                    clearTimeout(hideTimer);
                    hideTimer = null;
                }

                // Deactivate any other active indicators
                wrap.querySelectorAll('.act-crosshair').forEach(function (c) {
                    if (c !== crosshair) c.setAttribute('stroke-opacity', '0');
                });
                wrap.querySelectorAll('.act-c-halo, .act-s-halo').forEach(function (h) {
                    if (h !== cHalo && h !== sHalo) h.setAttribute('fill-opacity', '0');
                });
                wrap.querySelectorAll('.act-c-dot').forEach(function (d) {
                    if (d !== cDot) {
                        const isL = d.dataset.isLast === '1';
                        d.setAttribute('r', isL ? '4.5' : '3');
                        d.setAttribute('stroke-width', '2');
                    }
                });
                wrap.querySelectorAll('.act-s-dot').forEach(function (d) {
                    if (d !== sDot) {
                        const isL = d.dataset.isLast === '1';
                        d.setAttribute('r', isL ? '4' : '2.5');
                        d.setAttribute('stroke-width', '1.8');
                    }
                });

                // Update tooltip content
                if (tipDate)        tipDate.textContent        = hit.dataset.date || hit.dataset.label;
                if (tipDay)         tipDay.textContent         = hit.dataset.day || '';
                if (tipCompletions) tipCompletions.textContent = hit.dataset.completions || '0';
                if (tipStudents)    tipStudents.textContent    = hit.dataset.students || '0';

                // Highlight hovered point elements
                if (crosshair) crosshair.setAttribute('stroke-opacity', '0.4');
                if (cHalo) {
                    cHalo.setAttribute('r', '11');
                    cHalo.setAttribute('fill-opacity', '0.22');
                }
                if (sHalo) {
                    sHalo.setAttribute('r', '10');
                    sHalo.setAttribute('fill-opacity', '0.22');
                }
                if (cDot) {
                    cDot.setAttribute('r', '5.5');
                    cDot.setAttribute('stroke-width', '2.5');
                }
                if (sDot) {
                    sDot.setAttribute('r', '5');
                    sDot.setAttribute('stroke-width', '2.5');
                }

                // Calculate tooltip position
                const wrapRect = wrap.getBoundingClientRect();
                const tipW     = tip.offsetWidth || 150;
                const cxSvg    = parseFloat(hit.dataset.cx);
                const cyComp   = parseFloat(hit.dataset.cyCompletions);
                const cyStud   = parseFloat(hit.dataset.cyStudents);
                const minCy    = Math.min(cyComp, cyStud);

                const pointCenterX = (cxSvg / svgW) * wrapRect.width;
                const pointCenterY = (minCy / svgH) * wrapRect.height;

                // Clamp horizontally so tooltip stays neatly inside card bounds
                const minLeft    = (tipW / 2) + 8;
                const maxLeft    = wrapRect.width - (tipW / 2) - 8;
                const clampedLeft = Math.max(minLeft, Math.min(maxLeft, pointCenterX));

                tip.style.left = clampedLeft + 'px';
                tip.style.top  = Math.max(12, pointCenterY - 10) + 'px';

                // Position arrow directly over the point
                if (tipArrow) {
                    const arrowOffset = pointCenterX - clampedLeft;
                    tipArrow.style.transform = 'translateX(calc(-50% + ' + arrowOffset + 'px)) rotate(45deg)';
                }

                tip.classList.remove('opacity-0', 'scale-95');
                tip.classList.add('opacity-100', 'scale-100');
            }

            function deactivate() {
                if (crosshair) crosshair.setAttribute('stroke-opacity', '0');
                if (cHalo) {
                    cHalo.setAttribute('r', '9');
                    cHalo.setAttribute('fill-opacity', '0');
                }
                if (sHalo) {
                    sHalo.setAttribute('r', '8');
                    sHalo.setAttribute('fill-opacity', '0');
                }
                if (cDot) {
                    const isL = cDot.dataset.isLast === '1';
                    cDot.setAttribute('r', isL ? '4.5' : '3');
                    cDot.setAttribute('stroke-width', '2');
                }
                if (sDot) {
                    const isL = sDot.dataset.isLast === '1';
                    sDot.setAttribute('r', isL ? '4' : '2.5');
                    sDot.setAttribute('stroke-width', '1.8');
                }

                hideTimer = setTimeout(function () {
                    tip.classList.remove('opacity-100', 'scale-100');
                    tip.classList.add('opacity-0', 'scale-95');
                }, 50);
            }

            hit.addEventListener('mouseenter', activate);
            hit.addEventListener('mouseleave', deactivate);
            hit.addEventListener('touchstart', function () {
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
    })();
});

// ── School Year Transition Notification Modal ────────────────────────────────
let notifyTransitionTeacherId = null;

function openNotifyTransitionModal(teacherId, teacherName) {
    notifyTransitionTeacherId = teacherId;
    const modal = document.getElementById('notifyTransitionModal');
    const targetLabel = document.getElementById('notifyTargetTeacherName');
    if (targetLabel) {
        targetLabel.textContent = teacherName || 'All Eligible Teachers';
    }
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeNotifyTransitionModal() {
    const modal = document.getElementById('notifyTransitionModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    notifyTransitionTeacherId = null;
}

function submitNotifyTransition() {
    const btn = document.getElementById('btnSubmitNotifyTransition');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="inline-block animate-spin mr-1">⏳</span> Sending...';
    }

    const payload = {
        _token: '{{ csrf_token() }}',
        all: notifyTransitionTeacherId === null,
        teacher_id: notifyTransitionTeacherId,
    };

    fetch('{{ route("grade-leader.notify-transition") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        closeNotifyTransitionModal();
        if (data.success) {
            alert(data.message || 'Notification sent successfully!');
            window.location.reload();
        } else {
            alert(data.message || 'Failed to send notification.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }
    })
    .catch(err => {
        closeNotifyTransitionModal();
        alert('A network error occurred. Please try again.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    });
}
</script>

<!-- ── Notify School Year Transition Modal ─────────────────────────────────── -->
<div id="notifyTransitionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
        <div class="p-6 bg-gradient-to-br from-indigo-50 to-blue-50 border-b border-indigo-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-600/20 flex-shrink-0">
                <span class="material-symbols-outlined text-[26px]">calendar_month</span>
            </div>
            <div>
                <h3 class="text-[17px] font-black text-slate-800">School Year Transition</h3>
                <p class="text-[12px] text-slate-500 font-medium">Initiate transition for School Year <span class="font-bold text-indigo-700">{{ $targetSyName ?? 'New S.Y.' }}</span></p>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-[13px] text-slate-600 space-y-2">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 font-semibold text-slate-700">
                    <span>Target Recipient:</span>
                    <span id="notifyTargetTeacherName" class="font-bold text-indigo-700">All Teachers</span>
                </div>
                <div class="flex items-center justify-between font-semibold text-slate-700">
                    <span>New School Year:</span>
                    <span class="font-bold text-slate-800">{{ $targetSyName ?? 'Next Year' }}</span>
                </div>
            </div>

            <div class="text-[12.5px] text-slate-500 leading-relaxed space-y-1.5">
                <p class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-[16px] text-emerald-600 mt-0.5 flex-shrink-0">check_circle</span>
                    <span>Teacher(s) will receive an actionable transition notification in their portal.</span>
                </p>
                <p class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-[16px] text-emerald-600 mt-0.5 flex-shrink-0">check_circle</span>
                    <span>No data is erased. Historical records remain fully accessible in Reports and Analytics.</span>
                </p>
                <p class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-[16px] text-emerald-600 mt-0.5 flex-shrink-0">check_circle</span>
                    <span>Duplicate pending notifications are automatically prevented.</span>
                </p>
            </div>
        </div>

        <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
            <button type="button"
                    onclick="closeNotifyTransitionModal()"
                    class="px-5 py-2.5 rounded-xl text-[12px] font-bold text-slate-600 hover:bg-slate-200/70 transition-colors">
                Cancel
            </button>
            <button type="button"
                    id="btnSubmitNotifyTransition"
                    onclick="submitNotifyTransition()"
                    class="px-5 py-2.5 rounded-xl text-[12px] font-black uppercase tracking-wider text-white shadow-md hover:opacity-95 transition-all"
                    style="background:linear-gradient(135deg,#4F46E5 0%,#3730A3 100%)">
                Send Notification
            </button>
        </div>
    </div>
</div>
@endsection


