<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Cargo;
use App\Models\Curso;
use App\Models\Funcionario;
use App\Models\PurchaseOrder;
use App\Models\TeacherRequest;
use App\Models\Turma;
use App\Support\EmployeeRole;
use Tests\TestCase;

class RoleStockWorkflowTest extends TestCase
{
    public function test_role_view_boundaries_match_the_operational_responsibilities(): void
    {
        $this->assertSame(
            ['insights', 'teacher_requests', 'library', 'book_registration', 'stock', 'reports', 'purchases', 'classes', 'courses', 'people'],
            EmployeeRole::allowedViews(EmployeeRole::COORDENADOR)
        );
        $this->assertSame(['teacher_requests'], EmployeeRole::allowedViews(EmployeeRole::PROFESSOR));
        $this->assertTrue(EmployeeRole::can(EmployeeRole::COORDENADOR, 'stock.receive'));
        $this->assertTrue(EmployeeRole::can(EmployeeRole::COORDENADOR, 'stock.withdraw'));
        $this->assertTrue(EmployeeRole::can(EmployeeRole::COORDENADOR, 'purchases.deliver'));
        $this->assertNotContains(false, EmployeeRole::permissions(EmployeeRole::COORDENADOR));
    }

    public function test_oversized_professor_request_becomes_purchase_then_coordenador_receives_and_fulfills_it(): void
    {
        $professor = $this->employee('Professor', 700001, 'Prof. Maria');
        $coordenador = $this->employee('Coordenador', 700002, 'Coordenador Senai');
        $book = Book::factory()->create(['quantity' => 2, 'subject' => 'Desenvolvimento de Sistemas']);
        [$curso, $turma] = $this->courseAndClass();

        $this->withEmployee($professor)->post(route('stock.teacher-requests.store'), [
            'turma_id' => $turma->id,
            'curso_id' => $curso->id,
            'book_id' => $book->id,
            'quantity' => 5,
        ])->assertRedirect(route('senai.dashboard', ['view' => 'teacher_requests']));

        $teacherRequest = TeacherRequest::firstOrFail();
        $purchaseOrder = PurchaseOrder::with('items')->firstOrFail();

        $this->assertSame('compra', $teacherRequest->status);
        $this->assertSame($professor->Id_funcionario, $teacherRequest->requested_by_funcionario_id);
        $this->assertSame('pendente_aprovacao', $purchaseOrder->status);
        $this->assertSame(3, $purchaseOrder->items->first()->quantity);
        $this->assertSame($teacherRequest->id, $purchaseOrder->items->first()->teacher_request_id);

        $this->withEmployee($coordenador)
            ->post(route('stock.purchases.approve', $purchaseOrder))
            ->assertRedirect();

        $this->assertSame('aprovado', $purchaseOrder->fresh()->status);
        $this->assertSame('compra_aprovada', $teacherRequest->fresh()->status);

        $purchaseItem = $purchaseOrder->items->first();
        $this->withEmployee($coordenador)
            ->post(route('stock.purchases.items.receive', [$purchaseOrder, $purchaseItem]), ['quantity' => 2])
            ->assertRedirect();

        $this->assertSame(4, $book->fresh()->quantity);
        $this->assertSame('recebimento_parcial', $purchaseOrder->fresh()->status);
        $this->assertSame(2, $purchaseItem->fresh()->received_quantity);
        $this->assertSame('compra_aprovada', $teacherRequest->fresh()->status);

        $this->withEmployee($coordenador)
            ->post(route('stock.purchases.items.receive', [$purchaseOrder, $purchaseItem]), ['quantity' => 1])
            ->assertRedirect();

        $this->assertSame(5, $book->fresh()->quantity);
        $this->assertSame('aprovado', $teacherRequest->fresh()->status);
        $this->assertDatabaseHas('movements', [
            'type' => 'entrada',
            'book_id' => $book->id,
            'quantity' => 2,
            'funcionario_id' => $coordenador->Id_funcionario,
        ]);
        $this->assertDatabaseHas('movements', [
            'type' => 'entrada',
            'book_id' => $book->id,
            'quantity' => 1,
            'funcionario_id' => $coordenador->Id_funcionario,
        ]);

        $this->withEmployee($coordenador)
            ->post(route('stock.teacher-requests.fulfill', $teacherRequest), ['quantity' => 2])
            ->assertRedirect();

        $this->assertSame(3, $book->fresh()->quantity);
        $this->assertSame('separado_parcial', $teacherRequest->fresh()->status);
        $this->assertSame(2, $teacherRequest->fresh()->fulfilled_quantity);

        $this->withEmployee($coordenador)
            ->post(route('stock.teacher-requests.fulfill', $teacherRequest), ['quantity' => 3])
            ->assertRedirect();

        $this->assertSame(0, $book->fresh()->quantity);
        $this->assertSame('atendido', $teacherRequest->fresh()->status);
        $this->assertDatabaseHas('movements', [
            'type' => 'saida',
            'book_id' => $book->id,
            'quantity' => 2,
            'funcionario_id' => $coordenador->Id_funcionario,
        ]);
        $this->assertDatabaseHas('movements', [
            'type' => 'saida',
            'book_id' => $book->id,
            'quantity' => 3,
            'funcionario_id' => $coordenador->Id_funcionario,
        ]);
    }

    public function test_coordenador_can_perform_all_stock_transitions(): void
    {
        $coordenador = $this->employee('Coordenador', 700011, 'Coordenador Senai');
        $book = Book::factory()->create(['quantity' => 1]);
        $order = PurchaseOrder::create([
            'order_number' => 'PED-ROLE-001',
            'status' => 'pendente_aprovacao',
            'generated_at' => now(),
        ]);

        $order->update(['status' => 'aprovado']);

        $this->withEmployee($coordenador)
            ->post(route('stock.books.receive', $book), ['quantity' => 1])
            ->assertRedirect();

        $this->assertSame(2, $book->fresh()->quantity);

        $teacherRequest = TeacherRequest::create([
            'protocol' => 'SS-COORD-001',
            'teacher_name' => 'Prof. Coordenado',
            'class_name' => 'DS-1A',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 1,
            'status' => 'pendente',
        ]);

        $this->withEmployee($coordenador)
            ->post(route('stock.teacher-requests.approve', $teacherRequest))
            ->assertRedirect();

        $this->withEmployee($coordenador)
            ->post(route('stock.teacher-requests.fulfill', $teacherRequest), ['quantity' => 1])
            ->assertRedirect();

        $this->assertSame('atendido', $teacherRequest->fresh()->status);
    }

    public function test_coordenador_can_create_a_request_and_saida_lists_linked_outstanding_requests(): void
    {
        $coordenador = $this->employee('Coordenador', 700031, 'Coordenador Senai');
        $book = Book::factory()->create(['quantity' => 2, 'subject' => 'Desenvolvimento de Sistemas']);
        [$curso, $turma] = $this->courseAndClass();

        $this->withEmployee($coordenador)
            ->post(route('stock.teacher-requests.store'), [
                'teacher_name' => 'Prof. Carlos',
                'turma_id' => $turma->id,
                'curso_id' => $curso->id,
                'book_id' => $book->id,
                'quantity' => 5,
            ])
            ->assertRedirect(route('senai.dashboard', ['view' => 'teacher_requests']));

        $teacherRequest = TeacherRequest::firstOrFail();
        $this->assertSame('compra', $teacherRequest->status);
        $this->assertSame($coordenador->Nome, $teacherRequest->teacher_name);
        $this->assertNull($teacherRequest->teacher_email);

        $this->withEmployee($coordenador)
            ->get(route('senai.dashboard', ['view' => 'stock', 'tab' => 'saida']))
            ->assertOk()
            ->assertDontSee(route('stock.teacher-requests.fulfill', $teacherRequest), false);

        $this->withEmployee($coordenador)
            ->post(route('stock.teacher-requests.fulfill', $teacherRequest), ['quantity' => 2])
            ->assertSessionHasErrors('teacher_request');

        $this->assertSame('compra', $teacherRequest->fresh()->status);
        $this->assertSame(2, $book->fresh()->quantity);
    }

    public function test_coordenador_request_with_enough_stock_is_approved_automatically(): void
    {
        $coordenador = $this->employee('Coordenador', 700035, 'Coordenador Senai');
        $book = Book::factory()->create(['quantity' => 10, 'subject' => 'Desenvolvimento de Sistemas']);
        [$curso, $turma] = $this->courseAndClass();

        $this->withEmployee($coordenador)
            ->post(route('stock.teacher-requests.store'), [
                'teacher_name' => $coordenador->Nome,
                'turma_id' => $turma->id,
                'curso_id' => $curso->id,
                'book_id' => $book->id,
                'quantity' => 3,
            ])
            ->assertRedirect(route('senai.dashboard', ['view' => 'teacher_requests']));

        $teacherRequest = TeacherRequest::firstOrFail();
        $this->assertSame('aprovado', $teacherRequest->status);
        $this->assertSame($coordenador->Id_funcionario, $teacherRequest->requested_by_funcionario_id);
    }

    public function test_approved_request_remains_visible_with_approved_status(): void
    {
        $coordenador = $this->employee('Coordenador', 700041, 'Coordenador Senai');
        $book = Book::factory()->create(['quantity' => 10]);
        $teacherRequest = TeacherRequest::create([
            'protocol' => 'SS-APPROVED-001',
            'teacher_name' => 'Prof. Ana',
            'class_name' => 'DS-1A',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 2,
            'status' => 'pendente',
        ]);

        $this->withEmployee($coordenador)
            ->post(route('stock.teacher-requests.approve', $teacherRequest))
            ->assertRedirect();

        $this->assertSame('aprovado', $teacherRequest->fresh()->status);

        $this->withEmployee($coordenador)
            ->get(route('senai.dashboard', ['view' => 'teacher_requests']))
            ->assertOk()
            ->assertSee('Aprovado')
            ->assertSee($teacherRequest->protocol);
    }

    public function test_approved_purchase_becomes_ready_to_separate_when_stock_is_already_enough(): void
    {
        $coordenador = $this->employee('Coordenador', 700045, 'Coordenador Senai');
        $book = Book::factory()->create(['quantity' => 5]);
        $teacherRequest = TeacherRequest::create([
            'protocol' => 'SS-BUY-READY-001',
            'teacher_name' => 'Prof. Ana',
            'class_name' => 'DS-1A',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 3,
            'status' => 'compra',
        ]);
        $order = PurchaseOrder::create([
            'order_number' => 'PED-BUY-READY-001',
            'status' => 'pendente_aprovacao',
            'generated_at' => now(),
        ]);
        $order->items()->create([
            'teacher_request_id' => $teacherRequest->id,
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 1,
            'type' => 'restock',
        ]);

        $this->withEmployee($coordenador)
            ->post(route('stock.purchases.approve', $order))
            ->assertRedirect();

        $this->assertSame('aprovado', $teacherRequest->fresh()->status);
    }

    public function test_coordenador_can_approve_or_reject_buy_request_inline_and_professor_sees_update(): void
    {
        $professor = $this->employee('Professor', 700051, 'Prof. Maria');
        $coordenador = $this->employee('Coordenador', 700052, 'Coordenador Senai');
        $book = Book::factory()->create(['quantity' => 0, 'subject' => 'Desenvolvimento de Sistemas']);
        [$curso, $turma] = $this->courseAndClass();

        $this->withEmployee($professor)->post(route('stock.teacher-requests.store'), [
            'turma_id' => $turma->id,
            'curso_id' => $curso->id,
            'book_id' => $book->id,
            'quantity' => 3,
        ])->assertRedirect();

        $teacherRequest = TeacherRequest::firstOrFail();
        $order = PurchaseOrder::firstOrFail();

        $this->withEmployee($coordenador)
            ->get(route('senai.dashboard', ['view' => 'teacher_requests']))
            ->assertOk()
            ->assertSee('Aprovar compra agora')
            ->assertSee('Rejeitar com observação');

        $this->withEmployee($coordenador)
            ->post(route('stock.purchases.approve', $order))
            ->assertRedirect();

        $this->withEmployee($professor)
            ->get(route('senai.dashboard', ['view' => 'teacher_requests']))
            ->assertOk()
            ->assertSee('Atualizações dos seus pedidos')
            ->assertSee('A compra necessária para o seu pedido foi aprovada.');

        $observation = 'Compra rejeitada porque o material será substituído na próxima turma.';
        $this->withEmployee($coordenador)
            ->post(route('stock.teacher-requests.reject', $teacherRequest), ['message' => $observation])
            ->assertRedirect();

        $this->assertSame('rejeitado', $teacherRequest->fresh()->status);
        $this->assertSame('rejeitado', $order->fresh()->status);

        $this->withEmployee($professor)
            ->get(route('senai.dashboard', ['view' => 'teacher_requests']))
            ->assertOk()
            ->assertSee($observation);
    }

    public function test_request_rejects_book_from_another_course(): void
    {
        $professor = $this->employee('Professor', 700042, 'Prof. Maria');
        [$curso, $turma] = $this->courseAndClass();
        $book = Book::factory()->create(['subject' => 'Administracao']);

        $this->withEmployee($professor)
            ->post(route('stock.teacher-requests.store'), [
                'turma_id' => $turma->id,
                'curso_id' => $curso->id,
                'book_id' => $book->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('book_id');

        $this->assertDatabaseCount('teacher_requests', 0);
    }

    public function test_professor_sees_only_own_requests_and_cannot_access_catalog(): void
    {
        $professor = $this->employee('Professor', 700021, 'Prof. Maria');
        $otherProfessor = $this->employee('Professor', 700022, 'Prof. Joao');
        $book = Book::factory()->create(['quantity' => 20]);

        TeacherRequest::create([
            'protocol' => 'SS-OWN-001',
            'requested_by_funcionario_id' => $professor->Id_funcionario,
            'teacher_name' => $professor->Nome,
            'class_name' => 'DS-1A',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 2,
            'status' => 'pendente',
        ]);
        TeacherRequest::create([
            'protocol' => 'SS-OTHER-001',
            'requested_by_funcionario_id' => $otherProfessor->Id_funcionario,
            'teacher_name' => $otherProfessor->Nome,
            'class_name' => 'DS-1B',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 2,
            'status' => 'pendente',
        ]);

        $this->withEmployee($professor)
            ->get(route('senai.dashboard', ['view' => 'teacher_requests']))
            ->assertOk()
            ->assertSee($book->title)
            ->assertDontSee($otherProfessor->Nome);

        $this->withEmployee($professor)
            ->get(route('senai.dashboard', ['view' => 'library']))
            ->assertForbidden();
    }

    public function test_purchase_approvals_and_history_show_detailed_order_information(): void
    {
        $coordenador = $this->employee('Coordenador', 700061, 'Coordenador Compras');
        $professor = $this->employee('Professor', 700062, 'Prof. Detalhes');
        $book = Book::factory()->create([
            'title' => 'Livro Detalhado de Compras',
            'isbn' => '978-85-00000-61-0',
            'subject' => 'Desenvolvimento de Sistemas',
        ]);
        $teacherRequest = TeacherRequest::create([
            'protocol' => 'SS-DETAIL-001',
            'requested_by_funcionario_id' => $professor->Id_funcionario,
            'teacher_name' => $professor->Nome,
            'class_name' => 'DS-DETAIL',
            'course_name' => 'Desenvolvimento de Sistemas',
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 8,
            'status' => 'compra',
            'due_date' => now()->addWeek(),
        ]);
        $order = PurchaseOrder::create([
            'order_number' => 'PED-DETAIL-001',
            'requested_by_funcionario_id' => $coordenador->Id_funcionario,
            'status' => 'pendente_aprovacao',
            'generated_at' => now(),
            'notes' => 'Observacao detalhada da compra.',
        ]);
        $order->items()->create([
            'teacher_request_id' => $teacherRequest->id,
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => 6,
            'received_quantity' => 2,
            'type' => 'restock',
            'justification' => 'Quantidade faltante para a turma.',
        ]);

        $this->withEmployee($coordenador)
            ->get(route('senai.dashboard', ['view' => 'purchases', 'tab' => 'aprovacoes']))
            ->assertOk()
            ->assertSee('Livro Detalhado de Compras')
            ->assertSee('6 un.')
            ->assertSee('SS-DETAIL-001')
            ->assertSee('Prof. Detalhes')
            ->assertSee('Observacao detalhada da compra.');

        $this->withEmployee($coordenador)
            ->get(route('senai.dashboard', ['view' => 'purchases', 'tab' => 'historico']))
            ->assertOk()
            ->assertSee('Clique para ver os detalhes')
            ->assertSee('Quantidade solicitada')
            ->assertSee('Quantidade recebida')
            ->assertSee('Quantidade pendente')
            ->assertSee('978-85-00000-61-0');
    }

    private function employee(string $role, int $nif, string $name): Funcionario
    {
        $cargo = Cargo::firstOrCreate(['Nome_cargo' => $role]);

        return Funcionario::factory()->create([
            'NIF' => $nif,
            'Nome' => $name,
            'Id_cargo_FK' => $cargo->Id_cargo,
        ]);
    }

    private function withEmployee(Funcionario $employee): static
    {
        return $this->withSession([
            'employee' => [
                'id' => $employee->Id_funcionario,
                'name' => $employee->Nome,
                'cargo' => $employee->cargo->Nome_cargo,
            ],
        ]);
    }

    private function courseAndClass(): array
    {
        $curso = Curso::create(['nome_curso' => 'Desenvolvimento de Sistemas']);
        $turma = Turma::create(['nome_turma' => 'DS-2A', 'curso_id' => $curso->id]);

        return [$curso, $turma];
    }
}
