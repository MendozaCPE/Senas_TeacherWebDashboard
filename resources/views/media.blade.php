@extends('layouts.app')
@section('title', 'Media')
@section('content')

{{-- ── SKELETON ─────────────────────────────────────────────────────────── --}}
<div id="page-skeleton" class="flex flex-col gap-5" aria-hidden="true">
    {{-- Toolbar --}}
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm p-4 flex flex-col gap-3">
        <div class="flex items-center gap-3 flex-wrap">
            <div class="flex gap-2">@for($i=0;$i<3;$i++)<div class="skeleton h-8 rounded-full w-20"></div>@endfor</div>
            <div class="flex gap-2">@for($i=0;$i<4;$i++)<div class="skeleton h-8 rounded-full w-16"></div>@endfor</div>
            <div class="ml-auto skeleton h-9 rounded-xl w-32"></div>
        </div>
        <div class="flex gap-2 pt-3 border-t border-slate-100">
            @for($i=0;$i<4;$i++)<div class="skeleton h-5 rounded-full w-16"></div>@endfor
        </div>
    </div>
    {{-- Media grid --}}
    <div class="grid gap-[18px]" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
        @for($i=0;$i<12;$i++)
        <div class="bg-white rounded-[18px] border border-slate-100 overflow-hidden shadow-sm" style="animation-delay:{{ $i*0.05 }}s">
            <div class="skeleton w-full" style="padding-bottom:56.25%"></div>
            <div class="p-3 flex flex-col gap-2">
                <div class="skeleton h-3 rounded w-3/4"></div>
                <div class="skeleton h-2 rounded w-1/2"></div>
            </div>
        </div>
        @endfor
    </div>
</div>
{{-- ── END SKELETON ─────────────────────────────────────────────────────── --}}
<script>document.addEventListener('DOMContentLoaded',function(){var s=document.getElementById('page-skeleton');if(s)s.style.display='none';});</script>

<style>
/* ── Filter tab pills ──────────────────────────────────────────────────────── */
.filter-tab {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 99px; font-size: 12px; font-weight: 700;
    cursor: pointer; border: 1.5px solid #e2e8f0; transition: all 0.15s;
    white-space: nowrap; background: #fff; color: #64748b;
}
.filter-tab:hover { background: #f1f5f9; border-color: #cbd5e1; color: #0d326b; }
.filter-tab.active { background: #0d326b; color: #fff; border-color: #0d326b; }
html.dark .filter-tab { background: #112240; color: rgba(255,255,255,0.6); border-color: rgba(255,255,255,0.08); }
html.dark .filter-tab:hover { background: #1a3a5c; color: #fff; }
html.dark .filter-tab.active { background: #0d326b; color: #fff; }

/* ── Media card grid ──────────────────────────────────────────────────────── */
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
}
.media-card {
    background: #fff; border-radius: 18px; overflow: hidden;
    border: 1.5px solid #f1f5f9; box-shadow: 0 1px 3px rgba(13,50,107,0.04);
    transition: transform 0.18s, box-shadow 0.18s; cursor: pointer; position: relative;
}
.media-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(13,50,107,0.10); border-color: #cbd5e1; }
html.dark .media-card { background: #112240; border-color: rgba(255,255,255,0.06); }
html.dark .media-card:hover { border-color: rgba(255,255,255,0.15); }

.media-thumb {
    position: relative; width: 100%; padding-top: 56.25%;
    background: #f1f5f9; overflow: hidden; flex-shrink: 0;
}
html.dark .media-thumb { background: #0a2244; }
.media-thumb img, .media-thumb video {
    position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
}
.play-overlay {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    background: rgba(0,0,0,0.25); transition: background 0.18s;
}
.media-card:hover .play-overlay { background: rgba(0,0,0,0.38); }
.play-icon-circle {
    width: 44px; height: 44px; background: rgba(255,255,255,0.92); border-radius: 50%;
    display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 12px rgba(0,0,0,0.3);
}
.source-badge {
    position: absolute; top: 8px; left: 8px; padding: 3px 10px; border-radius: 99px;
    font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; backdrop-filter: blur(4px);
}
.source-badge.system   { background: rgba(13,50,107,0.85); color: #fff; }
.source-badge.uploaded { background: rgba(16,185,129,0.85); color: #fff; }
.type-badge {
    position: absolute; top: 8px; right: 8px; padding: 3px 9px; border-radius: 99px;
    font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; backdrop-filter: blur(4px);
}
.type-badge.image { background: rgba(59,130,246,0.82);  color: #fff; }
.type-badge.video { background: rgba(239,68,68,0.82);   color: #fff; }
.type-badge.gif   { background: rgba(168,85,247,0.82);  color: #fff; }
.gif-badge {
    position: absolute; bottom: 8px; right: 8px; background: rgba(168,85,247,0.85); color: #fff;
    font-size: 9px; font-weight: 800; padding: 2px 7px; border-radius: 99px;
    letter-spacing: 0.08em; text-transform: uppercase;
}
.media-card-body { padding: 12px 14px 10px; }
.media-card-title {
    font-size: 13px; font-weight: 700; color: #0d326b;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px;
}
html.dark .media-card-title { color: #fff; }
.media-card-meta { font-size: 11px; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.media-card-actions { display: flex; align-items: center; gap: 4px; margin-top: 8px; padding-top: 8px; border-top: 1px solid #f1f5f9; }
html.dark .media-card-actions { border-color: rgba(255,255,255,0.06); }
.card-action-btn {
    flex: 1; padding: 5px 0; border-radius: 8px; border: none;
    font-size: 11px; font-weight: 700; cursor: pointer;
    transition: background 0.15s, color 0.15s;
    display: flex; align-items: center; justify-content: center; gap: 3px;
}
.card-action-btn.preview-btn { background: #eff6ff; color: #1a6fd4; }
.card-action-btn.preview-btn:hover { background: #dbeafe; }
.card-action-btn.edit-btn { background: #f8fafc; color: #64748b; }
.card-action-btn.edit-btn:hover { background: #f1f5f9; color: #0d326b; }
.card-action-btn.delete-btn { background: #fef2f2; color: #dc2626; }
.card-action-btn.delete-btn:hover { background: #fee2e2; }
html.dark .card-action-btn.preview-btn { background: rgba(59,130,246,0.12); color: #60a5fa; }
html.dark .card-action-btn.edit-btn    { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); }
html.dark .card-action-btn.delete-btn  { background: rgba(220,38,38,0.1); color: #f87171; }

/* ── Stat chip ─────────────────────────────────────────────────────────────── */
.stat-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 99px; font-size: 11px; font-weight: 700;
    background: #f8fafc; border: 1.5px solid #e2e8f0; color: #475569; transition: all 0.15s;
}
html.dark .stat-chip { background: #0d1b2e; border-color: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); }

/* ── Preview modal ─────────────────────────────────────────────────────────── */
#mediaPreviewModal {
    display: none; position: fixed; inset: 0; z-index: 9999;
    background: rgba(5,10,25,0.88); backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
}
#mediaPreviewModal.open { display: flex; }
.preview-modal-box { position: relative; max-width: 900px; width: 95%; max-height: 90vh; display: flex; flex-direction: column; }
.preview-modal-close {
    position: fixed; top: 18px; right: 22px; width: 42px; height: 42px;
    background: rgba(255,255,255,0.92); border: none; border-radius: 50%; cursor: pointer;
    font-size: 20px; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25); transition: transform 0.18s; z-index: 10001; color: #0d326b;
}
.preview-modal-close:hover { transform: scale(1.1); background: #fff; }
.preview-nav-btn {
    position: fixed; top: 50%; transform: translateY(-50%); width: 46px; height: 46px;
    background: rgba(255,255,255,0.88); border: none; border-radius: 50%; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.25); transition: transform 0.18s; z-index: 10001; color: #0d326b;
}
.preview-nav-btn:hover { transform: translateY(-50%) scale(1.08); background: #fff; }
.preview-nav-btn.prev { left: 16px; }
.preview-nav-btn.next { right: 16px; }
.preview-nav-btn:disabled { opacity: 0.35; pointer-events: none; }
.preview-media-wrap {
    background: #000; border-radius: 16px 16px 0 0; overflow: hidden;
    display: flex; align-items: center; justify-content: center; min-height: 280px; max-height: 60vh;
}
.preview-media-wrap img, .preview-media-wrap video { max-width: 100%; max-height: 60vh; object-fit: contain; }
.preview-info-panel { background: #fff; border-radius: 0 0 16px 16px; padding: 18px 22px; }
html.dark .preview-info-panel { background: #112240; }

/* ── Upload / Edit / Delete modals ────────────────────────────────────────── */
#uploadModal, #editModal, #deleteModal {
    display: none; position: fixed; inset: 0; z-index: 9998;
    background: rgba(5,10,25,0.6); backdrop-filter: blur(4px);
    align-items: center; justify-content: center;
}
#uploadModal.open, #editModal.open, #deleteModal.open { display: flex; }
.upload-dropzone {
    border: 2px dashed #cbd5e1; border-radius: 16px; padding: 32px 24px;
    text-align: center; cursor: pointer; transition: all 0.18s; position: relative;
}
.upload-dropzone:hover, .upload-dropzone.dragover { border-color: #0d326b; background: #eff6ff; }
.upload-dropzone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.upload-preview-thumb { width: 80px; height: 80px; border-radius: 12px; object-fit: cover; border: 2px solid #e2e8f0; display: none; margin: 0 auto 8px; }
.upload-progress-bar-wrap { background: #e2e8f0; border-radius: 99px; height: 6px; overflow: hidden; display: none; margin-top: 8px; }
.upload-progress-bar { height: 100%; background: linear-gradient(90deg, #0d326b, #1a6fd4); border-radius: 99px; width: 0%; transition: width 0.3s; }

.media-grid-wrap { min-height: 300px; }
.empty-media { text-align: center; padding: 64px 24px; }

@keyframes spin { to { transform: rotate(360deg); } }
@media (max-width: 768px) {
    .media-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
    .preview-nav-btn.prev { left: 4px; } .preview-nav-btn.next { right: 4px; }
}
@media (max-width: 500px) { .media-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; } }
</style>

{{-- ── Single Toolbar Card: filters + upload + stats all in one row ─────────── --}}
<div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-6 py-4 mb-6">

    {{-- Top row: all filters + upload button --}}
    <div class="flex flex-wrap items-center gap-3">

        {{-- Source --}}
        <div class="flex flex-col gap-1">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 px-0.5">Source</span>
            <div class="flex items-center gap-1.5" id="sourceTabs">
                <button type="button" data-val="all"      class="filter-tab active" onclick="setSource(this)">
                    <span class="material-symbols-outlined text-[14px]">perm_media</span> All
                </button>
                <button type="button" data-val="system"   class="filter-tab" onclick="setSource(this)">
                    <span class="material-symbols-outlined text-[14px]">shield</span> System
                </button>
                <button type="button" data-val="uploaded" class="filter-tab" onclick="setSource(this)">
                    <span class="material-symbols-outlined text-[14px]">cloud_upload</span> My Uploads
                </button>
            </div>
        </div>

        <div class="h-9 w-px bg-slate-100 hidden sm:block self-end mb-0.5"></div>

        {{-- Type --}}
        <div class="flex flex-col gap-1">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 px-0.5">Type</span>
            <div class="flex items-center gap-1.5" id="typeTabs">
                <button type="button" data-val="all"   class="filter-tab active" onclick="setType(this)">
                    <span class="material-symbols-outlined text-[14px]">category</span> All
                </button>
                <button type="button" data-val="image" class="filter-tab" onclick="setType(this)">
                    <span class="material-symbols-outlined text-[14px]">image</span> Images
                </button>
                <button type="button" data-val="video" class="filter-tab" onclick="setType(this)">
                    <span class="material-symbols-outlined text-[14px]">videocam</span> Videos
                </button>
                <button type="button" data-val="gif"   class="filter-tab" onclick="setType(this)">
                    <span class="material-symbols-outlined text-[14px]">gif</span> GIFs
                </button>
            </div>
        </div>

        <div class="h-9 w-px bg-slate-100 hidden sm:block self-end mb-0.5"></div>

        {{-- Sort --}}
        <div class="flex flex-col gap-1">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 px-0.5">Sort</span>
            <div class="flex items-center gap-1.5" id="sortTabs">
                <button type="button" data-val="newest" class="filter-tab active" onclick="setSort(this)">Newest</button>
                <button type="button" data-val="oldest" class="filter-tab" onclick="setSort(this)">Oldest</button>
                <button type="button" data-val="alpha"  class="filter-tab" onclick="setSort(this)">A–Z</button>
            </div>
        </div>

        {{-- Upload button — pushed to the far right --}}
        <div class="ml-auto">
            <button onclick="openUploadModal()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-bold text-[13px] transition-all shadow-sm hover:opacity-90 hover:-translate-y-px"
                    style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 60%, #1a6fd4 100%);">
                <span class="material-symbols-outlined icon-outline text-[18px]">upload</span>
                Upload Media
            </button>
        </div>
    </div>

    {{-- Stats row --}}
    <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-slate-100" id="statsBar"></div>
</div>

{{-- ── Media Grid (rendered by JS) ─────────────────────────────────────────── --}}
<div class="media-grid-wrap">
    <div class="media-grid" id="mediaGrid"></div>
    <div id="emptyState" class="empty-media hidden">
        <div class="w-20 h-20 rounded-3xl bg-[#0d326b]/08 flex items-center justify-center mx-auto mb-5">
            <span class="material-symbols-outlined text-[#0d326b] text-[40px]">perm_media</span>
        </div>
        <h3 class="text-xl font-bold text-[#0d326b] mb-2">No media found</h3>
        <p class="text-slate-500 text-sm mb-4" id="emptyStateMsg">Adjust your filters or upload new media.</p>
        <button onclick="openUploadModal()" id="emptyUploadBtn"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-bold text-sm transition-all shadow-md hover:opacity-90"
                style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 60%, #1a6fd4 100%);">
            <span class="material-symbols-outlined icon-outline text-[18px]">upload</span>
            Upload Your First Media
        </button>
    </div>
</div>

{{-- ── Preview Modal ───────────────────────────────────────────────────────── --}}
<div id="mediaPreviewModal">
    <button class="preview-modal-close" onclick="closePreview()">✕</button>
    <button class="preview-nav-btn prev" id="prevBtn" onclick="navigatePreview(-1)">
        <span class="material-symbols-outlined text-[22px]">chevron_left</span>
    </button>
    <button class="preview-nav-btn next" id="nextBtn" onclick="navigatePreview(1)">
        <span class="material-symbols-outlined text-[22px]">chevron_right</span>
    </button>
    <div class="preview-modal-box">
        <div class="preview-media-wrap" id="previewMediaWrap"></div>
        <div class="preview-info-panel">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-[#0d326b] truncate" id="previewTitle"></h3>
                    <p class="text-sm text-slate-500 mt-0.5" id="previewMeta"></p>
                    <p class="text-sm text-slate-600 mt-1" id="previewDesc" style="display:none;"></p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span id="previewSourceBadge" class="source-badge" style="position:static;font-size:10px;padding:4px 12px;"></span>
                    <span id="previewTypeBadge"   class="type-badge"   style="position:static;font-size:10px;padding:4px 10px;"></span>
                </div>
            </div>
            <div class="mt-3 text-[12px] font-semibold text-slate-400" id="previewCounter"></div>
        </div>
    </div>
</div>

{{-- ── Upload Modal ────────────────────────────────────────────────────────── --}}
<div id="uploadModal">
    <div class="bg-white rounded-3xl p-7 w-full max-w-md mx-4 shadow-2xl relative" style="max-height:90vh;overflow-y:auto;">
        <button onclick="closeUploadModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, #0d326b 0%, #1a6fd4 100%);">
                <span class="material-symbols-outlined text-white icon-outline text-[20px]">upload</span>
            </div>
            <div>
                <h3 class="text-[17px] font-bold text-[#0d326b]">Upload Media</h3>
                <p class="text-[12px] text-slate-400">Stored in your personal uploads</p>
            </div>
        </div>
        <div id="uploadError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium"></div>
        <div class="space-y-4">
            <div class="upload-dropzone" id="dropzone"
                 ondragover="event.preventDefault();this.classList.add('dragover')"
                 ondragleave="this.classList.remove('dragover')"
                 ondrop="handleDrop(event)">
                <input type="file" id="uploadFileInput" accept="image/*,video/*,.gif" onchange="handleFileSelect(this)">
                <img id="uploadThumb" class="upload-preview-thumb" src="" alt="">
                <div id="dropzoneContent">
                    <span class="material-symbols-outlined text-slate-300 text-[40px] block mb-2">cloud_upload</span>
                    <p class="font-bold text-slate-500 text-[14px]">Click or drag file here</p>
                    <p class="text-[12px] text-slate-400 mt-1">JPG, PNG, GIF, WebP, MP4, MOV — max 100 MB</p>
                </div>
                <div id="dropzoneFilename" class="text-[13px] font-semibold text-[#0d326b] mt-2 hidden"></div>
            </div>
            <div>
                <label class="block text-[13px] font-bold text-slate-600 mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" id="uploadTitle" placeholder="e.g., Letter A Hand Sign"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-[13px] focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/15 outline-none transition-all">
            </div>
            <div>
                <label class="block text-[13px] font-bold text-slate-600 mb-1.5">Description <span class="text-slate-400 font-normal">(optional)</span></label>
                <textarea id="uploadDesc" rows="2" placeholder="Brief description..."
                          class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-[13px] focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/15 outline-none transition-all resize-none"></textarea>
            </div>
            <div class="upload-progress-bar-wrap" id="uploadProgressWrap">
                <div class="upload-progress-bar" id="uploadProgressBar"></div>
            </div>
        </div>
        <div class="flex gap-3 mt-5">
            <button type="button" onclick="closeUploadModal()"
                    class="flex-1 py-2.5 border border-slate-200 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-50 transition-colors">
                Cancel
            </button>
            <button type="button" onclick="submitUpload()" id="uploadSubmitBtn"
                    class="flex-1 py-2.5 text-white font-bold text-sm rounded-xl transition-all hover:opacity-90 flex items-center justify-center gap-2"
                    style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 60%, #1a6fd4 100%);">
                <span class="material-symbols-outlined icon-outline text-[16px]">upload</span> Upload
            </button>
        </div>
    </div>
</div>

{{-- ── Edit Modal ───────────────────────────────────────────────────────────── --}}
<div id="editModal">
    <div class="bg-white rounded-3xl p-7 w-full max-w-md mx-4 shadow-2xl relative">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <span class="material-symbols-outlined text-amber-600 text-[20px]">edit</span>
            </div>
            <h3 class="text-[17px] font-bold text-[#0d326b]">Edit Media</h3>
        </div>
        <input type="hidden" id="editMediaId">
        <div id="editError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium"></div>
        <div class="space-y-4">
            <div>
                <label class="block text-[13px] font-bold text-slate-600 mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" id="editTitle"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-[13px] focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/15 outline-none transition-all">
            </div>
            <div>
                <label class="block text-[13px] font-bold text-slate-600 mb-1.5">Description</label>
                <textarea id="editDesc" rows="3"
                          class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-[13px] focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/15 outline-none transition-all resize-none"></textarea>
            </div>
            <div>
                <label class="block text-[13px] font-bold text-slate-600 mb-1.5">Replace file <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="file" id="editFileInput" accept="image/*,video/*,.gif"
                       class="w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#0d326b]/10 file:text-[#0d326b] hover:file:bg-[#0d326b]/20">
            </div>
        </div>
        <div class="flex gap-3 mt-5">
            <button type="button" onclick="closeEditModal()"
                    class="flex-1 py-2.5 border border-slate-200 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="button" onclick="submitEdit()" id="editSubmitBtn"
                    class="flex-1 py-2.5 bg-[#0d326b] hover:bg-[#154188] text-white font-bold text-sm rounded-xl transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined icon-outline text-[16px]">save</span> Save Changes
            </button>
        </div>
    </div>
</div>

{{-- ── Delete Modal ─────────────────────────────────────────────────────────── --}}
<div id="deleteModal">
    <div class="bg-white rounded-3xl p-7 w-full max-w-sm mx-4 shadow-2xl">
        <div class="text-center mb-5">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <span class="material-symbols-outlined text-red-500 text-3xl">delete_forever</span>
            </div>
            <h3 class="text-[18px] font-bold text-slate-800 mb-1">Delete Media?</h3>
            <p class="text-slate-500 text-sm">You're about to delete <strong id="deleteMediaTitle" class="text-slate-700"></strong>. This cannot be undone.</p>
        </div>
        <input type="hidden" id="deleteMediaId">
        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 py-2.5 border border-slate-200 rounded-2xl text-slate-600 font-bold hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="button" onclick="confirmDelete()" id="deleteConfirmBtn"
                    class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white font-bold rounded-2xl transition-colors">Delete</button>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

// All media loaded once — never re-fetched for client-side filtering
const ALL_MEDIA = @json($mediaJs);

// Active filter state
let activeSource = 'all';
let activeType   = 'all';
let activeSort   = 'newest';

// Currently displayed subset (used by preview navigator)
let filteredMedia          = [];
let currentPreviewIndex    = -1;

// ── Filter setters — instant, no page reload ─────────────────────────────────
function setSource(btn) {
    activeSource = btn.dataset.val;
    document.querySelectorAll('#sourceTabs .filter-tab').forEach(b => b.classList.toggle('active', b === btn));
    applyFilters();
}
function setType(btn) {
    activeType = btn.dataset.val;
    document.querySelectorAll('#typeTabs .filter-tab').forEach(b => b.classList.toggle('active', b === btn));
    applyFilters();
}
function setSort(btn) {
    activeSort = btn.dataset.val;
    document.querySelectorAll('#sortTabs .filter-tab').forEach(b => b.classList.toggle('active', b === btn));
    applyFilters();
}

// ── Core filter + render ──────────────────────────────────────────────────────
function applyFilters(searchOverride) {
    let result = ALL_MEDIA.slice();

    // Source
    if (activeSource !== 'all') result = result.filter(i => i.source === activeSource);

    // Type
    if (activeType !== 'all') result = result.filter(i => i.media_type === activeType);

    // External search (from nav bar — passed as string)
    if (searchOverride && searchOverride.trim()) {
        const q = searchOverride.trim().toLowerCase();
        result = result.filter(i =>
            i.title.toLowerCase().includes(q) ||
            i.file_name.toLowerCase().includes(q) ||
            (i.module && i.module.toLowerCase().includes(q)) ||
            (i.owner  && i.owner.toLowerCase().includes(q))
        );
    }

    // Sort
    if (activeSort === 'oldest') {
        result.sort((a, b) => (a.created_at || '').localeCompare(b.created_at || ''));
    } else if (activeSort === 'alpha') {
        result.sort((a, b) => a.title.localeCompare(b.title));
    } else {
        result.sort((a, b) => (b.created_at || '').localeCompare(a.created_at || ''));
    }

    filteredMedia = result;
    renderGrid(result, searchOverride || '');
    updateStats(result);
}

// ── Render grid ───────────────────────────────────────────────────────────────
function renderGrid(items, searchTerm) {
    const grid      = document.getElementById('mediaGrid');
    const emptyEl   = document.getElementById('emptyState');
    const emptyMsg  = document.getElementById('emptyStateMsg');
    const uploadBtn = document.getElementById('emptyUploadBtn');

    if (items.length === 0) {
        grid.innerHTML = '';
        emptyEl.classList.remove('hidden');
        emptyMsg.textContent = searchTerm
            ? 'No results for "' + searchTerm + '". Try a different search term.'
            : 'Adjust your filters or upload new media.';
        uploadBtn.style.display = activeSource === 'system' ? 'none' : '';
        return;
    }

    emptyEl.classList.add('hidden');
    grid.innerHTML = items.map((item, i) => buildCard(item, i)).join('');

    // Attach video hover events
    grid.querySelectorAll('video[data-hover]').forEach(vid => {
        vid.addEventListener('mouseenter', () => vid.play());
        vid.addEventListener('mouseleave', () => { vid.pause(); vid.currentTime = 0; });
    });
}

function buildCard(item, idx) {
    const isOwner = item.source === 'uploaded' && item.raw_id !== null;

    let thumb = '';
    if (item.media_type === 'video') {
        thumb = `
            <video src="${eh(item.url)}" preload="metadata" muted playsinline data-hover style="object-fit:cover;"></video>
            <div class="play-overlay">
                <div class="play-icon-circle">
                    <span class="material-symbols-outlined text-[#0d326b] text-[22px]">play_arrow</span>
                </div>
            </div>`;
    } else {
        thumb = `<img src="${eh(item.url)}" alt="${eh(item.title)}" loading="lazy">`;
        if (item.media_type === 'gif') thumb += `<div class="gif-badge">GIF</div>`;
    }

    const typeBadge = item.media_type !== 'gif'
        ? `<span class="type-badge ${item.media_type}">${item.media_type.toUpperCase()}</span>`
        : '';

    const metaParts = [];
    if (item.module)     metaParts.push(eh(item.module));
    if (item.owner)      metaParts.push(eh(item.owner));
    if (item.created_at) metaParts.push(eh(item.created_at));

    let actions = `
        <button class="card-action-btn preview-btn" onclick="event.stopPropagation();openPreview(${idx})">
            <span class="material-symbols-outlined text-[13px]">visibility</span> Preview
        </button>`;
    if (isOwner) {
        actions += `
        <button class="card-action-btn edit-btn"
                onclick="event.stopPropagation();openEditModal(${item.raw_id},'${ej(item.title)}','${ej(item.description||'')}')">
            <span class="material-symbols-outlined text-[13px]">edit</span> Edit
        </button>
        <button class="card-action-btn delete-btn"
                onclick="event.stopPropagation();openDeleteModal(${item.raw_id},'${ej(item.title)}')">
            <span class="material-symbols-outlined text-[13px]">delete</span> Del
        </button>`;
    }

    return `
    <div class="media-card" onclick="openPreview(${idx})">
        <div class="media-thumb">
            ${thumb}
            <span class="source-badge ${item.source}">${item.source === 'system' ? 'System' : 'Uploaded'}</span>
            ${typeBadge}
        </div>
        <div class="media-card-body">
            <div class="media-card-title" title="${eh(item.title)}">${eh(item.title)}</div>
            <div class="media-card-meta">${metaParts.join(' · ')}</div>
            <div class="media-card-actions" onclick="event.stopPropagation()">${actions}</div>
        </div>
    </div>`;
}

// ── Stats bar ─────────────────────────────────────────────────────────────────
function updateStats(items) {
    const total    = items.length;
    const system   = items.filter(i => i.source === 'system').length;
    const uploaded = items.filter(i => i.source === 'uploaded').length;
    const images   = items.filter(i => i.media_type === 'image').length;
    const videos   = items.filter(i => i.media_type === 'video').length;
    const gifs     = items.filter(i => i.media_type === 'gif').length;

    document.getElementById('statsBar').innerHTML = `
        <span class="stat-chip"><span class="material-symbols-outlined text-[14px] text-[#0d326b]">perm_media</span>${total} Shown</span>
        <span class="stat-chip"><span class="material-symbols-outlined text-[14px] text-slate-400">shield</span>${system} System</span>
        <span class="stat-chip"><span class="material-symbols-outlined text-[14px] text-emerald-500">cloud_upload</span>${uploaded} Uploaded</span>
        <span class="stat-chip"><span class="material-symbols-outlined text-[14px] text-blue-500">image</span>${images} Images</span>
        <span class="stat-chip"><span class="material-symbols-outlined text-[14px] text-red-500">videocam</span>${videos} Videos</span>
        ${gifs > 0 ? `<span class="stat-chip"><span class="material-symbols-outlined text-[14px] text-purple-500">gif</span>${gifs} GIFs</span>` : ''}
    `;
}

// ── Escape helpers ────────────────────────────────────────────────────────────
function eh(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function ej(s) { return String(s??'').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/\n/g,'\\n').replace(/\r/g,''); }

// ── Preview modal ─────────────────────────────────────────────────────────────
function openPreview(idx) {
    currentPreviewIndex = idx;
    renderPreview(idx);
    document.getElementById('mediaPreviewModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closePreview() {
    const vid = document.getElementById('previewMediaWrap').querySelector('video');
    if (vid) { vid.pause(); vid.src = ''; }
    document.getElementById('mediaPreviewModal').classList.remove('open');
    document.body.style.overflow = '';
    currentPreviewIndex = -1;
}
function navigatePreview(dir) {
    const n = currentPreviewIndex + dir;
    if (n < 0 || n >= filteredMedia.length) return;
    const vid = document.getElementById('previewMediaWrap').querySelector('video');
    if (vid) { vid.pause(); vid.src = ''; }
    currentPreviewIndex = n;
    renderPreview(n);
}
function renderPreview(idx) {
    const item = filteredMedia[idx];
    if (!item) return;
    const wrap = document.getElementById('previewMediaWrap');
    wrap.innerHTML = '';
    if (item.media_type === 'video') {
        const vid = document.createElement('video');
        vid.src = item.url; vid.controls = true; vid.autoplay = true; vid.style.maxHeight = '60vh';
        wrap.appendChild(vid);
    } else {
        const img = document.createElement('img');
        img.src = item.url; img.alt = item.title; img.style.maxHeight = '60vh';
        wrap.appendChild(img);
    }
    document.getElementById('previewTitle').textContent = item.title;
    const meta = [];
    if (item.module) meta.push(item.module);
    if (item.owner)  meta.push('By ' + item.owner);
    if (item.created_at) meta.push(item.created_at);
    if (item.file_name)  meta.push(item.file_name);
    document.getElementById('previewMeta').textContent = meta.join(' · ');
    const descEl = document.getElementById('previewDesc');
    descEl.textContent = item.description || '';
    descEl.style.display = item.description ? 'block' : 'none';
    const sb = document.getElementById('previewSourceBadge');
    sb.textContent = item.source === 'system' ? 'System' : 'Uploaded';
    sb.className = 'source-badge ' + item.source;
    sb.style.cssText = 'position:static;font-size:10px;padding:4px 12px;';
    const tb = document.getElementById('previewTypeBadge');
    tb.textContent = item.media_type.toUpperCase();
    tb.className = 'type-badge ' + item.media_type;
    tb.style.cssText = 'position:static;font-size:10px;padding:4px 10px;';
    document.getElementById('previewCounter').textContent = (idx + 1) + ' / ' + filteredMedia.length + ' in current view';
    document.getElementById('prevBtn').disabled = idx === 0;
    document.getElementById('nextBtn').disabled = idx === filteredMedia.length - 1;
}

// ── Upload modal ──────────────────────────────────────────────────────────────
let selectedFile = null;
function openUploadModal() { resetUploadModal(); document.getElementById('uploadModal').classList.add('open'); document.body.style.overflow='hidden'; }
function closeUploadModal() { document.getElementById('uploadModal').classList.remove('open'); document.body.style.overflow=''; resetUploadModal(); }
function resetUploadModal() {
    selectedFile = null;
    ['uploadFileInput','uploadTitle','uploadDesc'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
    document.getElementById('uploadThumb').style.display='none';
    document.getElementById('dropzoneContent').style.display='block';
    document.getElementById('dropzoneFilename').classList.add('hidden');
    document.getElementById('uploadError').classList.add('hidden');
    document.getElementById('uploadProgressWrap').style.display='none';
    document.getElementById('uploadProgressBar').style.width='0%';
    const btn=document.getElementById('uploadSubmitBtn'); btn.disabled=false;
    btn.innerHTML='<span class="material-symbols-outlined icon-outline text-[16px]">upload</span> Upload';
}
function handleDrop(e) { e.preventDefault(); document.getElementById('dropzone').classList.remove('dragover'); if(e.dataTransfer.files[0]) setSelectedFile(e.dataTransfer.files[0]); }
function handleFileSelect(input) { if(input.files&&input.files[0]) setSelectedFile(input.files[0]); }
function setSelectedFile(file) {
    selectedFile = file;
    const size = file.size>1048576 ? (file.size/1048576).toFixed(1)+' MB' : (file.size/1024).toFixed(0)+' KB';
    const nm = document.getElementById('dropzoneFilename');
    nm.textContent = '📎 '+file.name+' ('+size+')'; nm.classList.remove('hidden');
    document.getElementById('dropzoneContent').style.display='none';
    const ti = document.getElementById('uploadTitle');
    if(!ti.value.trim()) ti.value = file.name.replace(/\.[^.]+$/,'').replace(/[_\-]+/g,' ');
    if(file.type.startsWith('image/')) { const r=new FileReader(); r.onload=e=>{const t=document.getElementById('uploadThumb');t.src=e.target.result;t.style.display='block';}; r.readAsDataURL(file); }
}
async function submitUpload() {
    const title=document.getElementById('uploadTitle').value.trim();
    const desc=document.getElementById('uploadDesc').value.trim();
    const err=document.getElementById('uploadError'); err.classList.add('hidden');
    if(!selectedFile){err.textContent='Please select a file.';err.classList.remove('hidden');return;}
    if(!title){err.textContent='Please enter a title.';err.classList.remove('hidden');return;}
    const btn=document.getElementById('uploadSubmitBtn'); btn.disabled=true;
    btn.innerHTML='<span class="material-symbols-outlined icon-outline text-[16px]" style="animation:spin .7s linear infinite">sync</span> Uploading…';
    const pw=document.getElementById('uploadProgressWrap'); pw.style.display='block';
    const pb=document.getElementById('uploadProgressBar');
    const fd=new FormData(); fd.append('_token',CSRF_TOKEN); fd.append('file',selectedFile); fd.append('title',title); fd.append('description',desc);
    try {
        await new Promise((res,rej)=>{
            const xhr=new XMLHttpRequest(); xhr.open('POST','{{ route('media.upload') }}');
            xhr.setRequestHeader('X-CSRF-TOKEN',CSRF_TOKEN); xhr.setRequestHeader('Accept','application/json');
            xhr.upload.onprogress=e=>{if(e.lengthComputable) pb.style.width=Math.round(e.loaded/e.total*90)+'%';};
            xhr.onload=()=>{pb.style.width='100%'; if(xhr.status>=200&&xhr.status<300) res(JSON.parse(xhr.responseText)); else {try{rej(new Error(JSON.parse(xhr.responseText).message));}catch{rej(new Error('Upload failed.'));}}};
            xhr.onerror=()=>rej(new Error('Network error.')); xhr.send(fd);
        });
        closeUploadModal(); window.location.reload();
    } catch(e) {
        pw.style.display='none'; err.textContent='⚠ '+e.message; err.classList.remove('hidden');
        btn.disabled=false; btn.innerHTML='<span class="material-symbols-outlined icon-outline text-[16px]">upload</span> Upload';
    }
}

// ── Edit modal ────────────────────────────────────────────────────────────────
function openEditModal(id,title,desc){document.getElementById('editMediaId').value=id;document.getElementById('editTitle').value=title;document.getElementById('editDesc').value=desc;document.getElementById('editFileInput').value='';document.getElementById('editError').classList.add('hidden');document.getElementById('editModal').classList.add('open');document.body.style.overflow='hidden';}
function closeEditModal(){document.getElementById('editModal').classList.remove('open');document.body.style.overflow='';}
async function submitEdit(){
    const id=document.getElementById('editMediaId').value,title=document.getElementById('editTitle').value.trim(),desc=document.getElementById('editDesc').value.trim(),file=document.getElementById('editFileInput').files[0];
    const err=document.getElementById('editError'); err.classList.add('hidden');
    if(!title){err.textContent='Please enter a title.';err.classList.remove('hidden');return;}
    const btn=document.getElementById('editSubmitBtn'); btn.disabled=true;
    btn.innerHTML='<span class="material-symbols-outlined icon-outline text-[16px]" style="animation:spin .7s linear infinite">sync</span> Saving…';
    const fd=new FormData(); fd.append('_token',CSRF_TOKEN); fd.append('_method','PUT'); fd.append('title',title); fd.append('description',desc); if(file) fd.append('file',file);
    try{const r=await fetch('/media/'+id,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},body:fd});const d=await r.json();if(!r.ok)throw new Error(d.message||'Save failed.');closeEditModal();window.location.reload();}
    catch(e){err.textContent='⚠ '+e.message;err.classList.remove('hidden');btn.disabled=false;btn.innerHTML='<span class="material-symbols-outlined icon-outline text-[16px]">save</span> Save Changes';}
}

// ── Delete modal ──────────────────────────────────────────────────────────────
function openDeleteModal(id,title){document.getElementById('deleteMediaId').value=id;document.getElementById('deleteMediaTitle').textContent='"'+title+'"';document.getElementById('deleteModal').classList.add('open');document.body.style.overflow='hidden';}
function closeDeleteModal(){document.getElementById('deleteModal').classList.remove('open');document.body.style.overflow='';}
async function confirmDelete(){
    const id=document.getElementById('deleteMediaId').value,btn=document.getElementById('deleteConfirmBtn');
    btn.disabled=true;btn.textContent='Deleting…';
    try{const r=await fetch('/media/'+id,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF_TOKEN}});const d=await r.json();if(!r.ok)throw new Error(d.message||'Delete failed.');closeDeleteModal();window.location.reload();}
    catch(e){btn.disabled=false;btn.textContent='Delete';alert('⚠ '+e.message);}
}

// ── Keyboard shortcuts ────────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    const po = document.getElementById('mediaPreviewModal').classList.contains('open');
    if (e.key==='Escape'){if(po)closePreview();else if(document.getElementById('uploadModal').classList.contains('open'))closeUploadModal();else if(document.getElementById('editModal').classList.contains('open'))closeEditModal();else if(document.getElementById('deleteModal').classList.contains('open'))closeDeleteModal();}
    if (po){if(e.key==='ArrowLeft')navigatePreview(-1);if(e.key==='ArrowRight')navigatePreview(1);}
});
['uploadModal','editModal','deleteModal'].forEach(id=>{
    document.getElementById(id).addEventListener('click',function(e){if(e.target===this){if(id==='uploadModal')closeUploadModal();else if(id==='editModal')closeEditModal();else closeDeleteModal();}});
});
document.getElementById('mediaPreviewModal').addEventListener('click',e=>{if(e.target===document.getElementById('mediaPreviewModal'))closePreview();});

// ── Listen for media search queries coming from the global nav search bar ─────
// When the nav search bar fires a custom event (or the URL has ?media_search=),
// we apply it as a filter without reloading.
(function () {
    // Support URL param ?media_search=xxx on initial load
    const urlParams = new URLSearchParams(window.location.search);
    const initialSearch = urlParams.get('media_search') || '';
    applyFilters(initialSearch);

    // Listen for messages from the nav search bar on this same page
    window.addEventListener('mediaSearch', e => {
        applyFilters(e.detail || '');
    });
})();
</script>

@endsection
