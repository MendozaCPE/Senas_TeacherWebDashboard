@extends('layouts.app')
@section('title', 'Students')
@section('content')

<div class="space-y-6">

    {{-- ═══════════════════════════════════════════════
         HEADER ROW
    ═══════════════════════════════════════════════ --}}
    <div class="flex items-start justify-between">
        <div>
            <p class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-1">Management</p>
            <h2 class="text-[32px] font-semibold text-[#0d326b] leading-tight">Student Overview</h2>
        </div>
        <button id="open-modal-btn"
            class="bg-gradient-to-r from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] hover:opacity-90 text-white px-5 py-3 rounded-xl text-[14px] font-bold transition-all flex items-center space-x-2 shadow-md mt-1 border border-[#0d326b]/20">
            <span class="material-symbols-outlined icon-outline text-[20px]">person_add</span>
            <span>Add New Student</span>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════
         STAT CARDS ROW  (3 cards like the image)
    ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-3 gap-4">

        {{-- Total Students — Navy --}}
        <div class="rounded-2xl p-6 flex items-end justify-between relative overflow-hidden text-white shadow-md" style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%)">
            <div class="absolute -top-8 -right-8 w-28 h-28 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white/5 rounded-full"></div>
            <div class="relative z-10">
                <div class="flex items-center space-x-2 mb-3">
                    <span class="material-symbols-outlined text-white/60 text-[20px]">group</span>
                    <p class="text-[11px] font-bold text-white/60 uppercase tracking-widest">Total Students</p>
                </div>
                <p class="text-[48px] font-black text-white leading-none">{{ $totalStudents }}</p>
                <p class="text-[12px] font-semibold text-[#facc15] mt-2">+{{ $newThisWeek }} this month</p>
            </div>
        </div>

        {{-- Avg. Progress — White --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute -top-8 -right-8 w-28 h-28 bg-slate-50 rounded-full"></div>
            <div class="relative z-10">
                <div class="flex items-center space-x-2 mb-3">
                    <span class="material-symbols-outlined text-slate-400 text-[20px]">trending_up</span>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Avg. Progress</p>
                </div>
                @php
                    $avgXp = $students->count() ? round($students->avg('total_xp')) : 0;
                    $maxXp = 10000;
                    $progressPct = min(100, round($avgXp / $maxXp * 100));
                @endphp
                <p class="text-[48px] font-black text-[#0d326b] leading-none">{{ $progressPct }}%</p>
                <div class="mt-3 h-1.5 bg-slate-100 rounded-full overflow-hidden w-full">
                    <div class="h-full bg-[#0d326b] rounded-full" style="width: {{ $progressPct }}%"></div>
                </div>
            </div>
        </div>

        {{-- Top Mastery — Yellow --}}
        <div class="bg-[#facc15] rounded-2xl p-6 relative overflow-hidden shadow-md">
            <div class="absolute -top-8 -right-8 w-28 h-28 bg-[#0d326b]/5 rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-[#0d326b]/5 rounded-full"></div>
            <div class="relative z-10">
                <div class="flex items-center space-x-2 mb-3">
                    <span class="material-symbols-outlined text-[#0d326b]/60 text-[20px]">workspace_premium</span>
                    <p class="text-[11px] font-bold text-[#0d326b]/60 uppercase tracking-widest">Top Mastery</p>
                </div>
                @php
                    $advancedCount    = $students->where('fsl_mastery_level','Advanced')->count();
                    $intermediateCount = $students->where('fsl_mastery_level','Intermediate')->count();
                    $topLabel = $advancedCount > 0
                        ? 'Advanced'
                        : ($intermediateCount > 0 ? 'Intermediate' : 'Beginner');
                    $topCount = $advancedCount > 0 ? $advancedCount : ($intermediateCount > 0 ? $intermediateCount : $students->where('fsl_mastery_level','Beginner')->count());
                @endphp
                <p class="text-[32px] font-black text-[#0d326b] leading-none">{{ $topLabel }}</p>
                <p class="text-[13px] font-semibold text-[#0d326b]/70 mt-1">{{ $topCount }} student{{ $topCount !== 1 ? 's' : '' }}</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         MAIN CONTENT: TABLE + SIDEBAR
    ═══════════════════════════════════════════════ --}}
    <div class="flex gap-5 items-start">

        {{-- LEFT: Table Panel --}}
        <div class="flex-1 min-w-0">

            {{-- Filter Toolbar --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-3.5 mb-4 flex items-center gap-3 flex-wrap">

                {{-- Segmented Control --}}
                <div class="bg-[#f1f5f9] p-1 rounded-full flex items-center shadow-inner shrink-0">
                    <button data-filter="all"
                        class="filter-tab px-5 py-2 bg-white text-[#0d326b] text-[12px] font-bold rounded-full shadow-sm transition-all">
                        All Students
                    </button>
                    <button data-filter="active"
                        class="filter-tab px-5 py-2 text-slate-500 hover:text-[#0d326b] text-[12px] font-medium rounded-full transition-all">
                        Active Only
                    </button>
                </div>

                <div class="flex-1"></div>

                {{-- Level Filter --}}
                <div class="relative shrink-0">
                    <select id="filter-level"
                        class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[12px] font-semibold py-2.5 pl-4 pr-9 rounded-full outline-none border border-transparent hover:bg-slate-200 transition-colors cursor-pointer">
                        <option value="">Filter: Level</option>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[16px] text-slate-500 pointer-events-none">expand_more</span>
                </div>

                {{-- Grade Filter --}}
                <div class="relative shrink-0">
                    <select id="filter-grade"
                        class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[12px] font-semibold py-2.5 pl-4 pr-9 rounded-full outline-none border border-transparent hover:bg-slate-200 transition-colors cursor-pointer">
                        <option value="">Filter: Grade</option>
                        <option value="Grade 1">Grade 1</option>
                        <option value="Grade 2">Grade 2</option>
                        <option value="Grade 3">Grade 3</option>
                        <option value="Grade 4">Grade 4</option>
                        <option value="Grade 5">Grade 5</option>
                        <option value="Grade 6">Grade 6</option>
                        <option value="SPED A">SPED A</option>
                        <option value="SPED B">SPED B</option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[16px] text-slate-500 pointer-events-none">expand_more</span>
                </div>

                {{-- Search --}}
                <div class="relative shrink-0">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[18px]">search</span>
                    <input id="student-search" type="text" placeholder="Search…"
                        class="bg-[#f1f5f9] text-[13px] font-medium py-2.5 pl-9 pr-4 rounded-full outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400 w-[160px]" />
                </div>

                {{-- Results count --}}
                <span id="results-count" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider shrink-0"></span>
            </div>

            {{-- Student Table --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse" id="students-table">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="py-4 px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase">Student Profile</th>
                            <th class="py-4 px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase">Status & Level</th>
                            <th class="py-4 px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase">XP Progression</th>
                            <th class="py-4 px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase">Enrolled</th>
                            <th class="py-4 px-5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50" id="student-tbody">
                        @forelse($students as $student)
                        @php
                            $masteryColor = match($student->fsl_mastery_level) {
                                'Advanced'     => ['bg' => 'bg-[#0d326b]',   'text' => 'text-white'],
                                'Intermediate' => ['bg' => 'bg-[#e0e7ff]',   'text' => 'text-[#4f46e5]'],
                                default        => ['bg' => 'bg-slate-100',   'text' => 'text-slate-600'],
                            };
                            $statusColor = match($student->status) {
                                'active'   => ['dot' => 'bg-emerald-400', 'text' => 'text-emerald-600'],
                                'inactive' => ['dot' => 'bg-slate-300',   'text' => 'text-slate-400'],
                                default    => ['dot' => 'bg-red-300',     'text' => 'text-red-400'],
                            };
                            $xpMax = 10000;
                            $xpPct = min(100, round(($student->total_xp / $xpMax) * 100));
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors group student-row"
                            data-status="{{ $student->status }}"
                            data-grade="{{ $student->grade_level ?? '' }}"
                            data-mastery="{{ $student->fsl_mastery_level }}"
                            data-name="{{ strtolower($student->first_name . ' ' . $student->last_name) }}">

                            {{-- Student Profile --}}
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full shadow-sm shrink-0 overflow-hidden bg-[#e0e7ff] flex items-center justify-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name . '+' . $student->last_name) }}&background=random&color=fff&rounded=true&size=80"
                                             class="w-10 h-10 rounded-full" />
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-bold text-[#0d326b]">{{ $student->first_name }} {{ $student->last_name }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium mt-0.5">
                                            LRN: {{ $student->lrn ?? 'N/A' }}
                                            @if($student->grade_level)
                                            · {{ $student->grade_level }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Status & FSL Level --}}
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-1.5 mb-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusColor['dot'] }} shrink-0"></span>
                                    <span class="text-[11px] font-bold {{ $statusColor['text'] }} uppercase tracking-wide">{{ $student->status }}</span>
                                </div>
                                <span class="px-2.5 py-1 {{ $masteryColor['bg'] }} {{ $masteryColor['text'] }} text-[10px] font-bold rounded-full uppercase tracking-wider">
                                    {{ $student->fsl_mastery_level }}
                                </span>
                            </td>

                            {{-- XP Progression --}}
                            <td class="py-4 px-5">
                                <div class="w-[160px]">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] text-slate-400 font-medium">Lv.{{ $student->level }}</span>
                                        <span class="text-[10px] font-bold text-[#0d326b]">{{ number_format($student->total_xp) }} XP</span>
                                    </div>
                                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#0d326b] rounded-full transition-all duration-500" style="width: {{ $xpPct }}%"></div>
                                    </div>
                                </div>
                            </td>

                            {{-- Enrolled --}}
                            <td class="py-4 px-5">
                                <p class="text-[12px] font-medium text-[#1e293b]">
                                    {{ $student->created_at ? $student->created_at->diffForHumans() : 'N/A' }}
                                </p>
                            </td>

                            {{-- Action --}}
                            <td class="py-4 px-5 text-right">
                                <button class="w-8 h-8 rounded-full flex items-center justify-center text-slate-300 hover:bg-slate-100 hover:text-[#0d326b] transition-colors ml-auto">
                                    <span class="material-symbols-outlined icon-outline text-[20px]">chevron_right</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr id="empty-state-row">
                            <td colspan="5">
                                <div class="flex flex-col items-center justify-center py-16 text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-[#e8eef8] flex items-center justify-center mb-4">
                                        <span class="material-symbols-outlined text-[#0d326b] text-[32px]">group_off</span>
                                    </div>
                                    <p class="text-[16px] font-bold text-[#0d326b] mb-1">No students yet</p>
                                    <p class="text-[13px] text-slate-400 font-medium mb-5">Add your first student to get started.</p>
                                    <button onclick="document.getElementById('open-modal-btn').click()"
                                        class="bg-[#0d326b] hover:bg-[#154188] text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-colors">
                                        Add Student
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- No results from filter --}}
                <div id="no-filter-results" class="hidden py-12 text-center">
                    <p class="text-[14px] font-bold text-slate-400 mb-1">No students match your filters</p>
                    <p class="text-[12px] text-slate-400">Try adjusting the search or filter options above.</p>
                </div>

                {{-- Pagination --}}
                @if($students->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $students->links('pagination::tailwind') }}
                </div>
                @endif
            </div>
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="w-[260px] shrink-0 space-y-4">

            {{-- FSL Mastery Donut --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">FSL Mastery</p>
                @php
                    $total         = $students->count() ?: 1;
                    $beginnerCount = $students->where('fsl_mastery_level','Beginner')->count();
                    $intermCount   = $students->where('fsl_mastery_level','Intermediate')->count();
                    $advCount      = $students->where('fsl_mastery_level','Advanced')->count();
                    $beginnerPct   = round($beginnerCount / $total * 100);
                    $intermPct     = round($intermCount   / $total * 100);
                    $advancedPct   = round($advCount      / $total * 100);

                    // SVG donut: radius 40, circumference ≈ 251.3
                    $circ = 251.3;
                    $begDash  = round($beginnerPct / 100 * $circ, 1);
                    $intDash  = round($intermPct   / 100 * $circ, 1);
                    $advDash  = round($advancedPct / 100 * $circ, 1);
                    $begOffset  = 0;
                    $intOffset  = -$begDash;
                    $advOffset  = -($begDash + $intDash);
                @endphp

                {{-- Donut chart --}}
                <div class="flex items-center justify-center mb-4">
                    <div class="relative w-[120px] h-[120px]">
                        <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                            {{-- Background ring --}}
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                            {{-- Beginner (yellow) --}}
                            @if($begDash > 0)
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#facc15" stroke-width="14"
                                stroke-dasharray="{{ $begDash }} {{ $circ - $begDash }}"
                                stroke-dashoffset="{{ $begOffset }}" stroke-linecap="butt"/>
                            @endif
                            {{-- Intermediate (navy) --}}
                            @if($intDash > 0)
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#0d326b" stroke-width="14"
                                stroke-dasharray="{{ $intDash }} {{ $circ - $intDash }}"
                                stroke-dashoffset="{{ $intOffset }}" stroke-linecap="butt"/>
                            @endif
                            {{-- Advanced (slate) --}}
                            @if($advDash > 0)
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#94a3b8" stroke-width="14"
                                stroke-dasharray="{{ $advDash }} {{ $circ - $advDash }}"
                                stroke-dashoffset="{{ $advOffset }}" stroke-linecap="butt"/>
                            @endif
                        </svg>
                        {{-- Center label --}}
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-[22px] font-black text-[#0d326b]">{{ $students->count() }}</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Students</span>
                        </div>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#facc15] shrink-0"></span>
                            <span class="text-[12px] font-medium text-slate-600">Beginner</span>
                        </div>
                        <span class="text-[12px] font-bold text-slate-500">{{ $beginnerPct }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#0d326b] shrink-0"></span>
                            <span class="text-[12px] font-medium text-slate-600">Intermediate</span>
                        </div>
                        <span class="text-[12px] font-bold text-slate-500">{{ $intermPct }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400 shrink-0"></span>
                            <span class="text-[12px] font-medium text-slate-600">Advanced</span>
                        </div>
                        <span class="text-[12px] font-bold text-slate-500">{{ $advancedPct }}%</span>
                    </div>
                </div>
            </div>

            {{-- Top XP Earners --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Top XP Earners</p>
                @php $topStudents = $students->sortByDesc('total_xp')->take(3); @endphp
                @if($topStudents->count())
                <div class="space-y-3">
                    @foreach($topStudents as $idx => $ts)
                    @php
                        $rankColors = [
                            ['ring' => 'ring-[#facc15]', 'bg' => 'bg-[#facc15]/10', 'badge' => 'bg-[#facc15] text-[#0d326b]'],
                            ['ring' => 'ring-slate-200',  'bg' => 'bg-slate-50',     'badge' => 'bg-slate-200 text-slate-600'],
                            ['ring' => 'ring-amber-200',  'bg' => 'bg-amber-50/50',  'badge' => 'bg-amber-100 text-amber-700'],
                        ];
                        $rc = $rankColors[$idx] ?? $rankColors[2];
                    @endphp
                    <div class="flex items-center space-x-3 p-2.5 rounded-xl {{ $rc['bg'] }}">
                        <div class="relative shrink-0">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($ts->first_name . '+' . $ts->last_name) }}&background=random&color=fff&rounded=true&size=60"
                                 class="w-9 h-9 rounded-full ring-2 {{ $rc['ring'] }}" />
                            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full {{ $rc['badge'] }} text-[8px] font-black flex items-center justify-center">{{ $idx + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[12px] font-bold text-[#0d326b] truncate">{{ $ts->first_name }} {{ $ts->last_name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ number_format($ts->total_xp) }} XP</p>
                        </div>
                        @if($idx === 0)
                        <span class="material-symbols-outlined text-[#facc15] text-[18px] shrink-0">star</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-[12px] text-slate-400 text-center py-3">No XP data yet.</p>
                @endif
            </div>

        </div>{{-- /sidebar --}}
    </div>{{-- /main content --}}
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     ADD STUDENT MODAL
═══════════════════════════════════════════════════════════════════ --}}
<div id="add-student-modal"
     class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">

    <div class="bg-white rounded-[32px] w-[620px] max-w-full p-8 shadow-2xl relative transform scale-95 transition-transform duration-300">

        {{-- Close --}}
        <button id="close-modal-btn" class="absolute top-7 right-7 text-slate-400 hover:text-slate-600 outline-none">
            <span class="material-symbols-outlined text-[24px]">close</span>
        </button>

        {{-- Header --}}
        <div class="mb-6">
            <h2 class="text-[24px] font-bold text-[#0d326b] mb-1">Add New Students</h2>
            <p class="text-[13px] text-slate-400 font-medium">Populate your classroom</p>
        </div>

        {{-- Tabs --}}
        <div class="flex space-x-6 border-b border-slate-100 mb-6 text-[12px] font-bold tracking-wider uppercase">
            <button id="tab-single" class="text-[#0d326b] border-b-2 border-[#0d326b] pb-3 outline-none transition-all">Single Student</button>
            <button id="tab-bulk"   class="text-slate-400 border-b-2 border-transparent pb-3 hover:text-slate-600 outline-none transition-all">Bulk Add (Excel)</button>
        </div>

        {{-- Alert --}}
        <div id="modal-alert" class="hidden mb-5 p-4 rounded-xl text-sm font-medium flex items-start space-x-2 border">
            <span id="modal-alert-icon" class="material-symbols-outlined text-[20px] mt-0.5 shrink-0"></span>
            <div id="modal-alert-message" class="flex-1"></div>
        </div>

        {{-- ── Single Student Form ── --}}
        <form id="form-single" class="block" onsubmit="submitSingleStudent(event)">
            @csrf
            <div class="grid grid-cols-2 gap-x-6 gap-y-5 mb-6">
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Learner Reference Number (LRN)</label>
                    <input type="text" name="lrn" required placeholder="12-digit LRN" pattern="\d{12}" maxlength="12"
                           title="LRN must be exactly 12 digits"
                           class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Full Name</label>
                    <input type="text" name="full_name" required placeholder="Last Name, First Name"
                           class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Grade Level</label>
                    <div class="relative">
                        <select name="grade_level"
                                class="w-full bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 pl-4 pr-10 rounded-xl outline-none border border-transparent focus:border-slate-300 appearance-none transition-all cursor-pointer">
                            <option value="">Select Grade</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 4">Grade 4</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 6">Grade 6</option>
                            <option value="SPED A">SPED A</option>
                            <option value="SPED B">SPED B</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                    </div>
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Age</label>
                    <input type="number" name="age" required min="1" max="100" placeholder="Enter age"
                           class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Section</label>
                    <input type="text" name="section" placeholder="e.g. SPED-A"
                           class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">FSL Mastery Level</label>
                    <div class="relative">
                        <select name="fsl_mastery_level"
                                class="w-full bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 pl-4 pr-10 rounded-xl outline-none border border-transparent focus:border-slate-300 appearance-none transition-all cursor-pointer">
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                    </div>
                </div>
            </div>

            {{-- Auto-PIN --}}
            <div class="bg-[#f1f5f9] p-4 rounded-[20px] flex items-center justify-between mb-8 shadow-sm">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-[#0d326b] shadow-sm">
                        <span class="material-symbols-outlined text-[20px]">lock</span>
                    </div>
                    <div>
                        <p class="text-[13px] font-bold text-[#0d326b]">Auto-generate Student PIN</p>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Default: Student LRN</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="auto_pin" value="1" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white
                                after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0d326b]"></div>
                </label>
            </div>

            <div class="flex items-center justify-end space-x-4">
                <button type="button" class="btn-cancel px-6 py-3 text-slate-500 hover:text-slate-800 font-semibold text-[14px] transition-colors">Cancel</button>
                <button type="submit" id="btn-single-submit"
                        class="bg-[#0d326b] hover:bg-[#154188] text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-colors flex items-center justify-center">
                    Save Student
                </button>
            </div>
        </form>

        {{-- ── Bulk Add ── --}}
        <div id="container-bulk" class="hidden">
            <div id="drop-zone"
                 class="border-2 border-dashed border-slate-300 hover:border-[#0d326b] rounded-[24px] p-10 flex flex-col items-center justify-center space-y-4 mb-6 transition-all cursor-pointer relative bg-slate-50/50">
                <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" class="absolute inset-0 opacity-0 cursor-pointer" />
                <div id="upload-icon-container" class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 shadow-sm">
                    <span id="upload-icon" class="material-symbols-outlined text-[28px]">article</span>
                </div>
                <div class="text-center">
                    <p id="upload-primary-text" class="text-[15px] font-bold text-slate-700">Drag and drop your student roster here</p>
                    <p id="upload-secondary-text" class="text-[12px] text-slate-400 mt-1">.xlsx only, max 5MB</p>
                </div>
                <button type="button" id="browse-btn"
                        class="border border-[#0d326b] text-[#0d326b] hover:bg-[#0d326b]/5 font-bold text-[13px] px-6 py-2.5 rounded-xl transition-colors pointer-events-none">
                    Browse Files
                </button>
            </div>

            <div class="bg-[#f1f5f9] p-4 rounded-[20px] flex items-center justify-between mb-8 shadow-sm">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-[#0d326b] shadow-sm">
                        <span class="material-symbols-outlined text-[20px]">lock</span>
                    </div>
                    <div>
                        <p class="text-[13px] font-bold text-[#0d326b]">Auto-generate Student PINs</p>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Apply to all imported students</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="bulk-auto-pin" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white
                                after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0d326b]"></div>
                </label>
            </div>

            <div class="flex items-center justify-end space-x-4">
                <button type="button" class="btn-cancel px-6 py-3 text-slate-500 hover:text-slate-800 font-semibold text-[14px] transition-colors">Cancel</button>
                <button type="button" id="btn-import-submit" disabled
                        class="bg-slate-300 text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-not-allowed flex items-center justify-center">
                    Confirm Import
                </button>
            </div>
        </div>

    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
// ─── DOM refs ───────────────────────────────────────────────────────────────
const openModalBtn    = document.getElementById('open-modal-btn');
const closeModalBtn   = document.getElementById('close-modal-btn');
const cancelBtns      = document.querySelectorAll('.btn-cancel');
const modal           = document.getElementById('add-student-modal');
const modalCard       = modal.querySelector('.bg-white');
const tabSingle       = document.getElementById('tab-single');
const tabBulk         = document.getElementById('tab-bulk');
const formSingle      = document.getElementById('form-single');
const containerBulk   = document.getElementById('container-bulk');
const modalAlert      = document.getElementById('modal-alert');
const modalAlertIcon  = document.getElementById('modal-alert-icon');
const modalAlertMsg   = document.getElementById('modal-alert-message');
const dropZone        = document.getElementById('drop-zone');
const excelInput      = document.getElementById('excel-file');
const btnImport       = document.getElementById('btn-import-submit');
const uploadIcon      = document.getElementById('upload-icon');
const uploadIconWrap  = document.getElementById('upload-icon-container');
const uploadPrimary   = document.getElementById('upload-primary-text');
const uploadSecondary = document.getElementById('upload-secondary-text');

let parsedStudents = [];

// ─── Modal open/close ────────────────────────────────────────────────────────
openModalBtn.addEventListener('click', () => {
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0');
        modalCard.classList.remove('scale-95');
    });
});

function closeModal() {
    modal.classList.add('opacity-0');
    modalCard.classList.add('scale-95');
    setTimeout(() => { modal.classList.add('hidden'); resetModal(); }, 300);
}

closeModalBtn.addEventListener('click', closeModal);
cancelBtns.forEach(b => b.addEventListener('click', closeModal));
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

// ─── Tabs ────────────────────────────────────────────────────────────────────
const ACTIVE_TAB   = 'text-[#0d326b] border-b-2 border-[#0d326b] pb-3 outline-none transition-all';
const INACTIVE_TAB = 'text-slate-400 border-b-2 border-transparent pb-3 hover:text-slate-600 outline-none transition-all';

tabSingle.addEventListener('click', () => {
    tabSingle.className = ACTIVE_TAB; tabBulk.className = INACTIVE_TAB;
    formSingle.classList.remove('hidden'); containerBulk.classList.add('hidden');
});
tabBulk.addEventListener('click', () => {
    tabBulk.className = ACTIVE_TAB; tabSingle.className = INACTIVE_TAB;
    containerBulk.classList.remove('hidden'); formSingle.classList.add('hidden');
});

// ─── Alert helpers ───────────────────────────────────────────────────────────
function showAlert(msg, type = 'error') {
    modalAlert.classList.remove('hidden','bg-red-50','border-red-200','text-red-800','bg-emerald-50','border-emerald-200','text-emerald-800');
    modalAlertIcon.innerText = type === 'error' ? 'error' : 'check_circle';
    modalAlert.classList.add(type === 'error' ? 'bg-red-50' : 'bg-emerald-50',
                              type === 'error' ? 'border-red-200' : 'border-emerald-200',
                              type === 'error' ? 'text-red-800' : 'text-emerald-800');
    modalAlertMsg.innerHTML = msg;
}
function hideAlert() { modalAlert.classList.add('hidden'); }

function resetModal() {
    formSingle.reset();
    parsedStudents = [];
    resetUploadArea();
    hideAlert();
    tabSingle.click();
}

// ─── Drag & Drop ─────────────────────────────────────────────────────────────
['dragenter','dragover'].forEach(ev => dropZone.addEventListener(ev, e => {
    e.preventDefault();
    dropZone.classList.add('border-[#0d326b]','bg-[#0d326b]/5');
}));
['dragleave','drop'].forEach(ev => dropZone.addEventListener(ev, e => {
    e.preventDefault();
    dropZone.classList.remove('border-[#0d326b]','bg-[#0d326b]/5');
}));
dropZone.addEventListener('drop', e => {
    const f = e.dataTransfer.files[0];
    if (f) { excelInput.files = e.dataTransfer.files; handleExcelFile(f); }
});
excelInput.addEventListener('change', e => { if (e.target.files[0]) handleExcelFile(e.target.files[0]); });

function handleExcelFile(file) {
    hideAlert();
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['xlsx','xls','csv'].includes(ext)) {
        showAlert('Invalid file format. Please upload .xlsx, .xls, or .csv.'); resetUploadArea(); return;
    }
    const reader = new FileReader();
    reader.onload = e => {
        try {
            const wb  = XLSX.read(new Uint8Array(e.target.result), { type: 'array' });
            const ws  = wb.Sheets[wb.SheetNames[0]];
            const raw = XLSX.utils.sheet_to_json(ws, { header: 1 });
            parsedStudents = mapExcelData(raw);
            if (!parsedStudents.length) { showAlert('File is empty or has no student rows.'); resetUploadArea(); return; }
            showUploadedFile(file.name, parsedStudents.length);
        } catch (err) {
            showAlert(err.message || 'Failed to parse file.'); resetUploadArea();
        }
    };
    reader.readAsArrayBuffer(file);
}

function showUploadedFile(name, count) {
    uploadIcon.innerText = 'check';
    uploadIconWrap.className = 'w-14 h-14 bg-emerald-500 rounded-full flex items-center justify-center text-white shadow-sm';
    uploadPrimary.innerText   = name;
    uploadSecondary.innerText = `${count} students detected. Ready to import.`;
    btnImport.removeAttribute('disabled');
    btnImport.className = 'bg-[#0d326b] hover:bg-[#154188] text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-pointer flex items-center justify-center';
}

function resetUploadArea() {
    excelInput.value          = '';
    uploadIcon.innerText      = 'article';
    uploadIconWrap.className  = 'w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 shadow-sm';
    uploadPrimary.innerText   = 'Drag and drop your student roster here';
    uploadSecondary.innerText = '.xlsx or .csv only, max 5MB';
    parsedStudents = [];
    btnImport.setAttribute('disabled','true');
    btnImport.className = 'bg-slate-300 text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-not-allowed flex items-center justify-center';
}

function mapExcelData(rows) {
    if (!rows || rows.length < 2) return [];
    const headers    = rows[0].map(h => String(h || '').trim().toLowerCase());
    const lrnIdx     = headers.findIndex(h => h.includes('lrn') || h.includes('reference') || h.includes('learner'));
    const nameIdx    = headers.findIndex(h => h.includes('name') || h.includes('student') || h.includes('full'));
    const firstIdx   = headers.findIndex(h => h.includes('first'));
    const lastIdx    = headers.findIndex(h => h.includes('last'));
    const gradeIdx   = headers.findIndex(h => h.includes('grade') || h.includes('level') || h.includes('class'));
    const ageIdx     = headers.findIndex(h => h.includes('age'));
    const sectionIdx = headers.findIndex(h => h.includes('section'));
    const masteryIdx = headers.findIndex(h => h.includes('fsl') || h.includes('mastery') || h.includes('skill'));

    if (lrnIdx === -1) throw new Error("Missing LRN column.");
    if (nameIdx === -1 && (firstIdx === -1 || lastIdx === -1)) throw new Error("Missing Name column.");
    if (ageIdx === -1) throw new Error("Missing Age column.");

    return rows.slice(1).filter(r => r && r.length && (r[lrnIdx] || r[nameIdx] || r[firstIdx])).map((row, i) => {
        const lrn       = String(row[lrnIdx] || '').trim();
        const fullName  = nameIdx !== -1
            ? String(row[nameIdx] || '').trim()
            : `${String(row[lastIdx]||'').trim()}, ${String(row[firstIdx]||'').trim()}`;
        const age       = parseInt(row[ageIdx], 10);
        const rawMastery = masteryIdx !== -1 ? String(row[masteryIdx]||'').trim().toLowerCase() : '';
        const fsl_mastery_level = rawMastery.includes('inter') ? 'Intermediate'
                                 : rawMastery.includes('adv')  ? 'Advanced'
                                 : 'Beginner';

        if (!lrn || lrn.length !== 12 || isNaN(Number(lrn))) throw new Error(`Row ${i+2}: LRN "${lrn}" must be exactly 12 digits.`);
        if (!fullName) throw new Error(`Row ${i+2}: Name is required.`);
        if (isNaN(age) || age < 1) throw new Error(`Row ${i+2}: Valid age is required.`);

        return {
            lrn, full_name: fullName,
            grade_level: gradeIdx !== -1 ? String(row[gradeIdx]||'').trim() || null : null,
            age,
            section: sectionIdx !== -1 ? String(row[sectionIdx]||'').trim() || null : null,
            fsl_mastery_level
        };
    });
}

// ─── Submit Single ────────────────────────────────────────────────────────────
async function submitSingleStudent(event) {
    event.preventDefault();
    hideAlert();
    const btn = document.getElementById('btn-single-submit');
    const nameVal = formSingle.querySelector('input[name="full_name"]').value;
    if (!nameVal.includes(',')) {
        showAlert('Full Name must be "Last Name, First Name" (comma-separated).'); return;
    }
    const orig = btn.innerText;
    btn.innerText = 'Saving…'; btn.disabled = true;
    const fd = new FormData(formSingle);
    const payload = {
        lrn: fd.get('lrn'), full_name: fd.get('full_name'),
        grade_level: fd.get('grade_level'), age: fd.get('age'),
        section: fd.get('section'), fsl_mastery_level: fd.get('fsl_mastery_level'),
        auto_pin: fd.get('auto_pin') ? 1 : 0
    };
    const token = formSingle.querySelector('input[name="_token"]').value;
    try {
        const res = await axios.post("{{ route('students.store') }}", payload,
            { headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
        if (res.data.success) {
            showAlert(res.data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert(res.data.message || 'An error occurred.');
            btn.innerText = orig; btn.disabled = false;
        }
    } catch (err) {
        let msg = 'An error occurred while saving.';
        if (err.response?.data?.errors) msg = Object.values(err.response.data.errors).flat().join('<br>');
        else if (err.response?.data?.message) msg = err.response.data.message;
        else if (err.request) msg = `Network error: ${err.message}`;
        showAlert(msg); btn.innerText = orig; btn.disabled = false;
    }
}

// ─── Bulk Import ─────────────────────────────────────────────────────────────
btnImport.addEventListener('click', async () => {
    hideAlert();
    const orig = btnImport.innerText;
    btnImport.innerText = 'Importing…'; btnImport.disabled = true;
    const payload = { students: parsedStudents, auto_pin: document.getElementById('bulk-auto-pin').checked ? 1 : 0 };
    const token   = formSingle.querySelector('input[name="_token"]').value;
    try {
        const res = await axios.post("{{ route('students.import') }}", payload,
            { headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
        if (res.data.success) {
            showAlert(res.data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert(res.data.message || 'Import error.'); btnImport.innerText = orig; btnImport.disabled = false;
        }
    } catch (err) {
        let msg = 'An error occurred during import.';
        if (err.response?.data?.errors) msg = Object.values(err.response.data.errors).flat().join('<br>');
        else if (err.response?.data?.message) msg = err.response.data.message;
        showAlert(msg); btnImport.innerText = orig; btnImport.disabled = false;
    }
});

// ─── Client-side Filtering ───────────────────────────────────────────────────
const searchInput  = document.getElementById('student-search');
const filterLevel  = document.getElementById('filter-level');
const filterGrade  = document.getElementById('filter-grade');
const filterTabs   = document.querySelectorAll('.filter-tab');
const noResults    = document.getElementById('no-filter-results');
const resultsCount = document.getElementById('results-count');

let activeStatus = 'all';

filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        filterTabs.forEach(t => {
            t.className = 'filter-tab px-5 py-2 text-slate-500 hover:text-[#0d326b] text-[12px] font-medium rounded-full transition-all';
        });
        tab.className = 'filter-tab px-5 py-2 bg-white text-[#0d326b] text-[12px] font-bold rounded-full shadow-sm transition-all';
        activeStatus = tab.dataset.filter;
        applyFilters();
    });
});

searchInput.addEventListener('input', applyFilters);
filterLevel.addEventListener('change', applyFilters);
filterGrade.addEventListener('change', applyFilters);

function applyFilters() {
    const search = searchInput.value.toLowerCase().trim();
    const level  = filterLevel.value;
    const grade  = filterGrade.value;
    const rows   = document.querySelectorAll('.student-row');
    let visible  = 0;

    rows.forEach(row => {
        const matchStatus = activeStatus === 'all' || row.dataset.status === activeStatus;
        const matchSearch = !search || row.dataset.name.includes(search);
        const matchLevel  = !level  || row.dataset.mastery === level;
        const matchGrade  = !grade  || row.dataset.grade === grade;
        const show = matchStatus && matchSearch && matchLevel && matchGrade;
        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    noResults.classList.toggle('hidden', visible > 0 || rows.length === 0);
    resultsCount.textContent = rows.length ? `${visible} student${visible !== 1 ? 's' : ''}` : '';
}

applyFilters();
</script>

@endsection