@php $layout = request()->routeIs('admin.*') ? 'layouts.admin' : 'layouts.app'; @endphp
@extends($layout)
@section('title', 'Checkpoint Exam Details')

@section('content')
<style>
    .exam-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 3px rgba(13, 50, 107, 0.05);
        overflow: hidden;
    }
    .badge-status {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .badge-status.published { background: #8b5cf6; color: #ffffff; }
    .badge-status.draft { background: #fef3c7; color: #92400e; }
</style>

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <a href="{{ route('lessons.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-[#0d326b] transition mb-2">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            Back to Lessons
        </a>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-[#0d326b]">{{ $exam->title }}</h1>
            <span class="badge-status {{ $exam->status }}">{{ $exam->status }}</span>
        </div>
        <p class="text-sm text-slate-500 font-medium">
            Module: <span class="font-bold text-slate-700">{{ $exam->module->title ?? 'N/A' }}</span>
        </p>
    </div>

    <div class="flex items-center gap-3">
        @if($exam->status === 'draft')
        <button onclick="openPublishModal()" class="py-2.5 px-5 rounded-xl bg-[#8b5cf6] text-white font-bold text-xs shadow-md hover:bg-[#7c3aed] transition flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[18px]">send</span>
            Publish Exam
        </button>
        @endif

        <form action="{{ route('lessons.checkpoint-exam.destroy', $exam->hash_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this checkpoint exam?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="py-2.5 px-4 rounded-xl border border-red-200 bg-red-50 text-red-600 font-bold text-xs hover:bg-red-100 transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">delete</span>
                Delete
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-xs font-semibold text-green-700 flex items-center gap-2">
    <span class="material-symbols-outlined text-[18px]">check_circle</span>
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Questions List (2 Cols) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="exam-card p-6">
            <h2 class="text-base font-bold text-[#0d326b] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#8b5cf6]">quiz</span>
                Exam Questions ({{ count($questions) }})
            </h2>

            <div class="space-y-4">
                @foreach($questions as $index => $q)
                <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/70 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">Question #{{ $q['question_number'] }}</span>
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-700">
                            {{ $q['points'] }} {{ $q['points'] === 1 ? 'Point' : 'Points' }}
                        </span>
                    </div>

                    <p class="text-sm font-semibold text-slate-800">{{ $q['question_text'] }}</p>

                    @if(!empty($q['options']))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pt-2">
                        @foreach($q['options'] as $opt)
                        <div class="px-3 py-2 rounded-lg text-xs font-medium border {{ !empty($opt['is_correct']) ? 'bg-green-50 border-green-300 text-green-800 font-bold flex items-center justify-between' : 'bg-white border-slate-200 text-slate-600' }}">
                            <span>{{ $opt['text'] }}</span>
                            @if(!empty($opt['is_correct']))
                            <span class="material-symbols-outlined text-[16px] text-green-600">check_circle</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Summary Stats Sidebar (1 Col) -->
    <div class="lg:col-span-1">
        <div class="exam-card p-6 space-y-4">
            <h3 class="text-base font-bold text-[#0d326b] mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#8b5cf6]">info</span>
                Exam Overview
            </h3>

           <div class="p-4 rounded-xl bg-purple-50 border border-purple-100 space-y-3">
    <div class="flex items-center justify-between text-sm">
        <span class="text-slate-600 font-medium">Total Questions:</span>
        <span class="font-bold text-slate-800">{{ count($questions) }}</span>
    </div>
    <div class="flex items-center justify-between text-sm">
        <span class="text-slate-600 font-medium">Total Points:</span>
        <span class="font-bold text-purple-700 text-base">{{ $exam->total_points }} pts</span>
    </div>
    <div class="flex items-center justify-between text-sm">
        <span class="text-slate-600 font-medium">Passing Score:</span>
        <span class="font-bold text-slate-800">{{ $exam->passing_score }} pts</span>
    </div>
    <!-- ✅ ADD THIS -->
    <div class="flex items-center justify-between text-sm">
        <span class="text-slate-600 font-medium">Time Limit:</span>
        <span class="font-bold text-amber-600">{{ $exam->time_limit_minutes ?? 60 }} minutes</span>
    </div>
</div>

            @if($exam->description)
            <div class="text-xs text-slate-500 leading-relaxed border-t border-slate-100 pt-3">
                <span class="font-bold text-slate-700 block mb-1">Description:</span>
                {{ $exam->description }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Publish Modal -->
@if($exam->status === 'draft')
<div id="publishModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-[#0d326b] flex items-center gap-2">
                <span class="material-symbols-outlined text-[#8b5cf6]">send</span>
                Publish Checkpoint Exam
            </h3>
            <button onclick="closePublishModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form action="{{ route('lessons.checkpoint-exam.publish', $exam->hash_id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Publish Target</label>
                <select name="publish_option" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 font-semibold text-sm text-slate-800 outline-none">
                    <option value="all">All Active Students</option>
                </select>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="notify_students" id="notifyCheck" value="1" checked class="w-4 h-4 text-purple-600 rounded border-slate-300">
                <label for="notifyCheck" class="text-xs font-semibold text-slate-700 cursor-pointer">Notify assigned students</label>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closePublishModal()" class="py-2 px-4 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition">Cancel</button>
                <button type="submit" class="py-2 px-5 rounded-xl bg-[#8b5cf6] text-white font-bold text-xs shadow hover:bg-[#7c3aed] transition">Confirm & Publish</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPublishModal() {
    document.getElementById('publishModal').classList.remove('hidden');
}
function closePublishModal() {
    document.getElementById('publishModal').classList.add('hidden');
}
</script>
@endif

@endsection
