<?php

namespace App\Mail;

use App\Models\Lesson;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LessonReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lesson $lesson
    ) {
    }

    public function envelope(): Envelope
    {
        $subject =
            $this->lesson
                ->tutorStudent
                ?->subject
            ?? 'Lesson';

        return new Envelope(
            subject: "Lesson reminder: {$subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.lesson-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}