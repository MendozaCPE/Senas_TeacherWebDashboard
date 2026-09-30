@extends('layouts.teacher-leader')
@section('title', 'School Dashboard')
@section('content')

{{-- ══════════════════════════════════════════════════════════════════════
     SKELETON
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="page-skeleton" class="flex flex-col gap-5 w-full pt-4" aria-hidden="true">
    <div class="skeleton skeleton-card w-full" style="min-height:130px;border-radius:28px;"></div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @for($i=0;$i<4;$i++)
        <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 border border-slate-100 shadow-sm flex flex-col gap-3 min-h-[130px]">
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
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
        <div class="lg:col-span-2 bg-white rounded-[22px] shadow-sm border border-slate-100 p-6 flex flex-col gap-4">
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <div class="flex flex-col gap-2"><div class="skeleton h-5 rounded w-36"></div><div class="skeleton h-3 rounded w-56"></div></div>
            </div>
            <div class="skeleton rounded-2xl w-full" style="padding-bottom:40%;"></div>
        </div>
        <div class="flex flex-col gap-4">
            <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-5 flex flex-col gap-4">
                <div class="skeleton h-4 rounded w-32"></div>
                @for($i=0;$i<5;$i++)
                <div class="flex items-center gap-3">
                    <div class="skeleton skeleton-circle w-9 h-9"></div>
                    <div class="flex-1 flex flex-col gap-2"><div class="skeleton h-3 rounded w-3/4"></div><div class="skeleton h-2 rounded w-1/2"></div></div>
                </div>
                @endfor
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @for($i=0;$i<2;$i++)
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50"><div class="skeleton h-4 rounded w-28"></div></div>
            @for($j=0;$j<4;$j++)
            <div class="flex items-center gap-3 px-6 py-3.5">
                <div class="skeleton skeleton-circle w-9 h-9 flex-shrink-0"></div>
                <div class="flex-1 flex flex-col gap-2"><div class="skeleton h-3 rounded w-3/4"></div><div class="skeleton h-2 rounded w-1/2"></div></div>
            </div>
            @endfor
        </div>
        @endfor
    </div>
</div>

@php
/* ── Inline SVG bezier helper (mirrors admin dashboard) ── */
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

/* ── Chart coords for activity trend ── */
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

{{-- ══════════════════════════════════════════════════════════════════════
     REAL CONTENT
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-col gap-5 w-full pt-4 skeleton-hide">

    {{-- ── WELCOME BANNER ──────────────────────────────────────────────── --}}
    <div class="rounded-[28px] relative overflow-hidden flex items-center"
         style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%);min-height:130px">
        <div class="absolute top-0 right-44 w-44 h-44 rounded-full opacity-10 bg-white"></div>
        <div class="absolute -bottom-8 left-1/3 w-32 h-32 rounded-full opacity-10 bg-white"></div>
        <div class="relative z-10 px-10 py-7 flex-1">
            <h2 class="text-[24px] font-black text-white leading-tight mb-1">School Academic Overview</h2>
            <p class="text-[13px] text-white/70 font-medium">
                Welcome back, <span class="text-white font-bold">{{ Auth::user()->teacher->first_name ?? Auth::user()->name }}</span>.
                All data is scoped to <span class="text-white font-bold">{{ $school->name ?? 'your school' }}</span>.
            </p>
        </div>
        <div class="relative z-10 flex-shrink-0 pr-10 hidden lg:flex items-center gap-8">
            @foreach([['label'=>'Teachers','val'=>$totalTeachers],['label'=>'Students','val'=>$totalStudents],['label'=>'Completion','val'=>$completionRate.'%']] as $bi)
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
                    <span class="material-symbols-outlined text-[20px] text-white">supervisor_account</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-white tracking-tight">{{ $totalTeachers }}</p>
            <p class="text-[12px] text-white/70 font-medium">in {{ $school->name ?? 'your school' }}</p>
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
            <p class="text-[12px] text-[#1a6fd4] font-medium">{{ $activeStudents }} active this week</p>
        </div>

        {{-- Card 3: Lesson Completion — emerald --}}
        <div style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #d1fae5;background:#f0fdf4;">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Completion Rate</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">task_alt</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-emerald-700 tracking-tight">{{ $completionRate }}%</p>
            <p class="text-[12px] text-emerald-600 font-medium">{{ number_format($totalCompleted) }} / {{ number_format($totalAssigned) }} lessons</p>
        </div>

        {{-- Card 4: Avg Quiz Score — amber gradient --}}
        <div class="text-amber-950" style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border:1px solid rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22);">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Avg Quiz Score</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 text-amber-950 flex items-center justify-center backdrop-blur-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">insights</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-amber-950 tracking-tight">{{ $avgQuizScore }}%</p>
            <p class="text-[12px] text-amber-950/80 font-bold">school-wide average</p>
        </div>

    </div>

    {{-- ── MAIN GRID: Chart (2/3) + Top Classes (1/3) ─────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

        {{-- Activity Trend Chart (pure inline SVG — no Chart.js) --}}
        <div class="lg:col-span-2 bg-white rounded-[22px] shadow-sm border border-slate-100 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Trend</p>
                    <h3 class="text-[16px] font-bold text-[#0d326b]">School Activity</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Lesson completions &amp; active students — last 14 days</p>
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

            <div class="bg-[#fafcff] rounded-2xl w-full relative" style="padding-bottom:40%">
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
                        <linearGradient id="tlGCLine" x1="0" y1="0" x2="100%" y2="0">
                            <stop offset="0%" stop-color="#1e4b8f"/>
                            <stop offset="100%" stop-color="#071c3f"/>
                        </linearGradient>
                        <linearGradient id="tlGSLine" x1="0" y1="0" x2="100%" y2="0">
                            <stop offset="0%" stop-color="#3b82f6"/>
                            <stop offset="100%" stop-color="#1a6fd4"/>
                        </linearGradient>
                    </defs>

                    {{-- Grid lines --}}
                    @foreach([0,25,50,75,100] as $gv)
                        @php $gy = round($pT + $plotH - ($gv/100)*$plotH, 1); @endphp
                        <line x1="{{ $pL }}" y1="{{ $gy }}" x2="{{ $pL+$plotW }}" y2="{{ $gy }}"
                              stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                    @endforeach

                    {{-- Area fills --}}
                    <path d="{{ $cArea }}" fill="url(#tlGCFill)"/>
                    <path d="{{ $sArea }}" fill="url(#tlGSFill)"/>

                    {{-- Lines --}}
                    <path d="{{ $cLine }}" fill="none" stroke="url(#tlGCLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="{{ $sLine }}" fill="none" stroke="url(#tlGSLine)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="5,3"/>

                    {{-- Completion dots --}}
                    @foreach($cPts as $i => $p)
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}"
                                r="{{ $i===count($cPts)-1 ? 4.5 : 3 }}"
                                fill="{{ $i===count($cPts)-1 ? '#0d326b' : '#1e4b8f' }}"
                                stroke="white" stroke-width="2"/>
                    @endforeach

                    {{-- X-axis labels --}}
                    @foreach($activityTrend as $i => $d)
                        @if($i % 2 === 0 || $i === count($activityTrend)-1)
                            <text x="{{ $cPts[$i]['x'] }}" y="{{ $H - 10 }}"
                                  font-size="10" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $d['label'] }}</text>
                        @endif
                    @endforeach
                </svg>
            </div>
        </div>

        {{-- Top Classes --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Top Classes</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">By average quiz score</p>
                </div>
                <span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700">TOP 5</span>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($topClasses as $idx => $class)
                <div class="flex items-center gap-3 px-6 py-3.5">
                    <div class="w-7 h-7 rounded-full text-[11px] font-black flex items-center justify-center flex-shrink-0
                        {{ $idx===0?'bg-[#facc15] text-[#0d326b]':($idx===1?'bg-slate-200 text-slate-600':($idx===2?'bg-amber-100 text-amber-700':'bg-slate-100 text-slate-400')) }}">
                        {{ $idx + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate">
                            {{ $class['teacher']->first_name }} {{ $class['teacher']->last_name }}
                        </p>
                        <p class="text-[11px] text-slate-400">{{ $class['student_count'] }} student{{ $class['student_count']==1?'':'s' }}</p>
                    </div>
                    <span class="text-[13px] font-black flex-shrink-0 {{ $class['avg_score']>=75?'text-emerald-600':($class['avg_score']>=50?'text-amber-600':'text-red-500') }}">
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
    </div>

    {{-- ── BOTTOM GRID ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Classes Needing Support --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-500 text-[20px]">warning</span>
                <div>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Needs Academic Support</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Classes with lowest average performance</p>
                </div>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($needsSupport as $class)
                <div class="flex items-center gap-4 px-6 py-4">
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

        {{-- Recent Student Engagement --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Recent Engagement</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Most recently active students</p>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[20px]">bolt</span>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($recentEngagement as $student)
                <div class="flex items-center gap-3 px-6 py-3.5">
                    <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center font-black text-[11px] text-white"
                         style="background:linear-gradient(135deg,#0d326b,#1a6fd4)">
                        {{ strtoupper(substr($student->first_name,0,1).substr($student->last_name,0,1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate">{{ $student->first_name }} {{ $student->last_name }}</p>
                        <p class="text-[11px] text-slate-400">Grade {{ $student->grade_level }} &bull; {{ $student->section }}</p>
                    </div>
                    <span class="text-[11px] text-slate-400 flex-shrink-0">
                        {{ $student->last_activity_date ? \Carbon\Carbon::parse($student->last_activity_date)->diffForHumans() : 'No activity' }}
                    </span>
                </div>
                @empty
                <div class="px-6 py-8 text-center">
                    <span class="material-symbols-outlined text-slate-200 text-[36px]">bolt</span>
                    <p class="text-[13px] text-slate-400 mt-2">No recent activity</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>{{-- /skeleton-hide --}}
@endsection
