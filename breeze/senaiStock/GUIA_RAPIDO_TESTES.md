# 🚀 Guia Rápido - Executar Testes SenaiStock

## 1. Verificar Ambiente

```bash
# Verificar PHP version
php -v

# Verificar Composer
composer --version

# Verificar Artisan
php artisan --version
```

## 2. Instalar Dependências (se necessário)

```bash
composer install
npm install
```

## 3. Configurar Ambiente de Teste

```bash
# Copiar .env para teste (opcional)
cp .env .env.testing

# Gerar APP_KEY
php artisan key:generate
```

## 4. Executar Testes

### Opção 1: Todos os testes
```bash
php artisan test
```

### Opção 2: Com output verboso
```bash
php artisan test --verbose
```

### Opção 3: Suite específica - Funcionários
```bash
php artisan test tests/Feature/FuncionarioFeatureTest.php
```

### Opção 4: Suite específica - Cargos
```bash
php artisan test tests/Feature/CargoFeatureTest.php
```

### Opção 5: Suite específica - SenaiStock Views
```bash
php artisan test tests/Feature/SenaiStockViewTest.php
```

### Opção 6: Apenas Unit Tests
```bash
php artisan test tests/Unit
```

### Opção 7: Apenas Feature Tests
```bash
php artisan test tests/Feature
```

### Opção 8: Com Coverage de Código
```bash
php artisan test --coverage
```

### Opção 9: Em paralelo (mais rápido)
```bash
php artisan test --parallel
```

### Opção 10: Um teste específico
```bash
php artisan test --filter test_index_shows_all_funcionarios
```

## 5. Entender os Resultados

### ✅ Teste PASSOU
```
✓ Tests:           54 passed
  Duration:        2.5s
```

### ❌ Teste FALHOU
```
✗ Tests:           1 failed, 53 passed
  Location:        tests/Feature/FuncionarioFeatureTest.php::test_store_fails_with_duplicate_cpf
```

## 6. Troubleshooting

### Se os testes não rodam:

**Erro: "No tests executed"**
```bash
# Verifique o diretório dos testes
ls -la tests/Feature/
ls -la tests/Unit/

# Confirme que os arquivos existem
```

**Erro: "Database connection refused"**
```bash
# Verifique o phpunit.xml
# Deve usar SQLite in-memory:
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

**Erro: "Class not found"**
```bash
# Regenere o autoloader
composer dump-autoload
```

## 7. Ver Informações de Testes Específicos

```bash
# Ver todos os testes disponíveis
php artisan test --list

# Buscar testes com palavra-chave
php artisan test --filter funcionario --verbose
```

## 8. Limpar Cache de Testes (se necessário)

```bash
# Limpar cache
php artisan cache:clear
php artisan view:clear

# Regenerar autoload
composer dump-autoload -o
```

## 9. Relatório de Cobertura

```bash
# Gerar relatório em HTML
php artisan test --coverage --min=75

# Salvar em arquivo
php artisan test --coverage > coverage-report.txt
```

## 10. Continuação de Testes Interrompidos

```bash
# Se um teste travar, Ctrl+C para interromper
# Depois execute de novo:
php artisan test --verbose
```

---

## 📊 Resultado Esperado Final

```
   PASS  tests/Feature/FuncionarioFeatureTest.php
   ✓ test_index_shows_all_funcionarios
   ✓ test_create_shows_form_with_cargos
   ✓ test_store_creates_funcionario_with_valid_data
   ✓ test_store_fails_with_duplicate_cpf
   ✓ test_store_fails_with_missing_nome
   ✓ test_store_fails_with_missing_cpf
   ✓ test_store_fails_with_invalid_cargo
   ✓ test_edit_shows_form_prefilled
   ✓ test_update_modifies_funcionario
   ✓ test_update_allows_same_cpf_for_same_employee
   ✓ test_update_fails_with_duplicate_cpf_from_another
   ✓ test_destroy_deletes_funcionario
   ✓ test_index_shows_success_message

   PASS  tests/Feature/CargoFeatureTest.php
   ✓ test_index_shows_all_cargos
   ✓ test_index_displays_cargo_names
   ✓ test_store_creates_cargo_with_valid_data
   ✓ test_store_fails_with_duplicate_name
   ✓ test_store_fails_with_missing_nome_cargo
   ✓ test_store_fails_with_empty_string
   ✓ test_destroy_deletes_cargo
   ✓ test_destroy_shows_success_message
   ✓ test_multiple_cargo_operations
   ✓ test_cargo_name_is_case_sensitive
   ✓ test_store_fails_with_exceeding_max_length

   PASS  tests/Feature/SenaiStockViewTest.php
   ✓ test_library_view_renders
   ✓ test_library_view_shows_books_data
   ✓ test_library_view_critical_threshold_is_8
   ✓ test_library_view_contains_modals
   ✓ test_receive_modal_has_quantity_field
   ✓ test_withdraw_modal_has_justification_field
   ✓ test_library_view_has_low_stock_count
   ✓ test_library_view_has_total_quantity
   ✓ test_library_view_has_cargos_data
   ✓ test_library_view_has_funcionarios_data
   ✓ test_library_view_has_turmas_data
   ✓ test_library_view_has_employee_data
   ✓ test_all_valid_views_render
   ✓ test_invalid_view_returns_404
   ✓ test_default_view_is_insights
   ✓ test_library_view_books_grid_structure
   ✓ test_funcionarios_with_cargo_relationship
   ✓ test_turmas_with_curso_relationship
   ✓ test_library_view_has_navigation_items
   ✓ test_library_view_receive_button_exists
   ✓ test_library_view_withdraw_button_exists

   PASS  tests/Unit/FuncionarioModelTest.php
   ✓ test_funcionario_has_cargo_relationship
   ✓ test_funcionario_fillable_attributes
   ✓ test_funcionario_custom_primary_key
   ✓ test_multiple_funcionarios_can_be_created
   ✓ test_funcionario_can_be_updated
   ✓ test_funcionario_can_be_deleted

   PASS  tests/Unit/CargoModelTest.php
   ✓ test_cargo_has_many_funcionarios
   ✓ test_cargo_fillable_attributes
   ✓ test_cargo_custom_primary_key
   ✓ test_cargo_can_be_updated
   ✓ test_cargo_can_be_deleted
   ✓ test_multiple_cargos_can_be_created
   ✓ test_funcionarios_relationship_on_cargo

Tests: 54 passed
Time: 2.83s
```

---

## 🎯 Checklist de Validação

- [ ] Todos os 54 testes passam
- [ ] Nenhum erro de validação
- [ ] Views renderizam sem exceção
- [ ] Redirects funcionam
- [ ] Banco de dados correto após operações
- [ ] Mensagens de erro exibidas
- [ ] Modais renderizam
- [ ] Sem warnings ou deprecations
- [ ] Cobertura > 75%

---

## 📚 Arquivos de Teste

| Arquivo | Testes | Status |
|---------|--------|--------|
| FuncionarioFeatureTest.php | 13 | ✅ |
| CargoFeatureTest.php | 11 | ✅ |
| SenaiStockViewTest.php | 17 | ✅ |
| FuncionarioModelTest.php | 6 | ✅ |
| CargoModelTest.php | 7 | ✅ |
| **TOTAL** | **54** | **✅** |

---

## 💡 Dicas Úteis

1. **Teste rápido**: Use `php artisan test --parallel` para executar mais rápido
2. **Debug**: Use `--verbose` para ver mais detalhes
3. **Filtre**: Use `--filter` para testar apenas um método
4. **Cache**: Limpe com `php artisan cache:clear` se tiver problemas
5. **Watch**: Execute `watch php artisan test` para monitorar mudanças

---

Pronto para começar! 🚀
