@extends('layouts.grade-leader')
@section('title', 'System Media')
@section('content')

{{-- ── SKELETON ─────────────────────────────────────────────────────────── --}}
<div id="page-skeleton" class="flex flex-col gap-5 pt-4" aria-hidden="true">
    {{-- Toolbar --}}
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm p-4 flex flex-col gap-3">
        <div class="flex items-center gap-3 flex-wrap">
            <div class="flex gap-2">@for($i=0;$i<4;$i++)<div class="skeleton h-8 rounded-full w-20"></div>@endfor</div>
            <div class="flex gap-2">@for($i=0;$i<3;$i++)<div class="skeleton h-8 rounded-full w-16"></div>@endfor</div>
            <div class="ml-auto skeleton h-9 rounded-xl w-28"></div>
        </div>
        <div class="flex gap-2 pt-3 border-t border-slate-100">
            @for($i=0;$i<4;$i++)<div class="skeleton h-5 rounded-full w-16"></div>@endfor
        </div>
    </div>
    {{-- Media grid --}}
    <div class="grid gap-[18px]" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
        @for($i=0;$i<10;$i++)
        <div class="bg-white rounded-[18px] border border-slate-100 overflow-hidden shadow-sm">
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
    display: flex; flex-direction: column;
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
    background: rgba(0,0,0,0.22); transition: background 0.18s; pointer-events: none;
}
.media-card:hover .play-overlay { background: rgba(0,0,0,0.36); }
.play-icon-circle {
    width: 44px; height: 44px; background: rgba(255,255,255,0.92); border-radius: 50%;
    display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 12px rgba(0,0,0,0.25);
}
.source-badge {
    position: absolute; top: 8px; left: 8px; padding: 3px 10px; border-radius: 99px;
    font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; backdrop-filter: blur(4px);
    background: rgba(13,50,107,0.85); color: #fff; z-index: 2;
}
.type-badge {
    position: absolute; top: 8px; right: 8px; padding: 3px 9px; border-radius: 99px;
    font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; backdrop-filter: blur(4px);
    z-index: 2;
}
.type-badge.image { background: rgba(59,130,246,0.85);  color: #fff; }
.type-badge.video { background: rgba(239,68,68,0.85);   color: #fff; }
.type-badge.gif   { background: rgba(168,85,247,0.85);  color: #fff; }
.gif-badge {
    position: absolute; bottom: 8px; right: 8px; background: rgba(168,85,247,0.85); color: #fff;
    font-size: 9px; font-weight: 800; padding: 2px 7px; border-radius: 99px;
    letter-spacing: 0.08em; text-transform: uppercase; z-index: 2;
}
.media-card-body { padding: 12px 14px 10px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
.media-card-title {
    font-size: 13px; font-weight: 700; color: #0d326b;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px;
}
.media-card-meta { font-size: 11px; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.media-card-actions { display: flex; align-items: center; gap: 4px; margin-top: 10px; padding-top: 8px; border-top: 1px solid #f1f5f9; }
.card-action-btn {
    flex: 1; padding: 6px 0; border-radius: 8px; border: none;
    font-size: 11px; font-weight: 700; cursor: pointer;
    transition: background 0.15s, color 0.15s;
    display: flex; align-items: center; justify-content: center; gap: 4px;
}
.card-action-btn.preview-btn { background: #eff6ff; color: #1a6fd4; }
.card-action-btn.preview-btn:hover { background: #dbeafe; }

/* ── Stat chip ─────────────────────────────────────────────────────────────── */
.stat-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 99px; font-size: 11px; font-weight: 700;
    background: #f8fafc; border: 1.5px solid #e2e8f0; color: #475569; transition: all 0.15s;
}

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

.media-grid-wrap { min-height: 300px; }
.empty-media { text-align: center; padding: 64px 24px; }

@media (max-width: 768px) {
    .media-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
    .preview-nav-btn.prev { left: 4px; } .preview-nav-btn.next { right: 4px; }
}
@media (max-width: 500px) { .media-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; } }
</style>

{{-- ── Single Toolbar Card: search + filters + view-only badge ────────────── --}}
<div class="skeleton-hide">

<div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-6 py-4 mb-6 pt-4">

    {{-- Top row: Search + Type + Sort + View Only badge --}}
    <div class="flex flex-wrap items-center gap-3">

        {{-- Search input --}}
        <div class="flex flex-col gap-1 min-w-[220px] flex-1 sm:flex-initial">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 px-0.5">Search</span>
            <div class="relative">
                <span class="material-symbols-outlined text-slate-400 text-[18px] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">search</span>
                <input type="text" id="mediaSearchInput" placeholder="Search system media or gestures…"
                       oninput="handleSearch(this.value)"
                       class="w-full pl-9 pr-3 py-1.5 rounded-full border border-slate-200 text-xs font-medium text-slate-700 bg-slate-50 focus:bg-white focus:border-[#0d326b] focus:outline-none transition">
            </div>
        </div>

        <div class="h-9 w-px bg-slate-100 hidden sm:block self-end mb-0.5"></div>

        {{-- Type Filter --}}
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
                <button type="button" data-val="default" class="filter-tab active" onclick="setSort(this)">Curriculum Order</button>
                <button type="button" data-val="alpha"   class="filter-tab" onclick="setSort(this)">A–Z</button>
                <button type="button" data-val="newest"  class="filter-tab" onclick="setSort(this)">Newest</button>
            </div>
        </div>

        {{-- View Only Badge --}}
        <div class="ml-auto self-end mb-0.5">
            <div class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold shadow-sm">
                <span class="material-symbols-outlined text-[16px] text-amber-600">visibility</span>
                View Only
            </div>
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
        <h3 class="text-xl font-bold text-[#0d326b] mb-2">No system media found</h3>
        <p class="text-slate-500 text-sm mb-4" id="emptyStateMsg">Adjust your search or filters to see system media.</p>
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
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span id="previewSourceBadge" class="source-badge" style="position:static;font-size:10px;padding:4px 12px;">SYSTEM</span>
                    <span id="previewTypeBadge"   class="type-badge"   style="position:static;font-size:10px;padding:4px 10px;"></span>
                </div>
            </div>
            <div class="mt-3 text-[12px] font-semibold text-slate-400" id="previewCounter"></div>
        </div>
    </div>
</div>

<script>
// ── Media Data & State ─────────────────────────────────────────────────────────
const ALL_MEDIA = {!! json_encode($mediaJs) !!};

let activeType          = 'all';
let activeSort          = 'default';
let currentSearch       = '';
let filteredMedia       = ALL_MEDIA.slice();
let currentPreviewIndex = -1;

// ── Tab click handlers ────────────────────────────────────────────────────────
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
function handleSearch(val) {
    currentSearch = val;
    applyFilters();
}

// ── Core filter + render ──────────────────────────────────────────────────────
function applyFilters() {
    let result = ALL_MEDIA.slice();

    // Type
    if (activeType !== 'all') result = result.filter(i => i.media_type === activeType);

    // Search query
    if (currentSearch && currentSearch.trim()) {
        const q = currentSearch.trim().toLowerCase();
        result = result.filter(i =>
            (i.title && i.title.toLowerCase().includes(q)) ||
            (i.file_name && i.file_name.toLowerCase().includes(q)) ||
            (i.module && i.module.toLowerCase().includes(q))
        );
    }

    // Sort
    if (activeSort === 'alpha') {
        result.sort((a, b) => a.title.localeCompare(b.title));
    } else if (activeSort === 'newest') {
        result.sort((a, b) => (b.created_at || '').localeCompare(a.created_at || ''));
    } else {
        // default: original curriculum order (by index)
        result.sort((a, b) => a.index - b.index);
    }

    filteredMedia = result;
    renderGrid(result, currentSearch);
    updateStats(result);
}

// ── Render grid ───────────────────────────────────────────────────────────────
function renderGrid(items, searchTerm) {
    const grid     = document.getElementById('mediaGrid');
    const emptyEl  = document.getElementById('emptyState');
    const emptyMsg = document.getElementById('emptyStateMsg');

    if (items.length === 0) {
        grid.innerHTML = '';
        emptyEl.classList.remove('hidden');
        emptyMsg.textContent = searchTerm
            ? 'No results for "' + searchTerm + '". Try a different search term.'
            : 'No system media matches the selected filters.';
        return;
    }

    emptyEl.classList.add('hidden');
    grid.innerHTML = items.map((item, i) => buildCard(item, i)).join('');

    // Attach video hover events
    grid.querySelectorAll('video[data-hover]').forEach(vid => {
        vid.addEventListener('mouseenter', () => vid.play().catch(()=>{}));
        vid.addEventListener('mouseleave', () => { vid.pause(); vid.currentTime = 0; });
    });
}

function buildCard(item, idx) {
    let thumb = '';
    if (item.media_type === 'video') {
        thumb = `
            <video src="${eh(item.url)}#t=0.001" preload="metadata" muted playsinline data-hover style="object-fit:cover;width:100%;height:100%;"></video>
            <div class="play-overlay">
                <div class="play-icon-circle">
                    <span class="material-symbols-outlined text-[#0d326b] text-[22px]">play_arrow</span>
                </div>
            </div>`;
    } else {
        thumb = `<img src="${eh(item.url)}" alt="${eh(item.title)}" loading="lazy"
                      style="object-fit:cover;width:100%;height:100%;"
                      onerror="this.onerror=null; this.parentElement.style.background='#f1f5f9';">`;
        if (item.media_type === 'gif') thumb += `<div class="gif-badge">GIF</div>`;
    }

    const typeBadge = item.media_type !== 'gif'
        ? `<span class="type-badge ${item.media_type}">${item.media_type.toUpperCase()}</span>`
        : '';

    const metaParts = [];
    if (item.module)     metaParts.push(eh(item.module));
    if (item.file_size)  metaParts.push(eh(item.file_size));

    return `
    <div class="media-card" onclick="openPreview(${idx})">
        <div class="media-thumb">
            ${thumb}
            <span class="source-badge">System</span>
            ${typeBadge}
        </div>
        <div class="media-card-body">
            <div>
                <div class="media-card-title" title="${eh(item.title)}">${eh(item.title)}</div>
                <div class="media-card-meta">${metaParts.join(' · ') || 'System Media'}</div>
            </div>
            <div class="media-card-actions" onclick="event.stopPropagation()">
                <button class="card-action-btn preview-btn" onclick="event.stopPropagation();openPreview(${idx})">
                    <span class="material-symbols-outlined text-[13px]">visibility</span> Preview
                </button>
            </div>
        </div>
    </div>`;
}

// ── Stats bar ─────────────────────────────────────────────────────────────────
function updateStats(items) {
    const total    = items.length;
    const images   = items.filter(i => i.media_type === 'image').length;
    const videos   = items.filter(i => i.media_type === 'video').length;
    const gifs     = items.filter(i => i.media_type === 'gif').length;

    document.getElementById('statsBar').innerHTML = `
        <span class="stat-chip"><span class="material-symbols-outlined text-[14px] text-[#0d326b]">perm_media</span>${total} System Media</span>
        <span class="stat-chip"><span class="material-symbols-outlined text-[14px] text-blue-500">image</span>${images} Images</span>
        <span class="stat-chip"><span class="material-symbols-outlined text-[14px] text-red-500">videocam</span>${videos} Videos</span>
        ${gifs > 0 ? `<span class="stat-chip"><span class="material-symbols-outlined text-[14px] text-purple-500">gif</span>${gifs} GIFs</span>` : ''}
    `;
}

// ── Escape helpers ────────────────────────────────────────────────────────────
function eh(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// ── Preview modal (Lightbox) ──────────────────────────────────────────────────
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
    if (item.file_name)  meta.push(item.file_name);
    if (item.file_size)  meta.push(item.file_size);
    document.getElementById('previewMeta').textContent = meta.join(' · ') || 'System Media';
    
    const sb = document.getElementById('previewSourceBadge');
    sb.textContent = 'SYSTEM';
    sb.className = 'source-badge system';
    sb.style.cssText = 'position:static;font-size:10px;padding:4px 12px;';
    
    const tb = document.getElementById('previewTypeBadge');
    tb.textContent = item.media_type.toUpperCase();
    tb.className = 'type-badge ' + item.media_type;
    tb.style.cssText = 'position:static;font-size:10px;padding:4px 10px;';
    
    document.getElementById('previewCounter').textContent = (idx + 1) + ' / ' + filteredMedia.length + ' in current view';
    document.getElementById('prevBtn').disabled = idx === 0;
    document.getElementById('nextBtn').disabled = idx === filteredMedia.length - 1;
}

// ── Keyboard shortcuts ────────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    const po = document.getElementById('mediaPreviewModal').classList.contains('open');
    if (e.key === 'Escape' && po) closePreview();
    if (po) {
        if (e.key === 'ArrowLeft') navigatePreview(-1);
        if (e.key === 'ArrowRight') navigatePreview(1);
    }
});
document.getElementById('mediaPreviewModal').addEventListener('click', e => {
    if (e.target === document.getElementById('mediaPreviewModal')) closePreview();
});

// Initial load
applyFilters();
</script>

</div>{{-- /skeleton-hide --}}

@endsection


