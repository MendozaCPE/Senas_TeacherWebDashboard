@extends('layouts.admin')
@section('title', 'Escalated Reports')
@section('content')

<div class="flex flex-col gap-4 pt-4">

    {{-- ── Workflow Banner ─────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-r from-[#071c3f] to-[#1e4b8f] rounded-[20px] px-6 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-white text-[20px]">account_tree</span>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-white font-black text-[14px]">Escalated Concerns Workflow</p>
            <p class="text-white/60 text-[12px] font-medium mt-0.5">
                Student → Teacher reviews → Teacher escalates → Admin handles
            </p>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-white/50 text-[11px] font-semibold flex-shrink-0">
            <span class="px-2.5 py-1 rounded-full bg-white/10">Student</span>
            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            <span class="px-2.5 py-1 rounded-full bg-white/10">Teacher</span>
            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            <span class="px-2.5 py-1 rounded-full bg-amber-400/30 text-amber-300">Admin</span>
        </div>
    </div>

    {{-- ── Stats Row ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @php
            $rStats = [
                ['label'=>'Total Escalated',  'value'=>$totalReports,  'icon'=>'warning',      'color'=>'#0d326b', 'subcolor'=>'#94a3b8', 'sub'=>'escalated to admin'],
                ['label'=>'Awaiting Review',  'value'=>$pendingReports,'icon'=>'schedule',     'color'=>'#f59e0b', 'subcolor'=>'#d97706', 'sub'=>'pending admin response'],
                ['label'=>'Closed by Admin',  'value'=>$resolvedCount, 'icon'=>'check_circle', 'color'=>'#10b981', 'subcolor'=>'#10b981', 'sub'=>'successfully resolved'],
                ['label'=>'All Time Reports', 'value'=>\App\Models\HelpRequest::count(), 'icon'=>'inbox', 'color'=>'#64748b', 'subcolor'=>'#94a3b8', 'sub'=>'student help requests total'],
            ];
        @endphp
        @foreach($rStats as $rs)
        <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[148px]">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background:{{ $rs['color'] }}">
                    <span class="material-symbols-outlined text-white text-[18px]">{{ $rs['icon'] }}</span>
                </div>
                <h3 class="text-[14px] font-semibold text-slate-700 leading-none">{{ $rs['label'] }}</h3>
            </div>
            <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $rs['value'] }}</p>
            <p class="text-[12px] font-medium mt-2" style="color:{{ $rs['subcolor'] }}">{{ $rs['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── Filter Bar ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5">
        <form method="GET" action="{{ route('admin.reports') }}" class="flex items-center gap-2 flex-wrap">
            <div class="relative flex-1 min-w-[180px]">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search by student, teacher, or message..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-full text-[13px] font-medium bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/30 outline-none text-slate-700"/>
            </div>
            <div class="relative">
                <select name="status" class="appearance-none bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 pr-8 py-2.5 rounded-full border-none outline-none cursor-pointer">
                    <option value="all"       {{ $statusFilter==='all'      ?'selected':'' }}>All</option>
                    <option value="escalated" {{ $statusFilter==='escalated'?'selected':'' }}>Escalated (Open)</option>
                    <option value="closed"    {{ $statusFilter==='closed'   ?'selected':'' }}>Closed</option>
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-[15px] text-slate-400 pointer-events-none">expand_more</span>
            </div>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 py-2.5 rounded-full border-none outline-none"/>
            <input type="date" name="date_to"   value="{{ $dateTo }}"   class="bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 py-2.5 rounded-full border-none outline-none"/>
            <button type="submit" class="px-4 py-2.5 rounded-full text-[12px] font-bold text-white flex items-center gap-1.5 transition-all hover:opacity-90"
                    style="background: linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
                <span class="material-symbols-outlined text-[14px]">filter_list</span>Filter
            </button>
            @if($search || ($statusFilter && $statusFilter!=='all') || $dateFrom || $dateTo)
            <a href="{{ route('admin.reports') }}" class="px-4 py-2.5 rounded-full border border-slate-200 text-[12px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Clear</a>
            @endif
        </form>
    </div>

    {{-- ── Reports List ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-[15px] font-black text-[#0d326b] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-amber-500">warning</span>
                    Escalated Concerns
                    <span class="ml-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[12px] font-bold">{{ $reports->total() }}</span>
                </h3>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">Only reports escalated by teachers appear here</p>
            </div>
        </div>

        @if($reports->isEmpty())
        <div class="py-20 text-center">
            <span class="material-symbols-outlined text-slate-200 text-[56px]">mark_email_read</span>
            <p class="text-[15px] text-slate-400 font-semibold mt-4">No escalated reports</p>
            <p class="text-[13px] text-slate-300 mt-1">Teachers will escalate concerns here when they need admin attention</p>
        </div>
        @else
        <div class="divide-y divide-slate-50">
            @foreach($reports as $report)
            @php
                $statusMap = [
                    'escalated' => ['bg'=>'bg-amber-100',   'text'=>'text-amber-700',   'dot'=>'bg-amber-400',   'label'=>'Escalated'],
                    'closed'    => ['bg'=>'bg-emerald-100', 'text'=>'text-emerald-700', 'dot'=>'bg-emerald-400', 'label'=>'Closed'],
                ];
                $sc = $statusMap[$report->status] ?? $statusMap['escalated'];
                $studentName = trim(($report->student->first_name ?? '') . ' ' . ($report->student->last_name ?? 'Unknown'));
                $teacherName = $report->teacher ? trim($report->teacher->first_name . ' ' . $report->teacher->last_name) : 'Unknown Teacher';
                $initials = strtoupper(substr($report->student->first_name ?? 'U', 0, 1) . substr($report->student->last_name ?? '?', 0, 1));
            @endphp
            <div class="flex items-start gap-4 px-6 py-5 hover:bg-[#f8fafc] transition-colors cursor-pointer"
                 onclick="openReportModal({{ $report->help_request_id }})">

                {{-- Avatar --}}
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#0d326b] to-[#1a6fd4] flex items-center justify-center text-white text-[13px] font-black flex-shrink-0">
                    {{ $initials }}
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[14px] font-bold text-slate-800 truncate">{{ $studentName }}</p>
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                @if($report->student?->lrn)
                                <span class="text-[11px] text-slate-400 font-medium">LRN: {{ $report->student->lrn }}</span>
                                <span class="text-slate-200">·</span>
                                @endif
                                <span class="text-[11px] text-slate-500 font-semibold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px] text-[#0d326b]">school</span>
                                    Teacher: {{ $teacherName }}
                                </span>
                                @if($report->escalated_at)
                                <span class="text-slate-200">·</span>
                                <span class="text-[11px] text-amber-600 font-semibold">Escalated {{ $report->escalated_at->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $sc['bg'] }} {{ $sc['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }} inline-block"></span>
                                {{ $sc['label'] }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap">{{ $report->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    {{-- Original message --}}
                    <p class="text-[13px] text-slate-500 mt-2 leading-snug line-clamp-2">{{ $report->message }}</p>

                    {{-- Escalation reason --}}
                    @if($report->escalation_reason)
                    <div class="flex items-start gap-1.5 mt-2 bg-amber-50 rounded-xl px-3 py-2">
                        <span class="material-symbols-outlined text-[14px] text-amber-500 flex-shrink-0 mt-0.5">flag</span>
                        <p class="text-[12px] text-amber-700 font-semibold leading-snug line-clamp-2">Reason: {{ $report->escalation_reason }}</p>
                    </div>
                    @endif

                    {{-- Admin response indicator --}}
                    @if($report->admin_response)
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="material-symbols-outlined text-[14px] text-emerald-500">mark_chat_read</span>
                        <p class="text-[11px] text-emerald-600 font-semibold truncate">Admin response: {{ Str::limit($report->admin_response, 60) }}</p>
                    </div>
                    @endif
                </div>

                <span class="material-symbols-outlined text-slate-300 text-[20px] flex-shrink-0 mt-1">chevron_right</span>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($reports->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <p class="text-[12px] text-slate-400 font-medium">
                Showing {{ $reports->firstItem() }}–{{ $reports->lastItem() }} of {{ $reports->total() }} reports
            </p>
            <div class="flex items-center gap-1">
                @if($reports->onFirstPage())
                <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-300"><span class="material-symbols-outlined text-[16px]">chevron_left</span></span>
                @else
                <a href="{{ $reports->previousPageUrl() }}" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors"><span class="material-symbols-outlined text-[16px] text-slate-600">chevron_left</span></a>
                @endif
                @foreach($reports->getUrlRange(max(1,$reports->currentPage()-2), min($reports->lastPage(),$reports->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-bold transition-colors {{ $page == $reports->currentPage() ? 'bg-[#0d326b] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $page }}</a>
                @endforeach
                @if($reports->hasMorePages())
                <a href="{{ $reports->nextPageUrl() }}" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors"><span class="material-symbols-outlined text-[16px] text-slate-600">chevron_right</span></a>
                @else
                <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-300"><span class="material-symbols-outlined text-[16px]">chevron_right</span></span>
                @endif
            </div>
        </div>
        @endif
        @endif
    </div>
</div>

{{-- ── Report Detail Modal ───────────────────────────────────────────────────── --}}
<div id="reportModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] z-[999] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl max-w-lg w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-300 overflow-hidden" id="reportModalBox">

        {{-- Modal Header --}}
        <div class="px-7 pt-7 pb-5 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div id="report-avatar" class="w-10 h-10 rounded-full bg-gradient-to-br from-[#0d326b] to-[#1a6fd4] flex items-center justify-center text-white text-[13px] font-black">—</div>
                    <div>
                        <p id="report-student-name" class="text-[15px] font-black text-[#0d326b]">—</p>
                        <p id="report-student-meta" class="text-[11px] text-slate-400 font-medium"></p>
                        <p id="report-teacher-name" class="text-[11px] text-[#0d326b] font-semibold mt-0.5"></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span id="report-status-badge" class="px-2.5 py-1 rounded-full text-[11px] font-bold"></span>
                    <button onclick="closeReportModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-[18px] text-slate-600">close</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Body --}}
        <div class="px-7 py-5 max-h-[65vh] overflow-y-auto">
            <div id="report-loading" class="py-10 text-center">
                <span class="material-symbols-outlined text-slate-300 text-[36px] animate-spin">refresh</span>
                <p class="text-[13px] text-slate-400 mt-2">Loading report...</p>
            </div>

            <div id="report-content" class="hidden space-y-4">

                {{-- Student's original message --}}
                <div>
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Student's Message</p>
                    <div class="bg-[#f8fafc] rounded-2xl p-4">
                        <p id="report-message" class="text-[14px] text-slate-700 leading-relaxed"></p>
                    </div>
                    <p id="report-date" class="text-[11px] text-slate-400 font-medium mt-2"></p>
                </div>

                {{-- Teacher's response --}}
                <div id="teacher-response-section" class="hidden">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Teacher's Response</p>
                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4">
                        <p id="teacher-response-text" class="text-[13px] text-blue-800 leading-relaxed"></p>
                        <p id="teacher-response-date" class="text-[11px] text-blue-400 font-medium mt-2"></p>
                    </div>
                </div>

                {{-- Escalation reason --}}
                <div id="escalation-section" class="hidden">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Escalation Reason</p>
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                        <div class="flex items-start gap-2 mb-2">
                            <span class="material-symbols-outlined text-[16px] text-amber-500 flex-shrink-0 mt-0.5">flag</span>
                            <p id="escalation-reason-text" class="text-[13px] text-amber-800 leading-relaxed font-medium"></p>
                        </div>
                        <p id="escalation-date" class="text-[11px] text-amber-500 font-medium"></p>
                        <p id="escalation-by" class="text-[11px] text-amber-600 font-semibold"></p>
                    </div>
                </div>

                {{-- Existing admin response --}}
                <div id="existing-response-section" class="hidden">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Admin Response</p>
                    <div class="bg-[#ecfdf5] border border-emerald-100 rounded-2xl p-4">
                        <p id="existing-response-text" class="text-[13px] text-emerald-800 leading-relaxed"></p>
                        <p id="existing-response-date" class="text-[11px] text-emerald-500 font-medium mt-2"></p>
                    </div>
                </div>

                {{-- Admin response form (only shown for escalated, not closed) --}}
                <div id="response-form-section">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Your Response</p>
                    <textarea id="response-textarea" rows="4"
                              placeholder="Write your response to close this escalated concern..."
                              class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-[13px] text-slate-700 focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/30 outline-none resize-none leading-relaxed"></textarea>
                    <div class="flex items-center gap-2 mt-3">
                        <button onclick="submitAdminResponse()"
                                class="flex-1 px-5 py-2.5 rounded-full text-[13px] font-bold text-white flex items-center justify-center gap-1.5 transition-all hover:opacity-90"
                                style="background: linear-gradient(135deg,#0d326b 0%,#1a6fd4 100%)">
                            <span class="material-symbols-outlined text-[15px]">check_circle</span>Close & Respond
                        </button>
                    </div>
                    <div id="response-feedback" class="hidden mt-3 px-4 py-2.5 rounded-xl text-[13px] font-semibold"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentReportId = null;
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const statusConfig = {
    escalated: { bg:'bg-amber-100',   text:'text-amber-700',   label:'Escalated' },
    closed:    { bg:'bg-emerald-100', text:'text-emerald-700', label:'Closed' },
};

async function openReportModal(id) {
    currentReportId = id;
    const modal = document.getElementById('reportModal');
    const box   = document.getElementById('reportModalBox');
    document.getElementById('report-loading').classList.remove('hidden');
    document.getElementById('report-content').classList.add('hidden');
    document.getElementById('response-feedback').classList.add('hidden');
    document.getElementById('response-textarea').value = '';
    modal.classList.remove('hidden');
    requestAnimationFrame(() => { modal.classList.remove('opacity-0'); box.classList.remove('scale-95'); });

    try {
        const res  = await fetch(`/admin/reports/${id}`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        renderReport(data);
    } catch {
        document.getElementById('report-loading').innerHTML = '<p class="text-red-500 text-[13px]">Failed to load report.</p>';
    }
}

function renderReport(data) {
    const sc = statusConfig[data.status] || statusConfig['escalated'];

    // Header
    document.getElementById('report-avatar').textContent       = data.student ? data.student.initials : '?';
    document.getElementById('report-student-name').textContent = data.student ? data.student.name : 'Unknown Student';
    document.getElementById('report-student-meta').textContent = data.student
        ? ['LRN: ' + (data.student.lrn || '—'), 'Grade ' + (data.student.grade_level || '—')].filter(Boolean).join(' · ')
        : '';
    document.getElementById('report-teacher-name').textContent = data.teacher_name ? '👩‍🏫 Teacher: ' + data.teacher_name : '';

    const badge = document.getElementById('report-status-badge');
    badge.textContent = sc.label;
    badge.className   = `px-2.5 py-1 rounded-full text-[11px] font-bold ${sc.bg} ${sc.text}`;

    // Student message
    document.getElementById('report-message').textContent = data.message;
    document.getElementById('report-date').textContent    = 'Submitted ' + data.created_at;

    // Teacher response
    const trSection = document.getElementById('teacher-response-section');
    if (data.teacher_response) {
        document.getElementById('teacher-response-text').textContent = data.teacher_response;
        document.getElementById('teacher-response-date').textContent = data.teacher_responded_at ? 'Responded ' + data.teacher_responded_at : '';
        trSection.classList.remove('hidden');
    } else {
        trSection.classList.add('hidden');
    }

    // Escalation reason
    const escSection = document.getElementById('escalation-section');
    if (data.escalation_reason) {
        document.getElementById('escalation-reason-text').textContent = data.escalation_reason;
        document.getElementById('escalation-date').textContent   = data.escalated_at ? 'Escalated ' + data.escalated_at : '';
        document.getElementById('escalation-by').textContent     = data.escalated_by_name ? 'By: ' + data.escalated_by_name : '';
        escSection.classList.remove('hidden');
    } else {
        escSection.classList.add('hidden');
    }

    // Existing admin response
    const existingSection = document.getElementById('existing-response-section');
    if (data.admin_response) {
        document.getElementById('existing-response-text').textContent = data.admin_response;
        document.getElementById('existing-response-date').textContent = 'Responded ' + (data.responded_at || '');
        existingSection.classList.remove('hidden');
    } else {
        existingSection.classList.add('hidden');
    }

    // Hide response form if already closed
    const formSection = document.getElementById('response-form-section');
    formSection.style.display = (data.status === 'closed') ? 'none' : 'block';

    document.getElementById('report-loading').classList.add('hidden');
    document.getElementById('report-content').classList.remove('hidden');
}

function closeReportModal() {
    const modal = document.getElementById('reportModal');
    const box   = document.getElementById('reportModalBox');
    modal.classList.add('opacity-0'); box.classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

async function submitAdminResponse() {
    const responseText = document.getElementById('response-textarea').value.trim();
    const fb           = document.getElementById('response-feedback');

    if (!responseText) {
        fb.textContent = 'Please write a response before closing.';
        fb.className   = 'mt-3 px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-red-50 text-red-600';
        fb.classList.remove('hidden');
        return;
    }

    try {
        const res  = await fetch(`/admin/reports/${currentReportId}/respond`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ admin_response: responseText, status: 'closed' }),
        });
        const data = await res.json();
        if (data.success) {
            fb.textContent = 'Report closed successfully.';
            fb.className   = 'mt-3 px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-emerald-50 text-emerald-700';
            fb.classList.remove('hidden');

            const badge = document.getElementById('report-status-badge');
            badge.textContent = 'Closed';
            badge.className   = 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700';

            document.getElementById('existing-response-text').textContent = data.admin_response;
            document.getElementById('existing-response-date').textContent = 'Responded ' + (data.responded_at || '');
            document.getElementById('existing-response-section').classList.remove('hidden');
            document.getElementById('response-form-section').style.display = 'none';

            setTimeout(() => { closeReportModal(); window.location.reload(); }, 1500);
        } else {
            fb.textContent = data.message || 'Failed to send response.';
            fb.className   = 'mt-3 px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-red-50 text-red-600';
            fb.classList.remove('hidden');
        }
    } catch {
        fb.textContent = 'An error occurred. Please try again.';
        fb.className   = 'mt-3 px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-red-50 text-red-600';
        fb.classList.remove('hidden');
    }
}

document.getElementById('reportModal').addEventListener('click', function(e) {
    if (e.target === this) closeReportModal();
});
</script>
@endsection
