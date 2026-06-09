<?php

namespace Tests\Feature;

use App\Mail\TeacherRequestStatusMail;
use App\Models\Book;
use App\Models\Cargo;
use App\Models\Curso;
use App\Models\Funcionario;
use App\Models\TeacherRequest;
use App\Models\TeacherRequestMessage;
use App\Models\Turma;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TeacherRequestFlowTest extends TestCase
{
    public function test_professor_can_create_request_from_authenticated_profile(): void
    {
        $book = Book::factory()->create(['quantity' => 20, 'subject' => 'Desenvolvimento de Sistemas']);
        $curso = Curso::create(['nome_curso' => 'Desenvolvimento de Sistemas']);
        $turma = Turma::create(['nome_turma' => 'DS-1A', 'curso_id' => $curso->id]);

        $cargo = Cargo::firstOrCreate(['Nome_cargo' => 'Professor']);
        $professor = Funcionario::factory()->create(['Id_cargo_FK' => $cargo->Id_cargo]);

        $response = $this->withSession([
            'employee' => [
                'id' => $professor->Id_funcionario,
                'name' => $professor->Nome,
                'cargo' => 'Professor',
            ],
        ])->post(route('stock.teacher-requests.store'), [
            'teacher_email' => 'ana.souza@senai.br',
            'curso_id' => $curso->id,
            'turma_id' => $turma->id,
            'book_id' => $book->id,
            'quantity' => 12,
            'notes' => 'Aula pratica de logica.',
        ]);

        $teacherRequest = TeacherRequest::first();

        $response->assertRedirect(route('senai.dashboard', ['view' => 'teacher_requests']));
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

    public function test_public_request_area_is_removed(): void
    {
        $this->get('/solicitar-livros')->assertNotFound();
        $this->get('/solicitacoes/SS-TEST')->assertNotFound();
    }

    public function test_coordenador_approval_sends_email_and_records_message(): void
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
            ->withEmployeeSession('Coordenador')
            ->post(route('stock.teacher-requests.fulfill', $teacherRequest), ['quantity' => 5]);

        $response->assertSessionHasErrors();
        $this->assertSame(2, $book->fresh()->quantity);
    }
}
