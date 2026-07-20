@extends('layouts.app')
@section('title', 'Edit Lesson')

@section('content')
<style>
    .hidden { display: none !important; }

    /* ── AJAX Upload Widget ──────────────────────────────────────────────── */
    .media-upload-widget {
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        padding: 16px;
        background: #f8fafc;
        transition: border-color 0.2s, background 0.2s;
        position: relative;
    }
    .media-upload-widget.has-file {
        border-color: #0d326b;
        background: #f0f4ff;
    }
    .media-upload-widget.uploading {
        border-color: #6366f1;
        background: #f5f3ff;
    }
    .media-upload-widget .upload-trigger {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }
    .media-upload-widget .upload-trigger input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }
    .media-upload-widget .upload-label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        pointer-events: none;
    }
    .media-upload-widget .upload-spinner {
        display: none;
        width: 18px;
        height: 18px;
        border: 2px solid #c7d2fe;
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        flex-shrink: 0;
    }
    .media-upload-widget.uploading .upload-spinner { display: block; }
    .media-upload-widget.uploading .upload-icon { display: none; }

    @keyframes spin { to { transform: rotate(360deg); } }

    .media-upload-widget .media-thumb-wrap {
        display: none;
        margin-top: 10px;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .media-upload-widget.has-file .media-thumb-wrap { display: flex; }
    .media-upload-widget .media-thumb {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        flex-shrink: 0;
    }
    .media-upload-widget .media-thumb-info {
        font-size: 12px;
        color: #64748b;
    }
    .media-upload-widget .media-thumb-info strong {
        display: block;
        font-size: 13px;
        color: #1e293b;
        margin-bottom: 2px;
    }
    .media-upload-widget .media-remove-btn {
        font-size: 11px;
        color: #ef4444;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        font-weight: 700;
        margin-top: 4px;
        display: inline-block;
    }
    .media-upload-error {
        font-size: 12px;
        color: #dc2626;
        margin-top: 6px;
        display: none;
    }

    /* ── Preview overlay ─────────────────────────────────────────────────── */
    #previewOverlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(15,23,42,0.7); z-index: 9999; overflow-y: auto;
        display: none; padding: 20px;
    }
    #previewOverlay.active { display: flex; align-items: flex-start; justify-content: center; }
    #previewOverlay .preview-container {
        width: auto; max-width: 900px; margin: 20px auto; background: transparent;
        border-radius: 0; overflow: visible; box-shadow: none; border: none;
        position: relative; min-height: auto;
    }
    #previewOverlay .preview-close {
        position: fixed; top: 20px; right: 20px; background: white; border: none;
        border-radius: 50%; width: 50px; height: 50px; font-size: 24px; cursor: pointer;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2); z-index: 10000; display: flex;
        align-items: center; justify-content: center; transition: all 0.3s;
    }
    #previewOverlay .preview-close:hover { transform: scale(1.1); background: #f0f0f0; }
    #previewOverlay .preview-loading {
        display: flex; align-items: center; justify-content: center;
        height: 400px; color: white; font-size: 16px; font-weight: 600;
    }
</style>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold text-[#0d326b]">Edit Lesson</h2>
            <p class="text-slate-500 text-sm mt-1">Update your lesson content and quiz questions</p>
        </div>
        <button type="button" onclick="window.location.href='{{ route('lessons.index') }}'"
                class="px-6 py-2.5 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50 transition-colors">
            Cancel
        </button>
    </div>

    <form action="{{ route('lessons.update', $lessonData['lesson_id']) }}" method="POST" id="lessonForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Lesson Details --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6">
            <h3 class="font-bold text-[#0d326b] text-lg mb-4">📝 Lesson Details</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Lesson Title *</label>
                    <input type="text" name="title" required value="{{ old('title', $lessonData['title']) }}"
                           class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">{{ old('description', $lessonData['description']) }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Difficulty</label>
                        <select name="difficulty" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                            <option value="beginner" {{ (old('difficulty', $lessonData['difficulty']) == 'beginner') ? 'selected' : '' }}>Beginner</option>
                            <option value="intermediate" {{ (old('difficulty', $lessonData['difficulty']) == 'intermediate') ? 'selected' : '' }}>Intermediate</option>
                            <option value="advanced" {{ (old('difficulty', $lessonData['difficulty']) == 'advanced') ? 'selected' : '' }}>Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Lesson Type</label>
                        <select name="lesson_type" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                            <option value="gesture" {{ (old('lesson_type', $lessonData['lesson_type']) == 'gesture') ? 'selected' : '' }}>Gesture Lesson</option>
                            <option value="interactive" {{ (old('lesson_type', $lessonData['lesson_type']) == 'interactive') ? 'selected' : '' }}>Interactive Lesson</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lesson Content --}}
        <div id="contentContainer" class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-[#0d326b] text-lg">📖 Lesson Content</h3>
                <button type="button" onclick="addContentCard()" class="text-sm text-[#0d326b] font-semibold hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">add</span> Add Slide
                </button>
            </div>
            <div id="contentCards">
                @if(isset($lessonData['contents']) && count($lessonData['contents']) > 0)
                    @foreach($lessonData['contents'] as $index => $content)
                    <div class="content-card bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative mt-4">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-slate-400 step-number">Step {{ $index + 1 }}</span>
                                <span class="text-xs bg-blue-50 text-[#0d326b] px-3 py-1 rounded-full font-semibold">{{ ucfirst($content['content_type'] ?? 'text') }}</span>
                            </div>
                            <button type="button" onclick="removeContentCard(this)" class="text-red-400 hover:text-red-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content Type</label>
                                <select name="contents[{{ $index }}][content_type]" class="content-type w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" onchange="toggleFields(this)">
                                    <option value="text" {{ $content['content_type'] == 'text' ? 'selected' : '' }}>Text</option>
                                    <option value="gesture_demo" {{ $content['content_type'] == 'gesture_demo' ? 'selected' : '' }}>Gesture Demo</option>
                                    <option value="image" {{ $content['content_type'] == 'image' ? 'selected' : '' }}>Image</option>
                                    <option value="video" {{ $content['content_type'] == 'video' ? 'selected' : '' }}>Video</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Title</label>
                                <input type="text" name="contents[{{ $index }}][title]" value="{{ $content['title'] ?? '' }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., Introduction to FSL Alphabet">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content</label>
                                <textarea name="contents[{{ $index }}][content_text]" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Write your lesson content here...">{{ $content['content_text'] ?? '' }}</textarea>
                            </div>
                            <div class="gesture-field {{ $content['content_type'] == 'gesture_demo' ? '' : 'hidden' }}">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Name</label>
                                <input type="text" name="contents[{{ $index }}][gesture_name]" value="{{ $content['gesture_name'] ?? '' }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., letter_a">
                            </div>
                            {{-- AJAX Media Upload Widget --}}
                            <div class="media-field {{ (in_array($content['content_type'], ['image', 'video']) || !empty($content['media_missing'])) ? '' : 'hidden' }}">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    {{ $content['content_type'] === 'video' ? 'Upload Video' : 'Upload Image' }}
                                </label>
                                @php
                                    $existingPath = $content['media_url'] ?? null;
                                    $isVideo = ($content['content_type'] === 'video');
                                    $accept = $isVideo ? 'video/*' : 'image/*';
                                    $context = $isVideo ? 'lesson_media' : 'lesson_media';
                                @endphp
                                {{-- Hidden field carries the stored path (existing or newly uploaded) --}}
                                <input type="hidden" name="contents[{{ $index }}][existing_media]"
                                       value="{{ $existingPath }}"
                                       class="media-path-input">
                                <div class="media-upload-widget {{ $existingPath ? 'has-file' : '' }}"
                                     data-context="{{ $context }}"
                                     data-accept="{{ $accept }}">
                                    <div class="upload-trigger">
                                        <input type="file" accept="{{ $accept }}"
                                               class="ajax-file-input"
                                               onchange="handleAjaxUpload(this, 'content')">
                                        <span class="upload-icon material-symbols-outlined text-slate-400" style="font-size:20px;">cloud_upload</span>
                                        <div class="upload-spinner"></div>
                                        <span class="upload-label">
                                            {{ $existingPath ? 'Click to replace file' : 'Click or drag to upload' }}
                                        </span>
                                    </div>
                                    <div class="media-thumb-wrap">
                                        @if($existingPath)
                                            @if(!$isVideo)
                                                <img class="media-thumb"
                                                     src="{{ asset('storage/' . $existingPath) }}"
                                                     alt="Current media"
                                                     onerror="this.style.display='none'">
                                            @else
                                                <span class="material-symbols-outlined text-slate-400" style="font-size:40px;">videocam</span>
                                            @endif
                                            <div class="media-thumb-info">
                                                <strong>Current file</strong>
                                                {{ basename($existingPath) }}
                                                <button type="button" class="media-remove-btn" onclick="clearMediaWidget(this)">✕ Remove</button>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="media-upload-error"></div>
                                </div>
                            </div>

                            {{-- ⚠ No Media Available badge --}}
                            @if(!empty($content['media_missing']))
                            <div style="background:#FEF9C3;border:1.5px solid #FDE047;border-radius:12px;padding:12px 16px;display:flex;align-items:flex-start;gap:10px;margin-top:4px;">
                                <span style="font-size:18px;flex-shrink:0;line-height:1.4;">⚠️</span>
                                <div style="flex:1;">
                                    <p style="font-size:13px;font-weight:700;color:#92400E;margin:0 0 4px;">No Media Available</p>
                                    <p style="font-size:12px;color:#A16207;margin:0;">This AI-generated slide is missing gesture media. Use the <strong>Upload Media</strong> field above to add it.</p>
                                </div>
                            </div>
                            @endif
                            <input type="hidden" name="contents[{{ $index }}][media_missing]" value="{{ !empty($content['media_missing']) ? '1' : '0' }}">
                        </div>
                    </div>
                    @endforeach
                @else
                    {{-- Default empty content card --}}
                    <div class="content-card bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative mt-4">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-slate-400 step-number">Step 1</span>
                                <span class="text-xs bg-blue-50 text-[#0d326b] px-3 py-1 rounded-full font-semibold">Text</span>
                            </div>
                            <button type="button" onclick="removeContentCard(this)" class="text-red-400 hover:text-red-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content Type</label>
                                <select name="contents[0][content_type]" class="content-type w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" onchange="toggleFields(this)">
                                    <option value="text">Text</option>
                                    <option value="gesture_demo">Gesture Demo</option>
                                    <option value="image">Image</option>
                                    <option value="video">Video</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Title</label>
                                <input type="text" name="contents[0][title]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., Introduction to FSL Alphabet">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content</label>
                                <textarea name="contents[0][content_text]" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Write your lesson content here..."></textarea>
                            </div>
                            <div class="gesture-field hidden">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Name</label>
                                <input type="text" name="contents[0][gesture_name]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., letter_a">
                            </div>
                            <div class="media-field hidden">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Upload Media</label>
                                <input type="hidden" name="contents[0][existing_media]" value="" class="media-path-input">
                                <div class="media-upload-widget" data-context="lesson_media" data-accept="image/*,video/*">
                                    <div class="upload-trigger">
                                        <input type="file" accept="image/*,video/*" class="ajax-file-input" onchange="handleAjaxUpload(this, 'content')">
                                        <span class="upload-icon material-symbols-outlined text-slate-400" style="font-size:20px;">cloud_upload</span>
                                        <div class="upload-spinner"></div>
                                        <span class="upload-label">Click or drag to upload</span>
                                    </div>
                                    <div class="media-thumb-wrap"></div>
                                    <div class="media-upload-error"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <button type="button" onclick="addContentCard()" class="w-full py-4 border-2 border-dashed border-[#0d326b]/30 rounded-2xl text-[#0d326b] font-semibold hover:bg-[#f0f4ff] transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span> Add Another Slide
            </button>
        </div>

        {{-- Quiz Questions --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-[#0d326b] text-lg">📝 Quiz Questions</h3>
                <div class="flex items-center gap-2">
                    <button type="button" id="aiQuizGenerateBtn" onclick="openAiQuizModal()"
                            title="Add lesson content first to enable AI quiz generation"
                            style="background:linear-gradient(135deg,#6d28d9,#4f46e5);color:white;padding:8px 16px;border-radius:11px;font-weight:700;font-size:12px;border:none;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all 0.2s;opacity:0.4;pointer-events:none;"
                            onmouseover="if(!this.disabled&&this.style.opacity==='1'){this.style.transform='translateY(-1px)'}"
                            onmouseout="this.style.transform=''">
                        ✨ Generate Quiz with AI
                    </button>
                    <button type="button" onclick="addQuizQuestion()" class="text-sm text-[#0d326b] font-semibold hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span> Add Question
                    </button>
                </div>
            </div>
            <div id="quizQuestions">
                @if(isset($lessonData['quiz']) && count($lessonData['quiz']) > 0)
                    @foreach($lessonData['quiz'] as $index => $q)
                    <div class="quiz-question bg-slate-50 rounded-xl p-4 mb-4 border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-slate-500 question-label">Question {{ $index + 1 }}</span>
                            <button type="button" onclick="removeQuizQuestion(this)" class="text-red-400 hover:text-red-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question</label>
                                <input type="text" name="quiz[{{ $index }}][question]" value="{{ $q['question'] ?? '' }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Enter your question">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Type</label>
                                <select name="quiz[{{ $index }}][type]" onchange="handleQuestionTypeChange(this)" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                                    <option value="multiple_choice" {{ ($q['type'] ?? 'multiple_choice') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="true_false" {{ ($q['type'] ?? '') == 'true_false' ? 'selected' : '' }}>True / False</option>
                                    <option value="drag_drop" {{ ($q['type'] ?? '') == 'drag_drop' ? 'selected' : '' }}>Drag and Drop</option>
                                    <option value="gesture" {{ ($q['type'] ?? '') == 'gesture' ? 'selected' : '' }}>Gesture Recognition</option>
                                </select>
                            </div>
                            {{-- Question media AJAX widget --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Image (Optional)</label>
                                @php $qMedia = $q['media'] ?? null; @endphp
                                <input type="hidden" name="quiz[{{ $index }}][existing_media]" value="{{ $qMedia }}" class="media-path-input">
                                <div class="media-upload-widget {{ $qMedia ? 'has-file' : '' }}" data-context="quiz_media" data-accept="image/*">
                                    <div class="upload-trigger">
                                        <input type="file" accept="image/*" class="ajax-file-input" onchange="handleAjaxUpload(this, 'quiz_media')">
                                        <span class="upload-icon material-symbols-outlined text-slate-400" style="font-size:20px;">cloud_upload</span>
                                        <div class="upload-spinner"></div>
                                        <span class="upload-label">{{ $qMedia ? 'Click to replace image' : 'Upload question image (optional)' }}</span>
                                    </div>
                                    <div class="media-thumb-wrap">
                                        @if($qMedia)
                                            <img class="media-thumb"
                                                 src="{{ asset('storage/' . $qMedia) }}"
                                                 alt="Question image"
                                                 onerror="this.style.display='none'">
                                            <div class="media-thumb-info">
                                                <strong>Current image</strong>
                                                {{ basename($qMedia) }}
                                                <button type="button" class="media-remove-btn" onclick="clearMediaWidget(this)">✕ Remove</button>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="media-upload-error"></div>
                                </div>
                            </div>
                            {{-- Options --}}
                             <div class="options-container {{ in_array($q['type'] ?? 'multiple_choice', ['multiple_choice', 'true_false']) ? '' : 'hidden' }}">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Options</label>
                                <p class="text-xs text-slate-400 mb-2">Each option can have text and/or an image.</p>
                                <div class="space-y-2 options-list">
                                    @php
                                        $options = $q['options'] ?? [['text' => ''], ['text' => '']];
                                        $correct = $q['correct'] ?? 0;
                                    @endphp
                                    @foreach($options as $optIndex => $option)
                                    @php
                                        $optText  = is_array($option) ? ($option['text'] ?? '') : $option;
                                        $optImage = is_array($option) ? ($option['image'] ?? null) : null;
                                    @endphp
                                    <div class="option-row flex items-start gap-2 bg-white border border-slate-200 rounded-xl p-3">
                                        <div class="option-letter w-7 h-7 rounded-lg bg-blue-50 text-[#0d326b] font-bold text-xs flex items-center justify-center flex-shrink-0 mt-1">{{ chr(65 + $optIndex) }}</div>
                                        <div class="option-body flex-1 space-y-2">
                                            <input type="text" name="quiz[{{ $index }}][options][{{ $optIndex }}][text]" value="{{ $optText }}" class="option-text-input w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Option {{ chr(65 + $optIndex) }} text">
                                            <div class="option-image-row flex items-center gap-2">
                                                {{-- Hidden path for existing OR newly uploaded option image --}}
                                                <input type="hidden" name="quiz[{{ $index }}][options][{{ $optIndex }}][existing_image]" value="{{ $optImage }}" class="media-path-input">
                                                {{-- Thumbnail --}}
                                                <img class="option-image-preview w-11 h-11 rounded-lg object-cover border border-slate-200 flex-shrink-0"
                                                     src="{{ $optImage ? asset('storage/' . $optImage) : '' }}"
                                                     alt=""
                                                     style="{{ $optImage ? 'display:block;' : 'display:none;' }}"
                                                     onerror="this.style.display='none'">
                                                {{-- AJAX file picker (compact, no full widget) --}}
                                                <label class="text-xs text-[#0d326b] font-semibold cursor-pointer hover:underline flex items-center gap-1 flex-shrink-0">
                                                    <span class="material-symbols-outlined" style="font-size:16px;">add_photo_alternate</span>
                                                    {{ $optImage ? 'Replace image' : 'Add image' }}
                                                    <input type="file" accept="image/*" class="option-image-input hidden" onchange="handleOptionImageUpload(this)">
                                                </label>
                                                <span class="option-upload-spinner" style="display:none;font-size:11px;color:#6366f1;">Uploading…</span>
                                                @if($optImage)
                                                <button type="button" class="text-xs text-red-400 hover:text-red-600 font-semibold" onclick="clearOptionImage(this)">✕</button>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="option-correct-row flex items-center gap-1 flex-shrink-0 mt-1">
                                            <input type="radio" name="quiz[{{ $index }}][correct]" value="{{ $optIndex }}" {{ $correct == $optIndex ? 'checked' : '' }} class="w-5 h-5 accent-[#0d326b]">
                                            <label class="text-sm text-slate-500">Correct</label>
                                        </div>
                                        <button type="button" class="option-remove-btn text-slate-300 hover:text-red-500 flex-shrink-0 mt-1" onclick="removeOption(this)">
                                            <span class="material-symbols-outlined text-sm">close</span>
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" onclick="addOption(this)" class="text-sm text-[#0d326b] font-semibold hover:underline mt-2">
                                    + Add Option
                                </button>
                            </div>
                             <div class="drag-drop-container {{ ($q['type'] ?? '') === 'drag_drop' ? '' : 'hidden' }}">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Drag and Drop Pairs</label>
                                <p class="text-xs text-slate-400 mb-2">Match items from the left column to the right column.</p>
                                <div class="space-y-2 drag-drop-pairs-list">
                                    @foreach($q['drag_drop_pairs'] ?? [] as $pairIndex => $pair)
                                    @php
                                        $leftText = $pair['left_text'] ?? $pair['left'] ?? '';
                                        $rightText = $pair['right_text'] ?? $pair['right'] ?? '';
                                        $leftImage = $pair['left_image'] ?? '';
                                        $rightImage = $pair['right_image'] ?? '';
                                    @endphp
                                    <div class="drag-drop-pair" style="display:flex;gap:12px;align-items:center;background:white;border:1.5px solid #E5EAF2;border-radius:14px;padding:12px;margin-bottom:8px;flex-wrap:wrap;">
                                        <div style="flex:1;min-width:120px;">
                                            <label style="font-size:12px;font-weight:600;color:#6B7280;display:block;margin-bottom:4px;">Left Item</label>
                                            <input type="text" name="quiz[{{ $index }}][drag_drop_pairs][{{ $pairIndex }}][left_text]" value="{{ $leftText }}" class="field-input" placeholder="e.g., Letter A" style="padding:8px 12px;font-size:13px;width:100%;">
                                            <div style="margin-top:4px;">
                                                <input type="hidden" name="quiz[{{ $index }}][drag_drop_pairs][{{ $pairIndex }}][left_image]" value="{{ $leftImage }}" class="drag-drop-image-path left-image-path">
                                                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*" style="padding:6px;border-radius:10px;">
                                                    <div class="upload-trigger" style="gap:6px;">
                                                        <input type="file" accept="image/*" class="ajax-file-input" data-side="left" onchange="handleDragDropImageUpload(this, {{ $index }}, {{ $pairIndex }})">
                                                        <span class="upload-icon material-symbols-outlined" style="font-size:16px;color:#94a3b8;">add_photo_alternate</span>
                                                        <div class="upload-spinner"></div>
                                                        <span class="upload-label" style="font-size:11px;">Add image</span>
                                                    </div>
                                                    <div class="media-thumb-wrap" style="margin-top:4px;">
                                                        @if($leftImage)
                                                        <img class="media-thumb" src="{{ asset('storage/' . $leftImage) }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1.5px solid #e2e8f0;" />
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="display:flex;align-items:center;padding:0 4px;color:#94a3b8;">
                                            <span class="material-symbols-outlined">arrow_forward</span>
                                        </div>
                                        <div style="flex:1;min-width:120px;">
                                            <label style="font-size:12px;font-weight:600;color:#6B7280;display:block;margin-bottom:4px;">Right Match</label>
                                            <input type="text" name="quiz[{{ $index }}][drag_drop_pairs][{{ $pairIndex }}][right_text]" value="{{ $rightText }}" class="field-input" placeholder="e.g., Hand sign for A" style="padding:8px 12px;font-size:13px;width:100%;">
                                            <div style="margin-top:4px;">
                                                <input type="hidden" name="quiz[{{ $index }}][drag_drop_pairs][{{ $pairIndex }}][right_image]" value="{{ $rightImage }}" class="drag-drop-image-path right-image-path">
                                                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*" style="padding:6px;border-radius:10px;">
                                                    <div class="upload-trigger" style="gap:6px;">
                                                        <input type="file" accept="image/*" class="ajax-file-input" data-side="right" onchange="handleDragDropImageUpload(this, {{ $index }}, {{ $pairIndex }})">
                                                        <span class="upload-icon material-symbols-outlined" style="font-size:16px;color:#94a3b8;">add_photo_alternate</span>
                                                        <div class="upload-spinner"></div>
                                                        <span class="upload-label" style="font-size:11px;">Add image</span>
                                                    </div>
                                                    <div class="media-thumb-wrap" style="margin-top:4px;">
                                                        @if($rightImage)
                                                        <img class="media-thumb" src="{{ asset('storage/' . $rightImage) }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1.5px solid #e2e8f0;" />
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" onclick="removeDragDropPair(this)" class="option-remove-btn" style="margin-top:16px;">
                                            <span class="material-symbols-outlined text-sm">close</span>
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" onclick="addDragDropPair(this)" class="text-sm text-[#0d326b] font-semibold hover:underline mt-2">
                                    + Add Pair
                                </button>
                            </div>
                             <div class="gesture-quiz-container {{ ($q['type'] ?? '') === 'gesture' ? '' : 'hidden' }}">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Recognition Settings</label>
                                <p class="text-xs text-slate-400 mb-2">Select a gesture module and the specific gestures students need to perform.</p>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Module</label>
                                        <select name="quiz[{{ $index }}][gesture_module_id]" class="field-select gesture-module-select w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" data-selected-ids="{{ json_encode($q['gesture_ids'] ?? []) }}" onchange="loadGesturesForModule(this, {{ $index }})">
                                            <option value="">Select a module...</option>
                                            @foreach($gestureModules as $module)
                                                <option value="{{ $module->module_id }}" {{ isset($q['gesture_module_id']) && $q['gesture_module_id'] == $module->module_id ? 'selected' : '' }}>{{ $module->display_name ?? $module->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Select Gestures to Recognize</label>
                                        <p class="text-xs text-slate-400 mb-2">Click to select gestures. Students will need to perform all selected gestures.</p>
                                        <div id="gestureCheckboxes_{{ $index }}" class="flex flex-wrap gap-2 mt-2" style="min-height:60px;">
                                            <span class="text-sm text-slate-400">Select a module first</span>
                                        </div>
                                    </div>
                                    <div class="selected-gestures-preview" style="display:none;">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Selected Gestures</label>
                                        <div class="flex flex-wrap gap-2" id="selectedGestureTags_{{ $index }}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    {{-- Default empty quiz question --}}
                    <div class="quiz-question bg-slate-50 rounded-xl p-4 mb-4 border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-slate-500 question-label">Question 1</span>
                            <button type="button" onclick="removeQuizQuestion(this)" class="text-red-400 hover:text-red-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question</label>
                                <input type="text" name="quiz[0][question]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Enter your question">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Type</label>
                                <select name="quiz[0][type]" onchange="handleQuestionTypeChange(this)" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                                     <option value="multiple_choice">Multiple Choice</option>
                                     <option value="true_false">True / False</option>
                                     <option value="drag_drop">Drag and Drop</option>
                                     <option value="gesture">Gesture Recognition</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Image (Optional)</label>
                                <input type="hidden" name="quiz[0][existing_media]" value="" class="media-path-input">
                                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*">
                                    <div class="upload-trigger">
                                        <input type="file" accept="image/*" class="ajax-file-input" onchange="handleAjaxUpload(this, 'quiz_media')">
                                        <span class="upload-icon material-symbols-outlined text-slate-400" style="font-size:20px;">cloud_upload</span>
                                        <div class="upload-spinner"></div>
                                        <span class="upload-label">Upload question image (optional)</span>
                                    </div>
                                    <div class="media-thumb-wrap"></div>
                                    <div class="media-upload-error"></div>
                                </div>
                            </div>
                            <div class="options-container">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Options</label>
                                <p class="text-xs text-slate-400 mb-2">Each option can have text and/or an image.</p>
                                <div class="space-y-2 options-list">
                                    <div class="option-row flex items-start gap-2 bg-white border border-slate-200 rounded-xl p-3">
                                        <div class="option-letter w-7 h-7 rounded-lg bg-blue-50 text-[#0d326b] font-bold text-xs flex items-center justify-center flex-shrink-0 mt-1">A</div>
                                        <div class="option-body flex-1 space-y-2">
                                            <input type="text" name="quiz[0][options][0][text]" class="option-text-input w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-[#0d326b] outline-none transition-all" placeholder="Option A text">
                                            <div class="option-image-row flex items-center gap-2">
                                                <input type="hidden" name="quiz[0][options][0][existing_image]" value="" class="media-path-input">
                                                <img class="option-image-preview w-11 h-11 rounded-lg object-cover border border-slate-200 flex-shrink-0" src="" alt="" style="display:none;">
                                                <label class="text-xs text-[#0d326b] font-semibold cursor-pointer hover:underline flex items-center gap-1 flex-shrink-0">
                                                    <span class="material-symbols-outlined" style="font-size:16px;">add_photo_alternate</span> Add image
                                                    <input type="file" accept="image/*" class="option-image-input hidden" onchange="handleOptionImageUpload(this)">
                                                </label>
                                                <span class="option-upload-spinner" style="display:none;font-size:11px;color:#6366f1;">Uploading…</span>
                                            </div>
                                        </div>
                                        <div class="option-correct-row flex items-center gap-1 flex-shrink-0 mt-1">
                                            <input type="radio" name="quiz[0][correct]" value="0" class="w-5 h-5 accent-[#0d326b]">
                                            <label class="text-sm text-slate-500">Correct</label>
                                        </div>
                                        <button type="button" class="option-remove-btn text-slate-300 hover:text-red-500 flex-shrink-0 mt-1" onclick="removeOption(this)">
                                            <span class="material-symbols-outlined text-sm">close</span>
                                        </button>
                                    </div>
                                    <div class="option-row flex items-start gap-2 bg-white border border-slate-200 rounded-xl p-3">
                                        <div class="option-letter w-7 h-7 rounded-lg bg-blue-50 text-[#0d326b] font-bold text-xs flex items-center justify-center flex-shrink-0 mt-1">B</div>
                                        <div class="option-body flex-1 space-y-2">
                                            <input type="text" name="quiz[0][options][1][text]" class="option-text-input w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-[#0d326b] outline-none transition-all" placeholder="Option B text">
                                            <div class="option-image-row flex items-center gap-2">
                                                <input type="hidden" name="quiz[0][options][1][existing_image]" value="" class="media-path-input">
                                                <img class="option-image-preview w-11 h-11 rounded-lg object-cover border border-slate-200 flex-shrink-0" src="" alt="" style="display:none;">
                                                <label class="text-xs text-[#0d326b] font-semibold cursor-pointer hover:underline flex items-center gap-1 flex-shrink-0">
                                                    <span class="material-symbols-outlined" style="font-size:16px;">add_photo_alternate</span> Add image
                                                    <input type="file" accept="image/*" class="option-image-input hidden" onchange="handleOptionImageUpload(this)">
                                                </label>
                                                <span class="option-upload-spinner" style="display:none;font-size:11px;color:#6366f1;">Uploading…</span>
                                            </div>
                                        </div>
                                        <div class="option-correct-row flex items-center gap-1 flex-shrink-0 mt-1">
                                            <input type="radio" name="quiz[0][correct]" value="1" class="w-5 h-5 accent-[#0d326b]">
                                            <label class="text-sm text-slate-500">Correct</label>
                                        </div>
                                        <button type="button" class="option-remove-btn text-slate-300 hover:text-red-500 flex-shrink-0 mt-1" onclick="removeOption(this)">
                                            <span class="material-symbols-outlined text-sm">close</span>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" onclick="addOption(this)" class="text-sm text-[#0d326b] font-semibold hover:underline mt-2">
                                    + Add Option
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <button type="button" onclick="addQuizQuestion()" class="w-full mt-4 py-4 border-2 border-dashed border-[#0d326b]/30 rounded-2xl text-[#0d326b] font-semibold hover:bg-[#f0f4ff] transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span> Add Another Question
            </button>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-between mt-8 pt-6 border-t border-slate-200">
            <button type="button" onclick="openPreview()" class="px-6 py-3 border border-[#0d326b] text-[#0d326b] rounded-xl font-semibold hover:bg-[#f0f4ff] transition-colors">
                <span class="material-symbols-outlined text-sm align-middle">visibility</span> Preview
            </button>
            <div class="flex gap-3">
                <button type="submit" name="status" value="draft" class="px-6 py-3 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 transition-colors">
                    💾 Save Draft
                </button>
                <button type="submit" name="status" value="published" class="px-8 py-3 bg-[#0d326b] text-white rounded-xl font-semibold hover:bg-[#154188] transition-colors shadow-sm">
                    🚀 Publish Lesson
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Preview Overlay --}}
<div id="previewOverlay">
    <button type="button" class="preview-close" onclick="closePreview()">✕</button>
    <div class="preview-container" id="previewContent">
        <div class="preview-loading">Loading preview...</div>
    </div>
</div>

<script>
const UPLOAD_URL  = '{{ route('lessons.upload-media') }}';
const PREVIEW_URL = '{{ route('lessons.preview') }}';
const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]')?.content
                 || document.querySelector('input[name="_token"]')?.value;

let contentIndex = {{ isset($lessonData['contents']) ? count($lessonData['contents']) : 0 }};
let quizIndex    = {{ isset($lessonData['quiz']) ? count($lessonData['quiz']) : 0 }};

/* ═══════════════════════════════════════════════════════
   AJAX UPLOAD HELPERS
═══════════════════════════════════════════════════════ */

/**
 * Upload a file via AJAX and update the widget + hidden field.
 * @param {HTMLInputElement} input  - the <input type="file">
 * @param {string}           type  - 'content' | 'quiz_media' | 'option'
 */
async function handleAjaxUpload(input, type) {
    if (!input.files || !input.files[0]) return;
    const file   = input.files[0];
    const widget = input.closest('.media-upload-widget');
    if (!widget) return;

    const context  = widget.dataset.context || 'lesson_media';
    const errorEl  = widget.querySelector('.media-upload-error');
    const spinner  = widget.querySelector('.upload-spinner');
    const label    = widget.querySelector('.upload-label');
    const thumbWrap = widget.querySelector('.media-thumb-wrap');

    // Find the hidden path input that is a sibling of the widget (or in parent)
    const pathInput = widget.closest('.media-field, div')?.querySelector('.media-path-input')
                   || widget.parentElement?.querySelector('.media-path-input');

    widget.classList.add('uploading');
    widget.classList.remove('has-file');
    if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('context', context);
    formData.append('_token', CSRF_TOKEN);

    try {
        const resp = await fetch(UPLOAD_URL, { method: 'POST', body: formData });
        const data = await resp.json();

        if (!resp.ok) throw new Error(data.message || 'Upload failed');

        // Store path in hidden field
        if (pathInput) pathInput.value = data.path;

        // Update widget appearance
        widget.classList.remove('uploading');
        widget.classList.add('has-file');
        if (label) label.textContent = 'Click to replace file';

        // Render thumb
        if (thumbWrap) {
            const isVideo = file.type.startsWith('video/');
            thumbWrap.innerHTML = '';
            if (!isVideo) {
                const img = document.createElement('img');
                img.className = 'media-thumb';
                img.src = data.url;
                img.alt = 'Uploaded media';
                thumbWrap.appendChild(img);
            } else {
                thumbWrap.innerHTML = '<span class="material-symbols-outlined text-slate-400" style="font-size:40px;">videocam</span>';
            }
            const info = document.createElement('div');
            info.className = 'media-thumb-info';
            info.innerHTML = `<strong>Uploaded</strong>${file.name}<button type="button" class="media-remove-btn" onclick="clearMediaWidget(this)">✕ Remove</button>`;
            thumbWrap.appendChild(info);
        }
    } catch (err) {
        widget.classList.remove('uploading');
        if (errorEl) { errorEl.textContent = '⚠ ' + err.message; errorEl.style.display = 'block'; }
        console.error('Upload error:', err);
    }

    // Reset the file input so the same file can be re-selected after an error
    input.value = '';
}

/**
 * Upload an option image. Updates hidden input + thumbnail inline.
 */
async function handleOptionImageUpload(input) {
    if (!input.files || !input.files[0]) return;
    const file    = input.files[0];
    const optBody = input.closest('.option-body');
    if (!optBody) return;

    const spinner   = optBody.querySelector('.option-upload-spinner');
    const preview   = optBody.querySelector('.option-image-preview');
    const pathInput = optBody.querySelector('.media-path-input');
    const label     = input.closest('label');

    if (spinner) spinner.style.display = 'inline';

    const formData = new FormData();
    formData.append('file', file);
    formData.append('context', 'quiz_option_media');
    formData.append('_token', CSRF_TOKEN);

    try {
        const resp = await fetch(UPLOAD_URL, { method: 'POST', body: formData });
        const data = await resp.json();

        if (!resp.ok) throw new Error(data.message || 'Upload failed');

        if (pathInput) pathInput.value = data.path;
        if (preview)  { preview.src = data.url; preview.style.display = 'block'; }

        // Add remove button if not there
        let clearBtn = optBody.querySelector('.option-img-clear-btn');
        if (!clearBtn) {
            clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'option-img-clear-btn text-xs text-red-400 hover:text-red-600 font-semibold flex-shrink-0';
            clearBtn.textContent = '✕';
            clearBtn.onclick = function() { clearOptionImage(this); };
            optBody.querySelector('.option-image-row').appendChild(clearBtn);
        }
        if (label) label.querySelector('span:first-child').nextSibling
            ? label.childNodes[label.childNodes.length - 2].textContent = ' Replace image'
            : null;

    } catch (err) {
        alert('Image upload failed: ' + err.message);
        console.error('Option image upload error:', err);
    } finally {
        if (spinner) spinner.style.display = 'none';
        input.value = '';
    }
}

function clearMediaWidget(btn) {
    const widget   = btn.closest('.media-upload-widget');
    if (!widget) return;
    const thumbWrap = widget.querySelector('.media-thumb-wrap');
    const pathInput = widget.closest('.media-field, div')?.querySelector('.media-path-input')
                   || widget.parentElement?.querySelector('.media-path-input');
    if (thumbWrap) thumbWrap.innerHTML = '';
    if (pathInput) pathInput.value = '';
    const label = widget.querySelector('.upload-label');
    if (label) label.textContent = 'Click or drag to upload';
    widget.classList.remove('has-file');
}

function clearOptionImage(btn) {
    const optBody = btn.closest('.option-body');
    if (!optBody) return;
    const preview   = optBody.querySelector('.option-image-preview');
    const pathInput = optBody.querySelector('.media-path-input');
    if (preview)  { preview.src = ''; preview.style.display = 'none'; }
    if (pathInput) pathInput.value = '';
    btn.remove();
}

/* ═══════════════════════════════════════════════════════
   PREVIEW
═══════════════════════════════════════════════════════ */

function openPreview() {
    const overlay = document.getElementById('previewOverlay');
    const content = document.getElementById('previewContent');
    overlay.classList.add('active');
    content.innerHTML = '<div class="preview-loading">⏳ Preparing preview...</div>';

    const form = document.getElementById('lessonForm');

    // Upload any pending (not yet saved) file inputs to temp_preview first,
    // then store the returned paths as existing_media hidden inputs.
    const pendingUploads = [];
    form.querySelectorAll('input[type="file"]').forEach(input => {
        if (input.files && input.files[0]) pendingUploads.push(input);
    });

    const uploadPromises = pendingUploads.map(input => {
        const file = input.files[0];
        const fd   = new FormData();
        fd.append('file', file);
        fd.append('context', 'temp_preview');
        fd.append('_token', CSRF_TOKEN);
        return fetch(UPLOAD_URL, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.path) {
                    const name = input.name;
                    let hiddenName;
                    const contentMatch  = name.match(/^(contents\[\d+\])\[media\]$/);
                    const quizMatch     = name.match(/^(quiz\[\d+\])\[media\]$/);
                    const optionMatch   = name.match(/^(quiz\[\d+\]\[options\]\[\d+\])\[image\]$/);
                    if (contentMatch)  hiddenName = contentMatch[1]  + '[existing_media]';
                    else if (quizMatch) hiddenName = quizMatch[1]    + '[existing_media]';
                    else if (optionMatch) hiddenName = optionMatch[1] + '[existing_image]';
                    if (hiddenName) {
                        let hidden = form.querySelector(`input[type="hidden"][name="${CSS.escape(hiddenName)}"]`);
                        if (!hidden) {
                            hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = hiddenName;
                            hidden.className = 'preview-temp-hidden';
                            input.parentElement.appendChild(hidden);
                        }
                        hidden.value = data.path;
                    }
                }
            })
            .catch(() => {});
    });

    Promise.all(uploadPromises).then(() => {
        const rawData   = new FormData(form);
        const cleanData = new FormData();
        for (const [key, value] of rawData.entries()) {
            if (value instanceof File) continue;
            if (key === '_method') continue;
            cleanData.append(key, value);
        }

        return fetch(PREVIEW_URL, {
            method: 'POST',
            body: cleanData,
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
        });
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
    .then(html => {
        content.innerHTML = html;
        content.querySelectorAll('script').forEach(old => {
            const s = document.createElement('script');
            for (const a of old.attributes) s.setAttribute(a.name, a.value);
            s.text = old.textContent;
            old.parentNode.replaceChild(s, old);
        });
        form.querySelectorAll('.preview-temp-hidden').forEach(el => el.remove());
    })
    .catch(err => {
        content.innerHTML = '<div class="preview-loading" style="color:#FCA5A5;">Preview failed. Please try again.</div>';
        console.error('Preview error:', err);
        form.querySelectorAll('.preview-temp-hidden').forEach(el => el.remove());
    });
}

function closePreview() {
    document.getElementById('previewOverlay').classList.remove('active');
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closePreview(); });

/* ═══════════════════════════════════════════════════════
   CONTENT CARDS
═══════════════════════════════════════════════════════ */

function toggleFields(select) {
    const card = select.closest('.content-card');
    if (!card) return;
    const gestureField = card.querySelector('.gesture-field');
    const mediaField   = card.querySelector('.media-field');
    const typeLabel    = card.querySelector('.text-xs.bg-blue-50');
    if (gestureField) gestureField.classList.add('hidden');
    if (mediaField)   mediaField.classList.add('hidden');
    if (typeLabel) {
        const map = { text: 'Text', gesture_demo: 'Gesture', image: 'Image', video: 'Video' };
        typeLabel.textContent = map[select.value] || 'Text';
    }
    if (select.value === 'gesture_demo') {
        if (gestureField) gestureField.classList.remove('hidden');
    } else if (select.value === 'image' || select.value === 'video') {
        if (mediaField) mediaField.classList.remove('hidden');
    }
    const mediaMissingInput = card.querySelector('input[name*="[media_missing]"]');
    if (mediaMissingInput && mediaMissingInput.value === '1') {
        if (mediaField) mediaField.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.content-type').forEach(toggleFields);
    document.querySelectorAll('.quiz-question select[name*="[type]"]').forEach(handleQuestionTypeChange);

    // Auto-load existing gesture module selections
    document.querySelectorAll('.gesture-module-select').forEach(select => {
        if (select.value) {
            const match = select.name.match(/quiz\[(\d+)\]/);
            if (match) {
                const index = match[1];
                loadGesturesForModule(select, index);
            }
        }
    });

    // Form validation on submit
    document.getElementById('lessonForm')?.addEventListener('submit', function(e) {
        const validation = validateDragDropPairs();
        if (!validation.isValid) {
            e.preventDefault();
            alert(validation.errorMsg);
            return;
        }
    });

    // Watch content changes to update AI quiz button state
    const contentCards = document.getElementById('contentCards');
    if (contentCards) {
        contentCards.addEventListener('input', updateAiQuizBtnState);
        new MutationObserver(updateAiQuizBtnState).observe(contentCards, { childList: true, subtree: true });
    }
    updateAiQuizBtnState();
});

function buildMediaWidget(index) {
    return `
        <input type="hidden" name="contents[\${index}][existing_media]" value="" class="media-path-input">
        <div class="media-upload-widget" data-context="lesson_media" data-accept="image/*,video/*">
            <div class="upload-trigger">
                <input type="file" accept="image/*,video/*" class="ajax-file-input" onchange="handleAjaxUpload(this,'content')">
                <span class="upload-icon material-symbols-outlined text-slate-400" style="font-size:20px;">cloud_upload</span>
                <div class="upload-spinner"></div>
                <span class="upload-label">Click or drag to upload</span>
            </div>
            <div class="media-thumb-wrap"></div>
            <div class="media-upload-error"></div>
        </div>`;
}

function addContentCard() {
    const container = document.getElementById('contentCards');
    const card = document.createElement('div');
    card.className = 'content-card bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative mt-4';
    card.innerHTML = `
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-slate-400 step-number">Step ${contentIndex + 1}</span>
                <span class="text-xs bg-blue-50 text-[#0d326b] px-3 py-1 rounded-full font-semibold">Text</span>
            </div>
            <button type="button" onclick="removeContentCard(this)" class="text-red-400 hover:text-red-600">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content Type</label>
                <select name="contents[${contentIndex}][content_type]" class="content-type w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" onchange="toggleFields(this)">
                    <option value="text">Text</option>
                    <option value="gesture_demo">Gesture Demo</option>
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Title</label>
                <input type="text" name="contents[${contentIndex}][title]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., Introduction to FSL Alphabet">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content</label>
                <textarea name="contents[${contentIndex}][content_text]" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Write your lesson content here..."></textarea>
            </div>
            <div class="gesture-field hidden">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Name</label>
                <input type="text" name="contents[${contentIndex}][gesture_name]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., letter_a">
            </div>
            <div class="media-field hidden">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Upload Media</label>
                <input type="hidden" name="contents[${contentIndex}][existing_media]" value="" class="media-path-input">
                <div class="media-upload-widget" data-context="lesson_media" data-accept="image/*,video/*">
                    <div class="upload-trigger">
                        <input type="file" accept="image/*,video/*" class="ajax-file-input" onchange="handleAjaxUpload(this,'content')">
                        <span class="upload-icon material-symbols-outlined text-slate-400" style="font-size:20px;">cloud_upload</span>
                        <div class="upload-spinner"></div>
                        <span class="upload-label">Click or drag to upload</span>
                    </div>
                    <div class="media-thumb-wrap"></div>
                    <div class="media-upload-error"></div>
                </div>
            </div>
        </div>`;
    container.appendChild(card);
    contentIndex++;
    toggleFields(card.querySelector('.content-type'));
}

function removeContentCard(btn) {
    const card = btn.closest('.content-card');
    if (document.querySelectorAll('.content-card').length > 1) {
        card.remove();
        updateStepNumbers();
    }
}

function updateStepNumbers() {
    document.querySelectorAll('.content-card .step-number').forEach((el, i) => {
        el.textContent = `Step ${i + 1}`;
    });
}

/* ═══════════════════════════════════════════════════════
   QUIZ
═══════════════════════════════════════════════════════ */

function handleQuestionTypeChange(select) {
    const questionDiv = select.closest('.quiz-question');
    if (!questionDiv) return;
    const optionsList = questionDiv.querySelector('.options-list');
    const addOptionBtn = questionDiv.querySelector('.options-container > button');
    const dragDropContainer = questionDiv.querySelector('.drag-drop-container');
    const gestureContainer = questionDiv.querySelector('.gesture-quiz-container');
    const qIndex = getQuizQuestionIndex(questionDiv);

    // Hide all type-specific containers
    if (dragDropContainer) dragDropContainer.classList.add('hidden');
    if (gestureContainer) gestureContainer.classList.add('hidden');
    if (optionsList) optionsList.closest('.options-container')?.classList.remove('hidden');

    if (select.value === 'true_false') {
        const rows = optionsList.querySelectorAll('.option-row');
        while (rows.length > 2) rows[rows.length - 1].remove();
        const textInputs = optionsList.querySelectorAll('.option-text-input');
        const radios = optionsList.querySelectorAll('input[type="radio"]');
        if (textInputs[0]) textInputs[0].value = 'True';
        if (textInputs[1]) textInputs[1].value = 'False';
        if (radios[0]) radios[0].value = '0';
        if (radios[1]) radios[1].value = '1';
        optionsList.querySelectorAll('.option-image-row, .option-remove-btn').forEach(el => el.style.visibility = 'hidden');
        if (addOptionBtn) addOptionBtn.style.display = 'none';
        if (optionsList) optionsList.closest('.options-container')?.classList.remove('hidden');
        
    } else if (select.value === 'drag_drop') {
        if (dragDropContainer) dragDropContainer.classList.remove('hidden');
        if (optionsList) optionsList.closest('.options-container')?.classList.add('hidden');
        
        // Auto-add first pair if none exist
        const pairsList = dragDropContainer.querySelector('.drag-drop-pairs-list');
        if (pairsList && pairsList.querySelectorAll('.drag-drop-pair').length === 0) {
            const addBtn = dragDropContainer.querySelector('button[onclick*="addDragDropPair"]');
            if (addBtn) addBtn.click();
        }
    } else if (select.value === 'gesture') {
        if (gestureContainer) gestureContainer.classList.remove('hidden');
        if (optionsList) optionsList.closest('.options-container')?.classList.add('hidden');
        
        // Auto-load gestures if module is already selected
        const moduleSelect = gestureContainer.querySelector('.gesture-module-select');
        if (moduleSelect && moduleSelect.value) {
            loadGesturesForModule(moduleSelect, getQuizQuestionIndex(questionDiv));
        }
    } else if (select.value === 'multiple_choice') {
        if (optionsList) optionsList.closest('.options-container')?.classList.remove('hidden');
        optionsList.querySelectorAll('.option-image-row, .option-remove-btn').forEach(el => el.style.visibility = 'visible');
        if (addOptionBtn) addOptionBtn.style.display = 'inline-block';
    }
}

function buildOptionRow(qIndex, optIndex) {
    const letter = String.fromCharCode(65 + optIndex);
    const row = document.createElement('div');
    row.className = 'option-row flex items-start gap-2 bg-white border border-slate-200 rounded-xl p-3';
    row.innerHTML = `
        <div class="option-letter w-7 h-7 rounded-lg bg-blue-50 text-[#0d326b] font-bold text-xs flex items-center justify-center flex-shrink-0 mt-1">${letter}</div>
        <div class="option-body flex-1 space-y-2">
            <input type="text" name="quiz[${qIndex}][options][${optIndex}][text]" class="option-text-input w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-[#0d326b] outline-none transition-all" placeholder="Option ${letter} text">
            <div class="option-image-row flex items-center gap-2">
                <input type="hidden" name="quiz[${qIndex}][options][${optIndex}][existing_image]" value="" class="media-path-input">
                <img class="option-image-preview w-11 h-11 rounded-lg object-cover border border-slate-200 flex-shrink-0" src="" alt="" style="display:none;">
                <label class="text-xs text-[#0d326b] font-semibold cursor-pointer hover:underline flex items-center gap-1 flex-shrink-0">
                    <span class="material-symbols-outlined" style="font-size:16px;">add_photo_alternate</span> Add image
                    <input type="file" accept="image/*" class="option-image-input hidden" onchange="handleOptionImageUpload(this)">
                </label>
                <span class="option-upload-spinner" style="display:none;font-size:11px;color:#6366f1;">Uploading…</span>
            </div>
        </div>
        <div class="option-correct-row flex items-center gap-1 flex-shrink-0 mt-1">
            <input type="radio" name="quiz[${qIndex}][correct]" value="${optIndex}" class="w-5 h-5 accent-[#0d326b]">
            <label class="text-sm text-slate-500">Correct</label>
        </div>
        <button type="button" class="option-remove-btn text-slate-300 hover:text-red-500 flex-shrink-0 mt-1" onclick="removeOption(this)">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>`;
    return row;
}

function getQuizQuestionIndex(questionDiv) {
    const questionInput = questionDiv.querySelector('input[name*="[question]"]');
    if (!questionInput) return 0;
    const match = questionInput.name.match(/^quiz\[(\d+)\]/);
    return match ? parseInt(match[1], 10) : 0;
}

function relabelOptions(optionsList, qIndex) {
    optionsList.querySelectorAll('.option-row').forEach((row, i) => {
        const letter = String.fromCharCode(65 + i);
        const letterEl = row.querySelector('.option-letter');
        if (letterEl) letterEl.textContent = letter;
        const textInput = row.querySelector('.option-text-input');
        if (textInput) { textInput.name = `quiz[${qIndex}][options][${i}][text]`; textInput.placeholder = `Option ${letter} text`; }
        const imgInput = row.querySelector('.option-image-input');
        if (imgInput) imgInput.name = `quiz[${qIndex}][options][${i}][image]`;
        const pathInput = row.querySelector('.media-path-input');
        if (pathInput) pathInput.name = `quiz[${qIndex}][options][${i}][existing_image]`;
        const radio = row.querySelector('input[type="radio"]');
        if (radio) { radio.name = `quiz[${qIndex}][correct]`; radio.value = i; }
    });
}

function addOption(btn) {
    const questionDiv = btn.closest('.quiz-question');
    const qIndex = getQuizQuestionIndex(questionDiv);
    const optionsList = questionDiv.querySelector('.options-list');
    const optIndex = optionsList.querySelectorAll('.option-row').length;
    optionsList.appendChild(buildOptionRow(qIndex, optIndex));
}

function removeOption(btn) {
    const questionDiv = btn.closest('.quiz-question');
    const optionsList = questionDiv.querySelector('.options-list');
    const qIndex = getQuizQuestionIndex(questionDiv);
    if (optionsList.querySelectorAll('.option-row').length > 2) {
        btn.closest('.option-row').remove();
        relabelOptions(optionsList, qIndex);
    }
}

function addQuizQuestion() {
    const container = document.getElementById('quizQuestions');
    const qIndex = quizIndex;
    const question = document.createElement('div');
    question.className = 'quiz-question bg-slate-50 rounded-xl p-4 mb-4 border border-slate-100';
    question.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-bold text-slate-500 question-label">Question ${qIndex + 1}</span>
            <button type="button" onclick="removeQuizQuestion(this)" class="text-red-400 hover:text-red-600">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question</label>
                <input type="text" name="quiz[${qIndex}][question]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Enter your question">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Type</label>
                <select name="quiz[${qIndex}][type]" onchange="handleQuestionTypeChange(this)" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all question-type">
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="true_false">True / False</option>
                    <option value="drag_drop">Drag and Drop</option>
                    <option value="gesture">Gesture Recognition</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Image (Optional)</label>
                <input type="hidden" name="quiz[${qIndex}][existing_media]" value="" class="media-path-input">
                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*">
                    <div class="upload-trigger">
                        <input type="file" accept="image/*" class="ajax-file-input" onchange="handleAjaxUpload(this,'quiz_media')">
                        <span class="upload-icon material-symbols-outlined text-slate-400" style="font-size:20px;">cloud_upload</span>
                        <div class="upload-spinner"></div>
                        <span class="upload-label">Upload question image (optional)</span>
                    </div>
                    <div class="media-thumb-wrap"></div>
                    <div class="media-upload-error"></div>
                </div>
            </div>
            <div class="options-container">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Options</label>
                <p class="text-xs text-slate-400 mb-2">Each option can have text and/or an image.</p>
                <div class="space-y-2 options-list"></div>
                <button type="button" onclick="addOption(this)" class="text-sm text-[#0d326b] font-semibold hover:underline mt-2">+ Add Option</button>
            </div>
            <!-- Drag and Drop Pairs -->
            <div class="drag-drop-container hidden">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Drag and Drop Pairs</label>
                <p class="text-xs text-slate-400 mb-2">Match items from the left column to the right column.</p>
                <div class="space-y-2 drag-drop-pairs-list"></div>
                <button type="button" onclick="addDragDropPair(this)" class="text-sm text-[#0d326b] font-semibold hover:underline mt-2">
                    + Add Pair
                </button>
            </div>
            <!-- Gesture Recognition Fields -->
            <div class="gesture-quiz-container hidden">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Recognition Settings</label>
                <p class="text-xs text-slate-400 mb-2">Select a gesture module and the specific gestures students need to perform.</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Module</label>
                        <select name="quiz[${qIndex}][gesture_module_id]" class="field-select gesture-module-select w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" onchange="loadGesturesForModule(this, ${qIndex})">
                            <option value="">Select a module...</option>
                            @foreach($gestureModules as $module)
                                <option value="{{ $module->module_id }}">{{ $module->display_name ?? $module->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Select Gestures to Recognize</label>
                        <p class="text-xs text-slate-400 mb-2">Click to select gestures. Students will need to perform all selected gestures.</p>
                        <div id="gestureCheckboxes_${qIndex}" class="flex flex-wrap gap-2 mt-2" style="min-height:60px;">
                            <span class="text-sm text-slate-400">Select a module first</span>
                        </div>
                    </div>
                    <div class="selected-gestures-preview" style="display:none;">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Selected Gestures</label>
                        <div class="flex flex-wrap gap-2" id="selectedGestureTags_${qIndex}"></div>
                    </div>
                </div>
            </div>
        </div>`;
    container.appendChild(question);
    const optionsList = question.querySelector('.options-list');
    optionsList.appendChild(buildOptionRow(qIndex, 0));
    optionsList.appendChild(buildOptionRow(qIndex, 1));
    quizIndex++;
}

function removeQuizQuestion(btn) {
    const question = btn.closest('.quiz-question');
    if (document.querySelectorAll('.quiz-question').length > 1) {
        question.remove();
        reindexQuizQuestions();
    }
}

function reindexQuizQuestions() {
    document.querySelectorAll('.quiz-question').forEach((questionDiv, qIndex) => {
        const label = questionDiv.querySelector('.question-label');
        if (label) label.textContent = `Question ${qIndex + 1}`;
        const questionInput = questionDiv.querySelector('input[name*="[question]"]');
        if (questionInput) questionInput.name = `quiz[${qIndex}][question]`;
        const typeSelect = questionDiv.querySelector('select[name*="[type]"]');
        if (typeSelect) typeSelect.name = `quiz[${qIndex}][type]`;
        const mediaPathInput = questionDiv.querySelector(':scope > div.space-y-3 > div > .media-path-input');
        if (mediaPathInput) mediaPathInput.name = `quiz[${qIndex}][existing_media]`;
        const optionsList = questionDiv.querySelector('.options-list');
        if (optionsList) relabelOptions(optionsList, qIndex);

        // Relabel drag & drop pairs
        const dragDropContainer = questionDiv.querySelector('.drag-drop-container');
        if (dragDropContainer) {
            dragDropContainer.querySelectorAll('.drag-drop-pair').forEach((pair, pairIndex) => {
                const leftText = pair.querySelector('input[name*="[left_text]"]');
                if (leftText) leftText.name = `quiz[${qIndex}][drag_drop_pairs][${pairIndex}][left_text]`;
                
                const leftImage = pair.querySelector('input[name*="[left_image]"]');
                if (leftImage) leftImage.name = `quiz[${qIndex}][drag_drop_pairs][${pairIndex}][left_image]`;
                
                const rightText = pair.querySelector('input[name*="[right_text]"]');
                if (rightText) rightText.name = `quiz[${qIndex}][drag_drop_pairs][${pairIndex}][right_text]`;
                
                const rightImage = pair.querySelector('input[name*="[right_image]"]');
                if (rightImage) rightImage.name = `quiz[${qIndex}][drag_drop_pairs][${pairIndex}][right_image]`;
                
                const leftFileInput = pair.querySelector('.ajax-file-input[data-side="left"]');
                if (leftFileInput) {
                    leftFileInput.setAttribute('onchange', `handleDragDropImageUpload(this, ${qIndex}, ${pairIndex})`);
                }
                const rightFileInput = pair.querySelector('.ajax-file-input[data-side="right"]');
                if (rightFileInput) {
                    rightFileInput.setAttribute('onchange', `handleDragDropImageUpload(this, ${qIndex}, ${pairIndex})`);
                }
            });
        }

        // Relabel gesture fields
        const gestureContainer = questionDiv.querySelector('.gesture-quiz-container');
        if (gestureContainer) {
            const gestureModuleSelect = gestureContainer.querySelector('.gesture-module-select');
            if (gestureModuleSelect) {
                gestureModuleSelect.name = `quiz[${qIndex}][gesture_module_id]`;
                gestureModuleSelect.setAttribute('onchange', `loadGesturesForModule(this, ${qIndex})`);
            }
            const checkboxesContainer = gestureContainer.querySelector('[id^="gestureCheckboxes_"]');
            if (checkboxesContainer) {
                checkboxesContainer.id = `gestureCheckboxes_${qIndex}`;
                checkboxesContainer.querySelectorAll('.gesture-checkbox').forEach(checkbox => {
                    checkbox.name = `quiz[${qIndex}][gesture_ids][]`;
                });
            }
            const tagsContainer = gestureContainer.querySelector('[id^="selectedGestureTags_"]');
            if (tagsContainer) {
                tagsContainer.id = `selectedGestureTags_${qIndex}`;
            }
        }
    });
    quizIndex = document.querySelectorAll('.quiz-question').length;
}

// Drag and Drop functions
function addDragDropPair(btn) {
    const container = btn.closest('.drag-drop-container');
    if (!container) return;
    const pairsList = container.querySelector('.drag-drop-pairs-list');
    const qIndex = getQuizQuestionIndex(container.closest('.quiz-question'));
    const pairIndex = pairsList.querySelectorAll('.drag-drop-pair').length;
    
    const pair = document.createElement('div');
    pair.className = 'drag-drop-pair';
    pair.style.cssText = 'display:flex;gap:12px;align-items:center;background:white;border:1.5px solid #E5EAF2;border-radius:14px;padding:12px;margin-bottom:8px;flex-wrap:wrap;';
    pair.innerHTML = `
        <div style="flex:1;min-width:120px;">
            <label style="font-size:12px;font-weight:600;color:#6B7280;display:block;margin-bottom:4px;">Left Item</label>
            <input type="text" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][left_text]" class="field-input" placeholder="e.g., Letter A" style="padding:8px 12px;font-size:13px;width:100%;">
            <div style="margin-top:4px;">
                <input type="hidden" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][left_image]" value="" class="drag-drop-image-path left-image-path">
                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*" style="padding:6px;border-radius:10px;">
                    <div class="upload-trigger" style="gap:6px;">
                        <input type="file" accept="image/*" class="ajax-file-input" data-side="left" onchange="handleDragDropImageUpload(this, ${qIndex}, ${pairIndex})">
                        <span class="upload-icon material-symbols-outlined" style="font-size:16px;color:#94a3b8;">add_photo_alternate</span>
                        <div class="upload-spinner"></div>
                        <span class="upload-label" style="font-size:11px;">Add image</span>
                    </div>
                    <div class="media-thumb-wrap" style="margin-top:4px;"></div>
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;padding:0 4px;color:#94a3b8;">
            <span class="material-symbols-outlined">arrow_forward</span>
        </div>
        <div style="flex:1;min-width:120px;">
            <label style="font-size:12px;font-weight:600;color:#6B7280;display:block;margin-bottom:4px;">Right Match</label>
            <input type="text" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][right_text]" class="field-input" placeholder="e.g., Hand sign for A" style="padding:8px 12px;font-size:13px;width:100%;">
            <div style="margin-top:4px;">
                <input type="hidden" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][right_image]" value="" class="drag-drop-image-path right-image-path">
                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*" style="padding:6px;border-radius:10px;">
                    <div class="upload-trigger" style="gap:6px;">
                        <input type="file" accept="image/*" class="ajax-file-input" data-side="right" onchange="handleDragDropImageUpload(this, ${qIndex}, ${pairIndex})">
                        <span class="upload-icon material-symbols-outlined" style="font-size:16px;color:#94a3b8;">add_photo_alternate</span>
                        <div class="upload-spinner"></div>
                        <span class="upload-label" style="font-size:11px;">Add image</span>
                    </div>
                    <div class="media-thumb-wrap" style="margin-top:4px;"></div>
                </div>
            </div>
        </div>
        <button type="button" onclick="removeDragDropPair(this)" class="option-remove-btn" style="margin-top:16px;">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
    `;
    pairsList.appendChild(pair);
}

async function handleDragDropImageUpload(input, qIndex, pairIndex) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const widget = input.closest('.media-upload-widget');
    if (!widget) return;
    
    const side = input.dataset.side || 'left';
    const errorEl = widget.querySelector('.media-upload-error');
    const thumbWrap = widget.querySelector('.media-thumb-wrap');
    const label = widget.querySelector('.upload-label');
    
    let pathInput;
    if (side === 'left') {
        pathInput = widget.closest('.drag-drop-pair').querySelector('.left-image-path');
    } else {
        pathInput = widget.closest('.drag-drop-pair').querySelector('.right-image-path');
    }
    
    widget.classList.add('uploading');
    widget.classList.remove('has-file');
    if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
    
    const fd = new FormData();
    fd.append('file', file);
    fd.append('context', 'quiz_media');
    fd.append('_token', CSRF_TOKEN);
    
    try {
        const resp = await fetch(UPLOAD_URL, {
            method: 'POST',
            body: fd,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.message || 'Upload failed');
        
        if (pathInput) {
            const normalizedPath = data.path.replace(/\\/g, '/');
            pathInput.value = normalizedPath;
            console.log(`✅ ${side} image saved:`, normalizedPath);
        }
        
        widget.classList.remove('uploading');
        widget.classList.add('has-file');
        if (label) label.textContent = 'Replace image';
        if (thumbWrap) {
            thumbWrap.innerHTML = '';
            const img = document.createElement('img');
            img.className = 'media-thumb';
            img.src = data.url;
            img.style.width = '40px';
            img.style.height = '40px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '6px';
            img.style.border = '1.5px solid #e2e8f0';
            thumbWrap.appendChild(img);
            
            const info = document.createElement('div');
            info.className = 'media-thumb-info';
            info.innerHTML = `<button type="button" class="media-remove-btn" onclick="clearDragDropImage(this)">✕ Remove</button>`;
            thumbWrap.appendChild(info);
        }
    } catch (err) {
        widget.classList.remove('uploading');
        if (errorEl) {
            errorEl.textContent = '⚠ ' + err.message;
            errorEl.style.display = 'block';
        }
        console.error('Upload error:', err);
    }
    input.value = '';
}

function clearDragDropImage(btn) {
    const widget = btn.closest('.media-upload-widget');
    if (!widget) return;
    const thumbWrap = widget.querySelector('.media-thumb-wrap');
    const pair = widget.closest('.drag-drop-pair');
    
    const leftPath = pair.querySelector('.left-image-path');
    const rightPath = pair.querySelector('.right-image-path');
    
    if (thumbWrap) thumbWrap.innerHTML = '';
    if (leftPath) leftPath.value = '';
    if (rightPath) rightPath.value = '';
    
    const lbl = widget.querySelector('.upload-label');
    if (lbl) lbl.textContent = 'Add image';
    widget.classList.remove('has-file');
}

function removeDragDropPair(btn) {
    const pair = btn.closest('.drag-drop-pair');
    const container = pair.closest('.drag-drop-pairs-list');
    if (container.querySelectorAll('.drag-drop-pair').length > 2) {
        pair.remove();
    }
}

// Load gestures for a module
function loadGesturesForModule(select, questionIndex) {
    const moduleId = select.value;
    const questionDiv = select.closest('.quiz-question');
    const checkboxesContainer = document.getElementById(`gestureCheckboxes_${questionIndex}`);
    const previewContainer = questionDiv.querySelector('.selected-gestures-preview');
    
    if (!moduleId) {
        checkboxesContainer.innerHTML = '<span class="text-sm text-slate-400">Select a module first</span>';
        previewContainer.style.display = 'none';
        return;
    }
    
    // Read the pre-selected IDs from data-selected-ids if present
    let selectedIds = [];
    if (select.dataset.selectedIds) {
        try {
            selectedIds = JSON.parse(select.dataset.selectedIds).map(id => Number(id));
        } catch (e) {
            console.error(e);
        }
        // Clear it so it doesn't run again if they change modules manually
        select.removeAttribute('data-selected-ids');
    }
    
    checkboxesContainer.innerHTML = '<span class="text-sm text-slate-400">Loading gestures...</span>';
    
    fetch(`/api/gesture-modules/${moduleId}/gestures`, {
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        checkboxesContainer.innerHTML = '';
        if (data.gestures && data.gestures.length > 0) {
            data.gestures.forEach(gesture => {
                const label = document.createElement('label');
                label.className = 'gesture-checkbox-label';
                label.style.cssText = `
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 8px 14px;
                    border-radius: 12px;
                    border: 2px solid #E5EAF2;
                    background: white;
                    cursor: pointer;
                    transition: all 0.2s;
                    font-size: 14px;
                    font-weight: 600;
                    color: #475569;
                    user-select: none;
                `;
                
                label.onmouseenter = function() {
                    if (!this.classList.contains('selected')) {
                        this.style.borderColor = '#0d326b';
                        this.style.background = '#f0f4ff';
                    }
                };
                label.onmouseleave = function() {
                    if (!this.classList.contains('selected')) {
                        this.style.borderColor = '#E5EAF2';
                        this.style.background = 'white';
                    }
                };
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = `quiz[${questionIndex}][gesture_ids][]`;
                checkbox.value = gesture.gesture_id;
                checkbox.className = 'gesture-checkbox';
                checkbox.style.cssText = 'display: none;';
                checkbox.dataset.displayName = gesture.display_name || gesture.name;
                
                const checkIcon = document.createElement('span');
                checkIcon.className = 'check-icon';
                checkIcon.textContent = '✓';
                checkIcon.style.cssText = `
                    display: none;
                    color: #10B981;
                    font-weight: 800;
                    font-size: 14px;
                `;
                
                checkbox.onchange = function() {
                    const currentQIndex = getQuizQuestionIndex(this.closest('.quiz-question'));
                    if (this.checked) {
                        label.classList.add('selected');
                        label.style.borderColor = '#10B981';
                        label.style.background = '#ecfdf5';
                        label.style.color = '#065F46';
                        checkIcon.style.display = 'inline';
                    } else {
                        label.classList.remove('selected');
                        label.style.borderColor = '#E5EAF2';
                        label.style.background = 'white';
                        label.style.color = '#475569';
                        checkIcon.style.display = 'none';
                    }
                    updateGesturePreview(currentQIndex);
                };
                
                const span = document.createElement('span');
                span.textContent = gesture.display_name || gesture.name;
                
                if (selectedIds.includes(Number(gesture.gesture_id))) {
                    checkbox.checked = true;
                    label.classList.add('selected');
                    label.style.borderColor = '#10B981';
                    label.style.background = '#ecfdf5';
                    label.style.color = '#065F46';
                    checkIcon.style.display = 'inline';
                }
                
                label.appendChild(checkbox);
                label.appendChild(span);
                label.appendChild(checkIcon);
                checkboxesContainer.appendChild(label);
            });
            updateGesturePreview(questionIndex);
        } else {
            checkboxesContainer.innerHTML = '<span class="text-sm text-slate-400">No gestures in this module</span>';
        }
    })
    .catch(error => {
        console.error('Error loading gestures:', error);
        checkboxesContainer.innerHTML = '<span class="text-sm text-red-500">Error loading gestures</span>';
    });
}

function updateGesturePreview(questionIndex) {
    const tagsContainer = document.getElementById(`selectedGestureTags_${questionIndex}`);
    const questionDiv = tagsContainer?.closest('.quiz-question');
    if (!questionDiv) return;
    
    const previewContainer = questionDiv.querySelector('.selected-gestures-preview');
    const checkboxes = questionDiv.querySelectorAll(`.gesture-checkbox:checked`);
    
    if (checkboxes.length === 0) {
        previewContainer.style.display = 'none';
        return;
    }
    
    previewContainer.style.display = 'block';
    tagsContainer.innerHTML = '';
    
    checkboxes.forEach(checkbox => {
        const tag = document.createElement('span');
        tag.className = 'badge-pill';
        tag.style.cssText = `
            background: rgba(16, 185, 129, 0.15);
            color: #065F46;
            padding: 6px 14px;
            font-size: 12px;
            border-radius: 99px;
            font-weight: 700;
            border: 1px solid rgba(16, 185, 129, 0.2);
        `;
        tag.textContent = checkbox.dataset.displayName || checkbox.value;
        tagsContainer.appendChild(tag);
    });
}

function validateDragDropPairs() {
    let isValid = true;
    let errorMsg = '';
    
    document.querySelectorAll('.quiz-question').forEach((questionDiv, index) => {
        const typeSelect = questionDiv.querySelector('.question-type');
        if (!typeSelect || typeSelect.value !== 'drag_drop') return;
        
        const pairsList = questionDiv.querySelector('.drag-drop-pairs-list');
        if (!pairsList) return;
        
        const pairs = pairsList.querySelectorAll('.drag-drop-pair');
        if (pairs.length < 2) {
            isValid = false;
            errorMsg = `Question ${index + 1} (Drag and Drop) needs at least 2 pairs. Currently has ${pairs.length}.`;
            questionDiv.style.borderColor = '#EF4444';
            questionDiv.style.borderWidth = '2px';
        } else {
            questionDiv.style.borderColor = '#E5EAF2';
            questionDiv.style.borderWidth = '1.5px';
        }
    });
    
    return { isValid, errorMsg };
}

// AI Quiz from Content
function getLessonContentText() {
    let text = '';
    document.querySelectorAll('[name*="[content_text]"]').forEach(el => {
        if (el.value && el.value.trim()) text += el.value.trim() + '\n\n';
    });
    document.querySelectorAll('[name*="[title]"]').forEach(el => {
        if (el.name && el.name.includes('contents[') && el.value && el.value.trim()) text += el.value.trim() + ' ';
    });
    return text.trim();
}

function updateAiQuizBtnState() {
    const btn = document.getElementById('aiQuizGenerateBtn');
    if (!btn) return;
    const hasContent = getLessonContentText().length >= 20;
    btn.style.opacity = hasContent ? '1' : '0.4';
    btn.style.pointerEvents = hasContent ? 'auto' : 'none';
    btn.title = hasContent ? 'Generate quiz questions from your lesson content using AI' : 'Add lesson content first to enable AI quiz generation';
}

function openAiQuizModal() {
    document.getElementById('aiQuizModal').style.display = 'flex';
}

function closeAiQuizModal() {
    document.getElementById('aiQuizModal').style.display = 'none';
}

async function submitAiQuizGenerate() {
    const numMc = parseInt(document.getElementById('aqm_num_mc').value) || 0;
    const numTf = parseInt(document.getElementById('aqm_num_tf').value) || 0;
    const numDd = parseInt(document.getElementById('aqm_num_dd').value) || 0;
    const numGt = parseInt(document.getElementById('aqm_num_gt').value) || 0;
    const errorEl = document.getElementById('aqm_error');
    errorEl.style.display = 'none';

    if (numMc + numTf + numDd + numGt < 1) {
        errorEl.textContent = '⚠️ Please request at least 1 question.';
        errorEl.style.display = 'block';
        return;
    }

    const contentText = getLessonContentText();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;

    const genBtn = document.getElementById('aqm_generateBtn');
    genBtn.disabled = true;
    genBtn.textContent = '✨ Generating...';
    setAqmLoading(true);

    try {
        const resp = await fetch('{{ route("lessons.ai-generate-quiz") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ content_text: contentText, num_mc: numMc, num_tf: numTf, num_dd: numDd, num_gt: numGt })
        });
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.message || 'AI generation failed.');

        finishAiProgress();

        const quizContainer = document.getElementById('quizQuestions');
        quizContainer.innerHTML = '';
        quizIndex = 0;
        data.quiz.forEach((q, idx) => {
            const qCard = buildAiQuizCard(q, idx);
            quizContainer.insertAdjacentHTML('beforeend', qCard);
            quizIndex = idx + 1;
        });

        // Auto-load gestures for AI generated gesture questions
        quizContainer.querySelectorAll('.gesture-module-select').forEach((select) => {
            if (select.value) {
                const match = select.name.match(/quiz\[(\d+)\]/);
                if (match) {
                    const index = match[1];
                    loadGesturesForModule(select, index);
                }
            }
        });

        closeAiQuizModal();

        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:28px;right:28px;background:linear-gradient(135deg,#6d28d9,#4f46e5);color:white;padding:14px 22px;border-radius:16px;font-weight:700;font-size:14px;box-shadow:0 8px 30px rgba(109,40,217,0.4);z-index:20000;transition:all 0.4s;';
        toast.textContent = `✨ ${data.quiz.length} quiz questions generated!`;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(10px)'; }, 3000);
        setTimeout(() => toast.remove(), 3500);

    } catch (err) {
        errorEl.textContent = '⚠️ ' + (err.message || 'Something went wrong.');
        errorEl.style.display = 'block';
    } finally {
        setAqmLoading(false);
        genBtn.disabled = false;
        genBtn.textContent = '✨ Generate Questions';
    }
}

function setAqmLoading(loading) {
    const form = document.getElementById('aqm_form');
    const loadingEl = document.getElementById('aqm_loading');
    if (form) form.style.display = loading ? 'none' : 'block';
    if (loadingEl) loadingEl.style.display = loading ? 'block' : 'none';
    if (loading) {
        startAiProgress(92);
    } else {
        stopAiProgress();
        updateAiProgressDisplay(0);
    }
}

// Close preview overlay on click outside
window.addEventListener('click', function(e) {
    const aiQuizModal = document.getElementById('aiQuizModal');
    if (e.target === aiQuizModal) closeAiQuizModal();
});
</script>

{{-- AI Quiz from Content Modal --}}
<div id="aiQuizModal" style="display:none;position:fixed;inset:0;z-index:10001;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;">
    <div style="background:white;border-radius:24px;padding:32px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(15,49,114,0.2);position:relative;">
        <button onclick="closeAiQuizModal()" type="button"
                style="position:absolute;top:16px;right:16px;background:rgba(15,49,114,0.07);border:none;width:32px;height:32px;border-radius:9px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;color:#64748b;">✕</button>

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
            <div style="width:42px;height:42px;background:linear-gradient(135deg,#6d28d9,#4f46e5);border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">✨</div>
            <div>
                <h3 style="font-size:17px;font-weight:800;color:#0f3172;margin:0;">Generate Quiz with AI</h3>
                <p style="font-size:12px;color:#64748b;margin:2px 0 0;">Based on your lesson content</p>
            </div>
        </div>

        <div style="background:#F5F3FF;border-radius:12px;padding:10px 14px;margin:14px 0 20px;display:flex;gap:8px;align-items:flex-start;">
            <span style="font-size:16px;flex-shrink:0;">🇵🇭</span>
            <p style="font-size:11px;color:#6d28d9;font-weight:600;margin:0;line-height:1.5;">AI will read your lesson content and create quiz questions about the FSL concepts you wrote.</p>
        </div>

        <div id="aqm_error" style="display:none;background:#FEF2F2;border:1.5px solid #FCA5A5;border-radius:12px;padding:10px 14px;margin-bottom:16px;color:#B91C1C;font-size:13px;font-weight:600;"></div>

        <div id="aqm_loading" style="display:none;text-align:center;padding:24px 8px 8px;">
            <div style="display:inline-block;width:40px;height:40px;border:4px solid rgba(109,40,217,0.2);border-top-color:#6d28d9;border-radius:50%;animation:aiSpin 0.8s linear infinite;"></div>
            <p style="color:#6d28d9;font-weight:700;font-size:14px;margin:14px 0 4px;">Generating quiz questions...</p>
            <p style="color:#94a3b8;font-size:12px;margin:0 0 16px;">AI is reading your lesson content.<br>This may take up to 30 seconds.</p>
            <div style="max-width:260px;margin:0 auto;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:12px;font-weight:600;color:#64748b;">Progress</span>
                    <span id="aqm_progressPct" style="font-size:13px;font-weight:800;color:#6d28d9;">0%</span>
                </div>
                <div style="background:#E5EAF2;border-radius:99px;height:8px;overflow:hidden;">
                    <div id="aqm_progressBar" style="background:linear-gradient(90deg,#6d28d9,#4f46e5);height:100%;width:0%;border-radius:99px;transition:width 0.4s ease;"></div>
                </div>
            </div>
        </div>

        <div id="aqm_form">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
            <div>
                <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Multiple Choice Qs</label>
                <input id="aqm_num_mc" type="number" min="0" max="15" value="2"
                       style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#6d28d9';" onblur="this.style.borderColor='#E5EAF2';">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">True / False Qs</label>
                <input id="aqm_num_tf" type="number" min="0" max="15" value="1"
                       style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#6d28d9';" onblur="this.style.borderColor='#E5EAF2';">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Drag & Drop Qs</label>
                <input id="aqm_num_dd" type="number" min="0" max="15" value="1"
                       style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#6d28d9';" onblur="this.style.borderColor='#E5EAF2';">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Gesture Qs</label>
                <input id="aqm_num_gt" type="number" min="0" max="15" value="1"
                       style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#6d28d9';" onblur="this.style.borderColor='#E5EAF2';">
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;">
            <button id="aqm_generateBtn" onclick="submitAiQuizGenerate()" type="button"
                    style="background:linear-gradient(135deg,#6d28d9,#4f46e5);color:white;padding:14px 24px;border-radius:14px;font-weight:800;font-size:15px;border:none;cursor:pointer;width:100%;transition:all 0.2s;box-shadow:0 5px 20px rgba(109,40,217,0.35);"
                    onmouseover="if(!this.disabled){this.style.transform='translateY(-1px)'}"
                    onmouseout="this.style.transform=''">
                ✨ Generate Questions
            </button>
            <button onclick="closeAiQuizModal()" type="button"
                    style="background:white;color:#64748b;padding:13px 24px;border-radius:14px;font-weight:700;font-size:14px;border:1.5px solid #E5EAF2;cursor:pointer;width:100%;transition:all 0.2s;"
                    onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='white';">Cancel</button>
        </div>
        </div>
    </div>
</div>

@include('lessons.partials.ai-generator-modal')
@endsection