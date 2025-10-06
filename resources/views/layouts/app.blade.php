<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Plataforma Inova</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    @include('partials.favicon')

    <!-- App CSS (includes Bootstrap with custom variables) -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: var(--inova-primary);
            --sidebar-bg: var(--inova-sidebar-bg);
            --sidebar-text: var(--inova-sidebar-text);
            --sidebar-active: var(--inova-sidebar-active);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--inova-body-bg);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--sidebar-bg) 0%, var(--inova-primary) 100%);
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-header .brand-logo {
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .brand-logo {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* Logo específico para sidebar */
        .sidebar-logo {
            height: 28px;
            width: auto;
            max-width: 140px;
            transition: all 0.3s ease;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--sidebar-text);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }

        .sidebar-toggle:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar-nav {
            padding: 1rem 0;
            flex: 1;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            color: var(--sidebar-text);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0 1.5rem;
            margin-bottom: 0.5rem;
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .nav-section-title {
            opacity: 0;
        }

        .nav-item {
            margin: 0.25rem 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(4px);
        }

        .nav-link.active {
            background: var(--sidebar-active);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .nav-text {
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .nav-text {
            opacity: 0;
            width: 0;
        }

        /* User section */
        .user-section {
            padding: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .user-info:hover {
            background: rgba(255,255,255,0.1);
        }


        .user-details {
            color: white;
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .user-details {
            opacity: 0;
            width: 0;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--sidebar-text);
            margin: 0;
        }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        .topbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 1px solid var(--inova-border-default);
        }

        .breadcrumb {
            margin: 0;
            background: none;
            padding: 0;
        }

        .content-area {
            padding: 2rem;
        }

        /* Dropdown user no sidebar */
        .user-dropdown-menu {
            position: absolute;
            bottom: 100%;
            left: 1rem;
            right: 1rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
            padding: 0.5rem;
            display: none;
            z-index: 1001;
        }

        .user-dropdown-menu.show {
            display: block;
        }

        .user-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            color: #374151;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }

        .user-dropdown-item:hover {
            background: #f3f4f6;
            color: #374151;
            transform: translateX(4px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }

        /* Modern Cards */
        .modern-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }

        .modern-card:hover {
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        /* CSS global da paginação */
        .pagination svg,
        .pagination i {
            width: 16px !important;
            height: 16px !important;
            font-size: 14px !important;
            vertical-align: middle !important;
        }

        .pagination .page-link {
            border-radius: 0.375rem;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" id="sidebar">
        <div class="sidebar-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <div class="brand-logo">
                <img src="{{ asset('assets/svg/Logo_Inova.svg') }}" alt="INOVA" class="sidebar-logo">
            </div>
        </div>

        <nav class="sidebar-nav flex-grow-1">
            <!-- Dashboard -->
            <div class="nav-section">
                <div class="nav-section-title">Principal</div>
                <div class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- Cotações -->
            @can('cotacoes.view')
            <div class="nav-section">
                <div class="nav-section-title">Cotações</div>
                <div class="nav-item">
                    <a href="{{ route('cotacoes.index') }}" class="nav-link {{ request()->routeIs('cotacoes.index') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="nav-text">
                            @role('comercial')
                                Minhas Cotações
                            @else
                                Todas Cotações
                            @endrole
                        </span>
                    </a>
                </div>
                @can('cotacoes.create')
                <div class="nav-item">
                    <a href="{{ route('cotacoes.create') }}" class="nav-link {{ request()->routeIs('cotacoes.create') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle"></i>
                        <span class="nav-text">Nova Cotação</span>
                    </a>
                </div>
                @endcan
            </div>
            @endcan

            <!-- Consultas -->
            @can('cotacoes.view')
            <div class="nav-section">
                <div class="nav-section-title">Consultas</div>
                <div class="nav-item">
                    <a href="{{ route('consultas.seguros') }}" class="nav-link {{ request()->routeIs('consultas.seguros') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i>
                        <span class="nav-text">Buscar relacionamentos</span>
                    </a>
                </div>
            </div>
            @endcan

            <!-- Cadastros Base - TODOS DEVEM VER -->
            <div class="nav-section">
                <div class="nav-section-title">
                    @role('comercial')
                        Meus Cadastros
                    @else
                        Cadastros
                    @endrole
                </div>
                
                <!-- Segurados - TODOS -->
                <div class="nav-item">
                    <a href="{{ route('segurados.index') }}" class="nav-link {{ request()->routeIs('segurados.*') ? 'active' : '' }}">
                        <i class="bi bi-person-check"></i>
                        <span class="nav-text">Segurados</span>
                    </a>
                </div>
                
                <!-- Corretoras - TODOS -->
                <div class="nav-item">
                    <a href="{{ route('corretoras.index') }}" class="nav-link {{ request()->routeIs('corretoras.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>
                        <span class="nav-text">Corretoras</span>
                    </a>
                </div>
                
                <!-- Produtos - TODOS -->
                <div class="nav-item">
                    <a href="{{ route('produtos.index') }}" class="nav-link {{ request()->routeIs('produtos.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i>
                        <span class="nav-text">Produtos</span>
                    </a>
                </div>
                
                <!-- Seguradoras - TODOS -->
                <div class="nav-item">
                    <a href="{{ route('seguradoras.index') }}" class="nav-link {{ request()->routeIs('seguradoras.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span class="nav-text">Seguradoras</span>
                    </a>
                </div>
            </div>

            <!-- Relatórios (Admin e Diretor) -->
            @role('admin|diretor')
            <div class="nav-section">
                <div class="nav-section-title">Relatórios</div>
                <div class="nav-item">
                    <a href="{{ route('relatorios.auditoria') }}" 
                       class="nav-link {{ request()->routeIs('relatorios.auditoria') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i>
                        <span class="nav-text">Histórico de Auditoria</span>
                    </a>
                </div>
            </div>
            @endrole

            <!-- Administração (apenas para admin) -->
            @role('admin')
            <div class="nav-section">
                <div class="nav-section-title">Sistema</div>
                <div class="nav-item">
                    <a href="#" onclick="mostrarDesenvolvimento('Gestão de usuários em desenvolvimento'); return false;" class="nav-link">
                        <i class="bi bi-people"></i>
                        <span class="nav-text">Usuários</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" onclick="mostrarDesenvolvimento('Configurações do sistema em desenvolvimento'); return false;" class="nav-link">
                        <i class="bi bi-gear"></i>
                        <span class="nav-text">Configurações</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.downloads-cadastros') }}" 
                       class="nav-link {{ request()->routeIs('admin.downloads-cadastros') ? 'active' : '' }}">
                        <i class="bi bi-download"></i>
                        <span class="nav-text">Downloads</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.atribuicoes') }}" 
                       class="nav-link {{ request()->routeIs('admin.atribuicoes*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>
                        <span class="nav-text">Atribuições</span>
                    </a>
                </div>
            </div>
            @endrole
        </nav>

        <!-- User Section -->
        <div class="user-section">
            @auth
            <div class="position-relative">
                <div class="user-info" onclick="toggleUserDropdown()">
                    <x-avatar :name="Auth::user()->name" size="sm" />
                    <div class="user-details">
                        <p class="user-name">{{ Auth::user()->name }}</p>
                        <p class="user-role">
                            @role('admin')
                                Administrador
                            @elserole('diretor')
                                Diretor
                            @elserole('comercial')
                                Comercial
                            @else
                                Usuário
                            @endrole
                        </p>
                    </div>
                </div>
                
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <a href="{{ route('usuario.perfil') }}" class="user-dropdown-item">
                        <i class="bi bi-person-circle"></i>
                        <span>Perfil</span>
                    </a>
                    <a href="#" class="user-dropdown-item"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Sair</span>
                    </a>
                </div>
            </div>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
            @endauth
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Bar -->
        <div class="topbar">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Esquerda - Breadcrumb (navegação em primeiro) -->
                <nav aria-label="breadcrumb" class="d-flex align-items-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}" class="text-decoration-none">
                                <i class="bi bi-house-door me-1"></i>Home
                            </a>
                        </li>
                        @if(!request()->routeIs('home'))
                            @php
                                $segment = request()->segment(1);
                                $icon = match($segment) {
                                    'produtos' => 'box-seam',
                                    'seguradoras' => 'building',
                                    'corretoras' => 'person-badge',
                                    'segurados' => 'person-check',
                                    'cotacoes' => 'file-earmark-text',
                                    'consultas' => 'shield-check',
                                    default => 'folder'
                                };
                                $nome = match($segment) {
                                    'produtos' => 'Produtos',
                                    'seguradoras' => 'Seguradoras',
                                    'corretoras' => 'Corretoras',
                                    'segurados' => 'Segurados',
                                    'cotacoes' => 'Cotações',
                                    'consultas' => 'Consultas',
                                    default => ucfirst($segment)
                                };
                            @endphp
                            @if(!View::hasSection('breadcrumb-extra'))
                                <li class="breadcrumb-item active d-flex align-items-center" aria-current="page">
                                    <i class="bi bi-{{ $icon }} me-1"></i>{{ $nome }}
                                </li>
                            @else
                                <li class="breadcrumb-item d-flex align-items-center">
                                    @php
                                        $indexRoute = $segment . '.index';
                                    @endphp
                                    <a href="{{ route($indexRoute) }}" class="text-decoration-none">
                                        <i class="bi bi-{{ $icon }} me-1"></i>{{ $nome }}
                                    </a>
                                </li>
                                @yield('breadcrumb-extra')
                            @endif
                        @endif
                    </ol>
                </nav>

                <!-- Centro - Nome da empresa (contexto) 
                <div class="company-name d-none d-lg-block">
                    <h6 class="mb-0 fw-medium text-primary">
                        <i class="bi bi-building me-2"></i>Inova Representação LTDA
                    </h6>
                </div>
                            -->
                <!-- Direita - Data e hora (informação passiva) -->
                <div class="topbar-datetime d-none d-md-block">
                    <small class="text-muted">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ now()->format('d/m/Y') }}
                    </small>
                    <small class="text-muted ms-2">
                        <i class="bi bi-clock me-1"></i>
                        <span id="current-time">{{ now()->format('H:i') }}</span>
                    </small>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            @yield('content')
        </div>
    </div>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="toastDesenvolvimento" class="toast" role="alert">
            <div class="toast-header">
                <i class="bi bi-hammer text-warning me-2"></i>
                <strong class="me-auto">Em Desenvolvimento</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                🚧 Esta funcionalidade está sendo desenvolvida e estará disponível em breve!
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }

        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdownMenu');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userInfo = document.querySelector('.user-info');
            const dropdown = document.getElementById('userDropdownMenu');
            
            if (!userInfo.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Mobile responsiveness
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.add('collapsed');
            document.getElementById('mainContent').classList.add('expanded');
        }
        function mostrarDesenvolvimento(mensagem = null) {
            const toastEl = document.getElementById('toastDesenvolvimento');
            const toastBody = toastEl.querySelector('.toast-body');
            
            // Personalizar mensagem se fornecida
            if (mensagem) {
                toastBody.textContent = mensagem;
            } else {
                toastBody.innerHTML = '🚧 Esta funcionalidade está sendo desenvolvida e estará disponível em breve!';
            }
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    </script>
    <style>
        /* Topbar melhorado */
        .topbar {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 1rem 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-bottom: 1px solid var(--inova-border-default);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Breadcrumb (prioridade visual) */
        .breadcrumb {
            margin: 0;
            background: none;
            padding: 0;
        }

        .breadcrumb-item a {
            color: var(--inova-text-dark);
            transition: color 0.2s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .breadcrumb-item a:hover {
            color: var(--inova-primary);
        }

        .breadcrumb-item.active {
            color: var(--inova-primary);
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Nome da empresa (contexto, mais discreto) */
        .company-name h6 {
            color: var(--inova-text-muted);
            font-size: 0.9rem;
            letter-spacing: -0.025em;
        }

        .company-name h6 i {
            color: var(--inova-text-light);
        }

        /* Data/hora (informação passiva) */
        .topbar-datetime {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--inova-text-light);
            font-size: 0.875rem;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .topbar {
                padding: 0.75rem 1rem;
            }
            
            .breadcrumb-item a {
                font-size: 0.9rem;
            }
            
            .breadcrumb-item.active {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .topbar {
                padding: 0.5rem 1rem;
            }
            
            .breadcrumb-item a {
                font-size: 0.85rem;
            }
            
            .breadcrumb-item.active {
                font-size: 0.85rem;
            }
        }
    </style>
    @stack('styles')
    <script>
        // Atualizar hora em tempo real
        function updateTime() {
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                const now = new Date();
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                timeElement.textContent = `${hours}:${minutes}`;
            }
        }

        // Atualizar a cada minuto
        document.addEventListener('DOMContentLoaded', function() {
            updateTime(); // Atualiza imediatamente
            setInterval(updateTime, 60000); // Atualiza a cada minuto
        });
    </script>
    @stack('scripts')
</body>
</html>