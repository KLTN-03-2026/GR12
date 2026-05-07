<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderReceived extends Notification
{
    use Queueable;
    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $itemsCount = $this->order->items->count();
        $itemsList = $this->order->items->map(function ($item) {
            return $item->product->name . ' x' . $item->quantity;
        })->join(', ');

        return [
            'title' => 'Đơn hàng mới: ' . $this->order->order_code,
            'message' => 'Có ' . $itemsCount . ' món ăn: ' . $itemsList . '. Tổng cộng: ' . number_format($this->order->total, 0, ',', '.') . 'đ',
            'type' => 'info',
            'order_id' => $this->order->id,
            'link' => '/restaurant/orders/' . $this->order->id
        ];
    }
}
