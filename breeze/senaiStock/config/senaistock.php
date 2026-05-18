<?php

return [
    'low_stock_threshold' => 8,

    'navigation_items' => [
        ['id' => 'insights', 'label' => 'Insights', 'icon' => 'sparkles'],
        ['id' => 'overview', 'label' => 'Visão Geral', 'icon' => 'layout-grid'],
        ['id' => 'teacher_requests', 'label' => 'Pedidos', 'icon' => 'graduation-cap'],
        ['id' => 'purchases', 'label' => 'Compras', 'icon' => 'credit-card'],
        ['id' => 'history', 'label' => 'Histórico', 'icon' => 'history'],
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bar-chart-3'],
        ['id' => 'library', 'label' => 'Acervo', 'icon' => 'book-open'],
        ['id' => 'receive', 'label' => 'Recebimento', 'icon' => 'arrow-down-to-line'],
        ['id' => 'withdraw', 'label' => 'Retirada', 'icon' => 'arrow-up-from-line'],
    ],

    'books' => [
        ['id' => 1, 'title' => 'Fundamentos de Eletricidade', 'author' => 'Roberto Alves', 'publisher' => 'Editora SENAI-SP', 'year' => '2023', 'pages' => 280, 'isbn' => '978-85-111', 'subject' => 'Elétrica', 'quantity' => 45, 'desc' => 'Conceitos essenciais de tensão, corrente, resistência e circuitos elétricos aplicados à indústria.'],
        ['id' => 2, 'title' => 'Torno CNC Básico', 'author' => 'Antônio Peixoto', 'publisher' => 'Editora SENAI-SP', 'year' => '2022', 'pages' => 240, 'isbn' => '978-85-222', 'subject' => 'Mecânica', 'quantity' => 5, 'desc' => 'Programação e operação de tornos com Comando Numérico Computadorizado.'],
        ['id' => 3, 'title' => 'Lógica de Programação com Python', 'author' => 'Ana Paula', 'publisher' => 'Novatec', 'year' => '2023', 'pages' => 340, 'isbn' => '978-85-333', 'subject' => 'Tecnologia', 'quantity' => 120, 'desc' => 'Introdução à algoritmia e estruturas de dados utilizando a linguagem Python.'],
        ['id' => 4, 'title' => 'Segurança do Trabalho NR-10', 'author' => 'Mário Souza', 'publisher' => 'Editora SENAI-SP', 'year' => '2022', 'pages' => 150, 'isbn' => '978-85-444', 'subject' => 'Segurança', 'quantity' => 8, 'desc' => 'Norma Regulamentadora nº 10 - Segurança em Instalações e Serviços em Eletricidade.'],
        ['id' => 5, 'title' => 'Instalações Elétricas Prediais', 'author' => 'Juliana Costa', 'publisher' => 'Editora SENAI-SP', 'year' => '2022', 'pages' => 350, 'isbn' => '978-85-555', 'subject' => 'Elétrica', 'quantity' => 30, 'desc' => 'Projetos e execução de instalações elétricas de baixa tensão.'],
        ['id' => 6, 'title' => 'Desenho Técnico Mecânico', 'author' => 'Lúcia Ferreira', 'publisher' => 'Érica', 'year' => '2020', 'pages' => 310, 'isbn' => '978-85-666', 'subject' => 'Mecânica', 'quantity' => 15, 'desc' => 'Leitura e interpretação de desenhos técnicos mecânicos.'],
        ['id' => 7, 'title' => 'Redes de Computadores', 'author' => 'Gabriel Torres', 'publisher' => 'Novatec', 'year' => '2021', 'pages' => 500, 'isbn' => '978-85-777', 'subject' => 'Tecnologia', 'quantity' => 50, 'desc' => 'Infraestrutura, protocolos e configuração de redes de dados.'],
        ['id' => 8, 'title' => 'Comandos Elétricos', 'author' => 'Marcos Silva', 'publisher' => 'Érica', 'year' => '2021', 'pages' => 210, 'isbn' => '978-85-888', 'subject' => 'Elétrica', 'quantity' => 20, 'desc' => 'Acionamento e proteção de máquinas elétricas.'],
        ['id' => 9, 'title' => 'CLP Básico e Avançado', 'author' => 'Fernanda Lima', 'publisher' => 'Érica', 'year' => '2023', 'pages' => 415, 'isbn' => '978-85-999', 'subject' => 'Elétrica', 'quantity' => 12, 'desc' => 'Controladores Lógicos Programáveis na automação industrial.'],
        ['id' => 10, 'title' => 'Sistemas Fotovoltaicos', 'author' => 'Carlos Eduardo', 'publisher' => 'Editora SENAI-SP', 'year' => '2024', 'pages' => 190, 'isbn' => '978-85-101', 'subject' => 'Elétrica', 'quantity' => 5, 'desc' => 'Instalação e manutenção de painéis solares.'],
        ['id' => 11, 'title' => 'Metrologia Industrial', 'author' => 'Renato Mendes', 'publisher' => 'Editora SENAI-SP', 'year' => '2021', 'pages' => 180, 'isbn' => '978-85-112', 'subject' => 'Mecânica', 'quantity' => 25, 'desc' => 'Instrumentos de medição e controle dimensional.'],
        ['id' => 12, 'title' => 'Usinagem Aplicada', 'author' => 'Sérgio Ramos', 'publisher' => 'Saraiva', 'year' => '2019', 'pages' => 420, 'isbn' => '978-85-113', 'subject' => 'Mecânica', 'quantity' => 8, 'desc' => 'Processos de fresamento, torneamento e furação.'],
        ['id' => 13, 'title' => 'Manutenção Mecânica', 'author' => 'Vitor Hugo', 'publisher' => 'Editora SENAI-SP', 'year' => '2023', 'pages' => 265, 'isbn' => '978-85-114', 'subject' => 'Mecânica', 'quantity' => 18, 'desc' => 'Técnicas de manutenção preventiva e corretiva.'],
        ['id' => 14, 'title' => 'Desenvolvimento Web com React', 'author' => 'Diego Fernandes', 'publisher' => 'Casa do Código', 'year' => '2024', 'pages' => 290, 'isbn' => '978-85-115', 'subject' => 'Tecnologia', 'quantity' => 35, 'desc' => 'Criação de interfaces modernas e responsivas.'],
        ['id' => 15, 'title' => 'Banco de Dados SQL', 'author' => 'Patrícia Silva', 'publisher' => 'Érica', 'year' => '2020', 'pages' => 380, 'isbn' => '978-85-116', 'subject' => 'Tecnologia', 'quantity' => 40, 'desc' => 'Modelagem, consultas e administração de bancos relacionais.'],
        ['id' => 16, 'title' => 'Cibersegurança Básica', 'author' => 'Lucas Martins', 'publisher' => 'Alta Books', 'year' => '2023', 'pages' => 210, 'isbn' => '978-85-117', 'subject' => 'Tecnologia', 'quantity' => 9, 'desc' => 'Princípios de proteção de dados e sistemas de informação.'],
        ['id' => 17, 'title' => 'NR-35 Trabalho em Altura', 'author' => 'Equipe SENAI', 'publisher' => 'Editora SENAI-SP', 'year' => '2021', 'pages' => 120, 'isbn' => '978-85-118', 'subject' => 'Segurança', 'quantity' => 22, 'desc' => 'Requisitos e medidas de proteção para o trabalho em altura.'],
        ['id' => 18, 'title' => 'Primeiros Socorros no Trabalho', 'author' => 'Dr. Fernando', 'publisher' => 'Atheneu', 'year' => '2023', 'pages' => 200, 'isbn' => '978-85-119', 'subject' => 'Segurança', 'quantity' => 15, 'desc' => 'Atendimento pré-hospitalar em ambientes industriais.'],
        ['id' => 19, 'title' => 'EPI e EPC Industrial', 'author' => 'Camila Rocha', 'publisher' => 'Saraiva', 'year' => '2020', 'pages' => 175, 'isbn' => '978-85-120', 'subject' => 'Segurança', 'quantity' => 30, 'desc' => 'Seleção, uso e conservação de equipamentos de proteção.'],
        ['id' => 20, 'title' => 'Ergonomia Industrial', 'author' => 'Pedro Almeida', 'publisher' => 'Érica', 'year' => '2022', 'pages' => 230, 'isbn' => '978-85-121', 'subject' => 'Segurança', 'quantity' => 11, 'desc' => 'Adequação do ambiente de trabalho ao ser humano.'],
    ],

    'purchase_orders' => [
        ['orderId' => 'PED-7489', 'date' => '11/05/2026', 'time' => '10:15', 'status' => 'aguardando', 'items' => [['type' => 'restock', 'bookId' => 2, 'title' => 'Torno CNC Básico', 'requestedQty' => 20, 'justification' => 'Estoque crítico, fornecedor: Editora SENAI-SP']]],
        ['orderId' => 'PED-6512', 'date' => '02/05/2026', 'time' => '11:40', 'status' => 'entregue', 'items' => [['type' => 'new', 'bookId' => null, 'title' => 'Segurança do Trabalho NR-10', 'requestedQty' => 10, 'justification' => 'Material de apoio complementar']]],
        ['orderId' => 'PED-3210', 'date' => '20/04/2026', 'time' => '14:30', 'status' => 'entregue', 'items' => [['type' => 'new', 'bookId' => null, 'title' => 'Introdução à Robótica', 'requestedQty' => 30, 'justification' => 'Nova grade, fornecedor: Novatec'], ['type' => 'restock', 'bookId' => 10, 'title' => 'Sistemas Fotovoltaicos', 'requestedQty' => 15, 'justification' => 'Reposição anual, fornecedor: Saraiva']]],
        ['orderId' => 'PED-1102', 'date' => '15/03/2026', 'time' => '09:00', 'status' => 'entregue', 'items' => [['type' => 'restock', 'bookId' => 3, 'title' => 'Lógica de Programação com Python', 'requestedQty' => 50, 'justification' => 'Aumento de turmas na área de TI']]],
    ],

    'teacher_requests' => [
        ['id' => 201, 'teacher' => 'Prof. Carlos Mendes', 'email' => 'carlos.mendes@escola.senai.br', 'subject' => 'Mecânica', 'bookId' => 13, 'title' => 'Manutenção Mecânica', 'qty' => 18, 'status' => 'pendente', 'date' => '11/05/2026', 'time' => '09:30'],
        ['id' => 202, 'teacher' => 'Profa. Ana Paula', 'email' => 'ana.paula@escola.senai.br', 'subject' => 'Tecnologia', 'bookId' => 3, 'title' => 'Lógica de Programação com Python', 'qty' => 40, 'status' => 'pendente', 'date' => '10/05/2026', 'time' => '14:15'],
        ['id' => 203, 'teacher' => 'Prof. Roberto Alves', 'email' => 'roberto.alves@escola.senai.br', 'subject' => 'Elétrica', 'bookId' => 1, 'title' => 'Fundamentos de Eletricidade', 'qty' => 45, 'status' => 'atendido', 'date' => '09/05/2026', 'time' => '11:00'],
        ['id' => 204, 'teacher' => 'Prof. Fernando Souza', 'email' => 'fernando.souza@escola.senai.br', 'subject' => 'Mecânica', 'bookId' => 2, 'title' => 'Torno CNC Básico', 'qty' => 15, 'status' => 'pendente', 'date' => '11/05/2026', 'time' => '15:20'],
    ],
];