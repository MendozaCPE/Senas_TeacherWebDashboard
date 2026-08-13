@extends('layouts.admin')
@section('title', 'Audit Logs')
@section('content')

<div class="flex flex-col gap-4 pt-4">

    <!-- Stats Row -->
    <div class="grid grid-cols-3 gap-4">
        @php
            $logStats = [
                ['label'=>'Total Log Entries', 'value'=>number_format($totalLogs), 'icon'=>'fact_check',  'color'=>'#0d326b'],
                ['label'=>'Today',             'value'=>number_format($todayLogs), 'icon'=>'today',        'color'=>'#1e4b8f'],
                ['label'=>'Last 7 Days',       'value'=>number_format($weekLogs),  'icon'=>'date_range',  'color'=>'#1a6fd4'],
            ];
        @endphp
        @foreach($logStats as $ls)
        <div class="bg-white rounded-[20px] shadow-sm border border-slate-100 px-5 py-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background:{{ $ls['color'] }}">
                <span class="material-symbols-outlined text-white text-[18px]">{{ $ls['icon'] }}</span>
            </div>
            <div>
                <p class="text-[22px] font-black text-[#0d326b] leading-none">{{ $ls['value'] }}</p>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $ls['label'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5">
        <form method="GET" action="{{ route('admin.audit-logs') }}" class="flex items-center gap-2 flex-wrap">
            <!-- Search -->
            <div class="relative flex-1 min-w-[180px]">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search action, description, or user..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-full text-[13px] font-medium bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/30 outline-none text-slate-700"/>
            </div>
            <!-- Module filter -->
            <div class="relative">
                <select name="module" class="appearance-none bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 pr-8 py-2.5 rounded-full border-none outline-none cursor-pointer">
                    <option value="all" {{ $module==='all'?'selected':'' }}>All Modules</option>
                    @foreach($modules as $mod)
                    <option value="{{ $mod }}" {{ $module===$mod?'selected':'' }}>{{ ucfirst($mod) }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-[15px] text-slate-400 pointer-events-none">expand_more</span>
            </div>
            <!-- Date From -->
            <input type="date" name="date_from" value="{{ $dateFrom }}"
                   class="bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 py-2.5 rounded-full border-none outline-none cursor-pointer"/>
            <!-- Date To -->
            <input type="date" name="date_to" value="{{ $dateTo }}"
                   class="bg-slate-100 text-slate-700 text-[12px] font-semibold px-4 py-2.5 rounded-full border-none outline-none cursor-pointer"/>
            <button type="submit" class="px-4 py-2.5 rounded-full text-[12px] font-bold text-white flex items-center gap-1.5 transition-all hover:opacity-90"
                    style="background: linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
                <span class="material-symbols-outlined text-[14px]">filter_list</span>Filter
            </button>
            @if($search || ($module && $module!=='all') || $dateFrom || $dateTo)
            <a href="{{ route('admin.audit-logs') }}" class="px-4 py-2.5 rounded-full border border-slate-200 text-[12px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Clear</a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
            <h3 class="text-[15px] font-black text-[#0d326b]">
                Log Entries
                <span class="ml-2 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[12px] font-bold">{{ $logs->total() }}</span>
            </h3>
            <p class="text-[12px] text-slate-400 font-medium">Newest first</p>
        </div>

        @if($logs->isEmpty())
        <div class="py-20 text-center">
            <span class="material-symbols-outlined text-slate-200 text-[56px]">fact_check</span>
            <p class="text-[15px] text-slate-400 font-semibold mt-4">No audit logs found</p>
            <p class="text-[13px] text-slate-300 mt-1">System activity will appear here as actions are performed</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="border-b border-slate-100 bg-[#f8fafc]">
                        <th class="px-6 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400 w-44">Date & Time</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">User</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Action</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Module</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Description</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400 w-28">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($logs as $log)
                    @php
                        $moduleColors = [
                            'auth'     => ['bg'=>'bg-[#dbeafe]', 'text'=>'text-[#1e3a8a]'],
                            'accounts' => ['bg'=>'bg-purple-100', 'text'=>'text-purple-700'],
                            'reports'  => ['bg'=>'bg-amber-100',  'text'=>'text-amber-700'],
                            'lessons'  => ['bg'=>'bg-[#ecfdf5]', 'text'=>'text-emerald-700'],
                            'students' => ['bg'=>'bg-blue-100',  'text'=>'text-blue-700'],
                            'settings' => ['bg'=>'bg-slate-100', 'text'=>'text-slate-600'],
                        ];
                        $mc = $moduleColors[$log->module] ?? ['bg'=>'bg-slate-100','text'=>'text-slate-500'];
                        $roleColors = ['admin'=>'bg-[#0d326b] text-white','teacher'=>'bg-[#dbeafe] text-[#0d326b]','student'=>'bg-slate-100 text-slate-500'];
                        $rc = $roleColors[$log->user_role] ?? 'bg-slate-100 text-slate-500';
                    @endphp
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="font-semibold text-slate-700">{{ $log->created_at->format('M d, Y') }}</p>
                            <p class="text-[11px] text-slate-400">{{ $log->created_at->format('g:i A') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div>
                                    <p class="font-bold text-slate-800">{{ $log->user_name ?? '—' }}</p>
                                    @if($log->user_role)
                                    <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $rc }}">
                                        {{ $log->user_role }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <code class="text-[11px] bg-slate-100 text-slate-600 px-2 py-1 rounded-lg font-mono">{{ $log->action }}</code>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->module)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $mc['bg'] }} {{ $mc['text'] }}">
                                {{ $log->module }}
                            </span>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 max-w-xs">
                            <p class="text-slate-600 leading-snug line-clamp-2">{{ $log->description ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-[12px] font-mono">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <p class="text-[12px] text-slate-400 font-medium">
                Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} entries
            </p>
            <div class="flex items-center gap-1">
                @if($logs->onFirstPage())
                <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-300"><span class="material-symbols-outlined text-[16px]">chevron_left</span></span>
                @else
                <a href="{{ $logs->previousPageUrl() }}" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors"><span class="material-symbols-outlined text-[16px] text-slate-600">chevron_left</span></a>
                @endif
                @foreach($logs->getUrlRange(max(1,$logs->currentPage()-2), min($logs->lastPage(),$logs->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-bold transition-colors {{ $page == $logs->currentPage() ? 'bg-[#0d326b] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $page }}</a>
                @endforeach
                @if($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors"><span class="material-symbols-outlined text-[16px] text-slate-600">chevron_right</span></a>
                @else
                <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-300"><span class="material-symbols-outlined text-[16px]">chevron_right</span></span>
                @endif
            </div>
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
