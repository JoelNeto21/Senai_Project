<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Cargo;
use App\Models\Curso;
use App\Models\Funcionario;
use App\Models\Turma;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SenaiStockViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withEmployeeSession();
    }

    public function test_application_uses_pt_br_user_facing_messages(): void
    {
        $this->assertSame('pt_BR', app()->getLocale());
        $this->assertSame('Você está conectado!', __("You're logged in!"));
        $this->assertSame(
            'As credenciais informadas não correspondem aos nossos registros.',
            trans('auth.failed'),
        );
        $this->assertSame(
            'O campo quantidade não pode ser enviado.',
            validator(['quantity' => 10], ['quantity' => 'prohibited'])->errors()->first('quantity'),
        );
    }

    /**
     * Test that library view renders successfully
     */
    public function test_library_view_renders(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('activeView', 'library');
    }

    /**
     * Test that library view has books data
     */
    public function test_library_view_shows_books_data(): void
    {
        // Arrange - Books are from config

        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('books');
        $response->assertViewHas('stockCriticalThreshold', 8);
    }

    /**
     * Test that library view shows critical stock threshold
     */
    public function test_library_view_critical_threshold_is_8(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('stockCriticalThreshold', 8);
    }

    /**
     * Test that library view has receive and withdraw modals
     */
    public function test_library_view_contains_modals(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Entrada', false);
        $response->assertSee('Saída', false);
    }

    /**
     * Test that receive modal has quantity field
     */
    public function test_receive_modal_has_quantity_field(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        // Check for quantity input
        $response->assertSee('type="number"', false);
    }

    /**
     * Test that withdraw modal has justification field
     */
    public function test_withdraw_modal_has_justification_field(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        // Check for textarea (justification)
        $response->assertSee('textarea', false);
    }

    /**
     * Test that library view contains low stock count
     */
    public function test_library_view_has_low_stock_count(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('lowStockCount');
    }

    /**
     * Test that library view contains total quantity
     */
    public function test_library_view_has_total_quantity(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('totalQuantity');
    }

    /**
     * Test that library view contains cargos data
     */
    public function test_library_view_has_cargos_data(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('cargos');

        // The base setup creates three cargos and application migrations may add operational roles.
        $cargos = $response->viewData('cargos');
        $this->assertGreaterThanOrEqual(3, $cargos->count());
    }

    /**
     * Test that library view contains funcionarios data
     */
    public function test_library_view_has_funcionarios_data(): void
    {
        // Arrange
        Funcionario::factory()->create();

        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('funcionarios');

        // Verify at least one funcionario exists
        $funcionarios = $response->viewData('funcionarios');
        $this->assertGreaterThan(0, $funcionarios->count());
    }

    /**
     * Test that library view contains turmas data
     */
    public function test_library_view_has_turmas_data(): void
    {
        // Arrange
        $curso = Curso::factory()->create();
        $turma = Turma::factory()->create(['curso_id' => $curso->id]);

        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('turmas');
    }

    /**
     * Test that library view contains employee data
     */
    public function test_library_view_has_employee_data(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('employee');
    }

    /**
     * Test that all valid views render successfully
     */
    public function test_all_valid_views_render(): void
    {
        $validViews = [
            'insights',
            'teacher_requests',
            'purchases',
            'library',
            'book_registration',
            'stock',
            'reports',
            'classes',
            'courses',
            'people',
        ];

        foreach ($validViews as $view) {
            $response = $this->get(route('senai.dashboard', ['view' => $view]));
            $this->assertEquals(200, $response->getStatusCode(), "View $view failed to render");
            $response->assertViewHas('activeView', $view);
        }
    }

    /**
     * Test that legacy view URLs redirect to consolidated pages
     */
    public function test_legacy_views_redirect_to_consolidated_pages(): void
    {
        $redirects = [
            'overview' => '/dashboard/reports',
            'dashboard' => '/dashboard/reports',
            'receive' => '/dashboard/stock?tab=entrada',
            'withdraw' => '/dashboard/stock?tab=saida',
            'movements' => '/dashboard/stock?tab=historico',
            'history' => '/dashboard/purchases?tab=historico',
            'alerts' => '/dashboard/insights',
            'suppliers' => '/dashboard/purchases',
        ];

        foreach ($redirects as $view => $expectedTarget) {
            $response = $this->get(route('senai.dashboard', ['view' => $view]));
            $response->assertRedirect($expectedTarget);
        }
    }

    /**
     * Test that invalid view returns 404
     */
    public function test_invalid_view_returns_404(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'invalid_view']));

        // Assert
        $response->assertStatus(404);
    }

    /**
     * Test that default view is insights for coordenador
     */
    public function test_default_view_is_insights_for_coordenador(): void
    {
        // Act
        $response = $this->get('/dashboard');

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('activeView', 'insights');
    }

    public function test_professor_is_forbidden_from_admin_views(): void
    {
        $this->withEmployeeSession('Professor');

        $response = $this->get(route('senai.dashboard', ['view' => 'people']));

        $response->assertForbidden();
    }

    public function test_professor_only_sees_requests_navigation(): void
    {
        $this->withEmployeeSession('Professor');

        $response = $this->get(route('senai.dashboard', ['view' => 'teacher_requests']));

        $response->assertStatus(200);
        $navigationItems = $response->viewData('navigationItems');
        $this->assertSame(['teacher_requests'], collect($navigationItems)->pluck('id')->all());
        $this->get(route('senai.dashboard', ['view' => 'library']))->assertForbidden();
    }

    public function test_coordenador_can_create_turma(): void
    {
        $curso = Curso::factory()->create(['nome_curso' => 'Elétrica']);

        $response = $this->post(route('stock.classes.store'), [
            'nome_turma' => 'ELE-1A',
            'curso_id' => $curso->id,
        ]);

        $response->assertRedirect(route('senai.dashboard', ['view' => 'classes']));
        $this->assertDatabaseHas('turmas', [
            'nome_turma' => 'ELE-1A',
            'curso_id' => $curso->id,
        ]);
    }

    public function test_navigation_uses_consolidated_tabs(): void
    {
        $response = $this->get(route('senai.dashboard', ['view' => 'insights']));
        $items = collect($response->viewData('navigationItems'));

        $this->assertSame('Pedidos', $items->firstWhere('id', 'teacher_requests')['label']);
        $this->assertTrue($items->contains('id', 'book_registration'));
        $this->assertTrue($items->contains('id', 'courses'));
        $this->assertFalse($items->contains('id', 'alerts'));
        $this->assertFalse($items->contains('id', 'suppliers'));
        $this->assertSame('Escola', $items->firstWhere('id', 'book_registration')['group']);
    }

    public function test_catalog_link_prefills_request_book(): void
    {
        $book = Book::factory()->create(['title' => 'Livro selecionado no catalogo']);

        $response = $this->get(route('senai.dashboard', ['view' => 'teacher_requests', 'book_id' => $book->id]));

        $response
            ->assertOk()
            ->assertViewHas('initialRequestBookId', $book->id)
            ->assertSee('filteredTurmas', false)
            ->assertSee('>Livro</label>', false)
            ->assertSee($book->title)
            ->assertSee('name="book_id" value="'.$book->id.'"', false)
            ->assertSee('Coordenador Senai')
            ->assertDontSee('name="teacher_email"', false)
            ->assertDontSee('Selecionado pelo catálogo');

        $this->assertMatchesRegularExpression(
            '/<details class="[^"]*mb-8"\s+open\s*>/',
            $response->getContent(),
        );
    }

    public function test_normal_request_form_is_closed_and_keeps_manual_fields(): void
    {
        $response = $this->get(route('senai.dashboard', ['view' => 'teacher_requests']));

        $response->assertOk()
            ->assertSee('Solicitante')
            ->assertSee('Coordenador Senai')
            ->assertDontSee('name="teacher_name"', false)
            ->assertDontSee('name="teacher_email"', false)
            ->assertSee('name="curso_id"', false)
            ->assertSee('Limpar campos');

        $this->assertDoesNotMatchRegularExpression(
            '/<details class="[^"]*mb-8"\s+open\s*>/',
            $response->getContent(),
        );
    }

    public function test_coordenador_can_create_employee_without_cpf(): void
    {
        $cargo = Cargo::firstOrCreate(['Nome_cargo' => 'Professor']);

        $this->post(route('funcionarios.store'), [
            'NIF' => 778899,
            'Nome' => 'Professor sem CPF',
            'Id_cargo_FK' => $cargo->Id_cargo,
        ])->assertRedirect(route('senai.dashboard', ['view' => 'people']));

        $this->assertDatabaseHas('funcionarios', [
            'NIF' => 778899,
            'Nome' => 'Professor sem CPF',
            'Cpf' => null,
        ]);
    }

    public function test_simplified_purchase_and_report_interfaces_render(): void
    {
        $this->get(route('senai.dashboard', ['view' => 'purchases']))
            ->assertOk()
            ->assertDontSee('Título Inédito')
            ->assertDontSee('Fornecedor / Editora')
            ->assertDontSee('carrinho')
            ->assertSee('Todos os cursos')
            ->assertSee('Registrar compra');

        $this->get(route('senai.dashboard', ['view' => 'reports']))
            ->assertOk()
            ->assertSee('Buscar pelo nome do livro')
            ->assertSee('Todas as áreas')
            ->assertSee('x-model="subject"', false)
            ->assertSee("sort('title')", false);
    }

    public function test_book_registration_saves_metadata_and_uses_report_style_filters(): void
    {
        Curso::firstOrCreate(['nome_curso' => 'Desenvolvimento de Sistemas']);

        $response = $this->post(route('stock.books.store-new'), [
            'title' => 'Livro com Metadados',
            'isbn' => '978-85-12345-67-8',
            'subject' => 'Desenvolvimento de Sistemas',
            'quantity' => 4,
            'minimum_stock' => 2,
            'publication_year' => 2026,
            'pages' => 312,
        ]);

        $response->assertRedirect(route('senai.dashboard', ['view' => 'book_registration']));
        $this->assertDatabaseHas('books', [
            'title' => 'Livro com Metadados',
            'publication_year' => 2026,
            'pages' => 312,
        ]);

        $this->get(route('senai.dashboard', ['view' => 'book_registration']))
            ->assertOk()
            ->assertSee('Ano de publicação')
            ->assertSee('Páginas')
            ->assertSee('Buscar pelo nome do livro')
            ->assertSee('Todas as áreas')
            ->assertSee('x-model="subject"', false);

        $this->get(route('senai.dashboard', ['view' => 'library']))
            ->assertOk()
            ->assertDontSee('<dt class="text-gray-400">Editora</dt>', false);
    }

    public function test_tables_are_enhanced_and_school_pages_use_the_new_layout(): void
    {
        $tableScript = file_get_contents(resource_path('js/app.js'));
        $dashboardView = file_get_contents(resource_path('views/senai-stock/index.blade.php'));

        $this->assertStringContainsString('function enhanceTables()', $tableScript);
        $this->assertStringContainsString('table.dataset.tableSearchPlaceholder', $tableScript);
        $this->assertStringContainsString('table.dataset.tableFilterColumn', $tableScript);
        $this->assertStringNotContainsString('Todas as colunas', $tableScript);
        $this->assertStringContainsString('Ocultar tabela', $tableScript);
        $this->assertStringContainsString('clearForm(form)', $tableScript);
        $this->assertStringContainsString('data-table-skip', $dashboardView);
        $this->assertStringContainsString('data-table-filter-label="Todos os status"', $dashboardView);
        $this->assertStringContainsString('Motivo da rejeição', $dashboardView);
        $this->assertStringContainsString('Busca rápida do sistema', file_get_contents(resource_path('views/layouts/app.blade.php')));

        foreach (['book_registration', 'courses', 'classes', 'people'] as $view) {
            $this->get(route('senai.dashboard', ['view' => $view]))
                ->assertOk()
                ->assertSee('Escola ·');
        }
    }

    public function test_coordinator_can_register_single_book_restock_purchase(): void
    {
        $book = Book::factory()->create([
            'title' => 'Livro para reposição',
            'subject' => 'Desenvolvimento de Sistemas',
        ]);

        $response = $this->post(route('stock.purchases.generate'), [
            'book_id' => $book->id,
            'quantity' => 12,
            'justification' => 'Reposição avulsa.',
        ]);

        $response->assertRedirect(route('senai.dashboard', [
            'view' => 'purchases',
            'tab' => 'historico',
        ]));
        $this->assertDatabaseHas('purchase_order_items', [
            'book_id' => $book->id,
            'title' => 'Livro para reposição',
            'quantity' => 12,
            'justification' => 'Reposição avulsa.',
        ]);

        $this->get(route('senai.dashboard', ['view' => 'purchases', 'tab' => 'historico']))
            ->assertOk()
            ->assertSee('Livro para reposição');
    }

    public function test_coordenador_can_create_course(): void
    {
        $this->post(route('stock.courses.store'), [
            'nome_curso' => 'Administração',
        ])->assertRedirect(route('senai.dashboard', ['view' => 'courses']));

        $this->assertDatabaseHas('cursos', ['nome_curso' => 'Administração']);

        $this->get(route('senai.dashboard', ['view' => 'courses']))
            ->assertOk()
            ->assertSee('Curso')
            ->assertSee('Administração');
    }

    public function test_course_creation_rejects_accent_and_typo_variants(): void
    {
        Curso::factory()->create(['nome_curso' => 'Administração']);

        foreach (['Administracao', 'Admnisitração'] as $variant) {
            $this->post(route('stock.courses.store'), [
                'nome_curso' => $variant,
            ])->assertSessionHasErrors('nome_curso');
        }

        $this->assertSame(1, Curso::query()->count());
    }

    public function test_course_creation_allows_distinct_short_names(): void
    {
        Curso::factory()->create(['nome_curso' => 'Curso 1']);

        $this->post(route('stock.courses.store'), [
            'nome_curso' => 'Curso 2',
        ])->assertRedirect(route('senai.dashboard', ['view' => 'courses']));

        $this->assertDatabaseHas('cursos', ['nome_curso' => 'Curso 2']);
    }

    public function test_stock_withdraw_link_opens_withdraw_tab(): void
    {
        $this->get(route('senai.dashboard', ['view' => 'stock', 'tab' => 'saida']))
            ->assertOk()
            ->assertViewHas('activeTab', 'saida')
            ->assertSee('x-data="preservedTabs', false);
    }

    public function test_teacher_request_due_date_cannot_be_in_the_past(): void
    {
        $curso = Curso::factory()->create(['nome_curso' => 'Desenvolvimento']);
        $turma = Turma::factory()->create(['curso_id' => $curso->id]);
        $book = Book::factory()->create(['subject' => $curso->nome_curso]);
        $pastDate = now()->subDay()->toDateString();

        $response = $this->from(route('senai.dashboard', ['view' => 'teacher_requests']))
            ->post(route('stock.teacher-requests.store'), [
                'turma_id' => $turma->id,
                'curso_id' => $curso->id,
                'book_id' => $book->id,
                'quantity' => 3,
                'due_date' => $pastDate,
                'notes' => 'Manter estas informações',
            ]);

        $response
            ->assertRedirect(route('senai.dashboard', ['view' => 'teacher_requests']))
            ->assertSessionHasErrors([
                'due_date' => 'O prazo desejado deve ser igual ou posterior à data de hoje.',
            ])
            ->assertSessionHasInput('turma_id', $turma->id)
            ->assertSessionHasInput('book_id', $book->id)
            ->assertSessionHasInput('quantity', 3)
            ->assertSessionHasInput('notes', 'Manter estas informações');

        $renderedForm = $this->get(route('senai.dashboard', ['view' => 'teacher_requests']));
        $renderedForm
            ->assertOk()
            ->assertSee('value="'.$pastDate.'"', false)
            ->assertSee('value="3"', false)
            ->assertSee('Manter estas informações');

        $this->assertMatchesRegularExpression(
            '/<details[^>]*\sopen(?:\s|>)/',
            $renderedForm->getContent(),
        );
    }

    public function test_coordenador_can_update_and_delete_school_records_and_books(): void
    {
        $curso = Curso::factory()->create(['nome_curso' => 'Curso antigo']);
        $turma = Turma::factory()->create(['curso_id' => $curso->id, 'nome_turma' => 'Turma antiga']);
        $book = Book::factory()->create(['subject' => $curso->nome_curso]);

        $this->put(route('stock.courses.update', $curso), ['nome_curso' => 'Curso novo'])
            ->assertRedirect(route('senai.dashboard', ['view' => 'courses']));
        $this->assertDatabaseHas('books', ['id' => $book->id, 'subject' => 'Curso novo']);

        $this->put(route('stock.classes.update', $turma), [
            'nome_turma' => 'Turma nova',
            'curso_id' => $curso->id,
        ])->assertRedirect(route('senai.dashboard', ['view' => 'classes']));

        $this->put(route('stock.books.update', $book), [
            'title' => 'Livro atualizado',
            'subject' => 'Curso novo',
            'minimum_stock' => 5,
            'status' => 'ativo',
        ])->assertRedirect(route('senai.dashboard', ['view' => 'book_registration']));

        $this->delete(route('stock.classes.destroy', $turma))
            ->assertRedirect(route('senai.dashboard', ['view' => 'classes']));
        $this->delete(route('stock.books.destroy', $book))
            ->assertRedirect(route('senai.dashboard', ['view' => 'book_registration']));
        $this->delete(route('stock.courses.destroy', $curso))
            ->assertRedirect(route('senai.dashboard', ['view' => 'courses']));

        $this->assertDatabaseMissing('turmas', ['id' => $turma->id]);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('cursos', ['id' => $curso->id]);
    }

    public function test_people_page_integrates_employee_management(): void
    {
        $funcionario = Funcionario::factory()->create();

        $this->get(route('senai.dashboard', ['view' => 'people']))
            ->assertOk()
            ->assertSee('action="'.route('funcionarios.update', $funcionario).'"', false)
            ->assertSee('action="'.route('funcionarios.destroy', $funcionario).'"', false)
            ->assertDontSee('Gerenciar funcionários');
    }

    public function test_book_registration_uses_course_selects(): void
    {
        Curso::factory()->create(['nome_curso' => 'Mecatrônica']);

        $this->get(route('senai.dashboard', ['view' => 'book_registration']))
            ->assertOk()
            ->assertDontSee('Buscar curso / área')
            ->assertSee('<select name="subject"', false)
            ->assertDontSee('list="course-options"', false)
            ->assertDontSee('name="location"', false)
            ->assertSee('name="image"', false)
            ->assertSee('bookEditTable(', false)
            ->assertSee('Buscar pelo nome do livro')
            ->assertSee('Todas as áreas')
            ->assertSee('Mecatrônica');
    }

    public function test_book_registration_rejects_unregistered_course(): void
    {
        $this->post(route('stock.books.store-new'), [
            'title' => 'Livro com curso inválido',
            'subject' => 'Curso digitado manualmente',
            'quantity' => 10,
        ])->assertSessionHasErrors('subject');

        $this->assertDatabaseMissing('books', [
            'title' => 'Livro com curso inválido',
        ]);
    }

    public function test_books_api_rejects_unregistered_courses(): void
    {
        $book = Book::factory()->create();

        $this->postJson('/api/books', [
            'title' => 'Livro com curso inválido via API',
            'isbn' => '978-85-99999-01-1',
            'subject' => 'Curso não cadastrado',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('subject');

        $this->putJson("/api/books/{$book->id}", [
            'subject' => 'Outro curso não cadastrado',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('subject');
    }

    public function test_books_api_accepts_registered_courses(): void
    {
        $firstCourse = Curso::factory()->create(['nome_curso' => 'Administração']);
        $secondCourse = Curso::factory()->create(['nome_curso' => 'Eletroeletrônica']);

        $response = $this->postJson('/api/books', [
            'title' => 'Livro cadastrado via API',
            'isbn' => '978-85-99999-02-8',
            'subject' => $firstCourse->nome_curso,
        ])->assertCreated();

        $bookId = $response->json('id');

        $this->putJson("/api/books/{$bookId}", [
            'subject' => $secondCourse->nome_curso,
        ])->assertOk();

        $this->assertDatabaseHas('books', [
            'id' => $bookId,
            'subject' => $secondCourse->nome_curso,
        ]);
    }

    public function test_books_api_prohibits_direct_quantity_updates(): void
    {
        $book = Book::factory()->create(['quantity' => 10]);

        $this->putJson("/api/books/{$book->id}", [
            'quantity' => 99,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'quantity' => 10,
        ]);
        $this->assertDatabaseCount('movements', 0);
    }

    public function test_book_cover_upload_is_saved_and_shown_in_catalog(): void
    {
        Storage::fake('public');
        Curso::factory()->create(['nome_curso' => 'Desenvolvimento']);

        $this->post(route('stock.books.store-new'), [
            'title' => 'Livro com capa',
            'subject' => 'Desenvolvimento',
            'quantity' => 10,
            'image' => UploadedFile::fake()->createWithContent(
                'capa.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            ),
        ])->assertRedirect(route('senai.dashboard', ['view' => 'book_registration']));

        $book = Book::where('title', 'Livro com capa')->firstOrFail();
        Storage::disk('public')->assertExists($book->image_path);

        $this->get(route('senai.dashboard', ['view' => 'library']))
            ->assertOk()
            ->assertSee(Storage::url($book->image_path), false);
    }

    public function test_professor_cannot_create_turma(): void
    {
        $this->withEmployeeSession('Professor');

        $response = $this->post(route('stock.classes.store'), [
            'nome_turma' => 'ELE-1A',
            'nome_curso' => 'Elétrica',
        ]);

        $response->assertForbidden();
    }

    /**
     * Test that library view contains books grid structure
     */
    public function test_library_view_books_grid_structure(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        // Check for grid container (3-column layout)
        $response->assertSee('grid', false);
    }

    /**
     * Test that funcionarios are related to cargos
     */
    public function test_funcionarios_with_cargo_relationship(): void
    {
        // Arrange
        $cargo = Cargo::first();
        $funcionario = Funcionario::factory()->create(['Id_cargo_FK' => $cargo->Id_cargo]);

        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $funcionarios = $response->viewData('funcionarios');
        $this->assertTrue($funcionarios->count() >= 1);
    }

    /**
     * Test that turmas are related to cursos
     */
    public function test_turmas_with_curso_relationship(): void
    {
        // Arrange
        $curso = Curso::factory()->create();
        $turma = Turma::factory()->create(['curso_id' => $curso->id]);

        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $turmas = $response->viewData('turmas');

        // Check if turmas have curso relationship loaded
        foreach ($turmas as $t) {
            $this->assertIsNotNull($t->curso);
        }
    }

    /**
     * Test that books collection is properly formatted
     */
    public function test_books_collection_format(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $books = $response->viewData('books');

        // Books should be a collection
        $this->assertIsObject($books);
    }

    /**
     * Test that library view has necessary navigation items
     */
    public function test_library_view_has_navigation_items(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('navigationItems');
    }

    /**
     * Test that library view contains receive button/trigger
     */
    public function test_library_view_receive_button_exists(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        // Check for button text or trigger
        $response->assertSee('Entrada', false);
    }

    /**
     * Test that library view contains withdraw button/trigger
     */
    public function test_library_view_withdraw_button_exists(): void
    {
        // Act
        $response = $this->get(route('senai.dashboard', ['view' => 'library']));

        // Assert
        $response->assertStatus(200);
        // Check for button text or trigger
        $response->assertSee('Saída', false);
    }
}
