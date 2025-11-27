<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminNotificationController extends Controller
{
    /**
     * Get notifications for admin dashboard
     */
    public function index(Request $request)
    {
        // Verify admin access
        if (!Session::has('user_id') || Session::get('user_type') !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $query = AdminNotification::query()->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->unread();
            } elseif ($request->filter === 'read') {
                $query->read();
            }
        }

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'admin') {
            return response()->json([
                'success' => false,
                'count' => 0
            ]);
        }

        $count = AdminNotification::unread()->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Get notification details
     */
    public function show($id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $notification = AdminNotification::with([
            'order',
            'review',
            'donation',
            'donationRequest',
            'relatedUser'
        ])->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        // Mark as read when viewing
        $adminUserId = Session::get('user_id');
        if (!$notification->is_read) {
            $notification->markAsRead($adminUserId);
        }

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $notification = AdminNotification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $adminUserId = Session::get('user_id');
        $notification->markAsRead($adminUserId);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $adminUserId = Session::get('user_id');
        $updated = AdminNotification::unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'read_by' => $adminUserId,
            ]);

        return response()->json([
            'success' => true,
            'message' => "Marked {$updated} notifications as read"
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread($id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $notification = AdminNotification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread'
        ]);
    }

    /**
     * Delete notification (soft delete)
     */
    public function destroy($id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $notification = AdminNotification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * View all notifications page
     */
    public function viewAll(Request $request)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'admin') {
            return redirect()->route('admin.login');
        }

        $query = AdminNotification::query()->orderBy('created_at', 'desc');

        // Filters
        if ($request->has('filter') && $request->filter !== 'all') {
            if ($request->filter === 'unread') {
                $query->unread();
            } elseif ($request->filter === 'read') {
                $query->read();
            }
        }

        if ($request->has('type') && $request->type !== 'all') {
            $query->ofType($request->type);
        }

        if ($request->has('priority') && $request->priority !== 'all') {
            $query->byPriority($request->priority);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $notifications = $query->paginate(20)->withQueryString();

        // Get notification types for filter
        $types = AdminNotification::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('admin.notifications', compact('notifications', 'types'));
    }
}
