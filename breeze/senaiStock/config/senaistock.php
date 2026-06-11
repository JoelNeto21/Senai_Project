<?php

return [
    'low_stock_threshold' => 8,

    'navigation_items' => [
        ['id' => 'insights', 'label' => 'Início', 'icon' => '⌂', 'group' => 'Painel'],
        ['id' => 'teacher_requests', 'label' => 'Pedidos', 'icon' => 'P', 'group' => 'Painel'],
        ['id' => 'library', 'label' => 'Catálogo de Livros', 'icon' => 'L', 'group' => 'Livros'],
        ['id' => 'stock', 'label' => 'Entrada e Saída', 'icon' => '⇄', 'group' => 'Livros'],
        ['id' => 'reports', 'label' => 'Relatórios', 'icon' => 'R', 'group' => 'Livros'],
        ['id' => 'purchases', 'label' => 'Compras', 'icon' => 'C', 'group' => 'Compras'],
        ['id' => 'classes', 'label' => 'Turmas', 'icon' => 'T', 'group' => 'Escola'],
        ['id' => 'courses', 'label' => 'Curso', 'icon' => '+', 'group' => 'Escola'],
        ['id' => 'book_registration', 'label' => 'Livro', 'icon' => '+', 'group' => 'Escola'],
        ['id' => 'people', 'label' => 'Equipe', 'icon' => 'E', 'group' => 'Escola'],
    ],

    'default_supplier' => [
        'name' => 'Editora Senai',
        'contact_name' => 'Atendimento Corporativo',
        'email' => 'pedidos@editorasenai.com.br',
        'phone' => '(11) 3000-0101',
        'lead_time_days' => 5,
    ],

    'books' => [
        ['id' => 1, 'title' => 'Lógica de Programação e Algoritmos', 'author' => 'João Araújo', 'publisher' => 'Editora SENAI-SP', 'year' => '2023', 'pages' => 320, 'isbn' => '978-85-001-0001', 'subject' => 'Desenvolvimento de Sistemas', 'quantity' => 40, 'desc' => 'Fundamentos de algoritmos, fluxogramas, variáveis, estruturas condicionais e repetição.'],
        ['id' => 2, 'title' => 'Banco de Dados: Modelagem e SQL', 'author' => 'Patrícia Silva', 'publisher' => 'Érica', 'year' => '2022', 'pages' => 380, 'isbn' => '978-85-001-0002', 'subject' => 'Desenvolvimento de Sistemas', 'quantity' => 35, 'desc' => 'Modelagem relacional, normalização, comandos SQL e administração básica de bancos.'],
        ['id' => 3, 'title' => 'Desenvolvimento Web com HTML, CSS e JavaScript', 'author' => 'Diego Fernandes', 'publisher' => 'Casa do Código', 'year' => '2024', 'pages' => 420, 'isbn' => '978-85-001-0003', 'subject' => 'Desenvolvimento de Sistemas', 'quantity' => 50, 'desc' => 'Criação de interfaces web responsivas e interativas.'],
        ['id' => 4, 'title' => 'Redes de Computadores e Infraestrutura', 'author' => 'Gabriel Torres', 'publisher' => 'Novatec', 'year' => '2023', 'pages' => 500, 'isbn' => '978-85-001-0004', 'subject' => 'Desenvolvimento de Sistemas', 'quantity' => 28, 'desc' => 'Protocolos, endereçamento, serviços de rede e fundamentos de infraestrutura.'],
        ['id' => 5, 'title' => 'Engenharia de Software: Práticas Ágeis', 'author' => 'Fábio Gomes', 'publisher' => 'Editora SENAI-SP', 'year' => '2024', 'pages' => 290, 'isbn' => '978-85-001-0005', 'subject' => 'Desenvolvimento de Sistemas', 'quantity' => 22, 'desc' => 'Requisitos, testes, documentação, Scrum e boas práticas de desenvolvimento.'],
        ['id' => 6, 'title' => 'Versionamento e DevOps com Git', 'author' => 'Carlos Nogueira', 'publisher' => 'Novatec', 'year' => '2024', 'pages' => 210, 'isbn' => '978-85-001-0006', 'subject' => 'Desenvolvimento de Sistemas', 'quantity' => 7, 'desc' => 'Controle de versão, colaboração, integração contínua e entrega de software.'],
        ['id' => 7, 'title' => 'Administração Geral e Recursos Humanos', 'author' => 'Maria Oliveira', 'publisher' => 'Editora SENAI-SP', 'year' => '2023', 'pages' => 360, 'isbn' => '978-85-002-0001', 'subject' => 'Administração', 'quantity' => 45, 'desc' => 'Teorias administrativas, gestão de pessoas, liderança e rotinas organizacionais.'],
        ['id' => 8, 'title' => 'Contabilidade Geral e Gestão Financeira', 'author' => 'Roberto Campos', 'publisher' => 'Atlas', 'year' => '2022', 'pages' => 410, 'isbn' => '978-85-002-0002', 'subject' => 'Administração', 'quantity' => 30, 'desc' => 'Princípios contábeis, fluxo de caixa, custos e análise financeira.'],
        ['id' => 9, 'title' => 'Marketing Empresarial e Vendas', 'author' => 'Ana Costa', 'publisher' => 'Saraiva', 'year' => '2023', 'pages' => 280, 'isbn' => '978-85-002-0003', 'subject' => 'Administração', 'quantity' => 25, 'desc' => 'Estratégias de marketing, relacionamento com clientes e técnicas comerciais.'],
        ['id' => 10, 'title' => 'Direito Empresarial e Legislação Trabalhista', 'author' => 'Dr. Paulo Mendes', 'publisher' => 'Editora SENAI-SP', 'year' => '2022', 'pages' => 340, 'isbn' => '978-85-002-0004', 'subject' => 'Administração', 'quantity' => 6, 'desc' => 'Noções legais aplicadas às empresas, contratos, CLT e rotinas trabalhistas.'],
        ['id' => 11, 'title' => 'Economia e Mercado para Gestão', 'author' => 'Luciana Tavares', 'publisher' => 'Atlas', 'year' => '2024', 'pages' => 310, 'isbn' => '978-85-002-0005', 'subject' => 'Administração', 'quantity' => 20, 'desc' => 'Oferta, demanda, indicadores econômicos e análise de mercado.'],
        ['id' => 12, 'title' => 'Gestão de Projetos: PMBOK e Metodologias', 'author' => 'Sérgio Almeida', 'publisher' => 'Editora SENAI-SP', 'year' => '2024', 'pages' => 260, 'isbn' => '978-85-002-0006', 'subject' => 'Administração', 'quantity' => 5, 'desc' => 'Planejamento, execução, controle de projetos e metodologias preditivas e ágeis.'],
        ['id' => 13, 'title' => 'Circuitos Elétricos e Análise de Redes', 'author' => 'Roberto Alves', 'publisher' => 'Editora SENAI-SP', 'year' => '2023', 'pages' => 400, 'isbn' => '978-85-003-0001', 'subject' => 'Eletroeletrônica', 'quantity' => 32, 'desc' => 'Grandezas elétricas, leis de Ohm e Kirchhoff, análise CC e CA.'],
        ['id' => 14, 'title' => 'Eletrônica Analógica: Componentes e Aplicações', 'author' => 'Juliana Lima', 'publisher' => 'Érica', 'year' => '2023', 'pages' => 350, 'isbn' => '978-85-003-0002', 'subject' => 'Eletroeletrônica', 'quantity' => 28, 'desc' => 'Diodos, transistores, amplificadores e fontes de alimentação.'],
        ['id' => 15, 'title' => 'Eletrônica Digital e Microcontroladores', 'author' => 'Marcos Silva', 'publisher' => 'Érica', 'year' => '2024', 'pages' => 380, 'isbn' => '978-85-003-0003', 'subject' => 'Eletroeletrônica', 'quantity' => 24, 'desc' => 'Portas lógicas, sistemas digitais, programação embarcada e microcontroladores.'],
        ['id' => 16, 'title' => 'Instalações Elétricas Prediais e Industriais', 'author' => 'Carlos Eduardo', 'publisher' => 'Editora SENAI-SP', 'year' => '2022', 'pages' => 290, 'isbn' => '978-85-003-0004', 'subject' => 'Eletroeletrônica', 'quantity' => 18, 'desc' => 'Projetos, normas, proteção e execução de instalações elétricas.'],
        ['id' => 17, 'title' => 'CLP e Automação Industrial', 'author' => 'Fernanda Lima', 'publisher' => 'Saraiva', 'year' => '2023', 'pages' => 340, 'isbn' => '978-85-003-0005', 'subject' => 'Eletroeletrônica', 'quantity' => 15, 'desc' => 'Controladores lógicos programáveis, sensores, atuadores e comandos industriais.'],
        ['id' => 18, 'title' => 'Sistemas Embarcados e IoT Industrial', 'author' => 'Vitor Hugo', 'publisher' => 'Novatec', 'year' => '2024', 'pages' => 310, 'isbn' => '978-85-003-0006', 'subject' => 'Eletroeletrônica', 'quantity' => 10, 'desc' => 'Integração de sensores, conectividade, sistemas embarcados e aplicações IoT.'],
    ],

    'purchase_orders' => [
        ['orderId' => 'PED-7489', 'date' => '11/05/2026', 'time' => '10:15', 'status' => 'aguardando', 'items' => [['type' => 'restock', 'bookId' => 6, 'title' => 'Versionamento e DevOps com Git', 'requestedQty' => 20, 'justification' => 'Estoque crítico para o curso Desenvolvimento de Sistemas']]],
        ['orderId' => 'PED-6512', 'date' => '02/05/2026', 'time' => '11:40', 'status' => 'entregue', 'items' => [['type' => 'restock', 'bookId' => 10, 'title' => 'Direito Empresarial e Legislação Trabalhista', 'requestedQty' => 15, 'justification' => 'Reposição para o curso Administração']]],
        ['orderId' => 'PED-3210', 'date' => '20/04/2026', 'time' => '14:30', 'status' => 'entregue', 'items' => [['type' => 'new', 'bookId' => null, 'title' => 'Robótica Aplicada à Eletroeletrônica', 'requestedQty' => 30, 'justification' => 'Material complementar para laboratório'], ['type' => 'restock', 'bookId' => 12, 'title' => 'Gestão de Projetos: PMBOK e Metodologias', 'requestedQty' => 20, 'justification' => 'Estoque crítico para turmas novas']]],
        ['orderId' => 'PED-1102', 'date' => '15/03/2026', 'time' => '09:00', 'status' => 'entregue', 'items' => [['type' => 'restock', 'bookId' => 3, 'title' => 'Desenvolvimento Web com HTML, CSS e JavaScript', 'requestedQty' => 50, 'justification' => 'Aumento de turmas de Desenvolvimento de Sistemas']]],
    ],

    'teacher_requests' => [
        ['id' => 201, 'teacher' => 'Prof. Carlos Mendes', 'email' => 'carlos.mendes@escola.senai.br', 'subject' => 'Administração', 'bookId' => 8, 'title' => 'Contabilidade Geral e Gestão Financeira', 'qty' => 18, 'status' => 'pendente', 'date' => '11/05/2026', 'time' => '09:30'],
        ['id' => 202, 'teacher' => 'Profa. Ana Paula', 'email' => 'ana.paula@escola.senai.br', 'subject' => 'Desenvolvimento de Sistemas', 'bookId' => 3, 'title' => 'Desenvolvimento Web com HTML, CSS e JavaScript', 'qty' => 40, 'status' => 'pendente', 'date' => '10/05/2026', 'time' => '14:15'],
        ['id' => 203, 'teacher' => 'Prof. Roberto Alves', 'email' => 'roberto.alves@escola.senai.br', 'subject' => 'Eletroeletrônica', 'bookId' => 13, 'title' => 'Circuitos Elétricos e Análise de Redes', 'qty' => 25, 'status' => 'atendido', 'date' => '09/05/2026', 'time' => '11:00'],
        ['id' => 204, 'teacher' => 'Prof. Fernando Souza', 'email' => 'fernando.souza@escola.senai.br', 'subject' => 'Eletroeletrônica', 'bookId' => 17, 'title' => 'CLP e Automação Industrial', 'qty' => 15, 'status' => 'pendente', 'date' => '11/05/2026', 'time' => '15:20'],
    ],
];
