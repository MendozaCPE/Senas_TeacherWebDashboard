@extends('layouts.admin')
@section('title', 'Reports')
@section('content')

<div class="flex flex-col gap-4 pt-4">

    <!-- Stats Row -->
    <div class="grid grid-cols-2 xl:grid-cols-5 gap-3">
        @php
            $rStats = [
                ['label'=>'Total Reports',  'value'=>$totalReports,   'icon'=>'inbox',         'color'=>'#0d326b'],
                ['label'=>'Pending',        'value'=>$pendingReports,  'icon'=>'schedule',      'color'=>'#f59e0b'],
                ['label'=>'In Progress',    'value'=>$inProgressCount, 'icon'=>'hourglass_top', 'color'=>'#3b82f6'],
                ['label'=>'Responded',      'value'=>$respondedCount,  'icon'=>'mark_chat_read','color'=>'#8b5cf6'],
                ['label'=>'Resolved',       'value'=>$resolvedCount,   'icon'=>'check_circle',  'color'=>'#10b981'],
            ];
        @endphp
        @foreach($rStats as $rs)
        <div class="bg-white rounded-[20px] shadow-sm border border-slate-100 px-5 py-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background:{{ $rs['color'] }}">
                <span class="material-symbols-outlined text-white text-[18px]">{{ $rs['icon'] }}</span>
            </div>
            <div>
                <p class="text-[22px] font-black text-[#0d326b] leading-none">{{ $rs['value'] }}</p>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $rs['label'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5">
        <form method="GET" action="{{ route('admin.reports') }}" class="flex items-center gap-2 flex-wrap">
            <div class="relative flex-1 min-w-[180px]">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search by student name, LRN, or message..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-full text-[13px] font-medium bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/30 outline-none text-slate-700"/>
            </div>
            <div class="relative">
                <select name="status" class="appearance-none bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 pr-8 py-2.5 rounded-full border-none outline-none cursor-pointer">
                    <option value="all" {{ $statusFilter==='all'?'selected':'' }}>All Status</option>
                    <option value="pending" {{ $statusFilter==='pending'?'selected':'' }}>Pending</option>
                    <option value="in_progress" {{ $statusFilter==='in_progress'?'selected':'' }}>In Progress</option>
                    <option value="responded" {{ $statusFilter==='responded'?'selected':'' }}>Responded</option>
                    <option value="resolved" {{ $statusFilter==='resolved'?'selected':'' }}>Resolved</option>
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-[15px] text-slate-400 pointer-events-none">expand_more</span>
            </div>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 py-2.5 rounded-full border-none outline-none"/>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 py-2.5 rounded-full border-none outline-none"/>
            <button type="submit" class="px-4 py-2.5 rounded-full text-[12px] font-bold text-white flex items-center gap-1.5 transition-all hover:opacity-90"
                    style="background: linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
                <span class="material-symbols-outlined text-[14px]">filter_list</span>Filter
            </button>
            @if($search || ($statusFilter && $statusFilter!=='all') || $dateFrom || $dateTo)
            <a href="{{ route('admin.reports') }}" class="px-4 py-2.5 rounded-full border border-slate-200 text-[12px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Clear</a>
            @endif
        </form>
    </div>

    <!-- Reports List -->
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
            <h3 class="text-[15px] font-black text-[#0d326b]">
                Help Requests
                <span class="ml-2 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[12px] font-bold">{{ $reports->total() }}</span>
            </h3>
        </div>

        @if($reports->isEmpty())
        <div class="py-20 text-center">
            <span class="material-symbols-outlined text-slate-200 text-[56px]">inbox</span>
            <p class="text-[15px] text-slate-400 font-semibold mt-4">No reports found</p>
            <p class="text-[13px] text-slate-300 mt-1">Student help requests will appear here</p>
        </div>
        @else
        <div class="divide-y divide-slate-50">
            @foreach($reports as $report)
            @php
                $statusMap = [
                    'pending'     => ['bg'=>'bg-amber-100',  'text'=>'text-amber-700',  'dot'=>'bg-amber-400',  'label'=>'Pending'],
                    'in_progress' => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'dot'=>'bg-blue-400',   'label'=>'In Progress'],
                    'responded'   => ['bg'=>'bg-purple-100', 'text'=>'text-purple-700', 'dot'=>'bg-purple-400', 'label'=>'Responded'],
                    'resolved'    => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','dot'=>'bg-emerald-400','label'=>'Resolved'],
                ];
                $sc = $statusMap[$report->status] ?? $statusMap['pending'];
                $studentName = trim(($report->student->first_name ?? '') . ' ' . ($report->student->last_name ?? 'Unknown'));
                $initials = strtoupper(substr($report->student->first_name ?? 'U', 0, 1) . substr($report->student->last_name ?? '?', 0, 1));
            @endphp
            <div class="flex items-start gap-4 px-6 py-5 hover:bg-[#f8fafc] transition-colors cursor-pointer"
                 onclick="openReportModal({{ $report->help_request_id }})">

                <!-- Avatar -->
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#0d326b] to-[#1a6fd4] flex items-center justify-center text-white text-[13px] font-black flex-shrink-0">
                    {{ $initials }}
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[14px] font-bold text-slate-800 truncate">{{ $studentName }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($report->student?->lrn)
                                <span class="text-[11px] text-slate-400 font-medium">LRN: {{ $report->student->lrn }}</span>
                                <span class="text-slate-200">·</span>
                                @endif
                                @if($report->student?->grade_level)
                                <span class="text-[11px] text-slate-400 font-medium">Grade {{ $report->student->grade_level }}</span>
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
                    <p class="text-[13px] text-slate-500 mt-2 leading-snug line-clamp-2">{{ $report->message }}</p>
                    @if($report->admin_response)
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="material-symbols-outlined text-[14px] text-emerald-500">mark_chat_read</span>
                        <p class="text-[11px] text-emerald-600 font-semibold truncate">Response: {{ Str::limit($report->admin_response, 60) }}</p>
                    </div>
                    @endif
                </div>

                <span class="material-symbols-outlined text-slate-300 text-[20px] flex-shrink-0 mt-1">chevron_right</span>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
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

<!-- Report Detail Modal -->
<div id="reportModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] z-[999] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl max-w-lg w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-300 overflow-hidden" id="reportModalBox">
        <!-- Modal Header -->
        <div class="px-7 pt-7 pb-5 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div id="report-avatar" class="w-10 h-10 rounded-full bg-gradient-to-br from-[#0d326b] to-[#1a6fd4] flex items-center justify-center text-white text-[13px] font-black">—</div>
                    <div>
                        <p id="report-student-name" class="text-[15px] font-black text-[#0d326b]">—</p>
                        <p id="report-student-meta" class="text-[11px] text-slate-400 font-medium"></p>
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

        <!-- Modal Body -->
        <div class="px-7 py-5 max-h-[60vh] overflow-y-auto">
            <!-- Loading -->
            <div id="report-loading" class="py-10 text-center">
                <span class="material-symbols-outlined text-slate-300 text-[36px] animate-spin">refresh</span>
                <p class="text-[13px] text-slate-400 mt-2">Loading report...</p>
            </div>
            <!-- Content -->
            <div id="report-content" class="hidden space-y-5">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2">Student Message</p>
                    <div class="bg-[#f8fafc] rounded-2xl p-4">
                        <p id="report-message" class="text-[14px] text-slate-700 leading-relaxed"></p>
                    </div>
                    <p id="report-date" class="text-[11px] text-slate-400 font-medium mt-2"></p>
                </div>
                <!-- Admin Response (if any) -->
                <div id="existing-response-section" class="hidden">
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2">Admin Response</p>
                    <div class="bg-[#ecfdf5] border border-emerald-100 rounded-2xl p-4">
                        <p id="existing-response-text" class="text-[13px] text-emerald-800 leading-relaxed"></p>
                        <p id="existing-response-date" class="text-[11px] text-emerald-500 font-medium mt-2"></p>
                    </div>
                </div>
                <!-- Response Form -->
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2">Your Response</p>
                    <textarea id="response-textarea" rows="4"
                              placeholder="Write your response to this student..."
                              class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-[13px] text-slate-700 focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/30 outline-none resize-none leading-relaxed"></textarea>
                    <div class="flex items-center gap-2 mt-3">
                        <div class="relative flex-1">
                            <select id="response-status" class="w-full appearance-none bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 pr-8 py-2.5 rounded-full border-none outline-none cursor-pointer">
                                <option value="in_progress">Mark In Progress</option>
                                <option value="responded">Mark Responded</option>
                                <option value="resolved">Mark Resolved</option>
                            </select>
                            <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-[15px] text-slate-400 pointer-events-none">expand_more</span>
                        </div>
                        <button onclick="submitResponse()"
                                class="px-5 py-2.5 rounded-full text-[13px] font-bold text-white flex items-center gap-1.5 transition-all hover:opacity-90"
                                style="background: linear-gradient(135deg,#0d326b 0%,#1a6fd4 100%)">
                            <span class="material-symbols-outlined text-[15px]">send</span>Send
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
    pending:     { bg:'bg-amber-100',  text:'text-amber-700',   label:'Pending' },
    in_progress: { bg:'bg-blue-100',   text:'text-blue-700',    label:'In Progress' },
    responded:   { bg:'bg-purple-100', text:'text-purple-700',  label:'Responded' },
    resolved:    { bg:'bg-emerald-100',text:'text-emerald-700', label:'Resolved' },
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
    const sc = statusConfig[data.status] || statusConfig['pending'];
    document.getElementById('report-avatar').textContent         = data.student ? data.student.initials : '?';
    document.getElementById('report-student-name').textContent   = data.student ? data.student.name : 'Unknown Student';
    document.getElementById('report-student-meta').textContent   = data.student
        ? ['LRN: ' + (data.student.lrn || '—'), 'Grade ' + (data.student.grade_level || '—'), data.student.section || ''].filter(Boolean).join(' · ')
        : '';
    const badge = document.getElementById('report-status-badge');
    badge.textContent  = sc.label;
    badge.className    = `px-2.5 py-1 rounded-full text-[11px] font-bold ${sc.bg} ${sc.text}`;
    document.getElementById('report-message').textContent = data.message;
    document.getElementById('report-date').textContent    = 'Submitted ' + data.created_at;

    const existingSection = document.getElementById('existing-response-section');
    if (data.admin_response) {
        document.getElementById('existing-response-text').textContent = data.admin_response;
        document.getElementById('existing-response-date').textContent = 'Responded ' + (data.responded_at || '');
        existingSection.classList.remove('hidden');
    } else {
        existingSection.classList.add('hidden');
    }

    document.getElementById('report-loading').classList.add('hidden');
    document.getElementById('report-content').classList.remove('hidden');
}

function closeReportModal() {
    const modal = document.getElementById('reportModal');
    const box   = document.getElementById('reportModalBox');
    modal.classList.add('opacity-0'); box.classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

async function submitResponse() {
    const responseText = document.getElementById('response-textarea').value.trim();
    const status       = document.getElementById('response-status').value;
    const fb           = document.getElementById('response-feedback');

    if (!responseText) {
        fb.textContent  = 'Please write a response before sending.';
        fb.className    = 'mt-3 px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-red-50 text-red-600';
        fb.classList.remove('hidden');
        return;
    }

    try {
        const res  = await fetch(`/admin/reports/${currentReportId}/respond`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ admin_response: responseText, status }),
        });
        const data = await res.json();
        if (data.success) {
            fb.textContent = 'Response sent successfully.';
            fb.className   = 'mt-3 px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-emerald-50 text-emerald-700';
            fb.classList.remove('hidden');

            // Update badge
            const sc = statusConfig[data.status] || statusConfig['responded'];
            const badge = document.getElementById('report-status-badge');
            badge.textContent = sc.label;
            badge.className   = `px-2.5 py-1 rounded-full text-[11px] font-bold ${sc.bg} ${sc.text}`;

            // Show existing response
            document.getElementById('existing-response-text').textContent = data.admin_response;
            document.getElementById('existing-response-date').textContent = 'Responded ' + (data.responded_at || '');
            document.getElementById('existing-response-section').classList.remove('hidden');
            document.getElementById('response-textarea').value = '';

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
