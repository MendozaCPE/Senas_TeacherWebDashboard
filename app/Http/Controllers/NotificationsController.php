<?php

namespace App\Http\Controllers;

use App\Models\TeacherNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    /**
     * Full notifications page.
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403);
        }

        $filter = $request->query('filter', 'all'); // all | unread | read

        $query = TeacherNotification::where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc');

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'read') {
            $query->where('is_read', true);
        }

        $notifications = $query->paginate(20)->withQueryString();

        $unreadCount = TeacherNotification::where('teacher_id', $teacher->id)
            ->where('is_read', false)
            ->count();

        return view('notifications', compact('notifications', 'unreadCount', 'filter'));
    }

    /**
     * Return unread count JSON — polled by header badge.
     */
    public function unreadCount()
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['count' => 0]);
        }

        $count = TeacherNotification::where('teacher_id', $teacher->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Return latest notifications JSON for header dropdown.
     */
    public function latest()
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }

        $notifications = TeacherNotification::where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'icon'       => $n->icon,
                'color'      => $n->color,
                'data'       => $n->data,
                'action_url' => $n->action_url,
                'is_read'    => $n->is_read,
                'time_ago'   => $n->created_at->diffForHumans(),
                'created_at' => $n->created_at->toISOString(),
            ]);

        $unreadCount = TeacherNotification::where('teacher_id', $teacher->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, $id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false], 403);
        }

        $notification = TeacherNotification::where('teacher_id', $teacher->id)
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead()
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false], 403);
        }

        TeacherNotification::where('teacher_id', $teacher->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a single notification.
     */
    public function destroy($id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false], 403);
        }

        TeacherNotification::where('teacher_id', $teacher->id)
            ->where('id', $id)
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Delete all read notifications (cleanup).
     */
    public function clearRead()
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false], 403);
        }

        TeacherNotification::where('teacher_id', $teacher->id)
            ->where('is_read', true)
            ->delete();

        return response()->json(['success' => true]);
    }
}
