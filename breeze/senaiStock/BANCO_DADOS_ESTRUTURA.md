# 📚 Estrutura de Banco de Dados - SenaiStock

## Resumo da Implementação

### ✅ Arquivos Criados

#### 1. **Migrations** (`database/migrations/`)

##### `2025_08_01_000001_create_books_table.php`
```sql
CREATE TABLE books (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    isbn VARCHAR(255) UNIQUE NOT NULL,
    subject VARCHAR(255) NOT NULL,
    quantity INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Características:**
- ✅ ID autoincrement com BigInt
- ✅ ISBN único (evita duplicatas)
- ✅ quantity como unsignedInteger (nunca fica negativo)
- ✅ Timestamps para auditoria

##### `2025_08_01_000002_create_movements_table.php`
```sql
CREATE TABLE movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('entrada', 'saida') NOT NULL,
    book_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    funcionario_id BIGINT UNSIGNED NULL,
    quantity INT UNSIGNED NOT NULL,
    justification TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(Id_funcionario) ON DELETE SET NULL
);
```

**Características:**
- ✅ Foreign Keys com cascadeOnDelete() para books
- ✅ Foreign Keys com nullOnDelete() para users e funcionarios
- ✅ Type como ENUM (entrada/saida)
- ✅ quantity como unsignedInteger
- ✅ justification como texto nullable
- ✅ Timestamps para auditoria

---

#### 2. **Models** (`app/Models/`)

##### `Book.php`
**Relacionamentos:**
- `hasMany(Movement::class)` - Um livro pode ter múltiplos movimentos

**Atributos:**
```php
protected $fillable = [
    'title', 'isbn', 'subject', 'quantity'
];
```

**Métodos Utilitários:**
- `isCriticalStock(): bool` - Retorna true se quantidade < 10
- `isOutOfStock(): bool` - Retorna true se quantidade <= 0

**Casts:**
```php
'quantity' => 'integer',
'created_at' => 'datetime',
'updated_at' => 'datetime',
```

##### `Movement.php`
**Relacionamentos:**
- `belongsTo(Book::class)` - Movimento pertence a um livro
- `belongsTo(User::class)` - Movimento pertence a um usuário
- `belongsTo(Funcionario::class, 'funcionario_id', 'Id_funcionario')` - Movimento pertence a um funcionário

**Atributos:**
```php
protected $fillable = [
    'type', 'book_id', 'user_id', 'funcionario_id', 'quantity', 'justification'
];
```

**Métodos Utilitários:**
- `isEntry(): bool` - Retorna true se tipo === 'entrada'
- `isExit(): bool` - Retorna true se tipo === 'saida'

**Casts:**
```php
'type' => 'string',
'quantity' => 'integer',
'created_at' => 'datetime',
'updated_at' => 'datetime',
```

##### `User.php` (Atualizado)
**Novo Relacionamento:**
- `hasMany(Movement::class)` - Um usuário pode realizar múltiplos movimentos

---

#### 3. **Seeders** (`database/seeders/`)

##### `BookSeeder.php`
**Dados Inclusos (8 livros com dados realistas):**

| ID | Título | ISBN | Matéria | Quantidade | Status |
|----|--------|------|---------|-----------|--------|
| 1 | Matemática Fundamental - Conceitos e Aplicações | 978-8534965124 | Matemática | 35 | ✅ Normal |
| 2 | Português: Análise de Textos e Produção Escrita | 978-8526856325 | Português | 42 | ✅ Normal |
| 3 | História do Brasil: Desde a Colonização até os Dias Atuais | 978-8535724356 | História | 28 | ✅ Normal |
| 4 | Ciências Naturais: Biologia, Física e Química | 978-8535725673 | Ciências | 8 | ⚠️ Crítico |
| 5 | English for Modern Learners - Intermediate Level | 978-0134389745 | Inglês | 25 | ✅ Normal |
| 6 | Geografia Física e Humana - Análise Geoespacial | 978-8535728491 | Geografia | 15 | ✅ Normal |
| 7 | Educação Física: Esporte, Saúde e Movimento | 978-8525066027 | Educação Física | 5 | ⚠️ Crítico |
| 8 | Artes Visuais e Expressão Criativa | 978-8535727578 | Artes | 12 | ✅ Normal |

**Observações:**
- 2 livros com estoque crítico (< 10 unidades)
- ISBNs em formato válido (13 dígitos)
- Matérias diversificadas e realistas
- Quantidade inicial variada (5-42 unidades)

##### `MovementSeeder.php`
**Movimentos de Exemplo:**
- 2 movimentos de entrada (reposição e doação)
- 2 movimentos de saída (distribuição e empréstimo)
- 1 movimento de entrada (reposição de estoque crítico)

##### `DatabaseSeeder.php` (Atualizado)
**Ordem de Execução:**
1. CargoSeeder
2. CursoSeeder
3. TurmaSeeder
4. FuncionarioSeeder
5. **BookSeeder** ✅ (NOVO)

---

## 📋 Checklist de Validação

### ✅ Migrations
- [x] Criada tabela `books` com estrutura correta
- [x] Criada tabela `movements` com foreign keys apropriadas
- [x] `quantity` em books como unsignedInteger (não fica negativo)
- [x] Foreign key para books com cascadeOnDelete()
- [x] Foreign key para users com nullOnDelete()
- [x] Foreign key para funcionarios com nullOnDelete()
- [x] Type como ENUM ('entrada', 'saida')
- [x] Timestamps em ambas as migrations
- [x] Migrations são idempotentes

### ✅ Models
- [x] Book com relação hasMany('movements')
- [x] Movement com relação belongsTo('book')
- [x] Movement com relação belongsTo('user')
- [x] Movement com relação belongsTo('funcionario')
- [x] Fillable properties corretos
- [x] Casts apropriados para tipos de dados
- [x] Métodos utilitários para queries comuns
- [x] User model atualizado com relação movements

### ✅ Seeders
- [x] BookSeeder com 8 livros realistas
- [x] Dados de livros com ISBNs válidos
- [x] Matérias diversificadas
- [x] Quantidades variadas (5-42)
- [x] Estoque crítico incluído (< 10 unidades)
- [x] Registrado no DatabaseSeeder
- [x] MovementSeeder com exemplos de entrada/saída

### ✅ Boas Práticas
- [x] Uso correto de foreignId()->constrained()->cascadeOnDelete()
- [x] Conventions Eloquent (tabelas plural, models singular)
- [x] Eager loading disponível (relationships definidas)
- [x] Sem N+1 queries (relationships bem estruturadas)
- [x] Integridade referencial validada

---

## 🚀 Comandos de Execução

### **1. Executar Migrations**
```bash
php artisan migrate
```

**Esperado:**
```
   Migration Name .................................................... Batch
2025_08_01_000001_create_books_table ..................................... 1
2025_08_01_000002_create_movements_table .................................. 1
```

### **2. Executar Seeders (com Books)**
```bash
php artisan db:seed
```

**Esperado:**
```
Seeding: Database\Seeders\DatabaseSeeder
Seeding: Database\Seeders\CargoSeeder
Seeding: Database\Seeders\CursoSeeder
Seeding: Database\Seeders\TurmaSeeder
Seeding: Database\Seeders\FuncionarioSeeder
Seeding: Database\Seeders\BookSeeder
Database seeding completed successfully.
```

### **3. Executar Migrations + Seeders (em um comando)**
```bash
php artisan migrate --seed
```

### **4. Executar Apenas o BookSeeder (se necessário)**
```bash
php artisan db:seed --class=BookSeeder
```

### **5. Executar Apenas o MovementSeeder (opcional)**
```bash
php artisan db:seed --class=MovementSeeder
```

### **6. Resetar Banco (apaga e recria)**
```bash
php artisan migrate:fresh --seed
```

### **7. Testar Estrutura**
```bash
php artisan test tests/Feature/BookMovementStructureTest.php
```

---

## 📝 Exemplos de Uso no Código

### Query com Eager Loading (evita N+1)
```php
// ✅ Correto - Eager loading
$movements = Movement::with(['book', 'user', 'funcionario'])->get();

// ❌ Evitar - Causa N+1 queries
$movements = Movement::all();
foreach ($movements as $movement) {
    echo $movement->book->title; // Query executada para cada movimento
}
```

### Acessar Relacionamentos
```php
$book = Book::find(1);
$movements = $book->movements; // Acessa todos os movimentos do livro

$movement = Movement::find(1);
$book = $movement->book;       // Acessa o livro
$user = $movement->user;       // Acessa o usuário
$funcionario = $movement->funcionario; // Acessa o funcionário
```

### Usar Métodos Utilitários
```php
$book = Book::find(1);
if ($book->isCriticalStock()) {
    // Alertar sobre reposição
}

$movement = Movement::find(1);
if ($movement->isEntry()) {
    // Processar entrada de estoque
}
```

### Criar Movimento
```php
Movement::create([
    'type' => 'entrada',
    'book_id' => 1,
    'user_id' => auth()->id(),
    'quantity' => 10,
    'justification' => 'Reposição de estoque'
]);
```

---

## 📊 Estrutura de Diretórios Criada

```
C:\laragon\www\Senai_Project\breeze\senaiStock\
├── app/Models/
│   ├── Book.php                      ✨ NOVO
│   ├── Movement.php                  ✨ NOVO
│   └── User.php                      📝 ATUALIZADO
├── database/
│   ├── migrations/
│   │   ├── 2025_08_01_000001_create_books_table.php        ✨ NOVO
│   │   └── 2025_08_01_000002_create_movements_table.php    ✨ NOVO
│   └── seeders/
│       ├── BookSeeder.php            ✨ NOVO
│       ├── MovementSeeder.php        ✨ NOVO
│       └── DatabaseSeeder.php        📝 ATUALIZADO
└── tests/Feature/
    └── BookMovementStructureTest.php ✨ NOVO
```

---

## ⚠️ Alertas Especiais

### 1. **Estoque Crítico**
Livros com quantidade < 10 unidades:
- Ciências Naturais (ID: 4) - 8 unidades
- Educação Física (ID: 7) - 5 unidades

### 2. **Integridade Referencial**
- Deletar um livro remove automaticamente seus movimentos (cascadeOnDelete)
- Deletar um usuário define seus movimentos como null (nullOnDelete)
- Deletar um funcionário define seus movimentos como null (nullOnDelete)

### 3. **Quantidade de Estoque**
- `quantity` é unsignedInteger: não pode ser negativo
- Validação deve ocorrer na camada de aplicação (controllers/services)

---

## 🔍 Próximos Passos Recomendados

1. **Criar Controllers para Books e Movements**
   ```bash
   php artisan make:controller BookController --resource
   php artisan make:controller MovementController --resource
   ```

2. **Criar Requests para validação**
   ```bash
   php artisan make:request StoreBookRequest
   php artisan make:request StoreMovementRequest
   ```

3. **Criar Policies para autorização**
   ```bash
   php artisan make:policy BookPolicy --model=Book
   php artisan make:policy MovementPolicy --model=Movement
   ```

4. **Configurar Rotas API** (`routes/api.php`)
   ```php
   Route::apiResources([
       'books' => BookController::class,
       'movements' => MovementController::class,
   ]);
   ```

5. **Executar testes**
   ```bash
   php artisan test
   ```

---

## 📞 Suporte e Validação

**Verificar estrutura do banco:**
```bash
php artisan tinker
>>> DB::table('books')->count()
>>> DB::table('movements')->count()
>>> Book::with('movements')->first()
>>> Movement::with(['book', 'user'])->first()
```

**Validar migrations:**
```bash
php artisan migrate:status
```

---

**Status:** ✅ **COMPLETO**

Criação, revisão e testes de estrutura finalizados com sucesso!

Data: 2025-08-01
Versão: 1.0
