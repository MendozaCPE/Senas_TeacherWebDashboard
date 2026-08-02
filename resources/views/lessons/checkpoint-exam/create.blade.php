@extends('layouts.app')
@section('title', 'Create Checkpoint Exam')

@section('content')
<style>
    .exam-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 3px rgba(13, 50, 107, 0.05);
        overflow: hidden;
    }
    .lesson-group-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        transition: all 0.2s ease;
    }
    .lesson-group-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(13, 50, 107, 0.04);
    }
    .question-item {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 16px;
        transition: background 0.15s;
    }
    .question-item:hover {
        background: #f1f5f9;
    }
    .question-item.selected {
        background: #eff6ff;
        border-color: #bfdbfe;
    }
    .badge-qtype {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .badge-qtype.mc { background: #e0e7ff; color: #3730a3; }
    .badge-qtype.tf { background: #fef3c7; color: #92400e; }
    .badge-qtype.dd { background: #dcfce7; color: #166534; }
    .badge-qtype.gt { background: #fae8ff; color: #86198f; }

    .summary-card {
        position: sticky;
        top: 24px;
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(13, 50, 107, 0.06);
    }
</style>

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <a href="{{ route('lessons.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-[#0d326b] transition mb-2">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            Back to Lessons
        </a>
        <h1 class="text-2xl font-bold text-[#0d326b]">Create Checkpoint Exam</h1>
        <p class="text-sm text-slate-500 font-medium">
            Module: <span class="font-bold text-slate-700">{{ $module->title }}</span>
        </p>
    </div>
</div>

@if($errors->any())
<div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-xs font-semibold text-red-600">
    <div class="font-bold mb-1">Please correct the following errors:</div>
    <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('lessons.checkpoint-exam.store') }}" method="POST" id="checkpointForm">
    @csrf
    <input type="hidden" name="module_id" value="{{ $module->module_id }}">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form Column (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Exam Info Card -->
            <div class="exam-card p-6">
                <h2 class="text-base font-bold text-[#0d326b] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#1a6fd4]">assignment</span>
                    Exam Details
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Exam Title *</label>
                        <input type="text" name="title" value="{{ old('title', $module->title . ' - Checkpoint Exam') }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-[#1a6fd4] focus:ring-2 focus:ring-[#1a6fd4]/20 outline-none text-sm font-semibold text-slate-800 placeholder-slate-400">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Description (Optional)</label>
                        <textarea name="description" rows="2"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-[#1a6fd4] focus:ring-2 focus:ring-[#1a6fd4]/20 outline-none text-sm font-medium text-slate-800 placeholder-slate-400"
                            placeholder="Brief instructions or summary for students...">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Passing Score (%)</label>
                            <input type="number" name="passing_score_percentage" id="passingScoreInput" value="{{ old('passing_score_percentage', 60) }}" min="1" max="100"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-[#1a6fd4] focus:ring-2 focus:ring-[#1a6fd4]/20 outline-none text-sm font-semibold text-slate-800">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question Selection Card -->
            <div class="exam-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-[#0d326b] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#1a6fd4]">quiz</span>
                            Select Questions
                        </h2>
                        <p class="text-xs text-slate-400 font-medium">Choose questions from available published lessons. Questions from previous exams are excluded.</p>
                    </div>
                </div>

                @php $qIndex = 0; @endphp
                <div class="space-y-6">
                    @foreach($availableLessons as $lesson)
                    @if($lesson->quiz && $lesson->quiz->questions->count() > 0)
                    <div class="lesson-group-card p-5">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#0d326b] flex items-center justify-center font-bold text-xs">
                                    📖
                                </span>
                                <div>
                                    <h3 class="text-sm font-bold text-[#0d326b]">{{ $lesson->title }}</h3>
                                    <span class="text-xs text-slate-400 font-medium">{{ $lesson->quiz->questions->count() }} quiz question(s)</span>
                                </div>
                            </div>
                            <button type="button" onclick="selectAllInLesson({{ $lesson->lesson_id }})"
                                class="text-xs font-bold text-[#1a6fd4] hover:underline flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">done_all</span>
                                Select All in Lesson
                            </button>
                        </div>

                        <div class="space-y-3">
                            @foreach($lesson->quiz->questions as $question)
                            @php
                                $typeBadgeClass = match($question->question_type) {
                                    'multiple_choice' => 'mc',
                                    'true_false' => 'tf',
                                    'drag_drop' => 'dd',
                                    'gesture' => 'gt',
                                    default => 'mc'
                                };
                                $typeLabel = match($question->question_type) {
                                    'multiple_choice' => 'Multiple Choice',
                                    'true_false' => 'True / False',
                                    'drag_drop' => 'Drag & Drop',
                                    'gesture' => 'Sign Gesture',
                                    default => 'Question'
                                };
                            @endphp
                            <div class="question-item flex items-start gap-4 lesson-q-{{ $lesson->lesson_id }}" id="qContainer_{{ $qIndex }}">
                                <div class="pt-1">
                                    <input type="checkbox"
                                        class="question-checkbox w-5 h-5 rounded border-slate-300 text-[#0d326b] focus:ring-[#1a6fd4] cursor-pointer"
                                        id="qCheck_{{ $qIndex }}"
                                        data-qindex="{{ $qIndex }}"
                                        onchange="toggleQuestion({{ $qIndex }})">
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <span class="badge-qtype {{ $typeBadgeClass }}">{{ $typeLabel }}</span>
                                        <span class="text-xs text-slate-400 font-medium">Lesson: {{ $lesson->title }}</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800 mb-2">{{ $question->question_text }}</p>

                                    <!-- Options summary preview -->
                                    @if($question->options && $question->options->count() > 0)
                                    <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                                        @foreach($question->options as $opt)
                                        <span class="px-2.5 py-1 rounded-md bg-white border border-slate-200 {{ $opt->is_correct ? 'border-green-300 bg-green-50 text-green-800 font-bold' : '' }}">
                                            {{ $opt->option_text }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>

                                <!-- Points Input -->
                                <div class="w-28 text-right flex flex-col items-end">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Points</label>
                                    <input type="number"
                                        name="questions[{{ $qIndex }}][points]"
                                        id="qPoints_{{ $qIndex }}"
                                        value="1" min="1" max="10"
                                        disabled
                                        oninput="updateSummary()"
                                        class="w-16 px-2 py-1 text-center font-bold text-sm rounded-lg border border-slate-200 focus:border-[#1a6fd4] focus:ring-1 focus:ring-[#1a6fd4] outline-none disabled:bg-slate-100 disabled:text-slate-400">
                                    <input type="hidden"
                                        name="questions[{{ $qIndex }}][source_question_id]"
                                        id="qHidden_{{ $qIndex }}"
                                        value="{{ $question->question_id }}"
                                        disabled>
                                </div>
                            </div>
                            @php $qIndex++; @endphp
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar Summary Column (1 Col) -->
        <div class="lg:col-span-1">
            <div class="summary-card p-6">
                <h3 class="text-base font-bold text-[#0d326b] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#1a6fd4]">analytics</span>
                    Exam Summary
                </h3>

                <div class="space-y-4 text-sm border-b border-slate-100 pb-5 mb-5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Selected Questions:</span>
                        <span class="font-bold text-[#0d326b] text-base" id="selectedCount">0</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Total Exam Points:</span>
                        <span class="font-bold text-green-600 text-lg" id="totalPoints">0 pts</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Required Passing Score:</span>
                        <span class="font-bold text-slate-700" id="passingScoreCalc">0 pts (60%)</span>
                    </div>
                </div>

                <button type="submit" id="submitBtn" disabled
                    class="w-full py-3 px-4 rounded-xl bg-[#0d326b] text-white font-bold text-sm shadow-md hover:bg-[#1a6fd4] transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    Create Checkpoint Exam
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function toggleQuestion(qIndex) {
    const check = document.getElementById(`qCheck_${qIndex}`);
    const pointsInput = document.getElementById(`qPoints_${qIndex}`);
    const hiddenInput = document.getElementById(`qHidden_${qIndex}`);
    const container = document.getElementById(`qContainer_${qIndex}`);

    if (check.checked) {
        pointsInput.disabled = false;
        hiddenInput.disabled = false;
        container.classList.add('selected');
    } else {
        pointsInput.disabled = true;
        hiddenInput.disabled = true;
        container.classList.remove('selected');
    }

    updateSummary();
}

function selectAllInLesson(lessonId) {
    const checkboxes = document.querySelectorAll(`.lesson-q-${lessonId} .question-checkbox`);
    let allChecked = true;
    checkboxes.forEach(cb => {
        if (!cb.checked) allChecked = false;
    });

    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
        const qIndex = cb.getAttribute('data-qindex');
        toggleQuestion(qIndex);
    });
}

function updateSummary() {
    const checkboxes = document.querySelectorAll('.question-checkbox');
    let count = 0;
    let totalPts = 0;

    checkboxes.forEach(cb => {
        if (cb.checked) {
            count++;
            const qIndex = cb.getAttribute('data-qindex');
            const pts = parseInt(document.getElementById(`qPoints_${qIndex}`).value) || 1;
            totalPts += pts;
        }
    });

    const passPercent = parseInt(document.getElementById('passingScoreInput').value) || 60;
    const passPts = Math.max(1, Math.round((passPercent / 100) * totalPts));

    document.getElementById('selectedCount').textContent = count;
    document.getElementById('totalPoints').textContent = `${totalPts} pts`;
    document.getElementById('passingScoreCalc').textContent = `${passPts} pts (${passPercent}%)`;

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = (count === 0);
}

document.getElementById('passingScoreInput')?.addEventListener('input', updateSummary);
</script>
@endsection
