@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Cotações</h1>
        <p class="text-muted mb-0">Gerencie todas as cotações do sistema</p>
    </div>
    <a href="{{ route('cotacoes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nova Cotação
    </a>
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
    <form method="GET" action="{{ route('cotacoes.index') }}" class="row g-3">
        <div class="col-md-3">
            <label for="search" class="form-label">Buscar cotação</label>
            <input type="text" 
                   class="form-control" 
                   id="search" 
                   name="search" 
                   value="{{ request('search') }}" 
                   placeholder="ID, observações...">
        </div>
        <div class="col-md-2">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="">Todos os status</option>
                <option value="aguardando" {{ request('status') == 'aguardando' ? 'selected' : '' }}>
                    Aguardando
                </option>
                <option value="em_analise" {{ request('status') == 'em_analise' ? 'selected' : '' }}>
                    Em Análise
                </option>
                <option value="aprovada" {{ request('status') == 'aprovada' ? 'selected' : '' }}>
                    Aprovada
                </option>
                <option value="rejeitada" {{ request('status') == 'rejeitada' ? 'selected' : '' }}>
                    Rejeitada
                </option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="corretora" class="form-label">Corretora</label>
            <select class="form-select" id="corretora" name="corretora">
                <option value="">Todas as corretoras</option>
                @foreach($cotacoes->pluck('corretora')->unique('id') as $corretora)
                    @if($corretora)
                        <option value="{{ $corretora->id }}" {{ request('corretora') == $corretora->id ? 'selected' : '' }}>
                            {{ $corretora->nome }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="produto" class="form-label">Produto</label>
            <select class="form-select" id="produto" name="produto">
                <option value="">Todos os produtos</option>
                @foreach($cotacoes->pluck('produto')->unique('id') as $produto)
                    @if($produto)
                        <option value="{{ $produto->id }}" {{ request('produto') == $produto->id ? 'selected' : '' }}>
                            {{ $produto->nome }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-search me-1"></i>Filtrar
            </button>
            <a href="{{ route('cotacoes.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Limpar
            </a>
        </div>
    </form>
</div>

<!-- Lista de Cotações -->
<div class="modern-card">
    @if($cotacoes->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Corretora</th>
                        <th>Produto</th>
                        <th>Segurado</th>
                        <th>Status</th>
                        <th>Última Atividade</th>
                        <th>Criada em</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cotacoes as $cotacao)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-file-earmark-text text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">#{{ $cotacao->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($cotacao->corretora)
                                    <div class="fw-medium">{{ $cotacao->corretora->nome }}</div>
                                    @if($cotacao->corretora->email)
                                        <small class="text-muted">{{ $cotacao->corretora->email }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Não informado</span>
                                @endif
                            </td>
                            <td>
                                @if($cotacao->produto)
                                    <div class="fw-medium">{{ $cotacao->produto->nome }}</div>
                                    @if($cotacao->produto->linha)
                                        <small class="text-muted">{{ $cotacao->produto->linha }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Não informado</span>
                                @endif
                            </td>
                            <td>
                                @if($cotacao->segurado)
                                    <div class="fw-medium">{{ $cotacao->segurado->nome }}</div>
                                    @if($cotacao->segurado->documento)
                                        <small class="text-muted font-monospace">{{ $cotacao->segurado->documento_formatado }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Não informado</span>
                                @endif
                            </td>
                            <td>
                                @switch($cotacao->status)
                                    @case('aguardando')
                                        <span class="badge bg-warning">Aguardando</span>
                                        @break
                                    @case('em_analise')
                                        <span class="badge bg-info">Em Análise</span>
                                        @break
                                    @case('aprovada')
                                        <span class="badge bg-success">Aprovada</span>
                                        @break
                                    @case('rejeitada')
                                        <span class="badge bg-danger">Rejeitada</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($cotacao->status) }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if($cotacao->atividades->count() > 0)
                                    @php $ultimaAtividade = $cotacao->atividades->first() @endphp
                                    <div class="fw-medium">{{ Str::limit($ultimaAtividade->descricao, 30) }}</div>
                                    <small class="text-muted">
                                        {{ $ultimaAtividade->created_at->format('d/m/Y H:i') }}
                                        @if($ultimaAtividade->user)
                                            por {{ $ultimaAtividade->user->name }}
                                        @endif
                                    </small>
                                @else
                                    <span class="text-muted">Nenhuma atividade</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $cotacao->created_at->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <span class="dropdown-item text-muted">
                                                <i class="bi bi-eye me-2"></i>Visualizar (em breve)
                                            </span>
                                        </li>
                                        <li>
                                            <span class="dropdown-item text-muted">
                                                <i class="bi bi-pencil me-2"></i>Editar (em breve)
                                            </span>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <span class="dropdown-item text-muted">
                                                <i class="bi bi-trash me-2"></i>Excluir (em breve)
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-file-earmark-text display-1 text-muted"></i>
            <h5 class="mt-3 text-muted">Nenhuma cotação encontrada</h5>
            <p class="text-muted">
                @if(request()->hasAny(['search', 'status', 'corretora', 'produto']))
                    Tente ajustar os filtros ou 
                    <a href="{{ route('cotacoes.index') }}">limpar a busca</a>
                @else
                    Comece criando sua primeira cotação
                @endif
            </p>
            @if(!request()->hasAny(['search', 'status', 'corretora', 'produto']))
                <a href="{{ route('cotacoes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Criar Primeira Cotação
                </a>
            @endif
        </div>
    @endif
</div>

<!-- Estatísticas resumidas -->
@if($cotacoes->count() > 0)
<div class="row mt-4">
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-file-earmark-text text-primary fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $cotacoes->count() }}</h4>
            <p class="text-muted mb-0">Total de Cotações</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-warning bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-clock text-warning fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $cotacoes->where('status', 'aguardando')->count() }}</h4>
            <p class="text-muted mb-0">Aguardando</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-check-circle text-success fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $cotacoes->where('status', 'aprovada')->count() }}</h4>
            <p class="text-muted mb-0">Aprovadas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-x-circle text-danger fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $cotacoes->where('status', 'rejeitada')->count() }}</h4>
            <p class="text-muted mb-0">Rejeitadas</p>
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

.font-monospace {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.85rem;
}
</style>
@endsection