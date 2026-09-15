<?php

namespace App\Models\Order;

use App\Models\Customer\Customer;
use App\Models\Order\OrderItem;
use App\Models\Payment;
use App\Models\Promo\Voucher;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasUuids;

    protected $table = 'orders';

    public const STATUS_DRAFT = 0;
    public const STATUS_PENDING_APPROVAL = 1;
    public const STATUS_CONFIRMED = 2;
    public const STATUS_PROCESSING = 3;
    public const STATUS_SHIPPED = 4;
    public const STATUS_DELIVERED = 5;
    public const STATUS_CANCELLED = 6;
    public const STATUS_RETURNED = 7;

    public const PAYMENT_UNPAID = 0;
    public const PAYMENT_PAID = 1;
    public const PAYMENT_FAILED = 2;
    public const PAYMENT_REFUNDED = 3;
    public const PAYMENT_PARTIAL = 4;

    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'payment_method',
        'payment_status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'meta',
        'creator',
        'editor',
        'deleted',
        'voucher_id',
        'voucher_nominal',
        'shipping_cost',
        'shipping_cost_subsidy',
        'shipping_addresses_id',
        'transaction_fee',
        'courier_id',
    ];

    protected $appends = ['status_badge_class', 'payment_status_badge_class', 'resi', 'courier_name'];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'payment_status' => 'integer',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'transaction_fee' => 'decimal:2',
            'meta' => 'array',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('active', function ($query) {
            $query->where('deleted', false);
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id', 'id');
    }

    public function pickingList(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Picking\PickingList::class, 'order_id');
    }

    public function packingSlip(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Packing\PackingSlip::class, 'order_id');
    }

    public function packingOut(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            \App\Models\Packing\PackingOut::class,
            \App\Models\Packing\PackingSlip::class,
            'order_id',
            'packing_slip_id',
            'id',
            'id'
        );
    }

    public function delivery(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Packing\Delivery::class, 'order_id');
    }

    public function handover(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Packing\Handover::class, 'order_id');
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Invoice::class, 'order_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Shipping\Courier::class, 'courier_id');
    }

    public function getCourierIdAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (\Ramsey\Uuid\Uuid::isValid($value)) {
            return (string)$value;
        }

        static $courierCodeMap = null;
        if ($courierCodeMap === null) {
            $courierCodeMap = \App\Models\Shipping\Courier::withoutGlobalScopes()
                ->pluck('id', 'code')
                ->mapWithKeys(fn($id, $code) => [strtolower((string)$code) => (string)$id])
                ->toArray();
        }

        $code = strtolower(trim((string)$value));
        return $courierCodeMap[$code] ?? null;
    }

    public function setCourierIdAttribute($value): void
    {
        if (!empty($value) && !\Ramsey\Uuid\Uuid::isValid($value)) {
            static $courierCodeMap = null;
            if ($courierCodeMap === null) {
                $courierCodeMap = \App\Models\Shipping\Courier::withoutGlobalScopes()
                    ->pluck('id', 'code')
                    ->mapWithKeys(fn($id, $code) => [strtolower((string)$code) => (string)$id])
                    ->toArray();
            }

            $code = strtolower(trim((string)$value));
            $value = $courierCodeMap[$code] ?? null;
        }

        $this->attributes['courier_id'] = $value;
    }

    public function getResiAttribute(): ?string
    {
        if (!empty($this->meta['tracking_number'])) {
            return $this->meta['tracking_number'];
        }
        if (!empty($this->meta['resi'])) {
            return $this->meta['resi'];
        }
        if ($this->relationLoaded('delivery') && $this->delivery && !empty($this->delivery->tracking_number)) {
            return $this->delivery->tracking_number;
        }
        if ($this->relationLoaded('handover') && $this->handover && !empty($this->handover->tracking_number)) {
            return $this->handover->tracking_number;
        }
        if ($this->delivery && !empty($this->delivery->tracking_number)) {
            return $this->delivery->tracking_number;
        }
        if ($this->handover && !empty($this->handover->tracking_number)) {
            return $this->handover->tracking_number;
        }
        return null;
    }

    public function getCourierNameAttribute(): ?string
    {
        if ($this->relationLoaded('courier') && $this->courier) {
            return $this->courier->name;
        }
        if ($this->relationLoaded('delivery') && $this->delivery && $this->delivery->courier) {
            return $this->delivery->courier->name;
        }
        if ($this->relationLoaded('handover') && $this->handover && $this->handover->courier) {
            return $this->handover->courier->name;
        }
        if ($this->courier) {
            return $this->courier->name;
        }
        if (!empty($this->meta['courier_name'])) {
            return $this->meta['courier_name'];
        }
        return null;
    }

    public function getResiModalDataAttribute(): array
    {
        $recipientName = $this->meta['shipping_address']['recipient_name'] ?? null;
        $recipientPhone = $this->meta['shipping_address']['phone'] ?? null;
        $shippingAddressText = '';

        if (!empty($this->meta['shipping_address'])) {
            $addrParts = array_filter([
                $this->meta['shipping_address']['address'] ?? '',
                $this->meta['shipping_address']['sub_district'] ?? '',
                $this->meta['shipping_address']['city'] ?? '',
                $this->meta['shipping_address']['province'] ?? '',
                $this->meta['shipping_address']['postal_code'] ?? '',
            ]);
            $shippingAddressText = implode(', ', $addrParts);
        }

        if (!$shippingAddressText && $this->relationLoaded('customer') && $this->customer && method_exists($this->customer, 'addresses') && $this->customer->relationLoaded('addresses') && $this->customer->addresses->isNotEmpty()) {
            $firstAddr = $this->customer->addresses->first();
            $recipientName = $recipientName ?: ($firstAddr->recipient_name ?? $this->customer->name);
            $recipientPhone = $recipientPhone ?: ($firstAddr->phone ?? $this->customer->phone);
            $shippingAddressText = $firstAddr->address ?? '';
        }

        $recipientName = $recipientName ?: ($this->customer?->name ?? 'Guest');
        $recipientPhone = $recipientPhone ?: ($this->customer?->phone ?? '');

        $shippedAtText = '';
        if ($this->relationLoaded('delivery') && $this->delivery && $this->delivery->shipped_at) {
            $shippedAtText = is_numeric($this->delivery->shipped_at) ? date('d M Y H:i', $this->delivery->shipped_at) : \Carbon\Carbon::parse($this->delivery->shipped_at)->format('d M Y H:i');
        } elseif ($this->relationLoaded('handover') && $this->handover && $this->handover->handover_at) {
            $shippedAtText = $this->handover->handover_at->format('d M Y H:i');
        } elseif (!empty($this->meta['resi_updated_at'])) {
            $shippedAtText = \Carbon\Carbon::parse($this->meta['resi_updated_at'])->format('d M Y H:i');
        }

        return [
            'id' => $this->id,
            'order_number' => $this->order_number ?? substr($this->id, 0, 8),
            'resi' => $this->resi,
            'courier_id' => $this->courier_id ?? ($this->relationLoaded('delivery') ? $this->delivery?->courier_id : null) ?? ($this->relationLoaded('handover') ? $this->handover?->courier_id : null),
            'courier_name' => $this->courier_name ?? 'Kurir Belum Ditentukan',
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'status_badge_class' => $this->statusBadgeClass,
            'customer_name' => $recipientName,
            'customer_phone' => $recipientPhone,
            'shipping_address' => $shippingAddressText ?: 'Alamat tidak tersedia',
            'postal_code' => $this->meta['shipping_address']['postal_code'] ?? '',
            'courier_code' => $this->courier?->code ?? strtolower(explode(' ', (string)($this->courier_name ?? ''))[0] ?? 'jne'),
            'biteship_order_id' => $this->meta['biteship_order_id'] ?? null,
            'biteship_tracking_url' => $this->meta['biteship_tracking_url'] ?? null,
            'fulfillment_type' => $this->meta['fulfillment_type'] ?? null,
            'has_biteship_resi' => !empty($this->meta['biteship_order_id']) || (!empty($this->resi) && ($this->meta['fulfillment_type'] ?? '') === 'biteship'),
            'shipped_at' => $shippedAtText,
            'driver_name' => ($this->relationLoaded('delivery') ? $this->delivery?->driver_name : null) ?? ($this->relationLoaded('handover') ? $this->handover?->driver_name : null),
            'driver_phone' => ($this->relationLoaded('delivery') ? $this->delivery?->driver_phone : null) ?? ($this->relationLoaded('handover') ? $this->handover?->driver_phone : null),
            'delivery_status' => $this->delivery_status,
            'delivery_status_label' => $this->delivery_status_label,
            'delivery_status_badge_class' => $this->delivery_status_badge_class,
            'delivery_last_note' => $this->latest_delivery_log?->note ?? ($this->meta['biteship_last_note'] ?? null),
            'delivery_last_location' => $this->latest_delivery_log?->location ?? ($this->meta['biteship_last_location'] ?? null),
            'delivery_updated_at' => $this->latest_delivery_log?->created_at?->format('d M Y H:i') ?? null,
        ];
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id', 'id');
    }

    public function getEffectiveVoucherNominalAttribute(): float
    {
        $nominal = (float) ($this->voucher_nominal ?? 0);
        if ($nominal > 0) {
            return $nominal;
        }

        if ($this->voucher) {
            $subtotal = (float) ($this->subtotal ?? 0);
            if ((int) $this->voucher->type === 1) {
                return (float) ($subtotal * ((float) $this->voucher->value / 100));
            }
            return (float) $this->voucher->value;
        }

        return 0.0;
    }

    public function getPureDiscountAttribute(): float
    {
        $voucherNominal = $this->effective_voucher_nominal;

        $totalItemDiscount = 0;
        if ($this->items) {
            foreach ($this->items as $item) {
                $itemPrice = (float) ($item->unit_price ?? $item->total);
                if ($item->discount_nominal > 0) {
                    $totalItemDiscount += (float) $item->discount_nominal * (int) $item->quantity;
                } elseif ($item->discount_percent > 0) {
                    $totalItemDiscount += ($itemPrice * (float) $item->discount_percent / 100) * (int) $item->quantity;
                }
            }
        }

        $orderDiscountRemainder = max(0, (float) ($this->discount ?? 0) - $voucherNominal);
        return (float) max($totalItemDiscount, $orderDiscountRemainder);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_APPROVAL => 'Ordered',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_PROCESSING => 'Pesanan Diproses',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_RETURNED => 'Returned',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-600',
            self::STATUS_PENDING_APPROVAL => 'bg-yellow-100 text-yellow-700',
            self::STATUS_CONFIRMED => 'bg-blue-100 text-blue-700',
            self::STATUS_PROCESSING => 'bg-indigo-100 text-indigo-700',
            self::STATUS_SHIPPED => 'bg-purple-100 text-purple-700',
            self::STATUS_DELIVERED => 'bg-green-100 text-green-700',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-700',
            self::STATUS_RETURNED => 'bg-orange-100 text-orange-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public static function paymentStatusLabels(): array
    {
        return [
            self::PAYMENT_UNPAID => 'Unpaid',
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_FAILED => 'Failed',
            self::PAYMENT_REFUNDED => 'Refunded',
            self::PAYMENT_PARTIAL => 'Partial',
        ];
    }

    public function paymentStatusLabel(): string
    {
        return self::paymentStatusLabels()[$this->payment_status] ?? 'Unknown';
    }

    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_UNPAID => 'bg-gray-100 text-gray-600',
            self::PAYMENT_PAID => 'bg-success/10 text-success',
            self::PAYMENT_FAILED => 'bg-danger/10 text-danger',
            self::PAYMENT_REFUNDED => 'bg-orange-100 text-orange-700',
            self::PAYMENT_PARTIAL => 'bg-warning/10 text-warning',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function deliveryLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Packing\DeliveryLog::class, 'order_id')->latest();
    }

    public static function statusStageWeight(?string $status): int
    {
        return match (strtolower(trim((string)$status))) {
            'sedang_dikemas', 'packing' => 1,
            'dikemas', 'packed', 'siap_dikirim' => 2,
            'diserahkan_ke_kurir', 'handover', 'handed_over' => 3,
            'waybill_updated', 'waybill_issued' => 4,
            'price_updated' => 5,
            'allocated' => 6,
            'picking_up' => 7,
            'picked' => 8,
            'on_process', 'in_transit' => 9,
            'dropping_off', 'out_for_delivery' => 10,
            'delivered' => 11,
            'returned', 'return_in_transit' => 12,
            'cancelled', 'rejected' => 13,
            default => 0,
        };
    }

    public function getLatestDeliveryLogAttribute(): ?\App\Models\Packing\DeliveryLog
    {
        $logs = $this->relationLoaded('deliveryLogs')
            ? $this->deliveryLogs
            : \App\Models\Packing\DeliveryLog::where('order_id', $this->id)
                ->when(!empty($this->resi), fn($q) => $q->orWhere('waybill_id', $this->resi))
                ->orderBy('created_at', 'desc')
                ->get();

        if ($logs->isEmpty()) {
            return null;
        }

        return $logs->sort(function ($a, $b) {
            $timeCompare = ($b->created_at?->timestamp ?? 0) <=> ($a->created_at?->timestamp ?? 0);
            if ($timeCompare !== 0) {
                return $timeCompare;
            }
            return self::statusStageWeight($b->status) <=> self::statusStageWeight($a->status);
        })->first();
    }

    public static function deliveryStatusInfo(?string $rawStatus, ?string $event = null): array
    {
        $status = strtolower(trim((string) $rawStatus));

        return match ($status) {
            'delivered' => [
                'label' => 'Paket Diterima',
                'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'icon' => 'check_circle',
            ],
            'dropping_off', 'out_for_delivery' => [
                'label' => 'Sedang Diantar ke Tujuan',
                'badge_class' => 'bg-blue-100 text-blue-800 border-blue-200',
                'icon' => 'local_shipping',
            ],
            'diserahkan_ke_kurir', 'handover', 'handed_over' => [
                'label' => 'Diserahkan ke Kurir',
                'badge_class' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                'icon' => 'local_shipping',
            ],
            'dikemas', 'packed', 'siap_dikirim' => [
                'label' => 'Selesai Dikemas',
                'badge_class' => 'bg-teal-100 text-teal-800 border-teal-200',
                'icon' => 'inventory_2',
            ],
            'sedang_dikemas', 'packing' => [
                'label' => 'Sedang Dikemas',
                'badge_class' => 'bg-amber-100 text-amber-800 border-amber-200',
                'icon' => 'package',
            ],
            'picked' => [
                'label' => 'Paket Diambil Kurir',
                'badge_class' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                'icon' => 'inventory_2',
            ],
            'picking_up' => [
                'label' => 'Kurir Menjemput Paket',
                'badge_class' => 'bg-amber-100 text-amber-800 border-amber-200',
                'icon' => 'directions_run',
            ],
            'allocated' => [
                'label' => 'Kurir Telah Ditugaskan',
                'badge_class' => 'bg-sky-100 text-sky-800 border-sky-200',
                'icon' => 'assignment_ind',
            ],
            'in_transit', 'on_process' => [
                'label' => 'Dalam Perjalanan (Transit)',
                'badge_class' => 'bg-purple-100 text-purple-800 border-purple-200',
                'icon' => 'sync_alt',
            ],
            'returned', 'return_in_transit' => [
                'label' => 'Paket Retur',
                'badge_class' => 'bg-rose-100 text-rose-800 border-rose-200',
                'icon' => 'undo',
            ],
            'cancelled', 'rejected' => [
                'label' => 'Pengiriman Dibatalkan',
                'badge_class' => 'bg-red-100 text-red-800 border-red-200',
                'icon' => 'cancel',
            ],
            'waybill_updated', 'waybill_issued' => [
                'label' => 'Resi Diterbitkan',
                'badge_class' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                'icon' => 'receipt_long',
            ],
            'price_updated' => [
                'label' => 'Penyesuaian Ongkir',
                'badge_class' => 'bg-amber-100 text-amber-800 border-amber-200',
                'icon' => 'price_change',
            ],
            'pending' => [
                'label' => 'Menunggu Kurir',
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'icon' => 'schedule',
            ],
            default => [
                'label' => !empty($status) ? ucwords(str_replace('_', ' ', $status)) : 'Menunggu Update',
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'icon' => 'info',
            ],
        };
    }

    public function getDeliveryStatusAttribute(): string
    {
        $log = $this->latest_delivery_log;
        if ($log && !empty($log->status)) {
            return $log->status;
        }
        if (!empty($this->meta['biteship_status'])) {
            return $this->meta['biteship_status'];
        }
        if ((int)$this->status === self::STATUS_DELIVERED) {
            return 'delivered';
        }
        if ((int)$this->status === self::STATUS_SHIPPED) {
            return 'in_transit';
        }
        if (!empty($this->resi)) {
            return 'waybill_updated';
        }
        return 'pending';
    }

    public function getDeliveryStatusLabelAttribute(): string
    {
        if (empty($this->resi) && !$this->latest_delivery_log) {
            return 'Belum Ada Resi';
        }
        $info = self::deliveryStatusInfo($this->delivery_status);
        return $info['label'];
    }

    public function getDeliveryStatusBadgeClassAttribute(): string
    {
        if (empty($this->resi) && !$this->latest_delivery_log) {
            return 'bg-gray-100 text-gray-500 border-gray-200';
        }
        $info = self::deliveryStatusInfo($this->delivery_status);
        return $info['badge_class'];
    }
}
