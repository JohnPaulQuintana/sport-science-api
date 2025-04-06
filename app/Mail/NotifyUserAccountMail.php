<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyUserAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user,$password, $login_link;

    /**
     * Create a new message instance.
     */
    public function __construct($user,$password, $login_link)
    {
        $this->user = $user;
        $this->password = $password;
        $this->login_link = $login_link;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your System Registration Details',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user_registered', // Use your actual Blade view
            with: ['user' => $this->user,'password' => $this->password, 'login_link' => $this->login_link],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
