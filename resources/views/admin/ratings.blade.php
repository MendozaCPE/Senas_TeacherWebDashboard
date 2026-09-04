@extends('layouts.admin')
@section('title', 'Ratings')
@section('content')

@php
/* ── Inline bezier trend helper (same technique as admin.dashboard) ──── */
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

$W = 600; $H = 200; $pL = 28; $pR = 28; $pT = 16; $pB = 24;
$plotW = $W - $pL - $pR; $plotH = $H - $pT - $pB; $bot = $pT + $plotH;
$n = count($ratingTrend);
$peak = max(1, collect($ratingTrend)->max(fn($d) => max($d['teacher'], $d['student'])));

$tPts = []; $sPts = [];
foreach ($ratingTrend as $i => $d) {
    $x = $n > 1 ? $pL + ($i / ($n - 1)) * $plotW : $pL + $plotW / 2;
    $tPts[] = ['x' => round($x,2), 'y' => round($pT + $plotH - ($d['teacher'] / $peak) * $plotH, 2)];
    $sPts[] = ['x' => round($x,2), 'y' => round($pT + $plotH - ($d['student'] / $peak) * $plotH, 2)];
}
$tLine = $bezier($tPts, $bot); $tArea = $bezier($tPts, $bot, true);
$sLine = $bezier($sPts, $bot); $sArea = $bezier($sPts, $bot, true);

$starPill = function (int $rating) {
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $rating
            ? '<span class="material-symbols-outlined text-[15px] text-[#facc15]">star</span>'
            : '<span class="material-symbols-outlined icon-outline text-[15px] text-slate-200">star</span>';
    }
    return $out;
};
@endphp

<div class="flex flex-col gap-5 w-full pt-4" id="ratingsRoot" data-approval-url-base="{{ url('/admin/ratings') }}">

    {{-- ── HEADER BANNER ────────────────────────────────────────────────── --}}
    <div class="rounded-[28px] relative overflow-hidden flex items-center"
         style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%);min-height:120px">
        <div class="absolute top-0 right-44 w-44 h-44 rounded-full opacity-10 bg-white"></div>
        <div class="absolute -bottom-8 left-1/3 w-32 h-32 rounded-full opacity-10 bg-white"></div>
        <div class="relative z-10 px-10 py-7 flex-1">
            <h2 class="text-[24px] font-black text-white leading-tight mb-1">Ratings & Reviews</h2>
            <p class="text-[13px] text-white/70 font-medium">Approve teacher and student ratings before they appear on the public landing page.</p>
        </div>
        <div class="relative z-10 flex-shrink-0 pr-10 hidden lg:flex items-center gap-8">
            @foreach([['label'=>'Pending','val'=>$pendingTotal],['label'=>'Teacher Avg','val'=>($totalTeacherRatings>0?number_format($avgTeacherRating,1).'★':'—')],['label'=>'Student Avg','val'=>($totalStudentRatings>0?number_format($avgStudentRating,1).'★':'—')]] as $bi)
            @if(!$loop->first)<div class="w-px h-10 bg-white/20"></div>@endif
            <div class="text-center">
                <p class="text-[26px] font-black text-white leading-none">{{ $bi['val'] }}</p>
                <p class="text-[10px] font-semibold text-white/60 uppercase tracking-wider mt-0.5">{{ $bi['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── KPI CARDS ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- Card 1: Pending Approval — navy gradient --}}
        <div class="text-white" style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%);">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Pending Approval</span>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px] text-white">hourglass_top</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-white tracking-tight">{{ $pendingTotal }}</p>
            <p class="text-[12px] text-white/70 font-medium">{{ $pendingTeacherRatings }} teacher · {{ $pendingStudentRatings }} student</p>
        </div>

        {{-- Card 2: Approved (Live) — white --}}
        <div style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #d1fae5;background:#f0fdf4;">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Approved (Live)</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">verified</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-emerald-700 tracking-tight">{{ $approvedTeacherRatings + $approvedStudentRatings }}</p>
            <p class="text-[12px] text-emerald-600 font-medium">{{ $approvedTeacherRatings }} teacher · {{ $approvedStudentRatings }} student</p>
        </div>

        {{-- Card 3: Teacher Ratings — white --}}
        <div style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;border:1px solid #f1f5f9;background:#fff;">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Teacher Ratings</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">school</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ $totalTeacherRatings }}</p>
            <p class="text-[12px] text-[#1a6fd4] font-medium">{{ $totalTeacherRatings > 0 ? number_format($avgTeacherRating, 2).' avg / 5' : 'No ratings yet' }}</p>
        </div>

        {{-- Card 4: Student Ratings — amber gradient --}}
        <div class="text-amber-950" style="border-radius:24px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border:1px solid rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22);">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Student Ratings</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 text-amber-950 flex items-center justify-center backdrop-blur-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">group</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-amber-950 tracking-tight">{{ $totalStudentRatings }}</p>
            <p class="text-[12px] text-amber-950/80 font-bold">{{ $totalStudentRatings > 0 ? number_format($avgStudentRating, 2).' avg / 5' : 'No ratings yet' }}</p>
        </div>

    </div>

    {{-- ── ANALYTICS: trend + distributions ────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

        {{-- Submissions trend --}}
        <div class="lg:col-span-2 bg-white rounded-[22px] shadow-sm border border-slate-100 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Trend</p>
                    <h3 class="text-[16px] font-bold text-[#0d326b]">Rating Submissions</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">New teacher vs. student ratings — last 14 days</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-semibold flex-shrink-0">
                    <span class="flex items-center gap-1.5"><span class="w-8 h-1.5 rounded-full bg-[#0d326b] inline-block"></span>Teacher</span>
                    <span class="flex items-center gap-1.5"><span class="w-8 h-1.5 rounded-full inline-block" style="background:repeating-linear-gradient(90deg,#1a6fd4 0,#1a6fd4 4px,transparent 4px,transparent 7px)"></span>Student</span>
                </div>
            </div>

            <div class="bg-[#fafcff] rounded-2xl w-full relative" style="padding-bottom:34%">
                <svg viewBox="0 0 {{ $W }} {{ $H }}" class="absolute inset-0 w-full h-full" preserveAspectRatio="none" overflow="visible">
                    <defs>
                        <linearGradient id="rgTFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#0d326b" stop-opacity=".20"/>
                            <stop offset="100%" stop-color="#0d326b" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="rgSFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#1a6fd4" stop-opacity=".14"/>
                            <stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    @foreach([0,25,50,75,100] as $gv)
                        @php $gy = round($pT + $plotH - ($gv/100)*$plotH, 1); @endphp
                        <line x1="{{ $pL }}" y1="{{ $gy }}" x2="{{ $pL+$plotW }}" y2="{{ $gy }}" stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                    @endforeach
                    <path d="{{ $tArea }}" fill="url(#rgTFill)"/>
                    <path d="{{ $sArea }}" fill="url(#rgSFill)"/>
                    <path d="{{ $tLine }}" fill="none" stroke="#0d326b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="{{ $sLine }}" fill="none" stroke="#1a6fd4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="5,3"/>
                    @foreach($ratingTrend as $i => $d)
                        @if($i % 2 === 0 || $i === count($ratingTrend)-1)
                            <text x="{{ $tPts[$i]['x'] }}" y="{{ $H - 6 }}" font-size="10" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $d['label'] }}</text>
                        @endif
                    @endforeach
                </svg>
            </div>
        </div>

        {{-- Star distributions --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-5">
            <h4 class="text-[13px] font-black text-[#0d326b] mb-4">Star Distribution</h4>

            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Teacher Ratings</p>
            @foreach($teacherDist as $star => $d)
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-[11px] font-semibold text-slate-500 w-6">{{ $star }}★</span>
                <div class="flex-1 bg-slate-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full bg-[#0d326b]" style="width:{{ $d['pct'] }}%"></div>
                </div>
                <span class="text-[11px] font-bold text-slate-400 w-6 text-right">{{ $d['count'] }}</span>
            </div>
            @endforeach

            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-4">Student Ratings</p>
            @foreach($studentDist as $star => $d)
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-[11px] font-semibold text-slate-500 w-6">{{ $star }}★</span>
                <div class="flex-1 bg-slate-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full bg-[#1a6fd4]" style="width:{{ $d['pct'] }}%"></div>
                </div>
                <span class="text-[11px] font-bold text-slate-400 w-6 text-right">{{ $d['count'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── STATUS FILTER ────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2">
        @foreach(['all'=>'All','pending'=>'Pending','approved'=>'Approved'] as $val => $label)
        <a href="{{ route('admin.ratings', array_merge(request()->except(['status','teacher_page','student_page']), ['status'=>$val])) }}"
           class="px-4 py-2 rounded-full text-[12px] font-bold transition-colors
               {{ $statusFilter === $val ? 'bg-[#0d326b] text-white' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-300' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- ── RATINGS: Teacher & Student side-by-side ─────────────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 items-start">

        {{-- Teacher Ratings --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-[15px] font-black text-[#0d326b]">Teacher Ratings</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $totalTeacherRatings }} total · {{ $pendingTeacherRatings }} pending</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('admin.ratings') }}" class="flex items-center gap-2">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <input type="text" name="teacher_search" value="{{ $teacherSearch }}" placeholder="Search teacher name…"
                           class="flex-1 text-[12px] px-3 py-2 rounded-full border border-slate-200 focus:outline-none focus:border-[#0d326b]">
                    <button type="submit" class="w-8 h-8 flex-shrink-0 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[16px] text-slate-500">search</span>
                    </button>
                </form>
            </div>

            <div class="divide-y divide-slate-50 max-h-[560px] overflow-y-auto">
                @forelse($teacherRatings as $r)
                @php
                    $t = $r->teacher;
                    $name = $t ? trim($t->first_name.' '.$t->last_name) : 'Unknown teacher';
                    $avatarUrl = $t?->user?->avatarUrl() ?? "https://ui-avatars.com/api/?name=".urlencode($name)."&background=0d326b&color=fff&size=64&bold=true&rounded=true";
                @endphp
                <div class="px-6 py-4 rating-card" data-type="teacher" data-id="{{ $r->id }}" data-approved="{{ $r->is_approved ? '1' : '0' }}">
                    <div class="flex items-start gap-3">
                        <img src="{{ $avatarUrl }}" class="w-9 h-9 rounded-full object-cover border border-slate-100 flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[13px] font-bold text-slate-800 truncate">{{ $name }}</p>
                                <span class="status-badge flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $r->is_approved ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $r->is_approved ? 'Approved' : 'Pending' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-0.5 mt-0.5">{!! $starPill($r->rating) !!}</div>
                            @if($r->feedback)
                            <p class="text-[12px] text-slate-500 mt-1.5 leading-snug">{{ $r->feedback }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-2.5">
                                <p class="text-[10px] text-slate-300 font-medium">{{ $r->updated_at->diffForHumans() }}</p>
                                <button type="button"
                                        class="approval-toggle-btn text-[11px] font-bold px-3 py-1.5 rounded-full transition-colors {{ $r->is_approved ? 'bg-slate-100 text-slate-500 hover:bg-slate-200' : 'bg-[#0d326b] text-white hover:bg-[#1e4b8f]' }}">
                                    {{ $r->is_approved ? 'Unapprove' : 'Approve' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-10 text-center">
                    <span class="material-symbols-outlined text-slate-200 text-[36px]">school</span>
                    <p class="text-[13px] text-slate-400 mt-2">No teacher ratings found</p>
                </div>
                @endforelse
            </div>

            @if($teacherRatings->hasPages())
            <div class="px-6 py-3 border-t border-slate-50">{{ $teacherRatings->onEachSide(1)->links() }}</div>
            @endif
        </div>

        {{-- Student Ratings --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-[15px] font-black text-[#0d326b]">Student Ratings</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $totalStudentRatings }} total · {{ $pendingStudentRatings }} pending</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('admin.ratings') }}" class="flex items-center gap-2">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <input type="text" name="student_search" value="{{ $studentSearch }}" placeholder="Search student name…"
                           class="flex-1 text-[12px] px-3 py-2 rounded-full border border-slate-200 focus:outline-none focus:border-[#0d326b]">
                    <button type="submit" class="w-8 h-8 flex-shrink-0 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[16px] text-slate-500">search</span>
                    </button>
                </form>
            </div>

            <div class="divide-y divide-slate-50 max-h-[560px] overflow-y-auto">
                @forelse($studentRatings as $r)
                @php
                    $s = $r->student;
                    $name = $s ? trim($s->first_name.' '.$s->last_name) : 'Unknown student';
                    $avatarUrl = "https://ui-avatars.com/api/?name=".urlencode($name)."&background=1a6fd4&color=fff&size=64&bold=true&rounded=true";
                @endphp
                <div class="px-6 py-4 rating-card" data-type="student" data-id="{{ $r->id }}" data-approved="{{ $r->is_approved ? '1' : '0' }}">
                    <div class="flex items-start gap-3">
                        <img src="{{ $avatarUrl }}" class="w-9 h-9 rounded-full object-cover border border-slate-100 flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[13px] font-bold text-slate-800 truncate">{{ $name }}</p>
                                <span class="status-badge flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $r->is_approved ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $r->is_approved ? 'Approved' : 'Pending' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-0.5 mt-0.5">{!! $starPill($r->rating) !!}</div>
                            @if($r->feedback)
                            <p class="text-[12px] text-slate-500 mt-1.5 leading-snug">{{ $r->feedback }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-2.5">
                                <p class="text-[10px] text-slate-300 font-medium">{{ $r->updated_at->diffForHumans() }}</p>
                                <button type="button"
                                        class="approval-toggle-btn text-[11px] font-bold px-3 py-1.5 rounded-full transition-colors {{ $r->is_approved ? 'bg-slate-100 text-slate-500 hover:bg-slate-200' : 'bg-[#0d326b] text-white hover:bg-[#1e4b8f]' }}">
                                    {{ $r->is_approved ? 'Unapprove' : 'Approve' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-10 text-center">
                    <span class="material-symbols-outlined text-slate-200 text-[36px]">group</span>
                    <p class="text-[13px] text-slate-400 mt-2">No student ratings found</p>
                </div>
                @endforelse
            </div>

            @if($studentRatings->hasPages())
            <div class="px-6 py-3 border-t border-slate-50">{{ $studentRatings->onEachSide(1)->links() }}</div>
            @endif
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('ratingsRoot');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    root.addEventListener('click', function (e) {
        const btn = e.target.closest('.approval-toggle-btn');
        if (!btn) return;

        const card = btn.closest('.rating-card');
        const type = card.dataset.type;
        const id = card.dataset.id;
        const nextApproved = card.dataset.approved !== '1';

        btn.disabled = true;
        const originalLabel = btn.textContent;
        btn.textContent = 'Saving…';

        fetch(`${root.dataset.approvalUrlBase}/${type}/${id}/approval`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ is_approved: nextApproved }),
        })
        .then(res => {
            if (!res.ok) throw new Error('Request failed');
            return res.json();
        })
        .then(data => {
            card.dataset.approved = data.is_approved ? '1' : '0';
            const badge = card.querySelector('.status-badge');
            if (data.is_approved) {
                badge.textContent = 'Approved';
                badge.className = 'status-badge flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700';
                btn.className = 'approval-toggle-btn text-[11px] font-bold px-3 py-1.5 rounded-full transition-colors bg-slate-100 text-slate-500 hover:bg-slate-200';
                btn.textContent = 'Unapprove';
            } else {
                badge.textContent = 'Pending';
                badge.className = 'status-badge flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700';
                btn.className = 'approval-toggle-btn text-[11px] font-bold px-3 py-1.5 rounded-full transition-colors bg-[#0d326b] text-white hover:bg-[#1e4b8f]';
                btn.textContent = 'Approve';
            }
            btn.disabled = false;
        })
        .catch(() => {
            btn.textContent = originalLabel;
            btn.disabled = false;
            alert('Something went wrong updating this rating. Please try again.');
        });
    });
});
</script>

@endsection