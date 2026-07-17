@extends('layouts.app')
@section('title', 'Students')
@section('content')

<style>
/* ── Level color tokens — navy scale only ─────────── */
:root {
    --clr-beginner:     #93c5fd;
    --clr-intermediate: #3b82f6;
    --clr-advanced:     #1e4b8f;
    --clr-completed:    #0d326b;
    --clr-navy:         #0d326b;
}

/* ── Stat cards ───────────────────────────────────── */
.stat-card { border-radius: 20px; padding: 20px; position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(13,50,107,.12); }

/* ── Level badge pill — light to dark navy by tier ── */
.lvl-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 9999px; font-size: 10px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
.lvl-badge.beginner     { background: #eff6ff; color: #1e4b8f; }
.lvl-badge.intermediate { background: #dbeafe; color: #0d326b; }
.lvl-badge.advanced     { background: #93c5fd; color: #0d326b; }
.lvl-badge.completed    { background: #0d326b; color: #ffffff; }

/* ── XP Progress bar ──────────────────────────────── */
.xp-bar-wrap { border-radius: 9999px; background: #f1f5f9; overflow: hidden; height: 10px; }
.xp-bar-fill { height: 100%; border-radius: 9999px; transition: width .7s cubic-bezier(.4,0,.2,1); background: linear-gradient(90deg, #93c5fd 0%, #1e4b8f 100%); }
.xp-eligible-chip { display: inline-flex; align-items: center; gap: 3px; background: #dbeafe; color: #0d326b; font-size: 9px; font-weight: 800; padding: 2px 7px; border-radius: 9999px; letter-spacing: .05em; animation: pulse-chip 2s infinite; }
@keyframes pulse-chip { 0%,100%{opacity:1} 50%{opacity:.7} }

/* ── Promote button ───────────────────────────────── */
.promo-btn-eligible  { background: linear-gradient(135deg,#1a6fd4,#0d326b); color:#fff; border: none; }
.promo-btn-eligible:hover  { box-shadow: 0 4px 16px rgba(13,50,107,.35); transform: scale(1.04); }
.promo-btn-locked    { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
.promo-btn-locked:hover    { background: #f1f5f9; border-color: #cbd5e1; }
.promo-btn-completed { background: #eff6ff; color: #0d326b; border: none; }

.promo-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border-radius: 10px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all .2s; white-space: nowrap; }

/* ── Promotion history item ───────────────────────── */
.hist-item { display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; margin-bottom: 8px; }
.hist-dot  { width: 8px; height: 8px; border-radius: 50%; margin-top: 4px; flex-shrink: 0; }
</style>

<div class="space-y-6">

    {{-- ══════════ HEADER ══════════ --}}
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

    {{-- ══════════ STAT CARDS ══════════ --}}
    @php
        $allStudents   = $students->getCollection();
        $beginnerCnt   = $allStudents->where('fsl_mastery_level','Beginner')->count();
        $intermCnt     = $allStudents->where('fsl_mastery_level','Intermediate')->count();
        $advancedCnt   = $allStudents->where('fsl_mastery_level','Advanced')->count();
        $completedCnt  = $allStudents->where('fsl_mastery_level','Completed')->count();
        $avgXp         = $allStudents->count() ? round($allStudents->avg('total_xp')) : 0;
        $progressPct   = min(100, round($avgXp / 1000 * 100));
        $readyToPromote = $allStudents->filter(function($s) {
            $xp = $s->total_xp ?? 0; $lvl = $s->fsl_mastery_level;
            return ($lvl==='Beginner'&&$xp>=300)||($lvl==='Intermediate'&&$xp>=600)||($lvl==='Advanced'&&$xp>=1000);
        })->count();
    @endphp

    {{-- Row 1: Total + Avg XP + Ready to Promote --}}
    <div class="grid grid-cols-3 gap-4">

        {{-- Total Students --}}
        <div class="stat-card text-white" style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%)">
            <div class="absolute -top-7 -right-7 w-28 h-28 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white/5 rounded-full"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="material-symbols-outlined text-white/50 text-[15px]">group</span>
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest">Total Students</p>
                    </div>
                    <p class="text-[46px] font-black text-white leading-none">{{ $totalStudents }}</p>
                    <p class="text-[11px] font-semibold text-[#facc15] mt-2">+{{ $newThisWeek }} this month</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-white text-[28px]">school</span>
                </div>
            </div>
        </div>

        {{-- Average XP --}}
        <div class="stat-card bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-amber-400 text-[16px]">bolt</span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Avg. XP per Student</p>
                </div>
                <span class="text-[10px] font-bold text-[#0d326b] bg-[#e8eef8] px-2 py-0.5 rounded-full">{{ $progressPct }}% to 1K</span>
            </div>
            <p class="text-[40px] font-black text-[#0d326b] leading-none">{{ number_format($avgXp) }}</p>
            <p class="text-[10px] text-slate-400 font-medium mt-0.5 mb-3">experience points</p>
            <div class="xp-bar-wrap">
                <div class="xp-bar-fill" style="width:{{ $progressPct }}%;background:linear-gradient(90deg,#f59e0b,#1a6fd4)"></div>
            </div>
            <div class="flex justify-between mt-1">
                <span class="text-[9px] text-slate-300">0 XP</span>
                <span class="text-[9px] text-slate-400 font-medium">1,000 XP</span>
            </div>
        </div>

        {{-- Ready to Promote --}}
        <div class="stat-card relative" style="background:linear-gradient(135deg,#fef3c7,#fde68a)">
            <div class="absolute -top-7 -right-7 w-28 h-28 bg-[#0d326b]/5 rounded-full"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="material-symbols-outlined text-amber-600/60 text-[15px]">workspace_premium</span>
                        <p class="text-[10px] font-bold text-amber-700/70 uppercase tracking-widest">Ready to Promote</p>
                    </div>
                    <p class="text-[46px] font-black text-[#92400e] leading-none">{{ $readyToPromote }}</p>
                    <p class="text-[11px] font-semibold text-amber-700 mt-2">
                        student{{ $readyToPromote !== 1 ? 's' : '' }} eligible for next level
                    </p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-amber-700 text-[28px]">trending_up</span>
                </div>
            </div>
        </div>
    </div>


    {{-- ══════════ MAIN CONTENT ══════════ --}}
    <div class="flex gap-5 items-start">

        {{-- ── LEFT: Table Panel ── --}}
        <div class="flex-1 min-w-0">

            {{-- Filter Toolbar --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-3.5 mb-4 flex items-center gap-3 flex-wrap">
                <div class="relative shrink-0 order-1 lg:order-none">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[18px]">search</span>
                    <input id="student-search" type="text" placeholder="Search students..." class="bg-[#f1f5f9] text-[13px] font-medium py-2.5 pl-9 pr-4 rounded-full outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400 w-[180px]" />
                </div>
                <div class="bg-[#f1f5f9] p-1 rounded-full flex items-center shadow-inner shrink-0 order-2 lg:order-none">
                    <button data-filter="all" class="filter-tab px-5 py-2 bg-white text-[#0d326b] text-[12px] font-bold rounded-full shadow-sm transition-all">All Students</button>
                    <button data-filter="active" class="filter-tab px-5 py-2 text-slate-500 hover:text-[#0d326b] text-[12px] font-medium rounded-full transition-all">Active Only</button>
                </div>
                <div class="flex-1"></div>
                <div class="relative shrink-0">
                    <select id="filter-level" class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[12px] font-semibold py-2.5 pl-4 pr-9 rounded-full outline-none border border-transparent hover:bg-slate-200 transition-colors cursor-pointer">
                        <option value="">Filter: Level</option>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                        <option value="Completed">Completed</option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[16px] text-slate-500 pointer-events-none">expand_more</span>
                </div>
                <div class="relative shrink-0">
                    <select id="filter-program" class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[12px] font-semibold py-2.5 pl-4 pr-9 rounded-full outline-none border border-transparent hover:bg-slate-200 transition-colors cursor-pointer">
                        <option value="">Filter: Program Type</option>
                        <option value="Regular">Regular</option>
                        <option value="Self-contained">Self-Contained</option>
                        <option value="Transition">Transition</option>
                        <option value="Inclusion">Inclusion</option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[16px] text-slate-500 pointer-events-none">expand_more</span>
                </div>
                <span id="results-count" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider shrink-0"></span>
            </div>

            {{-- Student Table --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse" id="students-table">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="py-4 px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase">Student</th>
                            <th class="py-4 px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase">Level & Status</th>
                            <th class="py-4 px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase">XP Progress</th>
                            <th class="py-4 px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase">Enrolled</th>
                            <th class="py-4 px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50" id="student-tbody">
                        @forelse($students as $student)
                        @php
                            $xp    = $student->total_xp ?? 0;
                            $lvl   = $student->fsl_mastery_level;
                            $m1=300; $m2=600; $m3=1000;
                            $promoteTo=null; $promoteXp=null; $enoughXp=false;
                            if      ($lvl==='Beginner')     { $promoteTo='Intermediate'; $promoteXp=$m1; $enoughXp=$xp>=$m1; }
                            elseif  ($lvl==='Intermediate') { $promoteTo='Advanced';     $promoteXp=$m2; $enoughXp=$xp>=$m2; }
                            elseif  ($lvl==='Advanced')     { $promoteTo='Completed';    $promoteXp=$m3; $enoughXp=$xp>=$m3; }

                            // Bar calculations (relative to next threshold)
                            if      ($lvl==='Beginner')     { $barMax=$m1; $barXp=min($xp,$m1); }
                            elseif  ($lvl==='Intermediate') { $barMax=$m2; $barXp=min($xp,$m2); }
                            elseif  ($lvl==='Advanced')     { $barMax=$m3; $barXp=min($xp,$m3); }
                            else                            { $barMax=$m3; $barXp=$m3; }
                            $barPct = $barMax>0 ? min(100,round($barXp/$barMax*100)) : 100;

                            $levelMeta = match($lvl) {
                                'Intermediate' => ['barColor'=>'#3b82f6','class'=>'intermediate','icon'=>'bolt'],
                                'Advanced'     => ['barColor'=>'#10b981','class'=>'advanced',    'icon'=>'military_tech'],
                                'Completed'    => ['barColor'=>'#a855f7','class'=>'completed',   'icon'=>'verified'],
                                default        => ['barColor'=>'#f59e0b','class'=>'beginner',    'icon'=>'person'],
                            };
                            $statusColor = match($student->status) {
                                'active'   => ['dot'=>'bg-emerald-400','text'=>'text-emerald-600'],
                                'inactive' => ['dot'=>'bg-slate-300',  'text'=>'text-slate-400'],
                                default    => ['dot'=>'bg-red-300',    'text'=>'text-red-400'],
                            };
                            // Promotion history for this student (already eager-loaded)
                            $promoHistory = $student->promotions ?? collect();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors group student-row"
                            data-status="{{ $student->status }}"
                            data-program="{{ $student->program_type ?? '' }}"
                            data-mastery="{{ $lvl }}"
                            data-name="{{ strtolower($student->first_name . ' ' . $student->last_name) }}">

                            {{-- Student Profile --}}
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full shadow-sm shrink-0 overflow-hidden ring-2 ring-slate-100">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name.'+'.$student->last_name) }}&background=0d326b&color=fff&rounded=true&size=60" class="w-9 h-9 rounded-full ring-2 ring-slate-100" />
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-bold text-[#0d326b]">{{ $student->first_name }} {{ $student->last_name }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium mt-0.5">LRN: {{ $student->lrn ?? 'N/A' }}@if($student->grade_level) &middot; {{ $student->grade_level }}@endif</p>
                                        <div class="flex items-center space-x-1 mt-0.5">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $statusColor['dot'] }}"></span>
                                            <span class="text-[10px] font-semibold {{ $statusColor['text'] }} uppercase">{{ $student->status }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Level & Promo status --}}
                            <td class="py-4 px-5">
                                <div class="flex flex-col gap-1.5">
                                    <span class="lvl-badge {{ $levelMeta['class'] }}">
                                        <span class="material-symbols-outlined text-[11px]">{{ $levelMeta['icon'] }}</span>
                                        <span>{{ $lvl }}</span>
                                    </span>
                                    @if($enoughXp && $promoteTo)
                                        <span class="xp-eligible-chip">
                                            <span class="material-symbols-outlined text-[9px]">arrow_upward</span>
                                            Eligible for {{ $promoteTo }}
                                        </span>
                                    @elseif($lvl === 'Completed')
                                        <span style="font-size:9px;color:#7e22ce;font-weight:700">🏆 Completed</span>
                                    @else
                                        <span class="text-[9px] text-slate-400 font-medium">Lv.{{ $student->level ?? 1 }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- XP Progress --}}
                            <td class="py-4 px-5">
                                <div class="w-[200px]">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[12px] font-black text-[#0d326b]">{{ number_format($xp) }} <span class="text-[9px] font-semibold text-slate-400">XP</span></span>
                                        @if($promoteTo)
                                            <span class="text-[9px] font-semibold {{ $enoughXp ? 'text-emerald-600' : 'text-slate-400' }}">
                                                @if($enoughXp)
                                                    ✓ {{ number_format($promoteXp) }} reached
                                                @else
                                                    {{ number_format($promoteXp - $xp) }} to go
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-[9px] text-purple-500 font-bold">MAX ★</span>
                                        @endif
                                    </div>
                                    <div class="xp-bar-wrap">
                                        <div class="xp-bar-fill" style="width:{{ $barPct }}%;"></div>
                                    </div>
                                    <div class="flex justify-between mt-1">
                                        <span class="text-[9px] text-slate-300">0</span>
                                        <span class="text-[9px] font-bold {{ $enoughXp ? 'text-emerald-500' : 'text-slate-300' }}">
                                            @if($promoteTo) {{ number_format($promoteXp) }} XP @else MAX @endif
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Enrolled --}}
                            <td class="py-4 px-5">
                                <p class="text-[12px] font-medium text-[#1e293b]">{{ $student->created_at ? $student->created_at->diffForHumans() : 'N/A' }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $student->created_at ? $student->created_at->format('M d, Y') : '' }}</p>
                            </td>

                            {{-- Actions --}}
                            <td class="py-4 px-5 text-right">
                                @if($promoteTo)
                                <button class="promote-btn promo-btn {{ $enoughXp ? 'promo-btn-eligible' : 'promo-btn-locked' }}"
                                    data-student-id="{{ $student->student_id }}"
                                    data-student-name="{{ $student->first_name }} {{ $student->last_name }}"
                                    data-current-level="{{ $lvl }}"
                                    data-target-level="{{ $promoteTo }}"
                                    data-current-xp="{{ $xp }}"
                                    data-required-xp="{{ $promoteXp }}"
                                    data-enough="{{ $enoughXp ? 'true' : 'false' }}"
                                    data-history="{!! htmlspecialchars(json_encode($promoHistory->map(fn($p)=>['from'=>$p->from_level,'to'=>$p->to_level,'xp'=>$p->xp_at_promotion,'date'=>$p->promoted_at?->format('M d, Y'),'forced'=>$p->was_forced])->toArray()), ENT_QUOTES, 'UTF-8') !!}"
                                    title="{{ $enoughXp ? 'Promote to '.$promoteTo : 'Force promote (XP insufficient)' }}">
                                    <span class="material-symbols-outlined text-[13px]">{{ $enoughXp ? 'arrow_upward' : 'lock' }}</span>
                                    <span>{{ $enoughXp ? 'Promote' : 'Promote' }}</span>
                                </button>
                                @else
                                <span class="promo-btn promo-btn-completed">
                                    <span class="material-symbols-outlined text-[13px]">verified</span>
                                    <span>Completed</span>
                                </span>
                                @endif
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
                                    <button onclick="document.getElementById('open-modal-btn').click()" class="bg-[#0d326b] hover:bg-[#154188] text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-colors">Add Student</button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div id="no-filter-results" class="hidden py-12 text-center">
                    <p class="text-[14px] font-bold text-slate-400 mb-1">No students match your filters</p>
                    <p class="text-[12px] text-slate-400">Try adjusting the search or filter options above.</p>
                </div>

                @if($students->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">{{ $students->links('pagination::tailwind') }}</div>
                @endif
            </div>
        </div>

        {{-- ── RIGHT SIDEBAR ── --}}
        <div class="w-[260px] shrink-0 space-y-4">

            {{-- FSL Mastery Donut --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">FSL Mastery Distribution</p>
                @php
                    $total   = $allStudents->count() ?: 1;
                    $begPct  = round($beginnerCnt  / $total * 100);
                    $intPct  = round($intermCnt    / $total * 100);
                    $advPct  = round($advancedCnt  / $total * 100);
                    $comPct  = round($completedCnt / $total * 100);
                    $circ    = 251.3;
                    $begDash = round($begPct / 100 * $circ, 1);
                    $intDash = round($intPct / 100 * $circ, 1);
                    $advDash = round($advPct / 100 * $circ, 1);
                    $comDash = round($comPct / 100 * $circ, 1);
                    $begOff  = 0; $intOff = -$begDash;
                    $advOff  = -($begDash + $intDash);
                    $comOff  = -($begDash + $intDash + $advDash);
                @endphp
                <div class="flex items-center justify-center mb-4">
                    <div class="relative w-[120px] h-[120px]">
                        <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                            <defs>
                                <linearGradient id="masteryGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#93c5fd" />
                                    <stop offset="33%" stop-color="#3b82f6" />
                                    <stop offset="66%" stop-color="#1e4b8f" />
                                    <stop offset="100%" stop-color="#0d326b" />
                                </linearGradient>
                            </defs>
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                            @if($begDash>0)<circle cx="50" cy="50" r="40" fill="none" stroke="url(#masteryGradient)" stroke-width="14" stroke-dasharray="{{ $begDash }} {{ $circ-$begDash }}" stroke-dashoffset="{{ $begOff }}" stroke-linecap="round"/>@endif
                            @if($intDash>0)<circle cx="50" cy="50" r="40" fill="none" stroke="url(#masteryGradient)" stroke-width="14" stroke-dasharray="{{ $intDash }} {{ $circ-$intDash }}" stroke-dashoffset="{{ $intOff }}" stroke-linecap="round"/>@endif
                            @if($advDash>0)<circle cx="50" cy="50" r="40" fill="none" stroke="url(#masteryGradient)" stroke-width="14" stroke-dasharray="{{ $advDash }} {{ $circ-$advDash }}" stroke-dashoffset="{{ $advOff }}" stroke-linecap="round"/>@endif
                            @if($comDash>0)<circle cx="50" cy="50" r="40" fill="none" stroke="url(#masteryGradient)" stroke-width="14" stroke-dasharray="{{ $comDash }} {{ $circ-$comDash }}" stroke-dashoffset="{{ $comOff }}" stroke-linecap="round"/>@endif
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-[22px] font-black text-[#0d326b]">{{ $allStudents->count() }}</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Students</span>
                        </div>
                    </div>
                </div>
                <div class="space-y-2.5">
                    @foreach([
                        ['color'=>'bg-blue-100',  'dot'=>'#93c5fd','label'=>'Beginner',     'pct'=>$begPct,'cnt'=>$beginnerCnt],
                        ['color'=>'bg-blue-400',  'dot'=>'#3b82f6','label'=>'Intermediate', 'pct'=>$intPct,'cnt'=>$intermCnt],
                        ['color'=>'bg-blue-700',  'dot'=>'#1e4b8f','label'=>'Advanced',     'pct'=>$advPct,'cnt'=>$advancedCnt],
                        ['color'=>'bg-[#0d326b]', 'dot'=>'#0d326b','label'=>'Completed',    'pct'=>$comPct,'cnt'=>$completedCnt],
                    ] as $e)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $e['color'] }} shrink-0"></span>
                            <span class="text-[11px] font-medium text-slate-600">{{ $e['label'] }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-[11px] text-slate-400">{{ $e['cnt'] }}</span>
                            <span class="text-[11px] font-bold text-slate-500 w-8 text-right">{{ $e['pct'] }}%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- XP Milestones --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Promotion Thresholds</p>
                <div class="space-y-3">
                    @php
                        $milestones = [
                            ['from'=>'Beginner','to'=>'Intermediate','xp'=>300,'bg'=>'#ebf4ff','border'=>'#c7d2fe','iconBg'=>'#3b82f6','text'=>'#1e40af','ready'=>$allStudents->where('fsl_mastery_level','Beginner')->filter(fn($s)=>($s->total_xp??0)>=300)->count()],
                            ['from'=>'Intermediate','to'=>'Advanced','xp'=>600,'bg'=>'#dbeafe','border'=>'#93c5fd','iconBg'=>'#1d4ed8','text'=>'#1e40af','ready'=>$allStudents->where('fsl_mastery_level','Intermediate')->filter(fn($s)=>($s->total_xp??0)>=600)->count()],
                            ['from'=>'Advanced','to'=>'Completed','xp'=>1000,'bg'=>'#c7d2fe','border'=>'#6366f1','iconBg'=>'#0d3b82','text'=>'#0d316d','ready'=>$allStudents->where('fsl_mastery_level','Advanced')->filter(fn($s)=>($s->total_xp??0)>=1000)->count()],
                        ];
                    @endphp
                    @foreach($milestones as $ms)
                    <div class="flex items-center justify-between p-3 rounded-xl border" style="background:{{ $ms['bg'] }};border-color:{{ $ms['border'] }}">
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center text-white text-[11px] font-black" style="background:{{ $ms['iconBg'] }}">→</span>
                            <div>
                                <p class="text-[10px] font-bold" style="color:{{ $ms['text'] }}">{{ $ms['from'] }} → {{ $ms['to'] }}</p>
                                <p class="text-[9px]" style="color:{{ $ms['iconBg'] }}">{{ number_format($ms['xp']) }} XP needed</p>
                            </div>
                        </div>
                        <span class="text-[12px] font-black" style="color:{{ $ms['text'] }}">{{ $ms['ready'] }} ready</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Top XP Earners --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Top XP Earners</p>
                @php $topStudents = $allStudents->sortByDesc('total_xp')->take(3); @endphp
                @if($topStudents->count())
                <div class="space-y-3">
                    @foreach($topStudents as $idx => $ts)
                    @php
                        $rc = [
                            ['ring'=>'ring-[#0d326b]', 'bg'=>'bg-[#eff6ff]',  'badge'=>'bg-[#0d326b] text-white'],
                            ['ring'=>'ring-blue-200',  'bg'=>'bg-slate-50',    'badge'=>'bg-blue-100 text-[#1e4b8f]'],
                            ['ring'=>'ring-blue-100',  'bg'=>'bg-blue-50/50',  'badge'=>'bg-blue-50 text-[#1e4b8f]'],
                        ][$idx] ?? ['ring'=>'ring-slate-200','bg'=>'bg-slate-50','badge'=>'bg-slate-200 text-slate-600'];
                    @endphp
                    <div class="flex items-center space-x-3 p-2.5 rounded-xl {{ $rc['bg'] }}">
                        <div class="relative shrink-0">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name.'+'. $student->last_name) }}&background=0d326b&color=fff&rounded=true&size=80" class="w-10 h-10 rounded-full" />
                            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full {{ $rc['badge'] }} text-[8px] font-black flex items-center justify-center">{{ $idx+1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[12px] font-bold text-[#0d326b] truncate">{{ $ts->first_name }} {{ $ts->last_name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ number_format($ts->total_xp) }} XP</p>
                        </div>
                        @if($idx===0)<span class="material-symbols-outlined text-[#facc15] text-[18px] shrink-0">star</span>@endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-[12px] text-slate-400 text-center py-3">No XP data yet.</p>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- ══════════ PROMOTE STUDENT MODAL ══════════ --}}
<div id="promote-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div id="promote-card" class="bg-white rounded-[28px] w-[520px] max-w-full mx-4 shadow-2xl relative transform scale-95 transition-transform duration-300 overflow-hidden flex flex-col" style="max-height:92vh">

        {{-- Colored top bar --}}
        <div id="promote-header-bar" class="h-1.5 w-full bg-gradient-to-r from-emerald-400 to-emerald-600 shrink-0"></div>

        {{-- Scrollable body --}}
        <div class="overflow-y-auto flex-1 px-7 pt-6 pb-3">

            {{-- Header --}}
            <div class="flex items-start space-x-4 mb-5">
                <div id="promote-icon-wrap" class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center shrink-0">
                    <span id="promote-icon" class="material-symbols-outlined text-emerald-600 text-[24px]">arrow_upward</span>
                </div>
                <div>
                    <h3 class="text-[20px] font-black text-[#0d326b]" id="promote-title">Promote Student</h3>
                    <p class="text-[13px] text-slate-400 font-medium mt-0.5" id="promote-subtitle">Move to next mastery level</p>
                </div>
            </div>

            {{-- Student info + level transition --}}
            <div class="bg-slate-50 rounded-2xl p-4 mb-4 flex items-center space-x-3">
                <img id="promote-avatar" src="" class="w-12 h-12 rounded-full ring-2 ring-slate-200" />
                <div class="flex-1 min-w-0">
                    <p id="promote-student-name" class="text-[14px] font-black text-[#0d326b] truncate"></p>
                    <div class="flex items-center space-x-2 mt-1.5">
                        <span id="promote-from-badge" class="lvl-badge"></span>
                        <span class="material-symbols-outlined text-slate-400 text-[16px]">arrow_forward</span>
                        <span id="promote-to-badge" class="lvl-badge"></span>
                    </div>
                </div>
            </div>

            {{-- XP Indicator --}}
            <div class="mb-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500">XP Progress</span>
                    <span id="promote-xp-fraction" class="text-[11px] font-black text-[#0d326b]"></span>
                </div>
                <div class="xp-bar-wrap">
                    <div id="promote-xp-bar" class="xp-bar-fill" style="width:0%;background:linear-gradient(90deg,#10b981,#059669)"></div>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-[9px] text-slate-300">0</span>
                    <span id="promote-xp-target-label" class="text-[9px] text-slate-400 font-medium"></span>
                </div>
            </div>

            {{-- XP status panel (warning or success) --}}
            <div id="promote-xp-warning" class="hidden mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start space-x-3">
                <span class="material-symbols-outlined text-amber-500 text-[22px] shrink-0 mt-0.5">warning</span>
                <div>
                    <p class="text-[13px] font-bold text-amber-800">EXP Insufficient</p>
                    <p id="promote-xp-detail" class="text-[12px] text-amber-600 mt-0.5"></p>
                    <p class="text-[12px] text-amber-700 font-semibold mt-2">Do you still want to promote this student?</p>
                </div>
            </div>
            <div id="promote-xp-ok" class="hidden mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start space-x-3">
                <span class="material-symbols-outlined text-emerald-500 text-[22px] shrink-0 mt-0.5">check_circle</span>
                <div>
                    <p class="text-[13px] font-bold text-emerald-800">Eligible for Promotion!</p>
                    <p id="promote-xp-ok-detail" class="text-[12px] text-emerald-600 mt-0.5"></p>
                </div>
            </div>

            {{-- ── Promotion History ── --}}
            <div id="promote-history-wrap" class="mb-2">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Promotion History</p>
                <div id="promote-history-list">
                    {{-- filled by JS --}}
                </div>
                <div id="promote-history-empty" class="hidden text-center py-4">
                    <span class="material-symbols-outlined text-slate-200 text-[32px] block mb-1">history</span>
                    <p class="text-[11px] text-slate-400">No promotions yet</p>
                </div>
            </div>
        </div>

        {{-- Sticky footer buttons --}}
        <div class="flex items-center justify-end space-x-3 px-7 py-4 border-t border-slate-100 shrink-0 bg-white">
            <button id="promote-cancel-btn" class="px-6 py-2.5 text-slate-500 hover:text-slate-800 font-semibold text-[14px] transition-colors rounded-xl hover:bg-slate-100">Cancel</button>
            <button id="promote-confirm-btn" class="flex items-center space-x-2 px-7 py-3 rounded-xl text-[14px] font-bold transition-all bg-gradient-to-r from-emerald-500 to-emerald-600 text-white hover:shadow-lg hover:shadow-emerald-200">
                <span class="material-symbols-outlined text-[18px]">arrow_upward</span>
                <span id="promote-confirm-label">Promote</span>
            </button>
        </div>

    </div>
</div>

{{-- ══════════ ADD STUDENT MODAL ══════════ --}}
<div id="add-student-modal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-[32px] w-[620px] max-w-full p-8 shadow-2xl relative transform scale-95 transition-transform duration-300">
        <button id="close-modal-btn" class="absolute top-7 right-7 text-slate-400 hover:text-slate-600 outline-none">
            <span class="material-symbols-outlined text-[24px]">close</span>
        </button>
        <div class="mb-6">
            <h2 class="text-[24px] font-bold text-[#0d326b] mb-1">Add New Students</h2>
            <p class="text-[13px] text-slate-400 font-medium">Populate your classroom</p>
        </div>
        <div class="flex space-x-6 border-b border-slate-100 mb-6 text-[12px] font-bold tracking-wider uppercase">
            <button id="tab-single" class="text-[#0d326b] border-b-2 border-[#0d326b] pb-3 outline-none transition-all">Single Student</button>
            <button id="tab-bulk" class="text-slate-400 border-b-2 border-transparent pb-3 hover:text-slate-600 outline-none transition-all">Bulk Add (Excel)</button>
        </div>
        <div id="modal-alert" class="hidden mb-5 p-4 rounded-xl text-sm font-medium flex items-start space-x-2 border">
            <span id="modal-alert-icon" class="material-symbols-outlined text-[20px] mt-0.5 shrink-0"></span>
            <div id="modal-alert-message" class="flex-1"></div>
        </div>
        <form id="form-single" class="block" onsubmit="submitSingleStudent(event)">
            @csrf
            <div class="grid grid-cols-2 gap-x-6 gap-y-5 mb-6">
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Learner Reference Number (LRN)</label>
                    <input type="text" name="lrn" id="input-lrn" required placeholder="12-digit LRN" pattern="\d{12}" maxlength="12" title="LRN must be exactly 12 digits" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                    <p id="lrn-error" class="hidden text-[12px] font-medium text-red-600">LRN already exists.</p>
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Full Name</label>
                    <input type="text" name="full_name" required placeholder="Last Name, First Name" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Program Type</label>
                    <div class="relative">
                        <select name="program_type" id="input-program-type" required class="w-full bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 pl-4 pr-10 rounded-xl outline-none border border-transparent focus:border-slate-300 appearance-none transition-all cursor-pointer">
                            <option value="Regular">Regular</option>
                            <option value="Self-contained">Self-Contained</option>
                            <option value="Transition">Transition</option>
                            <option value="Inclusion">Inclusion</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                    </div>
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Age</label>
                    <input type="number" name="age" required min="1" max="100" placeholder="Enter age" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                </div>
                <div id="field-grade-level" class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Grade Level</label>
                    <div class="relative">
                        <select name="grade_level" id="input-grade-level" class="w-full bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 pl-4 pr-10 rounded-xl outline-none border border-transparent focus:border-slate-300 appearance-none transition-all cursor-pointer">
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
                <div id="field-section" class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Section</label>
                    <input type="text" name="section" id="input-section" placeholder="e.g. SPED-A" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">FSL Mastery Level</label>
                    <div class="relative">
                        <select name="fsl_mastery_level" class="w-full bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 pl-4 pr-10 rounded-xl outline-none border border-transparent focus:border-slate-300 appearance-none transition-all cursor-pointer">
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                    </div>
                </div>
            </div>
            <div class="bg-[#f1f5f9] p-4 rounded-[20px] flex items-center justify-between mb-8 shadow-sm">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-[#0d326b] shadow-sm">
                        <span class="material-symbols-outlined text-[20px]">lock</span>
                    </div>
                    <div>
                        <p class="text-[13px] font-bold text-[#0d326b]">Student PIN</p>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Last 4 digits of LRN</p>
                    </div>
                </div>
                <span id="pin-preview" class="text-[18px] font-bold text-[#0d326b] tracking-widest tabular-nums">----</span>
            </div>
            <div class="flex items-center justify-end space-x-4">
                <button type="button" class="btn-cancel px-6 py-3 text-slate-500 hover:text-slate-800 font-semibold text-[14px] transition-colors">Cancel</button>
                <button type="submit" id="btn-single-submit" class="bg-[#0d326b] hover:bg-[#154188] text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-colors flex items-center justify-center">Save Student</button>
            </div>
        </form>
        <div id="container-bulk" class="hidden">
            <div id="drop-zone" class="border-2 border-dashed border-slate-300 hover:border-[#0d326b] rounded-[24px] p-10 flex flex-col items-center justify-center space-y-4 mb-6 transition-all cursor-pointer relative bg-slate-50/50">
                <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" class="absolute inset-0 opacity-0 cursor-pointer" />
                <div id="upload-icon-container" class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 shadow-sm">
                    <span id="upload-icon" class="material-symbols-outlined text-[28px]">article</span>
                </div>
                <div class="text-center">
                    <p id="upload-primary-text" class="text-[15px] font-bold text-slate-700">Drag and drop your student roster here</p>
                    <p id="upload-secondary-text" class="text-[12px] text-slate-400 mt-1">.xlsx only, max 5MB</p>
                </div>
                <button type="button" id="browse-btn" class="border border-[#0d326b] text-[#0d326b] hover:bg-[#0d326b]/5 font-bold text-[13px] px-6 py-2.5 rounded-xl transition-colors pointer-events-none">Browse Files</button>
            </div>
            <div class="bg-[#f1f5f9] p-4 rounded-[20px] flex items-center justify-between mb-8 shadow-sm">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-[#0d326b] shadow-sm"><span class="material-symbols-outlined text-[20px]">lock</span></div>
                    <div>
                        <p class="text-[13px] font-bold text-[#0d326b]">Auto-generate Student PINs</p>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Apply to all imported students</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="bulk-auto-pin" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0d326b]"></div>
                </label>
            </div>
            <div class="flex items-center justify-end space-x-4">
                <button type="button" class="btn-cancel px-6 py-3 text-slate-500 hover:text-slate-800 font-semibold text-[14px] transition-colors">Cancel</button>
                <button type="button" id="btn-import-submit" disabled class="bg-slate-300 text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-not-allowed flex items-center justify-center">Confirm Import</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
// ─── Add Student Modal ────────────────────────────────────────────────────────
const openModalBtn=document.getElementById('open-modal-btn'),closeModalBtn=document.getElementById('close-modal-btn'),cancelBtns=document.querySelectorAll('.btn-cancel'),modal=document.getElementById('add-student-modal'),modalCard=modal.querySelector('.bg-white'),tabSingle=document.getElementById('tab-single'),tabBulk=document.getElementById('tab-bulk'),formSingle=document.getElementById('form-single'),containerBulk=document.getElementById('container-bulk'),modalAlert=document.getElementById('modal-alert'),modalAlertIcon=document.getElementById('modal-alert-icon'),modalAlertMsg=document.getElementById('modal-alert-message'),dropZone=document.getElementById('drop-zone'),excelInput=document.getElementById('excel-file'),btnImport=document.getElementById('btn-import-submit'),uploadIcon=document.getElementById('upload-icon'),uploadIconWrap=document.getElementById('upload-icon-container'),uploadPrimary=document.getElementById('upload-primary-text'),uploadSecondary=document.getElementById('upload-secondary-text');
let parsedStudents=[];
openModalBtn.addEventListener('click',()=>{modal.classList.remove('hidden');requestAnimationFrame(()=>{modal.classList.remove('opacity-0');modalCard.classList.remove('scale-95');});});
function closeModal(){modal.classList.add('opacity-0');modalCard.classList.add('scale-95');setTimeout(()=>{modal.classList.add('hidden');resetModal();},300);}
closeModalBtn.addEventListener('click',closeModal);cancelBtns.forEach(b=>b.addEventListener('click',closeModal));modal.addEventListener('click',e=>{if(e.target===modal)closeModal();});
const AT='text-[#0d326b] border-b-2 border-[#0d326b] pb-3 outline-none transition-all',IT='text-slate-400 border-b-2 border-transparent pb-3 hover:text-slate-600 outline-none transition-all';
tabSingle.addEventListener('click',()=>{tabSingle.className=AT;tabBulk.className=IT;formSingle.classList.remove('hidden');containerBulk.classList.add('hidden');});
tabBulk.addEventListener('click',()=>{tabBulk.className=AT;tabSingle.className=IT;containerBulk.classList.remove('hidden');formSingle.classList.add('hidden');});
function showAlert(msg,type='error'){modalAlert.classList.remove('hidden','bg-red-50','border-red-200','text-red-800','bg-emerald-50','border-emerald-200','text-emerald-800');modalAlertIcon.innerText=type==='error'?'error':'check_circle';modalAlert.classList.add(type==='error'?'bg-red-50':'bg-emerald-50',type==='error'?'border-red-200':'border-emerald-200',type==='error'?'text-red-800':'text-emerald-800');modalAlertMsg.innerHTML=msg;}
function hideAlert(){modalAlert.classList.add('hidden');}
function resetModal(){formSingle.reset();parsedStudents=[];resetUploadArea();hideAlert();hideLrnError();updatePinPreview();toggleGradeSectionFields();tabSingle.click();}
const inputProgramType=document.getElementById('input-program-type'),fieldGradeLevel=document.getElementById('field-grade-level'),fieldSection=document.getElementById('field-section'),inputGradeLevel=document.getElementById('input-grade-level'),inputSection=document.getElementById('input-section');
function toggleGradeSectionFields(){const show=['Regular','Inclusion'].includes(inputProgramType.value);fieldGradeLevel.classList.toggle('hidden',!show);fieldSection.classList.toggle('hidden',!show);inputGradeLevel.disabled=!show;inputSection.disabled=!show;if(!show){inputGradeLevel.value='';inputSection.value='';}}
inputProgramType.addEventListener('change',toggleGradeSectionFields);toggleGradeSectionFields();
const inputLrn=document.getElementById('input-lrn'),lrnError=document.getElementById('lrn-error'),pinPreview=document.getElementById('pin-preview');
let lrnExists=false,lrnCheckTimer=null;
function hideLrnError(){lrnError.classList.add('hidden');inputLrn.classList.remove('border-red-400','focus:border-red-400');lrnExists=false;}
function showLrnError(){lrnError.classList.remove('hidden');inputLrn.classList.add('border-red-400','focus:border-red-400');lrnExists=true;}
function updatePinPreview(){const lrn=inputLrn.value.replace(/\D/g,'');pinPreview.textContent=lrn.length>=4?lrn.slice(-4):'----';}
async function checkLrnUnique(){const lrn=inputLrn.value.replace(/\D/g,'');hideLrnError();if(lrn.length!==12)return;try{const res=await axios.get("{{ route('students.check-lrn') }}",{params:{lrn}});if(res.data.exists)showLrnError();}catch(_){}}
inputLrn.addEventListener('input',()=>{updatePinPreview();clearTimeout(lrnCheckTimer);hideLrnError();if(inputLrn.value.replace(/\D/g,'').length===12){lrnCheckTimer=setTimeout(checkLrnUnique,400);}});
inputLrn.addEventListener('blur',checkLrnUnique);updatePinPreview();
['dragenter','dragover'].forEach(ev=>dropZone.addEventListener(ev,e=>{e.preventDefault();dropZone.classList.add('border-[#0d326b]','bg-[#0d326b]/5');}));
['dragleave','drop'].forEach(ev=>dropZone.addEventListener(ev,e=>{e.preventDefault();dropZone.classList.remove('border-[#0d326b]','bg-[#0d326b]/5');}));
dropZone.addEventListener('drop',e=>{const f=e.dataTransfer.files[0];if(f){excelInput.files=e.dataTransfer.files;handleExcelFile(f);}});
excelInput.addEventListener('change',e=>{if(e.target.files[0])handleExcelFile(e.target.files[0]);});
function handleExcelFile(file){hideAlert();const ext=file.name.split('.').pop().toLowerCase();if(!['xlsx','xls','csv'].includes(ext)){showAlert('Invalid file format. Please upload .xlsx, .xls, or .csv.');resetUploadArea();return;}const reader=new FileReader();reader.onload=e=>{try{const wb=XLSX.read(new Uint8Array(e.target.result),{type:'array'}),ws=wb.Sheets[wb.SheetNames[0]],raw=XLSX.utils.sheet_to_json(ws,{header:1});parsedStudents=mapExcelData(raw);if(!parsedStudents.length){showAlert('File is empty or has no student rows.');resetUploadArea();return;}showUploadedFile(file.name,parsedStudents.length);}catch(err){showAlert(err.message||'Failed to parse file.');resetUploadArea();}};reader.readAsArrayBuffer(file);}
function showUploadedFile(name,count){uploadIcon.innerText='check';uploadIconWrap.className='w-14 h-14 bg-emerald-500 rounded-full flex items-center justify-center text-white shadow-sm';uploadPrimary.innerText=name;uploadSecondary.innerText=`${count} students detected. Ready to import.`;btnImport.removeAttribute('disabled');btnImport.className='bg-[#0d326b] hover:bg-[#154188] text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-pointer flex items-center justify-center';}
function resetUploadArea(){excelInput.value='';uploadIcon.innerText='article';uploadIconWrap.className='w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 shadow-sm';uploadPrimary.innerText='Drag and drop your student roster here';uploadSecondary.innerText='.xlsx or .csv only, max 5MB';parsedStudents=[];btnImport.setAttribute('disabled','true');btnImport.className='bg-slate-300 text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-not-allowed flex items-center justify-center';}
function mapExcelData(rows){if(!rows||rows.length<2)return[];const h=rows[0].map(x=>String(x||'').trim().toLowerCase()),lrnIdx=h.findIndex(x=>x.includes('lrn')||x.includes('reference')||x.includes('learner')),nameIdx=h.findIndex(x=>x.includes('name')||x.includes('student')||x.includes('full')),firstIdx=h.findIndex(x=>x.includes('first')),lastIdx=h.findIndex(x=>x.includes('last')),gradeIdx=h.findIndex(x=>x.includes('grade')||x.includes('level')||x.includes('class')),ageIdx=h.findIndex(x=>x.includes('age')),sectionIdx=h.findIndex(x=>x.includes('section')),masteryIdx=h.findIndex(x=>x.includes('fsl')||x.includes('mastery')||x.includes('skill'));if(lrnIdx===-1)throw new Error("Missing LRN column.");if(nameIdx===-1&&(firstIdx===-1||lastIdx===-1))throw new Error("Missing Name column.");if(ageIdx===-1)throw new Error("Missing Age column.");return rows.slice(1).filter(r=>r&&r.length&&(r[lrnIdx]||r[nameIdx]||r[firstIdx])).map((row,i)=>{const lrn=String(row[lrnIdx]||'').trim(),fullName=nameIdx!==-1?String(row[nameIdx]||'').trim():`${String(row[lastIdx]||'').trim()}, ${String(row[firstIdx]||'').trim()}`,age=parseInt(row[ageIdx],10),rawM=masteryIdx!==-1?String(row[masteryIdx]||'').trim().toLowerCase():'',fsl_mastery_level=rawM.includes('inter')?'Intermediate':rawM.includes('adv')?'Advanced':'Beginner';if(!lrn||lrn.length!==12||isNaN(Number(lrn)))throw new Error(`Row ${i+2}: LRN "${lrn}" must be exactly 12 digits.`);if(!fullName)throw new Error(`Row ${i+2}: Name is required.`);if(isNaN(age)||age<1)throw new Error(`Row ${i+2}: Valid age is required.`);return{lrn,full_name:fullName,grade_level:gradeIdx!==-1?String(row[gradeIdx]||'').trim()||null:null,age,section:sectionIdx!==-1?String(row[sectionIdx]||'').trim()||null:null,fsl_mastery_level};});}
async function submitSingleStudent(event){event.preventDefault();hideAlert();const btn=document.getElementById('btn-single-submit'),nameVal=formSingle.querySelector('input[name="full_name"]').value;if(!nameVal.includes(',')){showAlert('Full Name must be "Last Name, First Name" (comma-separated).');return;}if(inputLrn.value.replace(/\D/g,'').length!==12){showAlert('LRN must be exactly 12 digits.');return;}await checkLrnUnique();if(lrnExists)return;const orig=btn.innerText;btn.innerText='Saving...';btn.disabled=true;const fd=new FormData(formSingle),showGS=['Regular','Inclusion'].includes(fd.get('program_type')),payload={lrn:fd.get('lrn'),full_name:fd.get('full_name'),program_type:fd.get('program_type'),age:fd.get('age'),fsl_mastery_level:fd.get('fsl_mastery_level')};if(showGS){payload.grade_level=fd.get('grade_level');payload.section=fd.get('section');}const token=formSingle.querySelector('input[name="_token"]').value;try{const res=await axios.post("{{ route('students.store') }}",payload,{headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}});if(res.data.success){showAlert(res.data.message,'success');setTimeout(()=>window.location.reload(),1500);}else{showAlert(res.data.message||'An error occurred.');btn.innerText=orig;btn.disabled=false;}}catch(err){let msg='An error occurred while saving.';if(err.response?.data?.errors){const errors=err.response.data.errors;if(errors.lrn){showLrnError();msg=errors.lrn[0];}else{msg=Object.values(errors).flat().join('<br>');}}else if(err.response?.data?.message)msg=err.response.data.message;else if(err.request)msg=`Network error: ${err.message}`;showAlert(msg);btn.innerText=orig;btn.disabled=false;}}
btnImport.addEventListener('click',async()=>{hideAlert();const orig=btnImport.innerText;btnImport.innerText='Importing...';btnImport.disabled=true;const payload={students:parsedStudents,auto_pin:document.getElementById('bulk-auto-pin').checked?1:0},token=formSingle.querySelector('input[name="_token"]').value;try{const res=await axios.post("{{ route('students.import') }}",payload,{headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}});if(res.data.success){showAlert(res.data.message,'success');setTimeout(()=>window.location.reload(),1500);}else{showAlert(res.data.message||'Import error.');btnImport.innerText=orig;btnImport.disabled=false;}}catch(err){let msg='An error occurred during import.';if(err.response?.data?.errors)msg=Object.values(err.response.data.errors).flat().join('<br>');else if(err.response?.data?.message)msg=err.response.data.message;showAlert(msg);btnImport.innerText=orig;btnImport.disabled=false;}});

// ─── Client-side Filtering ────────────────────────────────────────────────────
const searchInput=document.getElementById('student-search'),filterLevel=document.getElementById('filter-level'),filterProgram=document.getElementById('filter-program'),filterTabs=document.querySelectorAll('.filter-tab'),noResults=document.getElementById('no-filter-results'),resultsCount=document.getElementById('results-count');
let activeStatus='all';
filterTabs.forEach(tab=>{tab.addEventListener('click',()=>{filterTabs.forEach(t=>{t.className='filter-tab px-5 py-2 text-slate-500 hover:text-[#0d326b] text-[12px] font-medium rounded-full transition-all';});tab.className='filter-tab px-5 py-2 bg-white text-[#0d326b] text-[12px] font-bold rounded-full shadow-sm transition-all';activeStatus=tab.dataset.filter;applyFilters();});});
searchInput.addEventListener('input',applyFilters);filterLevel.addEventListener('change',applyFilters);filterProgram.addEventListener('change',applyFilters);
function applyFilters(){const search=searchInput.value.toLowerCase().trim(),level=filterLevel.value,program=filterProgram.value,rows=document.querySelectorAll('.student-row');let visible=0;rows.forEach(row=>{const show=(activeStatus==='all'||row.dataset.status===activeStatus)&&(!search||row.dataset.name.includes(search))&&(!level||row.dataset.mastery===level)&&(!program||row.dataset.program===program);row.classList.toggle('hidden',!show);if(show)visible++;});noResults.classList.toggle('hidden',visible>0||rows.length===0);resultsCount.textContent=rows.length?`${visible} student${visible!==1?'s':''}`:'';} applyFilters();

// ─── Promote Modal ────────────────────────────────────────────────────────────
const promoteModal=document.getElementById('promote-modal'),promoteCard=document.getElementById('promote-card'),promoteTitle=document.getElementById('promote-title'),promoteSubtitle=document.getElementById('promote-subtitle'),promoteAvatar=document.getElementById('promote-avatar'),promoteStudentName=document.getElementById('promote-student-name'),promoteFromBadge=document.getElementById('promote-from-badge'),promoteToBadge=document.getElementById('promote-to-badge'),promoteXpWarning=document.getElementById('promote-xp-warning'),promoteXpDetail=document.getElementById('promote-xp-detail'),promoteXpOk=document.getElementById('promote-xp-ok'),promoteXpOkDetail=document.getElementById('promote-xp-ok-detail'),promoteHeaderBar=document.getElementById('promote-header-bar'),promoteIconWrap=document.getElementById('promote-icon-wrap'),promoteIcon=document.getElementById('promote-icon'),promoteCancelBtn=document.getElementById('promote-cancel-btn'),promoteConfirmBtn=document.getElementById('promote-confirm-btn'),promoteConfirmLabel=document.getElementById('promote-confirm-label'),promoteXpBar=document.getElementById('promote-xp-bar'),promoteXpFraction=document.getElementById('promote-xp-fraction'),promoteXpTargetLabel=document.getElementById('promote-xp-target-label'),promoteHistoryList=document.getElementById('promote-history-list'),promoteHistoryEmpty=document.getElementById('promote-history-empty');
let currentPromoteData=null;

const lvlMeta={
    'Beginner':     {cssClass:'beginner',     barColor:'#93c5fd'},
    'Intermediate': {cssClass:'intermediate',  barColor:'#3b82f6'},
    'Advanced':     {cssClass:'advanced',      barColor:'#1e4b8f'},
    'Completed':    {cssClass:'completed',     barColor:'#0d326b'},
};

function renderHistory(history){
    promoteHistoryList.innerHTML='';
    if(!history||!history.length){promoteHistoryEmpty.classList.remove('hidden');return;}
    promoteHistoryEmpty.classList.add('hidden');
    history.forEach(h=>{
        const fromMeta=lvlMeta[h.from]||lvlMeta['Beginner'];
        const toMeta=lvlMeta[h.to]||lvlMeta['Beginner'];
        const forcedBadge=h.forced?`<span style="background:#fef3c7;color:#92400e;font-size:9px;font-weight:700;padding:1px 6px;border-radius:9999px;margin-left:6px">forced</span>`:'';
        promoteHistoryList.innerHTML+=`
        <div class="hist-item">
            <div class="hist-dot" style="background:${toMeta.barColor}"></div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center flex-wrap gap-1">
                    <span class="lvl-badge ${fromMeta.cssClass}" style="font-size:9px;padding:2px 7px">${h.from}</span>
                    <span style="font-size:11px;color:#94a3b8">→</span>
                    <span class="lvl-badge ${toMeta.cssClass}" style="font-size:9px;padding:2px 7px">${h.to}</span>
                    ${forcedBadge}
                </div>
                <div class="flex items-center justify-between mt-1">
                    <span style="font-size:10px;color:#94a3b8">${h.date}</span>
                    <span style="font-size:10px;font-weight:700;color:#0d326b">${Number(h.xp).toLocaleString()} XP</span>
                </div>
            </div>
        </div>`;
    });
}

function openPromoteModal(btn){
    const sid=btn.dataset.studentId,sname=btn.dataset.studentName,cur=btn.dataset.currentLevel,tgt=btn.dataset.targetLevel,cxp=parseInt(btn.dataset.currentXp,10),rxp=parseInt(btn.dataset.requiredXp,10),ok=btn.dataset.enough==='true';
    let history=[];try{history=JSON.parse(btn.dataset.history||'[]');}catch(e){}
    currentPromoteData={studentId:sid,targetLevel:tgt,force:!ok};
    promoteAvatar.src=`https://ui-avatars.com/api/?name=${encodeURIComponent(sname.replace(/ /g,'+'))}&background=0d326b&color=fff&rounded=true&size=80`;
    promoteStudentName.textContent=sname;
    // Badges
    const fc=lvlMeta[cur]||lvlMeta['Beginner'],tc=lvlMeta[tgt]||lvlMeta['Beginner'];
    promoteFromBadge.className=`lvl-badge ${fc.cssClass}`;promoteFromBadge.textContent=cur;
    promoteToBadge.className=`lvl-badge ${tc.cssClass}`;promoteToBadge.textContent=tgt;
    // XP bar
    const pct=rxp>0?Math.min(100,Math.round(cxp/rxp*100)):100;
    promoteXpBar.style.width=pct+'%';
    promoteXpBar.style.background=ok?`linear-gradient(90deg,${tc.barColor}88,${tc.barColor})`:`linear-gradient(90deg,#f59e0b88,#f59e0b)`;
    promoteXpFraction.textContent=`${cxp.toLocaleString()} / ${rxp.toLocaleString()} XP`;
    promoteXpTargetLabel.textContent=`${rxp.toLocaleString()} XP required`;
    // Status panels
    promoteXpWarning.classList.add('hidden');promoteXpOk.classList.add('hidden');
    if(ok){
        promoteXpOk.classList.remove('hidden');
        document.getElementById('promote-xp-ok-detail').textContent=`This student is eligible for promotion to ${tgt}. They have met the ${rxp.toLocaleString()} XP requirement.`;
        promoteHeaderBar.className='h-1.5 w-full bg-gradient-to-r from-emerald-400 to-emerald-600';
        promoteIconWrap.className='w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center shrink-0';
        promoteIcon.className='material-symbols-outlined text-emerald-600 text-[24px]';promoteIcon.textContent='arrow_upward';
        promoteTitle.textContent=`Promote to ${tgt}`;promoteSubtitle.textContent=`Move ${sname} to the next mastery level`;
        promoteConfirmBtn.className='flex items-center space-x-2 px-7 py-3 rounded-xl text-[14px] font-bold transition-all bg-gradient-to-r from-emerald-500 to-emerald-600 text-white hover:shadow-lg hover:shadow-emerald-200';
        promoteConfirmLabel.textContent='Promote';
    } else {
        promoteXpWarning.classList.remove('hidden');
        promoteXpDetail.textContent=`Student has ${cxp.toLocaleString()} XP but needs ${rxp.toLocaleString()} XP to reach ${tgt}.`;
        promoteHeaderBar.className='h-1.5 w-full bg-gradient-to-r from-amber-400 to-amber-500';
        promoteIconWrap.className='w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center shrink-0';
        promoteIcon.className='material-symbols-outlined text-amber-600 text-[24px]';promoteIcon.textContent='warning';
        promoteTitle.textContent='EXP Insufficient';promoteSubtitle.textContent='Do you still want to force promote?';
        promoteConfirmBtn.className='flex items-center space-x-2 px-7 py-3 rounded-xl text-[14px] font-bold transition-all bg-gradient-to-r from-amber-500 to-amber-600 text-white hover:shadow-lg hover:shadow-amber-200';
        promoteConfirmLabel.textContent='Promote Anyway';
    }
    // Render history
    renderHistory(history);
    promoteModal.classList.remove('hidden');
    requestAnimationFrame(()=>{promoteModal.classList.remove('opacity-0');promoteCard.classList.remove('scale-95');});
}
function closePromoteModal(){promoteModal.classList.add('opacity-0');promoteCard.classList.add('scale-95');setTimeout(()=>promoteModal.classList.add('hidden'),300);currentPromoteData=null;}
document.querySelectorAll('.promote-btn').forEach(btn=>{btn.addEventListener('click',()=>openPromoteModal(btn));});
promoteCancelBtn.addEventListener('click',closePromoteModal);promoteModal.addEventListener('click',e=>{if(e.target===promoteModal)closePromoteModal();});
promoteConfirmBtn.addEventListener('click',async()=>{if(!currentPromoteData)return;const origInner=promoteConfirmBtn.innerHTML;promoteConfirmBtn.innerHTML='<span class="material-symbols-outlined text-[18px]">progress_activity</span><span>Promoting...</span>';promoteConfirmBtn.disabled=true;const token=formSingle.querySelector('input[name="_token"]').value,url=`/students/${currentPromoteData.studentId}/promote`;try{const res=await axios.post(url,{target_level:currentPromoteData.targetLevel,force:currentPromoteData.force?1:0},{headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}});if(res.data.success){promoteCard.innerHTML=`<div class="p-10 flex flex-col items-center text-center"><div class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center mb-5"><span class="material-symbols-outlined text-emerald-500 text-[42px]">check_circle</span></div><p class="text-[20px] font-black text-[#0d326b] mb-2">Promoted!</p><p class="text-[13px] text-slate-400">${res.data.message}</p></div>`;setTimeout(()=>window.location.reload(),1400);}else{promoteConfirmBtn.innerHTML=origInner;promoteConfirmBtn.disabled=false;}}catch(err){promoteConfirmBtn.innerHTML=origInner;promoteConfirmBtn.disabled=false;alert(err.response?.data?.message||'Something went wrong. Please try again.');}});
</script>

@endsection