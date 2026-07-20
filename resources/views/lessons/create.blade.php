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
    }
    .btn-outline-blue:hover { background: rgba(24,72,200,0.06); }

    /* ── AJAX Upload Widget ─────────────────────────────────────────── */
    .media-upload-widget {
        border: 2px dashed #cbd5e1; border-radius: 14px; padding: 12px;
        background: #f8fafc; transition: border-color 0.2s, background 0.2s; position: relative;
    }
    .media-upload-widget.has-file { border-color: #1848c8; background: #f0f4ff; }
    .media-upload-widget.uploading { border-color: #6366f1; background: #f5f3ff; }
    .media-upload-widget .upload-trigger {
        display: flex; align-items: center; gap: 10px; cursor: pointer; position: relative;
    }
    .media-upload-widget .upload-trigger input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .media-upload-widget .upload-label { font-size: 13px; font-weight: 600; color: #475569; pointer-events: none; }
    .media-upload-widget .upload-spinner {
        display: none; width: 16px; height: 16px; border: 2px solid #c7d2fe;
        border-top-color: #6366f1; border-radius: 50%; animation: spin 0.7s linear infinite; flex-shrink: 0;
    }
    .media-upload-widget.uploading .upload-spinner { display: block; }
    .media-upload-widget.uploading .upload-icon { display: none; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .media-upload-widget .media-thumb-wrap { display: none; align-items: center; gap: 10px; flex-wrap: wrap; }
    .media-upload-widget.has-file .media-thumb-wrap { display: flex; }
    .media-upload-widget .media-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1.5px solid #e2e8f0; flex-shrink: 0; }
    .media-upload-widget .media-thumb-info { font-size: 12px; color: #64748b; }
    .media-upload-widget .media-thumb-info strong { display: block; font-size: 12px; color: #1e293b; }
    .media-upload-widget .media-remove-btn { font-size: 11px; color: #ef4444; background: none; border: none; cursor: pointer; font-weight: 700; margin-top: 2px; }
    .media-upload-error { font-size: 12px; color: #dc2626; margin-top: 4px; display: none; }

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
    </div> <!-- END #quizQuestions -->
    
    <!-- ADD ANOTHER QUESTION BUTTON - OUTSIDE #quizQuestions -->
    <button type="button" onclick="addQuizQuestion()" class="dashed-add-btn mt-2">
        <span class="material-symbols-outlined text-sm">add</span> Add Another Question
    </button>
</div> <!-- END .section-card -->

        <div class="form-footer">
            <button type="button" onclick="openPreview()" class="btn-outline-blue flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">visibility</span> Preview
            </button>
            <div class="flex gap-3">
                <button type="submit" name="status" value="draft" class="btn-ghost">💾 Save Draft</button>
                <button type="submit" name="status" value="published" class="btn-primary">🚀 Publish Lesson</button>
            </div>
        </div>
    </form>
</div>

<!-- Mobile-only preview overlay (phone frame). No web/desktop preview mode. -->
<div id="previewOverlay">
    <button class="preview-close" onclick="closePreview()" style="position:fixed; top:20px; right:20px; background:white; border:none; border-radius:50%; width:50px; height:50px; font-size:24px; cursor:pointer; box-shadow:0 4px 20px rgba(0,0,0,0.2); z-index:10000; display:flex; align-items:center; justify-content:center;">✕</button>
    <div class="preview-container" id="previewContent">
        <div class="preview-loading">Loading preview...</div>
    </div>
</div>

<script>
const UPLOAD_URL = '{{ url('/lessons/upload-media') }}';
const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]')?.content
                 || document.querySelector('input[name="_token"]')?.value;
let contentIndex = 1;
let quizIndex = 1;

/* ── AJAX upload helpers ─────────────────────────────────────────────────── */
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
    
    // Find the media-path-input - this could be for content, quiz question, or option
    let pathInput = widget.closest('div')?.querySelector('.media-path-input');
    
    // If no media-path-input found, check if this is a quiz question media
    if (!pathInput) {
        // Look for the hidden input that stores the existing media path
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

function openPreview() {
    const overlay = document.getElementById('previewOverlay');
    const content = document.getElementById('previewContent');
    overlay.classList.add('active');
    content.innerHTML = '<div class="preview-loading">⏳ Preparing preview...</div>';

    const form   = document.getElementById('lessonForm');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                   || document.querySelector('input[name="_token"]')?.value;

    // Collect all pending file inputs that have a file selected
    const pendingUploads = [];
    form.querySelectorAll('input[type="file"]').forEach(input => {
        if (input.files && input.files[0]) {
            pendingUploads.push(input);
        }
    });

    // Upload each pending file to temp_preview, store the returned path
    // back into a hidden input so cleanData picks it up as existing_media
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
                    // Insert or update an existing_media / existing_image hidden input
                    // so it travels with the preview POST
                    const name = input.name; // e.g. contents[0][media] or quiz[0][options][1][image]

                    let hiddenName;
                    const contentMatch = name.match(/^(contents\[\d+\])\[media\]$/);
                    const quizMediaMatch = name.match(/^(quiz\[\d+\])\[media\]$/);
                    const optionMatch = name.match(/^(quiz\[\d+\]\[options\]\[\d+\])\[image\]$/);

                    if (contentMatch)   hiddenName = contentMatch[1]   + '[existing_media]';
                    else if (quizMediaMatch) hiddenName = quizMediaMatch[1] + '[existing_media]';
                    else if (optionMatch)    hiddenName = optionMatch[1]    + '[existing_image]';

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
            .catch(() => {}); // silently ignore individual upload failures
    });

    Promise.all(uploadPromises).then(() => {
        // Now build a clean FormData — no File objects (already handled above)
        const rawData   = new FormData(form);
        const cleanData = new FormData();
        for (const [key, value] of rawData.entries()) {
            if (value instanceof File) continue; // skip any remaining raw files
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
        // Clean up temp hidden inputs after preview loads
        form.querySelectorAll('.preview-temp-hidden').forEach(el => el.remove());
    })
    .catch(error => {
        content.innerHTML = '<div class="preview-loading" style="color:#FCA5A5;">Preview failed. Please try again.</div>';
        console.error('Preview error:', error);
        form.querySelectorAll('.preview-temp-hidden').forEach(el => el.remove());
    });
}

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

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.content-type').forEach(toggleFields);
    toggleModuleFields();

    // Auto-open AI modal if redirected from the index with ?ai=1
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('ai') === '1') {
        setTimeout(() => openAiModal(), 300);
    }

    document.getElementById('lessonForm')?.addEventListener('submit', function(e) {
        // First validate drag drop pairs
        const validation = validateDragDropPairs();
        if (!validation.isValid) {
            e.preventDefault();
            alert(validation.errorMsg);
            return;
        }
        
        // Then validate module
        const action = document.getElementById('moduleAction')?.value;
        if (action === 'existing') {
            const moduleSelect = document.getElementById('moduleIdSelect');
            if (!moduleSelect?.value) {
                e.preventDefault();
                alert('Please select a module or choose a different module option.');
                moduleSelect?.focus();
            }
        }
        if (action === 'new') {
            const newTitle = document.getElementById('newModuleTitle');
            if (!newTitle?.value.trim()) {
                e.preventDefault();
                alert('Please enter a title for the new module.');
                newTitle?.focus();
            }
        }
    });
});


function closePreview() {
    document.getElementById('previewOverlay').classList.remove('active');
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closePreview();
});

function toggleFields(select) {
    const card = select.closest('.content-card');
    if (!card) return;
    const gestureField = card.querySelector('.gesture-field');
    const mediaField = card.querySelector('.media-field');
    const typeLabel = card.querySelector('.badge-pill');
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
    // Always show media-field if slide is media_missing (AI-generated)
    const mediaMissingInput = card.querySelector('input[name*="[media_missing]"]');
    if (mediaMissingInput && mediaMissingInput.value === '1') {
        if (mediaField) mediaField.classList.remove('hidden');
    }
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

function getQuizQuestionIndex(questionDiv) {
    const questionInput = questionDiv.querySelector('input[name*="[question]"]');
    if (!questionInput) return 0;
    const match = questionInput.name.match(/^quiz\[(\d+)\]/);
    return match ? parseInt(match[1], 10) : 0;
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
    
    // Find the correct hidden input based on side
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
    
    // Find both hidden inputs and clear the one that has a value
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
    const tagsContainer = document.getElementById(`selectedGestureTags_${questionIndex}`);
    
    if (!moduleId) {
        checkboxesContainer.innerHTML = '<span class="text-sm text-slate-400">Select a module first</span>';
        previewContainer.style.display = 'none';
        return;
    }
    
    // Show loading state
    checkboxesContainer.innerHTML = '<span class="text-sm text-slate-400">Loading gestures...</span>';
    
    // Fetch gestures for this module
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
                
                // Hover effect
                label.onmouseenter = function() {
                    if (!this.classList.contains('selected')) {
                        this.style.borderColor = '#1848c8';
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
                
                // Store display name for preview
                checkbox.dataset.displayName = gesture.display_name || gesture.name;
                
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
                
                // Add checkmark icon when selected
                const checkIcon = document.createElement('span');
                checkIcon.className = 'check-icon';
                checkIcon.textContent = '✓';
                checkIcon.style.cssText = `
                    display: none;
                    color: #10B981;
                    font-weight: 800;
                    font-size: 14px;
                `;
                
                label.appendChild(checkbox);
                label.appendChild(span);
                label.appendChild(checkIcon);
                checkboxesContainer.appendChild(label);
            });
        } else {
            checkboxesContainer.innerHTML = '<span class="text-sm text-slate-400">No gestures in this module</span>';
        }
    })
    .catch(error => {
        console.error('Error loading gestures:', error);
        checkboxesContainer.innerHTML = '<span class="text-sm text-red-500">Error loading gestures</span>';
    });
}

// Update gesture preview tags
function updateGesturePreview(questionIndex) {
    const tagsContainer = document.getElementById(`selectedGestureTags_${questionIndex}`);
    const questionDiv = document.getElementById(`selectedGestureTags_${questionIndex}`)?.closest('.quiz-question');
    if (!questionDiv) return;
    
    const previewContainer = questionDiv.querySelector('.selected-gestures-preview');
    const checkboxes = questionDiv.querySelectorAll(`input[name="quiz[${questionIndex}][gesture_ids][]"]:checked`);
    
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
            // Highlight the question
            questionDiv.style.borderColor = '#EF4444';
            questionDiv.style.borderWidth = '2px';
        } else {
            questionDiv.style.borderColor = '#E5EAF2';
            questionDiv.style.borderWidth = '1.5px';
        }
    });
    
    return { isValid, errorMsg };
}

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
        // Existing true/false logic...
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
        
    }  else if (select.value === 'drag_drop') {
        // Show drag-drop container, hide options
        if (dragDropContainer) dragDropContainer.classList.remove('hidden');
        if (optionsList) optionsList.closest('.options-container')?.classList.add('hidden');
        
        // Auto-add first pair if none exist
        const pairsList = dragDropContainer.querySelector('.drag-drop-pairs-list');
        if (pairsList && pairsList.querySelectorAll('.drag-drop-pair').length === 0) {
            // Find the add pair button and click it
            const addBtn = dragDropContainer.querySelector('button[onclick*="addDragDropPair"]');
            if (addBtn) addBtn.click();
        }
    } else if (select.value === 'gesture') {
    // Show gesture container, hide options
    if (gestureContainer) gestureContainer.classList.remove('hidden');
    if (optionsList) optionsList.closest('.options-container')?.classList.add('hidden');
    
    // Auto-load gestures if module is already selected
    const moduleSelect = gestureContainer.querySelector('.gesture-module-select');
    if (moduleSelect && moduleSelect.value) {
        loadGesturesForModule(moduleSelect, getQuizQuestionIndex(questionDiv));
    }
}else if (select.value === 'multiple_choice') {
        // Show options
        if (optionsList) optionsList.closest('.options-container')?.classList.remove('hidden');
        optionsList.querySelectorAll('.option-image-row, .option-remove-btn').forEach(el => el.style.visibility = 'visible');
        if (addOptionBtn) addOptionBtn.style.display = 'inline-block';
    }
}


function previewOptionImage(input) {
    const row = input.closest('.option-body');
    const img = row.querySelector('.option-image-preview');
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }
}

function previewContentMedia(input) {
    const wrap = input.closest('.media-field')?.querySelector('.media-preview-wrap');
    const img  = wrap?.querySelector('.content-media-preview');
    if (!wrap || !img) return;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.type.startsWith('image/')) {
            img.src = URL.createObjectURL(file);
            img.style.display = 'block';
            wrap.style.display = 'block';
        } else {
            // video — just show filename, no preview
            img.style.display = 'none';
            wrap.style.display = 'none';
        }
    } else {
        img.style.display = 'none';
        wrap.style.display = 'none';
    }
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
        // Also update existing_image hidden inputs
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

// Update addQuizQuestion to include new fields
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
                    <div class="media-upload-widget" data-context="quiz_media" style="padding:10px;">
                        <div class="upload-trigger" style="gap:8px;">
                            <input type="file" accept="image/*" class="ajax-file-input" onchange="handleAjaxUpload(this,'quiz_media')">
                            <span class="upload-icon material-symbols-outlined" style="font-size:18px;color:#94a3b8;">cloud_upload</span>
                            <div class="upload-spinner"></div>
                            <span class="upload-label" style="font-size:12px;">Upload image</span>
                        </div>
                        <div class="media-thumb-wrap" style="margin-top:8px;"></div>
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
            </div>
        </div>
    `;
    container.appendChild(question);
    const optionsList = question.querySelector('.options-list');
    optionsList.appendChild(buildOptionRow(qIndex, 0));
    optionsList.appendChild(buildOptionRow(qIndex, 1));
    quizIndex++;
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
    });
    quizIndex = document.querySelectorAll('.quiz-question').length;
}

function removeQuizQuestion(btn) {
    const question = btn.closest('.quiz-question');
    if (document.querySelectorAll('.quiz-question').length > 1) {
        question.remove();
        reindexQuizQuestions();
    }
}

/* ── AI Quiz from Content ─────────────────────────────────────────── */
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

        // Populate quiz
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

        // Toast
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
    document.getElementById('contentCards').addEventListener('input', updateAiQuizBtnState);
    // MutationObserver to catch dynamically added cards
    new MutationObserver(updateAiQuizBtnState).observe(
        document.getElementById('contentCards'),
        { childList: true, subtree: true }
    );
    updateAiQuizBtnState();
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