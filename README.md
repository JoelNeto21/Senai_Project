# 📚 SenaiStock - API de Controle de Estoque

**Autores:** Joel, Júlio, Murilo  
**Instituição:** SENAI Limeira (2026)  
Acesse todo o nosso planejamento no [Milanote](https://app.milanote.com/1VTtUj1yQuuz4N?p=ZK5fK3a4HXt)

---

## Situação Problema

O **SenaiStock** é uma API RESTful desenvolvida para modernizar a gestão de materiais didáticos. O foco principal é garantir que a jornada de aprendizado não seja interrompida por falhas logísticas, oferecendo um controle rigoroso e em tempo real do saldo de livros.

Atualmente, as unidades de ensino recebem grandes remessas de livros que são armazenados em almoxarifados. Sem um sistema digital centralizado, o controle do saldo é falho, resultando em:

- Esgotamento inesperado de títulos
- Atrasos no cronograma de aulas
- Dificuldade em prever novas ordens de compra/remessa
- Falta de transparência no controle de estoque
- Desperdício ou perda de materiais por falta de organização

---

## Objetivo do Projeto

Desenvolver o Back-End do **SenaiStock** utilizando Laravel, responsável por:

- Gerenciar usuários com níveis de acesso distintos (Almoxarife e Coordenador)
- Controlar entrada e saída de materiais didáticos
- Garantir rastreabilidade e histórico das movimentações
- Fornecer transparência no acompanhamento do estoque
- Alertar automaticamente sobre níveis críticos de reposição

---

## Documentação

A documentação completa do projeto, incluindo diagramas de banco de dados, fluxos de API e especificações técnicas, está disponível no [Milanote](https://app.milanote.com/1VTtUj1yQuuz4N?p=ZK5fK3a4HXt).

---

## Levantamentos de Requisitos

Nesta etapa, foi realizado o levantamento de requisitos com base nas necessidades do sistema, considerando aspectos como funcionalidade, desempenho e segurança.

O objetivo é identificar e organizar os requisitos funcionais e não funcionais, garantindo uma solução eficiente, mais ágil e transparente para os usuários.

### Requisitos Funcionais

- Cadastro e autenticação de usuários com diferentes perfis
- Cadastro de títulos de livros com ISBN, matéria e metadados
- Registro de movimentações de entrada (recebimento de remessas)
- Registro de movimentações de saída (baixa para turmas)
- Listagem de livros com filtro por nível de estoque
- Histórico completo de todas as movimentações

### Requisitos Não Funcionais

- API RESTful com respostas em JSON
- Autenticação segura via token
- Validação de dados para evitar estoque negativo
- Performance adequada para múltiplas requisições simultâneas

---

## Prototipagem

Nesta etapa, é definida a interface inicial da plataforma por meio da criação de protótipos no Figma. Essa fase permite visualizar o design, a organização das informações e a navegação do sistema.

Com base nesses protótipos, são feitas melhorias contínuas a partir de testes e análises, garantindo uma base mais sólida para o desenvolvimento final.

---

## Metodologia Ágeis

Nesta etapa, são aplicadas metodologias ágeis para organizar o desenvolvimento do projeto, utilizando o Scrum. O trabalho é dividido em ciclos curtos, chamados Sprints, permitindo entregas contínuas e melhorias ao longo do processo.

- **Planejamento:** Kanban via Trello para acompanhamento de tarefas
- **Sprints:** Ciclos de 1 semana para entrega de módulos
- **Revisões:** Avaliação contínua do progresso e ajustes

---

## Diagramas

O diagrama de banco de dados representa as entidades principais do sistema:

- **Users** (usuários do sistema)
- **Books** (cadastro de títulos)
- **Movements** (registro de entradas e saídas)

Este modelo garante a rastreabilidade completa de todas as movimentações de estoque.

---

## O que a API oferece (Funcionalidades)

### Gestão de Acesso
- Autenticação segura para perfis de Almoxarife e Coordenador
- Proteção de rotas sensíveis via middleware

### Catálogo Inteligente
- Cadastro de títulos com ISBN, matéria e metadados essenciais
- Listagem completa com filtros e ordenação

### Movimentação de Entrada
- Registro de novas remessas vindas da editora
- Soma automática ao saldo existente

### Movimentação de Saída
- Baixa manual de livros para turmas específicas
- Obrigatoriedade de justificativa para cada saída

### Alertas de Reposição
- Filtro automático para identificar livros com menos de 10 unidades
- Indicador visual de nível crítico de estoque

---

## Especificações Técnicas

Para garantir escalabilidade e manutenibilidade, o projeto utiliza:

| Tecnologia | Descrição |
|------------|-----------|
| **Framework** | Laravel (PHP) - padrões PSR e Clean Code |
| **Banco de Dados** | MySQL com persistência via Eloquent ORM |
| **Comunicação** | Padrão RESTful com respostas em JSON |
| **Segurança** | Middleware de autenticação para proteção de rotas |

---

## Regras de Negócio Importantes

1. **Bloqueio de Saldo:** O sistema impede qualquer saída que resulte em estoque negativo
2. **Nível Crítico:** Constante de "Estoque Baixo" definida em 10 unidades para facilitar gestão visual e relatórios
3. **Restrição de Operação:** Apenas usuários autenticados e com permissões administrativas podem alterar quantidades
4. **Justificativa Obrigatória:** Toda saída de livro deve conter uma justificativa (turma, motivo, etc.)

---

## Como executar o projeto?

```bash
# Clone o repositório
git clone https://github.com/JoelNeto21/Senai_Project.git

# Entre na pasta do projeto
cd Senai_Project/breeze/senaiStock

# Instale as dependências do Composer
composer install

# Copie o arquivo de ambiente
cp .env.example .env

# Gere a chave da aplicação
php artisan key:generate

# Configure o banco de dados no arquivo .env e rode as migrations
php artisan migrate

# Inicie o servidor de desenvolvimento
php artisan serve
```

---

## 📂 Estrutura do Projeto

```
senaiStock/
├── app/
│   ├── Http/Controllers/    # Controladores da API
│   ├── Models/              # Modelos Eloquent
│   └── Providers/           # Provedores de serviço
├── database/
│   ├── migrations/          # Migrações do banco de dados
│   └── seeders/             # Seeders para dados iniciais
├── routes/
│   ├── api.php              # Rotas da API
│   └── web.php              # Rotas web
└── tests/                   # Testes automatizados
```
