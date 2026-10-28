<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * JSON feed for the bell dropdown + 60s poll.
     * Returns the unread count and the latest notifications.
     */
    public function feed(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['count' => 0, 'items' => []]);
        }

        // The notifications() relation is already ordered by created_at desc;
        // adding ->latest() would duplicate the ORDER BY (rejected by SQL Server).
        $items = $user->notifications()
            ->take(10)
            ->get()
            ->map(function ($n) {
                return [
                    'id'         => $n->id,
                    'title'      => $n->data['title']   ?? 'Notification',
                    'message'    => $n->data['message'] ?? '',
                    'url'        => route('notifications.read', $n->id),
                    'module'     => $n->data['module']  ?? null,
                    'icon'       => $n->data['icon']    ?? null,
                    'is_read'    => !is_null($n->read_at),
                    'created_at' => optional($n->created_at)->diffForHumans(),
                ];
            });

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'items' => $items,
        ]);
    }

    /**
     * Full "View all" page. Side is chosen by role so each header/layout matches.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $notifications = $user->notifications()->paginate(20);

        // The department (theme) layout expects a $page object; the admin layout ignores it.
        $page = new Page;
        $page->name = 'Notifications';

        $view = $user->is_an_admin() || $user->role_id != 6
            ? 'admin.notifications.index'
            : 'theme.pages.notifications.index';

        return view($view, compact('notifications', 'page'));
    }

    /**
     * Mark a single notification read, then redirect to its target url.
     */
    public function read(Request $request, $id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            if (is_null($notification->read_at)) {
                $notification->markAsRead();
            }

            $url = $notification->data['url'] ?? null;

            if ($url) {
                return redirect($url);
            }
        }

        return redirect()->route('notifications.index');
    }

    /**
     * Mark every notification read for the current user.
     */
    public function readAll(Request $request)
    {
        $user = Auth::user();

        $user->unreadNotifications->markAsRead();

        if ($request->ajax()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }
}
