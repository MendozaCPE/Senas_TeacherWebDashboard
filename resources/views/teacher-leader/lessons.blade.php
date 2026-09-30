@extends('layouts.teacher-leader')
@section('title', 'Default Lessons')
@section('content')

{{-- ══════════════════════════════════════════════════════════════════════
     SKELETON
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="page-skeleton" class="flex flex-col gap-4 pt-4 w-full min-w-0" aria-hidden="true">
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5 flex items-center gap-3">
        <div class="skeleton h-10 rounded-full flex-1"></div>
        <div class="skeleton h-10 rounded-full w-28"></div>
    </div>
    @for($m=0;$m<3;$m++)
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center gap-4">
            <div class="skeleton w-10 h-10 rounded-xl flex-shrink-0"></div>
            <div class="flex-1 flex flex-col gap-2"><div class="skeleton h-4 rounded w-48"></div><div class="skeleton h-3 rounded w-32"></div></div>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @for($l=0;$l<3;$l++)
            <div class="border border-slate-100 rounded-2xl p-4 flex flex-col gap-3">
                <div class="skeleton h-4 rounded w-3/4"></div>
                <div class="skeleton h-3 rounded w-full"></div>
                <div class="skeleton h-8 rounded-xl w-full mt-auto"></div>
            </div>
            @endfor
        </div>
    </div>
    @endfor
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     REAL CONTENT
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-col gap-4 pt-4 skeleton-hide w-full min-w-0">

    {{-- ── Search / Filter bar (matches admin accounts style) ─────────── --}}
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5 flex items-center gap-3 flex-wrap">
        <form method="GET" action="{{ route('teacher-leader.lessons') }}" class="flex items-center gap-2 flex-wrap w-full">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search modules or lessons…"
                       class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-[13px] font-medium text-slate-700 bg-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/40 transition">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-2xl text-[13px] font-bold text-white transition"
                    style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
                <span class="material-symbols-outlined text-[16px]">search</span> Search
            </button>
            @if($search)
            <a href="{{ route('teacher-leader.lessons') }}"
               class="inline-flex items-center gap-1 px-4 py-2.5 rounded-2xl text-[13px] font-semibold text-slate-500 border border-slate-200 bg-white hover:bg-slate-50 transition">
                <span class="material-symbols-outlined text-[15px]">close</span> Clear
            </a>
            @endif
        </form>
    </div>

    {{-- ── Summary row ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-3">
            <span class="text-[13px] font-semibold text-slate-500">
                <span class="text-[#0d326b] font-black">{{ $totalModules }}</span> {{ Str::plural('module', $totalModules) }},
                <span class="text-[#0d326b] font-black">{{ $totalLessons }}</span> {{ Str::plural('lesson', $totalLessons) }}
            </span>
        </div>
        <div class="flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-blue-50 border border-blue-100">
            <span class="material-symbols-outlined text-[#1a6fd4] text-[15px]">visibility</span>
            <p class="text-[12px] font-semibold text-[#1a6fd4]">View-only — no create, edit, or delete.</p>
        </div>
    </div>

    {{-- ── Module / Lesson Catalog ──────────────────────────────────────── --}}
    @forelse($modules as $module)
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">

        {{-- Module header --}}
        <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:linear-gradient(135deg,#0d326b,#1a6fd4)">
                <span class="material-symbols-outlined text-white text-[18px]">folder_open</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[15px] font-black text-[#0d326b] truncate">{{ $module->title }}</p>
                @if($module->description)
                <p class="text-[12px] text-slate-400 truncate mt-0.5">{{ $module->description }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2 flex-shrink-0 flex-wrap justify-end">
                @if($module->mastery_level)
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-blue-50 text-[#1a6fd4]">{{ ucfirst($module->mastery_level) }}</span>
                @endif
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500">
                    {{ $module->lessons->count() }} {{ Str::plural('lesson', $module->lessons->count()) }}
                </span>
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-[#0d326b] text-white">
                    Module {{ $module->module_order }}
                </span>
            </div>
        </div>

        {{-- Lesson grid --}}
        @if($module->lessons->isNotEmpty())
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($module->lessons as $lesson)
            @php
                $diffStyle = match($lesson->difficulty ?? '') {
                    'beginner'     => 'background:#ecfdf5;color:#15803d',
                    'intermediate' => 'background:#fffbeb;color:#b45309',
                    'advanced'     => 'background:#fef2f2;color:#b91c1c',
                    default        => 'background:#f8fafc;color:#64748b',
                };
            @endphp
            <div class="border border-slate-100 rounded-2xl p-4 flex flex-col gap-3 transition hover:border-[#0d326b]/20 hover:shadow-sm group">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate group-hover:text-[#0d326b]">{{ $lesson->title }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Lesson {{ $lesson->module_order }}
                            @if($lesson->lesson_type) &bull; {{ ucfirst($lesson->lesson_type) }} @endif
                        </p>
                    </div>
                    @if($lesson->difficulty)
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0" style="{{ $diffStyle }}">
                        {{ ucfirst($lesson->difficulty) }}
                    </span>
                    @endif
                </div>

                @if($lesson->description)
                <p class="text-[12px] text-slate-400 line-clamp-2">{{ $lesson->description }}</p>
                @endif

                <div class="flex items-center gap-3 mt-auto text-[11px] text-slate-400">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px] icon-outline">article</span>
                        {{ $lesson->contents->count() }} {{ Str::plural('step', $lesson->contents->count()) }}
                    </span>
                    @if($lesson->quiz)
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px] icon-outline">quiz</span>
                        {{ $lesson->quiz->questions->count() }} Qs
                    </span>
                    @endif
                </div>

                <button type="button"
                        onclick="openLessonPreview({{ $lesson->lesson_id }}, '{{ addslashes($lesson->title) }}')"
                        class="w-full py-2 rounded-xl text-[12px] font-bold flex items-center justify-center gap-1.5 transition border"
                        style="color:#0d326b;border-color:rgba(13,50,107,.2);background:#fff;"
                        onmouseover="this.style.background='#0d326b';this.style.color='#fff';"
                        onmouseout="this.style.background='#fff';this.style.color='#0d326b';">
                    <span class="material-symbols-outlined text-[14px]">visibility</span> Preview
                </button>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-6 py-8 text-center text-[13px] text-slate-400">No lessons in this module yet.</div>
        @endif

    </div>
    @empty
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 px-8 py-16 text-center">
        <span class="material-symbols-outlined text-slate-200 text-[56px]">auto_stories</span>
        <p class="text-[14px] text-slate-400 font-semibold mt-3">
            @if($search) No results for "{{ $search }}". @else No default lessons published yet. @endif
        </p>
    </div>
    @endforelse

</div>{{-- /skeleton-hide --}}

{{-- ══════════════════════════════════════════════════════════════════════
     LESSON PREVIEW MODAL
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="lessonPreviewModal"
     class="fixed inset-0 z-[999] items-center justify-center p-4"
     style="display:none;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);">
    <div class="bg-white rounded-[28px] shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">

        <div class="px-7 py-5 border-b border-slate-100 flex items-center justify-between flex-shrink-0"
             style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 100%)">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-white text-[22px]">auto_stories</span>
                <div>
                    <p class="text-white/60 text-[10px] font-black uppercase tracking-wider">Lesson Preview</p>
                    <p id="previewTitle" class="text-white font-black text-[15px]">Loading…</p>
                </div>
            </div>
            <button onclick="closeLessonPreview()"
                    class="w-8 h-8 rounded-full flex items-center justify-center transition"
                    style="background:rgba(255,255,255,.15)"
                    onmouseover="this.style.background='rgba(255,255,255,.25)'"
                    onmouseout="this.style.background='rgba(255,255,255,.15)'">
                <span class="material-symbols-outlined text-white text-[18px]">close</span>
            </button>
        </div>

        <div id="previewBody" class="flex-1 overflow-y-auto p-7">
            <div id="previewLoading" class="flex flex-col gap-3">
                <div class="skeleton h-4 rounded w-3/4"></div>
                <div class="skeleton h-4 rounded w-full"></div>
                <div class="skeleton h-4 rounded w-2/3"></div>
            </div>
            <div id="previewContent" class="hidden flex flex-col gap-4"></div>
        </div>

        <div class="px-7 py-4 border-t border-slate-100 flex items-center justify-between flex-shrink-0 bg-[#f8fafc]">
            <p class="text-[11px] text-slate-400 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[14px]">lock</span>
                Read-only — contact Super Admin to modify curriculum.
            </p>
            <button onclick="closeLessonPreview()"
                    class="px-5 py-2 rounded-xl text-[13px] font-bold text-white transition"
                    style="background:linear-gradient(135deg,#0d326b,#1e4b8f)">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function openLessonPreview(lessonId, title) {
    const modal   = document.getElementById('lessonPreviewModal');
    const loading = document.getElementById('previewLoading');
    const content = document.getElementById('previewContent');
    document.getElementById('previewTitle').textContent = title;
    loading.classList.remove('hidden');
    content.classList.add('hidden');
    content.innerHTML = '';
    modal.style.display = 'flex';

    fetch(`/teacher-leader/lessons/${lessonId}/preview`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            loading.classList.add('hidden');
            content.classList.remove('hidden');
            let html = '';
            if (data.lesson.description) {
                html += `<div style="background:#f8fafc;border-radius:16px;padding:16px">
                    <p style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:6px">Description</p>
                    <p style="font-size:13px;color:#475569">${data.lesson.description}</p></div>`;
            }
            html += `<div style="display:flex;flex-wrap:wrap;gap:8px">`;
            if (data.module) html += `<span style="padding:4px 12px;background:#eff6ff;color:#1d4ed8;border-radius:999px;font-size:11px;font-weight:700">📁 ${data.module.title}</span>`;
            if (data.lesson.lesson_type) html += `<span style="padding:4px 12px;background:#f5f3ff;color:#6d28d9;border-radius:999px;font-size:11px;font-weight:700">🎯 ${cap(data.lesson.lesson_type)}</span>`;
            if (data.lesson.difficulty) html += `<span style="padding:4px 12px;background:#fffbeb;color:#b45309;border-radius:999px;font-size:11px;font-weight:700">⚡ ${cap(data.lesson.difficulty)}</span>`;
            html += `</div>`;

            if (data.contents && data.contents.length > 0) {
                html += `<div><p style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:10px">Lesson Steps (${data.contents.length})</p><div style="display:flex;flex-direction:column;gap:8px">`;
                data.contents.forEach(c => {
                    const icon = c.content_type === 'video' ? 'play_circle' : (c.content_type === 'image' ? 'image' : 'article');
                    html += `<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 14px;background:#f8fafc;border-radius:14px">
                        <div style="width:28px;height:28px;border-radius:10px;background:rgba(13,50,107,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <span class="material-symbols-outlined" style="color:#0d326b;font-size:15px">${icon}</span>
                        </div>
                        <div><p style="font-size:12px;font-weight:700;color:#374151">Step ${c.step_number}: ${c.title || cap(c.content_type)}</p>
                        ${c.instructions ? `<p style="font-size:11px;color:#9ca3af;margin-top:2px">${c.instructions}</p>` : ''}</div>
                    </div>`;
                });
                html += `</div></div>`;
            }
            if (data.quiz) {
                html += `<div style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:#fffbeb;border-radius:16px;border:1px solid #fde68a">
                    <span class="material-symbols-outlined" style="color:#d97706;font-size:22px">quiz</span>
                    <div><p style="font-size:13px;font-weight:700;color:#374151">Quiz Included</p>
                    <p style="font-size:11px;color:#9ca3af">${data.quiz.question_count} question${data.quiz.question_count!==1?'s':''}</p></div>
                </div>`;
            }
            content.innerHTML = html;
        })
        .catch(() => {
            loading.classList.add('hidden');
            content.classList.remove('hidden');
            content.innerHTML = '<p style="color:#ef4444;font-size:13px">Failed to load. Please try again.</p>';
        });
}
function closeLessonPreview() { document.getElementById('lessonPreviewModal').style.display = 'none'; }
function cap(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
document.getElementById('lessonPreviewModal').addEventListener('click', function(e) { if (e.target===this) closeLessonPreview(); });
document.addEventListener('keydown', function(e) { if (e.key==='Escape') closeLessonPreview(); });
</script>
@endsection
