<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Inventory\Inventory;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get recent notifications and unread count.
     */
    public function index(): JsonResponse
    {
        $admin = auth('admin')->user();
        if ($admin && !$admin->isSuperAdmin() && !$admin->hasPermission('notifications.index')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat notifikasi.',
                'unread_count' => 0,
                'notifications' => [],
            ], 403);
        }

        // Scope to admin/system notifications (stock alerts and general admin notifications)
        $adminQuery = Notification::where(function ($q) {
            $q->where('type', 'stock_alert')
              ->orWhereNull('user_id');
        });

        $unreadCount = (clone $adminQuery)->where('is_read', false)->count();

        $notifications = (clone $adminQuery)->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'link_url' => $notification->link_url,
                    'is_read' => (bool) $notification->is_read,
                    'created_at_human' => $notification->created_at ? $notification->created_at->diffForHumans() : '',
                    'created_at' => $notification->created_at ? $notification->created_at->toIso8601String() : '',
                ];
            });

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = Notification::find($id);

        if ($notification) {
            $notification->is_read = true;
            $notification->save();
        }

        $unreadCount = Notification::where(function ($q) {
            $q->where('type', 'stock_alert')
              ->orWhereNull('user_id');
        })->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        Notification::where(function ($q) {
            $q->where('type', 'stock_alert')
              ->orWhereNull('user_id');
        })->where('is_read', false)->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Test trigger a stock alert broadcast (useful for debugging/testing).
     */
    public function testStockAlert(): JsonResponse
    {
        $inventory = Inventory::where('on_stock', '<=', 5)->first();

        if (!$inventory) {
            $inventory = Inventory::first();
        }

        if (!$inventory) {
            return response()->json([
                'success' => false,
                'message' => 'No inventory record found in database to test.',
            ], 404);
        }

        $result = InventoryService::checkAndBroadcastStockAlert($inventory);

        return response()->json([
            'success' => true,
            'message' => 'Stock alert event dispatched successfully.',
            'alert_data' => $result,
        ]);
    }
}
