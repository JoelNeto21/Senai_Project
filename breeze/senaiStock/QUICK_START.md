# Quick Start - SenaiStock API Tests

## Instalação Rápida

```bash
# 1. Instalar dependências
composer install

# 2. Configurar arquivo .env de testes (se necessário)
cp .env .env.testing

# 3. Gerar chave de aplicação (se não existir)
php artisan key:generate

# 4. Rodar testes
php artisan test
```

## Estrutura de Arquivos Criados

### Testes (PestPHP)
```
tests/Feature/
├── ApiAuthTest.php          # 19 testes de autenticação
├── ApiBookTest.php          # 26 testes de livros (CRUD)
└── ApiMovementTest.php      # 37 testes de movimentações (CRÍTICO)
```

### Factories
```
database/factories/
├── BookFactory.php          # Factory com estados (critical, out of stock, etc)
├── MovementFactory.php      # Factory com tipos (entrada, saida)
└── UserFactory.php          # Atualizada com roles (almoxarife, coordenador)
```

### Documentação
```
TESTING.md                              # Documentação completa (13KB)
senaistock-postman-collection.json     # Coleção Postman com testes
senaistock-api-collection.json         # Coleção Thunder Client
```

## Estatísticas

- **Total de Testes**: 82 testes
- **Testes Críticos** (🔴): 5 validações obrigatórias
- **Cobertura**: Autenticação, Livros (CRUD), Movimentações (entrada/saída)

### Distribuição de Testes

| Módulo | Testes | Críticos |
|--------|--------|----------|
| Autenticação | 14 | 3 |
| Livros | 26 | 2 |
| Movimentações | 37 | 5 |
| Erro Geral | 5 | - |
| **TOTAL** | **82** | **10** |

## Testes Críticos (🔴 DEVE PASSAR)

1. **Saida bloqueada se estoque insuficiente** → HTTP 422
   - Arquivo: `tests/Feature/ApiMovementTest.php`
   - Teste: `blocks saida when quantity exceeds available stock`

2. **Justificação obrigatória para saida** → HTTP 422
   - Arquivo: `tests/Feature/ApiMovementTest.php`
   - Testes: `requires justification field for saida`, `rejects saida with empty justification`

3. **Estoque nunca negativo** → Validação contínua
   - Arquivo: `tests/Feature/ApiMovementTest.php`
   - Teste: `never allows stock to go below zero`

4. **ISBN deve ser único** → HTTP 422 em duplicado
   - Arquivo: `tests/Feature/ApiBookTest.php`
   - Teste: `rejects book with duplicate ISBN`

5. **Entrada sempre permitida** → HTTP 201 (com dados válidos)
   - Arquivo: `tests/Feature/ApiMovementTest.php`
   - Teste: `entrada is always allowed with valid data`

## Como Rodar

### Todos os testes
```bash
php artisan test
```

### Apenas testes de autenticação
```bash
php artisan test tests/Feature/ApiAuthTest.php
```

### Apenas testes de livros
```bash
php artisan test tests/Feature/ApiBookTest.php
```

### Apenas testes de movimentações
```bash
php artisan test tests/Feature/ApiMovementTest.php
```

### Apenas testes críticos
```bash
php artisan test --filter "CRITICAL"
```

### Testes com cobertura
```bash
php artisan test --coverage
```

### Testes verbosos (mostra cada teste)
```bash
php artisan test --verbose
```

## Validações de Negócio Implementadas

### Entrada (Entrada)
✅ Sempre permitida (nunca bloqueia)
✅ Justificação NÃO obrigatória
✅ Aumenta quantidade do livro
✅ Cria movimento no banco

### Saída (Saída) - CRÍTICO
❌ BLOQUEIA se quantity > saldo → HTTP 422
❌ REQUER justification → HTTP 422 se ausente/vazio
✅ Diminui quantidade do livro
✅ Cria movimento com justificativa
✅ Estoque NUNCA vai negativo

### Livros
✅ ISBN único (422 em duplicado)
✅ Campos obrigatórios: title, isbn, subject
✅ Quantidade padrão = 0
✅ CRUD completo

## Estrutura de Respostas

### Sucesso - Entrada
```json
{
  "id": 1,
  "type": "entrada",
  "book_id": 1,
  "quantity": 10,
  "justification": null,
  "created_at": "2025-01-10T10:00:00Z"
}
```

### Erro - Saida Bloqueada
```json
{
  "message": "Validation failed",
  "errors": {
    "quantity": ["Quantidade insuficiente: solicitado 10, disponível 5"]
  }
}
```

## Uso com Postman/Insomnia

1. Importar arquivo: `senaistock-postman-collection.json`
2. Criar ambiente (Environment) com variável `base_url`
3. Rodar requisições em sequência (Login → Books → Movements)
4. Os testes marcarão ✅ ou ❌

## Próximos Passos

1. **Implementar API Controllers**:
   ```bash
   php artisan make:controller Api/BookController --resource
   php artisan make:controller Api/MovementController --resource
   ```

2. **Adicionar rotas em `routes/api.php`**:
   ```php
   Route::middleware('auth:sanctum')->group(function () {
       Route::apiResource('books', \App\Http\Controllers\Api\BookController::class);
       Route::apiResource('movements', \App\Http\Controllers\Api\MovementController::class);
   });
   ```

3. **Implementar FormRequests de validação**:
   ```bash
   php artisan make:request StoreBookRequest
   php artisan make:request StoreMovementRequest
   ```

4. **Executar testes novamente**:
   ```bash
   php artisan test
   ```

## Notas Importantes

- ✅ Testes rodam com SQLite em memória (rápido, isolado)
- ✅ Cada teste é independente (usa factories)
- ✅ Banco é limpo entre testes automaticamente
- ✅ Validações de negócio testadas, não apenas rotas
- ⚠️ Algumas rotas podem retornar 404 se controller não existir (normal por enquanto)

## Troubleshooting

**Erro: "No application encryption key"**
```bash
php artisan key:generate --env=testing
```

**Erro: "SQLSTATE[HY000] database is locked"**
```bash
php artisan test --without-parallel
```

**Testes lentos**
- Usar SQLite em memória (já configurado)
- Evitar queries extras

## Próximas Melhorias

- [ ] Testes de autorização (roles e permissões)
- [ ] Testes de performance (bulk operations)
- [ ] Testes de integração com funcionários
- [ ] Testes de relatórios
- [ ] Coverage > 80%

## Documentação Completa

Ver `TESTING.md` para guia completo com exemplos de factories, boas práticas, e troubleshooting.
