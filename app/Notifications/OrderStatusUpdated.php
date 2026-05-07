<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
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
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $role = $notifiable->role ?? 'customer';

        if ($role === 'restaurant') {
            $stageMessages = [
                'pending' => [
                    'stage' => 'Chờ xác nhận',
                    'message' => 'Có đơn hàng mới. Vui lòng xác nhận sớm.',
                    'type' => 'info'
                ],
                'assigned' => [
                    'stage' => 'Đã có tài xế',
                    'message' => 'Hệ thống đã điều phối tài xế đến lấy đơn hàng này.',
                    'type' => 'info'
                ],
                'shipping' => [
                    'stage' => 'Tài xế đang đến',
                    'message' => 'Tài xế đang trên đường đến quán của bạn để lấy món.',
                    'type' => 'info'
                ],
                'arrived' => [
                    'stage' => 'Tài xế đã đến quán',
                    'message' => 'Tài xế đã có mặt tại quán! Vui lòng giao món cho tài xế.',
                    'type' => 'info'
                ],
                'confirmed' => [
                    'stage' => 'Đã xác nhận đơn',
                    'message' => 'Bạn đã xác nhận đơn hàng. Vui lòng chuẩn bị món.',
                    'type' => 'success'
                ],
                'picked_up' => [
                    'stage' => 'Tài xế đã lấy hàng',
                    'message' => 'Tài xế đã nhận đơn hàng từ quán và đang đi giao.',
                    'type' => 'info'
                ],
                'completed' => [
                    'stage' => 'Đã giao thành công',
                    'message' => 'Tài xế đã giao đơn hàng thành công đến tay khách hàng!',
                    'type' => 'success'
                ],
                'cancelled' => [
                    'stage' => 'Đơn bị hủy',
                    'message' => 'Đơn hàng này đã bị hủy.',
                    'type' => 'warning'
                ]
            ];
        } elseif ($role === 'shipper') {
            $amount = number_format(($this->order->shipping_fee ?? 0) + ($this->order->shipper_tip ?? 0), 0, ',', '.');
            $distanceStr = $this->order->distance ? number_format($this->order->distance, 1) . 'km' : '';
            $distanceText = $distanceStr ? " - Quãng đường: $distanceStr" : '';

            $stageMessages = [
                'assigned' => [
                    'stage' => 'Đơn hàng mới!',
                    'message' => "Bạn vừa nhận được đơn hàng mới. Thu nhập: {$amount}đ{$distanceText}. Vui lòng di chuyển tới quán để lấy hàng.",
                    'type' => 'info'
                ],
                'cancelled' => [
                    'stage' => 'Đơn hàng bị hủy',
                    'message' => 'Đơn hàng của bạn đã bị hủy.',
                    'type' => 'warning'
                ]
            ];
        } else {
            $stageMessages = [
                'pending' => [
                    'stage' => 'Chờ xác nhận',
                    'message' => 'Đơn hàng đã được đặt. Đang chờ quán xác nhận.',
                    'type' => 'info'
                ],
                'assigned' => [
                    'stage' => 'Đã tìm thấy tài xế!',
                    'message' => 'Hệ thống đã tìm thấy tài xế cho đơn hàng của bạn.',
                    'type' => 'info'
                ],
                'shipping' => [
                    'stage' => 'Tài xế đang đến quán',
                    'message' => 'Tài xế đang di chuyển đến quán để lấy hàng.',
                    'type' => 'info'
                ],
                'arrived' => [
                    'stage' => 'Tài xế đã đến quán',
                    'message' => 'Tài xế đã đến quán và đang chờ lấy món ăn của bạn.',
                    'type' => 'info'
                ],
                'confirmed' => [
                    'stage' => 'Quán đã xác nhận',
                    'message' => 'Quán đã tiếp nhận và đang chuẩn bị món ăn của bạn.',
                    'type' => 'info'
                ],
                'picked_up' => [
                    'stage' => 'Đang giao hàng',
                    'message' => 'Tài xế đã lấy hàng và đang trên đường giao đến bạn.',
                    'type' => 'info'
                ],
                'completed' => [
                    'stage' => 'Đơn hàng hoàn tất',
                    'message' => 'Đơn hàng của bạn đã được giao thành công! Chúc bạn ngon miệng.',
                    'type' => 'success'
                ],
                'cancelled' => [
                    'stage' => 'Đơn hàng bị hủy',
                    'message' => 'Đơn hàng của bạn đã bị hủy.',
                    'type' => 'warning'
                ]
            ];
        }

        $stageInfo = $stageMessages[$this->newStatus] ?? [
            'stage' => 'Cập nhật trạng thái',
            'message' => 'Đơn hàng đã được cập nhật.',
            'type' => 'info'
        ];

        // Xác định link để click tùy thuộc vào role của người nhận. 
        $link = '#';
        if ($notifiable->role === 'customer') {
            $link = '/my-orders/' . $this->order->id;
        } elseif ($notifiable->role === 'restaurant') {
            $link = '/restaurant/orders/' . $this->order->id;
        } elseif ($notifiable->role === 'shipper') {
            $link = '/shipper/orders'; // Tạm thời link shipper có thể khác
        }
        
        return [
            'title' => $stageInfo['stage'],
            'message' => $stageInfo['message'] . ' - Mã đơn: ' . $this->order->order_code,
            'type' => $stageInfo['type'],
            'order_id' => $this->order->id,
            'link' => $link
        ];
    }
}
