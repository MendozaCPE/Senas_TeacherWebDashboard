@php $layout = request()->routeIs('admin.*') ? 'layouts.admin' : 'layouts.app'; @endphp
@extends($layout)
@section('title', 'Edit Checkpoint Exam')

@section('content')

{{-- ── SKELETON ─────────────────────────────────────────────────────────── --}}
<div id="page-skeleton" class="max-w-4xl mx-auto pb-10" aria-hidden="true">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div class="flex flex-col gap-2">
            <div class="skeleton h-3 rounded w-32"></div>
            <div class="skeleton h-8 rounded w-56"></div>
            <div class="skeleton h-3 rounded w-64"></div>
        </div>
        <div class="skeleton h-10 rounded-xl w-36"></div>
    </div>
    {{-- Exam Details card --}}
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm p-6 mb-6 flex flex-col gap-4">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
            <div class="skeleton w-10 h-10 rounded-[12px]"></div>
            <div class="flex flex-col gap-1.5"><div class="skeleton h-4 rounded w-36"></div><div class="skeleton h-3 rounded w-48"></div></div>
        </div>
        <div class="skeleton h-10 rounded-[12px] w-full"></div>
        <div class="skeleton h-16 rounded-[12px] w-full"></div>
        <div class="grid grid-cols-3 gap-4">
            @for($i=0;$i<3;$i++)<div class="skeleton h-10 rounded-[12px]"></div>@endfor
        </div>
    </div>
    {{-- Questions card --}}
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm p-6 mb-6 flex flex-col gap-4">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="skeleton w-10 h-10 rounded-[12px]"></div>
                <div class="flex flex-col gap-1.5"><div class="skeleton h-4 rounded w-24"></div><div class="skeleton h-3 rounded w-40"></div></div>
            </div>
            <div class="skeleton h-9 rounded-[12px] w-32"></div>
        </div>
        @for($i=0;$i<3;$i++)
        <div class="border border-slate-200 rounded-[16px] p-5 flex flex-col gap-3">
            <div class="flex gap-3"><div class="skeleton h-10 rounded-[12px] flex-1"></div><div class="skeleton w-8 h-8 rounded-lg"></div></div>
            @for($j=0;$j<4;$j++)
            <div class="flex items-center gap-3 border border-slate-200 rounded-[12px] p-3">
                <div class="skeleton w-7 h-7 rounded-lg"></div>
                <div class="skeleton h-8 rounded-[10px] flex-1"></div>
                <div class="skeleton w-5 h-5 rounded-full"></div>
            </div>
            @endfor
        </div>
        @endfor
    </div>
    {{-- Footer --}}
    <div class="flex gap-3 justify-end">
        <div class="skeleton h-11 rounded-[12px] w-24"></div>
        <div class="skeleton h-11 rounded-[12px] w-40"></div>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){var s=document.getElementById('page-skeleton');if(s)s.style.display='none';});</script>
{{-- ── END SKELETON ─────────────────────────────────────────────────────── --}}

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
    .custom-question-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        transition: all 0.2s ease;
    }
    .custom-question-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(13, 50, 107, 0.04);
    }
    .badge-qtype {
        display: inline-flex;
        align-items: center;
        gap: 4px;
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
        @php
            $isAdmin = request()->routeIs('admin.*');
            $showRoute = $isAdmin ? route('admin.lesson-templates.checkpoint-exam.show', $exam->hash_id) : route('lessons.checkpoint-exam.show', $exam->hash_id);
        @endphp
        <a href="{{ $showRoute }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-[#0d326b] transition mb-2">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            Back to Exam Details
        </a>
        <h1 class="text-2xl font-bold text-[#0d326b]">Edit Checkpoint Exam</h1>
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

<form action="{{ $isAdmin ? route('admin.lesson-templates.checkpoint-exam.update', $exam->hash_id) : route('lessons.checkpoint-exam.update', $exam->hash_id) }}"
      method="POST"
      enctype="multipart/form-data"
      id="checkpointForm">
    @csrf
    @method('PUT')

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
                        <input type="text" name="title" value="{{ old('title', $exam->title) }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-[#1a6fd4] focus:ring-2 focus:ring-[#1a6fd4]/20 outline-none text-sm font-semibold text-slate-800 placeholder-slate-400">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Description (Optional)</label>
                        <textarea name="description" rows="2"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-[#1a6fd4] focus:ring-2 focus:ring-[#1a6fd4]/20 outline-none text-sm font-medium text-slate-800 placeholder-slate-400"
                            placeholder="Brief instructions or summary for students...">{{ old('description', $exam->description) }}</textarea>
                    </div>

                    @php
                        $calcPercentage = ($exam->total_points > 0) ? round(($exam->passing_score / $exam->total_points) * 100) : 60;
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Passing Score (%)</label>
                            <input type="number" name="passing_score_percentage" id="passingScoreInput" value="{{ old('passing_score_percentage', $calcPercentage) }}" min="1" max="100"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-[#1a6fd4] focus:ring-2 focus:ring-[#1a6fd4]/20 outline-none text-sm font-semibold text-slate-800">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Time Limit (Minutes)</label>
                            <input type="number" name="time_limit_minutes" id="timeLimitInput" value="{{ old('time_limit_minutes', $exam->time_limit_minutes ?? 60) }}" min="1" max="180"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-[#1a6fd4] focus:ring-2 focus:ring-[#1a6fd4]/20 outline-none text-sm font-semibold text-slate-800">
                            <p class="text-xs text-slate-400 mt-1">Students must complete the exam within this time limit.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question Selection Card (From Lessons) -->
            <div class="exam-card p-6">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <div>
                        <h2 class="text-base font-bold text-[#0d326b] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#1a6fd4]">quiz</span>
                            Select Questions from Lessons
                        </h2>
                        <p class="text-xs text-slate-400 font-medium">Choose quiz questions from published lessons and customize their points.</p>
                    </div>
                </div>

                @php $qIndex = 0; @endphp
                <div class="space-y-6">
                    @forelse($availableLessons as $lesson)
                    @if($lesson->quiz && $lesson->quiz->questions->count() > 0)
                    <div class="lesson-group-card p-5">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 flex-wrap gap-2">
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
                                $isPreSelected = isset($selectedLessonQuestions[$question->question_id]);
                                $prePoints = $isPreSelected ? ($selectedLessonQuestions[$question->question_id]['points'] ?? 1) : 1;

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
                            <div class="question-item flex items-start gap-4 lesson-q-{{ $lesson->lesson_id }} {{ $isPreSelected ? 'selected' : '' }}" id="qContainer_{{ $qIndex }}">
                                <div class="pt-1">
                                    <input type="checkbox"
                                        class="question-checkbox w-5 h-5 rounded border-slate-300 text-[#0d326b] focus:ring-[#1a6fd4] cursor-pointer"
                                        id="qCheck_{{ $qIndex }}"
                                        data-qindex="{{ $qIndex }}"
                                        {{ $isPreSelected ? 'checked' : '' }}
                                        onchange="toggleQuestion({{ $qIndex }})">
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <span class="badge-qtype {{ $typeBadgeClass }}">{{ $typeLabel }}</span>
                                        <span class="text-xs text-slate-400 font-medium">Lesson: {{ $lesson->title }}</span>
                                    </div>

                                    @if($question->media_url)
                                    <div class="mb-2">
                                        @php
                                            $mediaPath = $question->media_url;
                                            $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $mediaPath);
                                            $isVideo = preg_match('/\.(mp4|mov|avi|mkv|webm)$/i', $mediaPath);
                                        @endphp
                                        @if($isImage)
                                            <img src="{{ asset('storage/' . $mediaPath) }}" alt="Question media" class="max-h-32 rounded-lg border border-slate-200">
                                        @elseif($isVideo)
                                            <video src="{{ asset('storage/' . $mediaPath) }}" controls class="max-h-32 rounded-lg border border-slate-200"></video>
                                        @else
                                            <span class="text-xs text-slate-400">📎 Media attached</span>
                                        @endif
                                    </div>
                                    @endif

                                    <p class="text-sm font-semibold text-slate-800 mb-2">{{ $question->question_text }}</p>

                                    @if($question->options && $question->options->count() > 0)
                                    <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                                        @foreach($question->options as $opt)
                                        <span class="px-2.5 py-1 rounded-md bg-white border border-slate-200 flex items-center gap-1 {{ $opt->is_correct ? 'border-green-300 bg-green-50 text-green-800 font-bold' : '' }}">
                                            @if($opt->option_media_url)
                                                @php
                                                    $optMediaPath = $opt->option_media_url;
                                                    $isOptImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $optMediaPath);
                                                @endphp
                                                @if($isOptImage)
                                                    <img src="{{ asset('storage/' . $optMediaPath) }}" alt="Option" class="h-5 w-5 object-cover rounded">
                                                @else
                                                    <span class="text-slate-400">📎</span>
                                                @endif
                                            @endif
                                            <span>{{ $opt->option_text }}</span>
                                            @if($opt->is_correct)
                                                <span class="text-green-600 text-[10px]">✓</span>
                                            @endif
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>

                                <!-- Editable Points Input -->
                                <div class="w-28 text-right flex flex-col items-end flex-shrink-0">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Points</label>
                                    <input type="number"
                                        name="questions[{{ $qIndex }}][points]"
                                        id="qPoints_{{ $qIndex }}"
                                        value="{{ $prePoints }}" min="1" max="50"
                                        {{ $isPreSelected ? '' : 'disabled' }}
                                        oninput="updateSummary()"
                                        class="w-16 px-2 py-1 text-center font-bold text-sm rounded-lg border border-slate-200 focus:border-[#1a6fd4] focus:ring-1 focus:ring-[#1a6fd4] outline-none disabled:bg-slate-100 disabled:text-slate-400">
                                    <input type="hidden"
                                        name="questions[{{ $qIndex }}][source_question_id]"
                                        id="qHidden_{{ $qIndex }}"
                                        value="{{ $question->question_id }}"
                                        {{ $isPreSelected ? '' : 'disabled' }}>
                                </div>
                            </div>
                            @php $qIndex++; @endphp
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @empty
                    <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs">
                        No published lesson quizzes available in this module. You can create your own custom questions below!
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Custom Questions Card -->
            <div class="exam-card p-6">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                    <div>
                        <h2 class="text-base font-bold text-[#0d326b] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#1a6fd4]">edit_note</span>
                            Create Custom Questions
                        </h2>
                        <p class="text-xs text-slate-400 font-medium">Add your own questions to this checkpoint exam.</p>
                    </div>

                    {{-- Question Type Add Buttons --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="addCustomQuestion('multiple_choice')"
                            class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 font-bold text-xs flex items-center gap-1.5 transition shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">format_list_bulleted</span>
                            + Multiple Choice
                        </button>
                        <button type="button" onclick="addCustomQuestion('true_false')"
                            class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 font-bold text-xs flex items-center gap-1.5 transition shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                            + True / False
                        </button>
                        <button type="button" onclick="addCustomQuestion('drag_drop')"
                            class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 font-bold text-xs flex items-center gap-1.5 transition shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">drag_indicator</span>
                            + Drag & Drop
                        </button>
                        <button type="button" onclick="addCustomQuestion('gesture')"
                            class="px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 font-bold text-xs flex items-center gap-1.5 transition shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">front_hand</span>
                            + Sign Gesture
                        </button>
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="customQuestionsEmpty" class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs">
                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300 block">add_circle_outline</span>
                    No custom questions added yet. Use the buttons above to add Multiple Choice, True/False, Drag & Drop, or Sign Gesture questions.
                </div>

                {{-- Container for dynamically added questions --}}
                <div id="customQuestionsContainer" class="space-y-4"></div>
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
                        <span class="text-slate-500 font-medium">Lesson Questions:</span>
                        <span class="font-bold text-slate-700 text-sm" id="lessonQuestionsCount">0</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Custom Questions:</span>
                        <span class="font-bold text-indigo-600 text-sm" id="customQuestionsCount">0</span>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-slate-500 font-medium">Total Exam Points:</span>
                        <span class="font-bold text-green-600 text-lg" id="totalPoints">0 pts</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Required Passing Score:</span>
                        <span class="font-bold text-slate-700" id="passingScoreCalc">0 pts (60%)</span>
                    </div>
                </div>

                <button type="submit" id="submitBtn"
                    class="w-full py-3 px-4 rounded-xl bg-[#0d326b] text-white font-bold text-sm shadow-md hover:bg-[#1a6fd4] transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    Update Checkpoint Exam
                </button>
            </div>
        </div>
    </div>
</form>

<script>
const gestureCatalog = @json($gestureModules ?? []);
const existingCustomQuestions = @json($existingCustomQuestions ?? []);
let customQuestionCounter = 0;

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

function addCustomQuestion(type, existingData = null) {
    const container = document.getElementById('customQuestionsContainer');
    const emptyState = document.getElementById('customQuestionsEmpty');
    emptyState.style.display = 'none';

    const index = customQuestionCounter++;
    const card = document.createElement('div');
    card.className = 'custom-question-card space-y-4';
    card.id = `customQCard_${index}`;

    const typeConfig = {
        multiple_choice: { label: 'Multiple Choice', badgeClass: 'mc', icon: 'format_list_bulleted' },
        true_false:      { label: 'True / False',    badgeClass: 'tf', icon: 'check_circle' },
        drag_drop:       { label: 'Drag & Drop',     badgeClass: 'dd', icon: 'drag_indicator' },
        gesture:         { label: 'Sign Gesture',    badgeClass: 'gt', icon: 'front_hand' }
    }[type] || { label: 'Question', badgeClass: 'mc', icon: 'quiz' };

    const promptVal = existingData?.question_text ? escapeHtml(existingData.question_text) : '';
    const pointsVal = existingData?.points || 1;

    let typeHtml = '';

    if (type === 'multiple_choice') {
        const opts = existingData?.options_data || [
            { text: '', is_correct: true },
            { text: '', is_correct: false },
            { text: '', is_correct: false },
            { text: '', is_correct: false }
        ];

        let optsHtml = '';
        opts.forEach((opt, oIdx) => {
            const isChecked = opt.is_correct ? 'checked' : (oIdx === 0 ? 'checked' : '');
            optsHtml += `
                <div class="flex items-center gap-2">
                    <input type="radio" name="custom_questions[${index}][correct_index]" value="${oIdx}" ${isChecked}
                        class="w-4 h-4 text-[#0d326b] focus:ring-[#1a6fd4] cursor-pointer" title="Mark as correct answer">
                    <input type="text" name="custom_questions[${index}][options][]" value="${escapeHtml(opt.text || '')}" required
                        placeholder="Option ${oIdx + 1}"
                        class="flex-1 px-3 py-2 rounded-lg border border-slate-200 text-xs font-semibold text-slate-800 focus:border-[#1a6fd4] outline-none">
                </div>
            `;
        });

        typeHtml = `
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Options (Select radio for correct answer)</label>
                <div class="space-y-2">${optsHtml}</div>
            </div>
        `;
    } else if (type === 'true_false') {
        const isTrueCorrect = existingData?.correct_answer === 'True' || (!existingData?.correct_answer || existingData?.correct_answer !== 'False');
        typeHtml = `
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Correct Answer</label>
                <div class="flex items-center gap-6">
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                        <input type="radio" name="custom_questions[${index}][correct_answer]" value="True" ${isTrueCorrect ? 'checked' : ''}
                            class="w-4 h-4 text-[#0d326b] focus:ring-[#1a6fd4]">
                        True
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                        <input type="radio" name="custom_questions[${index}][correct_answer]" value="False" ${!isTrueCorrect ? 'checked' : ''}
                            class="w-4 h-4 text-[#0d326b] focus:ring-[#1a6fd4]">
                        False
                    </label>
                </div>
            </div>
        `;
    } else if (type === 'drag_drop') {
        const pairs = existingData?.drag_drop_pairs && existingData.drag_drop_pairs.length > 0
            ? existingData.drag_drop_pairs
            : [{ left_text: '', right_text: '' }, { left_text: '', right_text: '' }];

        let pairsHtml = '';
        pairs.forEach((p, pIdx) => {
            pairsHtml += createPairRowHtml(index, pIdx, p.left_text || p.left || '', p.right_text || p.right || '');
        });

        typeHtml = `
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Matching Pairs</label>
                    <button type="button" onclick="addPairToQuestion(${index})" class="text-xs font-bold text-[#1a6fd4] hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">add</span> Add Pair
                    </button>
                </div>
                <div id="pairsContainer_${index}" class="space-y-2">${pairsHtml}</div>
            </div>
        `;
    } else if (type === 'gesture') {
        const currentModId = existingData?.gesture_data?.module_id || '';
        const currentGestureName = existingData?.gesture_data?.gesture_name || existingData?.correct_answer || '';
        const currentFSWords = existingData?.gesture_data?.fingerspelling_words || existingData?.fingerspelling_words || '';

        let modOptions = '<option value="">-- Choose Gesture Module --</option>';
        gestureCatalog.forEach(gm => {
            const isSel = String(gm.module_id) === String(currentModId) ? 'selected' : '';
            modOptions += `<option value="${gm.module_id}" data-name="${escapeHtml(gm.name)}" ${isSel}>${escapeHtml(gm.display_name || gm.name)}</option>`;
        });

        typeHtml = `
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Select Target Gesture</label>
                <div class="space-y-3">
                    <div>
                        <select name="custom_questions[${index}][module_id]" id="gestureModuleSelect_${index}"
                            onchange="onGestureModuleChange(${index})" required
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:border-[#1a6fd4] outline-none">
                            ${modOptions}
                        </select>
                    </div>
                    <div id="gestureItemContainer_${index}">
                        <select name="custom_questions[${index}][gesture_name]" id="gestureItemSelect_${index}"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:border-[#1a6fd4] outline-none">
                            <option value="">-- Select Gesture --</option>
                        </select>
                    </div>
                    <div id="fingerspellingContainer_${index}" style="display:none;">
                        <label class="block text-[11px] font-bold text-indigo-600 uppercase tracking-wider mb-1">
                            <span class="material-symbols-outlined text-[14px] align-middle">spellcheck</span>
                            Fingerspelling Words (one per line, e.g. CAT)
                        </label>
                        <textarea name="custom_questions[${index}][fingerspelling_words]" id="fingerspellingWords_${index}"
                            rows="4" placeholder="CAT&#10;SUN&#10;PEN" disabled
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono text-slate-800 focus:border-[#1a6fd4] focus:ring-1 focus:ring-indigo-400 outline-none resize-y">${escapeHtml(currentFSWords)}</textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Each line is one word. Students will fingerspell each letter of the word using FSL hand signs.</p>
                        <input type="hidden" name="custom_questions[${index}][is_fingerspelling]" id="isFingerspellingInput_${index}" value="1" disabled>
                    </div>
                </div>
            </div>
        `;
    }

    const existingMediaHtml = existingData?.media_url ? `
        <div class="mb-2">
            <span class="text-[11px] text-slate-400 block mb-1">Attached Media:</span>
            <img src="/storage/${existingData.media_url}" class="max-h-24 rounded-lg border border-slate-200">
            <input type="hidden" name="custom_questions[${index}][existing_media]" value="${escapeHtml(existingData.media_url)}">
        </div>
    ` : '';

    card.innerHTML = `
        <input type="hidden" name="custom_questions[${index}][question_type]" value="${type}">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="badge-qtype ${typeConfig.badgeClass}">
                    <span class="material-symbols-outlined text-[13px]">${typeConfig.icon}</span>
                    ${typeConfig.label}
                </span>
                <span class="text-xs font-bold text-slate-500">Custom Question</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Points</label>
                    <input type="number" name="custom_questions[${index}][points]" value="${pointsVal}" min="1" max="50"
                        oninput="updateSummary()"
                        class="custom-q-points w-16 px-2 py-1 text-center font-bold text-sm rounded-lg border border-slate-200 focus:border-[#1a6fd4] outline-none">
                </div>
                <button type="button" onclick="removeCustomQuestion(${index})" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete Question">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                </button>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Question Prompt *</label>
            <textarea name="custom_questions[${index}][question_text]" rows="2" required placeholder="Type question prompt..."
                class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-800 focus:border-[#1a6fd4] focus:ring-1 focus:ring-[#1a6fd4] outline-none">${promptVal}</textarea>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Optional Media (Image / Video)</label>
            ${existingMediaHtml}
            <input type="file" name="custom_questions[${index}][media]" accept="image/*,video/*"
                class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#0d326b] hover:file:bg-blue-100 cursor-pointer">
        </div>

        ${typeHtml}
    `;

    container.appendChild(card);

    if (type === 'gesture') {
        const currentModId = existingData?.gesture_data?.module_id;
        const currentGestureName = existingData?.gesture_data?.gesture_name || existingData?.correct_answer;
        if (currentModId) {
            onGestureModuleChange(index, currentGestureName);
        }
    }

    updateSummary();
}

function removeCustomQuestion(index) {
    const card = document.getElementById(`customQCard_${index}`);
    if (card) {
        card.remove();
        const container = document.getElementById('customQuestionsContainer');
        if (container.children.length === 0) {
            document.getElementById('customQuestionsEmpty').style.display = 'block';
        }
        updateSummary();
    }
}

function createPairRowHtml(qIndex, pIndex, leftVal = '', rightVal = '') {
    return `
        <div class="flex items-center gap-2 pair-row" id="pairRow_${qIndex}_${pIndex}">
            <input type="text" name="custom_questions[qIndex][pairs][${pIndex}][left]" value="${escapeHtml(leftVal)}" required
                placeholder="Left Item (e.g. Letter A)"
                class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-800 focus:border-[#1a6fd4] outline-none">
            <span class="text-slate-400 font-bold text-xs">➡️</span>
            <input type="text" name="custom_questions[${qIndex}][pairs][${pIndex}][right]" value="${escapeHtml(rightVal)}" required
                placeholder="Matches with (e.g. Hand sign A)"
                class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-800 focus:border-[#1a6fd4] outline-none">
            <button type="button" onclick="this.closest('.pair-row').remove(); updateSummary();" class="p-1 text-slate-300 hover:text-red-500 rounded" title="Remove Pair">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>
    `.replace(/custom_questions\[qIndex\]/g, `custom_questions[${qIndex}]`);
}

function addPairToQuestion(qIndex) {
    const container = document.getElementById(`pairsContainer_${qIndex}`);
    if (!container) return;
    const pIndex = container.querySelectorAll('.pair-row').length + Date.now();
    const div = document.createElement('div');
    div.innerHTML = createPairRowHtml(qIndex, pIndex);
    container.appendChild(div.firstElementChild);
}

function onGestureModuleChange(qIndex, selectedGestureName = null) {
    const modSelect = document.getElementById(`gestureModuleSelect_${qIndex}`);
    const itemSelect = document.getElementById(`gestureItemSelect_${qIndex}`);
    const itemContainer = document.getElementById(`gestureItemContainer_${qIndex}`);
    const fsContainer = document.getElementById(`fingerspellingContainer_${qIndex}`);
    const fsHidden = document.getElementById(`isFingerspellingInput_${qIndex}`);
    const fsWords = document.getElementById(`fingerspellingWords_${qIndex}`);
    if (!modSelect) return;

    const modId = modSelect.value;
    if (!modId) {
        if (itemSelect) {
            itemSelect.disabled = false;
            itemSelect.innerHTML = '<option value="">-- Select Gesture --</option>';
        }
        if (itemContainer) itemContainer.style.display = 'block';
        if (fsContainer) fsContainer.style.display = 'none';
        if (fsHidden) fsHidden.disabled = true;
        if (fsWords) fsWords.disabled = true;
        return;
    }

    const selectedOption = modSelect.options[modSelect.selectedIndex];
    const modName = selectedOption ? selectedOption.getAttribute('data-name') : '';
    const isFingerspelling = (modName === 'fingerspelling' || String(modId) === '6');

    if (isFingerspelling) {
        // Show fingerspelling word input, hide gesture dropdown
        if (itemContainer) itemContainer.style.display = 'none';
        if (itemSelect) {
            itemSelect.removeAttribute('required');
            itemSelect.disabled = true;
            itemSelect.innerHTML = '<option value="">-- n/a fingerspelling --</option>';
        }
        if (fsContainer) fsContainer.style.display = 'block';
        if (fsHidden) fsHidden.disabled = false;
        if (fsWords) fsWords.disabled = false;
    } else {
        // Show gesture dropdown, hide fingerspelling input
        if (itemContainer) itemContainer.style.display = 'block';
        if (itemSelect) {
            itemSelect.setAttribute('required', 'required');
            itemSelect.disabled = false;
            itemSelect.innerHTML = '<option value="">-- Select Gesture --</option>';
        }
        if (fsContainer) fsContainer.style.display = 'none';
        if (fsHidden) fsHidden.disabled = true;
        if (fsWords) fsWords.disabled = true;

        const mod = gestureCatalog.find(m => String(m.module_id) === String(modId));
        if (mod && mod.gestures && itemSelect) {
            mod.gestures.forEach(g => {
                const isSel = (selectedGestureName && (g.name === selectedGestureName || g.display_name === selectedGestureName)) ? 'selected' : '';
                const opt = document.createElement('option');
                opt.value = g.name;
                opt.textContent = g.display_name || g.name;
                if (isSel) opt.selected = true;
                itemSelect.appendChild(opt);
            });
        }
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function updateSummary() {
    const checkboxes = document.querySelectorAll('.question-checkbox');
    let lessonCount = 0;
    let totalPts = 0;

    checkboxes.forEach(cb => {
        if (cb.checked) {
            lessonCount++;
            const qIndex = cb.getAttribute('data-qindex');
            const pts = parseInt(document.getElementById(`qPoints_${qIndex}`)?.value) || 1;
            totalPts += pts;
        }
    });

    const customCards = document.querySelectorAll('.custom-question-card');
    let customCount = customCards.length;

    customCards.forEach(card => {
        const ptsInput = card.querySelector('.custom-q-points');
        const pts = parseInt(ptsInput?.value) || 1;
        totalPts += pts;
    });

    const totalCount = lessonCount + customCount;
    const passPercent = parseInt(document.getElementById('passingScoreInput').value) || 60;
    const passPts = Math.max(1, Math.round((passPercent / 100) * totalPts));

    document.getElementById('selectedCount').textContent = totalCount;
    document.getElementById('lessonQuestionsCount').textContent = `${lessonCount} question(s)`;
    document.getElementById('customQuestionsCount').textContent = `${customCount} question(s)`;
    document.getElementById('totalPoints').textContent = `${totalPts} pts`;
    document.getElementById('passingScoreCalc').textContent = `${passPts} pts (${passPercent}%)`;

    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.disabled = (totalCount === 0);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Populate existing custom questions
    if (Array.isArray(existingCustomQuestions) && existingCustomQuestions.length > 0) {
        existingCustomQuestions.forEach(cq => {
            addCustomQuestion(cq.question_type, cq);
        });
    }
    updateSummary();
});

document.getElementById('passingScoreInput')?.addEventListener('input', updateSummary);
</script>
@endsection
