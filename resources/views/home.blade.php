@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Page Header -->
    <div class="col-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Dashboard</h1>
                <p class="text-muted mb-0">Bem-vindo de volta, {{ Auth::user()->name }}! 👋</p>
            </div>
            <div>
                <a href="{{ route('cotacoes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Nova Cotação
                </a>
            </div>
        </div>
    </div>

    <!-- Status Messages -->
    @if (session('status'))
        <div class="col-12 mb-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="col-md-3 mb-4">
        <div class="modern-card p-4">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                    <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0">127</h3>
                    <p class="text-muted mb-0 small">Cotações Ativas</p>
                </div>
            </div>
            <div class="mt-3">
                <small class="text-success">
                    <i class="bi bi-arrow-up"></i> +12% este mês
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="modern-card p-4">
            <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                    <i class="bi bi-check-circle text-success fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0">89</h3>
                    <p class="text-muted mb-0 small">Aprovadas</p>
                </div>
            </div>
            <div class="mt-3">
                <small class="text-success">
                    <i class="bi bi-arrow-up"></i> +8% este mês
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="modern-card p-4">
            <div class="d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                    <i class="bi bi-clock text-warning fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0">23</h3>
                    <p class="text-muted mb-0 small">Pendentes</p>
                </div>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="bi bi-dash"></i> Sem mudança
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="modern-card p-4">
            <div class="d-flex align-items-center">
                <div class="bg-info bg-opacity-10 p-3 rounded-3 me-3">
                    <i class="bi bi-people text-info fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0">45</h3>
                    <p class="text-muted mb-0 small">Clientes Ativos</p>
                </div>
            </div>
            <div class="mt-3">
                <small class="text-success">
                    <i class="bi bi-arrow-up"></i> +3 novos
                </small>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="col-lg-8 mb-4">
        <div class="modern-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Cotações Recentes</h5>
                <a href="{{ route('cotacoes.index') }}" class="btn btn-sm btn-outline-primary">Ver Todas</a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Cliente</th>
                            <th>Produto</th>
                            <th>Status</th>
                            <th>Valor</th>
                            <th>Data</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                        <i class="bi bi-person text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">Maria Silva</div>
                                        <small class="text-muted">maria@email.com</small>
                                    </div>
                                </div>
                            </td>
                            <td>Seguro Auto</td>
                            <td><span class="badge bg-success">Aprovado</span></td>
                            <td>R$ 1.200,00</td>
                            <td>10/07/2025</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Ver Detalhes</a></li>
                                        <li><a class="dropdown-item" href="#">Editar</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-2 me-2">
                                        <i class="bi bi-person text-success"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">João Santos</div>
                                        <small class="text-muted">joao@email.com</small>
                                    </div>
                                </div>
                            </td>
                            <td>Seguro Residencial</td>
                            <td><span class="badge bg-warning">Pendente</span></td>
                            <td>R$ 890,00</td>
                            <td>09/07/2025</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Ver Detalhes</a></li>
                                        <li><a class="dropdown-item" href="#">Editar</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2">
                                        <i class="bi bi-person text-info"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">Ana Costa</div>
                                        <small class="text-muted">ana@email.com</small>
                                    </div>
                                </div>
                            </td>
                            <td>Seguro Vida</td>
                            <td><span class="badge bg-info">Em Análise</span></td>
                            <td>R$ 450,00</td>
                            <td>08/07/2025</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Ver Detalhes</a></li>
                                        <li><a class="dropdown-item" href="#">Editar</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Notifications -->
    <div class="col-lg-4 mb-4">
        <div class="modern-card p-4 mb-4">
            <h5 class="mb-3">Ações Rápidas</h5>
            <div class="d-grid gap-2">
                <a href="{{ route('cotacoes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nova Cotação
                </a>
                <a href="{{ route('consultas.seguros') }}" class="btn btn-outline-primary">
                    <i class="bi bi-search me-2"></i>Buscar Seguros
                </a>
                <a href="{{ route('segurados.create') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-person-plus me-2"></i>Novo Cliente
                </a>
                <a href="#" onclick="mostrarDesenvolvimento(); return false;" class="btn btn-outline-info">
                    <i class="bi bi-link-45deg me-2"></i>Relatórios
                </a>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="modern-card p-4">
            <h5 class="mb-3">Atividades Recentes</h5>
            <div class="activity-feed">
                <div class="activity-item d-flex mb-3">
                    <div class="activity-icon bg-success bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-check-circle text-success"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-medium">Cotação aprovada</div>
                        <small class="text-muted">Maria Silva - Seguro Auto</small>
                        <div class="text-muted small">2 horas atrás</div>
                    </div>
                </div>

                <div class="activity-item d-flex mb-3">
                    <div class="activity-icon bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-plus-circle text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-medium">Nova cotação criada</div>
                        <small class="text-muted">João Santos - Seguro Residencial</small>
                        <div class="text-muted small">4 horas atrás</div>
                    </div>
                </div>

                <div class="activity-item d-flex mb-3">
                    <div class="activity-icon bg-info bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-person-plus text-info"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-medium">Novo cliente cadastrado</div>
                        <small class="text-muted">Ana Costa</small>
                        <div class="text-muted small">1 dia atrás</div>
                    </div>
                </div>

                <div class="activity-item d-flex">
                    <div class="activity-icon bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-clock text-warning"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-medium">Cotação pendente</div>
                        <small class="text-muted">Carlos Lima - Seguro Empresarial</small>
                        <div class="text-muted small">2 dias atrás</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart -->
    <div class="col-12">
        <div class="modern-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Performance do Mês</h5>
                    <p class="text-muted mb-0 small">Cotações realizadas nos últimos 30 dias</p>
                </div>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="period" id="week" checked>
                    <label class="btn btn-sm btn-outline-secondary" for="week">7 dias</label>
                    
                    <input type="radio" class="btn-check" name="period" id="month">
                    <label class="btn btn-sm btn-outline-secondary" for="month">30 dias</label>
                    
                    <input type="radio" class="btn-check" name="period" id="year">
                    <label class="btn btn-sm btn-outline-secondary" for="year">12 meses</label>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <!-- Aqui você pode adicionar um gráfico com Chart.js ou similar -->
                    <div class="bg-light rounded p-4 text-center d-flex align-items-center justify-content-center" style="height: 300px;">
                        <div>
                            <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Gráfico de Performance</p>
                            <small class="text-muted">Integração com Chart.js disponível</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <h4 class="text-primary mb-1">78%</h4>
                                <small class="text-muted">Taxa de Aprovação</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <h4 class="text-success mb-1">R$ 45.230</h4>
                                <small class="text-muted">Receita do Mês</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                                <h4 class="text-info mb-1">2.3 dias</h4>
                                <small class="text-muted">Tempo Médio</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .activity-feed .activity-item:not(:last-child) {
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 1rem;
    }
    
    .activity-icon {
        min-width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
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
    
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
    }
    
    .btn-group .btn-check:checked + .btn {
        background-color: #2563eb;
        border-color: #2563eb;
        color: white;
    }
</style>
@endsection