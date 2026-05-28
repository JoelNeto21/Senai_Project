<?php

namespace App\Services;

use App\Mail\TeacherRequestStatusMail;
use App\Models\Book;
use App\Models\Funcionario;
use App\Models\StockNotification;
use App\Models\TeacherRequest;
use App\Models\TeacherRequestMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeacherRequestService
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    public function create(array $data): TeacherRequest
    {
        $book = Book::query()->findOrFail($data['book_id']);

        $teacherRequest = TeacherRequest::create([
            'protocol' => $this->nextProtocol(),
            'teacher_name' => $data['teacher_name'],
            'teacher_email' => $data['teacher_email'] ?? null,
            'class_name' => $data['class_name'],
            'course_name' => $data['course_name'] ?? null,
            'subject' => $book->subject,
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => (int) $data['quantity'],
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pendente',
        ]);

        $this->message(
            $teacherRequest,
            'sistema',
            'SenaiStock',
            'pendente',
            'Solicitacao registrada e enviada para analise do almoxarifado.',
            false
        );

        StockNotification::create([
            'type' => 'teacher_request',
            'severity' => $teacherRequest->quantity > $book->quantity ? 'warning' : 'info',
            'title' => 'Nova solicitacao de professor',
            'body' => "{$teacherRequest->teacher_name} solicitou {$teacherRequest->quantity} un de {$teacherRequest->title}.",
            'action_url' => route('senai.dashboard', ['view' => 'teacher_requests']),
            'teacher_request_id' => $teacherRequest->id,
            'book_id' => $book->id,
        ]);

        return $teacherRequest;
    }

    public function approve(TeacherRequest $teacherRequest, ?int $funcionarioId, ?string $message = null): TeacherRequest
    {
        $teacherRequest->update([
            'status' => 'aprovado',
            'approved_at' => now(),
        ]);

        $this->message(
            $teacherRequest,
            'almoxarifado',
            null,
            'aprovado',
            $message ?: 'Pedido aprovado. O material sera separado pelo almoxarifado.',
            true,
            $funcionarioId
        );

        return $teacherRequest->fresh(['book', 'messages']);
    }

    public function reject(TeacherRequest $teacherRequest, ?int $funcionarioId, string $message): TeacherRequest
    {
        $teacherRequest->update([
            'status' => 'rejeitado',
            'rejected_at' => now(),
        ]);

        $this->message(
            $teacherRequest,
            'almoxarifado',
            null,
            'rejeitado',
            $message,
            true,
            $funcionarioId
        );

        return $teacherRequest->fresh(['book', 'messages']);
    }

    public function markPrepared(TeacherRequest $teacherRequest, ?int $funcionarioId, ?string $message = null): TeacherRequest
    {
        $teacherRequest->update([
            'status' => 'separado',
            'prepared_at' => now(),
        ]);

        $this->message(
            $teacherRequest,
            'almoxarifado',
            null,
            'separado',
            $message ?: 'Material separado e disponivel para retirada.',
            true,
            $funcionarioId
        );

        return $teacherRequest->fresh(['book', 'messages']);
    }

    public function fulfill(TeacherRequest $teacherRequest, ?int $funcionarioId): TeacherRequest
    {
        $this->stockService->withdraw(
            $teacherRequest->book,
            $teacherRequest->quantity,
            $funcionarioId,
            'Pedido do professor ' . $teacherRequest->teacher_name . ' para ' . $teacherRequest->class_name . '.'
        );

        $teacherRequest->update([
            'status' => 'atendido',
            'prepared_at' => $teacherRequest->prepared_at ?? now(),
        ]);

        $this->message(
            $teacherRequest,
            'almoxarifado',
            null,
            'atendido',
            'Pedido atendido e baixa de estoque registrada.',
            true,
            $funcionarioId
        );

        return $teacherRequest->fresh(['book', 'messages']);
    }

    public function message(
        TeacherRequest $teacherRequest,
        string $senderType,
        ?string $senderName,
        ?string $status,
        string $message,
        bool $sendEmail = false,
        ?int $funcionarioId = null
    ): TeacherRequestMessage {
        $sent = false;

        if ($sendEmail && filled($teacherRequest->teacher_email)) {
            Mail::to($teacherRequest->teacher_email)->send(
                new TeacherRequestStatusMail($teacherRequest->fresh(['book']), $message)
            );

            $teacherRequest->forceFill(['notified_at' => now()])->save();
            $sent = true;
        }

        $funcionarioId = $funcionarioId && Funcionario::whereKey($funcionarioId)->exists()
            ? $funcionarioId
            : null;

        return TeacherRequestMessage::create([
            'teacher_request_id' => $teacherRequest->id,
            'funcionario_id' => $funcionarioId,
            'sender_type' => $senderType,
            'sender_name' => $senderName,
            'status' => $status,
            'message' => $message,
            'email_sent' => $sent,
            'sent_at' => $sent ? now() : null,
        ]);
    }

    private function nextProtocol(): string
    {
        do {
            $protocol = 'SS-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (TeacherRequest::where('protocol', $protocol)->exists());

        return $protocol;
    }
}
