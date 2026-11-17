<?php

namespace App\Mail;

use App\Models\AdoptionApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Копія анкети людині, яка її подала. */
class ApplicationReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public AdoptionApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Ми отримали вашу анкету на :name', ['name' => $this->application->animal->name]));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.application-received');
    }
}
