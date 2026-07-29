<header class="h-20 px-12 flex items-center justify-between flex-shrink-0 bg-[#f4f7f9] border-b border-slate-100 relative z-30">
    <!-- Global Search Bar with Autocomplete Suggestions -->
    <div class="relative w-[480px]" id="global-search-container">
        <div class="relative flex items-center">
            <span class="absolute left-4 text-slate-400 pointer-events-none flex items-center">
                <span class="material-symbols-outlined icon-outline text-[22px]" id="search-bar-icon">search</span>
            </span>
            
            <input id="global-search-input"
                   type="text"
                   autocomplete="off"
                   placeholder="Search student records or lessons..."
                   class="w-full bg-white border border-slate-200/80 rounded-full py-2.5 pl-12 pr-10 text-[14px] focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/30 shadow-sm transition-all text-slate-700 outline-none placeholder:text-slate-400 font-medium"/>
            
            <!-- Clear Button -->
            <button type="button"
                    id="global-search-clear"
                    class="absolute right-3.5 text-slate-400 hover:text-slate-600 hidden p-1 rounded-full hover:bg-slate-100 transition-colors flex items-center justify-center"
                    title="Clear search">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <!-- Google-style Suggestions Dropdown -->
        <div id="global-search-dropdown"
             class="absolute left-0 right-0 top-full mt-2 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden hidden transition-all duration-200 z-50">
            
            <div id="search-results-content" class="max-h-[420px] overflow-y-auto p-2 space-y-3">
                {{-- Dynamic contents injected here --}}
            </div>
            
            <!-- Keyboard shortcut hint footer -->
            <div class="px-4 py-2 bg-slate-50/90 text-[11px] text-slate-400 font-medium flex items-center justify-between border-t border-slate-100 select-none">
                <div class="flex items-center gap-3">
                    <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">↑</kbd> <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">↓</kbd> Navigate</span>
                    <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">↵</kbd> Select</span>
                </div>
                <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">ESC</kbd> Close</span>
            </div>
        </div>
    </div>

    <!-- Right controls -->
    <div class="flex items-center space-x-5">
        <div class="h-8 border-l border-slate-200"></div>
        <div class="text-[15px] font-semibold ">
            <span class="text-[#0d326b]">@yield('title')</span>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput    = document.getElementById('global-search-input');
    const searchClear    = document.getElementById('global-search-clear');
    const dropdown       = document.getElementById('global-search-dropdown');
    const resultsContent = document.getElementById('search-results-content');
    const searchIcon     = document.getElementById('search-bar-icon');

    let debounceTimer  = null;
    let selectedIndex  = -1;
    let focusableItems = [];

    if (!searchInput || !dropdown) return;

    function highlightMatch(text, query) {
        if (!query) return text;
        const reg = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(reg, '<mark class="bg-blue-100 text-[#0d326b] font-bold px-0.5 rounded">$1</mark>');
    }

    async function performSearch(q) {
        q = q.trim();
        if (q.length < 1) {
            dropdown.classList.add('hidden');
            searchClear.classList.add('hidden');
            searchIcon.textContent = 'search';
            return;
        }

        searchClear.classList.remove('hidden');
        searchIcon.textContent = 'sync';
        searchIcon.classList.add('animate-spin');

        try {
            const res = await fetch(`{{ route('api.global-search') }}?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            
            searchIcon.classList.remove('animate-spin');
            searchIcon.textContent = 'search';

            const students = data.students || [];
            const lessons  = data.lessons  || [];

            if (students.length === 0 && lessons.length === 0) {
                resultsContent.innerHTML = `
                    <div class="p-6 text-center">
                        <span class="material-symbols-outlined text-slate-300 text-[36px] mb-2">search_off</span>
                        <p class="text-[14px] font-bold text-slate-600">No matches found for "${q}"</p>
                        <p class="text-[12px] text-slate-400 mt-1">Try searching by student name, LRN, grade level, or lesson title.</p>
                    </div>
                `;
                dropdown.classList.remove('hidden');
                selectedIndex = -1;
                focusableItems = [];
                return;
            }

            let html = '';

            // ── STUDENTS SECTION ──
            if (students.length > 0) {
                html += `
                    <div>
                        <div class="px-3 py-1.5 text-[10px] font-bold tracking-widest uppercase text-slate-400 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px] text-blue-600">school</span>
                            <span>Students (${students.length})</span>
                        </div>
                        <div class="space-y-0.5 mt-1">
                `;

                students.forEach((s) => {
                    const titleHtml = highlightMatch(s.title, q);
                    html += `
                        <a href="${s.url}" class="search-item group flex items-center justify-between p-2.5 rounded-xl hover:bg-blue-50/70 transition-all cursor-pointer text-decoration-none">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="${s.avatar}" alt="${s.title}" class="w-9 h-9 rounded-full object-cover shrink-0 ring-2 ring-slate-100 group-hover:ring-blue-200 transition-all">
                                <div class="min-w-0">
                                    <p class="text-[13.5px] font-bold text-[#0d326b] truncate leading-snug">${titleHtml}</p>
                                    <p class="text-[11px] font-medium text-slate-400 truncate mt-0.5">${s.subtitle}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-[#0d326b] shrink-0 ml-2">
                                ${s.badge}
                            </span>
                        </a>
                    `;
                });

                html += `</div></div>`;
            }

            // ── LESSONS SECTION ──
            if (lessons.length > 0) {
                html += `
                    <div>
                        <div class="px-3 py-1.5 text-[10px] font-bold tracking-widest uppercase text-slate-400 flex items-center gap-1.5 ${students.length > 0 ? 'mt-2 border-t border-slate-100 pt-2.5' : ''}">
                            <span class="material-symbols-outlined text-[14px] text-amber-500">menu_book</span>
                            <span>Lessons (${lessons.length})</span>
                        </div>
                        <div class="space-y-0.5 mt-1">
                `;

                lessons.forEach((l) => {
                    const titleHtml = highlightMatch(l.title, q);
                    const badgeStyle = l.badge === 'Published' 
                        ? 'bg-emerald-100 text-emerald-800' 
                        : 'bg-amber-100 text-amber-800';
                    html += `
                        <a href="${l.url}" class="search-item group flex items-center justify-between p-2.5 rounded-xl hover:bg-amber-50/60 transition-all cursor-pointer text-decoration-none">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <span class="material-symbols-outlined text-[18px]">auto_stories</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[13.5px] font-bold text-[#0d326b] truncate leading-snug">${titleHtml}</p>
                                    <p class="text-[11px] font-medium text-slate-400 truncate mt-0.5">${l.subtitle}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${badgeStyle} shrink-0 ml-2">
                                ${l.badge}
                            </span>
                        </a>
                    `;
                });

                html += `</div></div>`;
            }

            resultsContent.innerHTML = html;
            dropdown.classList.remove('hidden');

            focusableItems = Array.from(resultsContent.querySelectorAll('.search-item'));
            selectedIndex = -1;

        } catch (err) {
            console.error('Global search error:', err);
            searchIcon.classList.remove('animate-spin');
            searchIcon.textContent = 'search';
        }
    }

    searchInput.addEventListener('input', function (e) {
        clearTimeout(debounceTimer);
        const val = e.target.value;
        if (val.trim() === '') {
            dropdown.classList.add('hidden');
            searchClear.classList.add('hidden');
            return;
        }
        searchClear.classList.remove('hidden');
        debounceTimer = setTimeout(() => performSearch(val), 180);
    });

    searchClear.addEventListener('click', function () {
        searchInput.value = '';
        dropdown.classList.add('hidden');
        searchClear.classList.add('hidden');
        searchInput.focus();
    });

    // Keyboard navigation (Up / Down / Enter / ESC)
    searchInput.addEventListener('keydown', function (e) {
        if (dropdown.classList.contains('hidden')) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (focusableItems.length === 0) return;
            selectedIndex = (selectedIndex + 1) % focusableItems.length;
            updateSelection();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (focusableItems.length === 0) return;
            selectedIndex = (selectedIndex - 1 + focusableItems.length) % focusableItems.length;
            updateSelection();
        } else if (e.key === 'Enter') {
            if (selectedIndex >= 0 && focusableItems[selectedIndex]) {
                e.preventDefault();
                focusableItems[selectedIndex].click();
            } else if (focusableItems.length > 0) {
                e.preventDefault();
                focusableItems[0].click();
            }
        } else if (e.key === 'Escape') {
            dropdown.classList.add('hidden');
        }
    });

    function updateSelection() {
        focusableItems.forEach((item, idx) => {
            if (idx === selectedIndex) {
                item.classList.add('bg-slate-100', 'ring-2', 'ring-[#0d326b]/20');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('bg-slate-100', 'ring-2', 'ring-[#0d326b]/20');
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const container = document.getElementById('global-search-container');
        if (container && !container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    searchInput.addEventListener('focus', function () {
        if (searchInput.value.trim().length > 0) {
            performSearch(searchInput.value);
        }
    });
});
</script>
