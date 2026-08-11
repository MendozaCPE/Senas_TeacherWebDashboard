<header class="h-20 px-12 flex items-center justify-between flex-shrink-0 bg-[#f4f7f9] border-b border-slate-100 relative z-30">

    <!-- Global Search Bar -->
    <div class="relative w-[480px]" id="global-search-container">
        <div class="relative flex items-center">
            <span class="absolute left-4 text-slate-400 pointer-events-none flex items-center">
                <span class="material-symbols-outlined icon-outline text-[22px]" id="search-bar-icon">search</span>
            </span>
            <input id="global-search-input" type="text" autocomplete="off"
                   placeholder="Search students, lessons, or media..."
                   class="w-full bg-white border border-slate-200/80 rounded-full py-2.5 pl-12 pr-10 text-[14px] focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/30 shadow-sm transition-all text-slate-700 outline-none placeholder:text-slate-400 font-medium"/>
            <button type="button" id="global-search-clear"
                    class="absolute right-3.5 text-slate-400 hover:text-slate-600 hidden p-1 rounded-full hover:bg-slate-100 transition-colors flex items-center justify-center"
                    title="Clear search">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div id="global-search-dropdown"
             class="absolute left-0 right-0 top-full mt-2 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden hidden transition-all duration-200 z-50">
            <div id="search-results-content" class="max-h-[420px] overflow-y-auto p-2 space-y-3"></div>
            <div class="px-4 py-2 bg-slate-50/90 text-[11px] text-slate-400 font-medium flex items-center justify-between border-t border-slate-100 select-none">
                <div class="flex items-center gap-3">
                    <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">↑</kbd> <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">↓</kbd> Navigate</span>
                    <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">↵</kbd> Select</span>
                </div>
                <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono text-slate-600">ESC</kbd> Close</span>
            </div>
        </div>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-3">

        <!-- ── NOTIFICATION BELL ──────────────────────────────────────────── -->
        <div class="relative" id="notif-container">
            <button id="notif-btn"
                    onclick="toggleNotifDropdown()"
                    class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-slate-200/70 transition-colors text-slate-500 relative"
                    title="Notifications">
                <span class="material-symbols-outlined text-[22px]" id="notif-bell-icon"
                      style="font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24;">notifications</span>
                <span id="notif-badge"
                      class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none shadow {{ ($unreadNotifCount ?? 0) > 0 ? '' : 'hidden' }} transition-all duration-300">
                    {{ ($unreadNotifCount ?? 0) > 99 ? '99+' : ($unreadNotifCount ?? 0) }}
                </span>
            </button>

            <!-- Dropdown Panel -->
            <div id="notif-dropdown"
                 class="absolute right-0 top-full mt-2.5 w-[380px] bg-white rounded-2xl shadow-[0_8px_40px_rgba(0,0,0,0.14)] border border-slate-100/80 overflow-hidden hidden z-[100]"
                 style="max-height:520px;">

                <!-- Panel header -->
                <div class="flex items-center justify-between px-4 py-3.5 border-b border-slate-100">
                    <p class="text-[15px] font-black text-[#0d326b]">Notifications</p>
                    <button id="notif-mark-all-btn"
                            onclick="markAllNotifRead()"
                            class="text-[11.5px] font-semibold text-[#0d326b] hover:underline hidden">
                        Mark all as read
                    </button>
                </div>

                <!-- List -->
                <div id="notif-list" class="overflow-y-auto" style="max-height:430px;">
                    <!-- Skeleton -->
                    <div id="notif-skeleton" class="p-2 space-y-1">
                        @for($i=0;$i<5;$i++)
                        <div class="flex items-start gap-3 px-3 py-3 animate-pulse">
                            <div class="w-10 h-10 rounded-full bg-slate-200 flex-shrink-0"></div>
                            <div class="flex-1 space-y-2 pt-1">
                                <div class="h-3 bg-slate-200 rounded-full w-3/4"></div>
                                <div class="h-2.5 bg-slate-100 rounded-full w-full"></div>
                                <div class="h-2 bg-slate-100 rounded-full w-1/3"></div>
                            </div>
                        </div>
                        @endfor
                    </div>
                    <!-- Items injected here -->
                    <div id="notif-items" class="hidden divide-y divide-slate-50"></div>
                    <!-- Empty state -->
                    <div id="notif-empty" class="hidden py-14 text-center px-6">
                        <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                            <span class="material-symbols-outlined text-slate-300 text-[28px]">notifications_off</span>
                        </div>
                        <p class="text-[13px] font-bold text-slate-500">All caught up!</p>
                        <p class="text-[12px] text-slate-400 mt-1 leading-relaxed">Student quiz answers, level-ups, and help requests will appear here.</p>
                    </div>
                </div>

            </div>
        </div>
        <!-- ── END BELL ───────────────────────────────────────────────────── -->

        <div class="h-8 border-l border-slate-200"></div>
        <div class="text-[15px] font-semibold">
            <span class="text-[#0d326b]">@yield('title')</span>
        </div>
    </div>
</header>

<script>
// ── Global Search ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const searchInput    = document.getElementById('global-search-input');
    const searchClear    = document.getElementById('global-search-clear');
    const dropdown       = document.getElementById('global-search-dropdown');
    const resultsContent = document.getElementById('search-results-content');
    const searchIcon     = document.getElementById('search-bar-icon');
    let debounceTimer = null, selectedIndex = -1, focusableItems = [];
    if (!searchInput || !dropdown) return;

    function highlightMatch(text, query) {
        if (!query) return text;
        const reg = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(reg, '<mark class="bg-blue-100 text-[#0d326b] font-bold px-0.5 rounded">$1</mark>');
    }

    async function performSearch(q) {
        q = q.trim();
        if (!q) { dropdown.classList.add('hidden'); searchClear.classList.add('hidden'); searchIcon.textContent = 'search'; return; }
        searchClear.classList.remove('hidden');
        searchIcon.textContent = 'sync'; searchIcon.classList.add('animate-spin');
        try {
            const res = await fetch(`{{ route('api.global-search') }}?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            searchIcon.classList.remove('animate-spin'); searchIcon.textContent = 'search';
            const students = data.students||[], lessons = data.lessons||[], media = data.media||[];
            if (!students.length && !lessons.length && !media.length) {
                resultsContent.innerHTML = `<div class="p-6 text-center"><span class="material-symbols-outlined text-slate-300 text-[36px] mb-2">search_off</span><p class="text-[14px] font-bold text-slate-600">No matches found for "${q}"</p><p class="text-[12px] text-slate-400 mt-1">Try searching by student name, LRN, grade level, lesson title, or media name.</p></div>`;
                dropdown.classList.remove('hidden'); focusableItems=[]; selectedIndex=-1; return;
            }
            let html = '';
            if (students.length) {
                html += `<div><div class="px-3 py-1.5 text-[10px] font-bold tracking-widest uppercase text-slate-400 flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px] text-blue-600">school</span><span>Students (${students.length})</span></div><div class="space-y-0.5 mt-1">`;
                students.forEach(s => { const t=highlightMatch(s.title,q); html+=`<a href="${s.url}" class="search-item group flex items-center justify-between p-2.5 rounded-xl hover:bg-blue-50/70 transition-all cursor-pointer"><div class="flex items-center gap-3 min-w-0"><img src="${s.avatar}" class="w-9 h-9 rounded-full object-cover shrink-0 ring-2 ring-slate-100 group-hover:ring-blue-200 transition-all"><div class="min-w-0"><p class="text-[13.5px] font-bold text-[#0d326b] truncate">${t}</p><p class="text-[11px] font-medium text-slate-400 truncate mt-0.5">${s.subtitle}</p></div></div><span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-[#0d326b] shrink-0 ml-2">${s.badge}</span></a>`; });
                html += `</div></div>`;
            }
            if (lessons.length) {
                html += `<div><div class="px-3 py-1.5 text-[10px] font-bold tracking-widest uppercase text-slate-400 flex items-center gap-1.5 ${students.length?'mt-2 border-t border-slate-100 pt-2.5':''}"><span class="material-symbols-outlined text-[14px] text-amber-500">menu_book</span><span>Lessons (${lessons.length})</span></div><div class="space-y-0.5 mt-1">`;
                lessons.forEach(l => { const t=highlightMatch(l.title,q); const bs=l.badge==='Published'?'bg-emerald-100 text-emerald-800':'bg-amber-100 text-amber-800'; html+=`<a href="${l.url}" class="search-item group flex items-center justify-between p-2.5 rounded-xl hover:bg-amber-50/60 transition-all cursor-pointer"><div class="flex items-center gap-3 min-w-0"><div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0"><span class="material-symbols-outlined text-[18px]">auto_stories</span></div><div class="min-w-0"><p class="text-[13.5px] font-bold text-[#0d326b] truncate">${t}</p><p class="text-[11px] font-medium text-slate-400 truncate mt-0.5">${l.subtitle}</p></div></div><span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${bs} shrink-0 ml-2">${l.badge}</span></a>`; });
                html += `</div></div>`;
            }
            if (media.length) {
                const hasPrev = students.length||lessons.length;
                html += `<div><div class="px-3 py-1.5 text-[10px] font-bold tracking-widest uppercase text-slate-400 flex items-center gap-1.5 ${hasPrev?'mt-2 border-t border-slate-100 pt-2.5':''}"><span class="material-symbols-outlined text-[14px] text-purple-500">perm_media</span><span>Media (${media.length})</span></div><div class="space-y-0.5 mt-1">`;
                media.forEach(m => { const t=highlightMatch(m.title,q); const ti=m.media_type==='video'?'videocam':(m.media_type==='gif'?'gif':'image'); const ss=m.source==='system'?'bg-blue-100 text-[#0d326b]':'bg-emerald-100 text-emerald-800'; const isMedia=window.location.pathname.startsWith('/media'); const ch=isMedia?`onclick="event.preventDefault();document.getElementById('global-search-dropdown').classList.add('hidden');document.getElementById('global-search-input').value='';document.getElementById('global-search-clear').classList.add('hidden');window.dispatchEvent(new CustomEvent('mediaSearch',{detail:'${q.replace(/'/g,"\\'")}'}));"`: ''; html+=`<a href="${m.url}" ${ch} class="search-item group flex items-center justify-between p-2.5 rounded-xl hover:bg-purple-50/50 transition-all cursor-pointer"><div class="flex items-center gap-3 min-w-0"><div class="w-9 h-9 rounded-xl overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center relative"><img src="${m.thumb}" class="w-full h-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"><span class="material-symbols-outlined text-slate-400 text-[18px]" style="display:none;">${ti}</span></div><div class="min-w-0"><p class="text-[13.5px] font-bold text-[#0d326b] truncate">${t}</p><p class="text-[11px] font-medium text-slate-400 truncate mt-0.5">${m.subtitle}</p></div></div><span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${ss} shrink-0 ml-2">${m.badge}</span></a>`; });
                html += `</div></div>`;
            }
            resultsContent.innerHTML = html; dropdown.classList.remove('hidden');
            focusableItems = Array.from(resultsContent.querySelectorAll('.search-item')); selectedIndex = -1;
        } catch(err) { searchIcon.classList.remove('animate-spin'); searchIcon.textContent = 'search'; }
    }

    searchInput.addEventListener('input', e => { clearTimeout(debounceTimer); const v=e.target.value; if(!v.trim()){dropdown.classList.add('hidden');searchClear.classList.add('hidden');return;} searchClear.classList.remove('hidden'); debounceTimer=setTimeout(()=>performSearch(v),180); });
    searchClear.addEventListener('click', () => { searchInput.value=''; dropdown.classList.add('hidden'); searchClear.classList.add('hidden'); searchInput.focus(); });
    searchInput.addEventListener('keydown', e => {
        if (dropdown.classList.contains('hidden')) return;
        if (e.key==='ArrowDown'){e.preventDefault();selectedIndex=(selectedIndex+1)%focusableItems.length;updateSel();}
        else if(e.key==='ArrowUp'){e.preventDefault();selectedIndex=(selectedIndex-1+focusableItems.length)%focusableItems.length;updateSel();}
        else if(e.key==='Enter'){e.preventDefault();(focusableItems[selectedIndex]||focusableItems[0])?.click();}
        else if(e.key==='Escape'){dropdown.classList.add('hidden');}
    });
    function updateSel(){focusableItems.forEach((el,i)=>{el.classList.toggle('bg-slate-100',i===selectedIndex);el.classList.toggle('ring-2',i===selectedIndex);el.classList.toggle('ring-[#0d326b]/20',i===selectedIndex);if(i===selectedIndex)el.scrollIntoView({block:'nearest'});});}
    document.addEventListener('click', e => { if(!document.getElementById('global-search-container')?.contains(e.target))dropdown.classList.add('hidden'); });
    searchInput.addEventListener('focus', () => { if(searchInput.value.trim())performSearch(searchInput.value); });
});

// ── Notifications (Facebook-style) ──────────────────────────────────────────
(function(){
    let open=false, loaded=false;

    const COLORS = {
        quiz_answered:     {bg:'#EFF6FF',ring:'#BFDBFE',fg:'#1D4ED8'},
        module_passed:     {bg:'#F5F3FF',ring:'#DDD6FE',fg:'#6D28D9'},
        checkpoint_passed: {bg:'#FFFBEB',ring:'#FDE68A',fg:'#B45309'},
        level_up:          {bg:'#ECFDF5',ring:'#A7F3D0',fg:'#047857'},
        mastery_promoted:  {bg:'#F5F3FF',ring:'#DDD6FE',fg:'#6D28D9'},
        help_request:      {bg:'#FEF2F2',ring:'#FECACA',fg:'#B91C1C'},
        streak_milestone:  {bg:'#FFF7ED',ring:'#FED7AA',fg:'#C2410C'},
    };

    function cfg(type){ return COLORS[type]||{bg:'#F8FAFC',ring:'#E2E8F0',fg:'#475569'}; }

    function renderItem(n){
        const c=cfg(n.type), unread=!n.is_read;
        // Redirect to reports page with open_student param
        const data = n.data || {};
        const sid = data.student_id;
        const dest = sid ? `/reports?open_student=${sid}` : '/reports';

        return `<div class="notif-row flex items-start gap-3 px-4 py-3.5 cursor-pointer transition-colors duration-150 ${unread?'bg-blue-50/50 hover:bg-blue-50/80':'hover:bg-slate-50/80'}"
                     data-id="${n.id}" data-dest="${dest}"
                     onclick="handleNotifClick(${n.id},'${dest}')">
            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mt-0.5 shadow-sm"
                 style="background:${c.bg};outline:1.5px solid ${c.ring};">
                <span class="material-symbols-outlined text-[18px]" style="color:${c.fg};">${n.icon}</span>
            </div>
            <div class="flex-1 min-w-0 pr-1">
                <p class="text-[13px] font-${unread?'bold':'semibold'} text-slate-800 leading-snug">${n.title}</p>
                <p class="text-[12px] text-slate-500 mt-0.5 leading-snug line-clamp-2">${n.message}</p>
                <p class="text-[11px] font-semibold mt-1.5 ${unread?'text-blue-500':'text-slate-400'}">${n.time_ago}</p>
            </div>
            ${unread?'<span class="flex-shrink-0 w-2.5 h-2.5 rounded-full bg-blue-500 mt-2 shadow-sm"></span>':''}
        </div>`;
    }

    async function loadNotifications(){
        try{
            const res  = await fetch('{{ route("notifications.latest") }}',{credentials:'same-origin'});
            const data = await res.json();
            const skel = document.getElementById('notif-skeleton');
            const items= document.getElementById('notif-items');
            const empty= document.getElementById('notif-empty');
            const badge= document.getElementById('notif-badge');
            const markBtn=document.getElementById('notif-mark-all-btn');
            if(skel) skel.classList.add('hidden');
            const notifs=data.notifications||[], count=data.unread_count||0;
            if(count>0){badge.textContent=count>99?'99+':count;badge.classList.remove('hidden');markBtn.classList.remove('hidden');}
            else{badge.classList.add('hidden');markBtn.classList.add('hidden');}
            if(!notifs.length){items.classList.add('hidden');empty.classList.remove('hidden');}
            else{items.innerHTML=notifs.map(renderItem).join('');items.classList.remove('hidden');empty.classList.add('hidden');}
            loaded=true;
        }catch(e){console.warn('notif error',e);}
    }

    async function pollBadge(){
        try{
            const res=await fetch('{{ route("notifications.unread-count") }}',{credentials:'same-origin'});
            const d=await res.json(); const count=d.count||0;
            const badge=document.getElementById('notif-badge');
            const markBtn=document.getElementById('notif-mark-all-btn');
            if(count>0){badge.textContent=count>99?'99+':count;badge.classList.remove('hidden');if(markBtn)markBtn.classList.remove('hidden');}
            else{badge.classList.add('hidden');if(markBtn)markBtn.classList.add('hidden');}
            if(open) loadNotifications();
        }catch(e){}
    }

    window.toggleNotifDropdown=function(){
        const dd=document.getElementById('notif-dropdown');
        const bell=document.getElementById('notif-bell-icon');
        open=!open;
        if(open){
            dd.classList.remove('hidden');
            bell.style.fontVariationSettings="'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24";
            loadNotifications();
            document.getElementById('global-search-dropdown')?.classList.add('hidden');
        } else {
            dd.classList.add('hidden');
            bell.style.fontVariationSettings="'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24";
        }
    };

    window.handleNotifClick=async function(id, dest){
        // Mark read silently
        try{
            await fetch(`/notifications/${id}/read`,{
                method:'POST',
                headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
                credentials:'same-origin'
            });
        }catch(e){}
        window.location.href = dest;
    };

    window.markAllNotifRead=async function(){
        try{
            await fetch('{{ route("notifications.read-all") }}',{
                method:'POST',
                headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
                credentials:'same-origin'
            });
            loadNotifications();
        }catch(e){}
    };

    // Close on outside click
    document.addEventListener('click',function(e){
        const container=document.getElementById('notif-container');
        if(open && container && !container.contains(e.target)){
            document.getElementById('notif-dropdown')?.classList.add('hidden');
            document.getElementById('notif-bell-icon').style.fontVariationSettings="'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24";
            open=false;
        }
    });

    document.addEventListener('DOMContentLoaded',()=>setInterval(pollBadge,45000));
})();
</script>
