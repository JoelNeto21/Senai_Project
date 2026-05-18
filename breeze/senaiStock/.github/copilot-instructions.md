# SenaiStock — Instruções Globais do Copilot

## Contexto do Projeto
- **Projeto:** SenaiStock — API RESTful de controle de estoque de livros didáticos
- **Stack:** Laravel 11 + PHP + MySQL + Breeze (autenticação)
- **ORM:** Eloquent
- **Ambiente local:** Laragon (Windows) em C:\laragon\www\Senai_Project\breeze\senaiStock
- **Repositório:** https://github.com/JoelNeto21/Senai_Project
- **Padrão de resposta:** JSON em todas as rotas de API
- **Autenticação:** Token via Laravel Sanctum (ou Breeze API)

## Entidades Principais
- **Users:** almoxarife e coordenador (campo `role`)
- **Books:** título, ISBN, matéria, `quantity` (saldo atual)
- **Movements:** tipo (entrada/saída), `book_id`, `user_id`, quantidade, justificativa

## Regras de Negócio — NUNCA violar
1. Saída bloqueada se quantidade > saldo disponível (retornar HTTP 422)
2. Estoque crítico = abaixo de 10 unidades
3. Toda saída exige campo `justification` não nulo
4. Apenas usuários autenticados alteram estoque

## Convenções de Código
- Controllers em `app/Http/Controllers/Api/`
- Resources em `app/Http/Resources/`
- Form Requests em `app/Http/Requests/`
- Sempre usar Form Request para validação (nunca validar no controller)
- Retornar `JsonResource` em vez de arrays crus
- Usar `$table->foreignId()->constrained()->cascadeOnDelete()` nas migrations

## Modelo de Resposta Padrão
```json
{
  "message": "Descrição da ação",
  "data": { ... }
}
```
Em erro de validação: HTTP 422 com `{ "message": "...", "errors": { ... } }`

## O que NÃO fazer
- Não usar `dd()` ou `dump()` em produção
- Não retornar `response()->json($model)` diretamente — usar Resources
- Não criar lógica de negócio no Controller — usar Services ou Actions
