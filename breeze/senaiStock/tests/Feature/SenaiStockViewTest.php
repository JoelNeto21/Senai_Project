<?php

namespace Tests\Feature;

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

        // Verify cargos count (3 from setUp)
        $cargos = $response->viewData('cargos');
        $this->assertCount(3, $cargos);
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
        $book = \App\Models\Book::factory()->create(['title' => 'Livro selecionado no catalogo']);

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
            ->assertDontSee('Fornecedor / Editora');

        $this->get(route('senai.dashboard', ['view' => 'reports']))
            ->assertOk()
            ->assertSee('Buscar pelo nome do livro')
            ->assertSee('Todas as áreas')
            ->assertSee('x-model="subject"', false)
            ->assertSee("sort('title')", false);
    }

    public function test_coordenador_can_create_course(): void
    {
        $this->post(route('stock.courses.store'), [
            'nome_curso' => 'Administração',
        ])->assertRedirect(route('senai.dashboard', ['view' => 'courses']));

        $this->assertDatabaseHas('cursos', ['nome_curso' => 'Administração']);

        $this->get(route('senai.dashboard', ['view' => 'courses']))
            ->assertOk()
            ->assertSee('Cadastrar Curso')
            ->assertSee('Administração');
    }

    public function test_book_registration_uses_single_searchable_course_field(): void
    {
        Curso::factory()->create(['nome_curso' => 'Mecatrônica']);

        $this->get(route('senai.dashboard', ['view' => 'book_registration']))
            ->assertOk()
            ->assertDontSee('Buscar curso / área')
            ->assertSee('list="course-options"', false)
            ->assertDontSee('name="location"', false)
            ->assertSee('name="image"', false)
            ->assertSee('Mecatrônica');
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

        $book = \App\Models\Book::where('title', 'Livro com capa')->firstOrFail();
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
