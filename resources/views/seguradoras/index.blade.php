@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Seguradoras</h1>
        <p class="text-muted mb-0">Gerencie as seguradoras parceiras do sistema</p>
    </div>
    @can('create', App\Models\Seguradora::class)
        <a href="{{ route('seguradoras.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nova Seguradora
        </a>
    @endcan
</div>

<!-- Alerts -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Filtros -->
<div class="modern-card p-4 mb-4">
    <form method="GET" action="{{ route('seguradoras.index') }}" class="row g-3">
        <div class="col-md-4">
            <label for="search" class="form-label">Buscar seguradora</label>
            <input type="text" 
                   class="form-control" 
                   id="search" 
                   name="search" 
                   value="{{ request('search') }}" 
                   placeholder="Nome, site ou observações...">
        </div>
        <div class="col-md-3">
            <label for="com_produtos" class="form-label">Com produtos</label>
            <select class="form-select" id="com_produtos" name="com_produtos">
                <option value="">Todas</option>
                <option value="1" {{ request('com_produtos') == '1' ? 'selected' : '' }}>
                    Apenas com produtos
                </option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="com_corretoras" class="form-label">Com corretoras</label>
            <select class="form-select" id="com_corretoras" name="com_corretoras">
                <option value="">Todas</option>
                <option value="1" {{ request('com_corretoras') == '1' ? 'selected' : '' }}>
                    Apenas com corretoras
                </option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-search me-1"></i>Filtrar
            </button>
            <a href="{{ route('seguradoras.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Limpar
            </a>
        </div>
    </form>
</div>

<!-- Lista de Seguradoras -->
<div class="modern-card">
    @if($seguradoras->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Seguradora</th>
                        <th>Site</th>
                        <th>Produtos</th>
                        <th>Corretoras</th>
                        <th>Cotações</th>
                        <th>Criada em</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($seguradoras as $seguradora)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-building text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $seguradora->nome }}</div>
                                        @if($seguradora->observacoes)
                                            <small class="text-muted">
                                                {{ Str::limit($seguradora->observacoes, 50) }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($seguradora->site)
                                    <a href="{{ $seguradora->site }}" 
                                       target="_blank" 
                                       class="text-decoration-none">
                                        <i class="bi bi-globe me-1"></i>
                                        {{ $seguradora->site_formatado }}
                                        <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($seguradora->produtos_count > 0)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        {{ $seguradora->produtos_count }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">0</span>
                                @endif
                            </td>
                            <td>
                                @if($seguradora->corretoras_count > 0)
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        {{ $seguradora->corretoras_count }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">0</span>
                                @endif
                            </td>
                            <td>
                                @if($seguradora->cotacoes_count > 0)
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        {{ $seguradora->cotacoes_count }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">0</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $seguradora->created_at->format('d/m/Y') }}
                                </small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('seguradoras.show', $seguradora) }}">
                                                <i class="bi bi-eye me-2"></i>Visualizar
                                            </a>
                                        </li>
                                        @can('update', $seguradora)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('seguradoras.edit', $seguradora) }}">
                                                <i class="bi bi-pencil me-2"></i>Editar
                                            </a>
                                        </li>
                                        @endcan
                                        @can('delete', $seguradora)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('seguradoras.destroy', $seguradora) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir esta seguradora?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Excluir
                                                </button>
                                        @endcan
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($seguradoras->hasPages())
            <div class="p-4 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $seguradoras->firstItem() }} a {{ $seguradoras->lastItem() }} 
                        de {{ $seguradoras->total() }} seguradoras
                    </div>
                    {{ $seguradoras->links() }}
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-5">
            <i class="bi bi-building display-1 text-muted"></i>
            <h5 class="mt-3 text-muted">Nenhuma seguradora encontrada</h5>
            <p class="text-muted">
                @if(request()->hasAny(['search', 'com_produtos', 'com_corretoras']))
                    Tente ajustar os filtros ou 
                    <a href="{{ route('seguradoras.index') }}">limpar a busca</a>
                @else
                    Comece cadastrando sua primeira seguradora
                @endif
            </p>
            @if(!request()->hasAny(['search', 'com_produtos', 'com_corretoras']))
                <a href="{{ route('seguradoras.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Criar Primeira Seguradora
                </a>
            @endif
        </div>
    @endif
</div>

<!-- Estatísticas resumidas -->
@if($seguradoras->count() > 0)
<div class="row mt-4">
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-building text-primary fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $seguradoras->total() }}</h4>
            <p class="text-muted mb-0">Total de Seguradoras</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-box-seam text-success fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $seguradoras->sum('produtos_count') }}</h4>
            <p class="text-muted mb-0">Produtos Oferecidos</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-info bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-people text-info fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $seguradoras->sum('corretoras_count') }}</h4>
            <p class="text-muted mb-0">Parcerias com Corretoras</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-warning bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-file-earmark-text text-warning fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $seguradoras->sum('cotacoes_count') }}</h4>
            <p class="text-muted mb-0">Cotações Realizadas</p>
        </div>
    </div>
</div>
@endif

<style>
.modern-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.modern-card:hover {
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    color: #64748b;
    border-bottom: 2px solid #f1f5f9;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
    border-radius: 0.75rem;
}

.badge {
    font-weight: 500;
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endsection