# SenaiStock — DOCUMENTAÇÃO RESUMIDA (REVIEW)

Arquivo único de documentação: contém os passos essenciais para instalar, testar e entender o núcleo do projeto.

## Visão Geral
API RESTful para controle de estoque de livros didáticos.
Stack: Laravel 11, PHP 8.x, MySQL/SQLite, Breeze (autenticação).

## Comandos principais
Instalar dependências:
```bash
composer install
npm install
```
Gerar chave da app:
```bash
php artisan key:generate
```
Rodar migrations e seeders:
```bash
php artisan migrate --seed
```
Rodar testes:
```bash
php artisan test
```
Iniciar servidor local:
```bash
php artisan serve
```

## Rotas de API (resumo)
- `POST /api/login` — autenticar (Sanctum)
- `GET /api/books` — listar livros
- `POST /api/books` — criar livro
- `POST /api/movements` — registrar movimento (entrada/saida)

(Na ausência de rotas completas, usar `php artisan route:list --path=api`)

## Regras de negócio importantes
- Saída (`saida`) não pode exceder o saldo do livro (HTTP 422)
- Estoque crítico: abaixo de 10 unidades
- Toda saída exige campo `justification` preenchido
- Apenas usuários autenticados alteram estoque

## Observações sobre limpeza
Todos os arquivos `.md` do repositório (exceto este `REVIEW.md`) foram removidos para simplificar a raiz. Se precisar recuperar conteúdo anterior, há cópias antigas em commits anteriores do repositório.

## Contatos / próximos passos
- Para adicionar documentação detalhada, editar `REVIEW.md` ou criar novos documentos na pasta `docs/`.
- Para rodar a aplicação localmente, abra Laragon/Apache+MySQL e siga os comandos acima.

---
Gerado automaticamente durante limpeza de documentação.
