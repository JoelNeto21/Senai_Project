import React, { useState, useMemo } from 'react';
import { 
  Book, 
  ArrowDownToLine, 
  ArrowUpFromLine, 
  AlertCircle, 
  LayoutGrid, 
  LogOut, 
  Menu, 
  X, 
  Search,
  Plus,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Info,
  Image as ImageIcon,
  BarChart3,
  Download,
  FileSpreadsheet,
  TrendingUp,
  Trash2,
  PlusCircle,
  Lightbulb,
  Sparkles,
  Clock,
  TrendingDown,
  ClipboardList,
  PackageCheck,
  Bell,
  Star,
  User,
  Building,
  FileText,
  Calendar,
  ShoppingCart,
  Send,
  GraduationCap,
  CreditCard,
  Truck,
  Check,
  XCircle,
  Mail,
  History,
  Printer,
  Folder,
  FolderOpen,
  ChevronDown
} from 'lucide-react';

export default function App() {
  // --- Estados Globais ---
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [activeView, setActiveView] = useState('insights');
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [notification, setNotification] = useState(null);
  
  // Estado para visualização detalhada de um livro
  const [selectedBook, setSelectedBook] = useState(null);

  // Estado global para o "Carrinho de Compras" do Almoxarife
  const [purchaseCart, setPurchaseCart] = useState([]);

  // Estado global para a "Lista de Retirada"
  const [withdrawCart, setWithdrawCart] = useState([]);

  // --- Dados Simulados (Mock Avançado) ---
  const [books, setBooks] = useState([
    // Elétrica
    { id: 1, title: 'Fundamentos de Eletricidade', author: 'Roberto Alves', publisher: 'Editora SENAI-SP', year: '2023', pages: 280, isbn: '978-85-111', subject: 'Elétrica', quantity: 45, desc: 'Conceitos essenciais de tensão, corrente, resistência e circuitos elétricos aplicados à indústria.' },
    { id: 5, title: 'Instalações Elétricas Prediais', author: 'Juliana Costa', publisher: 'Editora SENAI-SP', year: '2022', pages: 350, isbn: '978-85-555', subject: 'Elétrica', quantity: 30, desc: 'Projetos e execução de instalações elétricas de baixa tensão.' },
    { id: 8, title: 'Comandos Elétricos', author: 'Marcos Silva', publisher: 'Érica', year: '2021', pages: 210, isbn: '978-85-888', subject: 'Elétrica', quantity: 20, desc: 'Acionamento e proteção de máquinas elétricas.' },
    { id: 9, title: 'CLP Básico e Avançado', author: 'Fernanda Lima', publisher: 'Érica', year: '2023', pages: 415, isbn: '978-85-999', subject: 'Elétrica', quantity: 12, desc: 'Controladores Lógicos Programáveis na automação industrial.' },
    { id: 10, title: 'Sistemas Fotovoltaicos', author: 'Carlos Eduardo', publisher: 'Editora SENAI-SP', year: '2024', pages: 190, isbn: '978-85-101', subject: 'Elétrica', quantity: 5, desc: 'Instalação e manutenção de painéis solares.' },
    
    // Mecânica
    { id: 2, title: 'Torno CNC Básico', author: 'Antônio Peixoto', publisher: 'Editora SENAI-SP', year: '2022', pages: 240, isbn: '978-85-222', subject: 'Mecânica', quantity: 5, desc: 'Programação e operação de tornos com Comando Numérico Computadorizado.' },
    { id: 6, title: 'Desenho Técnico Mecânico', author: 'Lúcia Ferreira', publisher: 'Érica', year: '2020', pages: 310, isbn: '978-85-666', subject: 'Mecânica', quantity: 15, desc: 'Leitura e interpretação de desenhos técnicos mecânicos.' },
    { id: 11, title: 'Metrologia Industrial', author: 'Renato Mendes', publisher: 'Editora SENAI-SP', year: '2021', pages: 180, isbn: '978-85-112', subject: 'Mecânica', quantity: 25, desc: 'Instrumentos de medição e controle dimensional.' },
    { id: 12, title: 'Usinagem Aplicada', author: 'Sérgio Ramos', publisher: 'Saraiva', year: '2019', pages: 420, isbn: '978-85-113', subject: 'Mecânica', quantity: 8, desc: 'Processos de fresamento, torneamento e furação.' },
    { id: 13, title: 'Manutenção Mecânica', author: 'Vitor Hugo', publisher: 'Editora SENAI-SP', year: '2023', pages: 265, isbn: '978-85-114', subject: 'Mecânica', quantity: 18, desc: 'Técnicas de manutenção preventiva e corretiva.' },

    // Tecnologia
    { id: 3, title: 'Lógica de Programação com Python', author: 'Ana Paula', publisher: 'Novatec', year: '2023', pages: 340, isbn: '978-85-333', subject: 'Tecnologia', quantity: 120, desc: 'Introdução à algoritmia e estruturas de dados utilizando a linguagem Python.' },
    { id: 7, title: 'Redes de Computadores', author: 'Gabriel Torres', publisher: 'Novatec', year: '2021', pages: 500, isbn: '978-85-777', subject: 'Tecnologia', quantity: 50, desc: 'Infraestrutura, protocolos e configuração de redes de dados.' },
    { id: 14, title: 'Desenvolvimento Web com React', author: 'Diego Fernandes', publisher: 'Casa do Código', year: '2024', pages: 290, isbn: '978-85-115', subject: 'Tecnologia', quantity: 35, desc: 'Criação de interfaces modernas e responsivas.' },
    { id: 15, title: 'Banco de Dados SQL', author: 'Patrícia Silva', publisher: 'Érica', year: '2020', pages: 380, isbn: '978-85-116', subject: 'Tecnologia', quantity: 40, desc: 'Modelagem, consultas e administração de bancos relacionais.' },
    { id: 16, title: 'Cibersegurança Básica', author: 'Lucas Martins', publisher: 'Alta Books', year: '2023', pages: 210, isbn: '978-85-117', subject: 'Tecnologia', quantity: 9, desc: 'Princípios de proteção de dados e sistemas de informação.' },

    // Segurança
    { id: 4, title: 'Segurança do Trabalho NR-10', author: 'Mário Souza', publisher: 'Editora SENAI-SP', year: '2022', pages: 150, isbn: '978-85-444', subject: 'Segurança', quantity: 8, desc: 'Norma Regulamentadora nº 10 - Segurança em Instalações e Serviços em Eletricidade.' },
    { id: 17, title: 'NR-35 Trabalho em Altura', author: 'Equipe SENAI', publisher: 'Editora SENAI-SP', year: '2021', pages: 120, isbn: '978-85-118', subject: 'Segurança', quantity: 22, desc: 'Requisitos e medidas de proteção para o trabalho em altura.' },
    { id: 18, title: 'Primeiros Socorros no Trabalho', author: 'Dr. Fernando', publisher: 'Atheneu', year: '2023', pages: 200, isbn: '978-85-119', subject: 'Segurança', quantity: 15, desc: 'Atendimento pré-hospitalar em ambientes industriais.' },
    { id: 19, title: 'EPI e EPC Industrial', author: 'Camila Rocha', publisher: 'Saraiva', year: '2020', pages: 175, isbn: '978-85-120', subject: 'Segurança', quantity: 30, desc: 'Seleção, uso e conservação de equipamentos de proteção.' },
    { id: 20, title: 'Ergonomia Industrial', author: 'Pedro Almeida', publisher: 'Érica', year: '2022', pages: 230, isbn: '978-85-121', subject: 'Segurança', quantity: 11, desc: 'Adequação do ambiente de trabalho ao ser humano.' },
  ]);

  // Histórico de Compras Feitas pelo Almoxarifado (Com mais dados para testar as pastas)
  const [purchaseOrders, setPurchaseOrders] = useState([
    { 
      orderId: 'PED-7489', 
      date: '11/05/2026', 
      time: '10:15',
      status: 'aguardando', 
      items: [
        { type: 'restock', bookId: 2, title: 'Torno CNC Básico', requestedQty: 20, justification: 'Estoque crítico, fornecedor: Editora SENAI-SP' }
      ]
    },
    { 
      orderId: 'PED-6512', 
      date: '02/05/2026', 
      time: '11:40',
      status: 'entregue', 
      items: [
        { type: 'new', bookId: null, title: 'Segurança do Trabalho NR-10', requestedQty: 10, justification: 'Material de apoio complementar' }
      ]
    },
    { 
      orderId: 'PED-3210', 
      date: '20/04/2026', 
      time: '14:30',
      status: 'entregue', 
      items: [
        { type: 'new', bookId: null, title: 'Introdução à Robótica', requestedQty: 30, justification: 'Nova grade, fornecedor: Novatec' },
        { type: 'restock', bookId: 10, title: 'Sistemas Fotovoltaicos', requestedQty: 15, justification: 'Reposição anual, fornecedor: Saraiva' }
      ]
    },
    { 
      orderId: 'PED-1102', 
      date: '15/03/2026', 
      time: '09:00',
      status: 'entregue', 
      items: [
        { type: 'restock', bookId: 3, title: 'Lógica de Programação com Python', requestedQty: 50, justification: 'Aumento de turmas na área de TI' }
      ]
    }
  ]);

  // Pedidos feitos por Professores (agora com campo email para comunicação direta)
  const [teacherRequests, setTeacherRequests] = useState([
    { id: 201, teacher: 'Prof. Carlos Mendes', email: 'carlos.mendes@escola.senai.br', subject: 'Mecânica', bookId: 13, title: 'Manutenção Mecânica', qty: 18, status: 'pendente', date: '11/05/2026', time: '09:30' },
    { id: 202, teacher: 'Profa. Ana Paula', email: 'ana.paula@escola.senai.br', subject: 'Tecnologia', bookId: 3, title: 'Lógica de Programação com Python', qty: 40, status: 'pendente', date: '10/05/2026', time: '14:15' },
    { id: 204, teacher: 'Prof. Fernando Souza', email: 'fernando.souza@escola.senai.br', subject: 'Mecânica', bookId: 2, title: 'Torno CNC Básico', qty: 15, status: 'pendente', date: '11/05/2026', time: '15:20' },
    { id: 203, teacher: 'Prof. Roberto Alves', email: 'roberto.alves@escola.senai.br', subject: 'Elétrica', bookId: 1, title: 'Fundamentos de Eletricidade', qty: 45, status: 'atendido', date: '09/05/2026', time: '11:00' }
  ]);

  // --- Funções Auxiliares ---
  const showNotification = (message, type = 'success') => {
    setNotification({ message, type });
    setTimeout(() => setNotification(null), 3500);
  };

  const handleLogin = (e) => {
    e.preventDefault();
    setIsAuthenticated(true);
    showNotification('Sessão iniciada com sucesso.');
  };

  const handleLogout = () => {
    setIsAuthenticated(false);
    setActiveView('insights');
  };

  // --- Componentes Base (Design System) ---
  const Toast = () => {
    if (!notification) return null;
    const isError = notification.type === 'error';
    return (
      <div className="fixed top-6 left-1/2 -translate-x-1/2 flex items-center px-5 py-3 rounded-full shadow-sm text-sm font-medium z-50 transition-all bg-white border border-gray-200 text-gray-800 animate-in fade-in slide-in-from-top-4">
        {isError ? <AlertCircle className="w-5 h-5 mr-3 text-red-500" /> : <CheckCircle2 className="w-5 h-5 mr-3 text-emerald-500" />}
        {notification.message}
      </div>
    );
  };

  const PageHeader = ({ title, subtitle }) => (
    <div className="mb-8">
      <h1 className="text-3xl font-semibold tracking-tight text-gray-900">{title}</h1>
      {subtitle && <p className="text-gray-500 mt-1 text-base">{subtitle}</p>}
    </div>
  );

  const Card = ({ children, className = '' }) => (
    <div className={`bg-white rounded-3xl p-6 sm:p-8 ${className}`}>
      {children}
    </div>
  );

  const ChevronDownIcon = (props) => (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...props}>
      <polyline points="6 9 12 15 18 9"></polyline>
    </svg>
  );

  // ==========================================
  // TELAS DO SISTEMA
  // ==========================================

  const LoginScreen = () => (
    <div className="min-h-screen flex items-center justify-center bg-[#F5F5F7] p-4 font-sans">
      <div className="bg-white p-10 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] w-full max-w-sm">
        <div className="text-center mb-10">
          <div className="flex justify-center mb-6">
            <div className="w-14 h-14 bg-red-600 rounded-2xl flex items-center justify-center">
              <Book className="w-7 h-7 text-white" />
            </div>
          </div>
          <h1 className="text-2xl font-semibold tracking-tight text-gray-900">SenaiStock</h1>
          <p className="text-gray-500 text-sm mt-2">Acesse seu acervo</p>
        </div>

        <form onSubmit={handleLogin} className="space-y-5">
          <div>
            <input 
              type="text" 
              placeholder="NIF ou Usuário"
              defaultValue="almoxarifado" 
              className="w-full px-4 py-3.5 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none transition-all" 
              required 
            />
          </div>
          <div>
            <input 
              type="password" 
              placeholder="Senha"
              defaultValue="123456" 
              className="w-full px-4 py-3.5 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none transition-all" 
              required 
            />
          </div>
          <button type="submit" className="w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-3.5 px-4 rounded-xl transition-colors mt-2">
            Continuar
          </button>
        </form>
      </div>
    </div>
  );

  const Insights = () => {
    const [searchQuery, setSearchQuery] = useState('');
    const [isSearchFocused, setIsSearchFocused] = useState(false);

    const hour = new Date().getHours();
    let greeting = 'Boa noite';
    if (hour >= 5 && hour < 12) greeting = 'Bom dia';
    else if (hour >= 12 && hour < 18) greeting = 'Boa tarde';

    const searchResults = useMemo(() => {
      if (!searchQuery.trim()) return [];
      const query = searchQuery.toLowerCase();

      const features = [
         { type: 'page', id: 'teacher_requests', title: 'Pedidos de Professores', icon: GraduationCap },
         { type: 'page', id: 'purchases', title: 'Gestão de Compras', icon: CreditCard },
         { type: 'page', id: 'history', title: 'Histórico de Compras', icon: History },
         { type: 'page', id: 'receive', title: 'Recebimento de Estoque', icon: ArrowDownToLine },
         { type: 'page', id: 'withdraw', title: 'Retirada Direta', icon: ArrowUpFromLine },
         { type: 'page', id: 'dashboard', title: 'Dashboard & Relatórios', icon: BarChart3 },
         { type: 'page', id: 'overview', title: 'Visão Geral do Almoxarifado', icon: LayoutGrid },
      ].filter(f => f.title.toLowerCase().includes(query));

      const bookResults = books
         .filter(b => b.title.toLowerCase().includes(query) || b.isbn.includes(query) || b.subject.toLowerCase().includes(query))
         .map(b => ({ type: 'book', data: b, title: b.title, desc: `ISBN: ${b.isbn} • Área: ${b.subject}` }));

      return [...features, ...bookResults].slice(0, 6);
    }, [searchQuery, books]);

    const handleSelectResult = (result) => {
       if (result.type === 'page') {
          setActiveView(result.id);
       } else if (result.type === 'book') {
          setActiveView('library');
          setSelectedBook(result.data);
       }
       setSearchQuery(''); 
    };

    const lowStockCount = books.filter(b => b.quantity < 8).length;
    const totalQuantity = books.reduce((acc, b) => acc + b.quantity, 0);

    return (
       <div className="animate-in fade-in duration-500 max-w-4xl mx-auto pt-4 md:pt-10">
          <div className="text-center mb-10">
             <div className="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-red-50 text-red-600 text-sm font-medium mb-6 shadow-sm border border-red-100">
                <Sparkles className="w-4 h-4 mr-2" />
                Novos recursos adicionados
             </div>
             <h1 className="text-4xl md:text-5xl font-semibold tracking-tight text-gray-900 mb-4">
                {greeting}, Almoxarifado.
             </h1>
             <p className="text-lg text-gray-500 font-medium">O que você está procurando hoje?</p>
          </div>

          <div className="relative max-w-2xl mx-auto mb-20 z-20">
             <div className={`relative flex items-center bg-white rounded-2xl transition-all duration-300 ${isSearchFocused ? 'shadow-[0_8px_30px_rgb(0,0,0,0.08)] ring-2 ring-red-500/20 border-red-200' : 'shadow-sm border border-gray-200'}`}>
                <Search className={`absolute left-5 w-6 h-6 transition-colors ${isSearchFocused ? 'text-red-500' : 'text-gray-400'}`} />
                <input
                   type="text"
                   value={searchQuery}
                   onChange={(e) => setSearchQuery(e.target.value)}
                   onFocus={() => setIsSearchFocused(true)}
                   onBlur={() => setTimeout(() => setIsSearchFocused(false), 200)}
                   placeholder="Busque por livros, ISBN ou ações rápidas..."
                   className="w-full bg-transparent border-0 py-5 pl-14 pr-6 text-gray-900 placeholder-gray-400 focus:ring-0 outline-none text-lg rounded-2xl"
                />
             </div>

             {isSearchFocused && searchQuery.trim() && (
                <div className="absolute top-full left-0 w-full mt-3 bg-white rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.1)] border border-gray-100 overflow-hidden animate-in slide-in-from-top-2">
                   {searchResults.length === 0 ? (
                      <div className="p-8 text-center text-gray-500">
                        Nenhum resultado encontrado para "{searchQuery}".
                      </div>
                   ) : (
                      <ul className="py-2">
                         {searchResults.map((res, i) => (
                            <li key={i}>
                               <button
                                  onClick={() => handleSelectResult(res)}
                                  className="w-full text-left px-6 py-4 hover:bg-gray-50 flex items-center transition-colors group"
                               >
                                  {res.type === 'page' ? (
                                     <div className="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center mr-4 group-hover:bg-red-100 transition-colors">
                                        <res.icon className="w-5 h-5 text-red-600" />
                                     </div>
                                  ) : (
                                     <div className="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mr-4 group-hover:bg-gray-200 transition-colors">
                                        <Book className="w-5 h-5 text-gray-500" />
                                     </div>
                                  )}
                                  <div>
                                     <p className="font-medium text-gray-900 text-base">{res.title}</p>
                                     {res.desc && <p className="text-sm text-gray-500 mt-0.5">{res.desc}</p>}
                                  </div>
                               </button>
                            </li>
                         ))}
                      </ul>
                   )}
                </div>
             )}
          </div>

          <div className="flex items-center justify-between px-2 mb-5">
             <h2 className="text-xl font-semibold text-gray-900 tracking-tight">Insights Rápidos</h2>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
             <Card className="border border-gray-100 shadow-sm hover:shadow-md transition-shadow !p-6 cursor-pointer group" onClick={() => setActiveView('overview')}>
                <div className="flex items-center justify-between mb-6">
                   <div className="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-red-600 group-hover:scale-110 transition-transform">
                      <TrendingDown className="w-6 h-6" />
                   </div>
                </div>
                <h3 className="text-gray-500 text-sm font-medium mb-1">Atenção Necessária</h3>
                <p className="text-2xl font-semibold text-gray-900">{lowStockCount} títulos</p>
                <p className="text-sm text-gray-400 mt-1">com estoque abaixo do limite mínimo</p>
             </Card>
             
             <Card className="border border-gray-100 shadow-sm hover:shadow-md transition-shadow !p-6 cursor-pointer group" onClick={() => setActiveView('dashboard')}>
                <div className="flex items-center justify-between mb-6">
                   <div className="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                      <Book className="w-6 h-6" />
                   </div>
                </div>
                <h3 className="text-gray-500 text-sm font-medium mb-1">Volume Total Físico</h3>
                <p className="text-2xl font-semibold text-gray-900">{totalQuantity} exemplares</p>
                <p className="text-sm text-gray-400 mt-1">disponíveis em todo o almoxarifado</p>
             </Card>

             <Card className="border border-gray-100 shadow-sm hover:shadow-md transition-shadow !p-6 cursor-pointer group" onClick={() => setActiveView('teacher_requests')}>
                <div className="flex items-center justify-between mb-6">
                   <div className="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:scale-110 transition-transform">
                      <GraduationCap className="w-6 h-6" />
                   </div>
                </div>
                <h3 className="text-gray-500 text-sm font-medium mb-1">Pedidos de Professores</h3>
                <p className="text-2xl font-semibold text-gray-900">{teacherRequests.filter(r => r.status === 'pendente').length}</p>
                <p className="text-sm text-gray-400 mt-1">aguardando liberação e separação</p>
             </Card>
          </div>

          <div className="mt-10 mb-10">
             <div className="flex items-center justify-between px-2 mb-5">
                <h2 className="text-xl font-semibold text-gray-900 tracking-tight flex items-center">
                   <Bell className="w-5 h-5 mr-2 text-gray-400" /> Atualizações e Tarefas
                </h2>
                <button onClick={() => setActiveView('teacher_requests')} className="text-sm font-medium text-red-600 hover:text-red-700 transition-colors">
                   Ver todas
                </button>
             </div>
             <Card className="border border-gray-100 shadow-sm !p-0 overflow-hidden">
                {teacherRequests.length === 0 ? (
                   <div className="p-8 text-center text-gray-500">
                      Nenhuma atualização recente.
                   </div>
                ) : (
                   <ul className="divide-y divide-gray-100">
                      {teacherRequests.slice(0, 4).map((req) => {
                         let statusConfig = { icon: Clock, color: 'text-amber-600', bg: 'bg-amber-50', text: 'Aguardando sua liberação' };
                         
                         if (req.status === 'atendido') {
                            statusConfig = { icon: CheckCircle2, color: 'text-emerald-600', bg: 'bg-emerald-50', text: 'Material separado e entregue' };
                         }

                         const IconComponent = statusConfig.icon;

                         return (
                            <li key={req.id} className="p-6 hover:bg-gray-50/80 transition-colors flex items-start gap-4 group/item cursor-pointer" onClick={() => setActiveView('teacher_requests')}>
                               <div className={`w-11 h-11 rounded-full flex items-center justify-center shrink-0 ${statusConfig.bg} ${statusConfig.color} shadow-sm border border-white`}>
                                  <IconComponent className="w-5 h-5" />
                               </div>
                               <div className="flex-1">
                                  <div className="flex items-center justify-between mb-1">
                                     <p className="font-semibold text-gray-900 group-hover/item:text-red-600 transition-colors">
                                       {req.teacher} solicitou {req.qty}x {req.title}
                                     </p>
                                     <span className="text-xs text-gray-400 font-medium whitespace-nowrap ml-2">{req.date}</span>
                                  </div>
                                  <p className="text-sm text-gray-600">
                                     Turma de <span className="font-medium text-gray-900">{req.subject}</span> — <span className={`${statusConfig.color} font-medium`}>{statusConfig.text}</span>
                                  </p>
                               </div>
                            </li>
                         );
                      })}
                   </ul>
                )}
             </Card>
          </div>
       </div>
    );
  };

  const Overview = () => {
    const lowStockBooks = books.filter(b => b.quantity < 8);
    const totalBooks = books.reduce((acc, curr) => acc + curr.quantity, 0);

    return (
      <div className="animate-in fade-in duration-500">
        <PageHeader title="Visão Geral" subtitle="Resumo do seu acervo no almoxarifado." />
        
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <Card className="border border-gray-100 shadow-sm">
            <h3 className="text-gray-500 text-sm font-medium mb-1">Títulos Diferentes</h3>
            <p className="text-4xl font-semibold text-gray-900">{books.length}</p>
          </Card>
          <Card className="border border-gray-100 shadow-sm">
            <h3 className="text-gray-500 text-sm font-medium mb-1">Total de Exemplares</h3>
            <p className="text-4xl font-semibold text-gray-900">{totalBooks}</p>
          </Card>
          <Card className="border border-red-100 bg-red-50/50">
            <h3 className="text-red-600 text-sm font-medium mb-1 flex items-center">
              <AlertCircle className="w-4 h-4 mr-1.5" /> Requer Atenção
            </h3>
            <p className="text-4xl font-semibold text-red-700">{lowStockBooks.length}</p>
          </Card>
        </div>

        {lowStockBooks.length > 0 && (
          <div className="mb-8">
            <h2 className="text-lg font-semibold text-gray-900 mb-4 px-2">Baixo Estoque</h2>
            <div className="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="border-b border-gray-100 text-gray-400 text-sm">
                    <th className="px-6 py-4 font-medium">Livro</th>
                    <th className="px-6 py-4 font-medium">Área</th>
                    <th className="px-6 py-4 font-medium text-right">Saldo</th>
                  </tr>
                </thead>
                <tbody>
                  {lowStockBooks.map(book => (
                    <tr key={book.id} className="group hover:bg-gray-50/50 transition-colors cursor-pointer" onClick={() => { setActiveView('library'); setSelectedBook(book); }}>
                      <td className="px-6 py-4 border-b border-gray-50">
                        <p className="font-medium text-gray-900">{book.title}</p>
                        <p className="text-sm text-gray-500">ISBN: {book.isbn}</p>
                      </td>
                      <td className="px-6 py-4 border-b border-gray-50 text-gray-500">{book.subject}</td>
                      <td className="px-6 py-4 border-b border-gray-50 text-right">
                        <span className="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">
                          {book.quantity} un
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>
    );
  };

  const DashboardView = () => {
    const handleExportPDF = () => {
      showNotification('Preparando documento para impressão/PDF...', 'success');
      setTimeout(() => {
        window.print();
      }, 800);
    };

    const booksBySubject = useMemo(() => {
      const totals = {};
      books.forEach(b => {
        totals[b.subject] = (totals[b.subject] || 0) + b.quantity;
      });
      return Object.entries(totals).sort((a, b) => b[1] - a[1]);
    }, [books]);
    
    const maxSubjectQty = Math.max(...booksBySubject.map(b => b[1]), 1);

    const totalAdequate = books.filter(b => b.quantity >= 8).length;
    const totalCritical = books.filter(b => b.quantity < 8).length;
    const totalBooksCount = books.length || 1;
    
    const adequateHeight = Math.max((totalAdequate / totalBooksCount) * 100, 15);
    const criticalHeight = Math.max((totalCritical / totalBooksCount) * 100, 15);

    return (
      <div className="animate-in fade-in duration-500">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
          <div>
            <h1 className="text-3xl font-semibold tracking-tight text-gray-900">Dashboard</h1>
            <p className="text-gray-500 mt-1 text-base">Análises detalhadas e relatórios completos do acervo.</p>
          </div>
          <button 
            onClick={handleExportPDF}
            className="flex items-center px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-sm font-medium transition-colors shadow-sm shrink-0"
          >
            <Download className="w-4 h-4 mr-2" />
            Exportar PDF
          </button>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <Card className="border border-gray-100 shadow-sm flex flex-col">
            <h3 className="text-lg font-semibold text-gray-900 mb-6 flex items-center">
              <BarChart3 className="w-5 h-5 mr-2 text-gray-400" /> 
              Volume em Estoque por Área
            </h3>
            <div className="flex-1 flex flex-col justify-end space-y-5">
              {booksBySubject.map(([subject, qty]) => (
                <div key={subject} className="flex items-center text-sm">
                  <span className="w-24 text-gray-500 font-medium truncate pr-2" title={subject}>{subject}</span>
                  <div className="flex-1 bg-gray-50 rounded-full h-3.5 overflow-hidden flex items-center shadow-inner">
                    <div 
                      className="bg-gray-900 h-full rounded-full transition-all duration-1000 ease-out" 
                      style={{ width: `${(qty / maxSubjectQty) * 100}%` }}
                    />
                  </div>
                  <span className="w-12 text-right font-semibold text-gray-900 ml-3">{qty}</span>
                </div>
              ))}
            </div>
          </Card>
          
          <Card className="border border-gray-100 shadow-sm flex flex-col">
            <h3 className="text-lg font-semibold text-gray-900 mb-8 flex items-center">
              <TrendingUp className="w-5 h-5 mr-2 text-gray-400" /> 
              Saúde do Acervo (Status)
            </h3>
            <div className="flex-1 flex items-end justify-center h-48 pb-6 border-b border-gray-100 px-8">
               <div className="w-full max-w-[250px] flex gap-8 h-full items-end">
                  <div className="flex-1 flex flex-col items-center gap-3 group h-full justify-end">
                    <div 
                      className="w-full max-w-[64px] bg-emerald-100 rounded-t-xl group-hover:bg-emerald-200 transition-colors relative"
                      style={{ height: `${adequateHeight}%` }}
                    >
                      <span className="absolute -top-7 left-1/2 -translate-x-1/2 text-sm font-bold text-emerald-700">{totalAdequate}</span>
                    </div>
                    <span className="text-xs font-semibold text-gray-500 tracking-wide uppercase">Adequado</span>
                  </div>
                  <div className="flex-1 flex flex-col items-center gap-3 group h-full justify-end">
                    <div 
                      className="w-full max-w-[64px] bg-red-100 rounded-t-xl group-hover:bg-red-200 transition-colors relative"
                      style={{ height: `${criticalHeight}%` }}
                    >
                      <span className="absolute -top-7 left-1/2 -translate-x-1/2 text-sm font-bold text-red-700">{totalCritical}</span>
                    </div>
                    <span className="text-xs font-semibold text-gray-500 tracking-wide uppercase">Crítico</span>
                  </div>
               </div>
            </div>
          </Card>
        </div>

        <div className="mb-8">
          <div className="flex items-center justify-between mb-4 px-2">
            <h2 className="text-lg font-semibold text-gray-900 flex items-center">
              <FileSpreadsheet className="w-5 h-5 mr-2 text-gray-400" />
              Tabela Mestra de Livros
            </h2>
          </div>
          <div className="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
            <div className="overflow-x-auto [&::-webkit-scrollbar]:h-2 [&::-webkit-scrollbar-thumb]:bg-gray-300 [&::-webkit-scrollbar-thumb]:rounded-full pb-2">
              <table className="w-full text-left border-collapse text-sm whitespace-nowrap">
                <thead>
                  <tr className="bg-gray-50/80 border-b border-gray-200 text-gray-500">
                    <th className="px-4 py-3.5 font-medium border-r border-gray-200 w-16 text-center">ID</th>
                    <th className="px-4 py-3.5 font-medium border-r border-gray-200 w-32">ISBN</th>
                    <th className="px-4 py-3.5 font-medium border-r border-gray-200 min-w-[280px]">Título da Obra</th>
                    <th className="px-4 py-3.5 font-medium border-r border-gray-200">Área</th>
                    <th className="px-4 py-3.5 font-medium border-r border-gray-200 text-right w-24">Qtd.</th>
                    <th className="px-4 py-3.5 font-medium text-center w-32">Status Operacional</th>
                  </tr>
                </thead>
                <tbody>
                  {books.map((book, idx) => (
                    <tr key={book.id} className={`hover:bg-blue-50/40 transition-colors ${idx !== books.length - 1 ? 'border-b border-gray-100' : ''}`}>
                      <td className="px-4 py-2.5 border-r border-gray-100 text-center text-gray-400 font-mono text-xs">{book.id.toString().padStart(3, '0')}</td>
                      <td className="px-4 py-2.5 border-r border-gray-100 text-gray-500 font-mono text-xs">{book.isbn}</td>
                      <td className="px-4 py-2.5 border-r border-gray-100 text-gray-900 font-medium truncate max-w-[300px]">{book.title}</td>
                      <td className="px-4 py-2.5 border-r border-gray-100 text-gray-600">{book.subject}</td>
                      <td className="px-4 py-2.5 border-r border-gray-100 text-right font-semibold text-gray-800">{book.quantity}</td>
                      <td className="px-4 py-2.5 text-center">
                        {book.quantity < 8 ? (
                          <span className="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-50 text-red-600 border border-red-100">
                            ESTOQUE CRÍTICO
                          </span>
                        ) : (
                          <span className="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                            ADEQUADO
                          </span>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    );
  };

  // --- Nova Aba: Gestão de Compras (Almoxarife com controle total) ---
  const PurchasesView = () => {
    const [reqType, setReqType] = useState('restock');
    const [bookId, setBookId] = useState('');
    const [newTitle, setNewTitle] = useState('');
    const [quantity, setQuantity] = useState('');
    const [justification, setJustification] = useState('');

    const handleAddToCart = (e) => {
      e.preventDefault();
      
      const isRestock = reqType === 'restock';
      const titleToAdd = isRestock ? books.find(b => b.id === parseInt(bookId))?.title : newTitle;
      
      if (!titleToAdd || quantity <= 0) return;

      const newItem = {
        cartId: Date.now().toString(),
        type: reqType,
        bookId: isRestock ? parseInt(bookId) : null,
        title: titleToAdd,
        quantity: parseInt(quantity),
        justification: justification
      };

      setPurchaseCart([...purchaseCart, newItem]);
      showNotification(`"${titleToAdd}" adicionado à lista de compras.`);
      
      setBookId('');
      setNewTitle('');
      setQuantity('');
      setJustification('');
    };

    const handleUpdateCartItem = (cartId, field, value) => {
      setPurchaseCart(purchaseCart.map(item => item.cartId === cartId ? { ...item, [field]: value } : item));
    };

    const handleRemoveCartItem = (cartId) => {
      setPurchaseCart(purchaseCart.filter(item => item.cartId !== cartId));
    };

    const handleSendRequests = () => {
      if (purchaseCart.length === 0) return;

      const newOrder = {
        orderId: `PED-${Math.floor(1000 + Math.random() * 9000)}`,
        date: new Date().toLocaleDateString('pt-BR'),
        time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }),
        status: 'aguardando',
        items: purchaseCart.map(item => ({
          type: item.type,
          bookId: item.bookId,
          title: item.title,
          requestedQty: parseInt(item.quantity) || 1,
          justification: item.justification || 'Reposição de estoque.'
        }))
      };

      setPurchaseOrders([newOrder, ...purchaseOrders]);
      setPurchaseCart([]);
      showNotification('Pedido de compra gerado com sucesso! Redirecionando para as planilhas...');
      
      // Redireciona para a aba de Histórico para ver a planilha gerada
      setTimeout(() => setActiveView('history'), 600);
    };

    const totalCartItems = purchaseCart.reduce((acc, item) => acc + (parseInt(item.quantity) || 0), 0);

    return (
      <div className="animate-in fade-in duration-500">
        <PageHeader title="Gestão de Compras" subtitle="Monte a lista de reposição e registre as compras junto às editoras." />
        
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
          
          <div className="lg:col-span-8 flex flex-col gap-8">
            <div>
              <h2 className="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <ShoppingCart className="w-5 h-5 mr-2 text-gray-400" />
                Lista de Compras
              </h2>
              
              <div className="space-y-3">
                {purchaseCart.length === 0 ? (
                  <div className="bg-white border border-gray-200 border-dashed rounded-2xl p-10 text-center flex flex-col items-center justify-center">
                    <div className="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                      <ShoppingCart className="w-8 h-8 text-gray-300" />
                    </div>
                    <p className="text-gray-500 font-medium">Sua lista de compras está vazia.</p>
                    <p className="text-gray-400 text-sm mt-1">Identifique as faltas no acervo e adicione itens para cotar/comprar.</p>
                  </div>
                ) : (
                  purchaseCart.map((item) => (
                    <div key={item.cartId} className="bg-white border border-gray-100 shadow-sm rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row gap-4 sm:items-center relative animate-in slide-in-from-bottom-2">
                      <div className="flex items-center flex-1">
                        <div className={`w-10 h-10 rounded-full flex items-center justify-center shrink-0 mr-4 ${item.type === 'restock' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600'}`}>
                          {item.type === 'restock' ? <Book className="w-5 h-5" /> : <Sparkles className="w-5 h-5" />}
                        </div>
                        <div>
                          <p className="font-semibold text-gray-900 truncate max-w-[200px] sm:max-w-xs">{item.title}</p>
                          <span className="text-[10px] uppercase font-bold tracking-wider text-gray-400">
                            {item.type === 'restock' ? 'Reposição' : 'Título Inédito'}
                          </span>
                        </div>
                      </div>

                      <div className="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:w-1/2">
                        <input 
                          type="text" 
                          value={item.justification}
                          onChange={(e) => handleUpdateCartItem(item.cartId, 'justification', e.target.value)}
                          placeholder="Motivo / Justificativa"
                          className="w-full bg-gray-50 border border-gray-100 rounded-xl px-3 py-2 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 outline-none transition-all"
                        />
                        <div className="flex items-center gap-2 w-full sm:w-auto">
                          <input 
                            type="number" 
                            min="1"
                            value={item.quantity}
                            onChange={(e) => handleUpdateCartItem(item.cartId, 'quantity', e.target.value)}
                            className="w-20 bg-gray-50 border border-gray-100 rounded-xl px-3 py-2 text-sm text-center text-gray-900 focus:ring-2 focus:ring-red-500 outline-none transition-all"
                          />
                          <button 
                            onClick={() => handleRemoveCartItem(item.cartId)}
                            className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors shrink-0"
                          >
                            <Trash2 className="w-5 h-5" />
                          </button>
                        </div>
                      </div>
                    </div>
                  ))
                )}
              </div>
            </div>

            <Card className="border border-gray-100 shadow-sm">
              <h2 className="text-lg font-semibold text-gray-900 mb-6">Adicionar à Lista Manualmente</h2>
              
              <div className="flex p-1 bg-gray-100 rounded-xl mb-6 w-full sm:w-fit">
                <button
                  type="button"
                  onClick={() => setReqType('restock')}
                  className={`px-6 py-2.5 text-sm font-medium rounded-lg transition-all ${reqType === 'restock' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'}`}
                >
                  Repor Estoque
                </button>
                <button
                  type="button"
                  onClick={() => setReqType('new')}
                  className={`px-6 py-2.5 text-sm font-medium rounded-lg transition-all ${reqType === 'new' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'}`}
                >
                  Título Inédito
                </button>
              </div>

              <form onSubmit={handleAddToCart} className="space-y-5">
                {reqType === 'restock' ? (
                  <div>
                    <label className="block text-sm font-medium text-gray-900 mb-2">Selecionar Obra</label>
                    <div className="relative">
                      <select 
                        required value={bookId} onChange={(e) => setBookId(e.target.value)}
                        className="w-full appearance-none px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 focus:ring-2 focus:ring-red-500 outline-none text-sm"
                      >
                        <option value="" disabled>Escolha um título cadastrado...</option>
                        {books.map(b => (
                          <option key={b.id} value={b.id}>{b.title} (Estoque: {b.quantity})</option>
                        ))}
                      </select>
                      <ChevronDownIcon className="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                    </div>
                  </div>
                ) : (
                  <div>
                    <label className="block text-sm font-medium text-gray-900 mb-2">Nome da Obra / Assunto</label>
                    <input 
                      required type="text" value={newTitle} onChange={(e) => setNewTitle(e.target.value)}
                      className="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none text-sm"
                      placeholder="Ex: Introdução ao Desenho 3D"
                    />
                  </div>
                )}

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label className="block text-sm font-medium text-gray-900 mb-2">Quantidade de Compra</label>
                    <input 
                      required type="number" min="1" value={quantity} onChange={(e) => setQuantity(e.target.value)}
                      className="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none text-sm"
                      placeholder="Ex: 50"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-900 mb-2">Fornecedor / Editora</label>
                    <input 
                      required type="text" value={justification} onChange={(e) => setJustification(e.target.value)}
                      className="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none text-sm"
                      placeholder="Ex: Editora Érica"
                    />
                  </div>
                </div>

                <button type="submit" className="w-full bg-white border border-gray-200 hover:bg-gray-50 text-gray-900 font-medium py-3.5 px-4 rounded-xl transition-all flex items-center justify-center mt-2">
                  <PlusCircle className="w-4 h-4 mr-2" />
                  Adicionar à Lista
                </button>
              </form>
            </Card>
          </div>

          <div className="lg:col-span-4">
            <div className="sticky top-24 bg-white border border-gray-100 shadow-sm rounded-3xl p-6 sm:p-8">
              <h2 className="text-lg font-semibold text-gray-900 mb-6 border-b border-gray-100 pb-4">Resumo da Compra</h2>
              
              <div className="space-y-4 mb-8">
                <div className="flex justify-between items-center">
                  <span className="text-gray-500">Títulos diferentes</span>
                  <span className="font-semibold text-gray-900">{purchaseCart.length}</span>
                </div>
                <div className="flex justify-between items-center">
                  <span className="text-gray-500">Total de exemplares</span>
                  <span className="font-semibold text-gray-900 text-xl">{totalCartItems}</span>
                </div>
              </div>

              <button 
                onClick={handleSendRequests}
                disabled={purchaseCart.length === 0}
                className={`w-full font-medium py-4 px-4 rounded-xl transition-colors flex items-center justify-center
                  ${purchaseCart.length === 0 
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                    : 'bg-gray-900 hover:bg-gray-800 text-white shadow-md hover:shadow-lg'}`}
              >
                <FileSpreadsheet className="w-4 h-4 mr-2" />
                Gerar Planilha de Pedido
              </button>
              <p className="text-xs text-center text-gray-400 mt-4 leading-relaxed">
                Ao registrar, o sistema gerará uma planilha de ordem de compra que ficará armazenada no seu Histórico.
              </p>
            </div>
          </div>

        </div>
      </div>
    );
  };

  // --- Nova Aba: Histórico de Compras (Pastas e Planilhas) ---
  const HistoryView = () => {
    const [expandedFolders, setExpandedFolders] = useState({});
    const [selectedOrder, setSelectedOrder] = useState(null);

    // Agrupamento Inteligente por Mês/Ano
    const groupedOrders = useMemo(() => {
      const groups = {};
      
      // Garante que estão ordenados do mais novo pro mais velho
      const sortedOrders = [...purchaseOrders].sort((a, b) => {
        const [dayA, monthA, yearA] = a.date.split('/');
        const [dayB, monthB, yearB] = b.date.split('/');
        return new Date(yearB, monthB - 1, dayB) - new Date(yearA, monthA - 1, dayA);
      });

      sortedOrders.forEach(order => {
        const [day, month, year] = order.date.split('/');
        const dateObj = new Date(year, month - 1, day);
        const monthName = dateObj.toLocaleString('pt-BR', { month: 'long', year: 'numeric' });
        // Ex: "Maio de 2026"
        const groupKey = monthName.charAt(0).toUpperCase() + monthName.slice(1);
        
        if (!groups[groupKey]) {
          groups[groupKey] = [];
        }
        groups[groupKey].push(order);
      });

      return groups;
    }, [purchaseOrders]);

    // Efeito para abrir apenas a pasta mais recente por padrão
    React.useEffect(() => {
      const keys = Object.keys(groupedOrders);
      if (keys.length > 0 && Object.keys(expandedFolders).length === 0) {
        setExpandedFolders({ [keys[0]]: true });
      }
    }, [groupedOrders]);

    const toggleFolder = (key) => {
      setExpandedFolders(prev => ({ ...prev, [key]: !prev[key] }));
    };

    const handlePrintOrder = (orderId) => {
      showNotification(`Preparando Planilha ${orderId} para impressão...`, 'success');
      setTimeout(() => window.print(), 800);
    };

    // --- VISUALIZAÇÃO DETALHADA DA PLANILHA ---
    if (selectedOrder) {
      const totalExemplares = selectedOrder.items.reduce((sum, item) => sum + item.requestedQty, 0);
      const isDelivered = selectedOrder.status === 'entregue';

      return (
        <div className="animate-in slide-in-from-right-8 duration-300 max-w-5xl">
          <button 
            onClick={() => setSelectedOrder(null)}
            className="flex items-center text-sm font-medium text-gray-500 mb-8 px-2 hover:text-gray-900 transition-colors"
          >
            <ChevronLeft className="w-5 h-5 mr-1" />
            Voltar ao Histórico
          </button>

          <div className="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4 px-2">
            <div>
              <h1 className="text-3xl font-semibold tracking-tight text-gray-900 flex items-center gap-3">
                Ordem {selectedOrder.orderId}
                <span className={`text-xs uppercase font-bold tracking-wider px-2.5 py-1 rounded-md shadow-sm border ${
                  isDelivered ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100'
                }`}>
                  {isDelivered ? 'Entregue' : 'Aguardando'}
                </span>
              </h1>
              <p className="text-gray-500 mt-2 text-base font-medium">
                Registrado em {selectedOrder.date} às {selectedOrder.time} • {totalExemplares} unidades totais
              </p>
            </div>
            
            <button 
              onClick={() => handlePrintOrder(selectedOrder.orderId)}
              className="flex items-center px-6 py-3.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-sm font-medium transition-colors shadow-sm shrink-0"
            >
              <Printer className="w-5 h-5 mr-2" />
              Imprimir Planilha
            </button>
          </div>

          <div className="bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm whitespace-nowrap">
                <thead>
                  <tr className="border-b border-gray-100 text-gray-500 bg-gray-50/80">
                    <th className="px-6 py-4 font-semibold border-r border-gray-100 w-16 text-center">Nº</th>
                    <th className="px-6 py-4 font-semibold border-r border-gray-100 min-w-[250px]">Material Solicitado</th>
                    <th className="px-6 py-4 font-semibold border-r border-gray-100 min-w-[200px]">Justificativa e Fornecedor</th>
                    <th className="px-6 py-4 font-semibold text-right w-32">Qtd.</th>
                  </tr>
                </thead>
                <tbody className="bg-white">
                  {selectedOrder.items.map((item, idx) => (
                    <tr key={idx} className="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                      <td className="px-6 py-4 border-r border-gray-50 text-center text-gray-400 font-mono text-xs">{(idx + 1).toString().padStart(2, '0')}</td>
                      <td className="px-6 py-4 border-r border-gray-50 font-medium text-gray-900 truncate">
                        <div className="flex items-center gap-2">
                          {item.type === 'restock' ? (
                            <span className="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded shrink-0">REPOSIÇÃO</span>
                          ) : (
                            <span className="text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded shrink-0">NOVO</span>
                          )}
                          <span className="text-base">{item.title}</span>
                        </div>
                      </td>
                      <td className="px-6 py-4 border-r border-gray-50 text-gray-600 truncate max-w-[300px]" title={item.justification}>
                        {item.justification}
                      </td>
                      <td className="px-6 py-4 text-right font-semibold text-gray-900 bg-gray-50/30 text-base">
                        {item.requestedQty} un
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      );
    }

    // --- VISUALIZAÇÃO PRINCIPAL (PASTAS) ---
    return (
      <div className="animate-in fade-in duration-500 max-w-5xl">
        <PageHeader title="Histórico de Compras" subtitle="Acompanhe as ordens de compra organizadas por período." />

        <div className="space-y-6">
          {Object.keys(groupedOrders).length === 0 ? (
            <div className="bg-white border border-gray-100 shadow-sm rounded-3xl p-10 text-center text-gray-500">
              Nenhuma planilha de compra registrada no sistema.
            </div>
          ) : (
            Object.entries(groupedOrders).map(([monthYear, orders]) => {
              const isOpen = expandedFolders[monthYear];
              const totalOrdersInFolder = orders.length;

              return (
                <div key={monthYear} className="bg-transparent">
                  {/* Cabeçalho da Pasta (Folder Header) */}
                  <button 
                    onClick={() => toggleFolder(monthYear)}
                    className="w-full flex items-center justify-between p-5 bg-white border border-gray-200 shadow-sm rounded-2xl hover:bg-gray-50 transition-colors group"
                  >
                    <div className="flex items-center gap-4">
                      <div className={`w-12 h-12 rounded-xl flex items-center justify-center transition-colors ${isOpen ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500 group-hover:text-gray-700'}`}>
                        {isOpen ? <FolderOpen className="w-6 h-6" /> : <Folder className="w-6 h-6" />}
                      </div>
                      <div className="text-left">
                        <h3 className="font-semibold text-gray-900 text-lg tracking-tight">{monthYear}</h3>
                        <p className="text-sm text-gray-500 font-medium">
                          {totalOrdersInFolder} {totalOrdersInFolder === 1 ? 'pedido registrado' : 'pedidos registrados'}
                        </p>
                      </div>
                    </div>
                    <div className="w-10 h-10 rounded-full flex items-center justify-center bg-gray-50 group-hover:bg-gray-100 transition-colors">
                      <ChevronDown className={`w-5 h-5 text-gray-500 transition-transform duration-300 ${isOpen ? 'rotate-180' : ''}`} />
                    </div>
                  </button>

                  {/* Conteúdo da Pasta (Cards de Pedidos Resumidos) */}
                  <div className={`grid transition-all duration-500 ease-in-out ${isOpen ? 'grid-rows-[1fr] opacity-100 mt-4' : 'grid-rows-[0fr] opacity-0'}`}>
                    <div className="overflow-hidden">
                      <div className="flex flex-col gap-4 pl-2 sm:pl-8 border-l-2 border-gray-200/60 ml-6 pb-2">
                        
                        {orders.map((order) => {
                          const totalExemplares = order.items.reduce((sum, item) => sum + item.requestedQty, 0);
                          const isDelivered = order.status === 'entregue';

                          return (
                            <div key={order.orderId} className="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                              <div className="px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div className="flex items-center gap-4">
                                  <div className="w-10 h-10 bg-gray-50 rounded-lg border border-gray-100 flex items-center justify-center shrink-0">
                                    <FileSpreadsheet className="w-5 h-5 text-gray-400" />
                                  </div>
                                  <div>
                                    <h4 className="font-bold text-gray-900 tracking-tight flex items-center gap-2">
                                      {order.orderId}
                                      <span className={`text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md border ${
                                        isDelivered ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100'
                                      }`}>
                                        {isDelivered ? 'Entregue' : 'Aguardando'}
                                      </span>
                                    </h4>
                                    <p className="text-xs text-gray-500 font-medium mt-0.5">Em {order.date} às {order.time} • {totalExemplares} unidades totais</p>
                                  </div>
                                </div>
                                
                                <div className="flex items-center gap-2 self-start sm:self-auto w-full sm:w-auto mt-2 sm:mt-0">
                                  <button 
                                    onClick={() => handlePrintOrder(order.orderId)}
                                    className="flex-1 sm:flex-none items-center justify-center text-sm font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-200 px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition-all flex"
                                  >
                                    <Printer className="w-4 h-4 sm:mr-0 md:mr-2" /> 
                                    <span className="inline sm:hidden md:inline">Imprimir</span>
                                  </button>
                                  <button 
                                    onClick={() => setSelectedOrder(order)}
                                    className="flex-1 sm:flex-none items-center justify-center text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 px-4 py-2.5 rounded-xl shadow-sm transition-all flex"
                                  >
                                    <Info className="w-4 h-4 mr-2" /> 
                                    Detalhes
                                  </button>
                                </div>
                              </div>
                            </div>
                          );
                        })}

                      </div>
                    </div>
                  </div>
                </div>
              );
            })
          )}
        </div>
      </div>
    );
  };

  // --- Nova Aba: Pedidos de Professores ---
  const TeacherRequestsView = () => {
    const handleApprove = (reqId) => {
      const request = teacherRequests.find(r => r.id === reqId);
      const bookIndex = books.findIndex(b => b.id === request.bookId);
      
      if (bookIndex === -1) return;
      
      const book = books[bookIndex];

      if (book.quantity < request.qty) {
        showNotification(`Estoque insuficiente de "${book.title}". O pedido não pode ser separado no momento.`, 'error');
        return;
      }

      // Libera (retira o estoque)
      const newBooks = [...books];
      newBooks[bookIndex] = { ...book, quantity: book.quantity - request.qty };
      setBooks(newBooks);

      // Muda status
      setTeacherRequests(teacherRequests.map(r => r.id === reqId ? { ...r, status: 'atendido' } : r));
      showNotification(`Material separado! ${request.qty} exemplares subtraídos do estoque.`);
    };

    // Nova Lógica: Enviar Faltante Direto pro Carrinho de Compras
    const handleBuyDirectly = (req, book) => {
      const neededQty = req.qty - (book ? book.quantity : 0);
      const qtyToBuy = neededQty > 0 ? neededQty : req.qty;
      const titleToAdd = book ? book.title : req.title;
      const bookId = book ? book.id : null;

      const existing = purchaseCart.find(item => item.bookId === bookId);
      
      if (!existing) {
        setPurchaseCart([...purchaseCart, {
          cartId: Date.now().toString(),
          type: bookId ? 'restock' : 'new',
          bookId: bookId,
          title: titleToAdd,
          quantity: qtyToBuy,
          justification: `Reposição p/ atender pedido de ${req.teacher} (${req.subject})`
        }]);
      } else {
        setPurchaseCart(purchaseCart.map(item => 
          item.bookId === bookId 
            ? { ...item, quantity: parseInt(item.quantity) + qtyToBuy }
            : item
        ));
      }
      
      showNotification(`"${titleToAdd}" adicionado à lista de compras para atender o pedido.`);
      setActiveView('purchases');
    };

    const pendingRequests = teacherRequests.filter(r => r.status === 'pendente');
    const pastRequests = teacherRequests.filter(r => r.status !== 'pendente');

    return (
      <div className="animate-in fade-in duration-500">
        <PageHeader title="Pedidos de Professores" subtitle="Gerencie as solicitações de material didático feitas pelas turmas e docentes." />

        <h3 className="text-xl font-semibold text-gray-900 tracking-tight mb-6">Aguardando Separação ({pendingRequests.length})</h3>
        
        {/* Nova Grade: Lista Horizontal de Coluna Única */}
        <div className="flex flex-col gap-4 mb-12">
          {pendingRequests.length === 0 ? (
            <div className="w-full bg-white border border-gray-100 shadow-sm rounded-3xl p-10 text-center text-gray-500">
              Nenhum pedido pendente no momento. Bom trabalho!
            </div>
          ) : (
            pendingRequests.map(req => {
              // Gera as iniciais do professor. Ex: Prof. Carlos Mendes -> CM
              const initials = req.teacher.replace(/Prof\. |Profa\. /g, '').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
              const b = books.find(b => b.id === req.bookId);
              const canFulfill = b && b.quantity >= req.qty;

              return (
                <div key={req.id} className="bg-white rounded-[20px] border border-gray-200/80 shadow-[0_2px_12px_rgb(0,0,0,0.02)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition-all duration-300 flex flex-col md:flex-row relative overflow-hidden group">
                  
                  {/* Linha indicadora lateral (Desktop) / topo (Mobile) */}
                  <div className={`absolute top-0 left-0 w-full h-1 md:w-1 md:h-full ${canFulfill ? 'bg-amber-400' : 'bg-red-500'}`}></div>

                  {/* 1. Coluna do Professor / Detalhes (Esquerda) */}
                  <div className="p-5 md:w-72 border-b md:border-b-0 md:border-r border-gray-100 flex flex-col justify-center bg-gray-50/30">
                    <div className="flex items-center gap-3 mb-4">
                       <div className="w-10 h-10 rounded-full bg-gray-900 text-white flex items-center justify-center font-semibold text-sm shadow-sm shrink-0">
                         {initials}
                       </div>
                       <div className="min-w-0 flex-1">
                         <h4 className="font-semibold text-gray-900 leading-tight truncate" title={req.teacher}>{req.teacher}</h4>
                         <p className="text-xs text-gray-500 font-medium mt-0.5 truncate">Turma: {req.subject}</p>
                       </div>
                    </div>
                    <div className="flex items-center justify-between mt-auto pt-2 text-[10px] text-gray-400 font-medium">
                       <span className="bg-gray-100 text-gray-600 px-2 py-1 rounded-md font-bold tracking-wider">#{req.id}</span>
                       <span>{req.date} às {req.time}</span>
                    </div>
                  </div>

                  {/* 2. Coluna do Livro e Estoque (Centro - Flex-1 toma todo espaço restante) */}
                  <div className="p-5 flex-1 flex flex-col sm:flex-row sm:items-center gap-5">
                    <div className="w-12 h-16 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 shrink-0 relative overflow-hidden group-hover:shadow-md transition-shadow hidden sm:flex">
                       <ImageIcon className="w-5 h-5 text-gray-300" strokeWidth={2} />
                    </div>
                    <div className="flex-1 min-w-0 flex flex-col justify-center">
                       <h5 className="font-semibold text-gray-900 text-base leading-snug line-clamp-2 mb-2" title={req.title}>{req.title}</h5>
                       
                       <div className="flex items-center flex-wrap gap-2 text-sm text-gray-600">
                         <span className="bg-gray-100 border border-gray-200 px-2 py-0.5 rounded-lg font-bold text-gray-900 shadow-sm">{req.qty} un</span>
                         <span className="hidden sm:inline">solicitadas</span>
                         <span className="text-gray-300 hidden sm:inline">•</span>
                         
                         {/* Indicador inteligente de estoque inline */}
                         <div className="flex items-center gap-1.5 ml-1 sm:ml-0">
                           {canFulfill ? <CheckCircle2 className="w-4 h-4 text-emerald-600" /> : <AlertCircle className="w-4 h-4 text-red-600" />}
                           <span className={`text-xs font-semibold ${canFulfill ? 'text-emerald-700' : 'text-red-700'}`}>
                             Em estoque: {b ? b.quantity : 0}
                           </span>
                           <span className={`ml-1 text-[10px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded-md ${canFulfill ? 'bg-emerald-100/50 text-emerald-700' : 'bg-red-100/50 text-red-700'}`}>
                             {canFulfill ? 'Atende' : 'Falta'}
                           </span>
                         </div>
                       </div>
                    </div>
                  </div>

                  {/* 3. Coluna de Ações (Direita) */}
                  <div className="p-5 border-t md:border-t-0 md:border-l border-gray-100 flex flex-row md:flex-col gap-3 md:w-56 bg-gray-50/50 justify-center shrink-0">
                    {canFulfill ? (
                      <button 
                        onClick={() => handleApprove(req.id)}
                        className="flex-1 md:flex-none py-3 px-4 rounded-xl transition-all text-sm font-medium flex items-center justify-center shadow-sm bg-gray-900 hover:bg-gray-800 text-white hover:shadow-md"
                      >
                        <PackageCheck className="w-4 h-4 mr-2 shrink-0" /> 
                        Separar Estoque
                      </button>
                    ) : (
                      <button 
                        onClick={() => handleBuyDirectly(req, b)}
                        className="flex-1 md:flex-none py-3 px-4 rounded-xl transition-all text-sm font-medium flex items-center justify-center shadow-sm bg-amber-500 hover:bg-amber-600 text-white hover:shadow-md border border-amber-600"
                      >
                        <ShoppingCart className="w-4 h-4 mr-2 shrink-0" /> 
                        Comprar Faltante
                      </button>
                    )}
                    <a 
                      href={`mailto:${req.email}?subject=Sobre o pedido de material didático: ${req.title}`}
                      className="flex-none py-3 px-4 rounded-xl border border-gray-200 bg-white text-gray-600 hover:text-gray-900 hover:border-gray-300 hover:shadow-sm transition-all text-sm font-medium flex items-center justify-center"
                    >
                      <Mail className="w-4 h-4 md:mr-2 shrink-0" /> 
                      <span className="hidden md:inline">Enviar E-mail</span>
                    </a>
                  </div>
                </div>
              );
            })
          )}
        </div>

        <h3 className="text-xl font-semibold text-gray-900 tracking-tight mb-6">Histórico de Pedidos Processados</h3>
        <div className="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
            {pastRequests.length === 0 ? (
              <div className="text-center py-10 text-gray-500">
                Nenhum pedido no histórico.
              </div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-left border-collapse text-sm whitespace-nowrap">
                  <thead>
                    <tr className="bg-gray-50/80 border-b border-gray-100 text-gray-500">
                      <th className="px-6 py-4 font-medium border-r border-gray-100">Professor / Solicitante</th>
                      <th className="px-6 py-4 font-medium border-r border-gray-100">Título Solicitado</th>
                      <th className="px-6 py-4 font-medium border-r border-gray-100 text-center w-24">Qtd.</th>
                      <th className="px-6 py-4 font-medium border-r border-gray-100 text-center w-36">Status</th>
                      <th className="px-6 py-4 font-medium w-36">Data</th>
                    </tr>
                  </thead>
                  <tbody>
                    {pastRequests.map(req => (
                      <tr key={req.id} className="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td className="px-6 py-4 border-r border-gray-50 font-medium text-gray-900">
                          {req.teacher} <span className="text-xs text-gray-400 font-normal ml-1">({req.subject})</span>
                        </td>
                        <td className="px-6 py-4 border-r border-gray-50 text-gray-700">{req.title}</td>
                        <td className="px-6 py-4 border-r border-gray-50 text-center font-semibold text-gray-700">{req.qty}</td>
                        <td className="px-6 py-4 border-r border-gray-50 text-center">
                           <span className={`text-[10px] uppercase font-bold px-2 py-1 rounded border ${req.status === 'atendido' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-gray-50 text-gray-600 border-gray-200'}`}>
                            Atendido
                          </span>
                        </td>
                        <td className="px-6 py-4 text-gray-500 font-mono text-xs">{req.date} {req.time}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
      </div>
    );
  };

  const Library = () => {
    const [searchTerm, setSearchTerm] = useState('');
    
    const collections = useMemo(() => {
      const groups = {};
      books.forEach(book => {
        if (!groups[book.subject]) groups[book.subject] = [];
        groups[book.subject].push(book);
      });
      return groups;
    }, [books]);

    const filteredBooks = books.filter(b => 
      b.title.toLowerCase().includes(searchTerm.toLowerCase()) || 
      b.subject.toLowerCase().includes(searchTerm.toLowerCase())
    );

    const isSearching = searchTerm.trim().length > 0;

    const BookCard = ({ book }) => (
      <div 
        onClick={() => setSelectedBook(book)}
        className="flex-none w-40 sm:w-48 group cursor-pointer"
      >
        <div className="aspect-[3/4] bg-gray-50 hover:bg-gray-100 transition-colors duration-300 rounded-2xl mb-4 overflow-hidden shadow-sm border border-gray-100 relative flex items-center justify-center">
          <ImageIcon className="w-10 h-10 text-gray-300 group-hover:scale-110 group-hover:text-gray-400 transition-all duration-500 ease-out" strokeWidth={1.5} />
          {book.quantity < 8 && (
            <div className="absolute top-3 right-3 w-3 h-3 bg-red-500 rounded-full border-2 border-white shadow-sm" />
          )}
        </div>
        <h4 className="font-medium text-gray-900 text-sm leading-tight line-clamp-2 group-hover:text-red-600 transition-colors">{book.title}</h4>
        <p className="text-gray-500 text-xs mt-1">{book.quantity} un. disponíveis</p>
      </div>
    );

    // Integração da View Individual com a Lista de Retirada
    const handleAddToWithdrawFromLibrary = () => {
      const existing = withdrawCart.find(item => item.bookId == selectedBook.id);
      const currentCartQty = existing ? parseInt(existing.quantity) : 0;

      // Validação rápida: impede de colocar no carrinho mais do que o estoque tem
      if (currentCartQty >= selectedBook.quantity) {
        showNotification(`Você já adicionou o limite disponível de "${selectedBook.title}" à lista de retirada.`, 'error');
        return;
      }

      if (!existing) {
        setWithdrawCart([...withdrawCart, {
          id: Math.random().toString(36),
          bookId: selectedBook.id.toString(),
          quantity: 1
        }]);
        showNotification(`"${selectedBook.title}" adicionado à lista de retirada.`);
      } else {
        setWithdrawCart(withdrawCart.map(item => 
          item.bookId == selectedBook.id 
            ? { ...item, quantity: parseInt(item.quantity) + 1 }
            : item
        ));
        showNotification(`Quantidade de "${selectedBook.title}" atualizada na lista de retirada.`);
      }
    };

    // Integração da View Individual com o Carrinho de Compras do Almoxarife
    const handleAddToCartFromLibrary = () => {
      const existing = purchaseCart.find(item => item.bookId === selectedBook.id);
      
      if (!existing) {
        setPurchaseCart([...purchaseCart, {
          cartId: Date.now().toString(),
          type: 'restock',
          bookId: selectedBook.id,
          title: selectedBook.title,
          quantity: 1,
          justification: ''
        }]);
        showNotification(`"${selectedBook.title}" adicionado à lista de compras.`);
      } else {
        setPurchaseCart(purchaseCart.map(item => 
          item.bookId === selectedBook.id 
            ? { ...item, quantity: parseInt(item.quantity) + 1 }
            : item
        ));
        showNotification(`Quantidade de "${selectedBook.title}" atualizada na lista de compras.`);
      }
    };

    if (selectedBook) {
      const similarBooks = books.filter(b => b.subject === selectedBook.subject && b.id !== selectedBook.id).slice(0, 8);

      return (
        <div className="animate-in slide-in-from-right-8 duration-300">
          <div className="flex items-center text-sm font-medium text-gray-500 mb-8 px-2">
            <button onClick={() => setSelectedBook(null)} className="hover:text-gray-900 transition-colors flex items-center">
              <ChevronLeft className="w-4 h-4 mr-1" /> Acervo
            </button>
            <span className="mx-2 text-gray-300">/</span>
            <span className="text-gray-400">{selectedBook.subject}</span>
            <span className="mx-2 text-gray-300">/</span>
            <span className="text-gray-900 truncate max-w-[200px] sm:max-w-xs">{selectedBook.title}</span>
          </div>

          <div className="bg-white rounded-3xl p-6 sm:p-10 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 mb-16">
            
            {/* Top Section: Image + Title + Action Box */}
            <div className="flex flex-col md:flex-row gap-8 lg:gap-12 mb-12">
              
              {/* Esquerda: Capa do Livro */}
              <div className="w-full md:w-[260px] lg:w-[300px] shrink-0 mx-auto md:mx-0">
                 <div className="aspect-[3/4] bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl overflow-hidden shadow-lg border border-gray-200 flex items-center justify-center relative group">
                   <ImageIcon className="w-24 h-24 text-gray-300 group-hover:scale-110 transition-transform duration-700 ease-out" strokeWidth={1} />
                   <div className="absolute inset-0 bg-gradient-to-tr from-black/5 to-transparent"></div>
                 </div>
              </div>
              
              {/* Direita: Título e Ações Diretas */}
              <div className="flex-1 flex flex-col">
                
                <div className="mb-6">
                  <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold tracking-wider bg-red-50 text-red-600 uppercase mb-4">
                    {selectedBook.subject}
                  </span>
                  <h1 className="text-3xl sm:text-4xl lg:text-5xl font-semibold text-gray-900 tracking-tight leading-tight mb-4">
                    {selectedBook.title}
                  </h1>
                  <div className="flex items-center text-gray-500 text-sm md:text-base font-medium mb-6">
                    <User className="w-4 h-4 md:w-5 md:h-5 mr-2" />
                    Por <span className="text-gray-900 ml-1">{selectedBook.author || 'Autor não informado'}</span>
                  </div>
                </div>

                {/* Badge de Status de Estoque */}
                <div className={`flex items-center gap-2 w-fit px-4 py-2.5 rounded-xl border mb-8 ${
                  selectedBook.quantity === 0 
                    ? 'bg-red-50 border-red-100 text-red-600'
                    : selectedBook.quantity < 8 
                      ? 'bg-amber-50 border-amber-100 text-amber-600' 
                      : 'bg-emerald-50 border-emerald-100 text-emerald-600'
                }`}>
                  {selectedBook.quantity < 8 ? <AlertCircle className="w-5 h-5 shrink-0" /> : <CheckCircle2 className="w-5 h-5 shrink-0" />}
                  <span className="text-sm font-semibold tracking-wide uppercase">
                    {selectedBook.quantity === 0 
                      ? 'Estoque Esgotado' 
                      : selectedBook.quantity < 8 
                        ? `Estoque Crítico: Restam ${selectedBook.quantity} unidades` 
                        : `${selectedBook.quantity} unidades disponíveis`}
                  </span>
                </div>

                {/* Botões de Ação */}
                <div className="flex flex-col sm:flex-row gap-4 max-w-lg mt-auto">
                  <button 
                    onClick={handleAddToWithdrawFromLibrary}
                    disabled={selectedBook.quantity === 0}
                    className={`flex-1 py-4 px-6 rounded-2xl font-medium transition-colors flex items-center justify-center
                      ${selectedBook.quantity === 0 
                        ? 'bg-gray-200 text-gray-400 cursor-not-allowed' 
                        : 'bg-red-600 hover:bg-red-700 text-white'}`}
                  >
                    <ArrowUpFromLine className="w-5 h-5 mr-2 shrink-0" />
                    Retirar Material
                  </button>
                  <button 
                    onClick={handleAddToCartFromLibrary}
                    className="flex-1 bg-gray-900 hover:bg-gray-800 text-white py-4 px-6 rounded-2xl font-medium transition-colors flex items-center justify-center"
                  >
                    <ShoppingCart className="w-5 h-5 mr-2 shrink-0" />
                    Adicionar para Compra
                  </button>
                </div>

              </div>
            </div>

            {/* Bottom Section: Detalhes estendidos (Largura Total) */}
            <div className="w-full pt-6 border-t border-gray-100">
              <h3 className="text-xl font-semibold text-gray-900 mb-4">Sobre a Obra</h3>
              <p className="text-gray-600 text-lg leading-relaxed mb-10 max-w-4xl">
                {selectedBook.desc}
              </p>

              <h3 className="text-lg font-semibold text-gray-900 mb-6">Detalhes Técnicos</h3>
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-6">
                <div>
                   <span className="text-gray-400 text-xs font-semibold uppercase tracking-wider block mb-2">Editora</span>
                   <span className="text-gray-900 font-medium text-sm flex items-center">
                      <Building className="w-4 h-4 mr-2 text-gray-400 shrink-0"/>
                      <span className="truncate" title={selectedBook.publisher}>{selectedBook.publisher || 'N/A'}</span>
                   </span>
                </div>
                <div>
                   <span className="text-gray-400 text-xs font-semibold uppercase tracking-wider block mb-2">Páginas</span>
                   <span className="text-gray-900 font-medium text-sm flex items-center">
                      <FileText className="w-4 h-4 mr-2 text-gray-400 shrink-0"/>
                      {selectedBook.pages || '--'}
                   </span>
                </div>
                <div>
                   <span className="text-gray-400 text-xs font-semibold uppercase tracking-wider block mb-2">Publicação</span>
                   <span className="text-gray-900 font-medium text-sm flex items-center">
                      <Calendar className="w-4 h-4 mr-2 text-gray-400 shrink-0"/>
                      {selectedBook.year || 'N/A'}
                   </span>
                </div>
                <div>
                   <span className="text-gray-400 text-xs font-semibold uppercase tracking-wider block mb-2">Cód. ISBN</span>
                   <span className="text-gray-900 font-medium text-sm font-mono tracking-tight">
                      {selectedBook.isbn}
                   </span>
                </div>
              </div>
            </div>
          </div>

          {/* Coleção de Semelhantes */}
          {similarBooks.length > 0 && (
            <div className="pt-4 border-t border-gray-200">
              <h3 className="text-2xl font-semibold text-gray-900 tracking-tight mb-8">
                Outros títulos em {selectedBook.subject}
              </h3>
              <div className="flex overflow-x-auto gap-6 pb-6 px-2 snap-x [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                {similarBooks.map(book => (
                  <div key={book.id} className="snap-start">
                    <BookCard book={book} />
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      );
    }

    return (
      <div className="animate-in fade-in duration-500">
        <PageHeader title="Acervo" subtitle="Explore os materiais disponíveis." />

        <div className="relative mb-10">
          <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          <input 
            type="text" 
            placeholder="Buscar por título ou área..." 
            className="w-full bg-white border-0 shadow-sm rounded-2xl pl-12 pr-4 py-4 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none transition-all"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>

        {isSearching ? (
          <div>
            <h3 className="text-lg font-medium text-gray-900 mb-6 px-2">Resultados para "{searchTerm}"</h3>
            {filteredBooks.length === 0 ? (
              <div className="text-center py-20 text-gray-500 bg-white rounded-3xl border border-gray-100">
                Nenhum livro encontrado.
              </div>
            ) : (
              <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                {filteredBooks.map(book => <BookCard key={book.id} book={book} />)}
              </div>
            )}
          </div>
        ) : (
          <div className="space-y-12">
            {Object.entries(collections).map(([subject, subjectBooks]) => (
              <section key={subject}>
                <div className="flex items-center justify-between px-2 mb-5">
                  <h3 className="text-xl font-semibold text-gray-900 tracking-tight">{subject}</h3>
                  <span className="text-sm font-medium text-gray-400">{subjectBooks.length} itens</span>
                </div>
                <div className="flex overflow-x-auto gap-6 pb-6 px-2 snap-x [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                  {subjectBooks.map(book => (
                    <div key={book.id} className="snap-start">
                      <BookCard book={book} />
                    </div>
                  ))}
                </div>
              </section>
            ))}
          </div>
        )}
      </div>
    );
  };

  const ReceiveStock = () => {
    const [receiveMode, setReceiveMode] = useState('existing'); 
    
    const [selectedBookId, setSelectedBookId] = useState('');
    const [quantity, setQuantity] = useState('');

    const [newTitle, setNewTitle] = useState('');
    const [newIsbn, setNewIsbn] = useState('');
    const [newSubject, setNewSubject] = useState('');
    const [newDesc, setNewDesc] = useState('');

    const handleSubmitExisting = (e) => {
      e.preventDefault();
      if (!selectedBookId || quantity <= 0) return;

      const qty = parseInt(quantity);
      setBooks(books.map(b => b.id === parseInt(selectedBookId) ? { ...b, quantity: b.quantity + qty } : b));
      
      showNotification(`${qty} exemplares adicionados ao acervo.`);
      setSelectedBookId('');
      setQuantity('');
    };

    const handleSubmitNew = (e) => {
      e.preventDefault();
      if (!newTitle || quantity <= 0) return;

      const qty = parseInt(quantity);
      const newBook = {
        id: Date.now(),
        title: newTitle,
        isbn: newIsbn || 'Sem ISBN',
        subject: newSubject,
        desc: newDesc || 'Livro não possui descrição detalhada.',
        quantity: qty,
        author: 'Autor não informado',
        publisher: 'Editora não informada',
        year: new Date().getFullYear().toString(),
        pages: '--'
      };

      setBooks([...books, newBook]);
      showNotification(`"${newTitle}" foi cadastrado no sistema com ${qty} exemplares.`);
      
      setNewTitle(''); setNewIsbn(''); setNewSubject(''); setNewDesc(''); setQuantity('');
      setReceiveMode('existing'); 
    };

    return (
      <div className="animate-in fade-in duration-500 max-w-2xl mx-auto">
        <PageHeader title="Recebimento de Remessa" subtitle="Confirme a chegada de materiais e atualize o acervo." />
        
        <Card>
          <div className="flex p-1 bg-gray-100 rounded-xl mb-8">
            <button
              type="button"
              onClick={() => setReceiveMode('existing')}
              className={`flex-1 py-3 text-sm font-medium rounded-lg transition-all ${receiveMode === 'existing' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'}`}
            >
              Material Existente
            </button>
            <button
              type="button"
              onClick={() => setReceiveMode('new')}
              className={`flex-1 py-3 text-sm font-medium rounded-lg transition-all ${receiveMode === 'new' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'}`}
            >
              Cadastrar Novo Material
            </button>
          </div>

          {receiveMode === 'existing' ? (
            <form onSubmit={handleSubmitExisting} className="space-y-6 animate-in fade-in">
              <div>
                <label className="block text-sm font-medium text-gray-900 mb-2">Livro Recebido</label>
                <div className="relative">
                  <select 
                    required
                    value={selectedBookId} 
                    onChange={(e) => setSelectedBookId(e.target.value)}
                    className="w-full appearance-none px-4 py-4 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 focus:ring-2 focus:ring-red-500 outline-none transition-all"
                  >
                    <option value="" disabled>Selecione um título do acervo...</option>
                    {books.map(b => (
                      <option key={b.id} value={b.id}>{b.title} (Atual: {b.quantity})</option>
                    ))}
                  </select>
                  <ChevronDownIcon className="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" />
                </div>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-900 mb-2">Quantidade Recebida (Caixas/Unidades)</label>
                <input 
                  required type="number" min="1" value={quantity} onChange={(e) => setQuantity(e.target.value)}
                  className="w-full px-4 py-4 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none transition-all"
                  placeholder="Ex: 50"
                />
              </div>

              <div className="pt-4">
                <button type="submit" className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-4 px-4 rounded-xl transition-colors flex items-center justify-center">
                  <PackageCheck className="w-5 h-5 mr-2" />
                  Confirmar Recebimento de Estoque
                </button>
              </div>
            </form>
          ) : (
            <form onSubmit={handleSubmitNew} className="space-y-6 animate-in fade-in">
              <div>
                <label className="block text-sm font-medium text-gray-900 mb-2">Título do Novo Livro</label>
                <input 
                  required type="text" value={newTitle} onChange={(e) => setNewTitle(e.target.value)}
                  className="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none"
                  placeholder="Ex: Introdução à Robótica"
                />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-900 mb-2">Área / Matéria</label>
                  <input 
                    required type="text" value={newSubject} onChange={(e) => setNewSubject(e.target.value)}
                    className="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none"
                    placeholder="Ex: Mecatrônica"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-900 mb-2">ISBN (Opcional)</label>
                  <input 
                    type="text" value={newIsbn} onChange={(e) => setNewIsbn(e.target.value)}
                    className="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none"
                    placeholder="Ex: 978-85-..."
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-900 mb-2">Descrição Curta</label>
                <textarea 
                  rows="2" value={newDesc} onChange={(e) => setNewDesc(e.target.value)}
                  className="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none resize-none"
                  placeholder="Breve resumo sobre o conteúdo do material..."
                />
              </div>

              <hr className="border-gray-100 my-4" />

              <div>
                <label className="block text-sm font-medium text-gray-900 mb-2">Quantidade Recebida na Remessa</label>
                <input 
                  required type="number" min="1" value={quantity} onChange={(e) => setQuantity(e.target.value)}
                  className="w-full px-4 py-4 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none"
                  placeholder="Ex: 100"
                />
              </div>

              <div className="pt-4">
                <button type="submit" className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-4 px-4 rounded-xl transition-colors flex items-center justify-center">
                  <PackageCheck className="w-5 h-5 mr-2" />
                  Cadastrar e Receber Estoque
                </button>
              </div>
            </form>
          )}
        </Card>
      </div>
    );
  };

  const WithdrawStock = () => {
    const [destination, setDestination] = useState('');

    const handleAddRow = () => {
      setWithdrawCart([...withdrawCart, { id: Math.random().toString(36), bookId: '', quantity: '' }]);
    };

    const handleRemoveRow = (idToRemove) => {
      setWithdrawCart(withdrawCart.filter(item => item.id !== idToRemove));
    };

    const handleItemChange = (id, field, value) => {
      if (field === 'quantity') {
        const item = withdrawCart.find(c => c.id === id);
        const bookId = item.bookId;
        
        if (bookId) {
          const originalQty = books.find(b => b.id === parseInt(bookId))?.quantity || 0;
          const qtyInOtherRows = withdrawCart
            .filter(c => c.id !== id && c.bookId === bookId)
            .reduce((sum, c) => sum + (parseInt(c.quantity) || 0), 0);
          
          const maxAllowed = originalQty - qtyInOtherRows;
          
          let val = parseInt(value);
          let finalValue = value;
          
          if (!isNaN(val)) {
            if (val > maxAllowed) val = maxAllowed;
            if (val < 0) val = 0;
            finalValue = val.toString();
          } else if (value !== '') {
            finalValue = item.quantity;
          }

          setWithdrawCart(withdrawCart.map(c => c.id === id ? { ...c, [field]: finalValue } : c));
          return;
        }
      }
      
      if (field === 'bookId') {
        setWithdrawCart(withdrawCart.map(item => item.id === id ? { ...item, bookId: value, quantity: '' } : item));
        return;
      }

      setWithdrawCart(withdrawCart.map(item => item.id === id ? { ...item, [field]: value } : item));
    };

    const handleSubmit = (e) => {
      e.preventDefault();
      
      const validItems = withdrawCart.filter(item => item.bookId && item.quantity > 0);
      if (validItems.length === 0) {
        showNotification('Preencha ao menos um livro e quantidade para retirar.', 'error');
        return;
      }

      const requestedQuantities = {};
      for (const item of validItems) {
        const qty = parseInt(item.quantity);
        requestedQuantities[item.bookId] = (requestedQuantities[item.bookId] || 0) + qty;
      }

      for (const [bId, reqQty] of Object.entries(requestedQuantities)) {
        const book = books.find(b => b.id === parseInt(bId));
        if (reqQty > book.quantity) {
          showNotification(`Saldo insuficiente para "${book.title}". Restam apenas ${book.quantity} exemplares.`, 'error');
          return;
        }
      }

      setBooks(books.map(b => {
        if (requestedQuantities[b.id]) {
          return { ...b, quantity: b.quantity - requestedQuantities[b.id] };
        }
        return b;
      }));
      
      const totalExemplares = Object.values(requestedQuantities).reduce((a, b) => a + b, 0);
      showNotification(`Retirada concluída! ${totalExemplares} exemplares destinados à ${destination}.`);
      
      setDestination('');
      setWithdrawCart([]);
    };

    return (
      <div className="animate-in fade-in duration-500 max-w-3xl">
        <PageHeader title="Retirada em Lote" subtitle="Registre a entrega de um ou mais livros de uma vez." />
        
        <Card>
          <form onSubmit={handleSubmit} className="space-y-8">
            <div>
              <label className="block text-sm font-medium text-gray-900 mb-2">Turma / Destino Final</label>
              <input 
                required
                type="text" 
                value={destination}
                onChange={(e) => setDestination(e.target.value)}
                className="w-full px-4 py-4 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:bg-white outline-none transition-all"
                placeholder="Ex: Turma 102 - Manhã (Mecânica)"
              />
            </div>

            <hr className="border-gray-100" />

            <div className="space-y-4">
              <div className="flex items-center justify-between mb-2">
                <label className="block text-sm font-medium text-gray-900">Itens para Retirada</label>
                <span className="text-xs text-gray-400 font-medium">{withdrawCart.length} linha(s)</span>
              </div>

              {withdrawCart.length === 0 ? (
                <div className="bg-white border border-gray-200 border-dashed rounded-2xl p-10 text-center flex flex-col items-center justify-center mb-4">
                  <div className="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <ArrowUpFromLine className="w-8 h-8 text-gray-300" />
                  </div>
                  <p className="text-gray-500 font-medium">Sua lista de retirada está vazia.</p>
                  <p className="text-gray-400 text-sm mt-1">Adicione itens navegando pelo acervo ou crie uma linha manualmente.</p>
                  <button
                    type="button"
                    onClick={handleAddRow}
                    className="mt-6 inline-flex items-center px-5 py-2.5 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors"
                  >
                    <PlusCircle className="w-4 h-4 mr-2" />
                    Adicionar Manualmente
                  </button>
                </div>
              ) : (
                <>
                  {withdrawCart.map((item, index) => {
                    const book = item.bookId ? books.find(b => b.id === parseInt(item.bookId)) : null;
                    const originalQty = book ? book.quantity : 0;
                    const qtyInOtherRows = withdrawCart.filter(c => c.id !== item.id && c.bookId === item.bookId).reduce((sum, c) => sum + (parseInt(c.quantity) || 0), 0);
                    const maxAllowed = originalQty - qtyInOtherRows;
                    const currentInput = parseInt(item.quantity) || 0;
                    const projectedRemaining = book ? Math.max(0, maxAllowed - currentInput) : '-';

                    return (
                    <div key={item.id} className="flex flex-col sm:flex-row gap-3 items-start sm:items-center animate-in slide-in-from-bottom-2">
                      <div className="relative flex-1 w-full">
                        <select 
                          required
                          value={item.bookId} 
                          onChange={(e) => handleItemChange(item.id, 'bookId', e.target.value)}
                          className="w-full appearance-none px-4 h-[52px] bg-gray-50 border border-gray-100 rounded-xl text-gray-900 focus:ring-2 focus:ring-red-500 focus:bg-white outline-none transition-all text-sm"
                        >
                          <option value="" disabled>Selecione um título...</option>
                          {books.map(b => (
                            <option key={b.id} value={b.id} disabled={b.quantity === 0}>
                              {b.title} {b.quantity === 0 ? '• Faltante' : ''}
                            </option>
                          ))}
                        </select>
                        <ChevronDownIcon className="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                      </div>
                      
                      <div className="flex items-center gap-2 w-full sm:w-auto">
                        <div className="h-[52px] w-full sm:w-20 flex flex-col items-center justify-center bg-gray-100/80 border border-gray-100 rounded-xl text-sm shrink-0">
                          <span className="text-[10px] uppercase font-semibold text-gray-500 leading-none mb-1">Restante</span>
                          <span className={`font-bold leading-none ${projectedRemaining === 0 ? 'text-red-600' : 'text-gray-900'}`}>
                            {projectedRemaining}
                          </span>
                        </div>

                        <input 
                          required
                          type="number" 
                          min="1"
                          max={book ? maxAllowed : undefined}
                          disabled={!item.bookId}
                          value={item.quantity}
                          onChange={(e) => handleItemChange(item.id, 'quantity', e.target.value)}
                          className="h-[52px] w-full sm:w-24 px-4 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:bg-white outline-none transition-all text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                          placeholder="Qtd."
                        />
                        
                        <button 
                          type="button"
                          onClick={() => handleRemoveRow(item.id)}
                          className="h-[52px] w-[52px] flex items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 hover:bg-red-100 hover:border-red-200 transition-all flex-shrink-0"
                          title="Remover linha"
                        >
                          <Trash2 className="w-5 h-5" />
                        </button>
                      </div>
                    </div>
                    );
                  })}

                  <button 
                    type="button" 
                    onClick={handleAddRow}
                    className="inline-flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors"
                  >
                    <PlusCircle className="w-4 h-4 mr-2" />
                    Adicionar outro título
                  </button>
                </>
              )}
            </div>

            <div className="bg-orange-50/50 rounded-xl p-4 flex items-start border border-orange-100/50">
              <Info className="w-5 h-5 text-orange-400 mr-3 shrink-0 mt-0.5" />
              <p className="text-sm text-gray-600 leading-relaxed">
                Verifique se o saldo é suficiente. O sistema bloqueará a digitação automaticamente quando o estoque disponível do livro for alcançado.
              </p>
            </div>

            <div className="pt-4 border-t border-gray-100">
              <button 
                type="submit" 
                disabled={withdrawCart.length === 0}
                className={`w-full font-medium py-4 px-4 rounded-xl transition-colors flex items-center justify-center
                  ${withdrawCart.length === 0 
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                    : 'bg-red-600 hover:bg-red-700 text-white'}`}
              >
                <ArrowUpFromLine className="w-5 h-5 mr-2" />
                Registrar Lote de Retirada
              </button>
            </div>
          </form>
        </Card>
      </div>
    );
  };

  // --- Layout Principal ---
  if (!isAuthenticated) {
    return (
      <>
        <Toast />
        <LoginScreen />
      </>
    );
  }

  const menuItems = [
    { id: 'insights', label: 'Insights Rápidos', icon: Lightbulb },
    { id: 'overview', label: 'Visão Geral', icon: LayoutGrid },
    { id: 'teacher_requests', label: 'Pedidos', icon: GraduationCap },
    { id: 'purchases', label: 'Compras', icon: CreditCard },
    { id: 'history', label: 'Histórico', icon: History },
    { id: 'dashboard', label: 'Dashboard', icon: BarChart3 },
    { id: 'library', label: 'Acervo', icon: Book },
    { id: 'receive', label: 'Recebimento', icon: ArrowDownToLine },
    { id: 'withdraw', label: 'Retirada Direta', icon: ArrowUpFromLine },
  ];

  return (
    <div className="min-h-screen bg-[#F5F5F7] font-sans flex flex-col md:flex-row selection:bg-red-100 selection:text-red-900">
      <Toast />
      
      {/* Mobile Header */}
      <div className="md:hidden bg-white/80 backdrop-blur-md sticky top-0 border-b border-gray-100 p-4 flex items-center justify-between z-30">
        <div className="flex items-center font-semibold text-gray-900 tracking-tight">
          <div className="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center mr-2">
             <Book className="w-4 h-4 text-white" />
          </div>
          SenaiStock
        </div>
        <button onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)} className="p-2 text-gray-600">
          {isMobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
        </button>
      </div>

      {/* Sidebar Overlay for Mobile */}
      {isMobileMenuOpen && (
        <div 
          className="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 md:hidden transition-opacity"
          onClick={() => setIsMobileMenuOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside className={`
        fixed md:sticky top-0 left-0 h-screen w-64 bg-white border-r border-gray-200 z-50
        transform transition-transform duration-400 ease-out
        ${isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'}
        flex flex-col
      `}>
        <div className="p-8 flex items-center">
          <div className="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center mr-3 shadow-sm">
            <Book className="w-5 h-5 text-white" />
          </div>
          <span className="text-xl font-semibold text-gray-900 tracking-tight">SenaiStock</span>
        </div>
        
        <div className="px-4 flex-1 overflow-y-auto">
          <nav className="space-y-1">
            {menuItems.map(item => {
              const Icon = item.icon;
              const isActive = activeView === item.id;
              
              // Indicador de badge no menu para os carrinhos e pedidos
              const showBadgeReq = item.id === 'purchases' && purchaseCart.length > 0;
              const showBadgeWith = item.id === 'withdraw' && withdrawCart.length > 0;
              const showBadgeTeacher = item.id === 'teacher_requests' && teacherRequests.filter(r => r.status === 'pendente').length > 0;
              
              const hasBadge = showBadgeReq || showBadgeWith || showBadgeTeacher;
              let badgeCount = 0;
              if (showBadgeReq) badgeCount = purchaseCart.length;
              if (showBadgeWith) badgeCount = withdrawCart.length;
              if (showBadgeTeacher) badgeCount = teacherRequests.filter(r => r.status === 'pendente').length;

              return (
                <button
                  key={item.id}
                  onClick={() => {
                    setActiveView(item.id);
                    setSelectedBook(null);
                    setIsMobileMenuOpen(false);
                  }}
                  className={`w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 ${
                    isActive 
                      ? 'bg-gray-100 text-gray-900 font-medium' 
                      : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'
                  }`}
                >
                  <div className="flex items-center">
                    <Icon className={`w-5 h-5 mr-3 ${isActive ? 'text-gray-900' : 'text-gray-400'}`} />
                    {item.label}
                  </div>
                  {hasBadge && (
                    <span className="bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                      {badgeCount}
                    </span>
                  )}
                </button>
              );
            })}
          </nav>
        </div>

        <div className="p-4 mt-auto">
          <div className="bg-gray-50 rounded-2xl p-4 mb-2">
            <p className="text-sm font-medium text-gray-900">Almoxarifado</p>
            <p className="text-xs text-gray-500 mt-0.5">Unidade Limeira</p>
          </div>
          <button 
            onClick={handleLogout}
            className="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors"
          >
            <LogOut className="w-4 h-4 mr-2" />
            Encerrar Sessão
          </button>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 p-6 sm:p-10 lg:p-12 max-w-6xl mx-auto w-full overflow-x-hidden">
        {activeView === 'insights' && <Insights />}
        {activeView === 'overview' && <Overview />}
        {activeView === 'teacher_requests' && <TeacherRequestsView />}
        {activeView === 'purchases' && <PurchasesView />}
        {activeView === 'history' && <HistoryView />}
        {activeView === 'dashboard' && <DashboardView />}
        {activeView === 'library' && <Library />}
        {activeView === 'receive' && <ReceiveStock />}
        {activeView === 'withdraw' && <WithdrawStock />}
      </main>
    </div>
  );
}
