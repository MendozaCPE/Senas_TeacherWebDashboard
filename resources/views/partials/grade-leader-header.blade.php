<header class="h-20 px-12 flex items-center justify-between flex-shrink-0 bg-[#f4f7f9] border-b border-slate-100 relative z-30">

    {{-- Left: Page identity --}}
    <div class="flex items-center gap-3 flex-shrink-0 mr-6">
        <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 bg-[#0d326b]">
            <span class="material-symbols-outlined text-white text-[17px]">verified</span>
        </div>
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 leading-none mb-0.5">
                {{ Auth::user()->teacher->school->name ?? 'Grade Leader Portal' }}
            </p>
            <p class="text-[15px] font-bold text-[#0d326b] leading-none">@yield('title', 'Dashboard')</p>
        </div>
    </div>

    {{-- Global Search Bar (mirrors teacher header) --}}
    <div class="relative w-[480px]" id="tl-search-container">
        <div class="relative flex items-center">
            <span class="absolute left-4 text-[#0d326b] pointer-events-none flex items-center">
                <span class="material-symbols-outlined icon-outline text-[22px]" id="tl-search-icon">search</span>
            </span>
            <input id="tl-search-input" type="text" autocomplete="off"
                   placeholder="Search teachers, students, lessons, or media..."
                   class="w-full bg-white border-2 border-[#0d326b]/30 rounded-full py-2.5 pl-12 pr-10 text-[14px] focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b] shadow-sm transition-all text-slate-700 outline-none placeholder:text-slate-400 font-medium"/>
            <button type="button" id="tl-search-clear"
                    class="absolute right-3.5 text-slate-400 hover:text-slate-600 hidden p-1 rounded-full hover:bg-slate-100 transition-colors flex items-center justify-center"
                    title="Clear search">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div id="tl-search-dropdown"
             class="absolute left-0 right-0 top-full mt-2 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden hidden transition-all duration-200 z-50">
            <div id="tl-search-results" class="max-h-[420px] overflow-y-auto p-2 space-y-3"></div>
            <div class="px-4 py-2 bg-slate-50/90 text-[11px] text-slate-400 font-medium flex items-center justify-between border-t border-slate-100 select-none">
                <div class="flex items-center gap-3">
                    <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">↑</kbd> <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">↓</kbd> Navigate</span>
                    <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">↵</kbd> Select</span>
                </div>
                <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">ESC</kbd> Close</span>
            </div>
        </div>
    </div>

    {{-- Right: Avatar + name --}}
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-full"
             style="background: rgba(13,50,107,0.05);">
            <img src="{{ Auth::user()->avatarUrl() }}"
                 class="w-8 h-8 rounded-full border-2 border-[#0d326b]/20 object-cover flex-shrink-0"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
            <span class="text-[13px] font-bold text-[#0d326b] leading-none">
                {{ Auth::user()->teacher->first_name ?? explode(' ', Auth::user()->name)[0] }}
            </span>
        </div>
    </div>

</header>

<script>
// ── Teacher Leader Global Search ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const searchInput  = document.getElementById('tl-search-input');
    const searchClear  = document.getElementById('tl-search-clear');
    const dropdown     = document.getElementById('tl-search-dropdown');
    const results      = document.getElementById('tl-search-results');
    const searchIcon   = document.getElementById('tl-search-icon');
    let debounceTimer  = null, selectedIndex = -1, focusableItems = [];
    if (!searchInput || !dropdown) return;

    function highlightMatch(text, query) {
        if (!query) return text;
        const reg = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(reg, '<mark class="bg-blue-100 text-[#0d326b] font-bold px-0.5 rounded">$1</mark>');
    }

    async function performSearch(q) {
        q = q.trim();
        if (!q) { dropdown.classList.add('hidden'); searchClear.classList.add('hidden'); searchIcon.textContent = 'search'; return; }
        searchClear.classList.remove('hidden');
        searchIcon.textContent = 'sync'; searchIcon.classList.add('animate-spin');
        try {
            const res  = await fetch('/api/global-search?q=' + encodeURIComponent(q));
            const data = await res.json();
            searchIcon.classList.remove('animate-spin'); searchIcon.textContent = 'search';
            const teachers = data.teachers || [], students = data.students || [], lessons = data.lessons || [], media = data.media || [];
            if (!teachers.length && !students.length && !lessons.length && !media.length) {
                results.innerHTML = '<div class="p-6 text-center"><span class="material-symbols-outlined text-slate-300 text-[36px] mb-2">search_off</span><p class="text-[14px] font-bold text-slate-600">No matches for "' + q + '"</p><p class="text-[12px] text-slate-400 mt-1">Try a teacher or student name, lesson title, or media name.</p></div>';
                dropdown.classList.remove('hidden'); focusableItems = []; selectedIndex = -1; return;
            }
            let html = '';
            if (teachers.length) {
                html += '<div><div class="px-3 py-1.5 text-[10px] font-bold tracking-widest uppercase text-slate-400 flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px] text-blue-600">person</span><span>Teachers (' + teachers.length + ')</span></div><div class="space-y-0.5 mt-1">';
                teachers.forEach(function(t) { const title = highlightMatch(t.title, q); html += '<a href="' + t.url + '" class="search-item group flex items-center justify-between p-2.5 rounded-xl hover:bg-blue-50/70 transition-all cursor-pointer"><div class="flex items-center gap-3 min-w-0"><img src="' + t.avatar + '" class="w-9 h-9 rounded-full object-cover shrink-0 ring-2 ring-slate-100 group-hover:ring-blue-200 transition-all"><div class="min-w-0"><p class="text-[13.5px] font-bold text-[#0d326b] truncate">' + title + '</p><p class="text-[11px] font-medium text-slate-400 truncate mt-0.5">' + t.subtitle + '</p></div></div><span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-[#0d326b] shrink-0 ml-2">' + t.badge + '</span></a>'; });
                html += '</div></div>';
            }
            if (students.length) {
                html += '<div><div class="px-3 py-1.5 text-[10px] font-bold tracking-widest uppercase text-slate-400 flex items-center gap-1.5' + (teachers.length ? ' mt-2 border-t border-slate-100 pt-2.5' : '') + '"><span class="material-symbols-outlined text-[14px] text-blue-600">school</span><span>Students (' + students.length + ')</span></div><div class="space-y-0.5 mt-1">';
                students.forEach(function(s) { const t = highlightMatch(s.title, q); html += '<a href="' + s.url + '" class="search-item group flex items-center justify-between p-2.5 rounded-xl hover:bg-blue-50/70 transition-all cursor-pointer"><div class="flex items-center gap-3 min-w-0"><img src="' + s.avatar + '" class="w-9 h-9 rounded-full object-cover shrink-0 ring-2 ring-slate-100 group-hover:ring-blue-200 transition-all"><div class="min-w-0"><p class="text-[13.5px] font-bold text-[#0d326b] truncate">' + t + '</p><p class="text-[11px] font-medium text-slate-400 truncate mt-0.5">' + s.subtitle + '</p></div></div><span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-[#0d326b] shrink-0 ml-2">' + s.badge + '</span></a>'; });
                html += '</div></div>';
            }
            if (lessons.length) {
                html += '<div><div class="px-3 py-1.5 text-[10px] font-bold tracking-widest uppercase text-slate-400 flex items-center gap-1.5' + (teachers.length || students.length ? ' mt-2 border-t border-slate-100 pt-2.5' : '') + '"><span class="material-symbols-outlined text-[14px] text-amber-500">menu_book</span><span>Lessons (' + lessons.length + ')</span></div><div class="space-y-0.5 mt-1">';
                lessons.forEach(function(l) { const t = highlightMatch(l.title, q); const bs = l.badge === 'Published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'; html += '<a href="' + l.url + '" class="search-item group flex items-center justify-between p-2.5 rounded-xl hover:bg-amber-50/60 transition-all cursor-pointer"><div class="flex items-center gap-3 min-w-0"><div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0"><span class="material-symbols-outlined text-[18px]">auto_stories</span></div><div class="min-w-0"><p class="text-[13.5px] font-bold text-[#0d326b] truncate">' + t + '</p><p class="text-[11px] font-medium text-slate-400 truncate mt-0.5">' + l.subtitle + '</p></div></div><span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ' + bs + ' shrink-0 ml-2">' + l.badge + '</span></a>'; });
                html += '</div></div>';
            }
            if (media.length) {
                const hasPrev = teachers.length || students.length || lessons.length;
                html += '<div><div class="px-3 py-1.5 text-[10px] font-bold tracking-widest uppercase text-slate-400 flex items-center gap-1.5' + (hasPrev ? ' mt-2 border-t border-slate-100 pt-2.5' : '') + '"><span class="material-symbols-outlined text-[14px] text-purple-500">perm_media</span><span>Media (' + media.length + ')</span></div><div class="space-y-0.5 mt-1">';
                media.forEach(function(m) { const t = highlightMatch(m.title, q); const ti = m.media_type === 'video' ? 'videocam' : (m.media_type === 'gif' ? 'gif' : 'image'); const ss = m.source === 'system' ? 'bg-blue-100 text-[#0d326b]' : 'bg-emerald-100 text-emerald-800'; html += '<a href="' + m.url + '" class="search-item group flex items-center justify-between p-2.5 rounded-xl hover:bg-purple-50/50 transition-all cursor-pointer"><div class="flex items-center gap-3 min-w-0"><div class="w-9 h-9 rounded-xl overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center relative"><img src="' + m.thumb + '" class="w-full h-full object-cover" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'"><span class="material-symbols-outlined text-slate-400 text-[18px]" style="display:none;">' + ti + '</span></div><div class="min-w-0"><p class="text-[13.5px] font-bold text-[#0d326b] truncate">' + t + '</p><p class="text-[11px] font-medium text-slate-400 truncate mt-0.5">' + m.subtitle + '</p></div></div><span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ' + ss + ' shrink-0 ml-2">' + m.badge + '</span></a>'; });
                html += '</div></div>';
            }
            results.innerHTML = html;
            dropdown.classList.remove('hidden');
            focusableItems = Array.from(results.querySelectorAll('.search-item'));
            selectedIndex = -1;
        } catch (err) {
            searchIcon.classList.remove('animate-spin');
            searchIcon.textContent = 'search';
        }
    }

    searchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        const v = e.target.value;
        if (!v.trim()) { dropdown.classList.add('hidden'); searchClear.classList.add('hidden'); return; }
        searchClear.classList.remove('hidden');
        debounceTimer = setTimeout(function() { performSearch(v); }, 180);
    });
    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        dropdown.classList.add('hidden');
        searchClear.classList.add('hidden');
        searchInput.focus();
    });
    searchInput.addEventListener('keydown', function(e) {
        if (dropdown.classList.contains('hidden')) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); selectedIndex = (selectedIndex + 1) % focusableItems.length; updateSel(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); selectedIndex = (selectedIndex - 1 + focusableItems.length) % focusableItems.length; updateSel(); }
        else if (e.key === 'Enter') { e.preventDefault(); (focusableItems[selectedIndex] || focusableItems[0]) && (focusableItems[selectedIndex] || focusableItems[0]).click(); }
        else if (e.key === 'Escape') { dropdown.classList.add('hidden'); }
    });
    function updateSel() {
        focusableItems.forEach(function(el, i) {
            el.classList.toggle('bg-slate-100', i === selectedIndex);
            el.classList.toggle('ring-2', i === selectedIndex);
            el.classList.toggle('ring-[#0d326b]/20', i === selectedIndex);
            if (i === selectedIndex) el.scrollIntoView({ block: 'nearest' });
        });
    }
    document.addEventListener('click', function(e) {
        if (!document.getElementById('tl-search-container') || !document.getElementById('tl-search-container').contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
    searchInput.addEventListener('focus', function() {
        if (searchInput.value.trim()) performSearch(searchInput.value);
    });
});
</script>
