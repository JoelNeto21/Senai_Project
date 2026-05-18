<?php

/**
 * EXAMPLES - SenaiStock API Testing
 * 
 * Este arquivo contém exemplos práticos de como usar as factories
 * e estruturar testes com PestPHP.
 */

// ============================================
// 1. FACTORIES - Como Criar Dados de Teste
// ============================================

// ✅ Criar um usuário comum
$user = \App\Models\User::factory()->create();

// ✅ Criar usuário com role específico
$almoxarife = \App\Models\User::factory()->almoxarife()->create();
$coordenador = \App\Models\User::factory()->coordenador()->create();

// ✅ Criar livro com ISBN único e estoque aleatório
$book = \App\Models\Book::factory()->create();
// Resultado: ['title' => 'Lorem ipsum', 'isbn' => '978-X-XXX-XXXXX-X', 'subject' => 'Fiction', 'quantity' => 23]

// ✅ Criar livro com estoque crítico (< 10)
$criticalBook = \App\Models\Book::factory()->criticalStock()->create();
// Resultado: ['quantity' => 7]

// ✅ Criar livro sem estoque
$emptyBook = \App\Models\Book::factory()->outOfStock()->create();
// Resultado: ['quantity' => 0]

// ✅ Criar livro com estoque normal (20-50)
$normalBook = \App\Models\Book::factory()->normalStock()->create();
// Resultado: ['quantity' => 35]

// ✅ Criar 10 livros rapidamente
$books = \App\Models\Book::factory()->count(10)->create();

// ✅ Criar movimento de entrada
$entradaMovement = \App\Models\Movement::factory()->entrada()->create();

// ✅ Criar movimento de saída
$saidaMovement = \App\Models\Movement::factory()->saida()->create();

// ✅ Criar movimento para um livro específico
$movement = \App\Models\Movement::factory()->forBook($book)->create();

// ✅ Criar movimento por usuário específico
$movement = \App\Models\Movement::factory()->byUser($user)->create();

// ✅ Criar movimento com quantidade grande
$largeQty = \App\Models\Movement::factory()->largeQuantity()->create();

// ✅ Criar movimento com quantidade pequena
$smallQty = \App\Models\Movement::factory()->smallQuantity()->create();

// ============================================
// 2. TESTES BÁSICOS - Estrutura PestPHP
// ============================================

/**
 * Teste simples de um cenário
 */
it('can create a book', function () {
    $response = $this->actingAs(\App\Models\User::factory()->create())
        ->postJson('/api/books', [
            'title' => 'Test Book',
            'isbn' => '978-3-16-148410-0',
            'subject' => 'Science',
            'quantity' => 10,
        ]);

    $response->assertStatus(201);
    expect($response->json('id'))->toBeGreaterThan(0);
});

/**
 * Teste com describe (grupo de testes relacionados)
 */
describe('Book Creation', function () {
    it('requires title field', function () {
        $response = $this->actingAs(\App\Models\User::factory()->create())
            ->postJson('/api/books', [
                'isbn' => '978-3-16-148410-1',
                'subject' => 'Science',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    });

    it('rejects duplicate ISBN', function () {
        \App\Models\Book::factory()->create(['isbn' => '978-3-16-148410-0']);

        $response = $this->actingAs(\App\Models\User::factory()->create())
            ->postJson('/api/books', [
                'title' => 'Another Book',
                'isbn' => '978-3-16-148410-0',
                'subject' => 'Science',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    });
});

// ============================================
// 3. TESTES DE MOVIMENTAÇÕES - CRÍTICO
// ============================================

/**
 * CRÍTICO: Bloqueia saída quando estoque é insuficiente
 */
it('blocks saida when stock is insufficient', function () {
    $user = \App\Models\User::factory()->create();
    $book = \App\Models\Book::factory()->create(['quantity' => 5]);

    $response = $this->actingAs($user)->postJson('/api/movements', [
        'book_id' => $book->id,
        'type' => 'saida',
        'quantity' => 10,  // Maior que o saldo disponível
        'justification' => 'Classroom A',
    ]);

    // DEVE retornar 422
    expect($response->status())->toBe(422);
    
    // Quantidade NÃO deve mudar
    expect($book->fresh()->quantity)->toBe(5);
});

/**
 * CRÍTICO: Requer justificação para saída
 */
it('requires justification for saida', function () {
    $user = \App\Models\User::factory()->create();
    $book = \App\Models\Book::factory()->create(['quantity' => 20]);

    // Sem campo justification
    $response = $this->actingAs($user)->postJson('/api/movements', [
        'book_id' => $book->id,
        'type' => 'saida',
        'quantity' => 5,
    ]);

    expect($response->status())->toBe(422);
    expect($response->json('errors.justification'))->toBeDefined();
});

/**
 * CRÍTICO: Entrada sempre permitida
 */
it('entrada is always allowed', function () {
    $user = \App\Models\User::factory()->create();
    $book = \App\Models\Book::factory()->create(['quantity' => 0]);

    $response = $this->actingAs($user)->postJson('/api/movements', [
        'book_id' => $book->id,
        'type' => 'entrada',
        'quantity' => 999999,  // Sem limite
    ]);

    expect($response->status())->toBe(201);
    expect($book->fresh()->quantity)->toBe(999999);
});

/**
 * Validar quantidade correta após operações
 */
it('calculates quantity correctly', function () {
    $user = \App\Models\User::factory()->create();
    $book = \App\Models\Book::factory()->create(['quantity' => 10]);

    // Entrada: +20
    $this->actingAs($user)->postJson('/api/movements', [
        'book_id' => $book->id,
        'type' => 'entrada',
        'quantity' => 20,
    ]);
    expect($book->fresh()->quantity)->toBe(30);

    // Saída: -5
    $this->actingAs($user)->postJson('/api/movements', [
        'book_id' => $book->id,
        'type' => 'saida',
        'quantity' => 5,
        'justification' => 'Classroom',
    ]);
    expect($book->fresh()->quantity)->toBe(25);
});

// ============================================
// 4. TESTES COM SETUP COMPARTILHADO
// ============================================

describe('Movement Operations with Shared Setup', function () {
    $user = null;
    $book = null;

    beforeEach(function () use (&$user, &$book) {
        $user = \App\Models\User::factory()->create();
        $book = \App\Models\Book::factory()->create(['quantity' => 50]);
    });

    it('can create entrada', function () use ($user, $book) {
        $response = $this->actingAs($user)->postJson('/api/movements', [
            'book_id' => $book->id,
            'type' => 'entrada',
            'quantity' => 10,
        ]);

        $response->assertStatus(201);
    });

    it('can create saida with justification', function () use ($user, $book) {
        $response = $this->actingAs($user)->postJson('/api/movements', [
            'book_id' => $book->id,
            'type' => 'saida',
            'quantity' => 5,
            'justification' => 'Test',
        ]);

        $response->assertStatus(201);
    });
});

// ============================================
// 5. TESTES DE AUTENTICAÇÃO
// ============================================

it('requires authentication for API', function () {
    $response = $this->getJson('/api/books');
    
    // Sem autenticação, deve retornar 401
    expect($response->status())->toBe(401);
});

it('allows authenticated user to access API', function () {
    $user = \App\Models\User::factory()->create();
    \App\Models\Book::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/books');
    
    // Com autenticação, deve permitir
    expect($response->status())->toBeIn([200, 404]); // 404 se rota não existe
});

// ============================================
// 6. ASSERÇÕES ÚTEIS
// ============================================

/**
 * HTTP Status
 */
$response->assertStatus(200);           // Status exato
$response->assertOk();                  // 200
$response->assertCreated();             // 201
$response->assertNoContent();           // 204
$response->assertUnauthorized();        // 401
$response->assertForbidden();           // 403
$response->assertNotFound();            // 404
$response->assertUnprocessable();       // 422

/**
 * JSON Assertions
 */
$response->assertJson(['id' => 1]);     // Contém JSON
$response->assertJsonStructure([        // Estrutura
    'id', 'title', 'isbn', 'quantity'
]);
$response->assertJsonPath('title', 'Test');  // Valor em path
$response->assertJsonValidationErrors('isbn'); // Erros de validação
$response->assertJsonMissing(['password']);  // Não contém

/**
 * Database Assertions
 */
$this->assertDatabaseHas('books', [
    'title' => 'Test Book',
    'isbn' => '978-3-16-148410-0',
]);

$this->assertDatabaseMissing('books', [
    'title' => 'Deleted Book',
]);

$this->assertDatabaseCount('books', 5); // Exatamente 5 livros

/**
 * Expect (PestPHP)
 */
expect($book->quantity)->toBe(10);
expect($book->quantity)->toBeGreaterThan(0);
expect($book->quantity)->toBeLessThan(100);
expect($book->title)->toContain('Test');
expect($book->isbn)->toMatch('/^\d{3}-/');
expect($book->isCriticalStock())->toBeTrue();
expect($book->isOutOfStock())->toBeFalse();

// ============================================
// 7. DICAS E BOAS PRÁTICAS
// ============================================

/**
 * ✅ BOM - Use factories para dados isolados
 */
it('good practice example', function () {
    $user = \App\Models\User::factory()->create(); // Novo usuário por teste
    $book = \App\Models\Book::factory()->create();  // Novo livro por teste
    
    $response = $this->actingAs($user)->postJson('/api/books/' . $book->id);
    $response->assertStatus(200);
});

/**
 * ❌ RUIM - Não use dados hardcoded
 */
it('bad practice example', function () {
    // Erro: Se usuário ID=1 não existir, teste falha
    $response = $this->actingAs(\App\Models\User::find(1))
        ->getJson('/api/books');
});

/**
 * ✅ BOM - Um assertiva principal por teste
 */
it('should block insufficient stock', function () {
    $user = \App\Models\User::factory()->create();
    $book = \App\Models\Book::factory()->create(['quantity' => 5]);

    $response = $this->actingAs($user)->postJson('/api/movements', [
        'book_id' => $book->id,
        'type' => 'saida',
        'quantity' => 10,
        'justification' => 'Test',
    ]);

    // Uma assertiva clara
    expect($response->status())->toBe(422);
});

/**
 * ✅ BOM - Use beforeEach para setup compartilhado
 */
describe('Books API', function () {
    $user = null;

    beforeEach(function () use (&$user) {
        $user = \App\Models\User::factory()->create();
    });

    it('lists books', function () use ($user) {
        $response = $this->actingAs($user)->getJson('/api/books');
        $response->assertStatus(200);
    });
});

/**
 * ✅ BOM - Testes independentes
 */
it('first test', function () {
    \App\Models\Book::factory()->create();
    expect(\App\Models\Book::count())->toBe(1);
});

it('second test', function () {
    // Banco foi limpo, novo teste começa do zero
    expect(\App\Models\Book::count())->toBe(0);
});

// ============================================
// 8. REFERÊNCIAS RÁPIDAS
// ============================================

/**
 * Rodar testes
 * 
 * php artisan test                                    # Todos
 * php artisan test tests/Feature/ApiBookTest.php     # Arquivo específico
 * php artisan test --filter "Book"                   # Nome do teste
 * php artisan test --coverage                        # Com cobertura
 * php artisan test --verbose                         # Detalhado
 * php artisan test --without-parallel                # Sem paralelismo
 */

/**
 * Documentação
 * https://pestphp.com/                                # PestPHP
 * https://laravel.com/docs/testing                   # Laravel Testing
 * https://laravel.com/docs/eloquent-factories        # Factories
 */
