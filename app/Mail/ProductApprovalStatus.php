<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductApprovalStatus extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Product $product, public string $status) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->status === 'approved'
                ? 'Món ăn của bạn đã được duyệt'
                : 'Món ăn của bạn không được duyệt',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.product_status');
    }

    public function attachments(): array
    {
        return [];
    }
}
