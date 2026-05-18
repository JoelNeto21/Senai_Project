# 🔧 EXEMPLO DE IMPLEMENTAÇÃO — Controllers & Form Requests

Este arquivo mostra um exemplo mínimo viável (MVP) para fazer os testes passarem.

---

## 1️⃣ Form Request para Validação

### `app/Http/Requests/StoreMovementRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => 'required|exists:books,id',
            'type' => 'required|in:entrada,saida',
            'quantity' => 'required|numeric|min:1',
            'justification' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'Livro é obrigatório',
            'type.required' => 'Tipo de movimento é obrigatório',
            'quantity.required' => 'Quantidade é obrigatória',
            'quantity.min' => 'Quantidade deve ser maior que 0',
        ];
    }
}
```

### `app/Http/Requests/StoreBookRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'isbn' => 'required|unique:books,isbn',
            'subject' => 'required|string|max:100',
            'quantity' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Título é obrigatório',
            'isbn.required' => 'ISBN é obrigatório',
            'isbn.unique' => 'Este ISBN já existe',
            'subject.required' => 'Matéria é obrigatória',
        ];
    }
}
```

---

## 2️⃣ Controllers

### `app/Http/Controllers/Api/BookController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreBookRequest;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BookController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Book::all(),
        ]);
    }

    public function show(Book $book): JsonResponse
    {
        return response()->json([
            'data' => $book,
        ]);
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = Book::create($request->validated());

        return response()->json([
            'message' => 'Livro criado com sucesso',
            'data' => $book,
        ], Response::HTTP_CREATED);
    }

    public function update(StoreBookRequest $request, Book $book): JsonResponse
    {
        $book->update($request->validated());

        return response()->json([
            'message' => 'Livro atualizado com sucesso',
            'data' => $book,
        ]);
    }

    public function destroy(Book $book): Response
    {
        $book->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
```

### `app/Http/Controllers/Api/MovementController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreMovementRequest;
use App\Models\Book;
use App\Models\Movement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MovementController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Movement::all(),
        ]);
    }

    public function show(Movement $movement): JsonResponse
    {
        return response()->json([
            'data' => $movement,
        ]);
    }

    public function store(StoreMovementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $book = Book::findOrFail($data['book_id']);

        // 🔴 VALIDAÇÃO CRÍTICA 1: Saida não pode exceder estoque
        if ($data['type'] === 'saida' && $data['quantity'] > $book->quantity) {
            return response()->json([
                'message' => 'Quantidade solicitada excede o estoque disponível',
                'errors' => [
                    'quantity' => ['Estoque insuficiente. Disponível: ' . $book->quantity]
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 🔴 VALIDAÇÃO CRÍTICA 2: Justification obrigatória para saida
        if ($data['type'] === 'saida') {
            $justification = $data['justification'] ?? null;
            
            if (empty($justification)) {
                return response()->json([
                    'message' => 'Validação falhou',
                    'errors' => [
                        'justification' => ['Justificativa é obrigatória para saídas']
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        // Atualizar estoque
        if ($data['type'] === 'entrada') {
            $book->increment('quantity', $data['quantity']);
        } else {
            $book->decrement('quantity', $data['quantity']);
        }

        // Criar registro de movimento
        $movement = Movement::create([
            'book_id' => $data['book_id'],
            'user_id' => auth()->id() ?? 1,
            'type' => $data['type'],
            'quantity' => $data['quantity'],
            'justification' => $data['justification'] ?? null,
        ]);

        return response()->json([
            'message' => 'Movimento registrado com sucesso',
            'data' => $movement,
        ], Response::HTTP_CREATED);
    }

    public function destroy(Movement $movement): Response
    {
        $movement->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
```

---

## 3️⃣ Rotas

Adicionar em `routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\MovementController;
use Illuminate\Support\Facades\Route;

// Rotas públicas (se necessário)
Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);

// Rotas protegidas (se implementar Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('books', BookController::class);
    Route::apiResource('movements', MovementController::class);
    
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy']);
});

// Se não usar autenticação ainda, deixar rotas abertas:
// Route::apiResource('books', BookController::class);
// Route::apiResource('movements', MovementController::class);
```

---

## 4️⃣ Modificar Models

### `app/Models/Book.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'isbn', 'subject', 'quantity'];

    public function movements()
    {
        return $this->hasMany(Movement::class);
    }
}
```

### `app/Models/Movement.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'user_id', 'type', 'quantity', 'justification'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## 5️⃣ Verificar Migrations

Garantir que as migrations existem:

### `database/migrations/xxxx_xx_xx_create_books_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('isbn')->unique();
            $table->string('subject');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
```

### `database/migrations/xxxx_xx_xx_create_movements_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('type', ['entrada', 'saida']);
            $table->integer('quantity');
            $table->text('justification')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
```

---

## 6️⃣ Rodar Testes

```bash
# 1. Garantir que migrations foram criadas
php artisan migrate

# 2. Rodar testes
php artisan test

# Resultado esperado:
# Tests: 82 passed
```

---

## 📊 Estrutura Final

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── BookController.php ............ ✅
│   │   └── MovementController.php ........ ✅
│   └── Requests/
│       ├── StoreBookRequest.php ......... ✅
│       └── StoreMovementRequest.php ..... ✅
└── Models/
    ├── Book.php ......................... ✅
    └── Movement.php ..................... ✅

routes/
└── api.php .............................. ✅

database/
├── factories/
│   ├── UserFactory.php ................. ✅ (já criado)
│   ├── BookFactory.php ................. ✅ (já criado)
│   └── MovementFactory.php ............. ✅ (já criado)
└── migrations/ ......................... ✅ (já existem)

tests/
└── Feature/
    ├── ApiAuthTest.php ................. ✅ (já criado)
    ├── ApiBookTest.php ................. ✅ (já criado)
    └── ApiMovementTest.php ............. ✅ (já criado)
```

---

## 🚀 Próximas Ações

1. **Copiar código acima** para seus arquivos
2. **Rodar migrations**: `php artisan migrate`
3. **Rodar testes**: `php artisan test`
4. **Validar**: Todos os 82 testes devem passar ✅

---

**Nota**: Este é um exemplo mínimo. Para produção, adicionar:
- Autenticação/autorização completa
- Tratamento de erros mais robusto
- Logging
- Rate limiting
- Versionamento de API
