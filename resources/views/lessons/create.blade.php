@extends('layouts.app')
@section('title', 'Create New Lesson')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
<style>
    body, .max-w-4xl * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

    .material-symbols-outlined {
        font-family: 'Material Symbols Outlined';
        font-weight: normal;
        font-style: normal;
        font-size: 20px;
        line-height: 1;
        letter-spacing: normal;
        text-transform: none;
        display: inline-block;
        white-space: nowrap;
        word-wrap: normal;
        direction: ltr;
        -webkit-font-feature-settings: 'liga';
        -webkit-font-smoothing: antialiased;
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20;
        vertical-align: middle;
    }

    .section-card {
        background: white;
        border-radius: 22px;
        padding: 26px;
        box-shadow: 0 2px 16px rgba(15,49,114,0.07);
        border: 1px solid rgba(15,49,114,0.06);
        margin-bottom: 24px;
    }
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }
    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 17px;
        font-weight: 800;
        color: #0f3172;
    }
    .section-icon {
        width: 36px;
        height: 36px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }
    .field-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }
    .field-input, .field-select, .field-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #E5EAF2;
        border-radius: 14px;
        outline: none;
        transition: all 0.2s;
        font-size: 14px;
        background: #FAFBFD;
    }
    .field-input:focus, .field-select:focus, .field-textarea:focus {
        border-color: #1848c8;
        background: white;
        box-shadow: 0 0 0 4px rgba(24,72,200,0.08);
    }
    .badge-pill {
        font-size: 11px;
        font-weight: 800;
        padding: 4px 12px;
        border-radius: 99px;
        letter-spacing: 0.3px;
    }
    .step-circle {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        background: #1848c8;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        flex-shrink: 0;
    }
    .content-card {
        border: 1.5px solid #E5EAF2;
        border-radius: 18px;
        padding: 22px;
        background: #FAFBFD;
        position: relative;
        transition: border-color 0.2s;
    }
    .content-card:focus-within { border-color: rgba(24,72,200,0.3); }
    .icon-btn-remove {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(239,68,68,0.08);
        color: #EF4444;
        transition: all 0.2s;
    }
    .icon-btn-remove:hover { background: rgba(239,68,68,0.15); }

    .dashed-add-btn {
        width: 100%;
        padding: 16px;
        border: 2px dashed rgba(24,72,200,0.25);
        border-radius: 18px;
        color: #1848c8;
        font-weight: 700;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        background: rgba(24,72,200,0.02);
    }
    .dashed-add-btn:hover { background: rgba(24,72,200,0.06); border-color: rgba(24,72,200,0.4); }

    .quiz-question {
        border: 1.5px solid #E5EAF2;
        border-radius: 18px;
        padding: 22px;
        background: #FAFBFD;
        margin-bottom: 16px;
    }

    /* --- Option rows with image upload --- */
    .option-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        background: white;
        border: 1.5px solid #E5EAF2;
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 8px;
    }
    .option-letter {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(15,49,114,0.08);
        color: #1848c8;
        font-weight: 800;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 4px;
    }
    .option-body { flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .option-text-input {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #E5EAF2;
        border-radius: 11px;
        font-size: 14px;
        outline: none;
    }
    .option-text-input:focus { border-color: #1848c8; }
    .option-image-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .option-image-input {
        font-size: 12px;
        flex: 1;
        color: #6B7280;
    }
    .option-image-preview {
        width: 44px;
        height: 44px;
        border-radius: 9px;
        object-fit: cover;
        border: 1.5px solid #E5EAF2;
        display: none;
        flex-shrink: 0;
    }
    .option-correct-row {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        padding-top: 4px;
    }
    .option-correct-row label {
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
    }
    .option-correct-row input[type="radio"] {
        width: 18px;
        height: 18px;
        accent-color: #1848c8;
    }
    .option-remove-btn {
        background: none;
        border: none;
        color: #CBD5E1;
        cursor: pointer;
        flex-shrink: 0;
        margin-top: 4px;
        transition: color 0.2s;
    }
    .option-remove-btn:hover { color: #EF4444; }

    /* ── Drag & Drop Pair Styles ────────────────────────────────────────── */
    .drag-drop-pair {
        display: flex;
        gap: 12px;
        align-items: center;
        background: white;
        border: 1.5px solid #E5EAF2;
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .drag-drop-pair .pair-side {
        flex: 1;
        min-width: 120px;
    }
    .drag-drop-pair .pair-side label {
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        display: block;
        margin-bottom: 4px;
    }
    .drag-drop-pair .pair-side input[type="text"] {
        padding: 8px 12px;
        font-size: 13px;
        width: 100%;
        border: 1.5px solid #E5EAF2;
        border-radius: 11px;
        outline: none;
        transition: border-color 0.2s;
        background: white;
    }
    .drag-drop-pair .pair-side input[type="text"]:focus {
        border-color: #1848c8;
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

    /* ── Gesture Selection Styles ────────────────────────────────────── */
    .gesture-checkbox-label {
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
    }
    .gesture-checkbox-label:hover:not(.selected) {
        border-color: #1848c8;
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

    /* ── AJAX Upload Widget ─────────────────────────────────────────── */
    .media-upload-widget {
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        padding: 12px;
        background: #f8fafc;
        transition: border-color 0.2s, background 0.2s;
        position: relative;
    }
    .media-upload-widget.has-file {
        border-color: #1848c8;
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
        position: relative;
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
        width: 16px;
        height: 16px;
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
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    .media-upload-widget.has-file .media-thumb-wrap { display: flex; }
    .media-upload-widget .media-thumb {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        flex-shrink: 0;
    }
    .media-upload-widget .media-thumb-info {
        font-size: 12px;
        color: #64748b;
    }
    .media-upload-widget .media-thumb-info strong {
        display: block;
        font-size: 12px;
        color: #1e293b;
    }
    .media-upload-widget .media-remove-btn {
        font-size: 11px;
        color: #ef4444;
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 700;
        margin-top: 2px;
    }
    .media-upload-error {
        font-size: 12px;
        color: #dc2626;
        margin-top: 4px;
        display: none;
    }

    /* ── Mobile preview overlay ──────────────────────────────────────── */
    #previewOverlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(15,23,42,0.7); z-index: 9999; overflow-y: auto; display: none; padding: 20px;
    }
    #previewOverlay.active { display: flex; align-items: flex-start; justify-content: center; }
    #previewOverlay .preview-container {
        width: auto; max-width: 900px; margin: 20px auto; background: transparent;
        border-radius: 0; overflow: visible; box-shadow: none; border: none;
        position: relative; min-height: auto;
    }
    #previewOverlay .preview-loading {
        display: flex; align-items: center; justify-content: center;
        height: 400px; color: white; font-size: 16px; font-weight: 600;
    }
    #previewOverlay .preview-close {
        position: fixed; top: 20px; right: 20px; background: white; border: none;
        border-radius: 50%; width: 50px; height: 50px; font-size: 24px; cursor: pointer;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2); z-index: 10000; display: flex;
        align-items: center; justify-content: center; transition: all 0.3s;
    }
    #previewOverlay .preview-close:hover { transform: scale(1.1); background: #f0f0f0; }

    .form-footer {
        position: sticky;
        bottom: 0;
        background: white;
        border-top: 1px solid #E5EAF2;
        padding: 18px 26px;
        border-radius: 22px;
        box-shadow: 0 -4px 20px rgba(15,49,114,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 24px;
    }
    .btn-primary {
        background: #1848c8;
        color: white;
        padding: 13px 28px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.2s;
        box-shadow: 0 5px 16px rgba(24,72,200,0.25);
        border: none;
        cursor: pointer;
    }
    .btn-primary:hover { background: #0f3172; transform: translateY(-1px); }
    .btn-ghost {
        background: white;
        color: #475569;
        padding: 13px 22px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 14px;
        border: 1.5px solid #E5EAF2;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-ghost:hover { background: #F8FAFC; }
    .btn-outline-blue {
        background: white;
        color: #1848c8;
        padding: 13px 22px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 14px;
        border: 1.5px solid #1848c8;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-outline-blue:hover { background: rgba(24,72,200,0.06); }

/* Error styles */
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

.section-error .section-title {
    color: #EF4444 !important;
}


    

    .hidden { display: none !important; }
</style>

<div class="max-w-4xl mx-auto pb-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold text-[#0f3172]">Create New Lesson</h2>
            <p class="text-slate-500 text-sm mt-1">Build your lesson content and quiz questions</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="openAiModal()"
                    style="background:linear-gradient(135deg,#6d28d9,#4f46e5);color:white;padding:12px 22px;border-radius:14px;font-weight:800;font-size:14px;border:none;cursor:pointer;display:flex;align-items:center;gap:8px;box-shadow:0 5px 18px rgba(109,40,217,0.35);transition:all 0.2s;"
                    onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(109,40,217,0.45)';"
                    onmouseout="this.style.transform='';this.style.boxShadow='0 5px 18px rgba(109,40,217,0.35)';">
                ✨ Generate with AI
            </button>
            <button onclick="window.location.href='{{ route('lessons.index') }}'" class="btn-ghost">
                Cancel
            </button>
        </div>
    </div>

    <form action="{{ route('lessons.store') }}" method="POST" enctype="multipart/form-data" id="lessonForm">
        @csrf

        <!-- Hidden field carrying the clicked button's status (draft / published) -->
        <input type="hidden" name="status" id="lessonStatusField" value="draft">

        @if ($errors->any())
            <div class="section-card" style="border-color:#FCA5A5; background:#FEF2F2;">
                <p class="text-sm font-bold text-red-700 mb-2">Please fix the following:</p>
                <ul class="text-sm text-red-600 list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ============ LESSON DETAILS ============ -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon" style="background: rgba(24,72,200,0.1); color:#1848c8;">📝</div>
                    Lesson Details
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="field-label">Lesson Title *</label>
                    <input type="text" name="title" required class="field-input">
                </div>
                <div>
                    <label class="field-label">Description</label>
                    <textarea name="description" rows="3" class="field-textarea"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Difficulty</label>
                        <select name="difficulty" class="field-select">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Lesson Type</label>
                        <select name="lesson_type" class="field-select">
                            <option value="gesture">Gesture Lesson</option>
                            <option value="interactive">Interactive Lesson</option>
                        </select>
                    </div>
                </div>
                <p class="text-xs text-slate-400">Choose a module now or leave unassigned until publish.</p>
            </div>
        </div>

        <!-- ============ MODULE ============ -->
        @php
            $preselectedModuleId = old('module_id', request()->query('module_id'));
            $moduleActionDefault = old('module_action', $preselectedModuleId ? 'existing' : 'none');
        @endphp
        <div class="section-card" @if($preselectedModuleId) style="border-color:rgba(99,102,241,0.35);background:rgba(99,102,241,0.02);" @endif>
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon" style="background: rgba(99,102,241,0.12); color:#6366F1;">📁</div>
                    Module
                    @if($preselectedModuleId)
                        @php $preModule = $modules->firstWhere('module_id', $preselectedModuleId); @endphp
                        @if($preModule)
                            <span style="background:#EEF2FF;color:#4F46E5;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;margin-left:4px;">
                                📌 {{ $preModule->title }}
                            </span>
                        @endif
                    @endif
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="field-label">Module assignment</label>
                    <select name="module_action" id="moduleAction" class="field-select" onchange="toggleModuleFields()">
                        <option value="none" {{ $moduleActionDefault === 'none' ? 'selected' : '' }}>No module (assign when publishing)</option>
                        <option value="existing" {{ $moduleActionDefault === 'existing' ? 'selected' : '' }}>Use existing module</option>
                        <option value="new" {{ $moduleActionDefault === 'new' ? 'selected' : '' }}>Create new module</option>
                    </select>
                </div>
                <div id="existingModuleFields" class="hidden">
                    <label class="field-label">Select module</label>
                    <select name="module_id" id="moduleIdSelect" class="field-select">
                        <option value="">Choose a module</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->module_id }}" {{ (string) $preselectedModuleId === (string) $module->module_id ? 'selected' : '' }}>
                                {{ $module->title }}
                            </option>
                        @endforeach
                    </select>
                    @if($modules->isEmpty())
                        <p class="text-xs text-amber-600 mt-2">No modules yet — choose "Create new module" above.</p>
                    @endif
                </div>
                <div id="newModuleFields" class="hidden space-y-3">
                    <div>
                        <label class="field-label">New module title *</label>
                        <input type="text" name="new_module[title]" id="newModuleTitle" value="{{ old('new_module.title') }}" class="field-input" placeholder="e.g., FSL Alphabet Basics">
                    </div>
                    <div>
                        <label class="field-label">New module description</label>
                        <textarea name="new_module[description]" rows="2" class="field-textarea" placeholder="Optional description">{{ old('new_module.description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ LESSON CONTENT ============ -->
        <div class="section-card" id="contentContainer">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon" style="background: rgba(5,150,105,0.1); color:#059669;">📖</div>
                    Lesson Content
                </div>
                <button type="button" onclick="addContentCard()" class="text-sm text-[#1848c8] font-bold hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">add</span> Add Slide
                </button>
            </div>
            <div id="contentCards" class="space-y-4">
                <div class="content-card">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="step-circle step-number">1</div>
                            <span class="badge-pill" style="background: rgba(24,72,200,0.1); color:#1848c8;">Text</span>
                        </div>
                        <button type="button" onclick="removeContentCard(this)" class="icon-btn-remove">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="field-label">Content Type</label>
                            <select name="contents[0][content_type]" class="content-type field-select" onchange="toggleFields(this)">
                                <option value="text">Text</option>
                                <option value="gesture_demo">Gesture Demo</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Title</label>
                            <input type="text" name="contents[0][title]" class="field-input" placeholder="e.g., Introduction to FSL Alphabet">
                        </div>
                        <div>
                            <label class="field-label">Content</label>
                            <textarea name="contents[0][content_text]" rows="3" class="field-textarea" placeholder="Write your lesson content here..."></textarea>
                        </div>
                        <div class="gesture-field hidden">
                            <label class="field-label">Gesture Name</label>
                            <input type="text" name="contents[0][gesture_name]" class="field-input" placeholder="e.g., letter_a">
                        </div>
                        <div class="media-field hidden">
                            <label class="field-label">Upload Media</label>
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
            </div>
            <button type="button" onclick="addContentCard()" class="dashed-add-btn mt-4">
                <span class="material-symbols-outlined text-sm">add</span> Add Another Slide
            </button>
        </div>

        <!-- ============ QUIZ QUESTIONS ============ -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon" style="background: rgba(245,158,11,0.12); color:#D97706;">📝</div>
                    Quiz Questions
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="aiQuizGenerateBtn" onclick="openAiQuizModal()"
                            title="Add lesson content first to enable AI quiz generation"
                            style="background:linear-gradient(135deg,#6d28d9,#4f46e5);color:white;padding:8px 16px;border-radius:11px;font-weight:700;font-size:12px;border:none;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all 0.2s;opacity:0.4;pointer-events:none;"
                            onmouseover="if(!this.disabled&&this.style.opacity==='1'){this.style.transform='translateY(-1px)'}"
                            onmouseout="this.style.transform=''">
                        ✨ Generate Quiz with AI
                    </button>
                    <button type="button" onclick="addQuizQuestion()" class="text-sm text-[#1848c8] font-bold hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span> Add Question
                    </button>
                </div>
            </div>

            <!-- QUESTIONS CONTAINER -->
            <div id="quizQuestions">
                <!-- Question 1 - Initial Question -->
                <div class="quiz-question">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="step-circle" style="background:#D97706;">1</div>
                            <span class="text-sm font-bold text-slate-500 question-label">Question 1</span>
                        </div>
                        <button type="button" onclick="removeQuizQuestion(this)" class="icon-btn-remove">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="field-label">Question</label>
                            <input type="text" name="quiz[0][question]" class="field-input" placeholder="Enter your question">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="field-label">Question Type</label>
                                <select name="quiz[0][type]" onchange="handleQuestionTypeChange(this)" class="field-select question-type">
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="true_false">True / False</option>
                                    <option value="drag_drop">Drag and Drop</option>
                                    <option value="gesture">Gesture Recognition</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Question Image (Optional)</label>
                                <input type="hidden" name="quiz[0][existing_media]" value="" class="media-path-input">
                                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*" style="padding:10px;">
                                    <div class="upload-trigger" style="gap:8px;">
                                        <input type="file" accept="image/*" class="ajax-file-input" onchange="handleAjaxUpload(this,'quiz_media')">
                                        <span class="upload-icon material-symbols-outlined" style="font-size:18px;color:#94a3b8;">cloud_upload</span>
                                        <div class="upload-spinner"></div>
                                        <span class="upload-label" style="font-size:12px;">Upload image</span>
                                    </div>
                                    <div class="media-thumb-wrap" style="margin-top:8px;"></div>
                                    <div class="media-upload-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="options-container">
                            <label class="field-label">Answer Options</label>
                            <p class="text-xs text-slate-400 mb-2">Each option can have text and/or an image (e.g. for FSL hand-sign choices).</p>
                            <div class="space-y-2 options-list">
                                <div class="option-row">
                                    <div class="option-letter">A</div>
                                    <div class="option-body">
                                        <input type="text" name="quiz[0][options][0][text]" class="option-text-input" placeholder="Option A text">
                                        <div class="option-image-row">
                                            <input type="hidden" name="quiz[0][options][0][existing_image]" value="" class="media-path-input">
                                            <img class="option-image-preview" src="" alt="">
                                            <label class="text-xs font-semibold cursor-pointer hover:underline flex items-center gap-1" style="color:#1848c8;flex-shrink:0;">
                                                <span class="material-symbols-outlined" style="font-size:16px;">add_photo_alternate</span> Add image
                                                <input type="file" accept="image/*" class="option-image-input hidden" onchange="handleOptionImageUpload(this)">
                                            </label>
                                            <span class="option-upload-spinner" style="display:none;font-size:11px;color:#6366f1;">Uploading…</span>
                                        </div>
                                    </div>
                                    <div class="option-correct-row">
                                        <input type="radio" name="quiz[0][correct]" value="0">
                                        <label>Correct</label>
                                    </div>
                                    <button type="button" class="option-remove-btn" onclick="removeOption(this)">
                                        <span class="material-symbols-outlined text-sm">close</span>
                                    </button>
                                </div>
                                <div class="option-row">
                                    <div class="option-letter">B</div>
                                    <div class="option-body">
                                        <input type="text" name="quiz[0][options][1][text]" class="option-text-input" placeholder="Option B text">
                                        <div class="option-image-row">
                                            <input type="hidden" name="quiz[0][options][1][existing_image]" value="" class="media-path-input">
                                            <img class="option-image-preview" src="" alt="">
                                            <label class="text-xs font-semibold cursor-pointer hover:underline flex items-center gap-1" style="color:#1848c8;flex-shrink:0;">
                                                <span class="material-symbols-outlined" style="font-size:16px;">add_photo_alternate</span> Add image
                                                <input type="file" accept="image/*" class="option-image-input hidden" onchange="handleOptionImageUpload(this)">
                                            </label>
                                            <span class="option-upload-spinner" style="display:none;font-size:11px;color:#6366f1;">Uploading…</span>
                                        </div>
                                    </div>
                                    <div class="option-correct-row">
                                        <input type="radio" name="quiz[0][correct]" value="1">
                                        <label>Correct</label>
                                    </div>
                                    <button type="button" class="option-remove-btn" onclick="removeOption(this)">
                                        <span class="material-symbols-outlined text-sm">close</span>
                                    </button>
                                </div>
                            </div>
                            <button type="button" onclick="addOption(this)" class="text-sm text-[#1848c8] font-bold hover:underline mt-2">
                                + Add Option
                            </button>
                        </div>

                        <!-- Drag and Drop Pairs -->
                        <div class="drag-drop-container hidden">
                            <label class="field-label">Drag and Drop Pairs</label>
                            <p class="text-xs text-slate-400 mb-2">Match items from the left column to the right column.</p>
                            <div class="space-y-2 drag-drop-pairs-list"></div>
                            <button type="button" onclick="addDragDropPair(this)" class="text-sm text-[#1848c8] font-bold hover:underline mt-2">
                                + Add Pair
                            </button>
                        </div>

                        <!-- Gesture Recognition Fields -->
                        <div class="gesture-quiz-container hidden">
                            <label class="field-label">Gesture Recognition Settings</label>
                            <p class="text-xs text-slate-400 mb-2">Select a gesture module and the specific gestures students need to perform.</p>
                            <div class="space-y-3">
                                <div>
                                    <label class="field-label">Gesture Module</label>
                                    <select name="quiz[0][gesture_module_id]" class="field-select gesture-module-select" onchange="loadGesturesForModule(this, 0)">
                                        <option value="">Select a module...</option>
                                        @foreach($gestureModules as $module)
                                            <option value="{{ $module->module_id }}">{{ $module->display_name ?? $module->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="field-label">Select Gestures to Recognize</label>
                                    <p class="text-xs text-slate-400 mb-2">Click to select gestures. Students will need to perform all selected gestures.</p>
                                    <div id="gestureCheckboxes_0" class="flex flex-wrap gap-2 mt-2" style="min-height:60px;">
                                        <span class="text-sm text-slate-400">Select a module first</span>
                                    </div>
                                </div>
                                <div class="selected-gestures-preview" style="display:none;">
                                    <label class="field-label">Selected Gestures</label>
                                    <div class="flex flex-wrap gap-2" id="selectedGestureTags_0"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ADD ANOTHER QUESTION BUTTON - OUTSIDE #quizQuestions -->
            <button type="button" onclick="addQuizQuestion()" class="dashed-add-btn mt-2">
                <span class="material-symbols-outlined text-sm">add</span> Add Another Question
            </button>
        </div>

        <div class="form-footer">
            <button type="button" onclick="openPreview()" class="btn-outline-blue flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">visibility</span> Preview
            </button>
            <div class="flex gap-3">
                <button type="submit" data-status="draft" class="btn-ghost">💾 Save Draft</button>
                <button type="submit" data-status="published" class="btn-primary">🚀 Publish Lesson</button>
            </div>
        </div>
    </form>
</div>

<!-- Mobile-only preview overlay (phone frame) -->
<div id="previewOverlay">
    <button class="preview-close" onclick="closePreview()">✕</button>
    <div class="preview-container" id="previewContent">
        <div class="preview-loading">Loading preview...</div>
    </div>
</div>

<script>
const UPLOAD_URL = '{{ route('lessons.upload-media') }}';
const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]')?.content
                 || document.querySelector('input[name="_token"]')?.value;
let contentIndex = 1;
let quizIndex = 1;

/* ═══════════════════════════════════════════════════════
   AJAX upload helpers
═══════════════════════════════════════════════════════ */

async function handleAjaxUpload(input, type) {
    if (!input.files || !input.files[0]) return;
    const file   = input.files[0];
    const widget = input.closest('.media-upload-widget');
    if (!widget) return;
    const context   = widget.dataset.context || 'lesson_media';
    const errorEl   = widget.querySelector('.media-upload-error');
    const thumbWrap = widget.querySelector('.media-thumb-wrap');
    const label     = widget.querySelector('.upload-label');
    widget.classList.add('uploading');
    widget.classList.remove('has-file');
    if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }

    let pathInput = widget.closest('div')?.querySelector('.media-path-input');
    if (!pathInput) {
        pathInput = widget.closest('.quiz-question')?.querySelector('input[name*="[existing_media]"]');
    }

    const fd = new FormData();
    fd.append('file', file); fd.append('context', context); fd.append('_token', CSRF_TOKEN);
    try {
        const resp = await fetch(UPLOAD_URL, { method: 'POST', body: fd });
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.message || 'Upload failed');
        if (pathInput) pathInput.value = data.path;
        widget.classList.remove('uploading');
        widget.classList.add('has-file');
        if (label) label.textContent = 'Click to replace file';
        if (thumbWrap) {
            thumbWrap.innerHTML = '';
            const isVid = file.type.startsWith('video/');
            if (!isVid) {
                const img = document.createElement('img');
                img.className = 'media-thumb';
                img.src = data.url;
                thumbWrap.appendChild(img);
            } else {
                thumbWrap.innerHTML = '<span class="material-symbols-outlined" style="font-size:40px;color:#94a3b8;">videocam</span>';
            }
            const info = document.createElement('div');
            info.className = 'media-thumb-info';
            info.innerHTML = `<strong>Uploaded</strong>${file.name}<button type="button" class="media-remove-btn" onclick="clearMediaWidget(this)">✕ Remove</button>`;
            thumbWrap.appendChild(info);
        }
    } catch (err) {
        widget.classList.remove('uploading');
        if (errorEl) { errorEl.textContent = '⚠ ' + err.message; errorEl.style.display = 'block'; }
    }
    input.value = '';
}

async function handleOptionImageUpload(input) {
    if (!input.files || !input.files[0]) return;
    const file    = input.files[0];
    const optBody = input.closest('.option-body');
    if (!optBody) return;
    const spinner   = optBody.querySelector('.option-upload-spinner');
    const preview   = optBody.querySelector('.option-image-preview');
    const pathInput = optBody.querySelector('.media-path-input');
    if (spinner) spinner.style.display = 'inline';
    const fd = new FormData();
    fd.append('file', file); fd.append('context', 'quiz_option_media'); fd.append('_token', CSRF_TOKEN);
    try {
        const resp = await fetch(UPLOAD_URL, { method: 'POST', body: fd });
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.message || 'Upload failed');
        if (pathInput) pathInput.value = data.path;
        if (preview) { preview.src = data.url; preview.style.display = 'block'; }
        let clearBtn = optBody.querySelector('.option-img-clear-btn');
        if (!clearBtn) {
            clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'option-img-clear-btn text-xs font-semibold flex-shrink-0';
            clearBtn.style.color = '#ef4444';
            clearBtn.textContent = '✕';
            clearBtn.onclick = function() { clearOptionImage(this); };
            optBody.querySelector('.option-image-row').appendChild(clearBtn);
        }
    } catch (err) {
        alert('Image upload failed: ' + err.message);
    } finally {
        if (spinner) spinner.style.display = 'none';
        input.value = '';
    }
}

function clearMediaWidget(btn) {
    const widget = btn.closest('.media-upload-widget'); if (!widget) return;
    const thumbWrap = widget.querySelector('.media-thumb-wrap');
    const pathInput = widget.closest('div')?.querySelector('.media-path-input');
    if (thumbWrap) thumbWrap.innerHTML = '';
    if (pathInput) pathInput.value = '';
    const lbl = widget.querySelector('.upload-label'); if (lbl) lbl.textContent = 'Click or drag to upload';
    widget.classList.remove('has-file');
}

function clearOptionImage(btn) {
    const optBody = btn.closest('.option-body'); if (!optBody) return;
    const preview = optBody.querySelector('.option-image-preview');
    const pathInput = optBody.querySelector('.media-path-input');
    if (preview) { preview.src = ''; preview.style.display = 'none'; }
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

/* ═══════════════════════════════════════════════════════
   PREVIEW
═══════════════════════════════════════════════════════ */

function openPreview() {
    const overlay = document.getElementById('previewOverlay');
    const content = document.getElementById('previewContent');
    overlay.classList.add('active');
    content.innerHTML = '<div class="preview-loading">⏳ Preparing preview...</div>';

    const form   = document.getElementById('lessonForm');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                   || document.querySelector('input[name="_token"]')?.value;

    const pendingUploads = [];
    form.querySelectorAll('input[type="file"]').forEach(input => {
        if (input.files && input.files[0]) {
            pendingUploads.push(input);
        }
    });

    const uploadPromises = pendingUploads.map(input => {
        const file = input.files[0];
        const fd   = new FormData();
        fd.append('file', file);
        fd.append('context', 'temp_preview');
        fd.append('_token', csrfToken);
        return fetch('{{ route('lessons.upload-media') }}', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.path) {
                    const name = input.name;
                    let hiddenName;
                    const contentMatch = name.match(/^(contents\[\d+\])\[media\]$/);
                    const quizMediaMatch = name.match(/^(quiz\[\d+\])\[media\]$/);
                    const optionMatch = name.match(/^(quiz\[\d+\]\[options\]\[\d+\])\[image\]$/);
                    const ddLeftMatch = name.match(/^(quiz\[\d+\]\[drag_drop_pairs\]\[\d+\])\[left_image\]$/);
                    const ddRightMatch = name.match(/^(quiz\[\d+\]\[drag_drop_pairs\]\[\d+\])\[right_image\]$/);

                    if (contentMatch)   hiddenName = contentMatch[1]   + '[existing_media]';
                    else if (quizMediaMatch) hiddenName = quizMediaMatch[1] + '[existing_media]';
                    else if (optionMatch)    hiddenName = optionMatch[1]    + '[existing_image]';
                    else if (ddLeftMatch)    hiddenName = ddLeftMatch[1]    + '[left_image]';
                    else if (ddRightMatch)   hiddenName = ddRightMatch[1]   + '[right_image]';

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
            cleanData.append(key, value);
        }

        return fetch('{{ route('lessons.preview') }}', {
            method: 'POST',
            body: cleanData,
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            }
        });
    })
    .then(response => {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.text();
    })
    .then(html => {
        content.innerHTML = html;
        content.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            for (const attr of oldScript.attributes) newScript.setAttribute(attr.name, attr.value);
            newScript.text = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
        form.querySelectorAll('.preview-temp-hidden').forEach(el => el.remove());
    })
    .catch(error => {
        content.innerHTML = '<div class="preview-loading" style="color:#FCA5A5;">Preview failed. Please try again.</div>';
        console.error('Preview error:', error);
        form.querySelectorAll('.preview-temp-hidden').forEach(el => el.remove());
    });
}

function closePreview() {
    document.getElementById('previewOverlay').classList.remove('active');
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closePreview();
});

function toggleModuleFields() {
    const action = document.getElementById('moduleAction')?.value || 'none';
    const existing = document.getElementById('existingModuleFields');
    const fresh = document.getElementById('newModuleFields');
    const moduleSelect = document.getElementById('moduleIdSelect');
    const newTitle = document.getElementById('newModuleTitle');

    if (existing) existing.classList.toggle('hidden', action !== 'existing');
    if (fresh) fresh.classList.toggle('hidden', action !== 'new');

    if (moduleSelect) {
        moduleSelect.disabled = action !== 'existing';
        moduleSelect.required = action === 'existing';
        if (action !== 'existing') moduleSelect.value = '';
    }
    if (newTitle) {
        newTitle.required = action === 'new';
        if (action !== 'new') newTitle.value = '';
    }
}

/* ═══════════════════════════════════════════════════════
   CONTENT CARDS
═══════════════════════════════════════════════════════ */
function toggleFields(select) {
    const card = select.closest('.content-card');
    if (!card) return;
    const gestureField = card.querySelector('.gesture-field');
    const mediaField = card.querySelector('.media-field');
    const typeLabel = card.querySelector('.badge-pill');
    
    // Hide validation indicators
    card.style.borderColor = '#E5EAF2';
    
    if (gestureField) gestureField.classList.add('hidden');
    if (mediaField) mediaField.classList.add('hidden');
    if (typeLabel) {
        const map = { 'text': 'Text', 'gesture_demo': 'Gesture', 'image': 'Image', 'video': 'Video' };
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
    
    // Update AI quiz button state
    updateAiQuizBtnState();
}

function addContentCard() {
    const container = document.getElementById('contentCards');
    const card = document.createElement('div');
    card.className = 'content-card';
    card.innerHTML = `
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="step-circle step-number">${contentIndex + 1}</div>
                <span class="badge-pill" style="background: rgba(24,72,200,0.1); color:#1848c8;">Text</span>
            </div>
            <button type="button" onclick="removeContentCard(this)" class="icon-btn-remove">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="field-label">Content Type</label>
                <select name="contents[${contentIndex}][content_type]" class="content-type field-select" onchange="toggleFields(this)">
                    <option value="text">Text</option>
                    <option value="gesture_demo">Gesture Demo</option>
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div>
                <label class="field-label">Title</label>
                <input type="text" name="contents[${contentIndex}][title]" class="field-input" placeholder="e.g., Introduction to FSL Alphabet">
            </div>
            <div>
                <label class="field-label">Content</label>
                <textarea name="contents[${contentIndex}][content_text]" rows="3" class="field-textarea" placeholder="Write your lesson content here..."></textarea>
            </div>
            <div class="gesture-field hidden">
                <label class="field-label">Gesture Name</label>
                <input type="text" name="contents[${contentIndex}][gesture_name]" class="field-input" placeholder="e.g., letter_a">
            </div>
            <div class="media-field hidden">
                <label class="field-label">Upload Media</label>
                <input type="hidden" name="contents[${contentIndex}][existing_media]" value="" class="media-path-input">
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
    `;
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
   QUIZ - OPTIONS
═══════════════════════════════════════════════════════ */

function getQuizQuestionIndex(questionDiv) {
    const questionInput = questionDiv.querySelector('input[name*="[question]"]');
    if (!questionInput) return 0;
    const match = questionInput.name.match(/^quiz\[(\d+)\]/);
    return match ? parseInt(match[1], 10) : 0;
}

function buildOptionRow(qIndex, optIndex) {
    const letter = String.fromCharCode(65 + optIndex);
    const row = document.createElement('div');
    row.className = 'option-row';
    row.innerHTML = `
        <div class="option-letter">${letter}</div>
        <div class="option-body">
            <input type="text" name="quiz[${qIndex}][options][${optIndex}][text]" class="option-text-input" placeholder="Option ${letter} text">
            <div class="option-image-row">
                <input type="hidden" name="quiz[${qIndex}][options][${optIndex}][existing_image]" value="" class="media-path-input">
                <img class="option-image-preview" src="" alt="">
                <label class="text-xs font-semibold cursor-pointer hover:underline flex items-center gap-1" style="color:#1848c8;flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:16px;">add_photo_alternate</span> Add image
                    <input type="file" accept="image/*" class="option-image-input hidden" onchange="handleOptionImageUpload(this)">
                </label>
                <span class="option-upload-spinner" style="display:none;font-size:11px;color:#6366f1;">Uploading…</span>
            </div>
        </div>
        <div class="option-correct-row">
            <input type="radio" name="quiz[${qIndex}][correct]" value="${optIndex}">
            <label>Correct</label>
        </div>
        <button type="button" class="option-remove-btn" onclick="removeOption(this)">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
    `;
    return row;
}

function relabelOptions(optionsList, qIndex) {
    optionsList.querySelectorAll('.option-row').forEach((row, i) => {
        const letter = String.fromCharCode(65 + i);
        row.querySelector('.option-letter').textContent = letter;
        const textInput = row.querySelector('.option-text-input');
        textInput.name = `quiz[${qIndex}][options][${i}][text]`;
        textInput.placeholder = `Option ${letter} text`;
        const pathInput = row.querySelector('.media-path-input');
        if (pathInput) pathInput.name = `quiz[${qIndex}][options][${i}][existing_image]`;
        const imgInput = row.querySelector('.option-image-input');
        if (imgInput) imgInput.name = `quiz[${qIndex}][options][${i}][image]`;
        const existingImg = row.querySelector('input[type="hidden"][name*="[existing_image]"]');
        if (existingImg) existingImg.name = `quiz[${qIndex}][options][${i}][existing_image]`;
        row.querySelector('input[type="radio"]').name = `quiz[${qIndex}][correct]`;
        row.querySelector('input[type="radio"]').value = i;
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
   QUIZ - DRAG & DROP PAIRS
═══════════════════════════════════════════════════════ */

function buildDragDropPair(qIndex, pairIndex) {
    const pair = document.createElement('div');
    pair.className = 'drag-drop-pair';
    pair.innerHTML = `
        <div class="pair-side">
            <label>Left Item</label>
            <input type="text" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][left_text]" placeholder="e.g., Letter A">
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
        <div class="pair-arrow">
            <span class="material-symbols-outlined">arrow_forward</span>
        </div>
        <div class="pair-side">
            <label>Right Match</label>
            <input type="text" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][right_text]" placeholder="e.g., Hand sign for A">
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
            questionDiv.style.borderColor = '#E5EAF2';
            questionDiv.style.borderWidth = '1.5px';
        }
    });

    return { isValid, errorMsg };
}

/* ═══════════════════════════════════════════════════════
   QUIZ - QUESTION TYPE HANDLER
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
        // Auto-add TWO pairs if none exist
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
        }
    } else if (select.value === 'multiple_choice') {
        if (optionsContainer) optionsContainer.classList.remove('hidden');
        const optionsList = questionDiv.querySelector('.options-list');
        optionsList.querySelectorAll('.option-image-row, .option-remove-btn').forEach(el => el.style.visibility = 'visible');
        const addBtn = optionsContainer.querySelector('button');
        if (addBtn) addBtn.style.display = 'inline-block';
    }
}

/* ═══════════════════════════════════════════════════════
   QUIZ - GESTURE MODULE LOADING
═══════════════════════════════════════════════════════ */

function loadGesturesForModule(select, questionIndex) {
    const moduleId = select.value;
    const questionDiv = select.closest('.quiz-question');
    const checkboxesContainer = document.getElementById(`gestureCheckboxes_${questionIndex}`);
    const previewContainer = questionDiv.querySelector('.selected-gestures-preview');
    const tagsContainer = document.getElementById(`selectedGestureTags_${questionIndex}`);

    if (!moduleId) {
        checkboxesContainer.innerHTML = '<span class="text-sm text-slate-400">Select a module first</span>';
        if (previewContainer) previewContainer.style.display = 'none';
        return;
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
   QUIZ - ADD/REMOVE QUESTIONS
═══════════════════════════════════════════════════════ */

function addQuizQuestion() {
    const container = document.getElementById('quizQuestions');
    const qIndex = quizIndex;
    const question = document.createElement('div');
    question.className = 'quiz-question';
    question.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="step-circle" style="background:#D97706;">${qIndex + 1}</div>
                <span class="text-sm font-bold text-slate-500 question-label">Question ${qIndex + 1}</span>
            </div>
            <button type="button" onclick="removeQuizQuestion(this)" class="icon-btn-remove">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="field-label">Question</label>
                <input type="text" name="quiz[${qIndex}][question]" class="field-input" placeholder="Enter your question">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="field-label">Question Type</label>
                    <select name="quiz[${qIndex}][type]" onchange="handleQuestionTypeChange(this)" class="field-select question-type">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True / False</option>
                        <option value="drag_drop">Drag and Drop</option>
                        <option value="gesture">Gesture Recognition</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Question Image (Optional)</label>
                    <input type="hidden" name="quiz[${qIndex}][existing_media]" value="" class="media-path-input">
                    <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*" style="padding:10px;">
                        <div class="upload-trigger" style="gap:8px;">
                            <input type="file" accept="image/*" class="ajax-file-input" onchange="handleAjaxUpload(this,'quiz_media')">
                            <span class="upload-icon material-symbols-outlined" style="font-size:18px;color:#94a3b8;">cloud_upload</span>
                            <div class="upload-spinner"></div>
                            <span class="upload-label" style="font-size:12px;">Upload image</span>
                        </div>
                        <div class="media-thumb-wrap" style="margin-top:8px;"></div>
                        <div class="media-upload-error"></div>
                    </div>
                </div>
            </div>
            <div class="options-container">
                <label class="field-label">Answer Options</label>
                <p class="text-xs text-slate-400 mb-2">Each option can have text and/or an image (e.g. for FSL hand-sign choices).</p>
                <div class="space-y-2 options-list"></div>
                <button type="button" onclick="addOption(this)" class="text-sm text-[#1848c8] font-bold hover:underline mt-2">
                    + Add Option
                </button>
            </div>

            <!-- Drag and Drop Pairs -->
            <div class="drag-drop-container hidden">
                <label class="field-label">Drag and Drop Pairs</label>
                <p class="text-xs text-slate-400 mb-2">Match items from the left column to the right column.</p>
                <div class="space-y-2 drag-drop-pairs-list"></div>
                <button type="button" onclick="addDragDropPair(this)" class="text-sm text-[#1848c8] font-bold hover:underline mt-2">
                    + Add Pair
                </button>
            </div>

            <!-- Gesture Recognition Fields -->
            <div class="gesture-quiz-container hidden">
                <label class="field-label">Gesture Recognition Settings</label>
                <p class="text-xs text-slate-400 mb-2">Select a gesture module and the specific gestures students need to perform.</p>
                <div class="space-y-3">
                    <div>
                        <label class="field-label">Gesture Module</label>
                        <select name="quiz[${qIndex}][gesture_module_id]" class="field-select gesture-module-select" onchange="loadGesturesForModule(this, ${qIndex})">
                            <option value="">Select a module...</option>
                            @foreach($gestureModules as $module)
                                <option value="{{ $module->module_id }}">{{ $module->display_name ?? $module->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Select Gestures to Recognize</label>
                        <p class="text-xs text-slate-400 mb-2">Click to select gestures. Students will need to perform all selected gestures.</p>
                        <div id="gestureCheckboxes_${qIndex}" class="flex flex-wrap gap-2 mt-2" style="min-height:60px;">
                            <span class="text-sm text-slate-400">Select a module first</span>
                        </div>
                    </div>
                    <div class="selected-gestures-preview" style="display:none;">
                        <label class="field-label">Selected Gestures</label>
                        <div class="flex flex-wrap gap-2" id="selectedGestureTags_${qIndex}"></div>
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
        const stepCircle = questionDiv.querySelector('.step-circle');
        const questionLabel = questionDiv.querySelector('.question-label');
        if (stepCircle) stepCircle.textContent = qIndex + 1;
        if (questionLabel) questionLabel.textContent = `Question ${qIndex + 1}`;

        const questionInput = questionDiv.querySelector('input[name*="[question]"]');
        if (questionInput) questionInput.name = `quiz[${qIndex}][question]`;

        const typeSelect = questionDiv.querySelector('.question-type');
        if (typeSelect) typeSelect.name = `quiz[${qIndex}][type]`;

        const qMediaPath = questionDiv.querySelector('.media-path-input');
        if (qMediaPath) qMediaPath.name = `quiz[${qIndex}][existing_media]`;

        const optionsList = questionDiv.querySelector('.options-list');
        if (optionsList) relabelOptions(optionsList, qIndex);

        // Reindex drag & drop pairs
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
            });
        }

        // Reindex gesture fields
        const gestureContainer = questionDiv.querySelector('.gesture-quiz-container');
        if (gestureContainer) {
            const gestureModuleSelect = gestureContainer.querySelector('.gesture-module-select');
            if (gestureModuleSelect) {
                gestureModuleSelect.name = `quiz[${qIndex}][gesture_module_id]`;
                gestureModuleSelect.setAttribute('onchange', `loadGesturesForModule(this, ${qIndex})`);
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

function openAiQuizModal() {
    const text = getLessonContentText();
    if (text.length < 20) return;
    document.getElementById('aiQuizModal').style.display = 'flex';
    document.getElementById('aqm_error').style.display = 'none';
    document.getElementById('aqm_num_mc').value = 3;
    document.getElementById('aqm_num_tf').value = 2;
}

function closeAiQuizModal() {
    document.getElementById('aiQuizModal').style.display = 'none';
}

// Add this validation function before the form submit handler
function validateLessonContent() {
    let errors = [];
    
    // 1. Check Lesson Content Cards
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
        
        // Check title
        if (!titleInput || !titleInput.value.trim()) {
            errors.push(`Content Slide ${index + 1}: Please add a title.`);
        }
        
        // Check based on content type
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
        
        // Check question text
        if (!questionInput || !questionInput.value.trim()) {
            errors.push(`Quiz Question ${questionNum}: Please enter a question.`);
        }
        
        const typeSelect = question.querySelector('.question-type');
        const questionType = typeSelect ? typeSelect.value : 'multiple_choice';
        
        // Check based on question type
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
            
            // Check if correct answer is selected
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
    // Remove error styling from all fields
    document.querySelectorAll('.field-error').forEach(el => {
        el.classList.remove('field-error');
    });
    document.querySelectorAll('.section-error').forEach(el => {
        el.classList.remove('section-error');
    });
    document.querySelectorAll('.error-message').forEach(el => {
        el.remove();
    });
    
    // Reset media widgets
    document.querySelectorAll('.media-upload-widget').forEach(widget => {
        widget.style.borderColor = '';
        widget.style.borderWidth = '';
    });
}

// Show error on a specific field
function showFieldError(field, message) {
    if (!field) return;
    
    // Add error class
    field.classList.add('field-error');
    
    // Check if error message already exists
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
    
    // Check if error message already exists
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
    
    // 1. Check if there's any content at all
    const contentCards = document.querySelectorAll('.content-card');
    const questions = document.querySelectorAll('.quiz-question');
    const hasContent = contentCards.length > 0;
    const hasQuiz = questions.length > 0;
    
    if (!hasContent && !hasQuiz) {
        errorMessages.push('Please add at least one content slide OR one quiz question.');
        hasErrors = true;
        // Show error on the content section
        const contentSection = document.getElementById('contentContainer');
        if (contentSection) {
            contentSection.classList.add('section-error');
        }
        // Show error on the quiz section
        const quizSection = document.getElementById('quizQuestions')?.closest('.section-card');
        if (quizSection) {
            quizSection.classList.add('section-error');
        }
    }
    
    // 2. Validate Lesson Content Cards (only if there are content cards)
    contentCards.forEach((card, index) => {
        const cardNum = index + 1;
        const typeSelect = card.querySelector('.content-type');
        const contentType = typeSelect ? typeSelect.value : 'text';
        const titleInput = card.querySelector('input[name*="[title]"]');
        const contentText = card.querySelector('textarea[name*="[content_text]"]');
        const mediaInput = card.querySelector('input[name*="[existing_media]"]');
        const mediaWidget = card.querySelector('.media-upload-widget');
        let cardHasError = false;
        
        // Check title
        if (!titleInput || !titleInput.value.trim()) {
            showFieldError(titleInput, 'Please enter a title for this slide');
            errorMessages.push(`Content Slide ${cardNum}: Missing title`);
            cardHasError = true;
            hasErrors = true;
        }
        
        // Check based on content type
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
                // Highlight the media widget
                if (mediaWidget) {
                    mediaWidget.style.borderColor = '#EF4444';
                    mediaWidget.style.borderWidth = '2px';
                    // Remove existing error message
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
        }
        
        if (cardHasError) {
            card.classList.add('section-error');
        }
    });
    
    // 3. Validate Quiz Questions (only if there are questions)
    questions.forEach((question, index) => {
        const questionNum = index + 1;
        const questionInput = question.querySelector('input[name*="[question]"]');
        let questionHasError = false;
        
        // Check question text
        if (!questionInput || !questionInput.value.trim()) {
            showFieldError(questionInput, 'Please enter a question');
            errorMessages.push(`Quiz Question ${questionNum}: Missing question text`);
            questionHasError = true;
            hasErrors = true;
        }
        
        const typeSelect = question.querySelector('.question-type');
        const questionType = typeSelect ? typeSelect.value : 'multiple_choice';
        
        // Check based on question type
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
            
            // Check if correct answer is selected
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
                    // Remove existing error message
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
    
    // Add all errors to summary
    if (hasErrors) {
        errorMessages.forEach(msg => addToValidationSummary(msg));
    }
    
    // Return false if there are errors, true if no errors
    return !hasErrors;
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

// Watch content changes to update AI quiz button state
document.addEventListener('DOMContentLoaded', function() {
    const contentCards = document.getElementById('contentCards');
    if (contentCards) {
        contentCards.addEventListener('input', updateAiQuizBtnState);
        new MutationObserver(updateAiQuizBtnState).observe(contentCards, { childList: true, subtree: true });
    }
    updateAiQuizBtnState();

    // Set initial state for module fields
    toggleModuleFields();

    // Initialize question type handlers for existing questions
    document.querySelectorAll('.question-type').forEach(select => {
        handleQuestionTypeChange(select);
    });

    // Clear errors when user starts typing (moved OUTSIDE submit handler)
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('field-error')) {
            e.target.classList.remove('field-error');
            const errorMsg = e.target.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        }
    });
    
    // Clear errors on file upload (moved OUTSIDE submit handler)
    document.addEventListener('change', function(e) {
        if (e.target.type === 'file') {
            const widget = e.target.closest('.media-upload-widget');
            if (widget) {
                widget.style.borderColor = '';
                widget.style.borderWidth = '';
                const errorMsg = widget.parentElement.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();
            }
        }
    });

    // Form validation with inline errors
    const lessonForm = document.getElementById('lessonForm');
    let allowSubmit = false;

    // Remember which button was pressed (draft vs publish)
    lessonForm?.querySelectorAll('button[type="submit"][data-status]').forEach(btn => {
        btn.addEventListener('click', function() {
            const field = document.getElementById('lessonStatusField');
            if (field) field.value = this.dataset.status;
        });
    });

    lessonForm?.addEventListener('submit', function(e) {
        if (allowSubmit) return; // programmatic re-submit after passing validation

        // Block submission first: nothing below can accidentally let it through
        e.preventDefault();

        let isValid = true;
        try {
            clearValidationErrors();

            // Validate module
            const action = document.getElementById('moduleAction')?.value;
            if (action === 'existing') {
                const moduleSelect = document.getElementById('moduleIdSelect');
                if (!moduleSelect?.value) {
                    showFieldError(moduleSelect, 'Please select a module');
                    document.getElementById('existingModuleFields')?.classList.add('section-error');
                    addToValidationSummary('Please select a module');
                    moduleSelect?.focus();
                    isValid = false;
                }
            }
            if (action === 'new') {
                const newTitle = document.getElementById('newModuleTitle');
                if (!newTitle?.value.trim()) {
                    showFieldError(newTitle, 'Please enter a title for the new module');
                    document.getElementById('newModuleFields')?.classList.add('section-error');
                    addToValidationSummary('Please enter a new module title');
                    newTitle?.focus();
                    isValid = false;
                }
            }

            // Run main validation (content slides + quiz questions)
            if (!validateLessonForm(false)) isValid = false;
        } catch (err) {
            // A crash in validation must NEVER result in a silent publish
            console.error('Validation crashed:', err);
            addToValidationSummary('Validation could not complete. Please review the form and try again.');
            isValid = false;
        }

        if (!isValid) {
            // Scroll to the first highlighted field / section and focus it
            const firstBad = document.querySelector('.field-error, .section-error');
            if (firstBad) {
                firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof firstBad.focus === 'function' && firstBad.matches('input, select, textarea')) {
                    setTimeout(() => firstBad.focus({ preventScroll: true }), 350);
                }
            }
            return;
        }

        // Passed: let it through
        allowSubmit = true;
        lessonForm.submit();
    });
});

// Close preview overlay on click outside
window.addEventListener('click', function(e) {
    const overlay = document.getElementById('previewOverlay');
    if (e.target === overlay) closePreview();

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