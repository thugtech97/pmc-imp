@extends('theme.main')

@section('pagecss')
<style>
    .notif-wrap { max-width: 820px; margin: 0 auto; }
    .notif-head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:18px; }
    .notif-head h3 { margin:0; font-weight:700; }
    .notif-card { background:#fff; border:1px solid #eaedf2; border-radius:10px; overflow:hidden; }
    .notif-row { display:flex; align-items:flex-start; gap:12px; padding:15px 18px; border-bottom:1px solid #f2f4f7; text-decoration:none; color:#333; transition:background .12s; }
    .notif-row:hover { background:#f7f9fc; color:#333; }
    .notif-row.unread { background:#eef4ff; }
    .notif-row .n-dot { width:9px; height:9px; border-radius:50%; margin-top:6px; flex:0 0 auto; background:transparent; }
    .notif-row.unread .n-dot { background:#3b7ddd; }
    .notif-row .n-title { font-weight:600; font-size:14px; margin:0 0 3px; }
    .notif-row .n-msg { font-size:13px; color:#667085; margin:0 0 4px; }
    .notif-row .n-time { font-size:11.5px; color:#9aa0a6; }
    .notif-mod { font-size:10px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; padding:2px 8px; border-radius:5px; background:#eef2f7; color:#5a6b82; margin-left:8px; }
    .notif-empty { padding:60px 20px; text-align:center; color:#9aa0a6; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="notif-wrap">
        <div class="notif-head">
            <h3>Notifications</h3>
            @if ($notifications->total() > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit" class="button button-small button-rounded m-0">Mark all as read</button>
                </form>
            @endif
        </div>

        <div class="notif-card">
            @forelse ($notifications as $n)
                <a class="notif-row {{ is_null($n->read_at) ? 'unread' : '' }}" href="{{ route('notifications.read', $n->id) }}">
                    <span class="n-dot"></span>
                    <div style="flex:1;">
                        <p class="n-title">
                            {{ $n->data['title'] ?? 'Notification' }}
                            @if (!empty($n->data['module']))
                                <span class="notif-mod">{{ $n->data['module'] }}</span>
                            @endif
                        </p>
                        <p class="n-msg">{{ $n->data['message'] ?? '' }}</p>
                        <span class="n-time">{{ optional($n->created_at)->diffForHumans() }}</span>
                    </div>
                </a>
            @empty
                <div class="notif-empty">You have no notifications yet.</div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
