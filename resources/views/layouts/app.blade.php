<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Cotações</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #2563eb;
            --sidebar-bg: #1e293b;
            --sidebar-text: #94a3b8;
            --sidebar-active: #3b82f6;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--sidebar-bg) 0%, #0f172a 100%);
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

        .sidebar-header .brand-text {
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .brand-text {
            opacity: 0;
            width: 0;
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

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
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
            border-bottom: 1px solid #e2e8f0;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" id="sidebar">
        <div class="sidebar-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <span class="brand-text">Logos</span>
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
            <div class="nav-section">
                <div class="nav-section-title">Cotações</div>
                <div class="nav-item">
                    <a href="{{ route('cotacoes.index') }}" class="nav-link {{ request()->routeIs('cotacoes.index') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="nav-text">Todas Cotações</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('cotacoes.create') }}" class="nav-link {{ request()->routeIs('cotacoes.create') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle"></i>
                        <span class="nav-text">Nova Cotação</span>
                    </a>
                </div>
            </div>

            <!-- Consultas -->
            <div class="nav-section">
                <div class="nav-section-title">Consultas</div>
                <div class="nav-item">
                    <a href="{{ route('consultas.seguros') }}" class="nav-link {{ request()->routeIs('consultas.seguros') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i>
                        <span class="nav-text">Buscar Seguros</span>
                    </a>
                </div>
            </div>

            <!-- Gerenciamento -->
            <div class="nav-section">
                <div class="nav-section-title">Gerenciamento</div>
                <div class="nav-item">
                    <a href="{{ route('produtos.index') }}" class="nav-link {{ request()->routeIs('produtos.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i>
                        <span class="nav-text">Produtos</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('seguradoras.index') }}" class="nav-link {{ request()->routeIs('seguradoras.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span class="nav-text">Seguradoras</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('corretoras.index') }}" class="nav-link {{ request()->routeIs('corretoras.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>
                        <span class="nav-text">Corretoras</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('segurados.index') }}" class="nav-link {{ request()->routeIs('segurados.*') ? 'active' : '' }}">
                        <i class="bi bi-person-check"></i>
                        <span class="nav-text">Segurados</span>
                    </a>
                </div>
                
                <!-- <div class="nav-item">
                    <a href="{{ route('vinculos.index') }}" class="nav-link {{ request()->routeIs('vinculos.index') ? 'active' : '' }}">
                        <i class="bi bi-link-45deg"></i>
                        <span class="nav-text">Vínculos</span>
                    </a>
                </div> -->
            </div>
        </nav>

        <!-- User Section -->
        <div class="user-section">
            @auth
            <div class="position-relative">
                <div class="user-info" onclick="toggleUserDropdown()">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()->name)[1] ?? '', 0, 1)) }}
                    </div>
                    <div class="user-details">
                        <p class="user-name">{{ Auth::user()->name }}</p>
                        <p class="user-role">Usuário</p>
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
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        @if(!request()->routeIs('home'))
                            <li class="breadcrumb-item active" aria-current="page">{{ ucfirst(request()->segment(1)) }}</li>
                        @endif
                    </ol>
                </nav>
                
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">{{ now()->format('d/m/Y - H:i') }}</small>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            @yield('content')
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
    </script>
</body>
</html>