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
                '1' => 'Pesanan Diterima',
                '2' => 'Pesanan Dikonfirmasi',
                '3' => 'Pesanan Sedang Diproses',
                '4' => 'Pesanan Telah Dikirim',
                '5' => 'Pesanan Telah Diterima',
                '6' => 'Pesanan Dibatalkan',
                '7' => 'Pesanan Dikembalikan',
                'ordered' => 'Pesanan Diterima',
                'confirmed' => 'Pesanan Dikonfirmasi',
                'processing' => 'Pesanan Sedang Diproses',
                'shipped' => 'Pesanan Telah Dikirim',
                'allocated' => 'Kurir Telah Ditugaskan',
                'picking_up' => 'Kurir Sedang Menjemput Paket',
                'picked' => 'Paket Telah Dijemput Kurir',
                'dropping_off' => 'Paket Menuju Alamat Penerima',
                'in_transit' => 'Dalam Perjalanan Pengiriman',
                'on_process' => 'Sedang Diproses Pengiriman',
                'delivered' => 'Pesanan Telah Diterima',
                'returned' => 'Paket Dikembalikan ke Gudang',
                'return_in_transit' => 'Paket Dalam Pengembalian',
                'cancelled' => 'Pesanan Dibatalkan',
                'rejected' => 'Pengiriman Ditolak Kurir',
            ];
            $statusLabel = $statusLabels[$normalizedStatus] ?? ucfirst(str_replace('_', ' ', $normalizedStatus));

            // Helper to check if a message/title is a generic placeholder
            $isGenericText = function (?string $text): bool {
                if (empty($text)) {
                    return true;
                }
                $clean = strtolower(trim($text));
                $genericList = [
                    'pesanan diproses kurir',
                    'pesanan sedang diproses',
                    'pesanan diproses',
                    'status updated',
                    'status update',
                    'allocated',
                    'picking_up',
                    'picked',
                    'dropping_off',
                    'in_transit',
                    'on_process',
                    'processing',
                    'shipped',
                    'delivered',
                ];
                return in_array($clean, $genericList, true);
            };

            $effectiveCustomTitle = !$isGenericText($customTitle) ? $customTitle : null;
            $effectiveCustomMessage = !$isGenericText($customMessage) ? $customMessage : null;
            $resiSuffix = $tracking ? " No. Resi: {$tracking}." : "";

            // Title & message based on specific shipping status
            if ($normalizedStatus === 'allocated') {
                $title = $effectiveCustomTitle ?: "Pesanan #{$orderNumber}: Kurir Telah Ditugaskan 📋";
                $baseMsg = "Kurir {$courier} telah ditugaskan untuk menjemput dan mengirimkan pesanan #{$orderNumber}.{$resiSuffix}";
                $message = $effectiveCustomMessage ? "{$baseMsg} ({$effectiveCustomMessage})" : $baseMsg;
            } elseif ($normalizedStatus === 'picking_up') {
                $title = $effectiveCustomTitle ?: "Pesanan #{$orderNumber}: Kurir Sedang Menjemput Paket 🛵";
                $baseMsg = "Kurir {$courier} sedang menuju lokasi penjemputan untuk mengambil paket pesanan #{$orderNumber}.{$resiSuffix}";
                $message = $effectiveCustomMessage ? "{$baseMsg} ({$effectiveCustomMessage})" : $baseMsg;
            } elseif ($normalizedStatus === 'picked') {
                $title = $effectiveCustomTitle ?: "Pesanan #{$orderNumber}: Paket Telah Dijemput Kurir 📦";
                $baseMsg = "Paket pesanan #{$orderNumber} telah berhasil dijemput oleh kurir {$courier} dan siap diproses ke tahap pengiriman berikutnya.{$resiSuffix}";
                $message = $effectiveCustomMessage ? "{$baseMsg} ({$effectiveCustomMessage})" : $baseMsg;
            } elseif ($normalizedStatus === 'delivered' || $normalizedStatus === '5') {
                $title = $effectiveCustomTitle ?: "Pesanan #{$orderNumber} Telah Diterima! 🎉";
                $baseMsg = "Pesanan Anda dengan nomor #{$orderNumber} telah berhasil tiba di alamat tujuan.{$resiSuffix}";
                $message = $effectiveCustomMessage ?: $baseMsg;
            } elseif (in_array($normalizedStatus, ['dropping_off', 'in_transit', 'shipped', '4'], true)) {
                $title = $effectiveCustomTitle ?: "Pesanan #{$orderNumber} Sedang Diantar 🚚";
                $baseMsg = "Paket pesanan #{$orderNumber} sedang diantar oleh {$courier} menuju alamat tujuan.{$resiSuffix}";
                $message = $effectiveCustomMessage ? "{$baseMsg} ({$effectiveCustomMessage})" : $baseMsg;
            } elseif (in_array($normalizedStatus, ['on_process', 'processing', '3'], true)) {
                $title = $effectiveCustomTitle ?: "Pesanan #{$orderNumber} Sedang Diproses 📦";
                $message = $effectiveCustomMessage ?: "Pesanan #{$orderNumber} sedang disiapkan dan diproses oleh tim kami.";
            } elseif (in_array($normalizedStatus, ['confirmed', '2'], true)) {
                $title = $effectiveCustomTitle ?: "Pesanan #{$orderNumber} Dikonfirmasi ✅";
                $message = $effectiveCustomMessage ?: "Pembayaran dan pesanan #{$orderNumber} telah berhasil dikonfirmasi.";
            } elseif (in_array($normalizedStatus, ['returned', 'return_in_transit', '7'], true)) {
                $title = $effectiveCustomTitle ?: "Status Retur Pesanan #{$orderNumber} ⚠️";
                $baseMsg = "Paket pesanan #{$orderNumber} mengalami kendala dan dikembalikan oleh kurir.{$resiSuffix}";
                $message = $effectiveCustomMessage ? "{$baseMsg} ({$effectiveCustomMessage})" : $baseMsg;
            } elseif (in_array($normalizedStatus, ['cancelled', 'rejected', '6'], true)) {
                $title = $effectiveCustomTitle ?: "Pesanan #{$orderNumber} Dibatalkan ❌";
                $message = $effectiveCustomMessage ?: "Pesanan #{$orderNumber} telah dibatalkan.";
            } else {
                $title = $effectiveCustomTitle ?: "Update Pesanan #{$orderNumber}: {$statusLabel}";
                $message = $effectiveCustomMessage ?: "Status pesanan #{$orderNumber}: {$statusLabel}.{$resiSuffix}";
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
            if (!$userId && !empty($order->meta['customer']['user_id'])) {
                $userId = $order->meta['customer']['user_id'];
            }
            if (!$userId && !empty($order->meta['customer']['email'])) {
                $userId = DB::table('users')->where('email', $order->meta['customer']['email'])->value('id');
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
