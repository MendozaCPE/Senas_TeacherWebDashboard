@extends('layouts.admin')
@section('title', 'System Media')
@section('content')

<style>
/* ── Filter tab pills ── identical to teacher media page ───────────────────── */
.filter-tab {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 99px; font-size: 12px; font-weight: 700;
    cursor: pointer; border: 1.5px solid #e2e8f0; transition: all 0.15s;
    white-space: nowrap; background: #fff; color: #64748b;
}
.filter-tab:hover  { background: #f1f5f9; border-color: #cbd5e1; color: #0d326b; }
.filter-tab.active { background: #0d326b; color: #fff; border-color: #0d326b; }

/* ── Media card grid ── identical to teacher media page ────────────────────── */
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
.media-thumb {
    position: relative; width: 100%; padding-top: 56.25%;
    background: #f1f5f9; overflow: hidden; flex-shrink: 0;
}
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
    background: rgba(13,50,107,0.85); color: #fff;
}
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
.media-card-body  { padding: 12px 14px 10px; }
.media-card-title {
    font-size: 13px; font-weight: 700; color: #0d326b;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px;
}
.media-card-meta  { font-size: 11px; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.media-card-actions { display: flex; align-items: center; gap: 4px; margin-top: 8px; padding-top: 8px; border-top: 1px solid #f1f5f9; }
.card-action-btn {
    flex: 1; padding: 6px 4px; border-radius: 8px; border: none;
    font-size: 11px; font-weight: 700; cursor: pointer;
    transition: background 0.15s, color 0.15s;
    display: flex; align-items: center; justify-content: center;
}
.card-action-btn.preview-btn { background: #eff6ff; color: #1a6fd4; }
.card-action-btn.preview-btn:hover { background: #dbeafe; }
.card-action-btn.replace-btn { background: #f8fafc; color: #64748b; }
.card-action-btn.replace-btn:hover { background: #f1f5f9; color: #0d326b; }
.card-action-btn.delete-btn { background: #fef2f2; color: #dc2626; }
.card-action-btn.delete-btn:hover { background: #fee2e2; }

/* ── Stat chip ─────────────────────────────────────────────────────────────── */
.stat-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 99px; font-size: 11px; font-weight: 700;
    background: #f8fafc; border: 1.5px solid #e2e8f0; color: #475569;
}

/* ── Preview modal ── same as teacher media page ───────────────────────────── */
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
.preview-modal-close:hover { transform: scale(1.1); }
.preview-nav-btn {
    position: fixed; top: 50%; transform: translateY(-50%); width: 46px; height: 46px;
    background: rgba(255,255,255,0.88); border: none; border-radius: 50%; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.25); transition: transform 0.18s; z-index: 10001; color: #0d326b;
}
.preview-nav-btn:hover { transform: translateY(-50%) scale(1.08); }
.preview-nav-btn.prev { left: 16px; }
.preview-nav-btn.next { right: 16px; }
.preview-nav-btn:disabled { opacity: 0.35; pointer-events: none; }
.preview-media-wrap {
    background: #000; border-radius: 16px 16px 0 0; overflow: hidden;
    display: flex; align-items: center; justify-content: center; min-height: 280px; max-height: 60vh;
}
.preview-media-wrap img, .preview-media-wrap video { max-width: 100%; max-height: 60vh; object-fit: contain; }
.preview-info-panel { background: #fff; border-radius: 0 0 16px 16px; padding: 18px 22px; }

/* ── Modals ────────────────────────────────────────────────────────────────── */
#uploadModal, #editModal, #replaceModal, #deleteModal {
    display: none; position: fixed; inset: 0; z-index: 9998;
    background: rgba(5,10,25,0.6); backdrop-filter: blur(4px);
    align-items: center; justify-content: center;
}
#uploadModal.open, #editModal.open, #replaceModal.open, #deleteModal.open { display: flex; }
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

{{-- ── SKELETON ─────────────────────────────────────────────────────────── --}}
<div id="page-skeleton" class="pt-4" aria-hidden="true">
    {{-- Toolbar --}}
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-6 py-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex flex-col gap-1.5">
                <div class="skeleton h-2 rounded w-10"></div>
                <div class="flex gap-1.5">
                    @for($i=0;$i<4;$i++)<div class="skeleton h-8 rounded-full w-20"></div>@endfor
                </div>
            </div>
            <div class="w-px h-10 bg-slate-100 mx-1"></div>
            <div class="flex flex-col gap-1.5">
                <div class="skeleton h-2 rounded w-12"></div>
                <div class="flex gap-1.5">
                    @for($i=0;$i<3;$i++)<div class="skeleton h-8 rounded-full w-20"></div>@endfor
                </div>
            </div>
            <div class="ml-auto flex gap-2">
                <div class="skeleton h-9 rounded-full w-48"></div>
                <div class="skeleton h-9 rounded-full w-28"></div>
            </div>
        </div>
    </div>
    {{-- Media grid --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px;">
        @for($i=0;$i<12;$i++)
        <div class="bg-white rounded-[18px] border border-slate-100 overflow-hidden shadow-sm">
            <div class="skeleton w-full" style="padding-top:56.25%;"></div>
            <div class="p-3 flex flex-col gap-2">
                <div class="skeleton h-3 rounded w-3/4"></div>
                <div class="skeleton h-2 rounded w-1/2"></div>
                <div class="flex gap-1.5 pt-1.5 border-t border-slate-100 mt-1">
                    <div class="skeleton h-7 rounded-lg flex-1"></div>
                    <div class="skeleton h-7 rounded-lg flex-1"></div>
                    <div class="skeleton h-7 rounded-lg flex-1"></div>
                </div>
            </div>
        </div>
        @endfor
    </div>
</div>
{{-- ── END SKELETON ─────────────────────────────────────────────────────── --}}

<div class="skeleton-hide">

{{-- ── Toolbar Card ─────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-6 py-4 mb-6">
    <div class="flex flex-wrap items-center gap-3">

        {{-- Type filter --}}
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

        {{-- Upload button --}}
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

{{-- ── Media Grid ───────────────────────────────────────────────────────────── --}}
<div class="media-grid-wrap">
    <div class="media-grid" id="mediaGrid"></div>
    <div id="emptyState" class="empty-media hidden">
        <div class="w-20 h-20 rounded-3xl bg-[#0d326b]/08 flex items-center justify-center mx-auto mb-5">
            <span class="material-symbols-outlined text-[#0d326b] text-[40px]">perm_media</span>
        </div>
        <h3 class="text-xl font-bold text-[#0d326b] mb-2">No system media found</h3>
        <p class="text-slate-500 text-sm mb-4">Adjust your filters or upload a new system media file.</p>
        <button onclick="openUploadModal()"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-bold text-sm transition-all shadow-md hover:opacity-90"
                style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 60%, #1a6fd4 100%);">
            <span class="material-symbols-outlined icon-outline text-[18px]">upload</span>
            Upload System Media
        </button>
    </div>
</div>

{{-- ── Preview Modal ── identical styling to teacher media page ────────────── --}}
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
                    <span class="source-badge" style="position:static;font-size:10px;padding:4px 12px;">System</span>
                    <span id="previewTypeBadge" class="type-badge" style="position:static;font-size:10px;padding:4px 10px;"></span>
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
                <h3 class="text-[17px] font-bold text-[#0d326b]">Upload System Media</h3>
                <p class="text-[12px] text-slate-400">Add a new default sign-language video or image</p>
            </div>
        </div>
        <div id="uploadError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium"></div>
        <div class="space-y-4">

            {{-- Title --}}
            <div>
                <label class="block text-[13px] font-bold text-slate-600 mb-1.5">
                    Title <span class="text-red-500">*</span>
                    <span class="text-slate-400 font-normal ml-1">(becomes the filename)</span>
                </label>
                <input type="text" id="uploadTitle" placeholder="e.g., Hello, Number Five, Letter A…"
                       oninput="updateFilenamePreview()"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-[13px] focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/15 outline-none transition-all">
            </div>

            {{-- Folder / Category --}}
            <div>
                <label class="block text-[13px] font-bold text-slate-600 mb-1.5">Category / Folder</label>
                <select id="uploadFolder" onchange="handleFolderChange(this)"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-[13px] focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/15 outline-none transition-all bg-white">
                    <option value="Alphabets">Alphabets</option>
                    <option value="Numbers">Numbers</option>
                    <option value="Greetings">Greetings</option>
                    <option value="Survival">Survival</option>
                    <option value="__new__">＋ Create new folder…</option>
                </select>
                {{-- New folder input, hidden until "+ Create new folder" is selected --}}
                <div id="newFolderWrap" class="hidden mt-2">
                    <input type="text" id="newFolderName"
                           placeholder="New folder name, e.g. Emotions"
                           oninput="sanitizeNewFolder(this)"
                           class="w-full px-4 py-2.5 border border-[#0d326b] rounded-xl text-[13px] focus:ring-2 focus:ring-[#0d326b]/15 outline-none transition-all">
                    <p class="text-[11px] text-slate-400 mt-1">Letters, numbers and spaces only. Spaces become underscores.</p>
                </div>
            </div>

            {{-- Dropzone --}}
            <div class="upload-dropzone" id="dropzone"
                 ondragover="event.preventDefault();this.classList.add('dragover')"
                 ondragleave="this.classList.remove('dragover')"
                 ondrop="handleDrop(event)">
                <input type="file" id="uploadFileInput" accept="image/*,video/*,.gif" onchange="handleFileSelect(this)">
                <img id="uploadThumb" class="upload-preview-thumb" src="" alt="">
                <div id="dropzoneContent">
                    <span class="material-symbols-outlined text-slate-300 text-[40px] block mb-2">cloud_upload</span>
                    <p class="font-bold text-slate-500 text-[14px]">Click or drag file here</p>
                    <p class="text-[12px] text-slate-400 mt-1">JPG, PNG, GIF, WebP, MP4, MOV — max 200 MB</p>
                </div>
                <div id="dropzoneFilename" class="text-[13px] font-semibold text-[#0d326b] mt-2 hidden"></div>
            </div>

            <div class="upload-progress-bar-wrap" id="uploadProgressWrap">
                <div class="upload-progress-bar" id="uploadProgressBar"></div>
            </div>
        </div>
        <div class="flex gap-3 mt-5">
            <button type="button" onclick="closeUploadModal()"
                    class="flex-1 py-2.5 border border-slate-200 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="button" onclick="submitUpload()" id="uploadSubmitBtn"
                    class="flex-1 py-2.5 text-white font-bold text-sm rounded-xl transition-all hover:opacity-90 flex items-center justify-center gap-2"
                    style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 60%, #1a6fd4 100%);">
                <span class="material-symbols-outlined icon-outline text-[16px]">upload</span> Upload
            </button>
        </div>
    </div>
</div>

{{-- ── Edit Modal ── same style as teacher media edit ─────────────────────── --}}
<div id="editModal">
    <div class="bg-white rounded-3xl p-7 w-full max-w-md mx-4 shadow-2xl relative">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <span class="material-symbols-outlined text-amber-600 text-[20px]">edit</span>
            </div>
            <h3 class="text-[17px] font-bold text-[#0d326b]">Edit Media Info</h3>
        </div>
        <input type="hidden" id="editMediaId">
        <div id="editError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium"></div>
        <div class="space-y-4">
            <div>
                <label class="block text-[13px] font-bold text-slate-600 mb-1.5">Display Name <span class="text-red-500">*</span></label>
                <input type="text" id="editDisplayName"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-[13px] focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/15 outline-none transition-all">
            </div>
            <div>
                <label class="block text-[13px] font-bold text-slate-600 mb-1.5">Description <span class="text-slate-400 font-normal">(optional)</span></label>
                <textarea id="editDescription" rows="3"
                          class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-[13px] focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/15 outline-none transition-all resize-none"></textarea>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="editIsPrimary" class="w-4 h-4 accent-[#0d326b]">
                <span class="text-[13px] font-semibold text-slate-600">Set as primary image for this gesture</span>
            </label>
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

{{-- ── Replace Modal — two-step: upload → confirm ─────────────────────────── --}}
<div id="replaceModal">
    <div class="bg-white rounded-3xl p-7 w-full max-w-md mx-4 shadow-2xl relative" style="max-height:90vh;overflow-y:auto;">
        <button onclick="cancelReplace()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>

        {{-- Step 1: pick a file --}}
        <div id="replaceStep1">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-amber-600 text-[20px]">find_replace</span>
                </div>
                <div>
                    <h3 class="text-[17px] font-bold text-[#0d326b]">Replace System Media</h3>
                    <p class="text-[12px] text-slate-400">Select the replacement file first</p>
                </div>
            </div>
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl text-blue-800 text-[12px] font-medium flex items-start gap-2">
                <span class="material-symbols-outlined text-[16px] mt-0.5 flex-shrink-0">info</span>
                <span>Replacing <strong id="replaceCurrentFilename" class="font-mono"></strong>. The original filename will be kept — all lessons will automatically display the new media.</span>
            </div>
            <input type="hidden" id="replaceMediaId">
            <div id="replaceError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium"></div>
            <div>
                <label class="block text-[13px] font-bold text-slate-600 mb-1.5">New file <span class="text-red-500">*</span></label>
                <input type="file" id="replaceFileInput" accept="image/*,video/*,.gif"
                       class="w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#0d326b]/10 file:text-[#0d326b] hover:file:bg-[#0d326b]/20">
            </div>
            <div id="replaceUploadProgress" class="hidden mt-3">
                <div class="flex items-center gap-2 text-[12px] text-slate-500 font-medium mb-1.5">
                    <span class="material-symbols-outlined text-[14px] icon-outline" style="animation:spin .7s linear infinite">sync</span>
                    Uploading to staging area…
                </div>
                <div class="bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div id="replaceUploadBar" class="h-1.5 rounded-full bg-[#0d326b] transition-all" style="width:0%"></div>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="button" onclick="cancelReplace()"
                        class="flex-1 py-2.5 border border-slate-200 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="button" onclick="stageReplaceFile()" id="replaceStageBtn"
                        class="flex-1 py-2.5 bg-[#0d326b] hover:bg-[#154188] text-white font-bold text-sm rounded-xl transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined icon-outline text-[16px]">upload</span> Upload &amp; Review
                </button>
            </div>
        </div>

        {{-- Step 2: confirmation warning --}}
        <div id="replaceStep2" class="hidden">
            <div class="text-center mb-5">
                <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-amber-600 text-3xl">warning</span>
                </div>
                <h3 class="text-[18px] font-bold text-slate-800 mb-2">Confirm Replacement</h3>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Are you sure you want to replace
                    <strong id="confirmFilename" class="text-slate-700 font-mono text-[13px]"></strong>?
                </p>
            </div>
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-[13px] text-amber-800 space-y-2.5 mb-5">
                <p class="flex items-center gap-2.5"><span class="material-symbols-outlined text-[18px] flex-shrink-0">delete</span><span>The existing file will be <strong>permanently deleted</strong>.</span></p>
                <p class="flex items-center gap-2.5"><span class="material-symbols-outlined text-[18px] flex-shrink-0">check_circle</span><span>The new file will be saved with the <strong>exact same filename</strong>.</span></p>
                <p class="flex items-center gap-2.5"><span class="material-symbols-outlined text-[18px] flex-shrink-0">link</span><span>All lessons using this file will <strong>automatically show the new media</strong>.</span></p>
                <p class="flex items-center gap-2.5"><span class="material-symbols-outlined text-[18px] flex-shrink-0">block</span><span>This action <strong>cannot be undone</strong> unless the original was backed up.</span></p>
            </div>
            <div id="replaceConfirmError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium"></div>
            <div class="flex gap-3">
                <button type="button" onclick="cancelReplace()"
                        class="flex-1 py-2.5 border border-slate-200 rounded-2xl text-slate-600 font-bold hover:bg-slate-50 transition-colors">
                    Cancel — Keep Original
                </button>
                <button type="button" onclick="confirmReplaceFile()" id="replaceConfirmBtn"
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-2xl transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined icon-outline text-[16px]">find_replace</span> Yes, Replace
                </button>
            </div>
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
            <h3 class="text-[18px] font-bold text-slate-800 mb-1">Delete System Media?</h3>
            <p class="text-slate-500 text-sm">
                You're about to delete <strong id="deleteMediaTitle" class="text-slate-700"></strong>.
                This cannot be undone.
            </p>
            <div id="deleteWarning" class="hidden mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-[12px] font-medium text-left flex items-start gap-2">
                <span class="material-symbols-outlined text-[16px] mt-0.5 flex-shrink-0">warning</span>
                <span id="deleteWarningMsg"></span>
            </div>
        </div>
        <input type="hidden" id="deleteMediaId">
        <input type="hidden" id="deleteForce" value="0">
        <div id="deleteNormalBtns" class="flex gap-3">
            <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 py-2.5 border border-slate-200 rounded-2xl text-slate-600 font-bold hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="button" onclick="confirmDelete()" id="deleteConfirmBtn"
                    class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white font-bold rounded-2xl transition-colors">Delete</button>
        </div>
        <div id="deleteBlockedBtns" class="hidden flex-col gap-2 mt-0">
            <button type="button" onclick="closeDeleteModal()"
                    class="w-full py-2.5 border border-slate-200 rounded-2xl text-slate-600 font-bold hover:bg-slate-50 transition-colors">Keep It</button>
            <button type="button" onclick="forceDeleteMedia()"
                    class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-2xl transition-colors text-sm">
                Delete Anyway (gesture loses its media)
            </button>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

// All system media loaded server-side — client-side filtering like teacher media page
const ALL_MEDIA = @json($mediaJs);

let activeType  = 'all';
let activeSort  = 'newest';
let filteredMedia = [];
let currentPreviewIndex = -1;

// ── Filter setters ────────────────────────────────────────────────────────────
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

    if (activeType !== 'all') result = result.filter(i => i.media_type === activeType);

    if (searchOverride && searchOverride.trim()) {
        const q = searchOverride.trim().toLowerCase();
        result = result.filter(i =>
            i.title.toLowerCase().includes(q) ||
            i.file_name.toLowerCase().includes(q) ||
            (i.module && i.module.toLowerCase().includes(q))
        );
    }

    if (activeSort === 'oldest') {
        result.sort((a, b) => (a.created_at || '').localeCompare(b.created_at || ''));
    } else if (activeSort === 'alpha') {
        result.sort((a, b) => a.title.localeCompare(b.title));
    } else {
        result.sort((a, b) => (b.created_at || '').localeCompare(a.created_at || ''));
    }

    filteredMedia = result;
    renderGrid(result);
    updateStats(result);
}

// ── Render grid ───────────────────────────────────────────────────────────────
function renderGrid(items) {
    const grid    = document.getElementById('mediaGrid');
    const emptyEl = document.getElementById('emptyState');

    if (items.length === 0) {
        grid.innerHTML = '';
        emptyEl.classList.remove('hidden');
        return;
    }

    emptyEl.classList.add('hidden');
    grid.innerHTML = items.map((item, i) => buildCard(item, i)).join('');

    grid.querySelectorAll('video[data-hover]').forEach(vid => {
        vid.addEventListener('mouseenter', () => vid.play());
        vid.addEventListener('mouseleave', () => { vid.pause(); vid.currentTime = 0; });
    });
}

function buildCard(item, idx) {
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

    return `
    <div class="media-card" onclick="openPreview(${idx})">
        <div class="media-thumb">
            ${thumb}
            <span class="source-badge">System</span>
            ${typeBadge}
        </div>
        <div class="media-card-body">
            <div class="media-card-title" title="${eh(item.title)}">${eh(item.title)}</div>
            <div class="media-card-meta">${metaParts.join(' · ')}</div>
            <div class="media-card-actions" onclick="event.stopPropagation()">
                <button class="card-action-btn preview-btn" title="Preview" onclick="event.stopPropagation();openPreview(${idx})">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                </button>
                <button class="card-action-btn replace-btn" title="Edit"
                        data-media-id="${item.raw_id}"
                        data-title="${eh(item.title)}"
                        data-description="${eh(item.description||'')}"
                        data-is-primary="${item.is_primary ? 1 : 0}"
                        onclick="event.stopPropagation();openEditFromBtn(this)">
                    <span class="material-symbols-outlined text-[16px]">edit</span>
                </button>
                <button class="card-action-btn replace-btn" title="Replace file"
                        data-media-id="${item.raw_id}"
                        data-file-name="${eh(item.file_name)}"
                        onclick="event.stopPropagation();openReplaceFromBtn(this)">
                    <span class="material-symbols-outlined text-[16px]">find_replace</span>
                </button>
                <button class="card-action-btn delete-btn" title="Delete"
                        data-media-id="${item.raw_id}"
                        data-title="${eh(item.title)}"
                        onclick="event.stopPropagation();openDeleteFromBtn(this)">
                    <span class="material-symbols-outlined text-[16px]">delete</span>
                </button>
            </div>
        </div>
    </div>`;
}

// ── Stats bar ─────────────────────────────────────────────────────────────────
function updateStats(items) {
    const total  = items.length;
    const images = items.filter(i => i.media_type === 'image').length;
    const videos = items.filter(i => i.media_type === 'video').length;
    const gifs   = items.filter(i => i.media_type === 'gif').length;

    document.getElementById('statsBar').innerHTML = `
        <span class="stat-chip"><span class="material-symbols-outlined text-[14px] text-[#0d326b]">perm_media</span>${total} System</span>
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
    if (item.module)     meta.push(item.module);
    if (item.owner)      meta.push('By ' + item.owner);
    if (item.created_at) meta.push(item.created_at);
    if (item.file_name)  meta.push(item.file_name);
    document.getElementById('previewMeta').textContent = meta.join(' · ');
    const descEl = document.getElementById('previewDesc');
    descEl.textContent = item.description || '';
    descEl.style.display = item.description ? 'block' : 'none';
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
function openUploadModal()  { resetUploadModal(); document.getElementById('uploadModal').classList.add('open'); document.body.style.overflow='hidden'; }
function closeUploadModal() { document.getElementById('uploadModal').classList.remove('open'); document.body.style.overflow=''; resetUploadModal(); }
function resetUploadModal() {
    selectedFile = null;
    document.getElementById('uploadFileInput').value = '';
    document.getElementById('uploadTitle').value = '';
    document.getElementById('uploadFolder').value = 'Alphabets';
    document.getElementById('newFolderWrap').classList.add('hidden');
    document.getElementById('newFolderName').value = '';
    document.getElementById('uploadThumb').style.display = 'none';
    document.getElementById('dropzoneContent').style.display = 'block';
    document.getElementById('dropzoneFilename').classList.add('hidden');
    document.getElementById('uploadError').classList.add('hidden');
    document.getElementById('uploadProgressWrap').style.display = 'none';
    document.getElementById('uploadProgressBar').style.width = '0%';
    const btn = document.getElementById('uploadSubmitBtn'); btn.disabled = false;
    btn.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]">upload</span> Upload';
}

// Show/hide the new-folder text input
function handleFolderChange(sel) {
    const wrap = document.getElementById('newFolderWrap');
    if (sel.value === '__new__') {
        wrap.classList.remove('hidden');
        document.getElementById('newFolderName').focus();
    } else {
        wrap.classList.add('hidden');
        document.getElementById('newFolderName').value = '';
    }
}

// Strip anything that would be illegal in a folder name
function sanitizeNewFolder(input) {
    input.value = input.value.replace(/[^a-zA-Z0-9 _-]/g, '');
}

// Resolve the final folder value (existing or newly typed)
function resolveFolder() {
    const sel = document.getElementById('uploadFolder').value;
    if (sel === '__new__') {
        const raw = document.getElementById('newFolderName').value.trim();
        return raw.replace(/\s+/g, '_');   // spaces → underscores
    }
    return sel;
}
function handleDrop(e) { e.preventDefault(); document.getElementById('dropzone').classList.remove('dragover'); if(e.dataTransfer.files[0]) setSelectedFile(e.dataTransfer.files[0]); }
function handleFileSelect(input) { if(input.files&&input.files[0]) setSelectedFile(input.files[0]); }
function setSelectedFile(file) {
    selectedFile = file;
    const size = file.size>1048576 ? (file.size/1048576).toFixed(1)+' MB' : (file.size/1024).toFixed(0)+' KB';
    const nm = document.getElementById('dropzoneFilename');
    nm.classList.remove('hidden');
    document.getElementById('dropzoneContent').style.display = 'none';
    // Auto-fill title from filename if empty
    const ti = document.getElementById('uploadTitle');
    if (!ti.value.trim()) ti.value = file.name.replace(/\.[^.]+$/, '').replace(/[_\-]+/g, ' ');
    updateFilenamePreview();
    if(file.type.startsWith('image/')) { const r=new FileReader(); r.onload=e=>{const t=document.getElementById('uploadThumb');t.src=e.target.result;t.style.display='block';}; r.readAsDataURL(file); }
}

// Show what the actual saved filename will be
function updateFilenamePreview() {
    if (!selectedFile) return;
    const title = document.getElementById('uploadTitle').value.trim();
    const ext   = selectedFile.name.split('.').pop();
    const size  = selectedFile.size>1048576 ? (selectedFile.size/1048576).toFixed(1)+' MB' : (selectedFile.size/1024).toFixed(0)+' KB';
    const slug  = title ? title.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9._-]/g, '') + '.' + ext : selectedFile.name;
    document.getElementById('dropzoneFilename').innerHTML =
        '📎 Will be saved as: <strong>' + eh(slug) + '</strong> (' + size + ')';
}
async function submitUpload() {
    const title  = document.getElementById('uploadTitle').value.trim();
    const folder = resolveFolder();
    const err    = document.getElementById('uploadError'); err.classList.add('hidden');
    if (!selectedFile) { err.textContent='Please select a file.'; err.classList.remove('hidden'); return; }
    if (!title)        { err.textContent='Please enter a title.'; err.classList.remove('hidden'); return; }
    if (!folder)       { err.textContent='Please enter a name for the new folder.'; err.classList.remove('hidden'); return; }
    const btn = document.getElementById('uploadSubmitBtn'); btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]" style="animation:spin .7s linear infinite">sync</span> Uploading…';
    const pw = document.getElementById('uploadProgressWrap'); pw.style.display = 'block';
    const pb = document.getElementById('uploadProgressBar');
    const fd = new FormData();
    fd.append('_token', CSRF_TOKEN);
    fd.append('file', selectedFile);
    fd.append('title', title);
    fd.append('folder', folder);
    try {
        await new Promise((res,rej) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route('admin.media.upload') }}');
            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.onprogress = e => { if(e.lengthComputable) pb.style.width = Math.round(e.loaded/e.total*90)+'%'; };
            xhr.onload = () => {
                pb.style.width = '100%';
                if(xhr.status>=200&&xhr.status<300) res(JSON.parse(xhr.responseText));
                else { try{rej(new Error(JSON.parse(xhr.responseText).message));}catch{rej(new Error('Upload failed.'));} }
            };
            xhr.onerror = () => rej(new Error('Network error.'));
            xhr.send(fd);
        });
        closeUploadModal(); window.location.href = window.location.pathname + '?_t=' + Date.now();
    } catch(e) {
        pw.style.display='none'; err.textContent='⚠ '+e.message; err.classList.remove('hidden');
        btn.disabled=false; btn.innerHTML='<span class="material-symbols-outlined icon-outline text-[16px]">upload</span> Upload';
    }
}

// ── Data-attribute helpers (avoids escaping issues in onclick strings) ────────
function openEditFromBtn(btn) {
    openEditModal(
        btn.dataset.mediaId,
        btn.dataset.title,
        btn.dataset.description,
        parseInt(btn.dataset.isPrimary)
    );
}
function openReplaceFromBtn(btn) {
    openReplaceModal(btn.dataset.mediaId, btn.dataset.fileName);
}
function openDeleteFromBtn(btn) {
    openDeleteModal(btn.dataset.mediaId, btn.dataset.title);
}

// ── Edit modal ────────────────────────────────────────────────────────────────
function openEditModal(id, displayName, description, isPrimary) {
    document.getElementById('editMediaId').value       = id;
    document.getElementById('editDisplayName').value   = displayName;
    document.getElementById('editDescription').value   = description;
    document.getElementById('editIsPrimary').checked   = !!isPrimary;  // coerce 1/0 → true/false
    document.getElementById('editError').classList.add('hidden');
    const btn = document.getElementById('editSubmitBtn');
    btn.disabled = false;
    btn.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]">save</span> Save Changes';
    document.getElementById('editModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
    document.body.style.overflow = '';
}
async function submitEdit() {
    const id          = document.getElementById('editMediaId').value;
    const displayName = document.getElementById('editDisplayName').value.trim();
    const description = document.getElementById('editDescription').value.trim();
    const isPrimary   = document.getElementById('editIsPrimary').checked;
    const err         = document.getElementById('editError'); err.classList.add('hidden');
    if (!displayName) { err.textContent = 'Please enter a display name.'; err.classList.remove('hidden'); return; }
    const btn = document.getElementById('editSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]" style="animation:spin .7s linear infinite">sync</span> Saving…';

    const fd = new FormData();
    fd.append('_token', CSRF_TOKEN);
    fd.append('display_name', displayName);
    fd.append('description', description);
    fd.append('is_primary', isPrimary ? '1' : '0');

    try {
        const r = await fetch('/admin/media/' + id + '/edit', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: fd
        });
        const d = await r.json();
        if (!r.ok) throw new Error(d.message || 'Save failed.');
        closeEditModal();
        window.location.href = window.location.pathname + '?_t=' + Date.now();
    } catch(e) {
        err.textContent = '⚠ ' + e.message; err.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]">save</span> Save Changes';
    }
}

// ── Replace modal — two-step: stage → confirm ────────────────────────────────
let replaceTempKey = null;
let replaceMediaIdVal = null;

function openReplaceModal(id, filename) {
    replaceMediaIdVal = id;
    replaceTempKey = null;
    document.getElementById('replaceMediaId').value   = id;
    document.getElementById('replaceCurrentFilename').textContent = filename;
    document.getElementById('confirmFilename').textContent        = filename;
    document.getElementById('replaceFileInput').value = '';
    document.getElementById('replaceError').classList.add('hidden');
    document.getElementById('replaceConfirmError').classList.add('hidden');
    document.getElementById('replaceUploadProgress').classList.add('hidden');
    document.getElementById('replaceUploadBar').style.width = '0%';
    document.getElementById('replaceStep1').classList.remove('hidden');
    document.getElementById('replaceStep2').classList.add('hidden');
    const sb = document.getElementById('replaceStageBtn');
    sb.disabled = false;
    sb.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]">upload</span> Upload &amp; Review';
    document.getElementById('replaceModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

async function cancelReplace() {
    // If a temp file was staged, clean it up on the server
    if (replaceTempKey && replaceMediaIdVal) {
        try {
            await fetch('/admin/media/' + replaceMediaIdVal + '/cancel-replace', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ temp_key: replaceTempKey })
            });
        } catch(e) { /* best-effort cleanup */ }
    }
    replaceTempKey = null;
    replaceMediaIdVal = null;
    document.getElementById('replaceModal').classList.remove('open');
    document.body.style.overflow = '';
}

async function stageReplaceFile() {
    const id   = document.getElementById('replaceMediaId').value;
    const file = document.getElementById('replaceFileInput').files[0];
    const err  = document.getElementById('replaceError'); err.classList.add('hidden');
    if (!file) { err.textContent = 'Please select a replacement file.'; err.classList.remove('hidden'); return; }

    const btn = document.getElementById('replaceStageBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]" style="animation:spin .7s linear infinite">sync</span> Uploading…';
    document.getElementById('replaceUploadProgress').classList.remove('hidden');
    const bar = document.getElementById('replaceUploadBar');

    const fd = new FormData();
    fd.append('_token', CSRF_TOKEN);
    fd.append('file', file);

    try {
        const data = await new Promise((res, rej) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/admin/media/' + id + '/stage');
            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.onprogress = e => { if(e.lengthComputable) bar.style.width = Math.round(e.loaded/e.total*95)+'%'; };
            xhr.onload = () => {
                bar.style.width = '100%';
                if(xhr.status>=200&&xhr.status<300) res(JSON.parse(xhr.responseText));
                else { try{rej(new Error(JSON.parse(xhr.responseText).message));}catch{rej(new Error('Upload failed.'));} }
            };
            xhr.onerror = () => rej(new Error('Network error.'));
            xhr.send(fd);
        });

        replaceTempKey = data.temp_key;
        // Move to step 2: confirmation
        document.getElementById('replaceStep1').classList.add('hidden');
        document.getElementById('replaceStep2').classList.remove('hidden');

    } catch(e) {
        err.textContent = '⚠ ' + e.message; err.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]">upload</span> Upload &amp; Review';
        document.getElementById('replaceUploadProgress').classList.add('hidden');
    }
}

async function confirmReplaceFile() {
    const id  = document.getElementById('replaceMediaId').value;
    const err = document.getElementById('replaceConfirmError'); err.classList.add('hidden');
    const btn = document.getElementById('replaceConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]" style="animation:spin .7s linear infinite">sync</span> Replacing…';

    try {
        const r = await fetch('/admin/media/' + id + '/confirm-replace', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ temp_key: replaceTempKey })
        });
        const d = await r.json();
        if (!r.ok) throw new Error(d.message || 'Replace failed.');
        replaceTempKey = null;
        document.getElementById('replaceModal').classList.remove('open');
        document.body.style.overflow = '';
        // Force hard reload with cache buster so browser fetches the new file
        window.location.href = window.location.pathname + '?_t=' + Date.now();
    } catch(e) {
        err.textContent = '⚠ ' + e.message; err.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]">find_replace</span> Yes, Replace';
    }
}

// ── Delete modal ──────────────────────────────────────────────────────────────
function openDeleteModal(id, title) {
    document.getElementById('deleteMediaId').value = id;
    document.getElementById('deleteForce').value   = '0';
    document.getElementById('deleteMediaTitle').textContent = '"' + title + '"';
    document.getElementById('deleteWarning').classList.add('hidden');
    document.getElementById('deleteNormalBtns').style.display = 'flex';
    document.getElementById('deleteBlockedBtns').classList.add('hidden');
    document.getElementById('deleteBlockedBtns').style.display = 'none';
    document.getElementById('deleteModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('open'); document.body.style.overflow=''; }
function forceDeleteMedia() { document.getElementById('deleteForce').value='1'; confirmDelete(); }
async function confirmDelete() {
    const id    = document.getElementById('deleteMediaId').value;
    const force = document.getElementById('deleteForce').value === '1';
    const btn   = document.getElementById('deleteConfirmBtn');
    btn.disabled = true; btn.textContent = 'Deleting…';
    const url = '/admin/media/'+id+(force?'?force=true':'');
    try {
        const r = await fetch(url, { method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF_TOKEN} });
        const d = await r.json();
        if (!r.ok) {
            if (d.blocked) {
                // Show the warning and switch to force-delete mode
                document.getElementById('deleteWarningMsg').textContent = d.message;
                document.getElementById('deleteWarning').classList.remove('hidden');
                document.getElementById('deleteNormalBtns').style.display = 'none';
                document.getElementById('deleteBlockedBtns').classList.remove('hidden');
                document.getElementById('deleteBlockedBtns').style.display = 'flex';
                btn.disabled = false; btn.textContent = 'Delete';
                return;
            }
            throw new Error(d.message || 'Delete failed.');
        }
        closeDeleteModal(); window.location.reload();
    } catch(e) {
        btn.disabled=false; btn.textContent='Delete'; alert('⚠ '+e.message);
    }
}

// ── Keyboard shortcuts ────────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    const po = document.getElementById('mediaPreviewModal').classList.contains('open');
    if (e.key === 'Escape') {
        if (po) closePreview();
        else if (document.getElementById('uploadModal').classList.contains('open'))  closeUploadModal();
        else if (document.getElementById('editModal').classList.contains('open'))    closeEditModal();
        else if (document.getElementById('replaceModal').classList.contains('open')) cancelReplace();
        else if (document.getElementById('deleteModal').classList.contains('open'))  closeDeleteModal();
    }
    if (po) { if(e.key==='ArrowLeft') navigatePreview(-1); if(e.key==='ArrowRight') navigatePreview(1); }
});
['uploadModal','editModal','replaceModal','deleteModal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) {
            if (id==='uploadModal')  closeUploadModal();
            else if (id==='editModal')    closeEditModal();
            else if (id==='replaceModal') cancelReplace();
            else closeDeleteModal();
        }
    });
});
document.getElementById('mediaPreviewModal').addEventListener('click', e => {
    if(e.target===document.getElementById('mediaPreviewModal')) closePreview();
});

// ── Initial render ────────────────────────────────────────────────────────────
(function() {
    const urlParams = new URLSearchParams(window.location.search);
    applyFilters(urlParams.get('search') || '');
})();
</script>

</div>{{-- end skeleton-hide --}}
@endsection
