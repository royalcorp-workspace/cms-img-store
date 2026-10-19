<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class StockAlertEvent implements ShouldBroadcastNow
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('admin.notifications'),
            new Channel('admin.stock-alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stock.alert';
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}
