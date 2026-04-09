<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerAccountStatus extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $user, public $status) {}

    public function envelope(): Envelope {
        return new Envelope(
            subject: $this->status === 'active' 
                    ? 'Chúc mừng! Tài khoản FoodXpress của bạn đã được duyệt' 
                    : 'Thông báo về đăng ký đối tác FoodXpress',
        );
    }

    public function content(): Content {
        return new Content(view: 'emails.partner_status');
    }
    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
