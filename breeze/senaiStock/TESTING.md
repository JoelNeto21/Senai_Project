# SenaiStock - Testing Documentation

## Visão Geral

Esta documentação descreve como executar e manter a suite de testes automatizados da API SenaiStock. Os testes foram desenvolvidos com **PestPHP** e cobrem autenticação, gerenciamento de livros e movimentações de estoque.

## Estrutura dos Testes

```
tests/
├── Feature/
│   ├── ApiAuthTest.php           # Testes de autenticação (login, logout, sessões)
│   ├── ApiBookTest.php           # Testes CRUD de livros
│   ├── ApiMovementTest.php       # Testes de entrada/saída de estoque (CRÍTICO)
│   ├── Auth/                     # Testes padrão de autenticação do Laravel
│   ├── ProfileTest.php           # Testes de perfil do usuário
│   └── ExampleTest.php           # Teste exemplo do Laravel
└── Unit/                         # Testes de unidade (estrutura vazia para expansão)

database/
├── factories/
│   ├── UserFactory.php           # Factory com roles (almoxarife, coordenador)
│   ├── BookFactory.php           # Factory com estados (criticalStock, outOfStock, etc)
│   └── MovementFactory.php       # Factory de movimentações (entrada, saida)
└── seeders/
    ├── DatabaseSeeder.php        # Seeder principal (usar em testes se necessário)
    └── ... (outros seeders)
```

## Configuração do Ambiente de Testes

### Banco de Dados
O arquivo `phpunit.xml` está configurado para usar SQLite em memória (`:memory:`), o que garante:
- Testes rápidos e isolados
- Nenhuma contaminação entre testes
- Sem necessidade de limpeza manual de dados

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### Variáveis de Ambiente
O arquivo `.env.testing` pode ser criado para configurações específicas de teste:

```bash
APP_ENV=testing
APP_DEBUG=true
APP_URL=http://localhost
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

## Como Executar os Testes

### 1. Rodar todos os testes
```bash
php artisan test
```

### 2. Rodar testes específicos por arquivo
```bash
# Apenas testes de autenticação
php artisan test tests/Feature/ApiAuthTest.php

# Apenas testes de livros
php artisan test tests/Feature/ApiBookTest.php

# Apenas testes de movimentações (CRÍTICO)
php artisan test tests/Feature/ApiMovementTest.php
```

### 3. Rodar testes com padrão de nome
```bash
# Todos os testes que contêm "entrada"
php artisan test --filter entrada

# Todos os testes que contêm "saida"
php artisan test --filter saida

# Todos os testes de validação
php artisan test --filter validation
```

### 4. Rodar com cobertura de código
```bash
php artisan test --coverage

# Com geração de HTML report
php artisan test --coverage --coverage-html=coverage-report
```

### 5. Rodar testes em modo verbose
```bash
php artisan test --verbose
```

### 6. Rodar teste único (PestPHP)
```bash
php artisan test tests/Feature/ApiMovementTest.php --filter "blocks saida when quantity exceeds available stock"
```

## Arquitetura dos Testes

### Testes de Autenticação (`ApiAuthTest.php`)

**Objetivo**: Validar que o sistema de autenticação do Laravel Breeze está funcionando corretamente.

**Cenários Testados**:
- ✅ Login com credenciais válidas
- ✅ Rejeição de senha incorreta
- ✅ Rejeição de email não existente
- ✅ Validação de campos obrigatórios (email, password)
- ✅ Logout de usuário autenticado
- ✅ Acesso negado a rotas protegidas sem autenticação
- ✅ Acesso permitido a rotas protegidas com autenticação

**Executar**:
```bash
php artisan test --filter Auth
```

### Testes de Livros (`ApiBookTest.php`)

**Objetivo**: Validar CRUD completo de livros com regras de negócio.

**Endpoints Testados**:
| Método | Rota | Teste |
|--------|------|-------|
| GET | `/api/books` | Listar livros (requer auth) |
| GET | `/api/books/{id}` | Detalhes de um livro |
| POST | `/api/books` | Criar livro (validações) |
| PUT | `/api/books/{id}` | Atualizar livro |
| DELETE | `/api/books/{id}` | Deletar livro |

**Validações Críticas**:
- ISBN deve ser único (422 se duplicado)
- Campos `title`, `isbn`, `subject` são obrigatórios (422 se ausentes)
- Quantidade padrão é 0 se não fornecida
- Requer autenticação (401 se não autenticado)
- Deleção também remove movimentações associadas

**Executar**:
```bash
php artisan test tests/Feature/ApiBookTest.php
php artisan test --filter "Books API"
```

### Testes de Movimentações (`ApiMovementTest.php`) ⚠️ CRÍTICO

**Objetivo**: Validar regras críticas de negócio para entrada e saída de estoque.

#### **Entrada (Entrada)**
- ✅ Aumenta quantidade do livro
- ✅ Não requer justificação
- ✅ Sempre permitida (com dados válidos)
- ✅ Cria movimento no banco de dados

#### **Saída (Saída)** - REGRAS CRÍTICAS
- ❌ **BLOQUEIA** se quantidade > saldo disponível → HTTP 422
- ❌ **REQUER** campo `justification` obrigatório → HTTP 422 se vazio/nulo
- ✅ Diminui quantidade do livro
- ✅ Cria movimento com justificativa

**Exemplo de Teste Crítico**:
```php
it('blocks saida when quantity exceeds available stock', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create(['quantity' => 5]);
    
    $response = $this->actingAs($user)->postJson('/api/movements', [
        'book_id' => $book->id,
        'type' => 'saida',
        'quantity' => 10,
        'justification' => 'Turma A'
    ]);
    
    $response->assertStatus(422);
    expect($book->fresh()->quantity)->toBe(5); // Não alterado
});
```

**Validações Testadas**:
| Cenário | Entrada | Saída |
|---------|---------|-------|
| Aumenta/diminui estoque | ✅ Aumenta | ✅ Diminui |
| Justificação obrigatória | ❌ Não | ✅ Sim |
| Quantidade > estoque | ✅ Permitido | ❌ Bloqueado (422) |
| Quantidade negativa | ❌ Validação | ❌ Validação |
| Quantidade zero | ❌ Validação | ❌ Validação |
| Estoque nunca negativo | ✅ Garantido | ✅ Garantido |

**Executar**:
```bash
php artisan test tests/Feature/ApiMovementTest.php
php artisan test --filter "Movements API"
php artisan test --filter "CRITICAL"
php artisan test --filter "saida"
php artisan test --filter "entrada"
```

## Factories

### UserFactory
```php
// Usuário comum
User::factory()->create()

// Almoxarife
User::factory()->almoxarife()->create()

// Coordenador
User::factory()->coordenador()->create()
```

### BookFactory
```php
// Livro com estoque aleatório
Book::factory()->create()

// Estoque crítico (< 10)
Book::factory()->criticalStock()->create()

// Sem estoque
Book::factory()->outOfStock()->create()

// Estoque normal (20-50)
Book::factory()->normalStock()->create()

// Estoque alto (50-100)
Book::factory()->highStock()->create()

// Múltiplos livros
Book::factory()->count(10)->create()
```

### MovementFactory
```php
// Movimento aleatório (entrada ou saída)
Movement::factory()->create()

// Entrada específica
Movement::factory()->entrada()->create()

// Saída específica
Movement::factory()->saida()->create()

// Para um livro específico
Movement::factory()->forBook($book)->create()

// Por usuário específico
Movement::factory()->byUser($user)->create()

// Quantidade grande (50-100)
Movement::factory()->largeQuantity()->create()

// Quantidade pequena (1-5)
Movement::factory()->smallQuantity()->create()
```

## Importância de Cada Teste

### 🔴 CRÍTICO (Falha = Bug em Produção)
1. **Saída bloqueada quando estoque insuficiente** → HTTP 422
2. **Justificação obrigatória para saída** → HTTP 422
3. **Estoque nunca vai negativo** → Garantido sempre
4. **ISBN deve ser único** → 422 em duplicado
5. **Entrada sempre permitida** → Nunca falha com dados válidos

### 🟡 IMPORTANTE (Falha = Funcionalidade Quebrada)
1. Autenticação funcionando
2. Permissões sendo aplicadas (401/403)
3. Validações de campos
4. Cálculos corretos (quantidade)
5. Cascata de deleção funciona

### 🟢 DESEJÁVEL (Falha = Defeito Menor)
1. Campos adicionais estruturados
2. Formatação de resposta JSON
3. Mensagens de erro descritivas
4. Filtering e paginação

## Estrutura de Resposta Esperada

### Sucesso - GET /api/books
```json
[
  {
    "id": 1,
    "title": "The Great Gatsby",
    "isbn": "978-0-7432-7356-5",
    "subject": "Fiction",
    "quantity": 15,
    "created_at": "2025-01-10T10:00:00Z",
    "updated_at": "2025-01-10T10:00:00Z"
  }
]
```

### Sucesso - POST /api/movements (Entrada)
```json
{
  "id": 42,
  "type": "entrada",
  "book_id": 1,
  "quantity": 10,
  "justification": null,
  "created_at": "2025-01-10T10:05:00Z"
}
```

### Erro - POST /api/movements (Saída Bloqueada)
```json
{
  "message": "Saída não permitida: quantidade solicitada (10) excede o saldo disponível (5)",
  "errors": {
    "quantity": ["Quantidade insuficiente em estoque"]
  }
}
```

### Erro - Validação (422)
```json
{
  "message": "The given data was invalid",
  "errors": {
    "justification": ["O campo justificação é obrigatório para saídas"],
    "quantity": ["Quantidade deve ser maior que 0"]
  }
}
```

## Boas Práticas Para Adicionar Novos Testes

1. **Use Factories**:
   ```php
   // ✅ BOM - Isolado e reutilizável
   $book = Book::factory()->create();
   
   // ❌ RUIM - Hardcoded
   $book = Book::create(['title' => 'Test', ...]);
   ```

2. **Um assertiva principal por teste**:
   ```php
   // ✅ BOM - Claro qual é a falha
   it('blocks saida when insufficient stock', function () {
       $response = $this->actingAs($user)->postJson(...);
       $response->assertStatus(422);
   });
   
   // ❌ RUIM - Múltiplas assertivas ocultam falha
   it('handles movement correctly', function () {
       $response = ...; // Qual assertiva falhou?
       $response->assertStatus(422);
       $response->assertJsonValidationErrors('quantity');
   });
   ```

3. **Use Describe Blocks para Organização**:
   ```php
   describe('Saida Validations', function () {
       it('requires justification', function () { ... });
       it('blocks insufficient stock', function () { ... });
   });
   ```

4. **Nomes descritivos**:
   ```php
   // ✅ BOM - Descreve comportamento esperado
   it('can create entrada movement without justification', function () { ... })
   
   // ❌ RUIM - Vago
   it('test movement creation', function () { ... })
   ```

5. **Setup compartilhado com beforeEach**:
   ```php
   beforeEach(function () {
       $this->user = User::factory()->create();
       $this->book = Book::factory()->create();
   });
   ```

## Troubleshooting

### Erro: "No application encryption key has been specified"
**Solução**: Gerar chave APP_KEY
```bash
php artisan key:generate --env=testing
```

### Erro: "SQLSTATE[HY000] General error: 5 database is locked"
**Solução**: Usar apenas um processo de teste
```bash
php artisan test --without-parallel
```

### Testes lentos
**Solução**: Usar SQLite em memória (já configurado) e evitar queries desnecessárias
```bash
# Ver queries executadas
php artisan test --debug
```

### Erro: Factory não encontrada
**Solução**: Registrar factory em model ou usar namespace completo
```php
// Automático se factory em Database\Factories\BookFactory
Book::factory()->create()

// Manual
(new BookFactory())->create()
```

### Testes falhando intermitentemente
**Causas comuns**:
- Estado compartilhado entre testes (não usar static)
- Dados aleatórios (faker) criando conflitos
- Queries não esperando resultado
- Timing em operações assíncronas

**Solução**: Cada teste é isolado em transação SQLite; verificar lógica de negócio

## Relatório de Cobertura

Para gerar relatório HTML de cobertura:
```bash
php artisan test --coverage --coverage-html=coverage-report

# Abrir em navegador
start coverage-report/index.html  # Windows
open coverage-report/index.html   # macOS
xdg-open coverage-report/index.html # Linux
```

## Integração com CI/CD

### GitHub Actions
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: php-actions/composer@v6
      - run: php artisan test
```

### GitLab CI
```yaml
test:
  image: php:8.2
  script:
    - composer install
    - php artisan test --coverage
```

## Próximos Passos

1. **Implementar Controllers de API**:
   - `app/Http/Controllers/Api/BookController.php`
   - `app/Http/Controllers/Api/MovementController.php`

2. **Adicionar Rotas API**:
   - `routes/api.php` com recursos RESTful

3. **Implementar Validações de Negócio**:
   - FormRequests para Book e Movement
   - Validações de quantidade negativa/zero
   - Bloqueio de saída > estoque

4. **Testes de Performance**:
   - Benchmark de operações massivas
   - Query optimization

5. **Autenticação via Token (Sanctum)**:
   - Se implementar API pura sem sessões

## Referências

- **PestPHP**: https://pestphp.com/
- **Laravel Testing**: https://laravel.com/docs/testing
- **PHPUnit**: https://phpunit.de/
- **Factory Pattern**: https://laravel.com/docs/eloquent-factories

## Contato & Suporte

Para dúvidas sobre os testes, consulte:
- Documentação do Laravel: https://laravel.com/docs
- Issues no repositório
- Pull Request com testes falhando para debug
