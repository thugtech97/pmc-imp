{{--
    In-app notification bell (shared by admin panel + department theme).
    Pass $variant = 'admin' | 'theme' to match the surrounding header styling.
    Self-contained: inline SVG icon + vanilla JS, no framework dependency.

    The panel is rendered with position:fixed and moved to <body> at runtime so it
    can never be clipped by an ancestor with overflow:hidden (the department theme
    header is a vertical sidebar whose #header-wrap clips its children).
--}}
@php
    $variant = $variant ?? 'admin';
    $unreadCount = Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;
@endphp

@once
<style>
    .app-notif-bell { position: relative; display: inline-block; }
    .app-notif-toggle { position: relative; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: inherit; }
    .app-notif-toggle svg { width: 20px; height: 20px; }
    .app-notif-badge {
        position: absolute; top: -4px; right: -4px; min-width: 17px; height: 17px; padding: 0 4px;
        background: #dc3545; color: #fff; font-size: 10px; line-height: 17px; font-weight: 700;
        text-align: center; border-radius: 9px; display: none; box-shadow: 0 0 0 2px #fff;
    }
    .app-notif-badge.show { display: inline-block; }

    .app-notif-panel {
        position: fixed; top: 0; left: 0; width: 340px; max-width: calc(100vw - 16px);
        background: #fff; color: #333; border: 1px solid #e6e8ec; border-radius: 10px;
        box-shadow: 0 12px 40px rgba(0,0,0,.18); z-index: 200000; display: none; overflow: hidden;
    }
    .app-notif-panel.open { display: block; }
    .app-notif-head { display: flex; align-items: center; justify-content: space-between; padding: 13px 16px; border-bottom: 1px solid #eef0f3; }
    .app-notif-head .title { font-weight: 700; font-size: 13.5px; color: #1f2733; letter-spacing:.2px; }
    .app-notif-head a { font-size: 11px; color: #3b7ddd; cursor: pointer; text-decoration: none; font-weight: 600; }
    .app-notif-head a:hover { text-decoration: underline; }
    .app-notif-list { max-height: 340px; overflow-y: auto; }
    .app-notif-item { display: block; padding: 12px 16px; border-bottom: 1px solid #f4f5f7; text-decoration: none; color: #333; transition: background .12s; }
    .app-notif-item:last-child { border-bottom: 0; }
    .app-notif-item:hover { background: #f7f9fc; text-decoration: none; color: #333; }
    .app-notif-item.unread { background: #eef4ff; }
    .app-notif-item.unread:hover { background: #e3edff; }
    .app-notif-item .n-title { font-size: 12.5px; font-weight: 600; margin: 0 0 2px; color:#1f2733; }
    .app-notif-item .n-msg { font-size: 12px; color: #667085; margin: 0 0 4px; line-height: 1.4; }
    .app-notif-item .n-time { font-size: 10.5px; color: #9aa0a6; }
    .app-notif-empty { padding: 30px 16px; text-align: center; color: #9aa0a6; font-size: 12px; }
    .app-notif-foot { padding: 11px 16px; border-top: 1px solid #eef0f3; text-align: center; }
    .app-notif-foot a { font-size: 12px; color: #3b7ddd; text-decoration: none; font-weight: 600; }
    .app-notif-foot a:hover { text-decoration: underline; }
</style>
@endonce

<div class="app-notif-bell" data-variant="{{ $variant }}" data-feed-url="{{ route('notifications.feed') }}" data-readall-url="{{ route('notifications.readAll') }}">
    @if ($variant === 'theme')
        <a href="javascript:;" class="app-notif-toggle button button-circle border-0 button-large ht-40-f wd-40-f ht-lg-50-f wd-lg-50-f d-flex align-items-center justify-content-center p-0" aria-label="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            <span class="app-notif-badge {{ $unreadCount > 0 ? 'show' : '' }}">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        </a>
    @else
        <a href="javascript:;" class="app-notif-toggle" aria-label="Notifications" style="width:38px;height:38px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            <span class="app-notif-badge {{ $unreadCount > 0 ? 'show' : '' }}">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        </a>
    @endif

    <div class="app-notif-panel">
        <div class="app-notif-head">
            <span class="title">Notifications</span>
            <a class="app-notif-readall">Mark all as read</a>
        </div>
        <div class="app-notif-list">
            <div class="app-notif-empty">Loading&hellip;</div>
        </div>
        <div class="app-notif-foot">
            <a href="{{ route('notifications.index') }}">View all notifications</a>
        </div>
    </div>
</div>

@once
<script>
(function () {
    var PANEL_W = 340, GAP = 10, EDGE = 8;

    function init() {
        var bells = document.querySelectorAll('.app-notif-bell');
        if (!bells.length) return;
        var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

        bells.forEach(function (bell) {
            var variant = bell.getAttribute('data-variant') || 'admin';
            var toggle  = bell.querySelector('.app-notif-toggle');
            var panel   = bell.querySelector('.app-notif-panel');
            var list    = bell.querySelector('.app-notif-list');
            var badge   = bell.querySelector('.app-notif-badge');
            var readAll = bell.querySelector('.app-notif-readall');
            var feedUrl = bell.getAttribute('data-feed-url');
            var readAllUrl = bell.getAttribute('data-readall-url');

            // Move the panel to <body> so overflow:hidden ancestors can't clip it.
            document.body.appendChild(panel);

            function setBadge(count) {
                if (!badge) return;
                badge.textContent = count > 99 ? '99+' : count;
                if (count > 0) badge.classList.add('show');
                else badge.classList.remove('show');
            }

            function esc(s) {
                return String(s == null ? '' : s)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function place() {
                var r = toggle.getBoundingClientRect();
                var vw = window.innerWidth, vh = window.innerHeight;
                var w = Math.min(PANEL_W, vw - EDGE * 2);
                var ph = panel.offsetHeight || 420;

                // Drop below the bell, right edge aligned to it (works for a top bar).
                var top = r.bottom + GAP;
                var left = r.right - w;

                // Not enough room below -> flip above the bell; otherwise clamp.
                if (top + ph > vh - EDGE) {
                    if (r.top - GAP - ph > EDGE) top = r.top - GAP - ph;
                    else top = Math.max(EDGE, vh - EDGE - ph);
                }

                if (left < EDGE) left = EDGE;
                if (left + w > vw - EDGE) left = vw - EDGE - w;

                panel.style.width = w + 'px';
                panel.style.left = left + 'px';
                panel.style.top = top + 'px';
            }

            function render(items) {
                if (!items || !items.length) {
                    list.innerHTML = '<div class="app-notif-empty">You\'re all caught up.</div>';
                    return;
                }
                var html = '';
                items.forEach(function (n) {
                    html += '<a class="app-notif-item ' + (n.is_read ? '' : 'unread') + '" href="' + esc(n.url) + '">'
                          +   '<p class="n-title">' + esc(n.title) + '</p>'
                          +   '<p class="n-msg">' + esc(n.message) + '</p>'
                          +   '<span class="n-time">' + esc(n.created_at) + '</span>'
                          + '</a>';
                });
                list.innerHTML = html;
            }

            function loadFeed(renderList) {
                fetch(feedUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        setBadge(data.count || 0);
                        if (renderList) { render(data.items); place(); }
                    })
                    .catch(function () {
                        if (renderList) list.innerHTML = '<div class="app-notif-empty">Unable to load notifications.</div>';
                    });
            }

            function openPanel() {
                panel.classList.add('open');
                place();
                list.innerHTML = '<div class="app-notif-empty">Loading&hellip;</div>';
                loadFeed(true);
            }
            function closePanel() { panel.classList.remove('open'); }
            function isOpen() { return panel.classList.contains('open'); }

            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (isOpen()) closePanel();
                else openPanel();
            });

            if (readAll) {
                readAll.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    fetch(readAllUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    }).then(function () {
                        setBadge(0);
                        list.querySelectorAll('.app-notif-item.unread').forEach(function (el) {
                            el.classList.remove('unread');
                        });
                    });
                });
            }

            // Close when clicking outside both the bell and the (body-level) panel.
            document.addEventListener('click', function (e) {
                if (!bell.contains(e.target) && !panel.contains(e.target)) closePanel();
            });
            // Keep the flyout anchored while open.
            window.addEventListener('resize', function () { if (isOpen()) place(); });
            window.addEventListener('scroll', function () { if (isOpen()) place(); }, true);

            // Initial count + 60s poll (badge only).
            loadFeed(false);
            setInterval(function () { loadFeed(false); }, 60000);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endonce
