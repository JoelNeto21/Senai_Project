# ⚡ COMANDOS EXATOS PARA EXECUTAR

## 🎯 Objetivo
Configurar completamente a estrutura de banco de dados do SenaiStock com Books e Movements.

---

## 📋 Pré-requisitos
- ✅ Laragon rodando (PHP + MySQL)
- ✅ Projeto em: `C:\laragon\www\Senai_Project\breeze\senaiStock`
- ✅ `.env` configurado (verificar DB_DATABASE=senaiStk)
- ✅ Todos os arquivos criados (ver ARQUIVO_VALIDACAO.md)

---

## 🚀 COMANDOS PARA EXECUTAR (em ordem)

### 1️⃣ Abrir Terminal na Pasta do Projeto

```bash
cd C:\laragon\www\Senai_Project\breeze\senaiStock
```

**ou navegue até a pasta e abra terminal lá**

---

### 2️⃣ Executar Migrations

```bash
php artisan migrate
```

**Resultado esperado:**
```
Migration  Batch
────────────────────────────────────────
2025_08_01_000001_create_books_table    1
2025_08_01_000002_create_movements_table 1
```

✅ **Se funcionou:** Vá para o próximo passo  
❌ **Se deu erro:** Verifique `.env` e banco de dados

---

### 3️⃣ Executar Seeders

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

✅ **Se funcionou:** Vá para o próximo passo  
❌ **Se deu erro:** Verifique dados dos seeders

---

### 4️⃣ Testar a Estrutura (Opcional mas Recomendado)

```bash
php artisan test tests/Feature/BookMovementStructureTest.php
```

**Resultado esperado:**
```
PASS  Tests\Feature\BookMovementStructureTest
  ✓ book model has correct fillable properties
  ✓ movement model has correct fillable properties
  ✓ book has many movements
  ✓ movement belongs to book
  ✓ movement belongs to user
  ✓ movement belongs to funcionario
  ✓ book critical stock check
  ✓ book out of stock check
  ✓ movement is entry
  ✓ movement is exit
  ✓ user has many movements

Tests: 21 passed
```

✅ **Se funcionou:** Estrutura está perfeita!  
❌ **Se falhou:** Verifique models

---

### 5️⃣ Verificar Dados no Banco (Opcional)

```bash
php artisan tinker
```

**Dentro do Tinker, execute:**

```php
>>> Book::count()
=> 8

>>> Movement::count()
=> 5

>>> Book::with('movements')->first()
=> Book {
     id: 1,
     title: "Matemática Fundamental - Conceitos e Aplicações",
     isbn: "978-8534965124",
     subject: "Matemática",
     quantity: 35,
     created_at: "2025-08-01 12:00:00",
     updated_at: "2025-08-01 12:00:00",
   }

>>> exit
```

✅ **Se as contagens estão corretas:** Tudo perfeito!

---

## 🔄 COMANDOS ALTERNATIVOS (Se Algo Deu Errado)

### Reset Completo (⚠️ Deleta Tudo!)

```bash
# Desfaz todas as migrations e as refaz
php artisan migrate:refresh

# Ou desfaz, refaz e faz seed
php artisan migrate:refresh --seed

# Ou apaga tudo, cria tudo, planta dados
php artisan migrate:fresh --seed
```

**⚠️ ATENÇÃO:** Estes comandos deletam TODOS os dados!

---

### Apenas Reverter Migrations

```bash
# Reverter última execução de migration
php artisan migrate:rollback

# Reverter todas as migrations
php artisan migrate:reset
```

---

### Ver Status das Migrations

```bash
# Ver quais foram executadas
php artisan migrate:status

# Ver quais estão pendentes
php artisan migrate:status --pending
```

---

### Executar Apenas um Seeder

```bash
# Apenas Books
php artisan db:seed --class=BookSeeder

# Apenas Movements
php artisan db:seed --class=MovementSeeder

# Apenas Database (todos)
php artisan db:seed --class=DatabaseSeeder
```

---

## ✅ CHECKLIST DE SUCESSO

Após executar os comandos, verificar:

- [ ] `php artisan migrate` executou sem erros
- [ ] Viu as 2 migrations criadas
- [ ] `php artisan db:seed` executou sem erros
- [ ] Viu BookSeeder sendo executado
- [ ] `php artisan test` passou com 21 testes
- [ ] `php artisan tinker` mostrou 8 livros
- [ ] `php artisan tinker` mostrou 5 movimentos

Se tudo acima está feito ✅, então **SUCESSO!**

---

## 🐛 TROUBLESHOOTING

### Erro: "SQLSTATE[42S02]: Table 'senaiStk.books' doesn't exist"
```bash
# Solução: Executar migrate
php artisan migrate
```

### Erro: "Connection refused" ou "Unknown database 'senaiStk'"
```bash
# Verificar .env
cat .env | grep DB_

# Deve mostrar:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3308
# DB_DATABASE=senaiStk
# DB_USERNAME=root
# DB_PASSWORD=
```

### Erro: "Foreign key constraint fails"
```bash
# Solução: Migrations fora de ordem
# Executar reset completo
php artisan migrate:refresh --seed
```

### Erro: "Class not found" em seeder
```bash
# Solução: Testar syntax dos arquivos
php -l database/seeders/BookSeeder.php
php -l database/seeders/MovementSeeder.php
```

### Banco não tem dados
```bash
# Solução: Executar seed explicitamente
php artisan db:seed
```

---

## 📊 VERIFICAR DADOS DIRETO NO MYSQL (Avançado)

```bash
# Abrir MySQL no terminal
mysql -u root -p senaiStk

# Dentro do MySQL:
mysql> SHOW TABLES;
mysql> SELECT COUNT(*) FROM books;
mysql> SELECT COUNT(*) FROM movements;
mysql> SELECT * FROM books LIMIT 2;
mysql> SELECT * FROM movements LIMIT 2;
mysql> exit;
```

---

## 🎯 FLUXO COMPLETO RESUMIDO

```bash
# 1. Ir para pasta do projeto
cd C:\laragon\www\Senai_Project\breeze\senaiStock

# 2. Migrar banco de dados
php artisan migrate

# 3. Popular dados de teste
php artisan db:seed

# 4. (Opcional) Testar estrutura
php artisan test tests/Feature/BookMovementStructureTest.php

# 5. (Opcional) Explorar dados
php artisan tinker

# ✅ PRONTO!
```

---

## 🚀 PÓS-EXECUÇÃO (Próximos Passos)

### 1. Fazer Commit no Git
```bash
git add .
git commit -m "feat: Criar estrutura de banco de dados para Books e Movements

- Migrations criadas
- Models implementados
- Seeders com dados realistas
- Testes inclusos
- Documentação completa

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"

git push origin main
```

### 2. Criar Controllers (Próxima Etapa)
```bash
php artisan make:controller BookController --resource
php artisan make:controller MovementController --resource
```

### 3. Definir Rotas API (Próxima Etapa)
```bash
# Editar routes/api.php
Route::apiResources([
    'books' => BookController::class,
    'movements' => MovementController::class,
]);
```

---

## 📞 DOCUMENTAÇÃO DE REFERÊNCIA

| Arquivo | Leia Quando... |
|---------|----------------|
| BANCO_DADOS_ESTRUTURA.md | Precisa entender estrutura técnica |
| EXECUCAO_BANCO_DADOS.md | Quer detalhes sobre execução |
| RESUMO_EXECUTIVO.md | Quer overview rápido |
| ARQUIVO_VALIDACAO.md | Quer verificar tudo que foi feito |
| app/OptimizedQueries.php | Quer ver queries otimizadas |
| GIT_COMMIT_INSTRUCTIONS.md | Quer fazer commit |

---

## ✨ RESUMO

```
╔════════════════════════════════════╗
║ Comandos Principais                ║
╠════════════════════════════════════╣
║ php artisan migrate                ║
║ php artisan db:seed                ║
║ php artisan test                   ║
║ php artisan tinker                 ║
╚════════════════════════════════════╝
```

---

## ✅ Status

Se você chegou aqui e executou tudo acima ✅, então:

```
🎉 ESTRUTURA DE BANCO ESTÁ PRONTA PARA USO! 🎉

- ✅ Tabelas criadas
- ✅ Relacionamentos funcionando
- ✅ Dados de teste populados
- ✅ Testes passando
- ✅ Pronto para desenvolvimento
```

---

**Última Atualização:** 2025-08-01  
**Versão:** 1.0  
**Status:** ✅ Pronto para Usar
