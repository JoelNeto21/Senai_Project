<?php

namespace App\Services;

use App\Mail\TeacherRequestStatusMail;
use App\Models\Book;
use App\Models\Funcionario;
use App\Models\PurchaseOrder;
use App\Models\StockNotification;
use App\Models\Supplier;
use App\Models\TeacherRequest;
use App\Models\TeacherRequestMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TeacherRequestService
{
    public function __construct(private readonly StockService $stockService) {}

    public function create(array $data): TeacherRequest
    {
        $book = Book::query()->findOrFail($data['book_id']);

        $teacherRequest = DB::transaction(function () use ($book, $data): TeacherRequest {
            $teacherRequest = TeacherRequest::create([
                'protocol' => $this->nextProtocol(),
                'requested_by_funcionario_id' => $data['requested_by_funcionario_id'] ?? null,
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

            $missing = max($teacherRequest->quantity - $book->quantity, 0);

            if ($missing > 0) {
                $this->createPurchaseOrder($teacherRequest, $missing);
                $teacherRequest->update(['status' => 'compra']);
            }

            return $teacherRequest;
        });

        $this->message(
            $teacherRequest,
            'sistema',
            'SenaiStock',
            $teacherRequest->status,
            $teacherRequest->status === 'compra'
                ? 'Solicitacao registrada. A quantidade faltante foi enviada para aprovacao de compra.'
                : 'Solicitacao registrada e enviada para análise da coordenação.',
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
            'coordenacao',
            null,
            'aprovado',
            $message ?: 'Pedido aprovado pela coordenação.',
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
            'coordenacao',
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
            'coordenacao',
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
            'Pedido do professor '.$teacherRequest->teacher_name.' para '.$teacherRequest->class_name.'.'
        );

        $teacherRequest->update([
            'status' => 'atendido',
            'prepared_at' => $teacherRequest->prepared_at ?? now(),
        ]);

        $this->message(
            $teacherRequest,
            'coordenacao',
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
            try {
                Mail::to($teacherRequest->teacher_email)->send(
                    new TeacherRequestStatusMail($teacherRequest->fresh(['book']), $message)
                );

                $teacherRequest->forceFill(['notified_at' => now()])->save();
                $sent = true;
            } catch (\Throwable $exception) {
                Log::warning('Falha ao enviar atualização de pedido por e-mail.', [
                    'teacher_request_id' => $teacherRequest->id,
                    'status' => $status,
                    'exception' => $exception->getMessage(),
                ]);
            }
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
            $protocol = 'SS-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (TeacherRequest::where('protocol', $protocol)->exists());

        return $protocol;
    }

    private function createPurchaseOrder(TeacherRequest $teacherRequest, int $missing): PurchaseOrder
    {
        $order = PurchaseOrder::create([
            'order_number' => 'PED-'.now()->format('ymd-His').'-'.Str::upper(Str::random(3)),
            'supplier_id' => Supplier::where('status', 'ativo')->value('id'),
            'requested_by_funcionario_id' => $teacherRequest->requested_by_funcionario_id,
            'status' => 'pendente_aprovacao',
            'generated_at' => now(),
            'notes' => "Compra automatica para a solicitacao {$teacherRequest->protocol}.",
        ]);

        $order->items()->create([
            'teacher_request_id' => $teacherRequest->id,
            'book_id' => $teacherRequest->book_id,
            'title' => $teacherRequest->title,
            'quantity' => $missing,
            'type' => 'restock',
            'justification' => "Quantidade faltante para atender {$teacherRequest->teacher_name}.",
        ]);

        return $order;
    }
}
