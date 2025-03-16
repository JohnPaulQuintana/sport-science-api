<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token, $reset_link;

    /**
     * Create a new message instance.
     */
    public function __construct($token, $reset_link)
    {
        $this->token = $token;
        $this->reset_link = $reset_link;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password', // Use your actual Blade view
            with: ['token' => $this->token, 'reset_link' => $this->reset_link],
        );
    }
}
