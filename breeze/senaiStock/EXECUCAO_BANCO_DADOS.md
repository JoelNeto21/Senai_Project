# 🚀 Guia de Execução - Estrutura de Banco de Dados SenaiStock

## 📋 Resumo Rápido

Este documento descreve como executar as migrations e seeders para criar a estrutura de banco de dados do SenaiStock com tabelas **books** e **movements**.

---

## ⚡ Quick Start (3 passos)

### 1️⃣ Executar Migrations
```bash
php artisan migrate
```

**Resultado esperado:**
```
  1. Migration Name ..................... Batch
  2. 2025_08_01_000001_create_books_table .... 1
  3. 2025_08_01_000002_create_movements_table .... 1
```

### 2️⃣ Executar Seeders (com dados de exemplo)
```bash
php artisan db:seed
```

**Resultado esperado:**
```
Seeding: Database\Seeders\DatabaseSeeder
Seeding: Database\Seeders\CargoSeeder
Seeding: Database\Seeders\CursoSeeder
Seeding: Database\Seeders\TurmaSeeder
Seeding: Database\Seeders\FuncionarioSeeder
Seeding: Database\Seeders\BookSeeder
Database seeding completed successfully.
```

### 3️⃣ Verificar Dados (opcional)
```bash
php artisan tinker
```

```php
>>> Book::count()
=> 8

>>> Movement::count()
=> 5

>>> Book::with('movements')->first()
```

---

## 📦 Comandos Disponíveis

### Executar Tudo de Uma Vez
```bash
# Fresh migration + seed (reseta o banco)
php artisan migrate:fresh --seed

# Ou incrementalmente (mantém dados)
php artisan migrate && php artisan db:seed
```

### Executar Apenas Seeders
```bash
# Todos os seeders
php artisan db:seed

# Apenas BookSeeder
php artisan db:seed --class=BookSeeder

# Apenas MovementSeeder (se necessário)
php artisan db:seed --class=MovementSeeder
```

### Verificar Status
```bash
# Ver migrations executadas
php artisan migrate:status

# Ver migrations pendentes
php artisan migrate:status --pending
```

### Reverter Migrations
```bash
# Desfazer última execução
php artisan migrate:rollback

# Desfazer tudo
php artisan migrate:reset

# Desfazer tudo e refazer
php artisan migrate:refresh

# Desfazer tudo, refazer e seedar
php artisan migrate:refresh --seed
```

---

## 📊 O Que Foi Criado

### 1. **Tabela: books**
```sql
CREATE TABLE books (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    isbn VARCHAR(255) UNIQUE NOT NULL,
    subject VARCHAR(255) NOT NULL,
    quantity INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Dados Iniciais (8 livros):**
- 📚 Matemática (35 unidades)
- 📖 Português (42 unidades)
- 🏛️ História (28 unidades)
- 🧬 Ciências (8 unidades - CRÍTICO)
- 🌐 Inglês (25 unidades)
- 🗺️ Geografia (15 unidades)
- 🏃 Educação Física (5 unidades - CRÍTICO)
- 🎨 Artes (12 unidades)

### 2. **Tabela: movements**
```sql
CREATE TABLE movements (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
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

**Dados Iniciais (5 movimentos):**
- ✅ Entrada: 20 livros de Matemática
- ✅ Entrada: 15 livros de Português
- ❌ Saída: 8 livros de História
- ❌ Saída: 5 livros de Ciências
- ✅ Entrada: 10 livros de Inglês

---

## 🔗 Estrutura de Relações

### Book
```
book (1) ──→ (N) movements
```

**Usar assim:**
```php
$book = Book::find(1);
$movements = $book->movements; // Todos os movimentos do livro
```

### Movement
```
movement (N) ──→ (1) book
movement (N) ──→ (1) user
movement (N) ──→ (1) funcionario
```

**Usar assim:**
```php
$movement = Movement::find(1);
$book = $movement->book;
$user = $movement->user;
$funcionario = $movement->funcionario;
```

### User
```
user (1) ──→ (N) movements
```

**Usar assim:**
```php
$user = User::find(1);
$movements = $user->movements; // Todos os movimentos do usuário
```

---

## 🎯 Exemplos de Uso

### ✅ Queries Otimizadas (recomendado)

```php
// Obter movimentos com eager loading (evita N+1)
$movements = Movement::with(['book', 'user', 'funcionario'])->get();

// Obter livros com contagem de movimentos
$books = Book::withCount('movements')->get();

// Obter livros com estoque crítico
$critical = Book::where('quantity', '<', 10)->get();

// Paginar movimentos
$movements = Movement::with(['book', 'user'])
    ->latest('created_at')
    ->paginate(15);
```

### ❌ Queries NÃO Otimizadas (evitar)

```php
// Causa N+1 queries - EVITAR!
$movements = Movement::all();
foreach ($movements as $movement) {
    echo $movement->book->title; // Query executada aqui!
}
```

---

## 🧪 Testar Estrutura

### Via Tinker (REPL Interativo)
```bash
php artisan tinker
```

```php
# Ver dados
>>> Book::all();
>>> Movement::all();

# Contar registros
>>> Book::count();
=> 8
>>> Movement::count();
=> 5

# Acessar relacionamentos
>>> $book = Book::first();
>>> $book->title;
=> "Matemática Fundamental - Conceitos e Aplicações"
>>> $book->movements()->count();
=> 2

# Verificar estoque crítico
>>> Book::where('quantity', '<', 10)->pluck('title');

# Sair
>>> exit
```

### Via Testes Automatizados
```bash
php artisan test tests/Feature/BookMovementStructureTest.php
```

---

## ⚠️ Alertas Especiais

### 1. **Banco de Dados**
Certifique-se de que o arquivo `.env` está correto:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3308
DB_DATABASE=senaiStk
DB_USERNAME=root
DB_PASSWORD=
```

### 2. **Estoque Crítico**
Dois livros têm estoque crítico (< 10 unidades):
- 🧬 Ciências Naturais (8 unidades)
- 🏃 Educação Física (5 unidades)

**Ação recomendada:** Criar alerta ou reposição automática

### 3. **Integridade Referencial**
- Deletar um livro **remove automaticamente** seus movimentos (cascadeOnDelete)
- Deletar um usuário define seus movimentos como NULL (nullOnDelete)
- Deletar um funcionário define seus movimentos como NULL (nullOnDelete)

### 4. **Quantidade de Estoque**
- `quantity` é `unsignedInteger`: **nunca fica negativo**
- Validação deve ocorrer na aplicação (controllers/services)
- Sugestão: Usar transaction ao atualizar estoque

---

## 📁 Arquivos Criados

```
📂 database/
  📂 migrations/
    📄 2025_08_01_000001_create_books_table.php ✨ NOVO
    📄 2025_08_01_000002_create_movements_table.php ✨ NOVO
  📂 seeders/
    📄 BookSeeder.php ✨ NOVO
    📄 MovementSeeder.php ✨ NOVO
    📄 DatabaseSeeder.php 📝 MODIFICADO

📂 app/
  📂 Models/
    📄 Book.php ✨ NOVO
    📄 Movement.php ✨ NOVO
    📄 User.php 📝 MODIFICADO (adicionado relação movements)
  📄 OptimizedQueries.php ✨ NOVO

📂 tests/
  📂 Feature/
    📄 BookMovementStructureTest.php ✨ NOVO

📄 BANCO_DADOS_ESTRUTURA.md ✨ NOVO
📄 EXECUCAO_BANCO_DADOS.md ✨ NOVO (este arquivo)
📄 validate_structure.sh ✨ NOVO
```

---

## 🔄 Fluxo Typical de Desenvolvimento

### 1. Primeira Execução (Fresh Database)
```bash
# No diretório do projeto
cd C:\laragon\www\Senai_Project\breeze\senaiStock

# Fresh migration com seed
php artisan migrate:fresh --seed
```

### 2. Desenvolver Nova Feature
```bash
# Adicionar dados de teste
php artisan db:seed --class=BookSeeder

# Ou executar tudo de novo
php artisan migrate:refresh --seed
```

### 3. Antes de Commit/Push
```bash
# Verificar migrations
php artisan migrate:status

# Testar estrutura
php artisan test tests/Feature/BookMovementStructureTest.php

# Validar dados
php artisan tinker
>>> Book::count()
>>> Movement::count()
```

---

## 🐛 Troubleshooting

### Erro: "SQLSTATE[42S02]: Table 'senaiStk.books' doesn't exist"
**Solução:** Execute `php artisan migrate`

### Erro: "Foreign key constraint fails"
**Solução:** Certifique-se que as migration foram executadas na ordem correta:
```bash
php artisan migrate:status
```

### Erro: "Seeder não encontrado"
**Solução:** Verifique o namespace no arquivo seeder:
```php
namespace Database\Seeders;
```

### Base vazia (sem dados)
**Solução:** Execute o seeder:
```bash
php artisan db:seed
```

### Perdeu dados importante
**Solução:** Se tiver backup:
```bash
# Restaurar dados de backup
mysql -u root senaiStk < backup.sql
```

---

## 📞 Suporte

Para dúvidas ou problemas:

1. Verifique `BANCO_DADOS_ESTRUTURA.md` para documentação completa
2. Execute testes: `php artisan test`
3. Use Tinker para explorar dados: `php artisan tinker`
4. Verifique logs: `storage/logs/laravel.log`

---

## ✅ Checklist Pré-Deploy

- [ ] Executou `php artisan migrate`
- [ ] Executou `php artisan db:seed`
- [ ] Verificou contagem de livros (8) e movimentos (5)
- [ ] Testou relações entre models
- [ ] Verificou estoque crítico (2 livros)
- [ ] Rodou testes: `php artisan test`
- [ ] Validou estrutura: `./validate_structure.sh`
- [ ] Fez commit com mensagens claras

---

**Status:** ✅ Pronto para Usar

**Data:** 2025-08-01  
**Versão:** 1.0  
**Laravel:** 11.x  
**PHP:** 8.0+
