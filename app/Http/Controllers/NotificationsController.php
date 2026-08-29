<?php

namespace App\Http\Controllers;

use App\Models\Student;
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

        // Batch-load students for notifications to avoid N+1 queries
        $studentIds = collect($notifications->items())
            ->map(fn ($n) => $n->data['student_id'] ?? null)
            ->filter()
            ->unique()
            ->values();

        $students = $studentIds->isNotEmpty()
            ? Student::whereIn('student_id', $studentIds)->get()->keyBy('student_id')
            : collect();

        $notifications->getCollection()->transform(function ($n) use ($students) {
            $studentId       = $n->data['student_id'] ?? null;
            $student         = $studentId ? ($students[$studentId] ?? null) : null;
            $studentName     = $student ? trim($student->first_name . ' ' . $student->last_name) : ($n->data['student_name'] ?? null);
            $studentAvatar   = $student ? $student->avatarUrl() : null;
            
            // Calculate first & last name initials
            $initials = $student ? $student->initials : self::extractInitials($studentName);
            $studentFallback = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45';

            $n->student          = $student;
            $n->student_name     = $studentName;
            $n->student_avatar   = $studentAvatar;
            $n->student_fallback = $studentFallback;
            return $n;
        });

        $unreadCount = TeacherNotification::where('teacher_id', $teacher->id)
            ->where('is_read', false)
            ->count();

        $allCount = TeacherNotification::where('teacher_id', $teacher->id)->count();
        $readCount = TeacherNotification::where('teacher_id', $teacher->id)->where('is_read', true)->count();

        return view('notifications', compact('notifications', 'unreadCount', 'allCount', 'readCount', 'filter'));
    }

    /**
     * Helper to extract initials from first and last name.
     */
    public static function extractInitials(?string $name): string
    {
        $name = trim($name ?? '');
        if (empty($name)) {
            return 'S';
        }
        $parts = preg_split('/\s+/', $name);
        if (count($parts) >= 2) {
            $first = mb_substr($parts[0], 0, 1);
            $last  = mb_substr($parts[count($parts) - 1], 0, 1);
            return strtoupper($first . $last);
        }
        return strtoupper(mb_substr($name, 0, min(2, mb_strlen($name))));
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

        $rawNotifs = TeacherNotification::where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        $studentIds = $rawNotifs
            ->map(fn ($n) => $n->data['student_id'] ?? null)
            ->filter()
            ->unique()
            ->values();

        $students = $studentIds->isNotEmpty()
            ? Student::whereIn('student_id', $studentIds)->get()->keyBy('student_id')
            : collect();

        $notifications = $rawNotifs->map(function ($n) use ($students) {
            $studentId       = $n->data['student_id'] ?? null;
            $student         = $studentId ? ($students[$studentId] ?? null) : null;
            $studentName     = $student ? trim($student->first_name . ' ' . $student->last_name) : ($n->data['student_name'] ?? null);
            $studentAvatar   = $student ? $student->avatarUrl() : null;
            $initials        = $student ? $student->initials : self::extractInitials($studentName);
            $studentFallback = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45';

            return [
                'id'               => $n->id,
                'type'             => $n->type,
                'title'            => $n->title,
                'message'          => $n->message,
                'icon'             => $n->icon,
                'color'            => $n->color,
                'data'             => $n->data,
                'student_id'       => $studentId,
                'student_name'     => $studentName,
                'student_avatar'   => $studentAvatar,
                'student_fallback' => $studentFallback,
                'action_url'       => (function() use ($n, $studentId) {
                    $url = $n->action_url;
                    if ($url && preg_match('#^/students/(\d+)$#', $url, $m)) {
                        return '/reports?open_student=' . $m[1];
                    }
                    return $url ?? ($studentId ? '/reports?open_student=' . $studentId : null);
                })(),
                'is_read'          => (bool) $n->is_read,
                'time_ago'         => $n->created_at->diffForHumans(),
                'created_at'       => $n->created_at->toISOString(),
            ];
        });

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
     * Mark a single notification as unread.
     */
    public function markUnread(Request $request, $id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false], 403);
        }

        $notification = TeacherNotification::where('teacher_id', $teacher->id)
            ->findOrFail($id);

        $notification->markAsUnread();

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
