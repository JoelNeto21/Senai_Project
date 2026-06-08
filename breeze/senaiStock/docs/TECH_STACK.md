# 📚 SenaiStock - Documentação Técnica das Stacks Utilizadas

> **Projeto:** SenaiStock - API de Controle de Estoque
> **Autores:** Joel, Júlio, Murilo
> **Instituição:** SENAI Limeira (2026)

---

## Sumário

1. [Stack Principal (Backend)](#1-stack-principal-backend)
2. [Stack de Frontend / Assets](#2-stack-de-frontend--assets)
3. [Stack de Testes e Qualidade](#3-stack-de-testes-e-qualidade)
4. [Banco de Dados](#4-banco-de-dados)
5. [Ferramentas de Build](#5-ferramentas-de-build)
6. [Arquitetura do Projeto](#6-arquitetura-do-projeto)
7. [Estrutura de Diretórios Relevante](#7-estrutura-de-diretórios-relevante)
8. [Diagrama de Dependências](#8-diagrama-de-dependências)

---

## 1. Stack Principal (Backend)

### Linguagem
| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **PHP** | ^8.3 | Linguagem principal do backend, executada sob Laravel 13 |

### Framework
| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **Laravel** | ^13.0 | Framework PHP full-stack, utilizado como base do projeto |

**Principais pacotes Laravel em uso:**
- `laravel/framework` — Núcleo do Laravel (Eloquent ORM, Blade, Routing, Artisan, etc.)
- `laravel/tinker` — REPL interativo para PHP dentro do Laravel

### Funcionalidades do Laravel Utilizadas

| Funcionalidade | Localização | Descrição |
|----------------|-------------|-----------|
| **Eloquent ORM** | `app/Models/` | Abstração do banco de dados com models como `Book`, `Movement`, `User`, `Cargo`, `Curso`, `Funcionario`, `Turma`, `Supplier`, `PurchaseOrder`, `PurchaseOrderItem`, `StockNotification`, `TeacherRequest`, `TeacherRequestMessage` |
| **Controllers** | `app/Http/Controllers/` | Controladores RESTful (ex.: `BookController`, `MovementController`) |
| **Middleware** | `app/Http/Middleware/` | Proteção de rotas sensíveis via autenticação |
| **Form Requests** | `app/Http/Requests/` | Validação de dados de entrada |
| **Services** | `app/Services/` | Lógica de negócio separada em camadas (`StockService`, `TeacherRequestService`) |
| **Queries** | `app/Queries/` | Consultas otimizadas ao banco (`OptimizedQueries`) |
| **Mail** | `app/Mail/` | Envio de notificações por e-mail (`TeacherRequestStatusMail`) |
| **Routing** | `routes/api.php` | Definição de rotas RESTful protegidas por middleware `auth` |
| **Migrations** | `database/migrations/` | Controle de versão do schema do banco |
| **Seeders** | `database/seeders/` | Dados iniciais para desenvolvimento/testes |

---

## 2. Stack de Frontend / Assets

### Gerenciamento de Assets (Build)

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **Vite** | ^8.0 | Bundler moderno para assets frontend (substituto do Laravel Mix) |
| **Laravel Vite Plugin** | ^3.0 | Plugin de integração Vite + Laravel |
| **PostCSS** | ^8.4 | Processador de CSS para transformações |
| **Tailwind CSS** | ^3.1 | Framework CSS utilitário para estilização rápida |
| **Autoprefixer** | ^10.4 | Prefixos CSS automáticos para compatibilidade entre browsers |
| **@tailwindcss/forms** | ^0.5.2 | Plugin Tailwind para estilização de formulários |

### Bibliotecas JavaScript

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **Alpine.js** | ^3.4.2 | Framework JavaScript leve para interatividade no frontend |
| **Axios** | ^1.15.2 | Cliente HTTP para requisições à API |

### Utilitários de Desenvolvimento

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **Concurrently** | ^9.0.1 | Executa múltiplos comandos simultaneamente (servidor + queue + logs + Vite) |

---

## 3. Stack de Testes e Qualidade

### Testes

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **PHPUnit** | ^12.5.12 | Framework de testes unitários para PHP |
| **Mockery** | ^1.6 | Biblioteca de mocks para testes |

### Qualidade de Código

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **Laravel Pint** | ^1.27 | Fixador de estilo de código PSR (PHP-CS-Fixer wrapper) |
| **Laravel Pail** | ^1.2.5 | Logger em tempo real para o terminal |

### Geração de Dados Fictícios

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **FakerPHP** | ^1.23 | Geração de dados falsos para testes e seeders |

### Starter Kit

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **Laravel Breeze** | ^2.4 | Starter kit de autenticação (scaffolding inicial) |

---

## 4. Banco de Dados

### Drivers Suportados (configurados em `config/database.php`)

| Tecnologia | Driver | Uso |
|------------|--------|-----|
| **SQLite** | `sqlite` | Padrão para desenvolvimento local (database/database.sqlite) |
| **MySQL** | `mysql` | Produção / ambiente de homologação |
| **MariaDB** | `mariadb` | Alternativa ao MySQL |
| **PostgreSQL** | `pgsql` | Suporte adicional |
| **SQL Server** | `sqlsrv` | Suporte adicional |

### Cache / Fila

| Tecnologia | Descrição |
|------------|-----------|
| **Redis** | Cache de dados e gerenciamento de filas (opcional) |

### Modelos do Banco (Eloquent ORM)

| Modelo | Tabela Provável | Descrição |
|--------|-----------------|-----------|
| `Book` | `books` | Cadastro de livros com ISBN, matéria e metadados |
| `Movement` | `movements` | Registro de entradas e saídas de estoque |
| `User` | `users` | Usuários do sistema (Almoxarife e Coordenador) |
| `Funcionario` | `funcionarios` | Funcionários vinculados aos usuários |
| `Cargo` | `cargos` | Cargos / perfis de acesso |
| `Curso` | `cursos` | Cursos oferecidos pela instituição |
| `Turma` | `turmas` | Turmas que recebem materiais |
| `Supplier` | `suppliers` | Fornecedores (editoras) |
| `PurchaseOrder` | `purchase_orders` | Ordens de compra |
| `PurchaseOrderItem` | `purchase_order_items` | Itens de uma ordem de compra |
| `StockNotification` | `stock_notifications` | Notificações de estoque baixo |
| `TeacherRequest` | `teacher_requests` | Solicitações de professores |
| `TeacherRequestMessage` | `teacher_request_messages` | Mensagens nas solicitações |

---

## 5. Ferramentas de Build

### Scripts do Composer (`composer.json`)

| Script | Comando | Descrição |
|--------|---------|-----------|
| `setup` | `composer install && cp .env && key:generate && migrate && npm install && npm build` | Configuração completa do projeto |
| `dev` | `php artisan serve + queue:listen + pail + npm run dev` | Inicia servidor de desenvolvimento com múltiplos processos |
| `test` | `php artisan config:clear && php artisan test` | Executa suite de testes |

### Scripts NPM (`package.json`)

| Script | Comando | Descrição |
|--------|---------|-----------|
| `build` | `vite build` | Compila assets para produção |
| `dev` | `vite` | Servidor de desenvolvimento Vite com HMR |

---

## 6. Arquitetura do Projeto

```
┌─────────────────────────────────────────────────────────┐
│                    ROTAS (routes/api.php)                │
│            Middleware: auth → Proteção via token         │
├─────────────────────────────────────────────────────────┤
│              CONTROLLERS (app/Http/Controllers/)         │
│          BookController | MovementController ...         │
├─────────────────────────────────────────────────────────┤
│          REQUESTS (app/Http/Requests/)                   │
│          Validação de dados de entrada                   │
├─────────────────────────────────────────────────────────┤
│           SERVICES (app/Services/)                       │
│   StockService | TeacherRequestService                   │
│   → Lógica de negócio desacoplada dos controllers       │
├─────────────────────────────────────────────────────────┤
│          QUERIES (app/Queries/)                          │
│   OptimizedQueries → Consultas performáticas            │
├─────────────────────────────────────────────────────────┤
│          MODELS (app/Models/)                            │
│   Book | Movement | User | Funcionario | Cargo | ...    │
│   → Eloquent ORM com relacionamentos                    │
├─────────────────────────────────────────────────────────┤
│    DATABASE (database/migrations/ + database/seeders/)  │
│    MySQL / SQLite → Migrations + Seeders                │
└─────────────────────────────────────────────────────────┘
```

**Padrões de Projeto Utilizados:**

| Padrão | Aplicação |
|--------|-----------|
| **MVC** | Model-View-Controller (Laravel nativo) |
| **Service Layer** | `app/Services/` para lógica de negócio |
| **Repository/Query** | `app/Queries/` para consultas otimizadas |
| **RESTful API** | Rotas HTTP com verbos GET, POST, PUT, DELETE |
| **PSR-4** | Autoloading padrão PHP |
| **Middleware** | Filtros de requisição (autenticação) |
| **Form Request** | Validação encapsulada em classes dedicadas |

---

## 7. Resumo das Tecnologias (Visão Geral)

| Categoria | Tecnologias |
|-----------|-------------|
| **Linguagem** | PHP 8.3+ |
| **Backend Framework** | Laravel 13 |
| **Banco de Dados** | MySQL (principal), SQLite (dev), PostgreSQL, MariaDB, SQL Server |
| **Cache** | Redis |
| **Frontend Assets** | Vite 8, Tailwind CSS 3, Alpine.js 3 |
| **HTTP Client** | Axios |
| **Testes** | PHPUnit 12, Mockery |
| **Qualidade** | Laravel Pint, Laravel Pail |
| **Metodologia** | Scrum (Sprints de 1 semana) |
| **Versionamento** | Git (GitHub) |
| **Planejamento** | Milanote, Trello (Kanban) |
| **Prototipagem** | Figma |

---

## 8. Comandos Úteis do Ambiente

```bash
# Instalar dependências
composer install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Executar migrations
php artisan migrate

# Iniciar servidor de desenvolvimento (completo: server + queue + logs + vite)
composer dev

# Executar testes
composer test

# Compilar assets para produção
npm run build
```

---

> 📝 **Documentação gerada em:** Junho/2026
> ⚙️ **Atualizada conforme:** `composer.json`, `package.json`, `config/database.php`, e estrutura do código-fonte.