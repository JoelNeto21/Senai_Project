<?php

namespace Tests\Feature;

use App\Mail\TeacherRequestStatusMail;
use App\Models\Book;
use App\Models\TeacherRequest;
use App\Models\TeacherRequestMessage;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TeacherRequestFlowTest extends TestCase
{
    public function test_public_teacher_can_create_request_and_receive_protocol(): void
    {
        $book = Book::factory()->create(['quantity' => 20]);

        $response = $this->post(route('teacher-requests.store'), [
            'teacher_name' => 'Prof. Ana Souza',
            'teacher_email' => 'ana.souza@senai.br',
            'course_name' => 'Desenvolvimento de Sistemas',
            'class_name' => 'DS-1A',
            'book_id' => $book->id,
            'quantity' => 12,
            'notes' => 'Aula pratica de logica.',
        ]);

        $teacherRequest = TeacherRequest::first();

        $response->assertRedirect(route('teacher-requests.show', $teacherRequest->protocol));
        $this->assertNotNull($teacherRequest->protocol);
        $this->assertDatabaseHas('teacher_request_messages', [
            'teacher_request_id' => $teacherRequest->id,
            'status' => 'pendente',
        ]);
        $this->assertDatabaseHas('stock_notifications', [
            'teacher_request_id' => $teacherRequest->id,
            'type' => 'teacher_request',
        ]);
    }

    public function test_almoxarifado_approval_sends_email_and_records_message(): void
    {
        Mail::fake();

        $book = Book::factory()->create(['quantity' => 30]);
        $teacherRequest = TeacherRequest::create([
            'protocol' => 'SS-20260526-ABCDE',
            'teacher_name' => 'Prof. Bruno',
            'teacher_email' => 'bruno@senai.br',
            'class_name' => 'MEC-2A',
            'course_name' => 'Mecanica',
            'subject' => $book->subject,
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 10,
            'status' => 'pendente',
        ]);

        $response = $this
            ->withEmployeeSession()
            ->post(route('stock.teacher-requests.approve', $teacherRequest), [
                'message' => 'Pedido aprovado para separacao.',
            ]);

        $response->assertRedirect();
        $this->assertSame('aprovado', $teacherRequest->fresh()->status);
        $this->assertTrue(TeacherRequestMessage::where('teacher_request_id', $teacherRequest->id)->where('email_sent', true)->exists());
        Mail::assertSent(TeacherRequestStatusMail::class);
    }

    public function test_fulfill_request_never_allows_negative_stock(): void
    {
        Mail::fake();

        $book = Book::factory()->create(['quantity' => 2]);
        $teacherRequest = TeacherRequest::create([
            'protocol' => 'SS-20260526-XYZ12',
            'teacher_name' => 'Prof. Carlos',
            'teacher_email' => 'carlos@senai.br',
            'class_name' => 'ELE-3C',
            'course_name' => 'Eletrica',
            'subject' => $book->subject,
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 5,
            'status' => 'pendente',
        ]);

        $response = $this
            ->withEmployeeSession()
            ->post(route('stock.teacher-requests.fulfill', $teacherRequest));

        $response->assertSessionHasErrors();
        $this->assertSame(2, $book->fresh()->quantity);
    }
}
