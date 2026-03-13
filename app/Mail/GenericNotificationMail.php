<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $text;
    public $product;
    public $shortLink;

    public function __construct($title, $text, $product = null, $shortLink = null)
    {
        $this->title = $title;
        $this->text = $text;
        $this->product = $product;
        $this->shortLink = $shortLink;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notifications.generic',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
