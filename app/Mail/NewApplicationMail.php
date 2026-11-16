<?php

namespace App\Mail;

use App\Models\AdoptionApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Сповіщення адміністратору про нову анкету (FR-05). Надсилається через чергу. */
class NewApplicationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public AdoptionApplication $application)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Нова анкета: '.$this->application->animal->name);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.new-application');
    }
}
