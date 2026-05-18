# 📚 Documentação Detalhada dos Testes - SenaiStock Views

## 📖 Índice

1. [Arquitetura dos Testes](#arquitetura)
2. [Estrutura AAA (Arrange-Act-Assert)](#aaa)
3. [Exemplos Detalhados](#exemplos)
4. [Factory Pattern](#factories)
5. [Assertions Disponíveis](#assertions)
6. [Boas Práticas](#boas-práticas)

---

## 🏗️ Arquitetura dos Testes {#arquitetura}

### Estrutura de Diretórios

```
tests/
├── Feature/                          # Testes de integração
│   ├── FuncionarioFeatureTest.php    # Testes CRUD Funcionários
│   ├── CargoFeatureTest.php          # Testes CRUD Cargos
│   └── SenaiStockViewTest.php        # Testes Views Blade
├── Unit/                             # Testes unitários
│   ├── FuncionarioModelTest.php      # Testes Modelo Funcionario
│   └── CargoModelTest.php            # Testes Modelo Cargo
└── TestCase.php                      # Base para todos os testes
```

### Diferença Feature vs Unit

| Feature Tests | Unit Tests |
|--------------|-----------|
| Testam fluxos completos | Testam componentes isolados |
| Usam banco de dados real | Não dependem de BD |
| Testam controllers + views | Testam apenas models/classes |
| ~15-50ms por teste | ~1-5ms por teste |
| Mais realistas | Mais rápidos |

---

## 🎯 Padrão AAA (Arrange-Act-Assert) {#aaa}

### Conceito

Todo teste segue 3 etapas:

```php
public function test_create_funcionario(): void
{
    // 1. ARRANGE - Preparar dados de teste
    $cargo = Cargo::factory()->create();
    $funcionarioData = [
        'Nome' => 'João Silva',
        'Cpf' => '12345678901',
        'Id_cargo_FK' => $cargo->Id_cargo,
    ];

    // 2. ACT - Executar a ação
    $response = $this->post(
        route('funcionarios.store'),
        $funcionarioData
    );

    // 3. ASSERT - Verificar resultados
    $response->assertRedirect(route('funcionarios.index'));
    $this->assertDatabaseHas('funcionarios', $funcionarioData);
}
```

### Por que AAA?

✅ **Clareza**: Cada seção tem propósito claro
✅ **Manutenibilidade**: Fácil entender e modificar
✅ **Isolamento**: Cada teste é independente
✅ **Documentação**: Código funciona como documentação

---

## 💻 Exemplos Detalhados {#exemplos}

### Exemplo 1: Teste de Listagem

```php
public function test_index_shows_all_funcionarios(): void
{
    // ARRANGE - Criar dados de teste
    // Cria 3 funcionários com seus cargos
    $funcionarios = Funcionario::factory()
        ->count(3)
        ->create();

    // ACT - Fazer requisição HTTP
    // Simula: GET /funcionarios
    $response = $this->get(route('funcionarios.index'));

    // ASSERT - Verificar resultados
    // 1. Resposta deve ser 200 OK
    $response->assertStatus(200);
    
    // 2. View deve ter dados dos funcionários
    $response->assertViewHas('funcionarios');
    
    // 3. Cada funcionário deve estar visível
    foreach ($funcionarios as $funcionario) {
        $response->assertSee($funcionario->Nome);
        $response->assertSee($funcionario->Cpf);
        $response->assertSee($funcionario->cargo->Nome_cargo);
    }
}
```

**O que testa:**
- ✅ GET /funcionarios retorna 200
- ✅ View tem dados corretos
- ✅ Todos os funcionários aparecem
- ✅ Relacionamento cargo funciona

**Tempo:** ~20ms

---

### Exemplo 2: Teste de Criação com Sucesso

```php
public function test_store_creates_funcionario_with_valid_data(): void
{
    // ARRANGE - Preparar dados válidos
    $cargo = Cargo::first(); // Cargo criado no setUp()
    
    $funcionarioData = [
        'Nome' => 'Maria Santos',
        'Cpf' => '98765432100',
        'Id_cargo_FK' => $cargo->Id_cargo,
    ];

    // ACT - Enviar POST request
    // Simula: POST /funcionarios com dados
    $response = $this->post(
        route('funcionarios.store'),
        $funcionarioData
    );

    // ASSERT - Verificar resultado
    // 1. Deve redirecionar (302)
    $response->assertRedirect(route('funcionarios.index'));
    
    // 2. Deve ter mensagem de sucesso
    $response->assertSessionHas('success');
    
    // 3. Funcionário deve estar no banco
    $this->assertDatabaseHas('funcionarios', [
        'Nome' => 'Maria Santos',
        'Cpf' => '98765432100',
    ]);
    
    // 4. Banco deve ter 1 novo registro
    $this->assertCount(1, Funcionario::all());
}
```

**O que testa:**
- ✅ POST aceita dados válidos
- ✅ Redirect funciona
- ✅ Mensagem de sucesso é armazenada
- ✅ Dados são salvos no banco
- ✅ Apenas 1 registro criado

**Tempo:** ~15ms

---

### Exemplo 3: Teste de Falha de Validação

```php
public function test_store_fails_with_duplicate_cpf(): void
{
    // ARRANGE - Criar funcionário com CPF específico
    Funcionario::factory()->create([
        'Cpf' => '11111111111'
    ]);

    // Tentar criar outro com mesmo CPF
    $cargo = Cargo::first();
    $funcionarioData = [
        'Nome' => 'Outro Funcionário',
        'Cpf' => '11111111111', // ← CPF duplicado!
        'Id_cargo_FK' => $cargo->Id_cargo,
    ];

    // ACT - Enviar POST com dados inválidos
    $response = $this->post(
        route('funcionarios.store'),
        $funcionarioData
    );

    // ASSERT - Deve falhar validação
    // 1. Status deve ser 422 (Unprocessable Entity)
    $response->assertStatus(422);
    
    // 2. Deve ter erro no campo Cpf
    $response->assertSessionHasErrors('Cpf');
    
    // 3. Funcionário NÃO deve ser criado
    $this->assertCount(1, Funcionario::all());
}
```

**O que testa:**
- ✅ Validação rejeita CPF duplicado
- ✅ Status HTTP correto (422)
- ✅ Mensagem de erro existe
- ✅ Registro não é criado
- ✅ Banco não é alterado

**Tempo:** ~10ms

---

### Exemplo 4: Teste de Relacionamento

```php
public function test_funcionario_has_cargo_relationship(): void
{
    // ARRANGE - Criar cargo e funcionário relacionado
    $cargo = Cargo::factory()->create([
        'Nome_cargo' => 'Gerente'
    ]);
    
    $funcionario = Funcionario::factory()->create([
        'Id_cargo_FK' => $cargo->Id_cargo
    ]);

    // ACT - Acessar relacionamento
    $cargoFromRelation = $funcionario->cargo;

    // ASSERT - Verificar relacionamento
    // 1. Cargo não deve ser null
    $this->assertNotNull($cargoFromRelation);
    
    // 2. IDs devem coincidir
    $this->assertEquals(
        $cargo->Id_cargo,
        $cargoFromRelation->Id_cargo
    );
    
    // 3. Dados devem coincidir
    $this->assertEquals(
        'Gerente',
        $cargoFromRelation->Nome_cargo
    );
}
```

**O que testa:**
- ✅ Relacionamento BelongsTo funciona
- ✅ Dados corretos são retornados
- ✅ Foreign key correto
- ✅ Modelo carrega relacionamento

**Tempo:** ~5ms

---

### Exemplo 5: Teste de Update

```php
public function test_update_modifies_funcionario(): void
{
    // ARRANGE - Criar funcionário para editar
    $funcionario = Funcionario::factory()->create([
        'Nome' => 'Nome Original'
    ]);
    
    $newCargo = Cargo::factory()->create();
    
    $updatedData = [
        'Nome' => 'Nome Atualizado',
        'Cpf' => '55555555555',
        'Id_cargo_FK' => $newCargo->Id_cargo,
    ];

    // ACT - Enviar PUT request
    // Simula: PUT /funcionarios/{id}
    $response = $this->put(
        route('funcionarios.update', $funcionario),
        $updatedData
    );

    // ASSERT - Verificar atualização
    // 1. Deve redirecionar
    $response->assertRedirect(route('funcionarios.index'));
    
    // 2. Deve ter mensagem de sucesso
    $response->assertSessionHas('success', 'Dados atualizados!');
    
    // 3. Dados devem estar atualizados no banco
    $this->assertDatabaseHas('funcionarios', [
        'Id_funcionario' => $funcionario->Id_funcionario,
        'Nome' => 'Nome Atualizado',
        'Cpf' => '55555555555',
    ]);
    
    // 4. Verifica que apenas 1 funcionário foi alterado
    $this->assertEquals(
        'Nome Atualizado',
        $funcionario->fresh()->Nome
    );
}
```

**O que testa:**
- ✅ PUT request aceita dados válidos
- ✅ Redirect funciona
- ✅ Mensagem de sucesso é exibida
- ✅ Dados são atualizados no banco
- ✅ Apenas 1 registro é modificado

**Tempo:** ~15ms

---

### Exemplo 6: Teste de Delete

```php
public function test_destroy_deletes_funcionario(): void
{
    // ARRANGE - Criar funcionário para deletar
    $funcionario = Funcionario::factory()->create();
    $funcionarioId = $funcionario->Id_funcionario;
    
    // Verificar que existe no banco
    $this->assertDatabaseHas('funcionarios', [
        'Id_funcionario' => $funcionarioId
    ]);

    // ACT - Enviar DELETE request
    // Simula: DELETE /funcionarios/{id}
    $response = $this->delete(
        route('funcionarios.destroy', $funcionario)
    );

    // ASSERT - Verificar exclusão
    // 1. Deve redirecionar
    $response->assertRedirect(route('funcionarios.index'));
    
    // 2. Deve ter mensagem de sucesso
    $response->assertSessionHas('success', 'Funcionário removido.');
    
    // 3. Funcionário NÃO deve existir mais
    $this->assertDatabaseMissing('funcionarios', [
        'Id_funcionario' => $funcionarioId
    ]);
    
    // 4. Banco deve estar vazio
    $this->assertNull(Funcionario::find($funcionarioId));
}
```

**O que testa:**
- ✅ DELETE request funciona
- ✅ Redirect correto
- ✅ Mensagem de sucesso
- ✅ Registro é removido do banco
- ✅ Não pode recuperar deletado

**Tempo:** ~12ms

---

## 🏭 Factory Pattern {#factories}

### O que é Factory?

Factory é um classe que gera dados de teste realistas:

```php
// Sem Factory (ruim)
$funcionario = new Funcionario([
    'Nome' => 'João',
    'Cpf' => '12345678901',
    'Id_cargo_FK' => 1,
]);

// Com Factory (bom)
$funcionario = Funcionario::factory()->create();
```

### FuncionarioFactory

```php
<?php

namespace Database\Factories;

use App\Models\Funcionario;
use App\Models\Cargo;

class FuncionarioFactory extends Factory
{
    protected $model = Funcionario::class;

    public function definition(): array
    {
        return [
            'NIF' => $this->faker->unique()->numerify('##########'),
            'Nome' => $this->faker->name(),
            'Cpf' => $this->faker->unique()->numerify('###########'),
            'Id_cargo_FK' => Cargo::factory(),
        ];
    }
}
```

### Usando Factories

```php
// Criar 1 funcionário
$func = Funcionario::factory()->create();

// Criar 5 funcionários
$funcs = Funcionario::factory()->count(5)->create();

// Criar com atributos customizados
$func = Funcionario::factory()->create([
    'Nome' => 'João Customizado',
    'Cpf' => '12345678901',
]);

// Criar sem salvar no banco (testing em memória)
$func = Funcionario::factory()->make();

// Com relacionamento customizado
$cargo = Cargo::first();
$func = Funcionario::factory()->create([
    'Id_cargo_FK' => $cargo->Id_cargo,
]);
```

---

## ✅ Assertions Disponíveis {#assertions}

### Assertions HTTP

```php
// Status codes
$response->assertStatus(200);              // Sucesso
$response->assertStatus(302);              // Redirect
$response->assertStatus(422);              // Validação falhou
$response->assertStatus(404);              // Não encontrado
$response->assertStatus(500);              // Erro servidor

// Shortcuts
$response->assertOk();                     // 200
$response->assertCreated();                // 201
$response->assertNoContent();              // 204
$response->assertUnprocessable();          // 422
$response->assertNotFound();               // 404
```

### Assertions de Redirecionamento

```php
$response->assertRedirect();               // Qualquer redirect
$response->assertRedirect('/home');        // Redirect específico
$response->assertRedirect(route('home'));  // Pelo route name
```

### Assertions de Sessão

```php
$response->assertSessionHas('key');        // Key existe
$response->assertSessionHas('key', 'value'); // Key com valor
$response->assertSessionHasErrors('field'); // Erro em campo
$response->assertSessionMissing('key');    // Key não existe
```

### Assertions de View

```php
$response->assertViewHas('variavel');      // View tem variável
$response->assertViewHas('var', 'value');  // Com valor específico
$response->assertViewMissing('variavel');  // View não tem
$response->assertViewIs('view.name');      // View name específica
```

### Assertions de Conteúdo

```php
$response->assertSee('text');              // Contém texto
$response->assertSee('text', false);       // HTML não escapado
$response->assertDontSee('text');          // Não contém texto
$response->assertSeeInOrder(['a', 'b']);   // Ordem específica
```

### Assertions de Banco de Dados

```php
$this->assertDatabaseHas('users', [        // Registro existe
    'email' => 'test@example.com'
]);

$this->assertDatabaseMissing('users', [    // Registro não existe
    'email' => 'test@example.com'
]);

$this->assertDatabaseCount('users', 5);    // Contagem exata
```

### Assertions Gerais

```php
$this->assertTrue($bool);                  // É verdadeiro
$this->assertFalse($bool);                 // É falso
$this->assertNull($value);                 // É null
$this->assertNotNull($value);              // Não é null
$this->assertEquals($expected, $actual);   // Iguais
$this->assertNotEquals($a, $b);            // Diferentes
$this->assertCount(5, $collection);        // Contagem
$this->assertIsArray($value);              // É array
```

---

## 🎓 Boas Práticas {#boas-práticas}

### 1. Naming Convention

```php
// ✅ BOM - Claro e descritivo
public function test_store_fails_with_duplicate_cpf(): void { }
public function test_update_allows_same_cpf_for_same_employee(): void { }

// ❌ RUIM - Vago
public function test_storage(): void { }
public function test_update_cpf(): void { }
```

### 2. Um Conceito por Teste

```php
// ✅ BOM - Testa apenas uma coisa
public function test_store_fails_with_duplicate_cpf(): void
{
    // Testa: CPF duplicado → erro 422
}

// ❌ RUIM - Testa múltiplos conceitos
public function test_store_validation(): void
{
    // Testa CPF, Nome, Cargo, etc tudo junto
}
```

### 3. Dados Realistas

```php
// ✅ BOM - Usa Faker
$name = $this->faker->name();
$cpf = $this->faker->unique()->numerify('###########');

// ❌ RUIM - Dados hardcoded
$name = 'A';
$cpf = '123';
```

### 4. Isolamento

```php
// ✅ BOM - Cada teste cria seus dados
public function test_create_funcionario(): void
{
    $cargo = Cargo::factory()->create();
    // ...
}

public function test_update_funcionario(): void
{
    $cargo = Cargo::factory()->create();
    // ...
}

// ❌ RUIM - Teste depende de outro
public function test_create_funcionario(): void
{
    // ...
}

public function test_update_funcionario(): void
{
    // Depende do resultado de test_create_funcionario
}
```

### 5. Assertion Específicas

```php
// ✅ BOM - Assertions específicas
$response->assertStatus(422);
$response->assertSessionHasErrors('Cpf');
$this->assertDatabaseCount('funcionarios', 1);

// ❌ RUIM - Muito genérico
$this->assertTrue($response->status() != 200);
$this->assertTrue(session()->has('errors'));
```

### 6. Mensagens de Erro Claras

```php
// ✅ BOM - Mensagem explicativa
$this->assertEquals(
    'João',
    $funcionario->Nome,
    'Nome deveria ser atualizado para "João"'
);

// ❌ RUIM - Sem contexto
$this->assertEquals('João', $funcionario->Nome);
```

---

## 📊 Checklist para Novo Teste

Antes de commitar um teste:

- [ ] Nome segue padrão `test_ação_estado_resultado`
- [ ] Tem seções Arrange, Act, Assert claras
- [ ] Usa RefreshDatabase (BD limpo)
- [ ] Usa Factories para dados
- [ ] Assertions são específicas
- [ ] Testa apenas 1 conceito
- [ ] Não depende de outros testes
- [ ] Executa em < 50ms
- [ ] Sem hardcoded data (exceto CPF único)
- [ ] Documentado com comentários

---

## 🔄 Ciclo de Desenvolvimento com Testes

### 1. Escrever Teste (RED)
```bash
php artisan test
# ✗ Tests: 1 failed
```

### 2. Implementar Feature (GREEN)
```bash
# Implementar controller/model
php artisan test
# ✓ Tests: 1 passed
```

### 3. Refatorar Código (REFACTOR)
```bash
# Melhorar qualidade
php artisan test
# ✓ Tests: 1 passed
```

---

## 📈 Executar e Monitorar

```bash
# Primeiro teste
php artisan test tests/Feature/FuncionarioFeatureTest.php --verbose

# Watch mode (reexecuta ao salvar)
watch php artisan test

# Com cobertura
php artisan test --coverage

# Apenas testes que falharam
php artisan test --failed
```

---

**Pronto para começar a escrever testes incríveis!** 🚀
