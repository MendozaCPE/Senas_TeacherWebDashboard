@extends('layouts.app')
@section('bg-class', 'bg-[#f4f7f9]')
@section('title', 'Analytics')
@section('content')

<style>
:root {
    --navy-950: #071c3f;
    --navy-900: #0d326b;
    --navy-700: #1e4b8f;
    --navy-500: #1a6fd4;
    --navy-400: #3b82f6;
    --navy-300: #93c5fd;
    --navy-200: #bfdbfe;
    --navy-100: #dbeafe;
    --navy-50:  #eff6ff;
    --analytics-slate: #64748b;
}

/* ── Stat cards (matches Students tab) ───────────────────────────── */
.stat-card { border-radius: 20px; padding: 20px; position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(13,50,107,.12); }

/* ── Panels (matches Students tab white cards) ───────────────────── */
.panel { background: #fff; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 1px 2px rgba(13,50,107,.04); padding: 24px; }
.panel:hover { box-shadow: 0 12px 32px rgba(13,50,107,.08); }

.heatmap-cell {
    border-radius: 16px;
    min-height: 84px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    transition: transform .15s;
}
.heatmap-cell:hover { transform: translateY(-2px); }

.donut-chart {
    width: 132px;
    height: 132px;
    border-radius: 9999px;
    background: #f1f5f9;
    display: grid;
    place-items: center;
}
.donut-chart span { display: block; font-size: 1rem; font-weight: 800; color: var(--navy-900); }
</style>

<div class="space-y-6">

    {{-- ══════════ HEADER (matches Students tab) ══════════ --}}
    <div class="flex items-start justify-between">
        <div>
            <p class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-1">Analytics</p>
            <h2 class="text-[32px] font-semibold text-[#0d326b] leading-tight">Class Performance Summary</h2>
            <p class="text-[13px] text-slate-400 font-medium mt-1 max-w-2xl">Review scores, mastery, progress, and completion for your class at a glance.</p>
        </div>
        <a href="{{ route('analytics.export-pdf') }}"
            class="bg-gradient-to-r from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] hover:opacity-90 text-white px-5 py-3 rounded-xl text-[14px] font-bold transition-all flex items-center space-x-2 shadow-md mt-1 border border-[#0d326b]/20 shrink-0">
            <span class="material-symbols-outlined text-[20px]">download</span>
            <span>Export PDF Report</span>
        </a>
    </div>

    {{-- ══════════ STAT CARDS ══════════ --}}
    @php
        // Fixed navy palette applied in-view only — backend values for accent/iconColor are ignored
        // so every card reads as a shade of navy regardless of what the controller sends.
        $statShades = [
            ['bg' => 'linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%)', 'text' => 'white',  'iconBg' => 'bg-white/10', 'iconColor' => 'text-white'],
            ['bg' => '#ffffff', 'text' => 'navy', 'iconBg' => 'bg-[#e8eef8]', 'iconColor' => 'text-[#1e4b8f]'],
            ['bg' => '#ffffff', 'text' => 'navy', 'iconBg' => 'bg-[#e8eef8]', 'iconColor' => 'text-[#1e4b8f]'],
            ['bg' => '#ffffff', 'text' => 'navy', 'iconBg' => 'bg-[#e8eef8]', 'iconColor' => 'text-[#1e4b8f]'],
        ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @foreach($classSummary as $i => $stat)
            @php $shade = $statShades[$i % count($statShades)]; @endphp
            <div class="stat-card {{ $shade['text'] === 'white' ? 'text-white' : 'bg-white border border-slate-100 shadow-sm' }}"
                 style="{{ $shade['text'] === 'white' ? 'background:' . $shade['bg'] : '' }}">
                @if($shade['text'] === 'white')
                    <div class="absolute -top-7 -right-7 w-28 h-28 bg-white/5 rounded-full"></div>
                    <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white/5 rounded-full"></div>
                @endif
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center {{ $shade['iconBg'] }}">
                            <span class="material-symbols-outlined text-[19px] {{ $shade['iconColor'] }}">{{ $stat['icon'] }}</span>
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest {{ $shade['text'] === 'white' ? 'text-white/50' : 'text-slate-400' }}">{{ $stat['title'] }}</p>
                    </div>
                    <p class="text-[32px] font-black leading-none mb-2 {{ $shade['text'] === 'white' ? 'text-white' : 'text-[#0d326b]' }}">{{ $stat['value'] }}</p>
                    <p class="text-[12px] {{ $shade['text'] === 'white' ? 'text-white/60' : 'text-slate-400' }}">{{ $stat['detail'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ══════════ ROW 2: Progress line chart + Module difficulty ══════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

        {{-- Class Progress Over Time — soft curved line chart --}}
        <div class="panel">
            <div class="flex items-start justify-between gap-3 mb-6">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Trend</p>
                    <h3 class="text-[16px] font-bold text-[#0d326b]">Class Progress Over Time</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Weekly average quiz score for your class</p>
                </div>
                <span class="text-[11px] font-bold text-slate-400 bg-[#f1f5f9] px-3 py-1.5 rounded-full shrink-0">Last 8 weeks</span>
            </div>

            @php
                $lineWidth  = 320;
                $lineHeight = 180;
                $padTop     = 16;
                $padBottom  = 16;
                $count      = count($progressOverTime);
                $lineMax    = max(1, collect($progressOverTime)->max('value'));
                $lineMin    = min(0, collect($progressOverTime)->min('value'));
                $range      = max(1, $lineMax - $lineMin);

                $pts = [];
                foreach ($progressOverTime as $index => $week) {
                    $x = $count > 1 ? ($index / ($count - 1)) * $lineWidth : $lineWidth / 2;
                    $y = $lineHeight - $padBottom - ((($week['value'] - $lineMin) / $range) * ($lineHeight - $padTop - $padBottom));
                    $pts[] = ['x' => round($x, 2), 'y' => round($y, 2)];
                }

                // Build a soft, curvy cubic-bezier path through the points (no overshoot,
                // horizontal-tangent smoothing) instead of a straight polyline.
                $curvePath = '';
                $areaPath  = '';
                if (count($pts) > 0) {
                    $curvePath = "M {$pts[0]['x']},{$pts[0]['y']}";
                    for ($i = 0; $i < count($pts) - 1; $i++) {
                        $p0 = $pts[$i];
                        $p1 = $pts[$i + 1];
                        $dx = ($p1['x'] - $p0['x']) / 2;
                        $c1x = $p0['x'] + $dx; $c1y = $p0['y'];
                        $c2x = $p1['x'] - $dx; $c2y = $p1['y'];
                        $curvePath .= " C {$c1x},{$c1y} {$c2x},{$c2y} {$p1['x']},{$p1['y']}";
                    }
                    $areaPath = $curvePath . " L {$pts[count($pts)-1]['x']},{$lineHeight} L {$pts[0]['x']},{$lineHeight} Z";
                }
            @endphp

            <div class="relative overflow-hidden rounded-2xl bg-[#f8fafc] p-6">
                <div class="absolute inset-x-6 top-6 h-px bg-slate-200"></div>
                <div class="absolute inset-x-6 top-[92px] h-px bg-slate-200"></div>

                @if(empty($pts))
                    <p class="text-slate-400 text-[13px] text-center py-16">No progress data available yet.</p>
                @else
                    <svg viewBox="0 0 {{ $lineWidth }} {{ $lineHeight }}" class="w-full h-[200px]" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="progressFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#0d326b" stop-opacity="0.22" />
                                <stop offset="100%" stop-color="#0d326b" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <path d="{{ $areaPath }}" fill="url(#progressFill)" stroke="none" />
                        <path d="{{ $curvePath }}" fill="none" stroke="#0d326b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        @foreach($pts as $p)
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4" fill="#ffffff" stroke="#0d326b" stroke-width="2.5" />
                        @endforeach
                    </svg>
                @endif

                <div class="grid gap-2 text-[10px] font-semibold text-slate-400 mt-4" style="grid-template-columns: repeat({{ max(1, $count) }}, minmax(0,1fr));">
                    @foreach($progressOverTime as $week)
                        <div class="text-center">{{ $week['label'] }}</div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Module Difficulty Ranking — navy gradient bars --}}
        <div class="panel">
            <div class="flex items-start justify-between gap-3 mb-6">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Focus areas</p>
                    <h3 class="text-[16px] font-bold text-[#0d326b]">Module Difficulty Ranking</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Lessons ordered lowest to highest average score</p>
                </div>
                <span class="text-[11px] font-bold text-slate-400 bg-[#f1f5f9] px-3 py-1.5 rounded-full shrink-0">Hardest first</span>
            </div>

            @if($lessonDifficulty->isEmpty())
                <p class="text-slate-400 text-[13px] text-center py-16">No lesson score data available yet.</p>
            @else
                <div class="space-y-4">
                    @foreach($lessonDifficulty as $lesson)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-[12.5px] font-semibold text-[#0d326b] truncate">{{ $lesson['title'] }}</span>
                                <span class="text-[11px] font-bold text-slate-400">{{ $lesson['avg_score'] }}%</span>
                            </div>
                            <div class="h-2.5 rounded-full bg-[#eef2f7] overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ max(4, $lesson['avg_score']) }}%; background: linear-gradient(90deg, #93c5fd, #1e4b8f);"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════ ROW 3: Heatmap + sidebar charts ══════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- Student Ranking — leaderboard by average quiz score --}}
        <div class="xl:col-span-2 panel">
            <div class="flex items-start justify-between gap-3 mb-6">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Leaderboard</p>
                    <h3 class="text-[16px] font-bold text-[#0d326b]">Student Ranking</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Ranked by average quiz score across all attempts</p>
                </div>
                <span class="text-[11px] font-bold text-slate-400 bg-[#f1f5f9] px-3 py-1.5 rounded-full shrink-0">{{ $studentRanking->count() ?? 0 }} students</span>
            </div>

            @if(($studentRanking ?? collect())->isEmpty())
                <p class="text-slate-400 text-[13px] text-center py-16">No quiz attempt data available yet.</p>
            @else
                @php
                    $rankShades = ['#0d326b', '#1e4b8f', '#3b82f6']; // top 3 get distinct navy shades
                @endphp
                <div class="space-y-2.5">
                    @foreach($studentRanking as $i => $s)
                        @php
                            $rank = $i + 1;
                            $isTop3 = $rank <= 3;
                            $badgeColor = $isTop3 ? $rankShades[$rank - 1] : '#e2e8f0';
                            $badgeText = $isTop3 ? '#ffffff' : '#64748b';
                        @endphp
                        <div class="flex items-center gap-4 p-3 rounded-xl {{ $isTop3 ? 'bg-[#f8fafc]' : '' }} hover:bg-[#f8fafc] transition-colors">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-black shrink-0"
                                 style="background: {{ $badgeColor }}; color: {{ $badgeText }};">
                                {{ $rank }}
                            </div>
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($s['name']) }}&background=0d326b&color=fff&rounded=true&size=60"
                                 class="w-9 h-9 rounded-full ring-2 ring-slate-100 shrink-0" />
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-bold text-[#0d326b] truncate">{{ $s['name'] }}</p>
                                <p class="text-[10.5px] text-slate-400">{{ $s['attempts'] }} quiz attempt{{ $s['attempts'] !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="w-32 shrink-0">
                                <div class="h-2 rounded-full bg-[#eef2f7] overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ max(4, $s['avg_score']) }}%; background: linear-gradient(90deg, #93c5fd, #0d326b);"></div>
                                </div>
                            </div>
                            <span class="text-[13px] font-black text-[#0d326b] w-12 text-right shrink-0">{{ $s['avg_score'] }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="space-y-5">

            {{-- Mastery Level Distribution — navy donut --}}
            <div class="panel !p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Mastery Level Distribution</p>
                @php
                    $navyScale = ['#dbeafe', '#93c5fd', '#3b82f6', '#1e4b8f', '#0d326b'];
                    $circ = 251.3;
                    $segments = [];
                    $off = 0;
                    foreach ($masteryDistribution as $i => $seg) {
                        $shade = $navyScale[$i % count($navyScale)];
                        $dash = round(($seg['pct'] / 100) * $circ, 1);
                        $segments[] = ['color' => $shade, 'dash' => $dash, 'offset' => -$off, 'label' => $seg['label'], 'pct' => $seg['pct']];
                        $off += $dash;
                    }
                @endphp
                <div class="flex items-center gap-5">
                    <div class="relative w-[120px] h-[120px] shrink-0">
                        <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                            @foreach($segments as $seg)
                                @if($seg['dash'] > 0)
                                <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $seg['color'] }}" stroke-width="14"
                                    stroke-dasharray="{{ $seg['dash'] }} {{ $circ - $seg['dash'] }}"
                                    stroke-dashoffset="{{ $seg['offset'] }}" stroke-linecap="butt"/>
                                @endif
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-[20px] font-black text-[#0d326b]">{{ $masteryTotal ?? 0 }}</span>
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Total</span>
                        </div>
                    </div>
                    <div class="space-y-2.5 flex-1 min-w-0">
                        @foreach($segments as $seg)
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $seg['color'] }}"></span>
                                    <span class="text-[11px] text-slate-600 truncate">{{ $seg['label'] }}</span>
                                </div>
                                <span class="text-[11px] font-bold text-[#0d326b] shrink-0">{{ $seg['pct'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Completion Funnel — navy shades --}}
            <div class="panel !p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Completion Funnel</p>
                @if($completionTotal === 0)
                    <p class="text-slate-400 text-[13px] text-center py-10">No lesson assignments recorded yet.</p>
                @else
                    @php $funnelScale = ['#dbeafe', '#93c5fd', '#3b82f6', '#1e4b8f', '#0d326b']; @endphp
                    <div class="h-4 rounded-full bg-slate-100 overflow-hidden mb-4 flex">
                        @foreach($completionFunnel as $i => $step)
                            @php $width = $completionTotal > 0 ? max(2, round(($step['count'] / $completionTotal) * 100)) : 0; @endphp
                            <div class="h-full" style="width: {{ $width }}%; background: {{ $funnelScale[$i % count($funnelScale)] }}"></div>
                        @endforeach
                    </div>
                    <div class="space-y-2.5">
                        @foreach($completionFunnel as $i => $step)
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $funnelScale[$i % count($funnelScale)] }}"></span>
                                    <span class="text-[11px] text-slate-600">{{ $step['label'] }}</span>
                                </div>
                                <span class="text-[11px] font-bold text-[#0d326b]">{{ $step['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Score Distribution — was a histogram, now a donut (same data, more meaningful at a glance) --}}
            <div class="panel !p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Score Distribution</p>
                <p class="text-[11px] text-slate-400 mb-4">Share of quiz attempts by score range</p>
                @php
                    $scoreTotal = collect($scoreBuckets)->sum('count');
                @endphp
                @if($scoreTotal === 0)
                    <p class="text-slate-400 text-[13px] text-center py-10">No quiz attempt data available yet.</p>
                @else
                    @php
                        $scoreScale = ['#dbeafe', '#93c5fd', '#3b82f6', '#1e4b8f', '#0d326b'];
                        $sCirc = 251.3;
                        $sSegments = [];
                        $sOff = 0;
                        foreach ($scoreBuckets as $i => $bucket) {
                            $pct = round(($bucket['count'] / $scoreTotal) * 100);
                            $shade = $scoreScale[$i % count($scoreScale)];
                            $dash = round(($pct / 100) * $sCirc, 1);
                            $sSegments[] = ['color' => $shade, 'dash' => $dash, 'offset' => -$sOff, 'label' => $bucket['label'], 'count' => $bucket['count'], 'pct' => $pct];
                            $sOff += $dash;
                        }
                    @endphp
                    <div class="flex items-center gap-5">
                        <div class="relative w-[120px] h-[120px] shrink-0">
                            <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                                @foreach($sSegments as $seg)
                                    @if($seg['dash'] > 0)
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $seg['color'] }}" stroke-width="14"
                                        stroke-dasharray="{{ $seg['dash'] }} {{ $sCirc - $seg['dash'] }}"
                                        stroke-dashoffset="{{ $seg['offset'] }}" stroke-linecap="butt"/>
                                    @endif
                                @endforeach
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-[20px] font-black text-[#0d326b]">{{ $scoreTotal }}</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Attempts</span>
                            </div>
                        </div>
                        <div class="space-y-2.5 flex-1 min-w-0">
                            @foreach($sSegments as $seg)
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $seg['color'] }}"></span>
                                        <span class="text-[11px] text-slate-600 truncate">{{ $seg['label'] }}</span>
                                    </div>
                                    <span class="text-[11px] font-bold text-[#0d326b] shrink-0">{{ $seg['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection