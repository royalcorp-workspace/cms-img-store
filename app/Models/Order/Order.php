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
        'transaction_fee',
    ];

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

    public function packingOut(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Packing\PackingOut::class, 'order_id');
    }

    public function delivery(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Packing\Delivery::class, 'order_id');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id', 'id');
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_PROCESSING => 'Processing',
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
}
