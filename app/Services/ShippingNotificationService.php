<?php

namespace App\Services;

use App\Events\ShippingStatusUpdated;
use App\Models\Order\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShippingNotificationService
{
    /**
     * Dispatch push notification and database notification on shipping status update.
     */
    public static function notifyShippingUpdate(
        Order $order,
        string $status,
        ?string $customTitle = null,
        ?string $customMessage = null,
        ?string $trackingNumber = null,
        ?string $courierName = null
    ): void {
        try {
            $orderNumber = $order->order_number ?: substr($order->id, 0, 8);
            $tracking = $trackingNumber ?: ($order->resi ?? ($order->meta['tracking_number'] ?? null));
            $courier = $courierName ?: ($order->meta['shipping_address']['courier_name'] ?? ($order->courier?->name ?? 'Kurir'));

            // Status label mapping
            $normalizedStatus = strtolower(trim($status));
            $statusLabels = [
                'allocated' => 'Kurir Telah Dialokasikan',
                'picking_up' => 'Kurir Menuju Lokasi Penjemputan',
                'picked' => 'Paket Telah Diambil Kurir',
                'dropping_off' => 'Paket Menuju Alamat Penerima',
                'in_transit' => 'Dalam Perjalanan Pengiriman',
                'on_process' => 'Sedang Diproses Pengiriman',
                'delivered' => 'Pesanan Telah Diterima',
                'returned' => 'Paket Dikembalikan ke Gudang',
                'return_in_transit' => 'Paket Dalam Pengembalian',
                'cancelled' => 'Pengiriman Dibatalkan',
                'rejected' => 'Pengiriman Ditolak Kurir',
            ];
            $statusLabel = $statusLabels[$normalizedStatus] ?? ucfirst(str_replace('_', ' ', $normalizedStatus));

            // Default title & message based on status
            if ($normalizedStatus === 'delivered') {
                $title = $customTitle ?: "Pesanan #{$orderNumber} Telah Diterima! 🎉";
                $message = $customMessage ?: "Pesanan Anda dengan nomor #{$orderNumber} telah berhasil tiba di alamat tujuan.";
            } elseif (in_array($normalizedStatus, ['dropping_off', 'in_transit'], true)) {
                $title = $customTitle ?: "Pesanan #{$orderNumber} Sedang Diantar 🚚";
                $message = $customMessage ?: "Paket Anda sedang diantar oleh {$courier} menuju alamat tujuan." . ($tracking ? " No. Resi: {$tracking}." : "");
            } elseif (in_array($normalizedStatus, ['picking_up', 'picked', 'allocated', 'on_process'], true)) {
                $title = $customTitle ?: "Pesanan #{$orderNumber} Diproses Kurir 📦";
                $message = $customMessage ?: "Kurir {$courier} telah mengambil/memproses paket pesanan #{$orderNumber}." . ($tracking ? " No. Resi: {$tracking}." : "");
            } elseif (in_array($normalizedStatus, ['returned', 'return_in_transit'], true)) {
                $title = $customTitle ?: "Status Retur Pesanan #{$orderNumber} ⚠️";
                $message = $customMessage ?: "Paket pesanan #{$orderNumber} mengalami kendala dan dikembalikan oleh kurir.";
            } elseif (in_array($normalizedStatus, ['cancelled', 'rejected'], true)) {
                $title = $customTitle ?: "Pengiriman #{$orderNumber} Dibatalkan ❌";
                $message = $customMessage ?: "Pengiriman pesanan #{$orderNumber} telah dibatalkan oleh kurir.";
            } else {
                $title = $customTitle ?: "Update Pengiriman Pesanan #{$orderNumber}";
                $message = $customMessage ?: "Status pengiriman pesanan #{$orderNumber}: {$statusLabel}.";
            }

            // Resolve Customer & User ID
            $customerId = $order->customer_id;
            $userId = null;
            if ($order->relationLoaded('customer') && $order->customer) {
                $userId = $order->customer->user_id;
            } elseif ($customerId) {
                $customer = DB::table('customers')->where('id', $customerId)->first();
                $userId = $customer?->user_id;
            }
            if (!$userId && !empty($order->customer?->email)) {
                $userId = DB::table('users')->where('email', $order->customer->email)->value('id');
            }

            // Persist to notifications table so it appears in the notification bell
            if ($userId) {
                DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'title' => $title,
                    'message' => $message,
                    'type' => 'shipping_update',
                    'link_url' => '/dashboard?tab=orders',
                    'is_read' => false,
                    'is_broadcast' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Construct payload for Pusher broadcast
            $payload = [
                'order_id' => (string) $order->id,
                'order_number' => (string) $orderNumber,
                'user_id' => $userId ? (string) $userId : null,
                'customer_id' => $customerId ? (string) $customerId : null,
                'status' => $normalizedStatus,
                'status_label' => $statusLabel,
                'title' => $title,
                'message' => $message,
                'tracking_number' => $tracking ? (string) $tracking : null,
                'courier_name' => (string) $courier,
                'link_url' => '/dashboard?tab=orders',
                'created_at' => now()->toISOString(),
            ];

            // Realtime push notification via Pusher
            broadcast(new ShippingStatusUpdated($payload));

            Log::info("Shipping status notification sent via Pusher", [
                'order_id' => $order->id,
                'user_id' => $userId,
                'status' => $normalizedStatus
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to broadcast shipping notification: " . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
