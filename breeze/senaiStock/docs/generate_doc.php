<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Shared\Html;

$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Calibri');
$phpWord->setDefaultFontSize(11);

// ================================================================
// STYLES
// ================================================================
$phpWord->addTitleStyle(1, ['bold' => true, 'size' => 20, 'color' => '1E3A5F'], ['spaceAfter' => 200]);
$phpWord->addTitleStyle(2, ['bold' => true, 'size' => 16, 'color' => '2C5F8A'], ['spaceAfter' => 150]);
$phpWord->addTitleStyle(3, ['bold' => true, 'size' => 13, 'color' => '3A7CB8'], ['spaceAfter' => 100]);

// Table style
$tableStyle = [
    'borderSize' => 6,
    'borderColor' => 'BBBBBB',
    'cellMargin' => 80,
];
$phpWord->addTableStyle('Tabela', $tableStyle, [
    'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
]);
$firstRowStyle = ['bgColor' => '1E3A5F', 'bold' => true, 'color' => 'FFFFFF'];

// ================================================================
// CAPA
// ================================================================
$section = $phpWord->addSection([
    'marginTop' => 3000,
    'marginBottom' => 2000,
    'marginLeft' => 2000,
    'marginRight' => 2000,
]);
$section->addText('SenaiStock', ['bold' => true, 'size' => 28, 'color' => '1E3A5F'], ['alignment' => 'center', 'spaceAfter' => 0]);
$section->addText('API de Controle de Estoque', ['size' => 16, 'color' => '555555'], ['alignment' => 'center', 'spaceAfter' => 300]);
$section->addTextBreak(1);
$section->addText('Documentação Técnica das Stacks Utilizadas', ['bold' => true, 'size' => 14, 'color' => '333333'], ['alignment' => 'center', 'spaceAfter' => 400]);
$section->addTextBreak(1);
$section->addText('Autores: Joel, Júlio, Murilo', ['size' => 12], ['alignment' => 'center']);
$section->addText('Instituição: SENAI Limeira (2026)', ['size' => 12], ['alignment' => 'center']);
$section->addTextBreak(2);
$section->addText('---', ['size' => 11, 'color' => '888888'], ['alignment' => 'center']);

// ================================================================
// SUMÁRIO (page break)
// ================================================================
$section->addPageBreak();
$section->addTitle('Sumário', 1);
$section->addText('1. Stack Principal (Backend)', ['size' => 11], ['spaceAfter' => 40]);
$section->addText('2. Stack de Frontend / Assets', ['size' => 11], ['spaceAfter' => 40]);
$section->addText('3. Stack de Testes e Qualidade', ['size' => 11], ['spaceAfter' => 40]);
$section->addText('4. Banco de Dados', ['size' => 11], ['spaceAfter' => 40]);
$section->addText('5. Ferramentas de Build', ['size' => 11], ['spaceAfter' => 40]);
$section->addText('6. Arquitetura do Projeto', ['size' => 11], ['spaceAfter' => 40]);
$section->addText('7. Resumo das Tecnologias', ['size' => 11], ['spaceAfter' => 40]);
$section->addText('8. Comandos Úteis do Ambiente', ['size' => 11], ['spaceAfter' => 40]);

// ================================================================
// HELPER
// ================================================================
function addTableRows($section, $headers, $rows, $phpWord) {
    global $firstRowStyle;
    $table = $section->addTable('Tabela');
    // Header row
    $table->addRow(null, ['tblHeader' => true, 'cantSplit' => true, 'bgColor' => '1E3A5F']);
    foreach ($headers as $h) {
        $table->addCell(2500, ['bgColor' => '1E3A5F'])->addText($h, $firstRowStyle);
    }
    // Data rows
    foreach ($rows as $row) {
        $table->addRow();
        foreach ($row as $cell) {
            $table->addCell(2500)->addText($cell ?? '-', ['size' => 10]);
        }
    }
    $section->addTextBreak(1);
}

// ================================================================
// 1. STACK PRINCIPAL (BACKEND)
// ================================================================
$section->addPageBreak();
$section->addTitle('1. Stack Principal (Backend)', 1);

$section->addTitle('1.1 Linguagem', 2);
addTableRows($section, ['Tecnologia', 'Versão', 'Descrição'], [
    ['PHP', '^8.3', 'Linguagem principal do backend, executada sob Laravel 13'],
], $phpWord);

$section->addTitle('1.2 Framework', 2);
addTableRows($section, ['Tecnologia', 'Versão', 'Descrição'], [
    ['Laravel', '^13.0', 'Framework PHP full-stack, utilizado como base do projeto'],
], $phpWord);

$section->addText('Principais pacotes Laravel em uso:', ['bold' => true, 'size' => 11], ['spaceAfter' => 60]);
$section->addText('- laravel/framework — Núcleo do Laravel (Eloquent ORM, Blade, Routing, Artisan, etc.)', ['size' => 10], ['spaceAfter' => 40]);
$section->addText('- laravel/tinker — REPL interativo para PHP dentro do Laravel', ['size' => 10], ['spaceAfter' => 120]);

$section->addTitle('1.3 Funcionalidades do Laravel Utilizadas', 2);
addTableRows($section, ['Funcionalidade', 'Localização', 'Descrição'], [
    ['Eloquent ORM', 'app/Models/', 'Abstração do banco de dados com 13 modelos (Book, Movement, User, Cargo, Funcionario, Curso, Turma, Supplier, PurchaseOrder, PurchaseOrderItem, StockNotification, TeacherRequest, TeacherRequestMessage)'],
    ['Controllers', 'app/Http/Controllers/', 'Controladores RESTful (BookController, MovementController)'],
    ['Middleware', 'app/Http/Middleware/', 'Proteção de rotas sensíveis via autenticação'],
    ['Form Requests', 'app/Http/Requests/', 'Validação de dados de entrada'],
    ['Services', 'app/Services/', 'Lógica de negócio separada em camadas (StockService, TeacherRequestService)'],
    ['Queries', 'app/Queries/', 'Consultas otimizadas ao banco (OptimizedQueries)'],
    ['Mail', 'app/Mail/', 'Envio de notificações por e-mail (TeacherRequestStatusMail)'],
    ['Routing', 'routes/api.php', 'Definição de rotas RESTful protegidas por middleware auth'],
    ['Migrations', 'database/migrations/', 'Controle de versão do schema do banco'],
    ['Seeders', 'database/seeders/', 'Dados iniciais para desenvolvimento/testes'],
], $phpWord);

// ================================================================
// 2. STACK DE FRONTEND / ASSETS
// ================================================================
$section->addPageBreak();
$section->addTitle('2. Stack de Frontend / Assets', 1);

$section->addTitle('2.1 Gerenciamento de Assets (Build)', 2);
addTableRows($section, ['Tecnologia', 'Versão', 'Descrição'], [
    ['Vite', '^8.0', 'Bundler moderno para assets frontend (substituto do Laravel Mix)'],
    ['Laravel Vite Plugin', '^3.0', 'Plugin de integração Vite + Laravel'],
    ['PostCSS', '^8.4', 'Processador de CSS para transformações'],
    ['Tailwind CSS', '^3.1', 'Framework CSS utilitário para estilização rápida'],
    ['Autoprefixer', '^10.4', 'Prefixos CSS automáticos para compatibilidade entre browsers'],
    ['@tailwindcss/forms', '^0.5.2', 'Plugin Tailwind para estilização de formulários'],
], $phpWord);

$section->addTitle('2.2 Bibliotecas JavaScript', 2);
addTableRows($section, ['Tecnologia', 'Versão', 'Descrição'], [
    ['Alpine.js', '^3.4.2', 'Framework JavaScript leve para interatividade no frontend'],
    ['Axios', '^1.15.2', 'Cliente HTTP para requisições à API'],
], $phpWord);

$section->addTitle('2.3 Utilitários de Desenvolvimento', 2);
addTableRows($section, ['Tecnologia', 'Versão', 'Descrição'], [
    ['Concurrently', '^9.0.1', 'Executa múltiplos comandos simultaneamente (servidor + queue + logs + Vite)'],
], $phpWord);

// ================================================================
// 3. STACK DE TESTES E QUALIDADE
// ================================================================
$section->addPageBreak();
$section->addTitle('3. Stack de Testes e Qualidade', 1);

$section->addTitle('3.1 Testes', 2);
addTableRows($section, ['Tecnologia', 'Versão', 'Descrição'], [
    ['PHPUnit', '^12.5.12', 'Framework de testes unitários para PHP'],
    ['Mockery', '^1.6', 'Biblioteca de mocks para testes'],
], $phpWord);

$section->addTitle('3.2 Qualidade de Código', 2);
addTableRows($section, ['Tecnologia', 'Versão', 'Descrição'], [
    ['Laravel Pint', '^1.27', 'Fixador de estilo de código PSR (PHP-CS-Fixer wrapper)'],
    ['Laravel Pail', '^1.2.5', 'Logger em tempo real para o terminal'],
], $phpWord);

$section->addTitle('3.3 Geração de Dados Fictícios', 2);
addTableRows($section, ['Tecnologia', 'Versão', 'Descrição'], [
    ['FakerPHP', '^1.23', 'Geração de dados falsos para testes e seeders'],
], $phpWord);

$section->addTitle('3.4 Starter Kit', 2);
addTableRows($section, ['Tecnologia', 'Versão', 'Descrição'], [
    ['Laravel Breeze', '^2.4', 'Starter kit de autenticação (scaffolding inicial)'],
], $phpWord);

// ================================================================
// 4. BANCO DE DADOS
// ================================================================
$section->addPageBreak();
$section->addTitle('4. Banco de Dados', 1);

$section->addTitle('4.1 Drivers Suportados', 2);
addTableRows($section, ['Tecnologia', 'Driver', 'Uso'], [
    ['SQLite', 'sqlite', 'Padrão para desenvolvimento local (database/database.sqlite)'],
    ['MySQL', 'mysql', 'Produção / ambiente de homologação'],
    ['MariaDB', 'mariadb', 'Alternativa ao MySQL'],
    ['PostgreSQL', 'pgsql', 'Suporte adicional'],
    ['SQL Server', 'sqlsrv', 'Suporte adicional'],
], $phpWord);

$section->addTitle('4.2 Cache / Fila', 2);
addTableRows($section, ['Tecnologia', 'Descrição'], [
    ['Redis', 'Cache de dados e gerenciamento de filas (opcional)'],
], $phpWord);

$section->addTitle('4.3 Modelos do Banco (Eloquent ORM)', 2);
addTableRows($section, ['Modelo', 'Tabela Provável', 'Descrição'], [
    ['Book', 'books', 'Cadastro de livros com ISBN, matéria e metadados'],
    ['Movement', 'movements', 'Registro de entradas e saídas de estoque'],
    ['User', 'users', 'Usuários do sistema (Almoxarife e Coordenador)'],
    ['Funcionario', 'funcionarios', 'Funcionários vinculados aos usuários'],
    ['Cargo', 'cargos', 'Cargos / perfis de acesso'],
    ['Curso', 'cursos', 'Cursos oferecidos pela instituição'],
    ['Turma', 'turmas', 'Turmas que recebem materiais'],
    ['Supplier', 'suppliers', 'Fornecedores (editoras)'],
    ['PurchaseOrder', 'purchase_orders', 'Ordens de compra'],
    ['PurchaseOrderItem', 'purchase_order_items', 'Itens de uma ordem de compra'],
    ['StockNotification', 'stock_notifications', 'Notificações de estoque baixo'],
    ['TeacherRequest', 'teacher_requests', 'Solicitações de professores'],
    ['TeacherRequestMessage', 'teacher_request_messages', 'Mensagens nas solicitações'],
], $phpWord);

// ================================================================
// 5. FERRAMENTAS DE BUILD
// ================================================================
$section->addPageBreak();
$section->addTitle('5. Ferramentas de Build', 1);

$section->addTitle('5.1 Scripts do Composer (composer.json)', 2);
addTableRows($section, ['Script', 'Comando', 'Descrição'], [
    ['setup', 'composer install && cp .env && key:generate && migrate && npm install && npm build', 'Configuração completa do projeto'],
    ['dev', 'php artisan serve + queue:listen + pail + npm run dev', 'Inicia servidor de desenvolvimento com múltiplos processos'],
    ['test', 'php artisan config:clear && php artisan test', 'Executa suite de testes'],
], $phpWord);

$section->addTitle('5.2 Scripts NPM (package.json)', 2);
addTableRows($section, ['Script', 'Comando', 'Descrição'], [
    ['build', 'vite build', 'Compila assets para produção'],
    ['dev', 'vite', 'Servidor de desenvolvimento Vite com HMR'],
], $phpWord);

// ================================================================
// 6. ARQUITETURA DO PROJETO
// ================================================================
$section->addPageBreak();
$section->addTitle('6. Arquitetura do Projeto', 1);

$section->addText('Diagrama de camadas:', ['bold' => true, 'size' => 11], ['spaceAfter' => 100]);
$lines = [
    '┌─────────────────────────────────────────────────────────┐',
    '│                    ROTAS (routes/api.php)                │',
    '│            Middleware: auth → Proteção via token         │',
    '├─────────────────────────────────────────────────────────┤',
    '│              CONTROLLERS (app/Http/Controllers/)         │',
    '│          BookController | MovementController ...         │',
    '├─────────────────────────────────────────────────────────┤',
    '│          REQUESTS (app/Http/Requests/)                   │',
    '│          Validação de dados de entrada                   │',
    '├─────────────────────────────────────────────────────────┤',
    '│           SERVICES (app/Services/)                       │',
    '│   StockService | TeacherRequestService                   │',
    '│   → Lógica de negócio desacoplada dos controllers       │',
    '├─────────────────────────────────────────────────────────┤',
    '│          QUERIES (app/Queries/)                          │',
    '│   OptimizedQueries → Consultas performáticas            │',
    '├─────────────────────────────────────────────────────────┤',
    '│          MODELS (app/Models/)                            │',
    '│   Book | Movement | User | Funcionario | Cargo | ...    │',
    '│   → Eloquent ORM com relacionamentos                    │',
    '├─────────────────────────────────────────────────────────┤',
    '│    DATABASE (database/migrations/ + database/seeders/)  │',
    '│    MySQL / SQLite → Migrations + Seeders                │',
    '└─────────────────────────────────────────────────────────┘',
];
foreach ($lines as $line) {
    $section->addText($line, ['monospace' => true, 'size' => 8], ['spaceAfter' => 0, 'spaceBefore' => 0]);
}
$section->addTextBreak(1);

$section->addTitle('6.1 Padrões de Projeto Utilizados', 2);
addTableRows($section, ['Padrão', 'Aplicação'], [
    ['MVC', 'Model-View-Controller (Laravel nativo)'],
    ['Service Layer', 'app/Services/ para lógica de negócio'],
    ['Repository/Query', 'app/Queries/ para consultas otimizadas'],
    ['RESTful API', 'Rotas HTTP com verbos GET, POST, PUT, DELETE'],
    ['PSR-4', 'Autoloading padrão PHP'],
    ['Middleware', 'Filtros de requisição (autenticação)'],
    ['Form Request', 'Validação encapsulada em classes dedicadas'],
], $phpWord);

// ================================================================
// 7. RESUMO DAS TECNOLOGIAS
// ================================================================
$section->addPageBreak();
$section->addTitle('7. Resumo das Tecnologias (Visão Geral)', 1);
addTableRows($section, ['Categoria', 'Tecnologias'], [
    ['Linguagem', 'PHP 8.3+'],
    ['Backend Framework', 'Laravel 13'],
    ['Banco de Dados', 'MySQL (principal), SQLite (dev), PostgreSQL, MariaDB, SQL Server'],
    ['Cache', 'Redis'],
    ['Frontend Assets', 'Vite 8, Tailwind CSS 3, Alpine.js 3'],
    ['HTTP Client', 'Axios'],
    ['Testes', 'PHPUnit 12, Mockery'],
    ['Qualidade', 'Laravel Pint, Laravel Pail'],
    ['Metodologia', 'Scrum (Sprints de 1 semana)'],
    ['Versionamento', 'Git (GitHub)'],
    ['Planejamento', 'Milanote, Trello (Kanban)'],
    ['Prototipagem', 'Figma'],
], $phpWord);

// ================================================================
// 8. COMANDOS ÚTEIS
// ================================================================
$section->addPageBreak();
$section->addTitle('8. Comandos Úteis do Ambiente', 1);

$cmds = [
    ['Instalar dependências', 'composer install'],
    ['Configurar ambiente', 'cp .env.example .env' . "\n" . 'php artisan key:generate'],
    ['Executar migrations', 'php artisan migrate'],
    ['Iniciar servidor de desenvolvimento', 'composer dev'],
    ['Executar testes', 'composer test'],
    ['Compilar assets para produção', 'npm run build'],
];

foreach ($cmds as $cmd) {
    $section->addText($cmd[0], ['bold' => true, 'size' => 11], ['spaceAfter' => 20]);
    $section->addText($cmd[1], ['monospace' => true, 'size' => 10, 'color' => '2C5F8A'], ['spaceAfter' => 80]);
}

$section->addTextBreak(2);
$section->addText('Documentação gerada em: Junho/2026', ['italic' => true, 'size' => 10, 'color' => '888888'], ['alignment' => 'center']);
$section->addText('Atualizada conforme: composer.json, package.json, config/database.php, e estrutura do código-fonte.', ['italic' => true, 'size' => 9, 'color' => '888888'], ['alignment' => 'center']);

// ================================================================
// SAVE
// ================================================================
$outputPath = __DIR__ . '/TECH_STACK_DOC.docx';
$phpWord->save($outputPath);

echo "Documento Word gerado com sucesso em: " . realpath($outputPath) . "\n";