<?php
/**
 * Setup Script for SenaiStock API
 * This script creates all necessary directories and files
 * Run this from the project root: php setup_api.php
 */

$projectRoot = __DIR__;

// Step 1: Create directories
$directories = [
    'app/Http/Controllers/Api',
    'app/Http/Resources',
];

foreach ($directories as $dir) {
    $fullPath = $projectRoot . '/' . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "[OK] Created directory: $dir\n";
    } else {
        echo "[EXISTS] Directory already exists: $dir\n";
    }
}

// Step 2: Create BookResource.php
$bookResourceContent = <<<'EOF'
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'subject' => $this->subject,
            'quantity' => $this->quantity,
            'is_critical_stock' => $this->isCriticalStock(),
            'is_out_of_stock' => $this->isOutOfStock(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
EOF;

file_put_contents($projectRoot . '/app/Http/Resources/BookResource.php', $bookResourceContent);
echo "[OK] Created: app/Http/Resources/BookResource.php\n";

// Step 3: Create MovementResource.php
$movementResourceContent = <<<'EOF'
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'book_id' => $this->book_id,
            'book' => new BookResource($this->whenLoaded('book')),
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user') ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null,
            'funcionario_id' => $this->funcionario_id,
            'quantity' => $this->quantity,
            'justification' => $this->justification,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
EOF;

file_put_contents($projectRoot . '/app/Http/Resources/MovementResource.php', $movementResourceContent);
echo "[OK] Created: app/Http/Resources/MovementResource.php\n";

// Step 4: Create BookController.php
$bookControllerContent = <<<'EOF'
<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class BookController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of books.
     */
    public function index(): ResourceCollection
    {
        $books = Book::paginate(15);
        return BookResource::collection($books);
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book): BookResource
    {
        return new BookResource($book);
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(StoreBookRequest $request): BookResource
    {
        $book = Book::create($request->validated());
        return new BookResource($book);
    }

    /**
     * Update the specified book in storage.
     */
    public function update(UpdateBookRequest $request, Book $book): BookResource
    {
        $book->update($request->validated());
        return new BookResource($book);
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Book $book): Response
    {
        $book->delete();
        return response()->noContent();
    }
}
EOF;

file_put_contents($projectRoot . '/app/Http/Controllers/Api/BookController.php', $bookControllerContent);
echo "[OK] Created: app/Http/Controllers/Api/BookController.php\n";

// Step 5: Create MovementController.php
$movementControllerContent = <<<'EOF'
<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\Movement;
use App\Http\Requests\StoreMovementRequest;
use App\Http\Resources\MovementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MovementController extends \App\Http\Controllers\Controller
{
    /**
     * Store a new movement (entrada or saida).
     * Applies business rules: prevents saida if insufficient quantity.
     */
    public function store(StoreMovementRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $book = Book::findOrFail($validated['book_id']);

        // Business Rule: Prevent saida if quantity exceeds available stock
        if ($validated['type'] === 'saida' && $validated['quantity'] > $book->quantity) {
            return response()->json([
                'message' => 'Quantidade insuficiente em estoque.',
                'errors' => [
                    'quantity' => [
                        "A saída solicitada ({$validated['quantity']}) excede o saldo disponível ({$book->quantity})."
                    ]
                ]
            ], 422);
        }

        // Use database transaction for atomicity
        return DB::transaction(function () use ($validated, $book, $request) {
            // Update book quantity
            if ($validated['type'] === 'entrada') {
                $book->increment('quantity', $validated['quantity']);
            } else {
                $book->decrement('quantity', $validated['quantity']);
            }

            // Create movement record
            $movement = Movement::create([
                'type' => $validated['type'],
                'book_id' => $validated['book_id'],
                'user_id' => Auth::id(),
                'quantity' => $validated['quantity'],
                'justification' => $validated['justification'] ?? null,
            ]);

            return response()->json([
                'message' => 'Movimento registrado com sucesso.',
                'data' => new MovementResource($movement->load('book', 'user'))
            ], 201);
        });
    }
}
EOF;

file_put_contents($projectRoot . '/app/Http/Controllers/Api/MovementController.php', $movementControllerContent);
echo "[OK] Created: app/Http/Controllers/Api/MovementController.php\n";

echo "\n✅ Setup complete! All directories and files have been created.\n";
echo "Next steps:\n";
echo "1. Run: php artisan migrate\n";
echo "2. Run: php artisan sanctum:install\n";
echo "3. Update routes/api.php (see example below)\n";
echo "4. Update app/Models/User.php to include HasApiTokens trait\n";
?>
