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
use RuntimeException;
use Tests\TestCase;

class TeacherRequestFlowTest extends TestCase
{
    public function test_user_can_delete_only_own_request_and_dismiss_own_notifications(): void
    {
        $owner = \App\Models\Funcionario::factory()->create();
        $other = \App\Models\Funcionario::factory()->create();
        $book = Book::factory()->create();
        $ownRequest = \App\Models\TeacherRequest::create([
            'protocol' => 'SS-OWN-001',
            'requested_by_funcionario_id' => $owner->Id_funcionario,
            'teacher_name' => $owner->Nome,
            'class_name' => 'TURMA-1',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 1,
            'status' => 'pendente',
        ]);
        $otherRequest = \App\Models\TeacherRequest::create([
            'protocol' => 'SS-OTHER-001',
            'requested_by_funcionario_id' => $other->Id_funcionario,
            'teacher_name' => $other->Nome,
            'class_name' => 'TURMA-2',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 1,
            'status' => 'pendente',
        ]);
        $ownRequest->messages()->create([
            'sender_type' => 'sistema',
            'status' => 'pendente',
            'message' => 'Atualização própria',
        ]);

        $this->withSession([
            'employee' => [
                'id' => $owner->Id_funcionario,
                'name' => $owner->Nome,
                'cargo' => 'Professor',
                'role_key' => 'professor',
            ],
        ]);

        $this->delete(route('stock.teacher-requests.notifications.dismiss', $ownRequest))
            ->assertRedirect();
        $this->assertDatabaseHas('teacher_request_messages', ['teacher_request_id' => $ownRequest->id]);
        $this->assertNotNull($ownRequest->fresh()->notifications_dismissed_at);
        $this->assertSame(
            $ownRequest->messages()->latest()->value('id'),
            $ownRequest->fresh()->notifications_dismissed_message_id
        );
        $this->get(route('senai.dashboard', ['view' => 'teacher_requests']))
            ->assertDontSee('Atualização própria');

        $ownRequest->messages()->create([
            'sender_type' => 'sistema',
            'status' => 'pendente',
            'message' => 'Nova atualização',
        ]);
        $this->get(route('senai.dashboard', ['view' => 'teacher_requests']))
            ->assertSee('Nova atualização');

        $this->delete(route('stock.teacher-requests.destroy', $otherRequest))
            ->assertForbidden();
        $this->assertDatabaseHas('teacher_requests', ['id' => $otherRequest->id]);

        $this->delete(route('stock.teacher-requests.destroy', $ownRequest))
            ->assertRedirect(route('senai.dashboard', ['view' => 'teacher_requests']));
        $this->assertDatabaseMissing('teacher_requests', ['id' => $ownRequest->id]);
    }

    public function test_professor_can_delete_only_own_pending_request_and_coordinator_can_delete_any_request(): void
    {
        $professor = Funcionario::factory()->create();
        $other = Funcionario::factory()->create();
        $book = Book::factory()->create();
        $approved = TeacherRequest::create([
            'protocol' => 'SS-APPROVED',
            'requested_by_funcionario_id' => $professor->Id_funcionario,
            'teacher_name' => $professor->Nome,
            'class_name' => 'TURMA-1',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 1,
            'status' => 'aprovado',
        ]);
        $otherPending = TeacherRequest::create([
            'protocol' => 'SS-OTHER-PENDING',
            'requested_by_funcionario_id' => $other->Id_funcionario,
            'teacher_name' => $other->Nome,
            'class_name' => 'TURMA-2',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 1,
            'status' => 'pendente',
        ]);

        $professorSession = [
            'employee' => [
                'id' => $professor->Id_funcionario,
                'name' => $professor->Nome,
                'cargo' => 'Professor',
            ],
        ];

        $this->withSession($professorSession)
            ->delete(route('stock.teacher-requests.destroy', $approved))
            ->assertForbidden();
        $this->withSession($professorSession)
            ->delete(route('stock.teacher-requests.destroy', $otherPending))
            ->assertForbidden();

        $this->withSession([
            'employee' => [
                'id' => $professor->Id_funcionario,
                'name' => 'Coordenador',
                'cargo' => 'Coordenador',
            ],
        ])->delete(route('stock.teacher-requests.destroy', $otherPending))
            ->assertRedirect(route('senai.dashboard', ['view' => 'teacher_requests']));

        $this->assertDatabaseMissing('teacher_requests', ['id' => $otherPending->id]);
        $this->assertDatabaseHas('teacher_requests', ['id' => $approved->id]);
    }

    public function test_request_cards_show_class_and_course_and_are_ordered_by_closest_due_date(): void
    {
        $book = Book::factory()->create();
        TeacherRequest::create([
            'protocol' => 'SS-LATER',
            'teacher_name' => 'Prof. Data',
            'class_name' => 'DS-3A',
            'course_name' => 'Desenvolvimento de Sistemas',
            'book_id' => $book->id,
            'title' => 'Livro com prazo distante',
            'quantity' => 1,
            'status' => 'pendente',
            'due_date' => now()->addDays(10),
        ]);
        TeacherRequest::create([
            'protocol' => 'SS-SOONER',
            'teacher_name' => 'Prof. Data',
            'class_name' => 'DS-1A',
            'course_name' => 'Desenvolvimento de Sistemas',
            'book_id' => $book->id,
            'title' => 'Livro com prazo próximo',
            'quantity' => 1,
            'status' => 'pendente',
            'due_date' => now()->addDay(),
        ]);

        $response = $this->withSession([
            'employee' => [
                'id' => 1,
                'name' => 'Coordenador',
                'cargo' => 'Coordenador',
            ],
        ])->get(route('senai.dashboard', ['view' => 'teacher_requests']));

        $response->assertOk()
            ->assertSee('Turma: DS-1A')
            ->assertSee('Curso: Desenvolvimento de Sistemas')
            ->assertSeeInOrder(['Livro com prazo próximo', 'Livro com prazo distante']);
    }
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

    public function test_request_cannot_be_separated_before_approval(): void
    {
        $book = Book::factory()->create(['quantity' => 10]);
        $teacherRequest = TeacherRequest::create([
            'protocol' => 'SS-APPROVAL-FIRST',
            'teacher_name' => 'Prof. Carlos',
            'class_name' => 'ELE-3C',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 2,
            'status' => 'pendente',
        ]);

        $this->withEmployeeSession('Coordenador')
            ->post(route('stock.teacher-requests.fulfill', $teacherRequest), ['quantity' => 2])
            ->assertSessionHasErrors('teacher_request');

        $this->assertSame(10, $book->fresh()->quantity);
    }

    public function test_rejection_is_saved_and_visible_even_when_email_delivery_fails(): void
    {
        $cargo = Cargo::firstOrCreate(['Nome_cargo' => 'Professor']);
        $professor = Funcionario::factory()->create(['Id_cargo_FK' => $cargo->Id_cargo]);
        $book = Book::factory()->create(['quantity' => 10]);
        $teacherRequest = TeacherRequest::create([
            'protocol' => 'SS-REJECT-EMAIL-FAIL',
            'requested_by_funcionario_id' => $professor->Id_funcionario,
            'teacher_name' => $professor->Nome,
            'teacher_email' => 'professor@senai.br',
            'class_name' => 'DS-1A',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 2,
            'status' => 'pendente',
        ]);

        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('SMTP indisponível'));

        $this->withEmployeeSession('Coordenador')
            ->post(route('stock.teacher-requests.reject', $teacherRequest), ['message' => 'Pedido recusado por conflito de agenda.'])
            ->assertRedirect(route('senai.dashboard', ['view' => 'teacher_requests']));

        $this->assertSame('rejeitado', $teacherRequest->fresh()->status);
        $this->assertDatabaseHas('teacher_request_messages', [
            'teacher_request_id' => $teacherRequest->id,
            'status' => 'rejeitado',
            'message' => 'Pedido recusado por conflito de agenda.',
            'email_sent' => false,
        ]);

        $this->withSession(['employee' => [
            'id' => $professor->Id_funcionario,
            'name' => $professor->Nome,
            'cargo' => 'Professor',
        ]])
            ->get(route('senai.dashboard', ['view' => 'teacher_requests']))
            ->assertOk()
            ->assertSee('Seu pedido foi rejeitado. Consulte o motivo na tabela Meus Pedidos.')
            ->assertSee('Motivo da rejeição')
            ->assertSee('Pedido recusado por conflito de agenda.');

        $this->withSession(['employee' => [
            'id' => $professor->Id_funcionario,
            'name' => $professor->Nome,
            'cargo' => 'Professor',
        ]])
            ->delete(route('stock.teacher-requests.notifications.dismiss', $teacherRequest))
            ->assertRedirect();

        $this->withSession(['employee' => [
            'id' => $professor->Id_funcionario,
            'name' => $professor->Nome,
            'cargo' => 'Professor',
        ]])
            ->get(route('senai.dashboard', ['view' => 'teacher_requests']))
            ->assertOk()
            ->assertDontSee('Seu pedido foi rejeitado. Consulte o motivo na tabela Meus Pedidos.')
            ->assertSee('Motivo da rejeição')
            ->assertSee('Pedido recusado por conflito de agenda.');
    }
}
