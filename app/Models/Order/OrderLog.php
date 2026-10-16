<?php

declare(strict_types=1);

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLog extends Model
{
    use HasUuids;

    protected $table = 'order_logs';

    protected $fillable = [
        'id',
        'order_id',
        'action',
        'status_from',
        'status_to',
        'notes',
        'creator',
        'editor',
    ];

    protected $appends = [
        'action_label',
        'action_badge_class',
        'action_icon',
        'human_status_from',
        'human_status_to',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Pesanan Dibuat',
            'status_changed' => 'Status Pesanan',
            'payment_status_changed' => 'Status Pembayaran',
            'jde_push_status_changed' => 'Integrasi JDE',
            'resi_updated' => 'Resi Pengiriman',
            'courier_changed' => 'Kurir Pengiriman',
            'reconciliation' => 'Rekonsiliasi',
            default => ucwords(str_replace(['_', '-'], ' ', (string) $this->action)),
        };
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            'created' => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-300',
            'status_changed' => 'bg-blue-50 text-blue-800 border-blue-300 dark:bg-blue-950/50 dark:text-blue-300',
            'payment_status_changed' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300',
            'jde_push_status_changed' => 'bg-purple-50 text-purple-800 border-purple-300 dark:bg-purple-950/50 dark:text-purple-300',
            'resi_updated' => 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/50 dark:text-amber-300',
            'courier_changed' => 'bg-cyan-50 text-cyan-800 border-cyan-300 dark:bg-cyan-950/50 dark:text-cyan-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'created' => 'add_shopping_cart',
            'status_changed' => 'swap_horiz',
            'payment_status_changed' => 'payments',
            'jde_push_status_changed' => 'cloud_sync',
            'resi_updated' => 'local_shipping',
            'courier_changed' => 'directions_boat',
            'reconciliation' => 'receipt_long',
            default => 'history',
        };
    }

    public function getHumanStatusFromAttribute(): ?string
    {
        if ($this->status_from === null || $this->status_from === '') {
            return null;
        }

        return $this->formatStatusValue((string) $this->status_from, $this->action);
    }

    public function getHumanStatusToAttribute(): ?string
    {
        if ($this->status_to === null || $this->status_to === '') {
            return null;
        }

        return $this->formatStatusValue((string) $this->status_to, $this->action);
    }

    protected function formatStatusValue(string $val, ?string $action): string
    {
        if ($action === 'status_changed') {
            return match ((int) $val) {
                0 => 'Draft',
                1 => 'Menunggu Konfirmasi',
                2 => 'Dikonfirmasi',
                3 => 'Diproses',
                4 => 'Dikirim',
                5 => 'Selesai',
                6 => 'Dibatalkan',
                7 => 'Diretur',
                default => 'Status ' . $val,
            };
        }

        if ($action === 'payment_status_changed') {
            return match ((int) $val) {
                0 => 'Belum Bayar',
                1 => 'Lunas (Paid)',
                2 => 'Gagal',
                3 => 'Dikembalikan (Refund)',
                4 => 'Sebagian',
                default => 'Payment ' . $val,
            };
        }

        if ($action === 'jde_push_status_changed') {
            return match ((int) $val) {
                0 => 'Pending',
                1 => 'Processing',
                2 => 'Success (Terkirim)',
                3 => 'Failed (Gagal)',
                default => 'JDE ' . $val,
            };
        }

        return $val;
    }
}
