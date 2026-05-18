# ✅ Checklist de Testes - SenaiStock Views

## 🧪 Testes Manual

### 📋 Funcionários - Index (GET /funcionarios)
- [ ] Page carrega sem erros
- [ ] Exibe tabela com colunas: Nome, CPF, Cargo, Ações
- [ ] Se vazio, mostra mensagem "Nenhum funcionário cadastrado"
- [ ] Botão "Novo Funcionário" visível no topo
- [ ] Se houver dados, cada linha mostra:
  - [ ] Nome com font-medium
  - [ ] CPF em font-mono (menor)
  - [ ] Cargo com badge azul
  - [ ] Links "Editar" e "Deletar"
- [ ] Tabela tem striped (alternando branco/cinza)
- [ ] Hover efeito nas linhas

### 📋 Funcionários - Create (GET /funcionarios/create)
- [ ] Page carrega sem erros
- [ ] Exibe título "Novo Funcionário"
- [ ] Formulário com 3 campos:
  - [ ] Nome (text input)
  - [ ] CPF (text input)
  - [ ] Cargo (select dropdown)
- [ ] Select mostra "Selecione um cargo..." como placeholder
- [ ] Select carrega todos os cargos do banco
- [ ] Campos têm labels apropriados
- [ ] Botões no footer:
  - [ ] "Salvar Funcionário" (primário, cinza escuro)
  - [ ] "Cancelar" (secundário, branco/cinza)
- [ ] Cancelar leva de volta para /funcionarios

### 📋 Funcionários - Create - Validação (POST /funcionarios)
- [ ] Enviar sem preencher → Exibe erros
- [ ] Enviar CPF duplicado → Erro "The Cpf has already been taken"
- [ ] Enviar sem cargo → Erro "The Id cargo FK field is required"
- [ ] Enviar válido → Redireciona para index com mensagem "Funcionário cadastrado com sucesso!"

### 📋 Funcionários - Edit (GET /funcionarios/{id}/edit)
- [ ] Page carrega sem erros
- [ ] Exibe título "Editar Funcionário"
- [ ] Campos pré-preenchidos com dados atuais
- [ ] Select mostra cargo atual como selecionado
- [ ] Botões:
  - [ ] "Atualizar" (primário)
  - [ ] "Cancelar" (secundário)

### 📋 Funcionários - Update (PUT /funcionarios/{id})
- [ ] Mudar apenas nome → Atualiza e redireciona com mensagem "Dados atualizados!"
- [ ] Mudar CPF para existente → Erro único
- [ ] Mudar CPF para novo (válido) → Atualiza
- [ ] Mudar cargo → Atualiza relationship
- [ ] Voltar para index → Dados refletem mudanças

### 📋 Funcionários - Delete (DELETE /funcionarios/{id})
- [ ] Clicar "Deletar" → Mostra confirm()
- [ ] Confirmar delete → Redireciona com mensagem "Funcionário removido"
- [ ] Rejeitar confirm → Mantém na página
- [ ] Verificar banco → Registro foi deletado

### 📋 Cargos - Index (GET /cargos)
- [ ] Page carrega sem erros
- [ ] Exibe tabela com colunas: Nome do Cargo, Ações
- [ ] Se vazio, mostra mensagem "Nenhum cargo cadastrado"
- [ ] Botão "Novo Cargo" visível no topo
- [ ] Se houver dados:
  - [ ] Cada linha mostra Nome_cargo
  - [ ] Link "Deletar" em cada linha
- [ ] Tabela tem striped

### 📋 Cargos - Create Modal (POST /cargos)
- [ ] Clicar "Novo Cargo" → Abre modal
- [ ] Modal exibe:
  - [ ] Título "Novo Cargo"
  - [ ] Campo "Nome do Cargo" (text input)
  - [ ] Botões "Cancelar" e "Salvar Cargo"
- [ ] Preencher e enviar → Valida
  - [ ] Vazio → Erro
  - [ ] Cargo duplicado → Erro unique
  - [ ] Válido → Redireciona com mensagem "Cargo criado com sucesso!"
- [ ] Cancelar → Fecha modal sem recarregar

### 📋 Cargos - Delete
- [ ] Clicar "Deletar" → Mostra confirm()
- [ ] Confirmar → Redireciona com "Cargo removido"
- [ ] Verificar banco → Deletado

### 📋 SenaiStock Library (GET /dashboard/library)
- [ ] Page carrega sem erros
- [ ] Exibe título "Acervo"
- [ ] Livros agrupados por subject/disciplina
- [ ] Cada grupo mostra:
  - [ ] Nome da disciplina (h3)
  - [ ] Contador de itens "X itens"
- [ ] Grid de livros em cada grupo:
  - [ ] 1 coluna mobile
  - [ ] 2 colunas tablet
  - [ ] 3 colunas desktop
- [ ] Cada card de livro mostra:
  - [ ] Título
  - [ ] Badge CRÍTICO (vermelho) ou OK (verde)
  - [ ] ISBN
  - [ ] Quantidade disponível
  - [ ] Botão "⬇ Entrada" (azul)
  - [ ] Botão "⬆ Saída" (laranja)

### 📋 Library - Modal Entrada (Receive)
- [ ] Clicar "Entrada" em um livro → Abre modal
- [ ] Modal exibe:
  - [ ] Título "Entrada de Estoque"
  - [ ] Nome do livro abaixo
  - [ ] Campo "Quantidade a Receber" (number, min=1)
  - [ ] Campo "Observações" (textarea, opcional)
  - [ ] Botões "Cancelar" e "Confirmar Entrada"
- [ ] Preencher quantidade e enviar:
  - [ ] Faz fetch POST para `/api/books/{id}/receive`
  - [ ] Envia JSON: { quantity, notes }
  - [ ] Inclui CSRF token no header
  - [ ] Botão mostra "Processando..." durante envio
- [ ] Resposta sucesso:
  - [ ] Alert com mensagem
  - [ ] Modal fecha
  - [ ] Page recarrega

### 📋 Library - Modal Saída (Withdraw)
- [ ] Clicar "Saída" em um livro → Abre modal
- [ ] Modal exibe:
  - [ ] Título "Saída de Estoque"
  - [ ] Nome do livro
  - [ ] Disponível: X unidades
  - [ ] Campo "Quantidade a Retirar" (number)
  - [ ] Campo "Justificativa" (textarea, obrigatório)
  - [ ] Aviso: "Esta ação não pode ser desfeita"
  - [ ] Max de unidades validado
  - [ ] Botões "Cancelar" e "Confirmar Saída"
- [ ] Validação client-side:
  - [ ] Qty > 0
  - [ ] Qty <= quantidade disponível
  - [ ] Justificativa preenchida
  - [ ] Botão desabilita se validação falha
- [ ] Enviar válido:
  - [ ] Faz fetch POST para `/api/books/{id}/withdraw`
  - [ ] Envia JSON: { quantity, justification }
  - [ ] Mostra "Processando..."
  - [ ] Recarrega página ao sucesso

### 📋 Indicadores de Estoque
- [ ] Livros com quantity < 8:
  - [ ] Badge vermelho "CRÍTICO"
  - [ ] Classe bg-red-100 text-red-700
- [ ] Livros com quantity >= 8:
  - [ ] Badge verde "OK"
  - [ ] Classe bg-green-100 text-green-700

---

## 🔍 Testes de Validação

### Funcionários - Validação Server
```ruby
# Nome
POST /funcionarios { nome: "" } 
# Esperado: erro "The nome field is required"

# CPF
POST /funcionarios { cpf: "123.456.789-10", cpf_existente: true }
# Esperado: erro "The Cpf has already been taken"

# Cargo
POST /funcionarios { cargo_id: 999 }
# Esperado: erro "The selected Id cargo FK is invalid"
```

### Cargos - Validação
```ruby
POST /cargos { nome_cargo: "" }
# Esperado: erro required

POST /cargos { nome_cargo: "Existente" }
# Esperado: erro unique
```

---

## 🎨 Testes de UI/UX

### Responsividade
- [ ] Mobile (320px):
  - [ ] Sidebar colapsa
  - [ ] Buttons stackam
  - [ ] Tables scrollam horizontalmente
- [ ] Tablet (768px):
  - [ ] Grid 2 colunas
  - [ ] Sidebar visível
- [ ] Desktop (1024px+):
  - [ ] Grid 3 colunas
  - [ ] Sidebar sempre visível

### Acessibilidade
- [ ] Todos os inputs têm <label>
- [ ] Erros de validação exibidos
- [ ] Modais com focus trap
- [ ] Botões com hover/focus states
- [ ] Contrast adequado (WCAG AA)

### Performance
- [ ] Modais abrem sem lag
- [ ] Transições suaves
- [ ] Sem memory leaks em console
- [ ] Requests via fetch são rápidos

---

## 🚨 Testes de Erro

### Edge Cases
- [ ] Deletar funcionário com cargos associados:
  - [ ] Se há FK constraint → Erro apropriado
  - [ ] Se sem constraint → Deleta
- [ ] Acessar /funcionarios/{id}/edit com ID inexistente → 404
- [ ] Enviar form sem CSRF token → 419 Página Expirada
- [ ] JavaScript desabilita → Buttons ainda funcionam

### Browser Compatibilidade
- [ ] Chrome latest ✓
- [ ] Firefox latest ✓
- [ ] Safari latest ✓
- [ ] Edge latest ✓
- [ ] Mobile Safari iOS ✓
- [ ] Chrome Android ✓

---

## 🔐 Testes de Segurança

- [ ] CSRF token presente em todos os forms
- [ ] Escapagem de HTML no output
  - Testar: Nome com <script>alert('xss')</script>
  - Esperado: HTML escapado, não executado
- [ ] SQL Injection:
  - Testar CPF: "' OR '1'='1"
  - Esperado: Tratado como string, sem erro
- [ ] Autorização:
  - [ ] User não autenticado acessa /funcionarios → Redireciona login
  - [ ] User autenticado acessa → Sucesso

---

## 📊 Testes de Database

- [ ] Funcionário criado no banco com todos os campos
- [ ] Cargo atualizado reflete na lista
- [ ] CPF é realmente único (validação)
- [ ] Relationship cargo carrega corretamente
- [ ] Deletar cascata (se configurado)

---

## 🐛 Bugs Conhecidos

### Não Há Bugs Reportados
- ✅ Controllers corrigidos (Role → Cargo)
- ✅ Validações alinhadas com DB schema
- ✅ Views renderizam sem erros

### Observações
- [ ] APIs `/api/books/{id}/receive|withdraw` ainda não implementadas
  - Modais farão 404 ao enviar
  - Frontend está preparado para tratamento de erro

---

## ✅ Aprovação Final

### Checklist de Produção
- [ ] Todas as views funcionam
- [ ] Nenhum erro 404/500 em fluxo normal
- [ ] Validações funcionam
- [ ] Design responsivo
- [ ] CSRF protection
- [ ] Database migrations ok
- [ ] Documentação atualizada
- [ ] Code review aprovado

### Sign-off
- [ ] Frontend Developer: _______________ Data: _____
- [ ] QA Tester: _______________ Data: _____
- [ ] DevOps/Deployment: _______________ Data: _____

---

## 📝 Notas para Próxima Etapa

1. Implementar APIs de books (receive/withdraw)
2. Adicionar histórico de movimentações
3. Criar testes unitários
4. Setup de CI/CD
5. Deploy para staging

---

**Última Atualização:** 12/01/2025
**Versão do Sistema:** 1.0
**Tester:** _____________
**Status:** Pronto para Teste ✅
