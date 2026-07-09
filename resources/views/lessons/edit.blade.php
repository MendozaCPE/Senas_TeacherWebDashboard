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
                <button type="button" onclick="addQuizQuestion()" class="text-sm text-[#0d326b] font-semibold hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">add</span> Add Question
                </button>
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
                            <div class="options-container">
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
    if (!optionsList) return;
    const addOptionBtn = questionDiv.querySelector('.options-container > button');
    const qIndex = getQuizQuestionIndex(questionDiv);

    if (select.value === 'true_false') {
        const rows = Array.from(optionsList.querySelectorAll('.option-row'));
        rows.slice(2).forEach(r => r.remove());
        while (optionsList.querySelectorAll('.option-row').length < 2) {
            optionsList.appendChild(buildOptionRow(qIndex, optionsList.querySelectorAll('.option-row').length));
        }
        const textInputs = optionsList.querySelectorAll('.option-text-input');
        if (textInputs[0]) textInputs[0].value = 'True';
        if (textInputs[1]) textInputs[1].value = 'False';
        relabelOptions(optionsList, qIndex);
        optionsList.querySelectorAll('.option-image-row, .option-remove-btn').forEach(el => el.style.visibility = 'hidden');
        if (addOptionBtn) addOptionBtn.style.display = 'none';
    } else {
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
                <select name="quiz[${qIndex}][type]" onchange="handleQuestionTypeChange(this)" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="true_false">True / False</option>
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
    });
    quizIndex = document.querySelectorAll('.quiz-question').length;
}
</script>
@endsection