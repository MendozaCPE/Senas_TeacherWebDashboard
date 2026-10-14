@extends('layouts.ict')
@section('title', 'System Dashboard')
@section('content')

{{-- ══════════════════════════════════════════════════════════════════════
     SKELETON
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="page-skeleton" class="flex flex-col gap-5 w-full pt-4" aria-hidden="true">
    <div class="skeleton skeleton-card w-full" style="min-height:130px;border-radius:28px;"></div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @for($i=0;$i<4;$i++)
        <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 border border-slate-100 shadow-sm flex flex-col gap-3 min-h-[130px]">
            <div class="flex items-center justify-between"><div class="skeleton h-3 rounded w-28"></div><div class="skeleton w-10 h-10 rounded-xl"></div></div>
            <div class="skeleton h-9 rounded w-16 mt-1"></div>
            <div class="skeleton h-3 rounded w-24"></div>
        </div>
        @endfor
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
        <div class="lg:col-span-2 bg-white rounded-[22px] shadow-sm border border-slate-100 p-6">
            <div class="skeleton rounded-2xl w-full" style="padding-bottom:40%;"></div>
        </div>
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            @for($j=0;$j<5;$j++)
            <div class="flex items-center gap-3 px-6 py-3.5">
                <div class="skeleton skeleton-circle w-9 h-9"></div>
                <div class="flex-1 flex flex-col gap-2"><div class="skeleton h-3 rounded w-3/4"></div><div class="skeleton h-2 rounded w-1/2"></div></div>
            </div>
            @endfor
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

/* ── Usage trend chart coords ── */
$W = 600; $H = 240; $pL = 28; $pR = 28; $pT = 16; $pB = 28;
$plotW = $W-$pL-$pR; $plotH = $H-$pT-$pB; $bot = $pT+$plotH;
$n     = count($usageTrend);
$peak  = max(1, collect($usageTrend)->max(fn($d) => max($d['teachers'], $d['students'])));

$tPts = []; $sPts = [];
foreach ($usageTrend as $i => $d) {
    $x = $n > 1 ? $pL + ($i/($n-1))*$plotW : $pL+$plotW/2;
    $tPts[] = ['x'=>round($x,2), 'y'=>round($pT+$plotH-($d['teachers']/$peak)*$plotH, 2)];
    $sPts[] = ['x'=>round($x,2), 'y'=>round($pT+$plotH-($d['students']/$peak)*$plotH,  2)];
}
$tLine = $bezier($tPts, $bot); $tArea = $bezier($tPts, $bot, true);
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
            <h2 class="text-[24px] font-black text-white leading-tight mb-1">System Adoption Overview</h2>
            <p class="text-[13px] text-white/70 font-medium">
                Welcome back, <span class="text-white font-bold">{{ Auth::user()->teacher->first_name ?? Auth::user()->name }}</span>.
                Monitoring adoption for <span class="text-white font-bold">{{ $school->name ?? 'your school' }}</span>.
            </p>
        </div>
        <div class="relative z-10 flex-shrink-0 pr-10 hidden lg:flex items-center gap-8">
            @foreach([['label'=>'Teachers','val'=>$totalTeachers],['label'=>'Students','val'=>$totalStudents],['label'=>'Adoption','val'=>$adoptionRate.'%']] as $bi)
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

        {{-- Teachers --}}
        <div style="border-radius:24px;padding:22px 24px;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%)">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Classroom Teachers</span>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px] text-white">school</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-white tracking-tight">{{ $totalTeachers }}</p>
            <p class="text-[12px] text-white/70 font-medium">registered in school</p>
        </div>

        {{-- Grade Leaders --}}
        <div style="border-radius:24px;padding:22px 24px;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:#fff">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Grade Leaders</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">verified</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ $totalTeacherLeaders }}</p>
            <p class="text-[12px] text-[#1a6fd4] font-medium">leader accounts</p>
        </div>

        {{-- Active Users this week --}}
        <div class="font-sans" style="border-radius:24px;padding:22px 24px;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #e2e8f0;background:#ffffff">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-black">Active This Week</span>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-black flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">bolt</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-black tracking-tight">{{ $activeTeachers + $activeStudents }}</p>
            <p class="text-[12px] text-black font-medium">{{ $activeTeachers }} faculty &bull; {{ $activeStudents }} students</p>
        </div>

        {{-- System Adoption Rate --}}
        <div class="text-amber-950" style="border-radius:24px;padding:22px 24px;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border:1px solid rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22)">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Adoption Rate</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 text-amber-950 flex items-center justify-center backdrop-blur-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">trending_up</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-amber-950 tracking-tight">{{ $adoptionRate }}%</p>
            <p class="text-[12px] text-amber-950/80 font-bold">active / total users</p>
        </div>
    </div>

    {{-- ── MAIN GRID: Usage Trend (2/3) + Recent Teachers (1/3) ───────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

        {{-- 14-Day Platform Usage Trend (pure inline SVG) --}}
        <div class="lg:col-span-2 bg-white rounded-[22px] shadow-sm border border-slate-100 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Platform Activity</p>
                    <h3 class="text-[16px] font-bold text-[#0d326b]">System Usage — Last 14 Days</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Faculty sign-ins/actions and student app activity per day</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-semibold flex-shrink-0">
                    <span class="flex items-center gap-1.5">
                        <span class="w-8 h-1.5 rounded-full inline-block" style="background:#0d326b"></span>Faculty
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-8 h-1.5 rounded-full inline-block" style="background:repeating-linear-gradient(90deg,#1a6fd4 0,#1a6fd4 4px,transparent 4px,transparent 7px)"></span>Students
                    </span>
                </div>
            </div>

            <div id="ictUsageChartWrap" class="bg-[#fafcff] rounded-2xl w-full relative z-10" style="padding-bottom:40%">
                <div id="ictUsageChartTooltip" role="status" aria-live="polite"
                     class="pointer-events-none absolute z-30 opacity-0 scale-95 transition-all duration-150 -translate-x-1/2 -translate-y-full bg-[#0d326b] text-white text-[11px] rounded-xl shadow-xl px-3.5 py-2.5 whitespace-nowrap border border-blue-400/20">
                    <div class="font-extrabold text-[11.5px] tracking-wide pb-1.5 border-b border-white/10" id="ictUsageTipDate"></div>
                    <div class="mt-1.5 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between gap-5"><span class="flex items-center gap-1.5 text-blue-200"><span class="w-2 h-2 rounded-full bg-white"></span>Faculty</span><span id="ictUsageTipStaff" class="font-black"></span></div>
                        <div class="flex items-center justify-between gap-5"><span class="flex items-center gap-1.5 text-blue-200"><span class="w-2 h-2 rounded-full bg-[#60a5fa]"></span>Students</span><span id="ictUsageTipStudents" class="font-black text-[#93c5fd]"></span></div>
                    </div>
                    <div class="absolute left-1/2 -bottom-1 -translate-x-1/2 w-2 h-2 bg-[#0d326b] rotate-45 border-r border-b border-blue-400/20"></div>
                </div>
                <svg id="ictUsageChartSvg" viewBox="0 0 {{ $W }} {{ $H }}" class="absolute inset-0 w-full h-full"
                     preserveAspectRatio="none" overflow="visible">
                    <defs>
                        <linearGradient id="ictTFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#0d326b" stop-opacity=".20"/>
                            <stop offset="100%" stop-color="#0d326b" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="ictSFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#1a6fd4" stop-opacity=".14"/>
                            <stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="ictTLine" x1="0" y1="0" x2="100%" y2="0">
                            <stop offset="0%" stop-color="#1e4b8f"/>
                            <stop offset="100%" stop-color="#071c3f"/>
                        </linearGradient>
                        <linearGradient id="ictSLine" x1="0" y1="0" x2="100%" y2="0">
                            <stop offset="0%" stop-color="#3b82f6"/>
                            <stop offset="100%" stop-color="#1a6fd4"/>
                        </linearGradient>
                    </defs>

                    @foreach([0,25,50,75,100] as $gv)
                        @php $gy = round($pT+$plotH-($gv/100)*$plotH,1); @endphp
                        <line x1="{{ $pL }}" y1="{{ $gy }}" x2="{{ $pL+$plotW }}" y2="{{ $gy }}"
                              stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                    @endforeach

                    <path d="{{ $tArea }}" fill="url(#ictTFill)"/>
                    <path d="{{ $sArea }}" fill="url(#ictSFill)"/>
                    <path d="{{ $tLine }}" fill="none" stroke="url(#ictTLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="{{ $sLine }}" fill="none" stroke="url(#ictSLine)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="5,3"/>

                    @foreach($usageTrend as $i => $d)
                        <line class="ict-usage-crosshair ict-usage-crosshair-{{ $i }}" x1="{{ $tPts[$i]['x'] }}" y1="{{ $pT }}" x2="{{ $tPts[$i]['x'] }}" y2="{{ $bot }}" stroke="#0d326b" stroke-width="1.2" stroke-dasharray="3,3" stroke-opacity="0" style="pointer-events:none;transition:stroke-opacity .15s ease"/>
                        <circle class="ict-usage-staff-halo ict-usage-staff-halo-{{ $i }}" cx="{{ $tPts[$i]['x'] }}" cy="{{ $tPts[$i]['y'] }}" r="9" fill="#0d326b" fill-opacity="0" style="pointer-events:none;transition:fill-opacity .18s ease"/>
                        <circle class="ict-usage-student-halo ict-usage-student-halo-{{ $i }}" cx="{{ $sPts[$i]['x'] }}" cy="{{ $sPts[$i]['y'] }}" r="8" fill="#1a6fd4" fill-opacity="0" style="pointer-events:none;transition:fill-opacity .18s ease"/>
                        <circle class="ict-usage-staff-dot ict-usage-staff-dot-{{ $i }}" data-last="{{ $i === count($tPts)-1 ? '1' : '0' }}" cx="{{ $tPts[$i]['x'] }}" cy="{{ $tPts[$i]['y'] }}"
                                r="{{ $i===count($tPts)-1?4.5:3 }}"
                                fill="{{ $i===count($tPts)-1?'#0d326b':'#1e4b8f' }}"
                                stroke="white" stroke-width="2" style="pointer-events:none;transition:r .18s ease"/>
                        <circle class="ict-usage-student-dot ict-usage-student-dot-{{ $i }}" data-last="{{ $i === count($sPts)-1 ? '1' : '0' }}" cx="{{ $sPts[$i]['x'] }}" cy="{{ $sPts[$i]['y'] }}" r="{{ $i===count($sPts)-1?4:2.5 }}" fill="#1a6fd4" stroke="white" stroke-width="1.8" style="pointer-events:none;transition:r .18s ease"/>
                    @endforeach

                    @foreach($usageTrend as $i => $d)
                        @if($i % 2 === 0 || $i === count($usageTrend)-1)
                            <text x="{{ $tPts[$i]['x'] }}" y="{{ $H-10 }}"
                                  font-size="10" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $d['label'] }}</text>
                        @endif
                    @endforeach

                    @foreach($usageTrend as $i => $d)
                        @php $colW = $n > 1 ? $plotW / ($n - 1) : $plotW; $hitX = $n > 1 ? max(0, $tPts[$i]['x'] - $colW / 2) : $pL; @endphp
                        <rect class="ict-usage-hit" data-idx="{{ $i }}" x="{{ $hitX }}" y="0" width="{{ $colW }}" height="{{ $H }}" fill="transparent" tabindex="0" role="button"
                              aria-label="{{ $d['label'] }}: {{ $d['teachers'] }} active faculty, {{ $d['students'] }} active students"
                              data-label="{{ $d['label'] }}" data-staff="{{ $d['teachers'] }}" data-students="{{ $d['students'] }}"
                              data-cx="{{ $tPts[$i]['x'] }}" data-cy-staff="{{ $tPts[$i]['y'] }}" data-cy-students="{{ $sPts[$i]['y'] }}"/>
                    @endforeach
                </svg>
            </div>
        </div>

        {{-- Recently Active Teachers --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Recently Active</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Staff by last activity</p>
                </div>
                <a href="{{ route('ict.accounts') }}"
                   class="text-[11px] font-black uppercase tracking-wider text-[#0d326b] hover:underline">View All</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($recentTeachers as $u)
                <div class="flex items-center gap-3 px-6 py-3.5">
                    <img src="{{ $u->avatarUrl() }}"
                         class="w-8 h-8 rounded-full object-cover border border-slate-100 flex-shrink-0"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($u->name) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate">{{ $u->teacher->first_name ?? '' }} {{ $u->teacher->last_name ?? '' }}</p>
                        <p class="text-[11px] text-slate-400">{{ ucfirst(str_replace('_',' ',$u->role)) }}</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0
                        {{ $u->status==='active'?'bg-emerald-50 text-emerald-700':'bg-slate-100 text-slate-400' }}">
                        {{ ucfirst($u->status) }}
                    </span>
                </div>
                @empty
                <div class="px-6 py-8 text-center">
                    <span class="material-symbols-outlined text-slate-200 text-[36px]">group</span>
                    <p class="text-[13px] text-slate-400 mt-2">No staff yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── BOTTOM: Adoption Progress + Lessons Activity ────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Adoption breakdown --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-6">
            <h3 class="text-[15px] font-black text-[#0d326b] mb-4">Adoption Breakdown</h3>
            <div class="flex flex-col gap-4">
                @php
                $rows = [
                    ['label'=>'Classroom Teachers', 'active'=>$activeTeachers,  'total'=>max(1,$totalTeachers),  'color'=>'#0d326b'],
                    ['label'=>'Students',            'active'=>$activeStudents,  'total'=>max(1,$totalStudents),  'color'=>'#1a6fd4'],
                ];
                @endphp
                @foreach($rows as $row)
                @php $pct = round($row['active']/$row['total']*100); @endphp
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="text-[13px] font-semibold text-slate-700">{{ $row['label'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] text-slate-400">{{ $row['active'] }}/{{ $row['total'] }} active</span>
                            <span class="text-[14px] font-black text-[#0d326b]">{{ $pct }}%</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full transition-all" style="width:{{ $pct }}%;background:{{ $row['color'] }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-6">
            <h3 class="text-[15px] font-black text-[#0d326b] mb-4">Platform Totals</h3>
            <div class="grid grid-cols-2 gap-3">
                @php
                $qs = [
                    ['icon'=>'group',         'label'=>'Total Students',  'val'=>number_format($totalStudents)],
                    ['icon'=>'task_alt',       'label'=>'Lessons Done',    'val'=>number_format($totalLessonsDone)],
                    ['icon'=>'supervisor_account','label'=>'Staff Accounts', 'val'=>number_format($totalTeachers+$totalTeacherLeaders)],
                    ['icon'=>'computer',      'label'=>'ICT Staff',       'val'=>number_format($totalIct)],
                ];
                @endphp
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

</div>{{-- /skeleton-hide --}}

<script>
    (function () {
        const wrap = document.getElementById('ictUsageChartWrap');
        const svg = document.getElementById('ictUsageChartSvg');
        const tip = document.getElementById('ictUsageChartTooltip');
        if (!wrap || !svg || !tip) return;

        const date = document.getElementById('ictUsageTipDate');
        const staff = document.getElementById('ictUsageTipStaff');
        const students = document.getElementById('ictUsageTipStudents');
        const svgWidth = {{ $W }};
        const svgHeight = {{ $H }};

        function show(hit) {
            const index = hit.dataset.idx;
            const crosshair = wrap.querySelector('.ict-usage-crosshair-' + index);
            const staffHalo = wrap.querySelector('.ict-usage-staff-halo-' + index);
            const studentHalo = wrap.querySelector('.ict-usage-student-halo-' + index);
            const staffDot = wrap.querySelector('.ict-usage-staff-dot-' + index);
            const studentDot = wrap.querySelector('.ict-usage-student-dot-' + index);

            date.textContent = hit.dataset.label || '';
            staff.textContent = hit.dataset.staff || '0';
            students.textContent = hit.dataset.students || '0';

            wrap.querySelectorAll('.ict-usage-crosshair').forEach(el => el.setAttribute('stroke-opacity', el === crosshair ? '0.4' : '0'));
            wrap.querySelectorAll('.ict-usage-staff-halo, .ict-usage-student-halo').forEach(el => el.setAttribute('fill-opacity', (el === staffHalo || el === studentHalo) ? '0.22' : '0'));
            wrap.querySelectorAll('.ict-usage-staff-dot').forEach(el => el.setAttribute('r', el === staffDot ? '5.5' : (el.dataset.last === '1' ? '4.5' : '3')));
            wrap.querySelectorAll('.ict-usage-student-dot').forEach(el => el.setAttribute('r', el === studentDot ? '5' : (el.dataset.last === '1' ? '4' : '2.5')));

            const bounds = wrap.getBoundingClientRect();
            const x = (parseFloat(hit.dataset.cx) / svgWidth) * bounds.width;
            const y = (Math.min(parseFloat(hit.dataset.cyStaff), parseFloat(hit.dataset.cyStudents)) / svgHeight) * bounds.height;
            const halfTip = (tip.offsetWidth || 160) / 2;
            const left = Math.max(halfTip + 8, Math.min(bounds.width - halfTip - 8, x));
            tip.style.left = left + 'px';
            tip.style.top = Math.max(12, y - 10) + 'px';
            const arrow = tip.querySelector('div.absolute');
            if (arrow) arrow.style.transform = 'translateX(calc(-50% + ' + (x - left) + 'px)) rotate(45deg)';
            tip.classList.remove('opacity-0', 'scale-95');
            tip.classList.add('opacity-100', 'scale-100');
        }

        function hide() {
            tip.classList.add('opacity-0', 'scale-95');
            tip.classList.remove('opacity-100', 'scale-100');
            wrap.querySelectorAll('.ict-usage-crosshair').forEach(el => el.setAttribute('stroke-opacity', '0'));
            wrap.querySelectorAll('.ict-usage-staff-halo, .ict-usage-student-halo').forEach(el => el.setAttribute('fill-opacity', '0'));
            wrap.querySelectorAll('.ict-usage-staff-dot').forEach(el => el.setAttribute('r', el.dataset.last === '1' ? '4.5' : '3'));
            wrap.querySelectorAll('.ict-usage-student-dot').forEach(el => el.setAttribute('r', el.dataset.last === '1' ? '4' : '2.5'));
        }

        wrap.querySelectorAll('.ict-usage-hit').forEach(hit => {
            hit.addEventListener('pointerenter', () => show(hit));
            hit.addEventListener('focus', () => show(hit));
            hit.addEventListener('pointerleave', event => {
                if (!hit.contains(event.relatedTarget)) hide();
            });
            hit.addEventListener('blur', hide);
        });
    })();
</script>
@endsection

