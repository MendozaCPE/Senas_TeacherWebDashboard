@extends('layouts.teacher-leader')
@section('title', 'System Media')
@section('content')

{{-- ══════════════════════════════════════════════════════════════════════
     SKELETON
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="page-skeleton" class="flex flex-col gap-4 pt-4 w-full min-w-0" aria-hidden="true">
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5 flex items-center gap-3">
        <div class="skeleton h-10 rounded-full flex-1"></div>
        <div class="skeleton h-10 rounded-full w-28"></div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @for($i=0;$i<10;$i++)
        <div class="bg-white rounded-[20px] border border-slate-100 overflow-hidden">
            <div class="skeleton w-full" style="padding-bottom:72%;"></div>
            <div class="p-3 flex flex-col gap-2">
                <div class="skeleton h-3 rounded w-3/4"></div>
                <div class="skeleton h-2 rounded w-1/2"></div>
            </div>
        </div>
        @endfor
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     REAL CONTENT
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-col gap-4 pt-4 skeleton-hide w-full min-w-0">

    {{-- ── Filter bar ───────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5 flex items-center gap-3 flex-wrap">
        <form method="GET" action="{{ route('teacher-leader.media') }}" class="flex items-center gap-2 flex-wrap w-full">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search media…"
                       class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition">
            </div>

            {{-- Type dropdown --}}
            <div class="relative inline-flex items-center">
                <select name="type" onchange="this.form.submit()"
                        style="appearance:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:9px 34px 9px 14px;font-size:13px;font-weight:600;color:#0d326b;cursor:pointer;outline:none;">
                    <option value="">All Types</option>
                    @foreach($typeCounts as $type => $count)
                    <option value="{{ $type }}" {{ $typeFilter===$type?'selected':'' }}>
                        {{ ucfirst($type) }} ({{ $count }})
                    </option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-2.5 pointer-events-none text-[#0d326b] text-[18px]">expand_more</span>
            </div>

            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-2xl text-[13px] font-bold text-white"
                    style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
                <span class="material-symbols-outlined text-[16px]">search</span> Search
            </button>
            @if($search || $typeFilter)
            <a href="{{ route('teacher-leader.media') }}"
               class="inline-flex items-center gap-1 px-4 py-2.5 rounded-2xl text-[13px] font-semibold text-slate-500 border border-slate-200 bg-white hover:bg-slate-50 transition">
                <span class="material-symbols-outlined text-[15px]">close</span> Clear
            </a>
            @endif
        </form>
    </div>

    {{-- ── Summary + read-only badge ────────────────────────────────────── --}}
    <div class="flex items-center justify-between flex-wrap gap-2">
        <span class="text-[13px] font-semibold text-slate-500">
            <span class="text-[#0d326b] font-black">{{ $media->total() }}</span> files
        </span>
        <div class="flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-blue-50 border border-blue-100">
            <span class="material-symbols-outlined text-[#1a6fd4] text-[15px]">visibility</span>
            <p class="text-[12px] font-semibold text-[#1a6fd4]">View-only — upload, replace &amp; delete are not available.</p>
        </div>
    </div>

    {{-- ── Media grid ───────────────────────────────────────────────────── --}}
    @if($media->isNotEmpty())
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @foreach($media as $item)
        @php
            $isVideo = in_array($item->media_type, ['video','mp4','webm','mov']);
            $isImage = in_array($item->media_type, ['image','png','jpg','jpeg','gif','webp']);
            $url     = $item->url;
            $label   = $item->display_name ?: $item->file_name;
        @endphp
        <div class="bg-white rounded-[20px] border border-slate-100 overflow-hidden cursor-pointer transition hover:shadow-md hover:border-[#0d326b]/20"
             onclick="openMediaPreview('{{ addslashes($url) }}','{{ addslashes($label) }}','{{ $item->media_type }}','{{ addslashes($item->gesture->display_name ?? $item->gesture->name ?? '') }}','{{ addslashes($item->module->display_name ?? $item->module->name ?? '') }}',{{ $item->file_size ?? 0 }})">

            <div class="relative w-full bg-slate-50" style="padding-bottom:72%">
                <div class="absolute inset-0 flex items-center justify-center">
                    @if($isImage && $url)
                        <img src="{{ $url }}" alt="{{ $label }}"
                             class="w-full h-full object-cover"
                             style="transition:transform .3s"
                             onmouseover="this.style.transform='scale(1.05)'"
                             onmouseout="this.style.transform='scale(1)'"
                             onerror="this.parentElement.innerHTML='<span class=\'material-symbols-outlined\' style=\'color:#cbd5e1;font-size:40px\'>broken_image</span>'">
                    @elseif($isVideo && $url)
                        <video class="w-full h-full object-cover" muted preload="metadata">
                            <source src="{{ $url }}" type="video/mp4">
                        </video>
                        <div class="absolute inset-0 flex items-center justify-center" style="background:rgba(0,0,0,.18)">
                            <div class="w-10 h-10 rounded-full bg-white/90 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[#0d326b] text-[20px]">play_arrow</span>
                            </div>
                        </div>
                    @else
                        <span class="material-symbols-outlined text-[40px]" style="color:#cbd5e1">perm_media</span>
                    @endif
                </div>
            </div>

            <div class="p-3">
                <p class="text-[12px] font-semibold text-slate-700 truncate">{{ $label }}</p>
                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full uppercase"
                          style="background:#f1f5f9;color:#64748b">{{ $item->media_type }}</span>
                    @if($item->gesture)
                    <span class="text-[10px] text-slate-400 truncate">{{ $item->gesture->display_name ?? $item->gesture->name }}</span>
                    @endif
                </div>
                @if($item->file_size)
                <p class="text-[10px] mt-0.5" style="color:#cbd5e1">{{ number_format($item->file_size/1024,1) }} KB</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($media->hasPages())
    <div class="mt-2">{{ $media->withQueryString()->links() }}</div>
    @endif

    @else
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 px-8 py-16 text-center">
        <span class="material-symbols-outlined text-[56px]" style="color:#e2e8f0">perm_media</span>
        <p class="text-[14px] text-slate-400 font-semibold mt-3">
            @if($search || $typeFilter) No media matches your filters. @else No system media uploaded yet. @endif
        </p>
    </div>
    @endif

</div>{{-- /skeleton-hide --}}

{{-- ══════════════════════════════════════════════════════════════════════
     MEDIA PREVIEW MODAL
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="mediaPreviewModal"
     class="fixed inset-0 z-[999] items-center justify-center p-4"
     style="display:none;background:rgba(15,23,42,.65);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-[28px] shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">

        <div class="px-7 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <div class="flex-1 min-w-0 mr-4">
                <p id="mediaPreviewName" class="text-[15px] font-black text-[#0d326b] truncate">Media Preview</p>
                <p id="mediaPreviewMeta" class="text-[11px] text-slate-400 mt-0.5"></p>
            </div>
            <button onclick="closeMediaPreview()"
                    class="w-8 h-8 rounded-full flex items-center justify-center transition flex-shrink-0"
                    style="background:#f1f5f9"
                    onmouseover="this.style.background='#e2e8f0'"
                    onmouseout="this.style.background='#f1f5f9'">
                <span class="material-symbols-outlined text-slate-600 text-[18px]">close</span>
            </button>
        </div>

        <div id="mediaPreviewContent"
             class="flex-1 overflow-y-auto flex items-center justify-center min-h-[260px] p-6"
             style="background:#f8fafc"></div>

        <div class="px-7 py-4 border-t border-slate-100 flex items-center justify-between bg-white flex-shrink-0">
            <p class="text-[11px] text-slate-400 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[14px]">lock</span>
                Read-only — managed by the Super Admin.
            </p>
            <button onclick="closeMediaPreview()"
                    class="px-5 py-2 rounded-xl text-[13px] font-bold text-white"
                    style="background:linear-gradient(135deg,#0d326b,#1e4b8f)">Close</button>
        </div>
    </div>
</div>

<script>
function openMediaPreview(url, label, type, gesture, module, fileSize) {
    const modal   = document.getElementById('mediaPreviewModal');
    const content = document.getElementById('mediaPreviewContent');
    document.getElementById('mediaPreviewName').textContent = label;
    const parts = [type ? type.toUpperCase() : null, gesture || null, module || null, fileSize ? (fileSize/1024).toFixed(1)+' KB' : null];
    document.getElementById('mediaPreviewMeta').textContent = parts.filter(Boolean).join(' · ');

    const isVideo = ['video','mp4','webm','mov'].includes(type);
    const isImage = ['image','png','jpg','jpeg','gif','webp'].includes(type);

    if (isVideo && url) {
        content.innerHTML = `<video controls autoplay style="max-width:100%;max-height:55vh;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.1)"><source src="${url}" type="video/mp4">Your browser does not support video.</video>`;
    } else if (isImage && url) {
        content.innerHTML = `<img src="${url}" alt="${label}" style="max-width:100%;max-height:55vh;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.1);object-fit:contain">`;
    } else {
        content.innerHTML = `<div style="text-align:center;padding:40px 0"><span class="material-symbols-outlined" style="font-size:56px;color:#e2e8f0">perm_media</span><p style="color:#94a3b8;font-size:13px;margin-top:8px">Preview not available for this file type.</p></div>`;
    }
    modal.style.display = 'flex';
}
function closeMediaPreview() {
    const content = document.getElementById('mediaPreviewContent');
    const video   = content.querySelector('video');
    if (video) { video.pause(); video.src = ''; }
    content.innerHTML = '';
    document.getElementById('mediaPreviewModal').style.display = 'none';
}
document.getElementById('mediaPreviewModal').addEventListener('click', function(e) { if(e.target===this) closeMediaPreview(); });
document.addEventListener('keydown', function(e) { if(e.key==='Escape') closeMediaPreview(); });
</script>
@endsection
