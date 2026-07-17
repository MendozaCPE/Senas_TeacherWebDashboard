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
                    <div class="w-9 h-9 rounded-full bg-[#2563eb] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">group</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Total Students</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $totalStudents }}</p>
                <p class="text-[12px] font-medium text-emerald-600 mt-2 mb-4">↑ {{ $newStudentsThisWeek }} this week</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineTotalStudents ?: array_fill(0, 7, 0), '#2563eb') !!}
                </div>
            </div>

            {{-- Active Today --}}
            <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full bg-[#7c3aed] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">monitor_heart</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Active Today</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $activeToday }}</p>
                <p class="text-[12px] font-medium text-slate-400 mt-2 mb-4">{{ $activeTodayPercent }}% of total</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineActive ?: array_fill(0, 7, 0), '#7c3aed') !!}
                </div>
            </div>

            {{-- Avg. Accuracy --}}
            <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full bg-[#10b981] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">gps_fixed</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Avg. Accuracy</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $avgAccuracy > 0 ? $avgAccuracy . '%' : '0%' }}</p>
                <p class="text-[12px] font-medium text-emerald-600 mt-2 mb-4">↑ {{ max(0, $accuracyWeeklyChange) }}% this week</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineAccuracy ?: array_fill(0, 7, 0), '#10b981') !!}
                </div>
            </div>

            {{-- Lessons Completed --}}
            <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full bg-[#f59e0b] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">menu_book</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Lessons Completed</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $lessonsCompleted }}</p>
                <p class="text-[12px] font-medium text-emerald-600 mt-2 mb-4">↑ {{ $lessonsCompletedThisWeek }} this week</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineLessons ?: array_fill(0, 7, 0), '#f59e0b') !!}
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
                        ['bg' => '#fef9e7', 'tab_bg' => '#fdefc0', 'title' => '#6b5000', 'sub' => '#9a7a00', 'bar_fill' => '#facc15', 'bar_bg' => '#fde68a'],
                        ['bg' => '#dbeafe', 'tab_bg' => '#bfd7f9', 'title' => '#1e3a8a', 'sub' => '#3b5fc0', 'bar_fill' => '#007fff', 'bar_bg' => '#bcd4f8'],
                        ['bg' => '#e0e9ff', 'tab_bg' => '#c7d5ff', 'title' => '#1e2f8a', 'sub' => '#3b4fc0', 'bar_fill' => '#0047ab', 'bar_bg' => '#bcc7f8'],
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
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($s->first_name . '+' . $s->last_name) }}&background={{ ltrim($p['bar_fill'], '#') }}&color=fff&rounded=true&size=60"
                                         class="w-8 h-8 rounded-full border-2 border-white shadow-sm"
                                         title="{{ $s->first_name }} {{ $s->last_name }}"/>
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
                                <a href="{{ route('lessons.view', $lesson->lesson_id) }}"
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
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($s->first_name . '+' . $s->last_name) }}&background={{ ltrim($p['bar_fill'], '#') }}&color=fff&rounded=true&size=60"
                                         class="w-8 h-8 rounded-full border-2 border-white shadow-sm"
                                         title="{{ $s->first_name }} {{ $s->last_name }}"/>
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

                <!-- Practice Sessions Yellow Card -->
                <div class="bg-[#facc15] rounded-[20px] p-6 w-[280px] flex-shrink-0 flex flex-col justify-between shadow-sm relative overflow-hidden" style="min-height: 200px;">
                    <div class="absolute top-0 right-0 w-24 h-24 opacity-10 rounded-bl-full bg-white"></div>
                    <div class="flex justify-between items-start relative z-10">
                        <span class="material-symbols-outlined text-[#0d326b] text-[28px]">assignment_ind</span>
                        <button class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center hover:bg-black/5 transition-colors text-[#0d326b]">
                            <span class="material-symbols-outlined text-[20px]">more_vert</span>
                        </button>
                    </div>
                    <div class="relative z-10">
                        <h4 class="font-bold text-[#0d326b] text-[17px] mb-0.5">Practice Sessions</h4>
                        <p class="text-[#0d326b]/60 text-[12px] mb-3">Active Monitoring</p>
                        <div class="w-full h-2 rounded-full bg-black/10 mb-1.5">
                            <div class="h-full rounded-full bg-[#0d326b]" style="width: 75%;"></div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-[#0d326b] uppercase tracking-wider">75% Activity</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @php
            /* ---------------------------------------------------------
             | Data prep for the redesigned Mastery + Lesson Progress
             | + Student Performance widgets below. Reuses existing
             | controller variables; swap in real series data when
             | available.
             ---------------------------------------------------------- */

            // 7-day trend line — reuse the accuracy sparkline series as the
            // "mastery over time" trend, with date labels for the last 7 days.
            $masterySeries = $sparklineAccuracy ?: array_fill(0, 7, 0);
            if (count($masterySeries) < 7) {
                $masterySeries = array_pad($masterySeries, 7, end($masterySeries) ?: 0);
            }
            $masterySeries = array_slice($masterySeries, -7);

            $masteryDayLabels = [];
            for ($i = 6; $i >= 0; $i--) {
                $masteryDayLabels[] = $today->copy()->subDays($i)->format('M j');
            }

            $masteryCurrentValue = (int) round(end($masterySeries));

            // Lesson Progress donut — derive Completed / In Progress / Not Started
            // counts from the lesson mastery collection.
            $lpTotal = $lessons->count();
            $lpCompleted = $lessonMastery->where('masteryPct', '>=', 100)->count();
            $lpInProgress = $lessonMastery->filter(fn ($lm) => $lm->masteryPct > 0 && $lm->masteryPct < 100)->count();
            $lpNotStarted = max(0, $lpTotal - $lpCompleted - $lpInProgress);

            $donutCircumference = round(2 * pi() * 60, 4);

            // Segment order: Completed, In Progress, Not Started
            $donutSegments = $lpTotal > 0 ? [
                ['count' => $lpCompleted, 'color' => '#10b981'],   // emerald — matches existing "Good" badge
                ['count' => $lpInProgress, 'color' => '#1a6fd4'],  // brand blue
                ['count' => $lpNotStarted, 'color' => '#e2e8f0'],  // slate-200
            ] : [];

            $donutOffset = 0;
        @endphp

    </div>

    <!-- Right Sidebar Column -->
    <div class="w-[340px] flex-shrink-0 flex flex-col space-y-4 pl-4 self-stretch">
        
        <!-- Senya Tip Widget -->
        <div class="rounded-[32px] p-8 relative overflow-hidden shadow-sm" style="background: linear-gradient(135deg, #f59e0b 0%, #facc15 50%, #fbbf24 100%)">
            <div class="absolute -bottom-16 -right-16 text-[#eab308] opacity-50 transform rotate-45">
                <svg width="150" height="150" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L15 9L22 12L15 15L12 22L9 15L2 12L9 9L12 2Z"/></svg>
            </div>
            <div class="flex items-center space-x-3 mb-6 relative z-10">
                <div class="w-[42px] h-[42px] bg-white rounded-full flex items-center justify-center shadow-sm overflow-hidden p-1">
                    <img src="{{ asset('images/senya_teaching.png') }}" alt="Senya" class="w-full h-full object-contain"/>
                </div>
                <span class="text-[11px] font-black uppercase tracking-[0.15em] text-[#1e293b]">Senya Tip</span>
            </div>
            <p class="text-[14px] font-bold text-[#1e293b] leading-[1.6] mb-8 relative z-10">
                You have <span class="text-[#0d326b]">{{ $allStudents->count() }} student{{ $allStudents->count() === 1 ? '' : 's' }}</span> enrolled. Keep them engaged with regular lessons!
            </p>
            <a href="{{ route('students') }}" class="block w-full bg-[#18181b] text-[#facc15] text-[13px] font-bold py-3.5 rounded-xl hover:bg-black transition-colors shadow-sm relative z-10 text-center">
                View Students
            </a>
        </div>

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

            <!-- Scrollable list — fixed height shows ~5 rows, scrolls for more -->
            <div class="overflow-y-auto divide-y divide-slate-50 flex-1 scrollbar-hide" style="max-height: 360px;">
                @forelse($allStudents as $s)
                @php
                    $mastery = $s->fsl_mastery_level ?? 'Beginner';
                    // Badge colour keyed by mastery level
                    $masteryMap = [
                        'beginner'     => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'dot' => '#3b82f6'],
                        'elementary'   => ['bg' => '#f0fdf4', 'text' => '#15803d', 'dot' => '#22c55e'],
                        'intermediate' => ['bg' => '#fefce8', 'text' => '#a16207', 'dot' => '#eab308'],
                        'advanced'     => ['bg' => '#fdf4ff', 'text' => '#7e22ce', 'dot' => '#a855f7'],
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
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($s->first_name . '+' . $s->last_name) }}&background=0d326b&color=fff&rounded=true&size=80"
                             class="w-11 h-11 rounded-full shadow-sm"
                             alt="{{ $s->first_name }}"/>
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

            <!-- Student Mastery Overview (line/area chart) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 relative overflow-hidden">
                <div class="absolute -top-8 -right-8 w-32 h-32 bg-indigo-50 rounded-full opacity-60"></div>

                <div class="flex items-center justify-between mb-6 relative z-10">
                    <h3 class="text-base font-bold text-[#0d326b]">Student Mastery Overview</h3>
                </div>

                @if(array_sum($masterySeries) === 0 && $lessonMastery->isEmpty())
                    <div class="text-center text-sm text-slate-400 py-12 italic relative z-10">No record</div>
                @else
                @php
                    $chartW = 320; $chartH = 180;
                    $padL = 34; $padR = 12; $padT = 32; $padB = 26;
                    $plotW = $chartW - $padL - $padR;
                    $plotH = $chartH - $padT - $padB;
                    $maxVal = 100; $minVal = 0;
                    $n = count($masterySeries);
                    $pts = [];
                    foreach ($masterySeries as $i => $v) {
                        $x = $padL + ($n > 1 ? ($i / ($n - 1)) * $plotW : $plotW / 2);
                        $y = $padT + $plotH - (($v - $minVal) / max($maxVal - $minVal, 1)) * $plotH;
                        $pts[] = [round($x, 1), round($y, 1)];
                    }
                    $polyPoints = implode(' ', array_map(fn($p) => $p[0] . ',' . $p[1], $pts));
                    $areaPoints = $polyPoints . ' ' . ($padL + $plotW) . ',' . ($padT + $plotH) . ' ' . $padL . ',' . ($padT + $plotH);
                    $lastPt = end($pts);
                @endphp
                <div class="relative z-10">
                    <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="w-full h-[180px]" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="masteryAreaFill" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#1a6fd4" stop-opacity="0.25"/>
                                <stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/>
                            </linearGradient>
                        </defs>

                        <!-- Gridlines + Y labels -->
                        @foreach([0, 25, 50, 75, 100] as $gv)
                        @php $gy = round($padT + $plotH - ($gv / 100) * $plotH, 1); @endphp
                        <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $padL + $plotW }}" y2="{{ $gy }}" stroke="#f1f5f9" stroke-width="1"/>
                        <text x="0" y="{{ $gy + 4 }}" font-size="10" fill="#94a3b8" font-weight="600">{{ $gv }}%</text>
                        @endforeach

                        <!-- Area fill -->
                        <polygon points="{{ $areaPoints }}" fill="url(#masteryAreaFill)"/>

                        <!-- Line -->
                        <polyline fill="none" stroke="#0d326b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="{{ $polyPoints }}"/>

                        <!-- Points -->
                        @foreach($pts as $i => $p)
                        <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="{{ $i === count($pts) - 1 ? 5 : 3.5 }}" fill="{{ $i === count($pts) - 1 ? '#1a6fd4' : '#0d326b' }}" stroke="white" stroke-width="2"/>
                        @endforeach

                        <!-- X labels -->
                        @foreach($masteryDayLabels as $i => $label)
                        <text x="{{ $pts[$i][0] }}" y="{{ $chartH - 4 }}" font-size="9" fill="#94a3b8" font-weight="600" text-anchor="middle">{{ $label }}</text>
                        @endforeach
                    </svg>

                    <!-- Callout bubble for current value -->
                    <div class="absolute top-0 right-0 bg-white border border-slate-100 shadow-sm rounded-full px-3 py-1">
                        <span class="text-[13px] font-black text-[#0d326b]">{{ $masteryCurrentValue }}%</span>
                    </div>
                </div>
                @endif
            </div>

            <!-- Lesson Progress (donut chart) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 relative overflow-hidden flex flex-col">
                <div class="absolute -top-10 -right-10 w-36 h-36 bg-yellow-50 rounded-full opacity-60"></div>

                <h3 class="text-base font-bold text-[#0d326b] mb-6 relative z-10">Lesson Progress</h3>

                @if($lpTotal === 0)
                    <div class="flex-1 flex items-center justify-center text-sm text-slate-400 italic relative z-10">No record</div>
                @else
                <div class="flex items-center gap-5 relative z-10">
                    <!-- Donut -->
                    <div class="relative w-28 h-28 flex-shrink-0">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 144 144">
                            <circle cx="72" cy="72" r="60" fill="transparent" stroke="#f1f5f9" stroke-width="14"></circle>
                            @php $cumulative = 0; @endphp
                            @foreach($donutSegments as $seg)
                                @if($seg['count'] > 0)
                                @php
                                    $segLen = round(($seg['count'] / $lpTotal) * $donutCircumference, 2);
                                    $gap = round($donutCircumference - $segLen, 2);
                                    $offset = round(-1 * ($cumulative / $lpTotal) * $donutCircumference, 2);
                                @endphp
                                <circle cx="72" cy="72" r="60" fill="transparent"
                                        stroke="{{ $seg['color'] }}"
                                        stroke-width="14"
                                        stroke-dasharray="{{ $segLen }} {{ $gap }}"
                                        stroke-dashoffset="{{ $offset }}"
                                        stroke-linecap="round"></circle>
                                @php $cumulative += $seg['count']; @endphp
                                @endif
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-black text-[#0d326b] leading-none">{{ $lpTotal }}</span>
                            <span class="text-[8px] font-bold uppercase tracking-widest text-slate-400 mt-1">Total</span>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="space-y-3 flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:#10b981"></div>
                                <span class="text-[12px] font-semibold text-slate-600 truncate">Completed</span>
                            </div>
                            <span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $lpCompleted }} ({{ round(($lpCompleted / $lpTotal) * 100) }}%)</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:#1a6fd4"></div>
                                <span class="text-[12px] font-semibold text-slate-600 truncate">In Progress</span>
                            </div>
                            <span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $lpInProgress }} ({{ round(($lpInProgress / $lpTotal) * 100) }}%)</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-200 flex-shrink-0"></div>
                                <span class="text-[12px] font-semibold text-slate-600 truncate">Not Started</span>
                            </div>
                            <span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $lpNotStarted }} ({{ round(($lpNotStarted / $lpTotal) * 100) }}%)</span>
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
                        if ($pct >= 75) { $badge = ['label' => 'Good', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'bar' => 'linear-gradient(90deg,#059669,#10b981)']; }
                        elseif ($pct >= 40) { $badge = ['label' => 'Fair', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'bar' => 'linear-gradient(90deg,#1e40af,#6366f1)']; }
                        else { $badge = ['label' => 'Needs Help', 'bg' => 'bg-red-50', 'text' => 'text-red-600', 'bar' => 'linear-gradient(90deg,#dc2626,#f87171)']; }
                    @endphp
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name . ' ' . $student->last_name) }}&background=random&color=fff&rounded=true"
                             class="w-9 h-9 rounded-full flex-shrink-0 shadow-sm"/>
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

@endsection