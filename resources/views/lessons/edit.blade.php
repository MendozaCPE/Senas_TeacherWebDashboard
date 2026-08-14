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
        pointer-events: none; /* clicks fall through to .upload-trigger, which opens the source picker menu instead */
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
        width: 130px;
        height: 130px;
        object-fit: cover;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        flex-shrink: 0;
    }
    .media-upload-widget .media-thumb-info {
    .media-upload-widget .media-thumb-video {
        width: 220px;
        height: 138px;
        object-fit: cover;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #0f172a;
        flex-shrink: 0;
    }

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

    /* ── YouTube embed ─────────────────────────────────────────────────── */
    .youtube-embed-wrap {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%; /* 16:9 */
        margin: 10px 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(13,50,107,0.14);
        background: #0f0f0f;
    }
    .youtube-embed-wrap iframe {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        border: none;
        display: block;
    }

    /* ── Media source picker (dropdown menu + library modal) ───────────── */
    .media-picker-menu {
        position: absolute;
        z-index: 10050;
        background: white;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 12px 32px rgba(13,50,107,0.18);
        padding: 6px;
        min-width: 200px;
        display: flex;
        flex-direction: column;
    }
    .media-picker-menu button {
        display: flex;
        align-items: center;
        gap: 8px;
        text-align: left;
        padding: 9px 10px;
        border-radius: 9px;
        border: none;
        background: none;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
    }
    .media-picker-menu button .material-symbols-outlined {
        font-size: 18px;
        color: #64748b;
        flex-shrink: 0;
    }
    .media-picker-menu button:hover { background: #f1f5f9; color: #0d326b; }
    .media-picker-menu button:hover .material-symbols-outlined { color: #0d326b; }
    .media-picker-menu-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 5px 4px;
    }

    #libraryModal {
        position: fixed; inset: 0; z-index: 10055;
        background: rgba(15,23,42,0.6);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    #libraryModal.active { display: flex; }
    .library-modal-box {
        background: white;
        border-radius: 22px;
        padding: 24px;
        width: 100%;
        max-width: 640px;
        max-height: 82vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(13,50,107,0.25);
    }
    .library-tab {
        border: 1.5px solid #e2e8f0;
        background: #fafbfc;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        padding: 8px 14px;
        border-radius: 99px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .library-tab .material-symbols-outlined { font-size: 16px; }
    .library-tab.active { background: #0d326b; border-color: #0d326b; color: white; }
    .library-file-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 8px;
        cursor: pointer;
        transition: all 0.15s;
        text-align: center;
        background: #fafbfc;
    }
    .library-file-card:hover { border-color: #0d326b; background: #f0f4ff; transform: translateY(-1px); }
    .library-file-thumb {
        width: 100%;
        height: 84px;
        object-fit: cover;
        border-radius: 9px;
        background: #e2e8f0;
        display: block;
    }
    .library-file-thumb-video {
        position: relative;
        overflow: hidden;
    }
    .library-file-thumb-video video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        background: #0f172a;
        pointer-events: none;
    }
    .library-file-thumb-video .play-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 30px;
        color: white;
        text-shadow: 0 2px 6px rgba(0,0,0,0.45);
        font-variation-settings: 'FILL' 1;
    }
    .library-file-name {
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        margin-top: 6px;
        word-break: break-word;
        line-height: 1.3;
    }
    .library-loading, .library-empty {
        grid-column: 1 / -1;
        text-align: center;
        color: #94a3b8;
        font-size: 13px;
        font-weight: 600;
        padding: 30px 10px;
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

    /* ── Drag & Drop Styles ────────────────────────────────────────────── */
    .drag-drop-pair {
        display: flex;
        gap: 12px;
        align-items: center;
        background: white;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .drag-drop-pair .pair-side {
        flex: 1;
        min-width: 120px;
    }
    .drag-drop-pair .pair-arrow {
        display: flex;
        align-items: center;
        padding: 0 4px;
        color: #94a3b8;
    }
    .drag-drop-pair .pair-remove {
        margin-top: 16px;
    }
    .drag-drop-pair .pair-side label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        display: block;
        margin-bottom: 4px;
    }
    .drag-drop-pair .pair-side input[type="text"] {
        padding: 8px 12px;
        font-size: 13px;
        width: 100%;
        border: 1.5px solid #e2e8f0;
        border-radius: 11px;
        outline: none;
        transition: border-color 0.2s;
    }
    .drag-drop-pair .pair-side input[type="text"]:focus {
        border-color: #0d326b;
    }

    /* ── Gesture Selection Styles ────────────────────────────────────── */
    .gesture-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 14px;
        font-weight: 600;
        color: #475569;
        user-select: none;
    }
    .gesture-checkbox-label:hover:not(.selected) {
        border-color: #0d326b;
        background: #f0f4ff;
    }
    .gesture-checkbox-label.selected {
        border-color: #10B981;
        background: #ecfdf5;
        color: #065F46;
    }
    .gesture-checkbox-label .check-icon {
        display: none;
        color: #10B981;
        font-weight: 800;
        font-size: 14px;
    }
    .gesture-checkbox-label.selected .check-icon {
        display: inline;
    }
    .gesture-checkbox {
        display: none;
    }

    /* ── Fingerspelling Word Input ────────────────────────────────────── */
    .fingerspelling-words-textarea {
        min-height: 100px;
        font-family: 'Inter', monospace;
        font-size: 14px;
        line-height: 1.6;
    }
    .fingerspelling-words-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 10px;
        min-height: 50px;
        background: #F8FAFC;
        border-radius: 12px;
        border: 1.5px dashed #e2e8f0;
    }
    .fingerspelling-word-pill {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        background: rgba(13, 50, 107, 0.06);
        border: 1.5px solid rgba(13, 50, 107, 0.15);
        border-radius: 14px;
        padding: 8px 14px;
        min-width: 60px;
    }
    .fingerspelling-word-pill .word-letters {
        display: flex;
        gap: 4px;
        font-size: 18px;
        font-weight: 800;
        color: #0d326b;
    }
    .fingerspelling-word-pill .word-count {
        font-size: 9px;
        color: #4b7bbb;
        font-weight: 600;
        margin-top: 2px;
    }

    /* ── Validation Error Styles ──────────────────────────────────────── */
    .field-error {
        border-color: #EF4444 !important;
        background-color: #FEF2F2 !important;
    }
    .field-error:focus {
        border-color: #EF4444 !important;
        box-shadow: 0 0 0 4px rgba(239,68,68,0.08) !important;
    }
    .error-message {
        color: #EF4444;
        font-size: 12px;
        font-weight: 600;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .error-message .material-symbols-outlined {
        font-size: 16px;
    }
    .section-error {
        border-color: #EF4444 !important;
        border-width: 2px !important;
        background-color: #FEF2F2 !important;
    }

    /* ── Shared section-card / section-header system (matches create) ─── */
    .section-card {
        background: white;
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 1px 3px rgba(13,50,107,0.05), 0 4px 20px rgba(13,50,107,0.04);
        border: 1px solid rgba(13,50,107,0.07);
        margin-bottom: 24px;
    }
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
        padding-bottom: 18px;
        border-bottom: 1px solid #f1f5f9;
    }
    .section-title-group {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 16px;
        font-weight: 800;
        color: #0d326b;
    }
    .section-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .section-subtitle {
        font-size: 12px;
        color: #94a3b8;
        font-weight: 500;
        margin-top: 2px;
    }
    .field-label-sm {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .step-number-badge {
        width: 26px;
        height: 26px;
        border-radius: 8px;
        background: #0d326b;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 12px;
        flex-shrink: 0;
    }
    .content-type-pill {
        font-size: 10px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 99px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        background: rgba(13,50,107,0.08);
        color: #0d326b;
    }
    .add-link-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 7px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        color: #0d326b;
        background: rgba(13,50,107,0.05);
        border: 1px solid rgba(13,50,107,0.12);
        transition: all 0.15s;
        cursor: pointer;
    }
    .add-link-btn:hover {
        background: rgba(13,50,107,0.09);
        border-color: rgba(13,50,107,0.22);
    }
    .quiz-question-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        background: #fafbfc;
        margin-bottom: 16px;
        transition: border-color 0.2s;
    }
    .quiz-question-card:focus-within {
        border-color: rgba(13,50,107,0.2);
    }
    .dashed-add {
        width: 100%;
        padding: 13px;
        border: 2px dashed rgba(13,50,107,0.18);
        border-radius: 14px;
        color: #0d326b;
        font-weight: 700;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        background: rgba(13,50,107,0.02);
        cursor: pointer;
        margin-top: 12px;
    }
    .dashed-add:hover {
        background: rgba(13,50,107,0.05);
        border-color: rgba(13,50,107,0.32);
    }
</style>

<div class="max-w-4xl mx-auto pb-10">

    {{-- ── Page Header ─────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-2">
                <a href="{{ route('lessons.index') }}" class="hover:text-[#0d326b] transition-colors">Lessons</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-[#0d326b] truncate max-w-[260px]">{{ $lessonData['title'] }}</span>
            </div>
            <h2 class="text-[28px] font-black text-[#0d326b] tracking-tight leading-none">Edit Lesson</h2>
            <p class="text-slate-400 text-sm mt-1.5 font-medium">Update content, quiz questions and settings</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Status badge --}}
            @php $st = $lessonData['status'] ?? 'draft'; @endphp
            <span class="px-3.5 py-1.5 rounded-full text-[11px] font-bold uppercase tracking-wider
                {{ $st === 'published' ? 'bg-[#0d326b] text-white' : 'bg-amber-100 text-amber-700' }}">
                {{ $st === 'published' ? '✓ Published' : '✎ Draft' }}
            </span>
            <a href="{{ route('lessons.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl font-semibold text-[13px] text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all">
                <span class="material-symbols-outlined text-[16px]">close</span>
                Cancel
            </a>
        </div>
    </div>

    <form action="{{ route('lessons.update', $lessonData['hash_id']) }}" method="POST" id="lessonForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ── Lesson Details ──────────────────────────────────────── --}}
        <div class="section-card">
            <div class="section-header">
                <div class="section-title-group">
                    <div class="section-icon-box" style="background:rgba(13,50,107,0.08);">
                        <span class="material-symbols-outlined text-[20px] text-[#0d326b]">edit_note</span>
                    </div>
                    <div>
                        Lesson Details
                        <div class="section-subtitle">Title, description, type and difficulty</div>
                    </div>
                </div>
            </div>
            <div class="space-y-5">
                <div>
                    <label class="field-label-sm">Lesson Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required value="{{ old('title', $lessonData['title']) }}"
                           class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]"
                           placeholder="Lesson title">
                </div>
                <div>
                    <label class="field-label-sm">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]"
                              placeholder="Brief overview of what students will learn...">{{ old('description', $lessonData['description']) }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label-sm">Difficulty</label>
                        <select name="difficulty" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]">
                            <option value="beginner"     {{ old('difficulty', $lessonData['difficulty']) == 'beginner'     ? 'selected' : '' }}>🌱 Beginner</option>
                            <option value="intermediate" {{ old('difficulty', $lessonData['difficulty']) == 'intermediate' ? 'selected' : '' }}>⚡ Intermediate</option>
                            <option value="advanced"     {{ old('difficulty', $lessonData['difficulty']) == 'advanced'     ? 'selected' : '' }}>🔥 Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label-sm">Lesson Type</label>
                        <select name="lesson_type" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]">
                            <option value="gesture"     {{ old('lesson_type', $lessonData['lesson_type']) == 'gesture'     ? 'selected' : '' }}>👋 Gesture Lesson</option>
                            <option value="interactive" {{ old('lesson_type', $lessonData['lesson_type']) == 'interactive' ? 'selected' : '' }}>🎯 Interactive Lesson</option>
                            <option value="video"       {{ old('lesson_type', $lessonData['lesson_type']) == 'video'       ? 'selected' : '' }}>🎥 Video Lesson</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Lesson Content ──────────────────────────────────────── --}}
        <div id="contentContainer" class="section-card">
            <div class="section-header">
                <div class="section-title-group">
                    <div class="section-icon-box" style="background:rgba(5,150,105,0.1);">
                        <span class="material-symbols-outlined text-[20px] text-emerald-600">menu_book</span>
                    </div>
                    <div>
                        Lesson Content
                        <div class="section-subtitle">Slides — text, gestures, images, videos, or YouTube</div>
                    </div>
                </div>
                <button type="button" onclick="addContentCard()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[12px] font-bold text-[#0d326b] bg-[#f0f4ff] hover:bg-[#e0e8ff] border border-[#c7d2fe] transition-all">
                    <span class="material-symbols-outlined text-[15px]">add</span> Add Slide
                </button>
            </div>
            <div id="contentCards" class="space-y-4">
                @if(isset($lessonData['contents']) && count($lessonData['contents']) > 0)
                    @foreach($lessonData['contents'] as $index => $content)
                    <div class="content-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="step-number-badge step-number">{{ $index + 1 }}</div>
                                <span class="content-type-pill">{{ ucfirst(str_replace('_', ' ', $content['content_type'] ?? 'text')) }}</span>
                            </div>
                            <button type="button" onclick="removeContentCard(this)"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all">
                                <span class="material-symbols-outlined text-[16px]">close</span>
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
                                    <option value="youtube_video" {{ $content['content_type'] == 'youtube_video' ? 'selected' : '' }}>YouTube Video</option>
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
                            {{-- YouTube Video URL field --}}
                            @php
                                $isYoutubeSlide = ($content['content_type'] ?? '') === 'youtube_video';
                                $existingYtUrl  = $isYoutubeSlide ? ($content['media_url'] ?? '') : '';
                                // Build embed URL for existing youtube slides to show a preview
                                $existingYtEmbed = null;
                                if ($existingYtUrl) {
                                    $ytIdEdit = \App\Models\LessonContent::extractYoutubeId($existingYtUrl);
                                    if ($ytIdEdit) {
                                        $existingYtEmbed = 'https://www.youtube.com/embed/' . $ytIdEdit . '?rel=0&modestbranding=1';
                                    }
                                }
                            @endphp
                            <div class="youtube-field {{ $isYoutubeSlide ? '' : 'hidden' }}">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">YouTube Video URL</label>
                                <input type="text"
                                       name="contents[{{ $index }}][youtube_url]"
                                       value="{{ $existingYtUrl }}"
                                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all youtube-url-input"
                                       placeholder="https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID"
                                       autocomplete="off">
                                <div class="youtube-url-error" style="display:none; color:#EF4444; font-size:12px; font-weight:600; margin-top:4px;"></div>
                                <div class="youtube-preview-wrap" style="{{ $existingYtEmbed ? '' : 'display:none;' }} margin-top:12px; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(13,50,107,0.12);">
                                    <iframe class="youtube-preview-iframe"
                                            src="{{ $existingYtEmbed ?? '' }}"
                                            width="100%" height="250" frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen
                                            style="display:block; border-radius:14px;"></iframe>
                                </div>
                            </div>
                            {{-- AJAX Media Upload Widget --}}
                            <div class="media-field {{ (in_array($content['content_type'], ['image', 'video', 'gesture_demo']) || !empty($content['media_missing'])) ? '' : 'hidden' }}">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Media</label>
                                @php
                                    $existingPath = $content['media_url'] ?? null;
                                    // Detect video by the actual file extension rather than content_type —
                                    // a "Gesture Demo" slide can point at either an image or a video file.
                                    $isVideo = $existingPath
                                        ? in_array(strtolower(pathinfo($existingPath, PATHINFO_EXTENSION)), ['mp4','mov','avi','mkv','webm'])
                                        : ($content['content_type'] === 'video');
                                    $accept = 'image/*,video/*';
                                @endphp
                                <input type="hidden" name="contents[{{ $index }}][existing_media]"
                                       value="{{ $existingPath }}"
                                       class="media-path-input">
                                <div class="media-upload-widget {{ $existingPath ? 'has-file' : '' }}"
                                     data-context="lesson_media"
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
                                                <video class="media-thumb media-thumb-video"
                                                       src="{{ asset('storage/' . $existingPath) }}"
                                                       controls muted playsinline preload="metadata"></video>
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
                    <div class="content-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="step-number-badge step-number">1</div>
                                <span class="content-type-pill">Text</span>
                            </div>
                            <button type="button" onclick="removeContentCard(this)"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all">
                                <span class="material-symbols-outlined text-[16px]">close</span>
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
                                    <option value="youtube_video">YouTube Video</option>
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
                            <div class="youtube-field hidden">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">YouTube Video URL</label>
                                <input type="text" name="contents[0][youtube_url]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all youtube-url-input" placeholder="https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID" autocomplete="off">
                                <div class="youtube-url-error" style="display:none; color:#EF4444; font-size:12px; font-weight:600; margin-top:4px;"></div>
                                <div class="youtube-preview-wrap" style="display:none; margin-top:12px; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(13,50,107,0.12);">
                                    <iframe class="youtube-preview-iframe" width="100%" height="250" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="display:block; border-radius:14px;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>{{-- /#contentCards --}}
            <button type="button" onclick="addContentCard()" class="dashed-add">
                <span class="material-symbols-outlined text-[16px]">add</span> Add Another Slide
            </button>
        </div>{{-- /.section-card #contentContainer --}}

        {{-- ── Quiz Questions ───────────────────────────────────────── --}}
        <div class="section-card">
            <div class="section-header">
                <div class="section-title-group">
                    <div class="section-icon-box" style="background:rgba(245,158,11,0.1);">
                        <span class="material-symbols-outlined text-[20px] text-amber-600">quiz</span>
                    </div>
                    <div>
                        Quiz Questions
                        <div class="section-subtitle">Multiple choice, true/false, drag &amp; drop, or gesture</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="aiQuizGenerateBtn" onclick="openAiQuizModal()"
                            title="Add lesson content first to enable AI quiz generation"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[12px] font-bold text-white transition-all"
                            style="background:linear-gradient(135deg,#0d326b,#1a6fd4);opacity:0.4;pointer-events:none;"
                            onmouseover="if(this.style.opacity==='1')this.style.opacity='0.9'"
                            onmouseout="if(this.style.opacity!=='0.4')this.style.opacity='1'">
                        <span class="material-symbols-outlined text-[15px]">auto_awesome</span> Generate Quiz with AI
                    </button>
                    <button type="button" onclick="addQuizQuestion()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[12px] font-bold text-[#0d326b] bg-[#f0f4ff] hover:bg-[#e0e8ff] border border-[#c7d2fe] transition-all">
                        <span class="material-symbols-outlined text-[15px]">add</span> Add Question
                    </button>
                </div>
            </div>
            <div id="quizQuestions">
                @if(isset($lessonData['quiz']) && count($lessonData['quiz']) > 0)
                    @foreach($lessonData['quiz'] as $index => $q)
                    <div class="quiz-question quiz-question-card" data-question-index="{{ $index }}">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="step-number-badge" style="background:#0d326b;">{{ $index + 1 }}</div>
                                <span class="text-sm font-bold text-slate-600 question-label">Question {{ $index + 1 }}</span>
                            </div>
                            <button type="button" onclick="removeQuizQuestion(this)"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="field-label-sm">Question</label>
                                <input type="text" name="quiz[{{ $index }}][question]" value="{{ $q['question'] ?? '' }}"
                                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]"
                                       placeholder="Enter your question">
                            </div>
                            <div>
                                <label class="field-label-sm">Question Type</label>
                                <select name="quiz[{{ $index }}][type]" onchange="handleQuestionTypeChange(this)"
                                        class="question-type w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]">
                                    <option value="multiple_choice" {{ ($q['type'] ?? 'multiple_choice') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="true_false"     {{ ($q['type'] ?? '') == 'true_false'     ? 'selected' : '' }}>True / False</option>
                                    <option value="drag_drop"      {{ ($q['type'] ?? '') == 'drag_drop'      ? 'selected' : '' }}>Drag and Drop</option>
                                    <option value="gesture"        {{ ($q['type'] ?? '') == 'gesture'        ? 'selected' : '' }}>Gesture Recognition</option>
                                </select>
                            </div>
                            {{-- Question media AJAX widget --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Image (Optional)</label>
                                @php $qMedia = $q['media'] ?? null; @endphp
                                <input type="hidden" name="quiz[{{ $index }}][existing_media]" value="{{ $qMedia }}" class="media-path-input">
                                <div class="media-upload-widget {{ $qMedia ? 'has-file' : '' }}" data-context="quiz_media" data-accept="image/*,video/*">
                                    <div class="upload-trigger">
                                        <input type="file" accept="image/*,video/*" class="ajax-file-input" onchange="handleAjaxUpload(this, 'quiz_media')">
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
                                                <input type="hidden" name="quiz[{{ $index }}][options][{{ $optIndex }}][existing_image]" value="{{ $optImage }}" class="media-path-input">
                                                <img class="option-image-preview w-16 h-16 rounded-lg object-cover border border-slate-200 flex-shrink-0"
                                                     src="{{ $optImage ? asset('storage/' . $optImage) : '' }}"
                                                     alt=""
                                                     style="{{ $optImage ? 'display:block;' : 'display:none;' }}"
                                                     onerror="this.style.display='none'">
                                                <label class="text-xs text-[#0d326b] font-semibold cursor-pointer hover:underline flex items-center gap-1 flex-shrink-0">
                                                    <span class="material-symbols-outlined" style="font-size:16px;">add_photo_alternate</span>
                                                    {{ $optImage ? 'Replace image' : 'Add image' }}
                                                    <input type="file" accept="image/*,video/*" class="option-image-input hidden" onchange="handleOptionImageUpload(this)">
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

                            {{-- DRAG AND DROP PAIRS --}}
                            <div class="drag-drop-container {{ ($q['type'] ?? '') === 'drag_drop' ? '' : 'hidden' }}">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Drag and Drop Pairs</label>
                                <p class="text-xs text-slate-400 mb-2">Match items from the left column to the right column.</p>
                                <div class="space-y-2 drag-drop-pairs-list">
                                    @php
                                        $pairs = $q['drag_drop_pairs'] ?? [];
                                        if (is_string($pairs)) {
                                            $pairs = json_decode($pairs, true) ?: [];
                                        }
                                        if (!is_array($pairs)) {
                                            $pairs = [];
                                        }
                                    @endphp
                                    @if(count($pairs) > 0)
                                        @foreach($pairs as $pairIndex => $pair)
                                        @php
                                            $leftText = is_array($pair) ? ($pair['left_text'] ?? $pair['left'] ?? '') : '';
                                            $rightText = is_array($pair) ? ($pair['right_text'] ?? $pair['right'] ?? '') : '';
                                            $leftImage = is_array($pair) ? ($pair['left_image'] ?? '') : '';
                                            $rightImage = is_array($pair) ? ($pair['right_image'] ?? '') : '';
                                        @endphp
                                        <div class="drag-drop-pair" data-pair-index="{{ $pairIndex }}">
                                            <div class="pair-side">
                                                <label>Left Item</label>
                                                <input type="text" name="quiz[{{ $index }}][drag_drop_pairs][{{ $pairIndex }}][left_text]" value="{{ $leftText }}" placeholder="e.g., Letter A">
                                                <div style="margin-top:4px;">
                                                    <input type="hidden" name="quiz[{{ $index }}][drag_drop_pairs][{{ $pairIndex }}][left_image]" value="{{ $leftImage }}" class="drag-drop-image-path left-image-path">
                                                    <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*,video/*" style="padding:6px;border-radius:10px;">
                                                        <div class="upload-trigger" style="gap:6px;">
                                                            <input type="file" accept="image/*,video/*" class="ajax-file-input" data-side="left" onchange="handleDragDropImageUpload(this, {{ $index }}, {{ $pairIndex }})">
                                                            <span class="upload-icon material-symbols-outlined" style="font-size:16px;color:#94a3b8;">add_photo_alternate</span>
                                                            <div class="upload-spinner"></div>
                                                            <span class="upload-label" style="font-size:11px;">{{ $leftImage ? 'Replace image' : 'Add image' }}</span>
                                                        </div>
                                                        <div class="media-thumb-wrap" style="margin-top:4px;">
                                                            @if($leftImage)
                                                            <img class="media-thumb" src="{{ asset('storage/' . $leftImage) }}" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;" />
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pair-arrow">
                                                <span class="material-symbols-outlined">arrow_forward</span>
                                            </div>
                                            <div class="pair-side">
                                                <label>Right Match</label>
                                                <input type="text" name="quiz[{{ $index }}][drag_drop_pairs][{{ $pairIndex }}][right_text]" value="{{ $rightText }}" placeholder="e.g., Hand sign for A">
                                                <div style="margin-top:4px;">
                                                    <input type="hidden" name="quiz[{{ $index }}][drag_drop_pairs][{{ $pairIndex }}][right_image]" value="{{ $rightImage }}" class="drag-drop-image-path right-image-path">
                                                    <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*,video/*" style="padding:6px;border-radius:10px;">
                                                        <div class="upload-trigger" style="gap:6px;">
                                                            <input type="file" accept="image/*,video/*" class="ajax-file-input" data-side="right" onchange="handleDragDropImageUpload(this, {{ $index }}, {{ $pairIndex }})">
                                                            <span class="upload-icon material-symbols-outlined" style="font-size:16px;color:#94a3b8;">add_photo_alternate</span>
                                                            <div class="upload-spinner"></div>
                                                            <span class="upload-label" style="font-size:11px;">{{ $rightImage ? 'Replace image' : 'Add image' }}</span>
                                                        </div>
                                                        <div class="media-thumb-wrap" style="margin-top:4px;">
                                                            @if($rightImage)
                                                            <img class="media-thumb" src="{{ asset('storage/' . $rightImage) }}" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;" />
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" onclick="removeDragDropPair(this)" class="pair-remove option-remove-btn">
                                                <span class="material-symbols-outlined text-sm">close</span>
                                            </button>
                                        </div>
                                        @endforeach
                                    @else
                                        {{-- No pairs yet, show empty state --}}
                                        <div class="text-sm text-slate-400 py-2">No pairs added yet. Click "Add Pair" below.</div>
                                    @endif
                                </div>
                                <button type="button" onclick="addDragDropPair(this)" class="text-sm text-[#0d326b] font-semibold hover:underline mt-2">
                                    + Add Pair
                                </button>
                            </div>

                            {{-- GESTURE RECOGNITION --}}
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

                                    <div class="fingerspelling-word-container {{ (($q['gesture_module_id'] ?? null) == 6) ? '' : 'hidden' }}" style="margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">📝 Fingerspelling Words</label>
                                        <p class="text-xs text-slate-400 mb-2">Enter words one per line. Students will fingerspell each word.</p>
                                        <div class="space-y-3">
                                            <div>
                                                <textarea
                                                    name="quiz[{{ $index }}][fingerspelling_words]"
                                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all fingerspelling-words-textarea"
                                                    placeholder="Enter one word per line&#10;Example:&#10;HELLO&#10;NICE&#10;SENYAS"
                                                    rows="4"
                                                    oninput="updateFingerspellingWordPreview(this)"
                                                >{{ $q['fingerspelling_words'] ?? '' }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Preview</label>
                                                <div class="fingerspelling-words-preview" id="fingerspellingPreview_{{ $index }}">
                                                    <span class="text-sm text-slate-400">Enter words to see preview</span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="quiz[{{ $index }}][fingerspelling_letter_ids]" class="fingerspelling-letter-ids" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    {{-- Default empty quiz question --}}
                    <div class="quiz-question quiz-question-card">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="step-number-badge">1</div>
                                <span class="text-sm font-bold text-slate-600 question-label">Question 1</span>
                            </div>
                            <button type="button" onclick="removeQuizQuestion(this)"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                        </div>
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
                                <select name="quiz[0][type]" onchange="handleQuestionTypeChange(this)" class="question-type w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                                     <option value="multiple_choice">Multiple Choice</option>
                                     <option value="true_false">True / False</option>
                                     <option value="drag_drop">Drag and Drop</option>
                                     <option value="gesture">Gesture Recognition</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Image (Optional)</label>
                                <input type="hidden" name="quiz[0][existing_media]" value="" class="media-path-input">
                                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*,video/*">
                                    <div class="upload-trigger">
                                        <input type="file" accept="image/*,video/*" class="ajax-file-input" onchange="handleAjaxUpload(this, 'quiz_media')">
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
                                                <img class="option-image-preview w-16 h-16 rounded-lg object-cover border border-slate-200 flex-shrink-0" src="" alt="" style="display:none;">
                                                <label class="text-xs text-[#0d326b] font-semibold cursor-pointer hover:underline flex items-center gap-1 flex-shrink-0">
                                                    <span class="material-symbols-outlined" style="font-size:16px;">add_photo_alternate</span> Add image
                                                    <input type="file" accept="image/*,video/*" class="option-image-input hidden" onchange="handleOptionImageUpload(this)">
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
                                                <img class="option-image-preview w-16 h-16 rounded-lg object-cover border border-slate-200 flex-shrink-0" src="" alt="" style="display:none;">
                                                <label class="text-xs text-[#0d326b] font-semibold cursor-pointer hover:underline flex items-center gap-1 flex-shrink-0">
                                                    <span class="material-symbols-outlined" style="font-size:16px;">add_photo_alternate</span> Add image
                                                    <input type="file" accept="image/*,video/*" class="option-image-input hidden" onchange="handleOptionImageUpload(this)">
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
                            {{-- Drag and Drop Container (hidden by default) --}}
                            <div class="drag-drop-container hidden">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Drag and Drop Pairs</label>
                                <p class="text-xs text-slate-400 mb-2">Match items from the left column to the right column.</p>
                                <div class="space-y-2 drag-drop-pairs-list"></div>
                                <button type="button" onclick="addDragDropPair(this)" class="text-sm text-[#0d326b] font-semibold hover:underline mt-2">
                                    + Add Pair
                                </button>
                            </div>
                            {{-- Gesture Recognition Container (hidden by default) --}}
                            <div class="gesture-quiz-container hidden">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Recognition Settings</label>
                                <p class="text-xs text-slate-400 mb-2">Select a gesture module and the specific gestures students need to perform.</p>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Module</label>
                                        <select name="quiz[0][gesture_module_id]" class="field-select gesture-module-select w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" onchange="loadGesturesForModule(this, 0)">
                                            <option value="">Select a module...</option>
                                            @foreach($gestureModules as $module)
                                                <option value="{{ $module->module_id }}">{{ $module->display_name ?? $module->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Select Gestures to Recognize</label>
                                        <p class="text-xs text-slate-400 mb-2">Click to select gestures. Students will need to perform all selected gestures.</p>
                                        <div id="gestureCheckboxes_0" class="flex flex-wrap gap-2 mt-2" style="min-height:60px;">
                                            <span class="text-sm text-slate-400">Select a module first</span>
                                        </div>
                                    </div>
                                    <div class="selected-gestures-preview" style="display:none;">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Selected Gestures</label>
                                        <div class="flex flex-wrap gap-2" id="selectedGestureTags_0"></div>
                                    </div>

                                    <div class="fingerspelling-word-container hidden" style="margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">📝 Fingerspelling Words</label>
                                        <p class="text-xs text-slate-400 mb-2">Enter words one per line. Students will fingerspell each word.</p>
                                        <div class="space-y-3">
                                            <div>
                                                <textarea
                                                    name="quiz[0][fingerspelling_words]"
                                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all fingerspelling-words-textarea"
                                                    placeholder="Enter one word per line&#10;Example:&#10;HELLO&#10;NICE&#10;SENYAS"
                                                    rows="4"
                                                    oninput="updateFingerspellingWordPreview(this)"
                                                ></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Preview</label>
                                                <div class="fingerspelling-words-preview" id="fingerspellingPreview_0">
                                                    <span class="text-sm text-slate-400">Enter words to see preview</span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="quiz[0][fingerspelling_letter_ids]" class="fingerspelling-letter-ids" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>{{-- /#quizQuestions --}}
            <button type="button" onclick="addQuizQuestion()" class="dashed-add">
                <span class="material-symbols-outlined text-[16px]">add</span> Add Another Question
            </button>
        </div>{{-- /.section-card quiz --}}

        {{-- ── Sticky Form Footer ──────────────────────────────────── --}}
        <div class="sticky bottom-0 bg-white border-t border-slate-100 rounded-2xl shadow-[0_-4px_24px_rgba(13,50,107,0.07)] px-7 py-4 flex items-center justify-between mt-0">
            <button type="button" onclick="openPreview()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-[13px] text-[#0d326b] border-2 border-[#0d326b] bg-white hover:bg-[#f0f4ff] transition-all">
                <span class="material-symbols-outlined text-[16px]">visibility</span> Preview
            </button>
            <div class="flex gap-3">
                <button type="submit" name="status" value="draft"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-[13px] text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined text-[16px]">save</span> Save Draft
                </button>
                <button type="submit" name="status" value="published"
                        class="inline-flex items-center gap-2 px-7 py-2.5 rounded-xl font-bold text-[13px] text-white shadow-md transition-all hover:opacity-90"
                        style="background:linear-gradient(135deg,#0d326b,#1e4b8f,#1a6fd4);">
                    <span class="material-symbols-outlined text-[16px]">rocket_launch</span> Update &amp; Publish
                </button>
            </div>
        </div>
    </form>
</div>{{-- /.max-w-4xl --}}

{{-- Preview Overlay --}}
<div id="previewOverlay">
    <button type="button" class="preview-close" onclick="closePreview()">✕</button>
    <div class="preview-container" id="previewContent">
        <div class="preview-loading">Loading preview...</div>
    </div>
</div>

{{-- Sign Language Media Library modal --}}
<div id="libraryModal">
    <div class="library-modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div style="display:flex;align-items:center;gap:10px;font-weight:800;font-size:15px;color:#0d326b;">
                <div style="width:32px;height:32px;border-radius:11px;background:rgba(13,50,107,0.08);color:#0d326b;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:18px;">video_library</span>
                </div>
                <span id="libraryModalTitle">Sign Language Library</span>
            </div>
            <button type="button" onclick="closeLibraryModal()" style="background:rgba(13,50,107,0.07);border:none;width:30px;height:30px;border-radius:9px;cursor:pointer;color:#64748b;flex-shrink:0;">✕</button>
        </div>
        <div id="libraryFoldersBar" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;"></div>
        <div id="libraryFilesGrid" style="flex:1;overflow-y:auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:12px;padding-right:4px;"></div>
    </div>
</div>

<script>
const UPLOAD_URL  = '{{ route('lessons.upload-media') }}';
const PREVIEW_URL = '{{ route('lessons.preview') }}';
const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]')?.content
                 || document.querySelector('input[name="_token"]')?.value;

const MAX_UPLOAD_BYTES = 50 * 1024 * 1024; // 50MB (images + video)
const MAX_UPLOAD_LABEL = '4MB';
function tooLargeMessage(file) {
    return `"${file.name}" is too large (${(file.size / (1024*1024)).toFixed(1)}MB). Max size is ${MAX_UPLOAD_LABEL}.`;
}

let contentIndex = {{ isset($lessonData['contents']) ? count($lessonData['contents']) : 0 }};
let quizIndex    = {{ isset($lessonData['quiz']) ? count($lessonData['quiz']) : 0 }};

/* ═══════════════════════════════════════════════════════
   AJAX UPLOAD HELPERS
═══════════════════════════════════════════════════════ */

/**
 * Upload a file via AJAX and update the widget + hidden field.
 */

/* ═══════════════════════════════════════════════════════
   MEDIA THUMBNAIL RENDERING (images + videos)
═══════════════════════════════════════════════════════ */
function isVideoUrl(url) {
    if (!url) return false;
    return /\.(mp4|mov|avi|mkv|webm|m4v|ogv)(\?|#|$)/i.test(url);
}

// Renders a real preview for the selected file: <img> for images,
// an inline playable <video> for videos (never a broken/gray box).
function renderMediaThumb(thumbWrap, url, isVideo, altText) {
    if (!thumbWrap) return null;
    if (isVideo === undefined || isVideo === null) isVideo = isVideoUrl(url);
    if (isVideo) {
        const vid = document.createElement('video');
        vid.className = 'media-thumb media-thumb-video';
        vid.src = url;
        vid.controls = true;
        vid.muted = true;
        vid.playsInline = true;
        vid.preload = 'metadata';
        thumbWrap.appendChild(vid);
        return vid;
    }
    const img = document.createElement('img');
    img.className = 'media-thumb';
    img.src = url;
    img.alt = altText || 'Selected media';
    thumbWrap.appendChild(img);
            upgradeVideoThumbs(thumbWrap);
    return img;
}


// Small inline preview (quiz option / drag-drop thumbs) that swaps between
// <img> and <video> depending on the file type, so mp4s show a real player
// instead of a broken-image icon.
function setSmallMediaPreview(el, url) {
    if (!el) return el;
    const wantVideo = isVideoUrl(url);
    const isVid = el.tagName === 'VIDEO';
    if (url && wantVideo !== isVid) {
        const n = document.createElement(wantVideo ? 'video' : 'img');
        n.className = el.className;
        const st = el.getAttribute('style');
        if (st) n.setAttribute('style', st);
        if (wantVideo) { n.muted = true; n.playsInline = true; n.controls = true; n.preload = 'metadata'; }
        el.replaceWith(n);
        el = n;
    }
    if (!url) { el.removeAttribute('src'); el.style.display = 'none'; }
    else { el.src = url; el.style.display = 'block'; }
    return el;
}

// Upgrades any <img> whose src is actually a video (server-rendered markup or
// thumbs built before the type was known) into an inline <video> player.
function upgradeVideoThumbs(root) {
    (root || document).querySelectorAll('img[src]').forEach(function (el) {
        const src = el.getAttribute('src');
        if (src && isVideoUrl(src)) setSmallMediaPreview(el, src);
    });
}
document.addEventListener('DOMContentLoaded', function () { upgradeVideoThumbs(document); });

// Gesture Demo: auto-fill the Gesture Name from the selected media file name
// (still editable — once the user types their own value we stop overwriting it).
function fileBaseName(name) {
    if (!name) return '';
    return String(name).split('/').pop().replace(/\.[^.]+$/, '');
}
function autoFillGestureName(widget, fileName) {
    if (!widget) return;
    const card = widget.closest('.content-card');
    if (!card) return;
    const input = card.querySelector('input[name*="[gesture_name]"]');
    if (!input) return;
    const base = fileBaseName(fileName);
    if (!base) return;
    if (input.value.trim() !== '' && input.dataset.autofilled !== '1') return;
    input.value = base;
    input.dataset.autofilled = '1';
}
document.addEventListener('input', function(e) {
    const t = e.target;
    if (t && t.name && t.name.indexOf('[gesture_name]') !== -1) t.dataset.autofilled = '0';
});

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

    const pathInput = widget.closest('.media-field, div')?.querySelector('.media-path-input')
                   || widget.parentElement?.querySelector('.media-path-input');

    if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
    if (file.size > MAX_UPLOAD_BYTES) {
        if (errorEl) { errorEl.textContent = '⚠ ' + tooLargeMessage(file); errorEl.style.display = 'block'; }
        input.value = '';
        return;
    }

    widget.classList.add('uploading');
    widget.classList.remove('has-file');

    const formData = new FormData();
    formData.append('file', file);
    formData.append('context', context);
    formData.append('_token', CSRF_TOKEN);

    try {
        const resp = await fetch(UPLOAD_URL, { method: 'POST', body: formData });
        const data = await resp.json();

        if (!resp.ok) throw new Error(data.message || 'Upload failed');

        if (pathInput) pathInput.value = data.path;

        widget.classList.remove('uploading');
        widget.classList.add('has-file');
        if (label) label.textContent = 'Click to replace file';

        if (thumbWrap) {
            const isVideo = file.type.startsWith('video/');
            thumbWrap.innerHTML = '';
            if (!isVideo) {
                const img = document.createElement('img');
                img.className = 'media-thumb';
                img.src = data.url;
                img.alt = 'Uploaded media';
                thumbWrap.appendChild(img);
            upgradeVideoThumbs(thumbWrap);
            } else {
                renderMediaThumb(thumbWrap, data.url, true);
            }
            const info = document.createElement('div');
            info.className = 'media-thumb-info';
            info.innerHTML = `<strong>Uploaded</strong>${file.name}<button type="button" class="media-remove-btn" onclick="clearMediaWidget(this)">✕ Remove</button>`;
            thumbWrap.appendChild(info);
            autoFillGestureName(widget, file.name);
        }
    } catch (err) {
        widget.classList.remove('uploading');
        if (errorEl) { errorEl.textContent = '⚠ ' + err.message; errorEl.style.display = 'block'; }
        console.error('Upload error:', err);
    }

    input.value = '';
}

/**
 * Upload an option image.
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

    if (file.size > MAX_UPLOAD_BYTES) {
        alert(tooLargeMessage(file));
        input.value = '';
        return;
    }
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
        setSmallMediaPreview(preview, data.url);

        let clearBtn = optBody.querySelector('.option-img-clear-btn');
        if (!clearBtn) {
            clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'option-img-clear-btn text-xs text-red-400 hover:text-red-600 font-semibold flex-shrink-0';
            clearBtn.textContent = '✕';
            clearBtn.onclick = function() { clearOptionImage(this); };
            optBody.querySelector('.option-image-row').appendChild(clearBtn);
        }
        if (label) {
            const textNode = label.childNodes[label.childNodes.length - 2];
            if (textNode && textNode.nodeType === 3) {
                textNode.textContent = ' Replace image';
            }
        }

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
    setSmallMediaPreview(preview, '');
    if (pathInput) pathInput.value = '';
    btn.remove();
}

/* ═══════════════════════════════════════════════════════
   DRAG & DROP IMAGE UPLOAD
═══════════════════════════════════════════════════════ */

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

    if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
    if (file.size > MAX_UPLOAD_BYTES) {
        if (errorEl) { errorEl.textContent = '⚠ ' + tooLargeMessage(file); errorEl.style.display = 'block'; }
        input.value = '';
        return;
    }

    widget.classList.add('uploading');
    widget.classList.remove('has-file');

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
            upgradeVideoThumbs(thumbWrap);

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

/* ═══════════════════════════════════════════════════════
   MEDIA SOURCE PICKER (Sign Language Library or Upload from Device)
   Works for every media slot on the page (lesson content, quiz question
   image, answer option image, drag-and-drop pair image) via event
   delegation — no need to wire up new cards/questions/options individually.
═══════════════════════════════════════════════════════ */

const LIBRARY_FOLDERS_URL = '{{ route('lessons.media-library.folders') }}';
const LIBRARY_FILES_BASE_URL = '{{ url('/lessons/media-library') }}';
const MY_UPLOADS_URL = '{{ route('lessons.media-library.my-uploads') }}';

// Icon + fallback label for each library folder. The backend is the source of
// truth for which folders exist and their file counts; this just supplies the
// icon (and a label to show before that first fetch resolves).
const LIBRARY_FOLDER_META = {
    Alphabets:  { icon: 'sort_by_alpha', label: 'Alphabets' },
    Numbers:    { icon: 'tag',           label: 'Numbers' },
    Greetings:  { icon: 'waving_hand',   label: 'Greetings' },
    Survival:   { icon: 'sos',           label: 'Survival / Conversation' },
    my_uploads: { icon: 'cloud_upload',  label: 'My Uploads' },
};

let mediaPickerTarget = null;
let libraryFoldersCache = null;

// Figures out which media slot a click came from and what "kind" of slot it is,
// so applySelectedMedia() knows how to wire the chosen file back into the form.
function buildPickerTarget(triggerEl) {
    const widget = triggerEl.closest('.media-upload-widget');
    if (widget) {
        const fileInput = widget.querySelector('.ajax-file-input');
        if (widget.closest('.drag-drop-pair')) {
            return { kind: 'dragdrop', el: widget, side: fileInput?.dataset.side || 'left', fileInput };
        }
        return { kind: 'widget', el: widget, fileInput };
    }
    const optBody = triggerEl.closest('.option-body');
    if (optBody) {
        return { kind: 'option', el: optBody, fileInput: optBody.querySelector('.option-image-input') };
    }
    return null;
}

// Intercepts clicks on any upload trigger / "Add image" label across the whole
// form (current and future cards alike) and opens the source-picker menu instead
// of letting the browser jump straight to the native file dialog.
document.addEventListener('click', function(e) {
    // A programmatic .click() on the hidden <input type="file"> also bubbles up
    // here; swallowing it with preventDefault() was blocking the native file
    // dialog ("Upload from Device" appeared to do nothing).
    if (e.target && e.target.tagName === 'INPUT' && e.target.type === 'file') return;
    const trigger = e.target.closest('.upload-trigger');
    if (trigger && trigger.closest('.media-upload-widget')) {
        e.preventDefault();
        openMediaPicker(trigger);
        return;
    }
    const optLabel = e.target.closest('.option-image-row label');
    if (optLabel) {
        e.preventDefault();
        openMediaPicker(optLabel);
    }
});

function openMediaPicker(triggerEl) {
    mediaPickerTarget = buildPickerTarget(triggerEl);
    if (!mediaPickerTarget) return;
    closeMediaPickerMenu();

    const menu = document.createElement('div');
    menu.className = 'media-picker-menu';
    menu.innerHTML = `
        <button type="button" data-folder="Alphabets"><span class="material-symbols-outlined">sort_by_alpha</span> Alphabets</button>
        <button type="button" data-folder="Numbers"><span class="material-symbols-outlined">tag</span> Numbers</button>
        <button type="button" data-folder="Greetings"><span class="material-symbols-outlined">waving_hand</span> Greetings</button>
        <button type="button" data-folder="Survival"><span class="material-symbols-outlined">sos</span> Survival / Conversation</button>
        <button type="button" data-folder="my_uploads"><span class="material-symbols-outlined">cloud_upload</span> My Uploads</button>
        <div class="media-picker-menu-divider"></div>
        <button type="button" data-device="1"><span class="material-symbols-outlined">upload_file</span> Upload from Device</button>
    `;
    menu.querySelectorAll('button[data-folder]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            closeMediaPickerMenu();
            openLibraryModal(btn.dataset.folder);
        });
    });
    menu.querySelector('button[data-device]').addEventListener('click', function() {
        closeMediaPickerMenu();
        mediaPickerTarget?.fileInput?.click();
    });

    document.body.appendChild(menu);
    const rect = triggerEl.getBoundingClientRect();
    const menuWidth = 220;
    let left = window.scrollX + rect.left;
    if (left + menuWidth > window.scrollX + window.innerWidth - 10) {
        left = window.scrollX + window.innerWidth - menuWidth - 10;
    }
    menu.style.top = (window.scrollY + rect.bottom + 4) + 'px';
    menu.style.left = left + 'px';

    setTimeout(function() { document.addEventListener('click', outsideMediaPickerMenuClick); }, 0);
}

function outsideMediaPickerMenuClick(e) {
    const menu = document.querySelector('.media-picker-menu');
    if (menu && !menu.contains(e.target)) closeMediaPickerMenu();
}

function closeMediaPickerMenu() {
    document.querySelectorAll('.media-picker-menu').forEach(function(m) { m.remove(); });
    document.removeEventListener('click', outsideMediaPickerMenuClick);
}

function openLibraryModal(folder) {
    document.getElementById('libraryModal').classList.add('active');
    loadLibraryFolder(folder);
}

function closeLibraryModal() {
    document.getElementById('libraryModal').classList.remove('active');
}

function loadLibraryFolder(activeFolder) {
    const grid = document.getElementById('libraryFilesGrid');
    const title = document.getElementById('libraryModalTitle');
    const foldersBar = document.getElementById('libraryFoldersBar');

    // Build static tab list (sign-language folders + My Uploads at the end)
    const staticFolders = Object.keys(LIBRARY_FOLDER_META).map(function(key) {
        return { key: key, icon: LIBRARY_FOLDER_META[key].icon, label: LIBRARY_FOLDER_META[key].label };
    });

    function renderTabs(folders) {
        foldersBar.innerHTML = '';
        // Always include system library folders fetched from backend,
        // then append the My Uploads tab if not already present.
        const withMyUploads = folders.some(function(f) { return f.key === 'my_uploads'; })
            ? folders
            : folders.concat([{ key: 'my_uploads', icon: 'cloud_upload', label: 'My Uploads' }]);

        withMyUploads.forEach(function(f) {
            const tab = document.createElement('button');
            tab.type = 'button';
            tab.className = 'library-tab' + (f.key === activeFolder ? ' active' : '');
            tab.innerHTML = `<span class="material-symbols-outlined">${f.icon}</span> ${f.label}`;
            tab.addEventListener('click', function() { loadLibraryFolder(f.key); });
            foldersBar.appendChild(tab);
        });
        const current = withMyUploads.find(function(f) { return f.key === activeFolder; });
        title.textContent = current ? current.label : 'Sign Language Library';
    }

    if (activeFolder === 'my_uploads') {
        // My Uploads: always render tabs with cached or static folders
        const tabFolders = libraryFoldersCache || Object.keys(LIBRARY_FOLDER_META)
            .filter(function(k) { return k !== 'my_uploads'; })
            .map(function(k) { return { key: k, icon: LIBRARY_FOLDER_META[k].icon, label: LIBRARY_FOLDER_META[k].label }; });
        renderTabs(tabFolders);
        grid.innerHTML = '<div class="library-loading">Loading files…</div>';
        fetch(MY_UPLOADS_URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                const files = data.files || [];
                if (!files.length) {
                    grid.innerHTML = '<div class="library-empty">You have no uploaded media yet.<br>Go to the <strong>Media</strong> tab to upload files.</div>';
                    return;
                }
                grid.innerHTML = '';
                files.forEach(function(f) {
                    const card = document.createElement('div');
                    card.className = 'library-file-card';
                    if (f.type === 'video') {
                        card.innerHTML = `
                            <div class="library-file-thumb library-file-thumb-video">
                                <video src="${f.url}#t=0.3" muted preload="metadata" playsinline></video>
                                <span class="material-symbols-outlined play-icon">play_circle</span>
                            </div>`;
                    } else {
                        card.innerHTML = `<img class="library-file-thumb" src="${f.url}" alt="" loading="lazy">`;
                    }
                    const nameEl = document.createElement('div');
                    nameEl.className = 'library-file-name';
                    nameEl.textContent = f.title || f.file_name;
                    card.appendChild(nameEl);
                    card.addEventListener('click', function() {
                        if (mediaPickerTarget) applySelectedMedia(mediaPickerTarget, f);
                        closeLibraryModal();
                    });
                    grid.appendChild(card);
                });
            })
            .catch(function() {
                grid.innerHTML = '<div class="library-empty">Could not load your uploads. Please try again.</div>';
            });
        return;
    }

    const fallbackFolders = Object.keys(LIBRARY_FOLDER_META)
        .filter(function(k) { return k !== 'my_uploads'; })
        .map(function(k) { return { key: k, icon: LIBRARY_FOLDER_META[k].icon, label: LIBRARY_FOLDER_META[k].label }; });

    if (libraryFoldersCache) {
        renderTabs(libraryFoldersCache);
    } else {
        renderTabs(fallbackFolders);
        fetch(LIBRARY_FOLDERS_URL, { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.folders && data.folders.length) {
                    libraryFoldersCache = data.folders.map(function(f) {
                        return { key: f.key, icon: (LIBRARY_FOLDER_META[f.key]?.icon) || 'folder', label: f.label };
                    });
                    renderTabs(libraryFoldersCache);
                }
            })
            .catch(function() { /* keep the fallback tabs already rendered */ });
    }

    grid.innerHTML = '<div class="library-loading">Loading files…</div>';

    fetch(`${LIBRARY_FILES_BASE_URL}/${encodeURIComponent(activeFolder)}`, { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            const files = data.files || [];
            if (!files.length) {
                grid.innerHTML = '<div class="library-empty">No files in this folder yet.</div>';
                return;
            }
            grid.innerHTML = '';
            files.forEach(function(f) {
                const card = document.createElement('div');
                card.className = 'library-file-card';
                if (f.type === 'video') {
                    card.innerHTML = `
                        <div class="library-file-thumb library-file-thumb-video">
                            <video src="${f.url}#t=0.3" muted preload="metadata" playsinline></video>
                            <span class="material-symbols-outlined play-icon">play_circle</span>
                        </div>`;
                } else {
                    card.innerHTML = `<img class="library-file-thumb" src="${f.url}" alt="" loading="lazy">`;
                }
                const nameEl = document.createElement('div');
                nameEl.className = 'library-file-name';
                nameEl.textContent = f.file_name;
                card.appendChild(nameEl);
                card.addEventListener('click', function() {
                    if (mediaPickerTarget) applySelectedMedia(mediaPickerTarget, f);
                    closeLibraryModal();
                });
                grid.appendChild(card);
            });
        })
        .catch(function() {
            grid.innerHTML = '<div class="library-empty">Could not load files. Please try again.</div>';
        });
}

// Wires a file already chosen from the library (no upload needed — it's already
// on the server) into whichever form slot triggered the picker. Mirrors the
// "success" branch of the equivalent AJAX upload handler for that slot type.
function applySelectedMedia(target, fileData) {
    const isVideo = fileData.type === 'video';

    if (target.kind === 'widget') {
        const widget = target.el;
        const errorEl = widget.querySelector('.media-upload-error');
        const thumbWrap = widget.querySelector('.media-thumb-wrap');
        const label = widget.querySelector('.upload-label');
        const pathInput = widget.closest('.media-field, div')?.querySelector('.media-path-input')
                       || widget.parentElement?.querySelector('.media-path-input');
        if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
        if (pathInput) pathInput.value = fileData.path;
        widget.classList.remove('uploading');
        widget.classList.add('has-file');
        if (label) label.textContent = 'Click to replace file';
        if (thumbWrap) {
            thumbWrap.innerHTML = '';
            if (!isVideo) {
                const img = document.createElement('img');
                img.className = 'media-thumb';
                img.src = fileData.url;
                thumbWrap.appendChild(img);
            upgradeVideoThumbs(thumbWrap);
            } else {
                renderMediaThumb(thumbWrap, fileData.url, true);
            }
            const info = document.createElement('div');
            info.className = 'media-thumb-info';
            info.innerHTML = `<strong>From Library</strong>${fileData.file_name}<button type="button" class="media-remove-btn" onclick="clearMediaWidget(this)">✕ Remove</button>`;
            thumbWrap.appendChild(info);
            autoFillGestureName(widget, fileData.file_name || fileData.path);
        }
    } else if (target.kind === 'dragdrop') {
        const widget = target.el;
        const thumbWrap = widget.querySelector('.media-thumb-wrap');
        const label = widget.querySelector('.upload-label');
        const pair = widget.closest('.drag-drop-pair');
        const pathInput = target.side === 'left'
            ? pair.querySelector('.left-image-path')
            : pair.querySelector('.right-image-path');
        if (pathInput) pathInput.value = fileData.path;
        widget.classList.remove('uploading');
        widget.classList.add('has-file');
        if (label) label.textContent = 'Replace image';
        if (thumbWrap) {
            thumbWrap.innerHTML = '';
            const img = document.createElement('img');
            img.className = 'media-thumb';
            img.src = fileData.url;
            img.style.cssText = 'width:64px;height:64px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;';
            thumbWrap.appendChild(img);
            upgradeVideoThumbs(thumbWrap);
            const info = document.createElement('div');
            info.className = 'media-thumb-info';
            info.innerHTML = '<button type="button" class="media-remove-btn" onclick="clearDragDropImage(this)">✕ Remove</button>';
            thumbWrap.appendChild(info);
        }
    } else if (target.kind === 'option') {
        const optBody = target.el;
        const preview = optBody.querySelector('.option-image-preview');
        const pathInput = optBody.querySelector('.media-path-input');
        const optImgInput = optBody.querySelector('.option-image-input');
        const label = optImgInput ? optImgInput.closest('label') : null;
        if (pathInput) pathInput.value = fileData.path;
        setSmallMediaPreview(preview, fileData.url);
        let clearBtn = optBody.querySelector('.option-img-clear-btn');
        if (!clearBtn) {
            clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'option-img-clear-btn text-xs text-red-400 hover:text-red-600 font-semibold flex-shrink-0';
            clearBtn.textContent = '✕';
            clearBtn.onclick = function() { clearOptionImage(this); };
            optBody.querySelector('.option-image-row').appendChild(clearBtn);
        }
        if (label) {
            const textNode = label.childNodes[label.childNodes.length - 2];
            if (textNode && textNode.nodeType === 3) {
                textNode.textContent = ' Replace image';
            }
        }
    }
}

// Close the library modal on click-outside, same pattern as the other overlays.
window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('libraryModal')) closeLibraryModal();
});

/* ═══════════════════════════════════════════════════════
   PREVIEW
═══════════════════════════════════════════════════════ */

function openPreview() {
    const overlay = document.getElementById('previewOverlay');
    const content = document.getElementById('previewContent');
    overlay.classList.add('active');
    content.innerHTML = '<div class="preview-loading">⏳ Preparing preview...</div>';

    const form = document.getElementById('lessonForm');

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
                    const ddLeftMatch   = name.match(/^(quiz\[\d+\]\[drag_drop_pairs\]\[\d+\])\[left_image\]$/);
                    const ddRightMatch  = name.match(/^(quiz\[\d+\]\[drag_drop_pairs\]\[\d+\])\[right_image\]$/);
                    if (contentMatch)  hiddenName = contentMatch[1]  + '[existing_media]';
                    else if (quizMatch) hiddenName = quizMatch[1]    + '[existing_media]';
                    else if (optionMatch) hiddenName = optionMatch[1] + '[existing_image]';
                    else if (ddLeftMatch) hiddenName = ddLeftMatch[1] + '[left_image]';
                    else if (ddRightMatch) hiddenName = ddRightMatch[1] + '[right_image]';
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
   YOUTUBE VIDEO HELPERS
═══════════════════════════════════════════════════════ */
function extractYoutubeId(url) {
    if (!url) return null;
    url = url.trim();
    let m = url.match(/youtu\.be\/([a-zA-Z0-9_-]{11})/);
    if (m) return m[1];
    m = url.match(/[?&]v=([a-zA-Z0-9_-]{11})/);
    if (m) return m[1];
    m = url.match(/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/);
    if (m) return m[1];
    m = url.match(/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/);
    if (m) return m[1];
    return null;
}

function buildYoutubeEmbedUrl(url) {
    const id = extractYoutubeId(url);
    if (!id) return null;
    return 'https://www.youtube.com/embed/' + id + '?rel=0&modestbranding=1';
}

function handleYoutubeUrlInput(input) {
    const card = input.closest('.content-card');
    if (!card) return;
    const errorEl    = card.querySelector('.youtube-url-error');
    const previewWrap = card.querySelector('.youtube-preview-wrap');
    const iframe     = card.querySelector('.youtube-preview-iframe');
    const url = input.value.trim();
    if (!url) {
        if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
        if (previewWrap) previewWrap.style.display = 'none';
        if (iframe) iframe.src = '';
        return;
    }
    const embedUrl = buildYoutubeEmbedUrl(url);
    if (!embedUrl) {
        if (errorEl) { errorEl.textContent = '⚠ Invalid or unsupported YouTube URL. Use a youtube.com or youtu.be link.'; errorEl.style.display = 'block'; }
        if (previewWrap) previewWrap.style.display = 'none';
        if (iframe) iframe.src = '';
        return;
    }
    if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
    if (iframe) iframe.src = embedUrl;
    if (previewWrap) previewWrap.style.display = 'block';
}

document.addEventListener('input', function(e) {
    if (e.target && e.target.classList.contains('youtube-url-input')) {
        handleYoutubeUrlInput(e.target);
    }
});

/* ═══════════════════════════════════════════════════════
   CONTENT CARDS
═══════════════════════════════════════════════════════ */

function toggleFields(select) {
    const card = select.closest('.content-card');
    if (!card) return;
    const gestureField = card.querySelector('.gesture-field');
    const mediaField   = card.querySelector('.media-field');
    const youtubeField = card.querySelector('.youtube-field');
    const typeLabel    = card.querySelector('.text-xs.bg-blue-50');
    if (gestureField) gestureField.classList.add('hidden');
    if (mediaField)   mediaField.classList.add('hidden');
    if (youtubeField) youtubeField.classList.add('hidden');
    if (typeLabel) {
        const map = { text: 'Text', gesture_demo: 'Gesture', image: 'Image', video: 'Video', youtube_video: 'YouTube' };
        typeLabel.textContent = map[select.value] || 'Text';
    }
    if (select.value === 'gesture_demo') {
        if (gestureField) gestureField.classList.remove('hidden');
        if (mediaField)   mediaField.classList.remove('hidden');
    } else if (select.value === 'image' || select.value === 'video') {
        if (mediaField) mediaField.classList.remove('hidden');
    } else if (select.value === 'youtube_video') {
        if (youtubeField) youtubeField.classList.remove('hidden');
    }
    const mediaMissingInput = card.querySelector('input[name*="[media_missing]"]');
    if (mediaMissingInput && mediaMissingInput.value === '1') {
        if (mediaField) mediaField.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.content-type').forEach(toggleFields);
    document.querySelectorAll('.quiz-question select.question-type').forEach(select => {
        handleQuestionTypeChange(select);
    });

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
        let isValid = true;
        try {
            clearValidationErrors();
            if (!validateLessonForm(false)) isValid = false;
        } catch (err) {
            // A crash in validation must NEVER result in a silent save
            console.error('Validation crashed:', err);
            addToValidationSummary('Validation could not complete. Please review the form and try again.');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            const firstBad = document.querySelector('.field-error, .section-error');
            if (firstBad) {
                firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof firstBad.focus === 'function' && firstBad.matches('input, select, textarea')) {
                    setTimeout(() => firstBad.focus({ preventScroll: true }), 350);
                }
            }
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
        <input type="hidden" name="contents[${index}][existing_media]" value="" class="media-path-input">
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
    card.className = 'content-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative';
    card.innerHTML = `
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="step-number-badge step-number">${contentIndex + 1}</div>
                <span class="content-type-pill">Text</span>
            </div>
            <button type="button" onclick="removeContentCard(this)"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="field-label-sm">Content Type</label>
                <select name="contents[${contentIndex}][content_type]" class="content-type w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]" onchange="toggleFields(this)">
                    <option value="text">Text</option>
                    <option value="gesture_demo">Gesture Demo</option>
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                    <option value="youtube_video">YouTube Video</option>
                </select>
            </div>
            <div>
                <label class="field-label-sm">Title</label>
                <input type="text" name="contents[${contentIndex}][title]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]" placeholder="e.g., Introduction to FSL Alphabet">
            </div>
            <div>
                <label class="field-label-sm">Content</label>
                <textarea name="contents[${contentIndex}][content_text]" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]" placeholder="Write your lesson content here..."></textarea>
            </div>
            <div class="gesture-field hidden">
                <label class="field-label-sm">Gesture Name</label>
                <input type="text" name="contents[${contentIndex}][gesture_name]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]" placeholder="e.g., letter_a">
            </div>
            <div class="media-field hidden">
                <label class="field-label-sm">Upload Media</label>
                ${buildMediaWidget(contentIndex)}
            </div>
            <div class="youtube-field hidden">
                <label class="field-label-sm">YouTube Video URL</label>
                <input type="text" name="contents[${contentIndex}][youtube_url]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc] youtube-url-input" placeholder="https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID" autocomplete="off">
                <div class="youtube-url-error" style="display:none; color:#EF4444; font-size:12px; font-weight:600; margin-top:4px;"></div>
                <div class="youtube-preview-wrap" style="display:none; margin-top:12px; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(13,50,107,0.12);">
                    <iframe class="youtube-preview-iframe" width="100%" height="250" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="display:block; border-radius:14px;"></iframe>
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
        el.textContent = i + 1;
    });
}

/* ═══════════════════════════════════════════════════════
   QUIZ
═══════════════════════════════════════════════════════ */

function handleQuestionTypeChange(select) {
    const questionDiv = select.closest('.quiz-question');
    if (!questionDiv) return;
    const optionsContainer = questionDiv.querySelector('.options-container');
    const dragDropContainer = questionDiv.querySelector('.drag-drop-container');
    const gestureContainer = questionDiv.querySelector('.gesture-quiz-container');
    const qIndex = getQuizQuestionIndex(questionDiv);

    // Hide all type-specific containers
    if (optionsContainer) optionsContainer.classList.add('hidden');
    if (dragDropContainer) dragDropContainer.classList.add('hidden');
    if (gestureContainer) gestureContainer.classList.add('hidden');

    if (select.value === 'true_false') {
        if (optionsContainer) optionsContainer.classList.remove('hidden');
        const optionsList = questionDiv.querySelector('.options-list');
        const rows = optionsList.querySelectorAll('.option-row');
        while (rows.length > 2) rows[rows.length - 1].remove();
        const textInputs = optionsList.querySelectorAll('.option-text-input');
        const radios = optionsList.querySelectorAll('input[type="radio"]');
        if (textInputs[0]) textInputs[0].value = 'True';
        if (textInputs[1]) textInputs[1].value = 'False';
        if (radios[0]) radios[0].value = '0';
        if (radios[1]) radios[1].value = '1';
        optionsList.querySelectorAll('.option-image-row, .option-remove-btn').forEach(el => el.style.visibility = 'hidden');
        const addBtn = optionsContainer.querySelector('button');
        if (addBtn) addBtn.style.display = 'none';
    } else if (select.value === 'drag_drop') {
        if (dragDropContainer) dragDropContainer.classList.remove('hidden');
        // Auto-add first pair if none exist
        const pairsList = dragDropContainer.querySelector('.drag-drop-pairs-list');
        if (pairsList && pairsList.querySelectorAll('.drag-drop-pair').length === 0) {
            const addBtn = dragDropContainer.querySelector('button[onclick*="addDragDropPair"]');
            if (addBtn) {
                addBtn.click();
                addBtn.click();
            }
        }
    } else if (select.value === 'gesture') {
        if (gestureContainer) gestureContainer.classList.remove('hidden');
        const moduleSelect = gestureContainer.querySelector('.gesture-module-select');
        if (moduleSelect && moduleSelect.value) {
            loadGesturesForModule(moduleSelect, qIndex);
            if (moduleSelect.value === '6') {
                const wordContainer = questionDiv.querySelector('.fingerspelling-word-container');
                if (wordContainer) wordContainer.classList.remove('hidden');
            }
        }
    } else if (select.value === 'multiple_choice') {
        if (optionsContainer) optionsContainer.classList.remove('hidden');
        const optionsList = questionDiv.querySelector('.options-list');
        optionsList.querySelectorAll('.option-image-row, .option-remove-btn').forEach(el => el.style.visibility = 'visible');
        const addBtn = optionsContainer.querySelector('button');
        if (addBtn) addBtn.style.display = 'inline-block';
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
                <img class="option-image-preview w-16 h-16 rounded-lg object-cover border border-slate-200 flex-shrink-0" src="" alt="" style="display:none;">
                <label class="text-xs text-[#0d326b] font-semibold cursor-pointer hover:underline flex items-center gap-1 flex-shrink-0">
                    <span class="material-symbols-outlined" style="font-size:16px;">add_photo_alternate</span> Add image
                    <input type="file" accept="image/*,video/*" class="option-image-input hidden" onchange="handleOptionImageUpload(this)">
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

/* ═══════════════════════════════════════════════════════
   DRAG & DROP PAIRS
═══════════════════════════════════════════════════════ */

function buildDragDropPair(qIndex, pairIndex) {
    const pair = document.createElement('div');
    pair.className = 'drag-drop-pair';
    pair.dataset.pairIndex = pairIndex;
    pair.innerHTML = `
        <div class="pair-side">
            <label>Left Item</label>
            <input type="text" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][left_text]" placeholder="e.g., Letter A">
            <div style="margin-top:4px;">
                <input type="hidden" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][left_image]" value="" class="drag-drop-image-path left-image-path">
                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*,video/*" style="padding:6px;border-radius:10px;">
                    <div class="upload-trigger" style="gap:6px;">
                        <input type="file" accept="image/*,video/*" class="ajax-file-input" data-side="left" onchange="handleDragDropImageUpload(this, ${qIndex}, ${pairIndex})">
                        <span class="upload-icon material-symbols-outlined" style="font-size:16px;color:#94a3b8;">add_photo_alternate</span>
                        <div class="upload-spinner"></div>
                        <span class="upload-label" style="font-size:11px;">Add image</span>
                    </div>
                    <div class="media-thumb-wrap" style="margin-top:4px;"></div>
                </div>
            </div>
        </div>
        <div class="pair-arrow">
            <span class="material-symbols-outlined">arrow_forward</span>
        </div>
        <div class="pair-side">
            <label>Right Match</label>
            <input type="text" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][right_text]" placeholder="e.g., Hand sign for A">
            <div style="margin-top:4px;">
                <input type="hidden" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][right_image]" value="" class="drag-drop-image-path right-image-path">
                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*,video/*" style="padding:6px;border-radius:10px;">
                    <div class="upload-trigger" style="gap:6px;">
                        <input type="file" accept="image/*,video/*" class="ajax-file-input" data-side="right" onchange="handleDragDropImageUpload(this, ${qIndex}, ${pairIndex})">
                        <span class="upload-icon material-symbols-outlined" style="font-size:16px;color:#94a3b8;">add_photo_alternate</span>
                        <div class="upload-spinner"></div>
                        <span class="upload-label" style="font-size:11px;">Add image</span>
                    </div>
                    <div class="media-thumb-wrap" style="margin-top:4px;"></div>
                </div>
            </div>
        </div>
        <button type="button" onclick="removeDragDropPair(this)" class="pair-remove option-remove-btn">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
    `;
    return pair;
}

function addDragDropPair(btn) {
    const container = btn.closest('.drag-drop-container');
    if (!container) return;
    const pairsList = container.querySelector('.drag-drop-pairs-list');
    const qIndex = getQuizQuestionIndex(container.closest('.quiz-question'));
    const pairIndex = pairsList.querySelectorAll('.drag-drop-pair').length;
    pairsList.appendChild(buildDragDropPair(qIndex, pairIndex));
}

function removeDragDropPair(btn) {
    const pair = btn.closest('.drag-drop-pair');
    const container = pair.closest('.drag-drop-pairs-list');
    if (container.querySelectorAll('.drag-drop-pair').length > 2) {
        pair.remove();
    }
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
            questionDiv.style.borderColor = '#e2e8f0';
            questionDiv.style.borderWidth = '1.5px';
        }
    });

    return { isValid, errorMsg };
}

/* ═══════════════════════════════════════════════════════
   GESTURE MODULE LOADING
═══════════════════════════════════════════════════════ */

function loadGesturesForModule(select, questionIndex) {
    const moduleId = select.value;
    const questionDiv = select.closest('.quiz-question');
    const checkboxesContainer = document.getElementById(`gestureCheckboxes_${questionIndex}`);
    const previewContainer = questionDiv.querySelector('.selected-gestures-preview');
    const tagsContainer = document.getElementById(`selectedGestureTags_${questionIndex}`);

    // Fingerspelling word container (only present for the fingerspelling module)
    const wordContainer = questionDiv.querySelector('.fingerspelling-word-container');
    const wordTextarea = wordContainer ? wordContainer.querySelector('.fingerspelling-words-textarea') : null;

    if (!moduleId) {
        checkboxesContainer.innerHTML = '<span class="text-sm text-slate-400">Select a module first</span>';
        if (previewContainer) previewContainer.style.display = 'none';
        if (wordContainer) wordContainer.classList.add('hidden');
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
        select.removeAttribute('data-selected-ids');
    }

    checkboxesContainer.innerHTML = '<span class="text-sm text-slate-400">Loading gestures...</span>';

    // Fingerspelling module (id 6): load the full alphabet instead of the module's own gesture list
    if (moduleId === '6') {
        if (wordContainer) wordContainer.classList.remove('hidden');
        loadFingerspellingLetters(checkboxesContainer, questionIndex, selectedIds);
        return;
    }

    if (wordContainer) wordContainer.classList.add('hidden');

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
                const isSelected = selectedIds.includes(Number(gesture.gesture_id));
                const label = createGestureCheckbox(gesture, questionIndex, isSelected);
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

/**
 * Load all alphabet letters (A-Z) from both alphabet modules for the Fingerspelling module.
 */
function loadFingerspellingLetters(container, questionIndex, selectedIds) {
    selectedIds = selectedIds || [];
    const alphabetModuleIds = [1, 2];
    let allGestures = [];
    let completed = 0;

    container.innerHTML = '<span class="text-sm text-slate-400">Loading alphabet letters...</span>';

    alphabetModuleIds.forEach(modId => {
        fetch(`/api/gesture-modules/${modId}/gestures`, {
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.gestures && data.gestures.length > 0) {
                data.gestures.forEach(g => {
                    g._module_id = modId;
                    g._module_label = modId === 1 ? 'A-M' : 'N-Z';
                });
                allGestures = allGestures.concat(data.gestures);
            }
            completed++;
            if (completed === alphabetModuleIds.length) {
                renderFingerspellingGestures(container, allGestures, questionIndex, selectedIds);
            }
        })
        .catch(error => {
            console.error(`Error loading alphabet module ${modId}:`, error);
            completed++;
            if (completed === alphabetModuleIds.length) {
                if (allGestures.length === 0) {
                    container.innerHTML = '<span class="text-sm text-red-500">Error loading alphabet letters</span>';
                } else {
                    renderFingerspellingGestures(container, allGestures, questionIndex, selectedIds);
                }
            }
        });
    });
}

/**
 * Render the combined alphabet gestures with module labels.
 */
function renderFingerspellingGestures(container, gestures, questionIndex, selectedIds) {
    selectedIds = selectedIds || [];
    container.innerHTML = '';

    if (!gestures || gestures.length === 0) {
        container.innerHTML = '<span class="text-sm text-slate-400">No alphabet letters found</span>';
        return;
    }

    // Sort by gesture_id (A=1, B=2, ... Z=26)
    gestures.sort((a, b) => a.gesture_id - b.gesture_id);

    const headerNote = document.createElement('div');
    headerNote.style.cssText = 'width:100%;margin-bottom:8px;font-size:12px;color:#64748b;font-weight:600;';
    headerNote.textContent = 'All 26 letters available for fingerspelling';
    container.appendChild(headerNote);

    gestures.forEach(gesture => {
        const isSelected = selectedIds.includes(Number(gesture.gesture_id));
        const label = createGestureCheckbox(gesture, questionIndex, isSelected);
        container.appendChild(label);
    });

    updateGesturePreview(questionIndex);

    // If this question already has saved fingerspelling words (edit mode), re-derive the
    // preview and letter selection from them now that the checkboxes exist.
    const questionDiv = container.closest('.quiz-question');
    const wordContainer = questionDiv ? questionDiv.querySelector('.fingerspelling-word-container') : null;
    const wordTextarea = wordContainer ? wordContainer.querySelector('.fingerspelling-words-textarea') : null;
    if (wordTextarea && wordTextarea.value.trim()) {
        updateFingerspellingWordPreview(wordTextarea);
    }
}

// ─── FINGERSPELLING WORD PREVIEW ───────────────────────────────────────────
function updateFingerspellingWordPreview(textarea) {
    const container = textarea.closest('.fingerspelling-word-container');
    if (!container) return;

    const questionDiv = container.closest('.quiz-question');
    const qIndex = getQuizQuestionIndex(questionDiv);
    const previewDiv = container.querySelector('.fingerspelling-words-preview');
    const letterIdsInput = container.querySelector('.fingerspelling-letter-ids');

    const raw = textarea.value;
    let words = raw.split('\n')
        .map(line => line.trim())
        .filter(line => line.length > 0)
        .flatMap(line => line.split(' '))
        .map(word => word.toUpperCase().replace(/[^A-Z]/g, ''))
        .filter(word => word.length > 0);

    words = [...new Set(words)];

    if (words.length === 0) {
        previewDiv.innerHTML = '<span class="text-sm text-slate-400">Enter words to spell (one per line)</span>';
        if (letterIdsInput) letterIdsInput.value = '';

        const checkboxes = questionDiv.querySelectorAll('.gesture-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = false;
            const label = cb.closest('.gesture-checkbox-label');
            if (label) label.classList.remove('selected');
        });
        updateGesturePreview(qIndex);
        return;
    }

    let html = '';
    let allLetterIds = [];

    words.forEach((word) => {
        const letters = word.split('');
        const wordLetterIds = letters.map(letter => {
            const id = letter.charCodeAt(0) - 64; // A=1, B=2, ...
            return id >= 1 && id <= 26 ? id : null;
        }).filter(id => id !== null);

        allLetterIds = allLetterIds.concat(wordLetterIds);

        html += `
            <div class="fingerspelling-word-pill">
                <div class="word-letters">
                    ${letters.map(letter => `<span>${letter}</span>`).join('')}
                </div>
                <span class="word-count">${letters.length} letters</span>
            </div>
        `;
    });

    previewDiv.innerHTML = html;

    if (letterIdsInput) {
        letterIdsInput.value = allLetterIds.join(',');
    }

    const uniqueLetterIds = [...new Set(allLetterIds)];
    const checkboxes = questionDiv.querySelectorAll('.gesture-checkbox');
    checkboxes.forEach(cb => {
        const isChecked = uniqueLetterIds.includes(parseInt(cb.value));
        cb.checked = isChecked;
        const label = cb.closest('.gesture-checkbox-label');
        if (label) {
            if (isChecked) {
                label.classList.add('selected');
            } else {
                label.classList.remove('selected');
            }
        }
    });

    updateGesturePreview(qIndex);
    if (letterIdsInput) letterIdsInput.dispatchEvent(new Event('change', { bubbles: true }));
}

/**
 * Helper to create a gesture checkbox label. `isSelected` pre-checks it (used for
 * pre-selected gesture IDs when editing an existing question).
 */
function createGestureCheckbox(gesture, questionIndex, isSelected) {
    const label = document.createElement('label');
    label.className = 'gesture-checkbox-label';

    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.name = `quiz[${questionIndex}][gesture_ids][]`;
    checkbox.value = gesture.gesture_id;
    checkbox.className = 'gesture-checkbox';
    checkbox.dataset.displayName = gesture.display_name || gesture.name;

    const checkIcon = document.createElement('span');
    checkIcon.className = 'check-icon';
    checkIcon.textContent = '✓';

    checkbox.onchange = function() {
        if (this.checked) {
            label.classList.add('selected');
        } else {
            label.classList.remove('selected');
        }
        updateGesturePreview(questionIndex);
    };

    const span = document.createElement('span');
    span.textContent = gesture.display_name || gesture.name;

    if (isSelected) {
        checkbox.checked = true;
        label.classList.add('selected');
    }

    label.appendChild(checkbox);
    label.appendChild(span);
    label.appendChild(checkIcon);

    return label;
}

function updateGesturePreview(questionIndex) {
    const tagsContainer = document.getElementById(`selectedGestureTags_${questionIndex}`);
    if (!tagsContainer) return;

    const questionDiv = tagsContainer.closest('.quiz-question');
    if (!questionDiv) return;

    const previewContainer = questionDiv.querySelector('.selected-gestures-preview');
    const checkboxes = questionDiv.querySelectorAll(`.gesture-checkbox:checked`);

    if (checkboxes.length === 0) {
        if (previewContainer) previewContainer.style.display = 'none';
        return;
    }

    if (previewContainer) previewContainer.style.display = 'block';
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

/* ═══════════════════════════════════════════════════════
   QUIZ QUESTIONS - ADD/REMOVE
═══════════════════════════════════════════════════════ */

function addQuizQuestion() {
    const container = document.getElementById('quizQuestions');
    const qIndex = quizIndex;
    const question = document.createElement('div');
    question.className = 'quiz-question-card';
    question.innerHTML = `
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="step-number-badge" style="background:#0d326b;">${qIndex + 1}</div>
                <span class="text-sm font-bold text-slate-600 question-label">Question ${qIndex + 1}</span>
            </div>
            <button type="button" onclick="removeQuizQuestion(this)"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="field-label-sm">Question</label>
                <input type="text" name="quiz[${qIndex}][question]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]" placeholder="Enter your question">
            </div>
            <div>
                <label class="field-label-sm">Question Type</label>
                <select name="quiz[${qIndex}][type]" onchange="handleQuestionTypeChange(this)" class="question-type w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]">
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="true_false">True / False</option>
                    <option value="drag_drop">Drag and Drop</option>
                    <option value="gesture">Gesture Recognition</option>
                </select>
            </div>
            <div>
                <label class="field-label-sm">Question Image (Optional)</label>
                <input type="hidden" name="quiz[${qIndex}][existing_media]" value="" class="media-path-input">
                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*,video/*">
                    <div class="upload-trigger">
                        <input type="file" accept="image/*,video/*" class="ajax-file-input" onchange="handleAjaxUpload(this,'quiz_media')">
                        <span class="upload-icon material-symbols-outlined text-slate-400" style="font-size:20px;">cloud_upload</span>
                        <div class="upload-spinner"></div>
                        <span class="upload-label">Upload question image (optional)</span>
                    </div>
                    <div class="media-thumb-wrap"></div>
                    <div class="media-upload-error"></div>
                </div>
            </div>
            <div class="options-container">
                <label class="field-label-sm">Answer Options</label>
                <p class="text-xs text-slate-400 mb-2">Each option can have text and/or an image.</p>
                <div class="space-y-2 options-list"></div>
                <button type="button" onclick="addOption(this)"
                        class="inline-flex items-center gap-1 mt-2 text-[12px] font-bold text-[#0d326b] hover:underline">+ Add Option</button>
            </div>
            <!-- Drag and Drop Pairs -->
            <div class="drag-drop-container hidden">
                <label class="field-label-sm">Drag and Drop Pairs</label>
                <p class="text-xs text-slate-400 mb-2">Match items from the left column to the right column.</p>
                <div class="space-y-2 drag-drop-pairs-list"></div>
                <button type="button" onclick="addDragDropPair(this)"
                        class="inline-flex items-center gap-1 mt-2 text-[12px] font-bold text-[#0d326b] hover:underline">+ Add Pair</button>
            </div>
            <!-- Gesture Recognition Fields -->
            <div class="gesture-quiz-container hidden">
                <label class="field-label-sm">Gesture Recognition Settings</label>
                <p class="text-xs text-slate-400 mb-2">Select a gesture module and the specific gestures students need to perform.</p>
                <div class="space-y-3">
                    <div>
                        <label class="field-label-sm">Gesture Module</label>
                        <select name="quiz[${qIndex}][gesture_module_id]" class="field-select gesture-module-select w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10 outline-none transition-all text-[14px] bg-[#fafbfc]" onchange="loadGesturesForModule(this, ${qIndex})">
                            <option value="">Select a module...</option>
                            @foreach($gestureModules as $module)
                                <option value="{{ $module->module_id }}">{{ $module->display_name ?? $module->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label-sm">Select Gestures to Recognize</label>
                        <p class="text-xs text-slate-400 mb-2">Click to select gestures. Students will need to perform all selected gestures.</p>
                        <div id="gestureCheckboxes_${qIndex}" class="flex flex-wrap gap-2 mt-2" style="min-height:60px;">
                            <span class="text-sm text-slate-400">Select a module first</span>
                        </div>
                    </div>
                    <div class="selected-gestures-preview" style="display:none;">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Selected Gestures</label>
                        <div class="flex flex-wrap gap-2" id="selectedGestureTags_${qIndex}"></div>
                    </div>

                    <div class="fingerspelling-word-container hidden" style="margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">📝 Fingerspelling Words</label>
                        <p class="text-xs text-slate-400 mb-2">Enter words one per line. Students will fingerspell each word.</p>
                        <div class="space-y-3">
                            <div>
                                <textarea
                                    name="quiz[${qIndex}][fingerspelling_words]"
                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all fingerspelling-words-textarea"
                                    placeholder="Enter one word per line&#10;Example:&#10;HELLO&#10;NICE&#10;SENYAS"
                                    rows="4"
                                    oninput="updateFingerspellingWordPreview(this)"
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Preview</label>
                                <div class="fingerspelling-words-preview" id="fingerspellingPreview_${qIndex}">
                                    <span class="text-sm text-slate-400">Enter words to see preview</span>
                                </div>
                            </div>
                            <input type="hidden" name="quiz[${qIndex}][fingerspelling_letter_ids]" class="fingerspelling-letter-ids" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
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
        const typeSelect = questionDiv.querySelector('.question-type');
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

            // Relabel fingerspelling fields
            const wordContainer = gestureContainer.querySelector('.fingerspelling-word-container');
            if (wordContainer) {
                const wordTextarea = wordContainer.querySelector('.fingerspelling-words-textarea');
                if (wordTextarea) wordTextarea.name = `quiz[${qIndex}][fingerspelling_words]`;
                const letterIdsInput = wordContainer.querySelector('.fingerspelling-letter-ids');
                if (letterIdsInput) letterIdsInput.name = `quiz[${qIndex}][fingerspelling_letter_ids]`;
                const previewDiv = wordContainer.querySelector('[id^="fingerspellingPreview_"]');
                if (previewDiv) previewDiv.id = `fingerspellingPreview_${qIndex}`;
            }
        }
    });
    quizIndex = document.querySelectorAll('.quiz-question').length;
}

/* ═══════════════════════════════════════════════════════
   AI QUIZ FROM CONTENT
═══════════════════════════════════════════════════════ */

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

/* ═══════════════════════════════════════════════════════
   FORM VALIDATION
═══════════════════════════════════════════════════════ */

function validateLessonContent() {
    let errors = [];

    const contentCards = document.querySelectorAll('.content-card');
    if (contentCards.length === 0) {
        errors.push('Please add at least one content slide.');
    }

    contentCards.forEach((card, index) => {
        const typeSelect = card.querySelector('.content-type');
        const contentType = typeSelect ? typeSelect.value : 'text';
        const titleInput = card.querySelector('input[name*="[title]"]');
        const contentText = card.querySelector('textarea[name*="[content_text]"]');
        const mediaInput = card.querySelector('input[name*="[existing_media]"]');

        if (!titleInput || !titleInput.value.trim()) {
            errors.push(`Content Slide ${index + 1}: Please add a title.`);
        }

        if (contentType === 'text') {
            if (!contentText || !contentText.value.trim()) {
                errors.push(`Content Slide ${index + 1}: Please add content text.`);
            }
        } else if (contentType === 'gesture_demo') {
            const gestureName = card.querySelector('input[name*="[gesture_name]"]');
            if (!gestureName || !gestureName.value.trim()) {
                errors.push(`Content Slide ${index + 1}: Please enter a gesture name.`);
            }
        } else if (contentType === 'image' || contentType === 'video') {
            if (!mediaInput || !mediaInput.value.trim()) {
                errors.push(`Content Slide ${index + 1}: Please upload a ${contentType}.`);
            }
        } else if (contentType === 'youtube_video') {
            const ytInput = card.querySelector('.youtube-url-input');
            const ytUrl = ytInput ? ytInput.value.trim() : '';
            if (!ytUrl) {
                errors.push(`Content Slide ${index + 1}: Please enter a YouTube video URL.`);
            } else if (!extractYoutubeId(ytUrl)) {
                errors.push(`Content Slide ${index + 1}: Invalid YouTube URL. Use a youtube.com or youtu.be link.`);
            }
        }
    });

    return errors;
}

function validateQuizQuestions() {
    let errors = [];
    const questions = document.querySelectorAll('.quiz-question');

    if (questions.length === 0) {
        errors.push('Please add at least one quiz question.');
        return errors;
    }

    questions.forEach((question, index) => {
        const questionNum = index + 1;
        const questionInput = question.querySelector('input[name*="[question]"]');

        if (!questionInput || !questionInput.value.trim()) {
            errors.push(`Quiz Question ${questionNum}: Please enter a question.`);
        }

        const typeSelect = question.querySelector('.question-type');
        const questionType = typeSelect ? typeSelect.value : 'multiple_choice';

        if (questionType === 'multiple_choice' || questionType === 'true_false') {
            const options = question.querySelectorAll('.option-row');
            const hasValidOption = Array.from(options).some(opt => {
                const textInput = opt.querySelector('.option-text-input');
                const imageInput = opt.querySelector('input[name*="[existing_image]"]');
                return (textInput && textInput.value.trim()) || (imageInput && imageInput.value.trim());
            });

            if (options.length < 2) {
                errors.push(`Quiz Question ${questionNum}: Need at least 2 options for ${questionType === 'true_false' ? 'True/False' : 'Multiple Choice'}.`);
            } else if (!hasValidOption) {
                errors.push(`Quiz Question ${questionNum}: Each option needs text OR an image.`);
            }

            const correctRadio = question.querySelector('input[type="radio"]:checked');
            if (!correctRadio) {
                errors.push(`Quiz Question ${questionNum}: Please select the correct answer.`);
            }

        } else if (questionType === 'drag_drop') {
            const pairs = question.querySelectorAll('.drag-drop-pair');

            if (pairs.length < 2) {
                errors.push(`Quiz Question ${questionNum}: Need at least 2 drag & drop pairs.`);
            }

            pairs.forEach((pair, pairIndex) => {
                const leftText = pair.querySelector('input[name*="[left_text]"]');
                const rightText = pair.querySelector('input[name*="[right_text]"]');
                const leftImage = pair.querySelector('input[name*="[left_image]"]');
                const rightImage = pair.querySelector('input[name*="[right_image]"]');

                const hasLeftContent = (leftText && leftText.value.trim()) || (leftImage && leftImage.value.trim());
                const hasRightContent = (rightText && rightText.value.trim()) || (rightImage && rightImage.value.trim());

                if (!hasLeftContent) {
                    errors.push(`Quiz Question ${questionNum}, Pair ${pairIndex + 1}: Left item needs text OR an image.`);
                }
                if (!hasRightContent) {
                    errors.push(`Quiz Question ${questionNum}, Pair ${pairIndex + 1}: Right item needs text OR an image.`);
                }
            });

        } else if (questionType === 'gesture') {
            const moduleSelect = question.querySelector('.gesture-module-select');
            const selectedGestures = question.querySelectorAll('.gesture-checkbox:checked');

            if (!moduleSelect || !moduleSelect.value) {
                errors.push(`Quiz Question ${questionNum}: Please select a gesture module.`);
            }

            if (selectedGestures.length === 0) {
                errors.push(`Quiz Question ${questionNum}: Please select at least one gesture.`);
            }
        }
    });

    return errors;
}

function clearValidationErrors() {
    document.querySelectorAll('.field-error').forEach(el => {
        el.classList.remove('field-error');
    });
    document.querySelectorAll('.section-error').forEach(el => {
        el.classList.remove('section-error');
    });
    document.querySelectorAll('.error-message').forEach(el => {
        el.remove();
    });

    document.querySelectorAll('.media-upload-widget').forEach(widget => {
        widget.style.borderColor = '';
        widget.style.borderWidth = '';
    });
}

// Show error on a specific field
function showFieldError(field, message) {
    if (!field) return;

    field.classList.add('field-error');

    let errorEl = field.parentElement.querySelector('.error-message');
    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.className = 'error-message';
        errorEl.innerHTML = `<span class="material-symbols-outlined">error</span> ${message}`;
        field.parentElement.appendChild(errorEl);
    } else {
        errorEl.innerHTML = `<span class="material-symbols-outlined">error</span> ${message}`;
    }
}

// Show section error (for whole cards)
function showSectionError(element, message) {
    if (!element) return;
    element.classList.add('section-error');

    let errorEl = element.querySelector('.section-error-message');
    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.className = 'error-message section-error-message';
        errorEl.style.marginTop = '12px';
        errorEl.innerHTML = `<span class="material-symbols-outlined">error</span> ${message}`;
        element.appendChild(errorEl);
    } else {
        errorEl.innerHTML = `<span class="material-symbols-outlined">error</span> ${message}`;
    }
}

// Add error to validation summary
// Inline red highlights are the only error UI; no summary list.
function addToValidationSummary(message) {
    console.warn('Validation:', message);
}

function validateLessonForm(shouldClear = true) {
    if (shouldClear) clearValidationErrors();
    let hasErrors = false;
    let errorMessages = [];

    const contentCards = document.querySelectorAll('.content-card');
    const questions = document.querySelectorAll('.quiz-question');
    const hasContent = contentCards.length > 0;
    const hasQuiz = questions.length > 0;

    if (!hasContent && !hasQuiz) {
        errorMessages.push('Please add at least one content slide OR one quiz question.');
        hasErrors = true;
        const contentSection = document.getElementById('contentCards')?.closest('div');
        if (contentSection) {
            contentSection.classList.add('section-error');
        }
        const quizSection = document.getElementById('quizQuestions')?.closest('div');
        if (quizSection) {
            quizSection.classList.add('section-error');
        }
    }

    // Validate Lesson Content Cards
    contentCards.forEach((card, index) => {
        const cardNum = index + 1;
        const typeSelect = card.querySelector('.content-type');
        const contentType = typeSelect ? typeSelect.value : 'text';
        const titleInput = card.querySelector('input[name*="[title]"]');
        const contentText = card.querySelector('textarea[name*="[content_text]"]');
        const mediaInput = card.querySelector('input[name*="[existing_media]"]');
        const mediaWidget = card.querySelector('.media-upload-widget');
        let cardHasError = false;

        if (!titleInput || !titleInput.value.trim()) {
            showFieldError(titleInput, 'Please enter a title for this slide');
            errorMessages.push(`Content Slide ${cardNum}: Missing title`);
            cardHasError = true;
            hasErrors = true;
        }

        if (contentType === 'text') {
            if (!contentText || !contentText.value.trim()) {
                showFieldError(contentText, 'Please add content text for this slide');
                errorMessages.push(`Content Slide ${cardNum}: Missing content text`);
                cardHasError = true;
                hasErrors = true;
            }
        } else if (contentType === 'gesture_demo') {
            const gestureName = card.querySelector('input[name*="[gesture_name]"]');
            if (!gestureName || !gestureName.value.trim()) {
                showFieldError(gestureName, 'Please enter a gesture name');
                errorMessages.push(`Content Slide ${cardNum}: Missing gesture name`);
                cardHasError = true;
                hasErrors = true;
            }
        } else if (contentType === 'image' || contentType === 'video') {
            if (!mediaInput || !mediaInput.value.trim()) {
                if (mediaWidget) {
                    mediaWidget.style.borderColor = '#EF4444';
                    mediaWidget.style.borderWidth = '2px';
                    const existingError = mediaWidget.parentElement.querySelector('.error-message');
                    if (existingError) existingError.remove();
                    const errorEl = document.createElement('div');
                    errorEl.className = 'error-message';
                    errorEl.innerHTML = `<span class="material-symbols-outlined">error</span> Please upload a ${contentType}`;
                    mediaWidget.parentElement.appendChild(errorEl);
                }
                errorMessages.push(`Content Slide ${cardNum}: Missing ${contentType}`);
                cardHasError = true;
                hasErrors = true;
            }
        } else if (contentType === 'youtube_video') {
            const ytInput = card.querySelector('.youtube-url-input');
            const ytUrl = ytInput ? ytInput.value.trim() : '';
            if (!ytUrl) {
                showFieldError(ytInput, 'Please enter a YouTube video URL');
                errorMessages.push(`Content Slide ${cardNum}: Missing YouTube URL`);
                cardHasError = true;
                hasErrors = true;
            } else if (!extractYoutubeId(ytUrl)) {
                showFieldError(ytInput, 'Invalid YouTube URL. Use a youtube.com or youtu.be link.');
                errorMessages.push(`Content Slide ${cardNum}: Invalid YouTube URL`);
                cardHasError = true;
                hasErrors = true;
            }
        }

        if (cardHasError) {
            card.classList.add('section-error');
        }
    });

    // Validate Quiz Questions
    questions.forEach((question, index) => {
        const questionNum = index + 1;
        const questionInput = question.querySelector('input[name*="[question]"]');
        let questionHasError = false;

        if (!questionInput || !questionInput.value.trim()) {
            showFieldError(questionInput, 'Please enter a question');
            errorMessages.push(`Quiz Question ${questionNum}: Missing question text`);
            questionHasError = true;
            hasErrors = true;
        }

        const typeSelect = question.querySelector('.question-type');
        const questionType = typeSelect ? typeSelect.value : 'multiple_choice';

        if (questionType === 'multiple_choice' || questionType === 'true_false') {
            const options = question.querySelectorAll('.option-row');
            const hasValidOption = Array.from(options).some(opt => {
                const textInput = opt.querySelector('.option-text-input');
                const imageInput = opt.querySelector('input[name*="[existing_image]"]');
                return (textInput && textInput.value.trim()) || (imageInput && imageInput.value.trim());
            });

            if (options.length < 2) {
                const optionsContainer = question.querySelector('.options-container');
                const errorMsg = `Need at least 2 options for ${questionType === 'true_false' ? 'True/False' : 'Multiple Choice'}`;
                showSectionError(optionsContainer, errorMsg);
                errorMessages.push(`Quiz Question ${questionNum}: ${errorMsg}`);
                questionHasError = true;
                hasErrors = true;
            } else if (!hasValidOption) {
                const optionsContainer = question.querySelector('.options-container');
                showSectionError(optionsContainer, 'Each option needs text OR an image');
                errorMessages.push(`Quiz Question ${questionNum}: Options need text or images`);
                questionHasError = true;
                hasErrors = true;
            }

            const correctRadio = question.querySelector('input[type="radio"]:checked');
            if (!correctRadio) {
                const optionsContainer = question.querySelector('.options-container');
                showSectionError(optionsContainer, 'Please select the correct answer');
                errorMessages.push(`Quiz Question ${questionNum}: No correct answer selected`);
                questionHasError = true;
                hasErrors = true;
            }

        } else if (questionType === 'drag_drop') {
            const pairs = question.querySelectorAll('.drag-drop-pair');
            const dragDropContainer = question.querySelector('.drag-drop-container');

            if (pairs.length < 2) {
                showSectionError(dragDropContainer, 'Need at least 2 drag & drop pairs');
                errorMessages.push(`Quiz Question ${questionNum}: Need at least 2 drag & drop pairs`);
                questionHasError = true;
                hasErrors = true;
            }

            pairs.forEach((pair, pairIndex) => {
                const leftText = pair.querySelector('input[name*="[left_text]"]');
                const rightText = pair.querySelector('input[name*="[right_text]"]');
                const leftImage = pair.querySelector('input[name*="[left_image]"]');
                const rightImage = pair.querySelector('input[name*="[right_image]"]');

                const hasLeftContent = (leftText && leftText.value.trim()) || (leftImage && leftImage.value.trim());
                const hasRightContent = (rightText && rightText.value.trim()) || (rightImage && rightImage.value.trim());

                if (!hasLeftContent) {
                    showFieldError(leftText, 'Left item needs text OR an image');
                    errorMessages.push(`Quiz Question ${questionNum}, Pair ${pairIndex + 1}: Left item missing content`);
                    questionHasError = true;
                    hasErrors = true;
                }
                if (!hasRightContent) {
                    showFieldError(rightText, 'Right item needs text OR an image');
                    errorMessages.push(`Quiz Question ${questionNum}, Pair ${pairIndex + 1}: Right item missing content`);
                    questionHasError = true;
                    hasErrors = true;
                }
            });

        } else if (questionType === 'gesture') {
            const moduleSelect = question.querySelector('.gesture-module-select');
            const selectedGestures = question.querySelectorAll('.gesture-checkbox:checked');
            const gestureContainer = question.querySelector('.gesture-quiz-container');

            if (!moduleSelect || !moduleSelect.value) {
                showFieldError(moduleSelect, 'Please select a gesture module');
                errorMessages.push(`Quiz Question ${questionNum}: No gesture module selected`);
                questionHasError = true;
                hasErrors = true;
            }

            if (selectedGestures.length === 0) {
                const checkboxesContainer = question.querySelector('#gestureCheckboxes_' + index);
                if (checkboxesContainer) {
                    const existingError = checkboxesContainer.parentElement.querySelector('.error-message');
                    if (existingError) existingError.remove();
                    const errorEl = document.createElement('div');
                    errorEl.className = 'error-message';
                    errorEl.innerHTML = `<span class="material-symbols-outlined">error</span> Please select at least one gesture`;
                    checkboxesContainer.parentElement.appendChild(errorEl);
                }
                errorMessages.push(`Quiz Question ${questionNum}: No gestures selected`);
                questionHasError = true;
                hasErrors = true;
            }
        }

        if (questionHasError) {
            question.classList.add('section-error');
        }
    });

    if (hasErrors) {
        errorMessages.forEach(msg => addToValidationSummary(msg));
    }

    return !hasErrors;
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
        toast.style.cssText = 'position:fixed;bottom:28px;right:28px;background:linear-gradient(135deg,#0d326b,#1a6fd4);color:white;padding:14px 22px;border-radius:16px;font-weight:700;font-size:14px;box-shadow:0 8px 30px rgba(13,50,107,0.4);z-index:20000;transition:all 0.4s;';
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
    <div style="background:white;border-radius:24px;padding:32px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(13,50,107,0.2);position:relative;">
        <button onclick="closeAiQuizModal()" type="button"
                style="position:absolute;top:16px;right:16px;background:rgba(13,50,107,0.07);border:none;width:32px;height:32px;border-radius:9px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;color:#64748b;">✕</button>

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
            <div style="width:42px;height:42px;background:linear-gradient(135deg,#0d326b,#1a6fd4);border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">✨</div>
            <div>
                <h3 style="font-size:17px;font-weight:800;color:#0f3172;margin:0;">Generate Quiz with AI</h3>
                <p style="font-size:12px;color:#64748b;margin:2px 0 0;">Based on your lesson content</p>
            </div>
        </div>

        <div style="background:#F0F7FF;border-radius:12px;padding:10px 14px;margin:14px 0 20px;display:flex;gap:8px;align-items:flex-start;">
            <span style="font-size:16px;flex-shrink:0;">🇵🇭</span>
            <p style="font-size:11px;color:#0d326b;font-weight:600;margin:0;line-height:1.5;">AI will read your lesson content and create quiz questions about the FSL concepts you wrote.</p>
        </div>

        <div id="aqm_error" style="display:none;background:#FEF2F2;border:1.5px solid #FCA5A5;border-radius:12px;padding:10px 14px;margin-bottom:16px;color:#B91C1C;font-size:13px;font-weight:600;"></div>

        <div id="aqm_loading" style="display:none;text-align:center;padding:24px 8px 8px;">
            <div style="display:inline-block;width:40px;height:40px;border:4px solid rgba(13,50,107,0.15);border-top-color:#0d326b;border-radius:50%;animation:aiSpin 0.8s linear infinite;"></div>
            <p style="color:#0d326b;font-weight:700;font-size:14px;margin:14px 0 4px;">Generating quiz questions...</p>
            <p style="color:#94a3b8;font-size:12px;margin:0 0 16px;">AI is reading your lesson content.<br>This may take up to 30 seconds.</p>
            <div style="max-width:260px;margin:0 auto;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:12px;font-weight:600;color:#64748b;">Progress</span>
                    <span id="aqm_progressPct" style="font-size:13px;font-weight:800;color:#0d326b;">0%</span>
                </div>
                <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden;">
                    <div id="aqm_progressBar" style="background:linear-gradient(90deg,#0d326b,#1a6fd4);height:100%;width:0%;border-radius:99px;transition:width 0.4s ease;"></div>
                </div>
            </div>
        </div>

        <div id="aqm_form">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
            <div>
                <label style="display:block;font-size:13px;font-weight:700;color:#475569;margin-bottom:6px;">Multiple Choice Qs</label>
                <input id="aqm_num_mc" type="number" min="0" max="15" value="2"
                       style="width:100%;padding:12px 16px;border:1.5px solid #e2e8f0;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#e2e8f0';">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:700;color:#475569;margin-bottom:6px;">True / False Qs</label>
                <input id="aqm_num_tf" type="number" min="0" max="15" value="1"
                       style="width:100%;padding:12px 16px;border:1.5px solid #e2e8f0;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#e2e8f0';">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:700;color:#475569;margin-bottom:6px;">Drag & Drop Qs</label>
                <input id="aqm_num_dd" type="number" min="0" max="15" value="1"
                       style="width:100%;padding:12px 16px;border:1.5px solid #e2e8f0;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#e2e8f0';">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:700;color:#475569;margin-bottom:6px;">Gesture Qs</label>
                <input id="aqm_num_gt" type="number" min="0" max="15" value="1"
                       style="width:100%;padding:12px 16px;border:1.5px solid #e2e8f0;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#e2e8f0';">
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;">
            <button id="aqm_generateBtn" onclick="submitAiQuizGenerate()" type="button"
                    style="background:linear-gradient(135deg,#0d326b,#1a6fd4);color:white;padding:14px 24px;border-radius:14px;font-weight:800;font-size:15px;border:none;cursor:pointer;width:100%;transition:all 0.2s;box-shadow:0 5px 20px rgba(13,50,107,0.35);"
                    onmouseover="if(!this.disabled){this.style.transform='translateY(-1px)'}"
                    onmouseout="this.style.transform=''">
                ✨ Generate Questions
            </button>
            <button onclick="closeAiQuizModal()" type="button"
                    style="background:white;color:#64748b;padding:13px 24px;border-radius:14px;font-weight:700;font-size:14px;border:1.5px solid #e2e8f0;cursor:pointer;width:100%;transition:all 0.2s;"
                    onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='white';">Cancel</button>
        </div>
        </div>
    </div>
</div>

@include('lessons.partials.ai-generator-modal')
@endsection