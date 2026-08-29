@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="flex flex-col gap-2 w-full overflow-x-hidden">

    <div class="flex flex-col lg:flex-row gap-4 w-full">
                
    <!-- Left/Center Content -->
    <div class="flex-1 min-w-0 flex flex-col space-y-4">
        
        <!-- Welcome Banner + Calendar -->
        @php
            $today = \Carbon\Carbon::now();
           $startOfWeek = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
           $weekDays = [];
           for ($i = 0; $i < 21; $i++) {
               $weekDays[] = $startOfWeek->copy()->addDays($i);
           }
        @endphp
        <div class="flex gap-5">
           <!-- Welcome Banner -->
           <div class="flex-1 rounded-[28px] relative overflow-hidden min-h-[160px] flex items-center"
                style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%)">
                <!-- Decorative circles -->
                <div class="absolute top-0 right-48 w-48 h-48 rounded-full opacity-10" style="background:white"></div>
                <div class="absolute -bottom-10 left-1/3 w-36 h-36 rounded-full opacity-10" style="background:white"></div>
 
                <!-- Text content -->
                <div class="relative z-10 px-10 py-8 flex-1">
                    <h2 class="text-[28px] font-black text-white leading-tight mb-2">{{ $greeting }}, Teacher {{ $firstName }}!</h2>
                    <p class="text-[13px] text-white/70 font-medium leading-relaxed mb-5">Here is a summary of your classroom's<br>archival progress today.</p>
                    <a href="{{ route('lessons.index') }}" class="inline-flex items-center space-x-2 text-[12px] font-black text-white/80 uppercase tracking-[0.1em] hover:text-white transition-colors">
                        <span>GO TO LESSONS</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
 
                <!-- Senya mascot -->
                <div class="relative z-10 flex-shrink-0 pr-4 flex items-end self-end">
                    <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya" class="h-[170px] w-auto object-contain drop-shadow-lg" style="filter: drop-shadow(0 8px 24px rgba(0,0,0,0.3))"/>
                </div>
            </div>
 
            <!-- Calendar Widget -->
            <div class="bg-white rounded-[28px] px-6 py-5 shadow-sm border border-slate-100 flex-shrink-0 w-[260px] flex flex-col">
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
                        <div data-date="{{ $day->format('Y-m-d') }}" class="week-day w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-medium {{ $day->isToday() ? 'bg-[#1C3D7A] text-white' : 'text-slate-600 hover:bg-slate-100 cursor-pointer transition-colors' }}">
                            {{ $day->format('d') }}
                        </div>
                    </div>
                    @endforeach
                </div>
 
            </div>
        </div>
 
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const weekLabel = document.getElementById('week-label');
                const prevButton = document.getElementById('week-prev');
                const nextButton = document.getElementById('week-next');
                const weekDays = Array.from(document.querySelectorAll('.week-day'));
                const today = new Date('{{ $today->format('Y-m-d') }}');
 
                function updateWeek(startDate) {
                    const start = new Date(startDate);
 
                    weekDays.forEach((cell, index) => {
                        const date = new Date(start);
                        date.setDate(start.getDate() + index);
                        cell.dataset.date = date.toISOString().slice(0, 10);
                        cell.textContent = date.getDate();
                        const isToday = date.toISOString().slice(0, 10) === today.toISOString().slice(0, 10);
                        cell.classList.toggle('bg-[#1C3D7A]', isToday);
                        cell.classList.toggle('text-white', isToday);
                        cell.classList.toggle('text-slate-600', !isToday);
                        cell.classList.toggle('hover:bg-slate-100', !isToday);
                        cell.classList.toggle('cursor-pointer', !isToday);
                    });
                }
 
                let currentStart = new Date('{{ $startOfWeek->format('Y-m-d') }}');
                prevButton.addEventListener('click', function() {
                    currentStart.setDate(currentStart.getDate() - 7);
                    updateWeek(currentStart);
                });
                nextButton.addEventListener('click', function() {
                    currentStart.setDate(currentStart.getDate() + 7);
                    updateWeek(currentStart);
                });
            });
        </script>

        @php
            $kpiSparkline = function (array $data, string $color, int $width = 240, int $height = 44): string {
                if (count($data) < 2) {
                    $data = array_fill(0, 7, $data[0] ?? 0);
                }
                $max = max($data);
                $min = min($data);
                $range = max($max - $min, 1);
                $count = count($data);
                $points = [];
                foreach ($data as $i => $value) {
                    $x = $count > 1 ? ($i / ($count - 1)) * $width : $width / 2;
                    $y = $height - 6 - (($value - $min) / $range) * ($height - 12);
                    $points[] = round($x, 1) . ',' . round($y, 1);
                }
                return '<svg viewBox="0 0 ' . $width . ' ' . $height . '" class="w-full h-[44px]" preserveAspectRatio="none" aria-hidden="true">'
                    . '<polyline fill="none" stroke="' . e($color) . '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="' . implode(' ', $points) . '"/>'
                    . '</svg>';
            };
        @endphp

        <!-- Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- Total Students --}}
            <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full bg-[#0d326b] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">group</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Total Students</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $totalStudents }}</p>
                <p class="text-[12px] font-medium text-[#1a6fd4] mt-2 mb-4">↑ {{ $newStudentsThisWeek }} this week</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineTotalStudents ?: array_fill(0, 7, 0), '#0d326b') !!}
                </div>
            </div>

            {{-- Active Today --}}
            <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full bg-[#1e4b8f] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">monitor_heart</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Active Today</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $activeToday }}</p>
                <p class="text-[12px] font-medium text-slate-400 mt-2 mb-4">{{ $activeTodayPercent }}% of total</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineActive ?: array_fill(0, 7, 0), '#1e4b8f') !!}
                </div>
            </div>

            {{-- Avg. Accuracy --}}
            <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full bg-[#1a6fd4] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">gps_fixed</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Avg. Accuracy</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $avgAccuracy > 0 ? $avgAccuracy . '%' : '0%' }}</p>
                <p class="text-[12px] font-medium text-[#1a6fd4] mt-2 mb-4">↑ {{ max(0, $accuracyWeeklyChange) }}% this week</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineAccuracy ?: array_fill(0, 7, 0), '#1a6fd4') !!}
                </div>
            </div>

            {{-- Total Lessons --}}
            <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full bg-[#3b82f6] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">menu_book</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Total Lessons</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $totalLessons }}</p>
                <p class="text-[12px] font-medium text-[#3b82f6] mt-2 mb-4">↑ {{ $newLessonsThisWeek }} this week</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineLessons ?: array_fill(0, 7, 0), '#3b82f6') !!}
                </div>
            </div>

        </div>

        <!-- Your Lessons (grouped by Module) -->
        <div class="mt-0">
            <div class="flex justify-between items-end mb-3 pl-2">
                <h3 class="text-[22px] font-bold text-[#0d326b]">Your Lessons</h3>
                <a href="{{ route('lessons.index') }}" class="text-[14px] font-bold text-[#0d326b] hover:underline pr-2">Manage Lessons</a>
            </div>

            @if($modules->isEmpty())
                <div class="bg-white rounded-[32px] p-10 text-center text-slate-400 text-sm font-medium shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-slate-100">
                    No record — no modules created yet.
                </div>
            @else
            <div class="flex space-x-4 overflow-x-auto pb-6 scrollbar-hide pt-8">
                @foreach($modules as $index => $module)
                @php
                    $palettes = [
                        ['bg' => '#e8eef8', 'tab_bg' => '#d0ddf2', 'title' => '#0d326b', 'sub' => '#4a6fa5', 'bar_fill' => '#0d326b', 'bar_bg' => '#c0cfe8'],
                        ['bg' => '#eff6ff', 'tab_bg' => '#dbeafe', 'title' => '#1e4b8f', 'sub' => '#4a6fa5', 'bar_fill' => '#1a6fd4', 'bar_bg' => '#c7ddf9'],
                        ['bg' => '#dbeafe', 'tab_bg' => '#bfd7f9', 'title' => '#1e3a8a', 'sub' => '#3b5fc0', 'bar_fill' => '#3b82f6', 'bar_bg' => '#bcd4f8'],
                        ['bg' => '#e0e9ff', 'tab_bg' => '#c7d5ff', 'title' => '#0d326b', 'sub' => '#3b4fc0', 'bar_fill' => '#071c3f', 'bar_bg' => '#bcc7f8'],
                    ];
                    $p = $palettes[$index % 4];
                @endphp

                <!-- CSS Folder Card (Module) -->
                <div class="flex-shrink-0 w-[280px] group cursor-pointer transition-transform duration-300 hover:-translate-y-1.5">
                    <!-- Folder Tab (top-left raised nub) -->
                    <div class="w-[90px] h-[24px] rounded-t-[16px] ml-4"
                         style="background: {{ $p['tab_bg'] }}"></div>

                    <!-- Folder Body wrapper (relative for hover overlay) -->
                    <div class="relative rounded-b-[24px] rounded-tr-[24px] shadow-sm transition-shadow duration-300 group-hover:shadow-md overflow-hidden h-[220px]">

                        <!-- Base face: module summary -->
                        <div class="absolute inset-0 p-6 flex flex-col"
                             style="background: {{ $p['bg'] }};">

                            <!-- Top: Title + 3-dot -->
                            <div class="flex items-start justify-between mb-3">
                                <h4 class="font-bold text-[17px] leading-snug pr-2"
                                    style="color: {{ $p['title'] }}">{{ $module->title }}</h4>
                                <button class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center hover:bg-black/5 transition-colors"
                                        style="color: {{ $p['sub'] }}">
                                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                </button>
                            </div>

                            <!-- Middle: quick completion bar -->
                            <div class="mb-3">
                                <div class="w-full h-1.5 rounded-full" style="background: {{ $p['tab_bg'] }}">
                                    <div class="h-full rounded-full" style="width: {{ $module->completion }}%; background: {{ $p['bar_fill'] }}"></div>
                                </div>
                                <p class="text-[11px] font-bold mt-1.5" style="color: {{ $p['sub'] }}">
                                    {{ $module->lessons->count() }} lesson{{ $module->lessons->count() === 1 ? '' : 's' }} · {{ $module->completion }}% complete
                                </p>
                            </div>

                            <!-- Spacer -->
                            <div class="flex-1"></div>

                            <!-- Bottom: Student avatars + count -->
                            <div class="flex items-center justify-between pt-3"
                                 style="border-top: 1px solid {{ $p['tab_bg'] }}">
                                <div class="flex -space-x-1.5">
                                    @forelse($module->topStudents->take(3) as $s)
                                    <img src="{{ $s->avatarUrl() }}"
                                         class="w-8 h-8 rounded-full border-2 border-white shadow-sm object-cover bg-[#0d326b]"
                                         title="{{ $s->first_name }} {{ $s->last_name }}"
                                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($s->initials) }}&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45';" />
                                    @empty
                                        <div class="w-8 h-8 rounded-full border-2 border-white flex items-center justify-center text-[10px] font-bold"
                                             style="background: {{ $p['tab_bg'] }}; color: {{ $p['sub'] }}">—</div>
                                    @endforelse
                                    @if($module->extraStudents > 0)
                                    <div class="w-8 h-8 rounded-full border-2 border-white flex items-center justify-center text-[10px] font-black shadow-sm"
                                         style="background: {{ $p['tab_bg'] }}; color: {{ $p['title'] }}">+{{ $module->extraStudents }}</div>
                                    @endif
                                </div>
                                <span class="text-[12px] font-semibold" style="color: {{ $p['sub'] }}">{{ $module->enrolled }} student{{ $module->enrolled === 1 ? '' : 's' }} using this</span>
                            </div>
                        </div>

                        <!-- Hover overlay: lessons inside this module -->
                        <div class="absolute inset-0 p-5 flex flex-col opacity-0 invisible translate-y-1 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-200 z-10"
                             style="background: {{ $p['bg'] }};">
                            <h5 class="text-[11px] font-black uppercase tracking-[0.1em] mb-3 flex-shrink-0" style="color: {{ $p['title'] }}">
                                Lessons in this module
                            </h5>

                            <div class="flex-1 overflow-y-auto space-y-1.5 pr-1 -mr-1">
                                @forelse($module->lessons as $lesson)
                                <a href="{{ route('lessons.view', $lesson->hash_id) }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg bg-white/60 hover:bg-white transition-colors">
                                    <span class="text-[12px] font-semibold truncate pr-2" style="color: {{ $p['title'] }}">{{ $lesson->title }}</span>
                                    <span class="material-symbols-outlined text-[14px] flex-shrink-0" style="color: {{ $p['sub'] }}">chevron_right</span>
                                </a>
                                @empty
                                <p class="text-[12px] italic" style="color: {{ $p['sub'] }}">No lessons yet</p>
                                @endforelse
                            </div>

                            <div class="flex items-center justify-between mt-3 pt-3 flex-shrink-0" style="border-top: 1px solid {{ $p['tab_bg'] }}">
                                <div class="flex -space-x-1.5">
                                    @forelse($module->topStudents->take(3) as $s)
                                    <img src="{{ $s->avatarUrl() }}"
                                         class="w-8 h-8 rounded-full border-2 border-white shadow-sm object-cover bg-[#0d326b]"
                                         title="{{ $s->first_name }} {{ $s->last_name }}"
                                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($s->initials) }}&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45';" />
                                    @empty
                                        <div class="w-8 h-8 rounded-full border-2 border-white flex items-center justify-center text-[10px] font-bold"
                                             style="background: {{ $p['tab_bg'] }}; color: {{ $p['sub'] }}">—</div>
                                    @endforelse
                                    @if($module->extraStudents > 0)
                                    <div class="w-8 h-8 rounded-full border-2 border-white flex items-center justify-center text-[10px] font-black shadow-sm"
                                         style="background: {{ $p['tab_bg'] }}; color: {{ $p['title'] }}">+{{ $module->extraStudents }}</div>
                                    @endif
                                </div>
                                <span class="text-[12px] font-semibold" style="color: {{ $p['sub'] }}">{{ $module->enrolled }} student{{ $module->enrolled === 1 ? '' : 's' }} using this</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Student Activity Card (navy gradient) -->
                <div class="rounded-[20px] p-6 w-[280px] flex-shrink-0 flex flex-col justify-between shadow-sm relative overflow-hidden text-white"
                     style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%); min-height: 200px;">
                    <div class="absolute top-0 right-0 w-24 h-24 opacity-10 rounded-bl-full bg-white"></div>
                    <div class="flex justify-between items-start relative z-10">
                        <span class="material-symbols-outlined text-white text-[28px]">assignment_ind</span>
                        <a href="{{ route('students') }}" class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center hover:bg-white/10 transition-colors text-white" title="View Students">
                            <span class="material-symbols-outlined text-[20px]">more_vert</span>
                        </a>
                    </div>
                    <div class="relative z-10">
                        <h4 class="font-bold text-white text-[17px] mb-0.5">Student Activity</h4>
                        <p class="text-white/70 text-[12px] mb-3">{{ $practiceSessions['subtitle'] ?? 'Active Monitoring' }}</p>
                        <div class="w-full h-2 rounded-full bg-white/15 mb-1.5 overflow-hidden">
                            <div class="h-full rounded-full bg-white/80 transition-all duration-700" style="width: {{ $practiceSessions['activity_pct'] ?? 0 }}%;"></div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-white uppercase tracking-wider">{{ $practiceSessions['activity_pct'] ?? 0 }}% Activeness</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @php
            /* ---------------------------------------------------------
             | Data prep for Lesson Progress donut + widgets.
             ---------------------------------------------------------- */

            // Lesson Progress donut — derive Completed / In Progress / Not Started
            // counts from the lesson mastery collection.
            $lpTotal = $lessons->count();
            $lpCompleted = $lessonMastery->where('masteryPct', '>=', 100)->count();
            $lpInProgress = $lessonMastery->filter(fn ($lm) => $lm->masteryPct > 0 && $lm->masteryPct < 100)->count();
            $lpNotStarted = max(0, $lpTotal - $lpCompleted - $lpInProgress);

            // Variable-radius doughnut (rose/polar chart style with distinct outer radii)
            $rawSegments = $lpTotal > 0 ? [
                ['key' => 'completed', 'count' => $lpCompleted, 'color' => '#0d326b', 'grad_id' => 'donutGradCompleted', 'grad_from' => '#1e4b8f', 'grad_to' => '#071c3f', 'label' => 'Completed'],
                ['key' => 'in_progress', 'count' => $lpInProgress, 'color' => '#1a6fd4', 'grad_id' => 'donutGradProgress', 'grad_from' => '#3b82f6', 'grad_to' => '#1a6fd4', 'label' => 'In Progress'],
                ['key' => 'not_started', 'count' => $lpNotStarted, 'color' => '#93c5fd', 'grad_id' => 'donutGradNotStarted', 'grad_from' => '#bfdbfe', 'grad_to' => '#93c5fd', 'label' => 'Not Started'],
            ] : [];

            $activeSegments = array_values(array_filter($rawSegments, fn($s) => $s['count'] > 0));
            $activeCount = count($activeSegments);
            $maxSegCount = $activeCount > 0 ? max(1, ...array_map(fn($s) => $s['count'], $activeSegments)) : 1;

            $donutPaths = [];
            $cx = 80;
            $cy = 80;
            $innerR = 40;
            $gapAngle = ($activeCount > 1) ? 0.08 : 0; // Clean ~4.5 deg spacing between slices
            $currentAngle = -M_PI / 2; // Start at 12 o'clock

            foreach ($activeSegments as $seg) {
                $fraction = $seg['count'] / $lpTotal;
                $angleSpan = $fraction * (2 * M_PI);

                // Variable radius: Outer radius scales from 56px to 74px based on share/count
                $outerR = round(56 + (18 * ($seg['count'] / $maxSegCount)), 1);

                if ($activeCount === 1) {
                    $segStart = $currentAngle;
                    $segEnd = $currentAngle + (2 * M_PI) - 0.001;
                } else {
                    $segStart = $currentAngle + ($gapAngle / 2);
                    $segEnd = $currentAngle + $angleSpan - ($gapAngle / 2);
                    if ($segEnd <= $segStart) {
                        $segStart = $currentAngle;
                        $segEnd = $currentAngle + $angleSpan;
                    }
                }

                $x1 = round($cx + $outerR * cos($segStart), 2);
                $y1 = round($cy + $outerR * sin($segStart), 2);
                $x2 = round($cx + $outerR * cos($segEnd), 2);
                $y2 = round($cy + $outerR * sin($segEnd), 2);
                $x3 = round($cx + $innerR * cos($segEnd), 2);
                $y3 = round($cy + $innerR * sin($segEnd), 2);
                $x4 = round($cx + $innerR * cos($segStart), 2);
                $y4 = round($cy + $innerR * sin($segStart), 2);

                $largeArc = ($segEnd - $segStart > M_PI) ? 1 : 0;
                $pathD = "M {$x1} {$y1} A {$outerR} {$outerR} 0 {$largeArc} 1 {$x2} {$y2} L {$x3} {$y3} A {$innerR} {$innerR} 0 {$largeArc} 0 {$x4} {$y4} Z";

                $donutPaths[] = array_merge($seg, [
                    'd' => $pathD,
                    'outerR' => $outerR,
                    'pct' => round($fraction * 100),
                ]);

                $currentAngle += $angleSpan;
            }
        @endphp

    </div>

    <!-- Right Sidebar Column -->
    <div class="w-[340px] flex-shrink-0 flex flex-col space-y-4 pl-4 self-stretch">

        <!-- ── Senya Insights Widget ─────────────────────────────────────────── -->
        @php $insights = $senyaInsights ?? []; $insightCount = count($insights); @endphp
        <div class="rounded-[28px] overflow-hidden shadow-sm"
             style="background: linear-gradient(135deg,#f59e0b 0%,#facc15 60%,#fbbf24 100%);">

            <!-- Header bar -->
            <div class="px-5 pt-5 pb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-black/10 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[18px] text-[#1e293b]">lightbulb</span>
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.15em] text-[#1e293b] leading-none">Senya Insights</p>
                        <p class="text-[10px] font-semibold text-[#1e293b]/60 mt-0.5">Based on your class data</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <!-- Refresh -->
                    <button id="insight-refresh"
                            class="w-7 h-7 rounded-full bg-black/10 hover:bg-black/20 flex items-center justify-center transition-all active:scale-90"
                            title="Show another insight">
                        <span class="material-symbols-outlined text-[15px] text-[#1e293b]">refresh</span>
                    </button>
                </div>
            </div>

            <!-- Insight card body -->
            @if($insightCount > 0)
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
                <p class="text-[12px] text-[#1e293b]/60 font-medium leading-relaxed">Insights will appear once your students start completing lessons and quizzes.</p>
            </div>
            @endif
        </div>
        <!-- ── End Senya Insights ─────────────────────────────────────────────── -->

        <!-- My Students List -->
        <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="px-7 pt-7 pb-4 flex items-center justify-between flex-shrink-0">
                <div>
                    <h4 class="text-[15px] font-black text-[#0d326b]">My Students</h4>
                    <p class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $allStudents->count() }} enrolled</p>
                </div>
                <a href="{{ route('students') }}"
                   class="px-3 py-1.5 rounded-lg text-[11px] font-black uppercase tracking-wider text-[#0d326b] hover:bg-[#e8eef8] transition-colors">
                    View All
                </a>
            </div>

            <!-- Divider -->
            <div class="mx-7 border-t border-slate-100 flex-shrink-0"></div>

            <!-- Scrollable list — fixed height shows ~7-8 rows, scrolls for more -->
            <div class="overflow-y-auto divide-y divide-slate-50 flex-1 scrollbar-hide" style="max-height: 500px;">
                @forelse($allStudents as $s)
                @php
                    $mastery = $s->fsl_mastery_level ?? 'Beginner';
                    // Badge colour keyed by mastery level
                    $masteryMap = [
                        'beginner'     => ['bg' => '#eff6ff', 'text' => '#1e4b8f', 'dot' => '#93c5fd'],
                        'elementary'   => ['bg' => '#dbeafe', 'text' => '#1e4b8f', 'dot' => '#3b82f6'],
                        'intermediate' => ['bg' => '#bfdbfe', 'text' => '#0d326b', 'dot' => '#1a6fd4'],
                        'advanced'     => ['bg' => '#0d326b', 'text' => '#ffffff', 'dot' => '#93c5fd'],
                    ];
                    $mk = strtolower(trim($mastery));
                    $mc = $masteryMap[$mk] ?? $masteryMap['beginner'];
                    $level = $s->level ?? 1;
                    $xp    = $s->total_xp ?? 0;
                    // XP needed per level (simple formula)
                    $xpNext = $level * 100;
                    $xpPct  = $xpNext > 0 ? min(100, round(($xp % $xpNext) / $xpNext * 100)) : 0;
                @endphp
                <div class="flex items-center gap-4 px-7 py-4 hover:bg-slate-50 transition-colors">
                    <!-- Avatar -->
                    <div class="relative flex-shrink-0">
                        <img src="{{ $s->avatarUrl() }}"
                             class="w-11 h-11 rounded-full shadow-sm object-cover bg-[#0d326b]"
                             alt="{{ $s->first_name }}"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($s->initials) }}&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45';" />
                        <!-- Level badge -->
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-[#0d326b] border-2 border-white flex items-center justify-center">
                            <span class="text-[8px] font-black text-white leading-none">{{ $level }}</span>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-[13px] font-bold text-[#1e293b] truncate">{{ $s->first_name }} {{ $s->last_name }}</span>
                            <!-- Mastery badge -->
                            <span class="flex-shrink-0 flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider"
                                  style="background: {{ $mc['bg'] }}; color: {{ $mc['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: {{ $mc['dot'] }}"></span>
                                {{ ucfirst($mastery) }}
                            </span>
                        </div>
                        <!-- Grade level + XP bar -->
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-semibold text-slate-400 flex-shrink-0">{{ $s->grade_level ?: '—' }}</span>
                            <div class="flex-1 h-1 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-[#0d326b] to-[#1a6fd4] transition-all duration-500"
                                     style="width: {{ $xpPct }}%"></div>
                            </div>
                            <span class="text-[9px] font-bold text-slate-400 flex-shrink-0">{{ $xp }} XP</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-7 py-12 text-center text-[13px] text-slate-400 italic">
                    No students yet.
                </div>
                @endforelse
            </div>

            <!-- Footer -->
            <div class="px-7 py-3 border-t border-slate-100 flex-shrink-0">
                <a href="{{ route('students') }}"
                   class="block w-full py-3.5 rounded-xl text-center text-[13px] font-bold uppercase tracking-wider text-white transition-all duration-200 hover:opacity-90"
                   style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%)">
                    Manage Students
                </a>
            </div>
        </div>

    </div>
</div>

    <!-- Bottom Row: Mastery / Lesson Progress / Student Performance (full width, ignores sidebar column) -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 pt-4">

            <!-- Enrollment Trend per School Year (5 Years) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 relative overflow-hidden flex flex-col justify-between">
                <div class="absolute -top-8 -right-8 w-32 h-32 bg-indigo-50/70 rounded-full pointer-events-none"></div>

                <div class="flex items-start justify-between mb-5 relative z-10">
                    <div>
                        <h3 class="text-base font-bold text-[#0d326b]">Enrollment Trend</h3>
                        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">Per School Year (June 8 – April 8)</p>
                    </div>
                    @php
                        $trendData = $enrollmentTrend ?? [];
                        $currentSyData = end($trendData) ?: ['school_year' => '2026-2027', 'count' => 0];
                        $totalEnrolledInTrend = array_sum(array_column($trendData, 'count'));
                    @endphp
                    <div class="bg-[#eff6ff] border border-blue-100 rounded-2xl px-3 py-1.5 flex items-center gap-2 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-[#1a6fd4]"></span>
                        <div class="text-right">
                            <p class="text-[12px] font-black text-[#0d326b] leading-tight">{{ $currentSyData['count'] }} <span class="text-[10px] font-bold text-slate-500">Enrolled</span></p>
                            <p class="text-[9px] font-bold text-slate-400 leading-none">S.Y. {{ $currentSyData['school_year'] }}</p>
                        </div>
                    </div>
                </div>

                @if(empty($trendData) || $totalEnrolledInTrend === 0)
                    <div class="text-center text-sm text-slate-400 py-12 italic relative z-10">No enrollment records found</div>
                @else
                @php
                    $chartW = 340; $chartH = 185;
                    $padL = 26; $padR = 24; $padT = 24; $padB = 30;
                    $plotW = $chartW - $padL - $padR;
                    $plotH = $chartH - $padT - $padB;
                    
                    $counts = array_column($trendData, 'count');
                    $rawMax = max($counts);
                    if ($rawMax <= 4) {
                        $maxVal = 4;
                        $ticks = [0, 1, 2, 3, 4];
                    } elseif ($rawMax <= 8) {
                        $maxVal = 8;
                        $ticks = [0, 2, 4, 6, 8];
                    } elseif ($rawMax <= 12) {
                        $maxVal = 12;
                        $ticks = [0, 3, 6, 9, 12];
                    } elseif ($rawMax <= 20) {
                        $maxVal = 20;
                        $ticks = [0, 5, 10, 15, 20];
                    } else {
                        $step = (int) ceil($rawMax / 4);
                        $maxVal = $step * 4;
                        $ticks = range(0, $maxVal, $step);
                    }

                    $n = count($trendData);
                    $pts = [];
                    foreach ($trendData as $i => $item) {
                        $val = $item['count'];
                        $x = $padL + ($n > 1 ? ($i / ($n - 1)) * $plotW : $plotW / 2);
                        $y = $padT + $plotH - ($maxVal > 0 ? ($val / $maxVal) * $plotH : 0);
                        $pts[] = [round($x, 1), round($y, 1)];
                    }

                    // Build smooth cubic-bezier curve
                    $curvePath = '';
                    $areaPath  = '';
                    if (count($pts) > 0) {
                        $curvePath = "M {$pts[0][0]},{$pts[0][1]}";
                        for ($i = 0; $i < count($pts) - 1; $i++) {
                            $p0 = $pts[$i];
                            $p1 = $pts[$i + 1];
                            $dx = ($p1[0] - $p0[0]) / 2;
                            $c1x = $p0[0] + $dx; $c1y = $p0[1];
                            $c2x = $p1[0] - $dx; $c2y = $p1[1];
                            $curvePath .= " C {$c1x},{$c1y} {$c2x},{$c2y} {$p1[0]},{$p1[1]}";
                        }
                        $areaPath = $curvePath . " L " . ($padL + $plotW) . "," . ($padT + $plotH) . " L " . $padL . "," . ($padT + $plotH) . " Z";
                    }
                @endphp
                <div class="relative z-10">
                    <div id="enrollmentTooltip" class="pointer-events-none absolute z-30 opacity-0 transition-all duration-150 -translate-x-1/2 -translate-y-full bg-[#0d326b] text-white text-[11px] rounded-xl shadow-xl px-3 py-2 whitespace-nowrap border border-blue-400/20 mb-2">
                        <div class="font-extrabold text-white text-[11px] tracking-wide" id="enrollmentTooltipSy">S.Y. 2026-2027</div>
                        <div class="text-[9.5px] text-blue-200" id="enrollmentTooltipRange">June 8, 2026 – April 8, 2027</div>
                        <div class="text-[12px] font-black text-amber-300 mt-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            <span id="enrollmentTooltipCount">10</span> <span class="font-bold text-white text-[10px]">Students Enrolled</span>
                        </div>
                        <div class="absolute left-1/2 -bottom-1 -translate-x-1/2 w-2 h-2 bg-[#0d326b] rotate-45"></div>
                    </div>
                    <svg id="enrollmentChartSvg" viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="w-full h-[185px]" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="enrollmentAreaFill" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#1a6fd4" stop-opacity="0.28"/>
                                <stop offset="85%" stop-color="#1a6fd4" stop-opacity="0.03"/>
                                <stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/>
                            </linearGradient>
                            <linearGradient id="enrollmentLineStroke" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#3b82f6"/>
                                <stop offset="50%" stop-color="#1a6fd4"/>
                                <stop offset="100%" stop-color="#0d326b"/>
                            </linearGradient>
                        </defs>

                        <!-- Gridlines + Y labels -->
                        @foreach($ticks as $gv)
                        @php $gy = round($padT + $plotH - ($gv / $maxVal) * $plotH, 1); @endphp
                        <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $padL + $plotW }}" y2="{{ $gy }}" stroke="#f1f5f9" stroke-width="1"/>
                        <text x="2" y="{{ $gy + 3.5 }}" font-size="9.5" fill="#94a3b8" font-weight="600">{{ $gv }}</text>
                        @endforeach

                        <!-- Area fill (smooth) -->
                        <path d="{{ $areaPath }}" fill="url(#enrollmentAreaFill)"/>

                        <!-- Line (smooth curve) -->
                        <path d="{{ $curvePath }}" fill="none" stroke="url(#enrollmentLineStroke)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Points -->
                        @foreach($pts as $i => $p)
                        @php
                            $isLast = ($i === count($pts) - 1);
                            $val = $trendData[$i]['count'];
                        @endphp
                        <!-- Outer pulse ring for current year point -->
                        @if($isLast)
                        <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="8" fill="#1a6fd4" fill-opacity="0.2"/>
                        @endif

                        <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="{{ $isLast ? 5.5 : 4 }}" fill="{{ $isLast ? '#1a6fd4' : ($val > 0 ? '#0d326b' : '#94a3b8') }}" stroke="white" stroke-width="2.5"/>

                        <!-- Hit Area for hover -->
                        <circle class="enrollment-point-hit" cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="16" fill="transparent" style="cursor:pointer"
                                data-sy="S.Y. {{ $trendData[$i]['school_year'] }}"
                                data-range="{{ $trendData[$i]['date_range'] }}"
                                data-count="{{ $trendData[$i]['count'] }}"></circle>
                        @endforeach

                        <!-- X labels (School Years) -->
                        @foreach($trendData as $i => $item)
                        @php
                            $isLast = ($i === count($trendData) - 1);
                        @endphp
                        <text x="{{ $pts[$i][0] }}" y="{{ $chartH - 6 }}" font-size="8.5" fill="{{ $isLast ? '#0d326b' : '#64748b' }}" font-weight="{{ $isLast ? '800' : '700' }}" text-anchor="middle">{{ $item['school_year'] }}</text>
                        @endforeach
                    </svg>
                </div>
                @endif
            </div>

            <!-- Lesson Progress (donut chart) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 relative overflow-hidden flex flex-col">
                <div class="absolute -top-10 -right-10 w-36 h-36 bg-blue-50/50 rounded-full opacity-60 pointer-events-none"></div>

                <h3 class="text-base font-bold text-[#0d326b] mb-6 relative z-10">Lesson Progress</h3>

                @if($lpTotal === 0)
                    <div class="flex-1 flex items-center justify-center text-sm text-slate-400 italic relative z-10">No record</div>
                @else
                <div class="flex items-center gap-5 relative z-10">
                    <!-- Variable-Radius Donut -->
                    <div class="relative w-32 h-32 flex-shrink-0 flex items-center justify-center donut-wrapper">
                        <div id="donutTooltip" class="pointer-events-none absolute z-30 opacity-0 transition-opacity duration-150 left-1/2 top-0 -translate-x-1/2 -translate-y-[110%] bg-[#0d326b] text-white text-[11px] font-bold px-3 py-1.5 rounded-lg shadow-lg whitespace-nowrap">
                            <span id="donutTooltipLabel"></span>: <span id="donutTooltipValue"></span>
                            <div class="absolute left-1/2 -bottom-1 -translate-x-1/2 w-2 h-2 bg-[#0d326b] rotate-45"></div>
                        </div>

                        <svg class="w-full h-full overflow-visible" viewBox="0 0 160 160">
                            <defs>
                                <filter id="donutSliceShadow" x="-10%" y="-10%" width="120%" height="120%">
                                    <feDropShadow dx="0" dy="1.5" stdDeviation="1.5" flood-opacity="0.08"/>
                                </filter>
                                @foreach($donutPaths as $seg)
                                <linearGradient id="{{ $seg['grad_id'] }}" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="{{ $seg['grad_from'] }}"/>
                                    <stop offset="100%" stop-color="{{ $seg['grad_to'] }}"/>
                                </linearGradient>
                                @endforeach
                            </defs>

                            <!-- Background track ring -->
                            <circle cx="80" cy="80" r="54" fill="none" stroke="#f1f5f9" stroke-width="26" opacity="0.6"></circle>

                            <!-- Segments with dynamic outer radius and angular gaps -->
                            @foreach($donutPaths as $seg)
                            <path class="donut-segment-hit cursor-pointer transition-all duration-200 hover:opacity-90 hover:brightness-110"
                                  d="{{ $seg['d'] }}"
                                  fill="url(#{{ $seg['grad_id'] }})"
                                  stroke="#ffffff"
                                  stroke-width="2"
                                  stroke-linejoin="round"
                                  filter="url(#donutSliceShadow)"
                                  data-label="{{ $seg['label'] }}"
                                  data-value="{{ $seg['count'] }} ({{ $seg['pct'] }}%)"></path>
                            @endforeach

                            <!-- Inner cutout circle with subtle elevation -->
                            <circle cx="80" cy="80" r="39" fill="#ffffff" filter="url(#donutSliceShadow)"></circle>
                        </svg>

                        <!-- Center text overlay -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                            <span class="text-2xl font-black text-[#0d326b] leading-none">{{ $lpTotal }}</span>
                            <span class="text-[8.5px] font-extrabold uppercase tracking-widest text-slate-400 mt-0.5">Total</span>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="space-y-3 flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:#0d326b"></div>
                                <span class="text-[12px] font-semibold text-slate-600 truncate">Completed</span>
                            </div>
                            <span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $lpCompleted }} ({{ $lpTotal > 0 ? round(($lpCompleted / $lpTotal) * 100) : 0 }}%)</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:#1a6fd4"></div>
                                <span class="text-[12px] font-semibold text-slate-600 truncate">In Progress</span>
                            </div>
                            <span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $lpInProgress }} ({{ $lpTotal > 0 ? round(($lpInProgress / $lpTotal) * 100) : 0 }}%)</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:#93c5fd"></div>
                                <span class="text-[12px] font-semibold text-slate-600 truncate">Not Started</span>
                            </div>
                            <span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $lpNotStarted }} ({{ $lpTotal > 0 ? round(($lpNotStarted / $lpTotal) * 100) : 0 }}%)</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('lessons.index') }}" class="mt-6 block w-full bg-[#eef2ff] text-[#0d326b] text-[13px] font-bold py-3 rounded-xl hover:bg-[#e0e7ff] transition-colors text-center relative z-10">
                    View All Lessons
                </a>
                @endif
            </div>

            <!-- Student Performance (compact) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 relative overflow-hidden flex flex-col">
                <div class="absolute -top-10 -right-10 w-36 h-36 bg-indigo-50 rounded-full opacity-40"></div>

                <div class="flex justify-between items-start mb-1 relative z-10">
                    <h3 class="text-base font-bold text-[#0d326b]">Student Performance</h3>
                    <a href="{{ route('students') }}" 
                        class="px-3 py-1.5 bg-gradient-to-br from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] text-white text-[11px] font-bold rounded-lg hover:from-[#1e4b8f] hover:via-[#1e4b8f] hover:to-[#1a6fd4] transition-all duration-300 shadow-sm flex items-center space-x-1 flex-shrink-0">
                        <span>View All</span>
                        <span class="material-symbols-outlined text-[12px]">arrow_forward</span>
                    </a>
                </div>
                <p class="text-[11px] text-slate-400 font-medium mb-5 relative z-10">Progress overview for all enrolled students</p>

                <div class="space-y-4 relative z-10 flex-1">
                    @forelse($students->take(4) as $student)
                    @php
                        $pct = $student->performancePct;
                        if ($pct >= 75) { $badge = ['label' => 'Good', 'bg' => 'bg-[#0d326b]', 'text' => 'text-white', 'bar' => 'linear-gradient(90deg,#1e4b8f,#0d326b)']; }
                        elseif ($pct >= 40) { $badge = ['label' => 'Fair', 'bg' => 'bg-[#dbeafe]', 'text' => 'text-[#0d326b]', 'bar' => 'linear-gradient(90deg,#93c5fd,#3b82f6)']; }
                        else { $badge = ['label' => 'Needs Help', 'bg' => 'bg-[#eff6ff]', 'text' => 'text-[#1e4b8f]', 'bar' => 'linear-gradient(90deg,#dbeafe,#93c5fd)']; }
                    @endphp
                    <div class="flex items-center gap-3">
                        <img src="{{ $student->avatarUrl() }}"
                             class="w-9 h-9 rounded-full flex-shrink-0 shadow-sm object-cover bg-[#0d326b]"
                             alt="{{ $student->first_name }}"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($student->initials) }}&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45';" />
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1.5 gap-2">
                                <span class="text-[13px] font-bold text-slate-700 truncate">{{ $student->first_name }}</span>
                                <div class="flex items-center space-x-2 flex-shrink-0">
                                    <span class="text-[13px] font-black text-[#0d326b]">{{ $pct }}%</span>
                                    <span class="px-2 py-0.5 text-[9px] font-black rounded-md uppercase tracking-wider whitespace-nowrap {{ $badge['bg'] }} {{ $badge['text'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700"
                                     style="width: {{ $pct }}%; background: {{ $badge['bar'] }}"></div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-sm text-slate-400 py-8 italic">
                        No record — no students added yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Enrollment Trend: hover tooltip on line points ──
    (function () {
        const wrap = document.getElementById('enrollmentChartSvg')?.closest('.relative.z-10');
        const tip = document.getElementById('enrollmentTooltip');
        if (!wrap || !tip) return;
        const tipSy = document.getElementById('enrollmentTooltipSy');
        const tipRange = document.getElementById('enrollmentTooltipRange');
        const tipCount = document.getElementById('enrollmentTooltipCount');

        wrap.querySelectorAll('.enrollment-point-hit').forEach(function (hit) {
            hit.addEventListener('mouseenter', function () {
                const rect = hit.getBoundingClientRect();
                const wrapRect = wrap.getBoundingClientRect();
                tip.style.left = (rect.left - wrapRect.left + rect.width / 2) + 'px';
                tip.style.top = (rect.top - wrapRect.top) + 'px';
                if (tipSy) tipSy.textContent = hit.dataset.sy;
                if (tipRange) tipRange.textContent = hit.dataset.range;
                if (tipCount) tipCount.textContent = hit.dataset.count;
                tip.classList.remove('opacity-0');
                tip.classList.add('opacity-100');
            });
            hit.addEventListener('mouseleave', function () {
                tip.classList.remove('opacity-100');
                tip.classList.add('opacity-0');
            });
        });
    })();

    // ── Lesson Progress: hover tooltip on donut segments ──
    (function () {
        document.querySelectorAll('.donut-segment-hit').forEach(function (seg) {
            const donutWrap = seg.closest('.donut-wrapper') || seg.closest('.relative');
            const tip = donutWrap?.querySelector('#donutTooltip');
            if (!tip) return;
            const tipLabel = tip.querySelector('#donutTooltipLabel');
            const tipValue = tip.querySelector('#donutTooltipValue');

            seg.addEventListener('mouseenter', function () {
                tipLabel.textContent = seg.dataset.label;
                tipValue.textContent = seg.dataset.value;
                tip.classList.remove('opacity-0');
                tip.classList.add('opacity-100');
            });
            seg.addEventListener('mouseleave', function () {
                tip.classList.remove('opacity-100');
                tip.classList.add('opacity-0');
            });
        });
    })();

    // ── Senya Insights ────────────────────────────────────────────────────
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
            btn.addEventListener('click', () => {
                const icon = btn.querySelector('.material-symbols-outlined');
                icon.style.transition = 'transform 0.4s ease';
                icon.style.transform = 'rotate(360deg)';
                setTimeout(() => {
                    icon.style.transition = 'none';
                    icon.style.transform = 'rotate(0deg)';
                }, 420);
                pickRandom();
            });
        }

        // Swipe support (touch devices)
        const body = document.getElementById('insight-body');
        if (body) {
            let startX = 0;
            body.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
            body.addEventListener('touchend', e => {
                const dx = e.changedTouches[0].clientX - startX;
                if (Math.abs(dx) > 40) pickRandom();
            }, { passive: true });
        }
    })();
});
</script>

@endsection