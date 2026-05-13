<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductStatusUpdated extends Notification
{
    use Queueable;

    protected $product;

    /**
     * Create a new notification instance.
     */
    public function __construct($product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->product->status === 'approved') {
            return [
                'title' => 'Món ăn đã được duyệt',
                'message' => 'Món ăn "' . $this->product->name . '" của bạn đã được Admin duyệt và hiện đã có thể bán.',
                'type' => 'success',
                'link' => '/restaurant/products'
            ];
        } else {
            return [
                'title' => 'Món ăn bị từ chối',
                'message' => 'Món ăn "' . $this->product->name . '" đã bị từ chối. Lý do: ' . $this->product->reject_reason,
                'type' => 'error',
                'link' => '/restaurant/products'
            ];
        }
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): \Illuminate\Notifications\Messages\BroadcastMessage
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
    }
}
