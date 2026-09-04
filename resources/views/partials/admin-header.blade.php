<header class="h-20 px-12 flex items-center justify-between flex-shrink-0 bg-[#f4f7f9] border-b border-slate-100 relative z-30">

    <!-- Left: Page identity -->
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
             style="background: linear-gradient(135deg,#0d326b,#1a6fd4)">
            <span class="material-symbols-outlined text-white text-[17px]">admin_panel_settings</span>
        </div>
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 leading-none mb-0.5">Administration</p>
            <p class="text-[15px] font-bold text-[#0d326b] leading-none">@yield('title', 'Dashboard')</p>
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
                      class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none shadow hidden transition-all duration-300">
                    0
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
                <div id="notif-list" class="overflow-y-auto divide-y divide-slate-50" style="max-height:380px;">
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
                        <p class="text-[12px] text-slate-400 mt-1 leading-relaxed">Platform alerts and system events will appear here.</p>
                    </div>
                </div>

                <!-- Footer -->
                <div id="notif-footer" class="border-t border-slate-100 bg-slate-50/90 p-2.5 text-center">
                    <a href="{{ route('notifications.index') }}"
                       class="inline-flex items-center justify-center gap-1.5 w-full py-2 text-[12.5px] font-bold text-[#0d326b] hover:text-[#1e4b8f] hover:bg-white rounded-xl transition-all duration-150 shadow-sm border border-slate-200/60">
                        <span>See all notifications</span>
                        <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                    </a>
                </div>

            </div>
        </div>
        <!-- ── END BELL ───────────────────────────────────────────────────── -->

        <div class="h-8 border-l border-slate-200"></div>

        <!-- Admin avatar + name -->
        <div class="flex items-center gap-2.5">
            <img src="{{ Auth::user()->avatarUrl() }}"
                 class="w-8 h-8 rounded-full border-2 border-slate-200 object-cover flex-shrink-0"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
            <div>
                <p class="text-[13px] font-bold text-[#0d326b] leading-none">{{ Auth::user()->name }}</p>
                <p class="text-[10px] font-semibold text-slate-400 mt-0.5">Administrator</p>
            </div>
        </div>
    </div>
</header>

<script>
// ── Admin Notifications (same pattern as teacher header) ────────────────────
(function(){
    let open=false, loaded=false;

    const COLORS = {
        quiz_answered:           {bg:'#EFF6FF',ring:'#BFDBFE',fg:'#1D4ED8'},
        module_passed:           {bg:'#F5F3FF',ring:'#DDD6FE',fg:'#6D28D9'},
        checkpoint_passed:       {bg:'#FFFBEB',ring:'#FDE68A',fg:'#B45309'},
        level_up:                {bg:'#ECFDF5',ring:'#A7F3D0',fg:'#047857'},
        mastery_promoted:        {bg:'#F5F3FF',ring:'#DDD6FE',fg:'#6D28D9'},
        help_request:            {bg:'#FEF2F2',ring:'#FECACA',fg:'#B91C1C'},
        streak_milestone:        {bg:'#FFF7ED',ring:'#FED7AA',fg:'#C2410C'},
        module_completed:        {bg:'#F0FDF4',ring:'#BBF7D0',fg:'#15803D'},
        challenge_completed:     {bg:'#F5F3FF',ring:'#DDD6FE',fg:'#6D28D9'},
        fingerspelling_completed:{bg:'#F0FDFA',ring:'#99F6E4',fg:'#0D9488'},
    };

    function cfg(type){ return COLORS[type]||{bg:'#F8FAFC',ring:'#E2E8F0',fg:'#475569'}; }

    function renderItem(n){
        const c=cfg(n.type), unread=!n.is_read;
        const data = n.data || {};
        const sid = n.student_id || data.student_id;
        let dest = n.action_url || (sid ? `/reports?open_student=${sid}` : '/notifications');
        const sName = n.student_name || 'Admin';
        const parts = sName.trim().split(/\s+/);
        const initials = (parts.length >= 2 ? (parts[0][0] + parts[parts.length - 1][0]) : sName.substring(0, 2)).toUpperCase();
        const fallback = `https://ui-avatars.com/api/?name=${encodeURIComponent(initials)}&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45`;
        const avatarSrc = n.student_avatar || fallback;

        const avatarHtml = (sid || n.student_name) ? `
            <div class="relative flex-shrink-0 w-10 h-10 mt-0.5">
                <img src="${avatarSrc}" alt="${sName}"
                     class="w-10 h-10 rounded-full object-cover shadow-sm ring-2 ring-slate-100 bg-[#0d326b]"
                     onerror="this.onerror=null;this.src='${fallback}';">
                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full flex items-center justify-center text-[10px] text-white shadow-sm ring-1 ring-white"
                     style="background:${c.fg};">
                    <span class="material-symbols-outlined text-[11px]" style="font-variation-settings:'FILL' 1,'wght' 600,'GRAD' 0,'opsz' 20;">${n.icon || 'notifications'}</span>
                </div>
            </div>
        ` : `
            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mt-0.5 shadow-sm"
                 style="background:${c.bg};outline:1.5px solid ${c.ring};">
                <span class="material-symbols-outlined text-[18px]" style="color:${c.fg};">${n.icon || 'notifications'}</span>
            </div>
        `;

        return `<div class="notif-row flex items-start gap-3 px-4 py-3.5 cursor-pointer transition-colors duration-150 ${unread?'bg-blue-50/50 hover:bg-blue-50/80':'hover:bg-slate-50/80'}"
                     data-id="${n.id}" data-dest="${dest}"
                     onclick="handleNotifClick(${n.id},'${dest}')"
                     onmouseenter="showNotifTooltip(this)"
                     onmouseleave="hideNotifTooltip()">
            ${avatarHtml}
            <div class="flex-1 min-w-0 pr-1">
                <p class="text-[13px] font-${unread?'bold':'semibold'} text-slate-800 leading-snug">${n.title}</p>
                <p class="notif-message-preview text-[12px] text-slate-500 mt-0.5 leading-snug line-clamp-2">${n.message}</p>
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
            if(count>0){badge.textContent=count>99?'99+':count;badge.classList.remove('hidden');if(markBtn)markBtn.classList.remove('hidden');}
            else{badge.classList.add('hidden');if(markBtn)markBtn.classList.add('hidden');}
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
        } else {
            dd.classList.add('hidden');
            bell.style.fontVariationSettings="'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24";
        }
    };

    window.handleNotifClick=async function(id, dest){
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

/* ── Notification full-message tooltip ─────────────────────────────── */
const _ntip = document.createElement('div');
_ntip.id = 'notif-tooltip';
_ntip.style.cssText = [
    'position:fixed',
    'z-index:99999',
    'max-width:320px',
    'background:#1e293b',
    'color:#f1f5f9',
    'font-size:12px',
    'line-height:1.55',
    'font-weight:500',
    'padding:10px 14px',
    'border-radius:12px',
    'box-shadow:0 8px 28px rgba(0,0,0,0.22)',
    'pointer-events:none',
    'opacity:0',
    'transition:opacity 0.15s ease',
    'word-break:break-word',
].join(';');
document.body.appendChild(_ntip);

let _ntipTimer;

window.showNotifTooltip = function(row) {
    const msgEl = row.querySelector('.notif-message-preview');
    if (!msgEl) return;
    if (msgEl.scrollHeight <= msgEl.clientHeight + 2) return;
    const fullText = msgEl.textContent.trim();
    _ntip.textContent = fullText;
    const rect = row.getBoundingClientRect();
    const dd   = document.getElementById('notif-dropdown');
    const ddRect = dd ? dd.getBoundingClientRect() : null;
    let left = ddRect ? ddRect.left - 336 : rect.left - 336;
    let top  = rect.top;
    if (left < 8) { left = rect.left; top = rect.bottom + 6; }
    const tipH = 80;
    if (top + tipH > window.innerHeight - 8) top = window.innerHeight - tipH - 8;
    _ntip.style.left = left + 'px';
    _ntip.style.top  = top  + 'px';
    clearTimeout(_ntipTimer);
    _ntipTimer = setTimeout(() => { _ntip.style.opacity = '1'; }, 120);
};

window.hideNotifTooltip = function() {
    clearTimeout(_ntipTimer);
    _ntip.style.opacity = '0';
};
</script>
