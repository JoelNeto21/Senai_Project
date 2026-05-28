<?php

namespace App\Mail;

use App\Models\TeacherRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherRequestStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly TeacherRequest $teacherRequest,
        public readonly string $messageBody
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Atualizacao do pedido ' . $this->teacherRequest->protocol . ' | SenaiStock'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.teacher-request-status'
        );
    }
}
