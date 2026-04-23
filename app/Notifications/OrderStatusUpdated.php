<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;
    protected $order;
    protected $oldStatus;
    protected $newStatus;
    /**
     * Create a new notification instance.
     */
    public function __construct($order, $oldStatus, $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusMessages = [
            'pending' => 'Đơn hàng đang chờ xử lý',
            'processing' => 'Đơn hàng đang được chuẩn bị',
            'confirmed' => 'Đơn hàng đã được xác nhận',
            'assigned' => 'Đơn hàng đã gán shipper',
            'shipping' => 'Đơn hàng đang giao',
            'picked_up' => 'Shipper đã lấy hàng',
            'delivering' => 'Đang giao đến bạn',
            'completed' => 'Đơn hàng đã hoàn thành',
            'cancelled' => 'Đơn hàng đã hủy',
        ];

        $subject = 'Cập nhật trạng thái đơn hàng ' . $this->order->order_code;
        $message = 'Đơn hàng của bạn đã chuyển từ "' . ($statusMessages[$this->oldStatus] ?? $this->oldStatus) . '" sang "' . ($statusMessages[$this->newStatus] ?? $this->newStatus) . '".';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line($message)
            ->line('Mã đơn hàng: ' . $this->order->order_code)
            ->line('Tổng tiền: ' . number_format($this->order->total, 0, ',', '.') . ' VND')
            ->action('Xem chi tiết đơn hàng', url('/my-orders/' . $this->order->id))
            ->line('Cảm ơn bạn đã sử dụng FoodXpress!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
