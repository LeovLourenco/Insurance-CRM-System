@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Segurados</h1>
        <p class="text-muted mb-0">Gerencie o banco de dados de clientes segurados</p>
    </div>
    <a href="{{ route('segurados.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Novo Segurado
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
    <form method="GET" action="{{ route('segurados.index') }}" class="row g-3">
        <div class="col-md-4">
            <label for="search" class="form-label">Buscar segurado</label>
            <input type="text" 
                   class="form-control" 
                   id="search" 
                   name="search" 
                   value="{{ request('search') }}" 
                   placeholder="Nome, CPF/CNPJ ou telefone...">
        </div>
        <div class="col-md-3">
            <label for="tipo_pessoa" class="form-label">Tipo de pessoa</label>
            <select class="form-select" id="tipo_pessoa" name="tipo_pessoa">
                <option value="">Todos os tipos</option>
                <option value="F" {{ request('tipo_pessoa') == 'F' ? 'selected' : '' }}>
                    Pessoa Física (CPF)
                </option>
                <option value="J" {{ request('tipo_pessoa') == 'J' ? 'selected' : '' }}>
                    Pessoa Jurídica (CNPJ)
                </option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="com_cotacoes" class="form-label">Com cotações</label>
            <select class="form-select" id="com_cotacoes" name="com_cotacoes">
                <option value="">Todos</option>
                <option value="1" {{ request('com_cotacoes') == '1' ? 'selected' : '' }}>
                    Apenas com cotações
                </option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-search me-1"></i>Filtrar
            </button>
            <a href="{{ route('segurados.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Limpar
            </a>
        </div>
    </form>
</div>

<!-- Lista de Segurados -->
<div class="modern-card">
    @if($segurados->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Segurado</th>
                        <th>Documento</th>
                        <th>Tipo</th>
                        <th>Telefone</th>
                        <th>Cotações</th>
                        <th>Cadastrado em</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($segurados as $segurado)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-person-check text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $segurado->nome }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($segurado->documento)
                                    <div class="font-monospace">{{ $segurado->documento_formatado }}</div>
                                @else
                                    <span class="text-muted">Não informado</span>
                                @endif
                            </td>
                            <td>
                                @if($segurado->tipo_pessoa)
                                    <span class="badge {{ $segurado->tipo_pessoa == 'F' ? 'bg-info' : 'bg-success' }} bg-opacity-10 {{ $segurado->tipo_pessoa == 'F' ? 'text-info' : 'text-success' }}">
                                        {{ $segurado->tipo_pessoa_texto }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        Não identificado
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($segurado->telefone)
                                    <a href="tel:{{ $segurado->telefone }}" 
                                       class="text-decoration-none">
                                        <i class="bi bi-telephone me-1"></i>
                                        {{ $segurado->telefone_formatado }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($segurado->cotacoes_count > 0)
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        {{ $segurado->cotacoes_count }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">0</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $segurado->created_at->format('d/m/Y') }}
                                </small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('segurados.show', $segurado) }}">
                                                <i class="bi bi-eye me-2"></i>Visualizar
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('segurados.edit', $segurado) }}">
                                                <i class="bi bi-pencil me-2"></i>Editar
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('segurados.destroy', $segurado) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir este segurado?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Excluir
                                                </button>
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
        @if($segurados->hasPages())
            <div class="p-4 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $segurados->firstItem() }} a {{ $segurados->lastItem() }} 
                        de {{ $segurados->total() }} segurados
                    </div>
                    {{ $segurados->links() }}
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-5">
            <i class="bi bi-person-check display-1 text-muted"></i>
            <h5 class="mt-3 text-muted">Nenhum segurado encontrado</h5>
            <p class="text-muted">
                @if(request()->hasAny(['search', 'tipo_pessoa', 'com_cotacoes']))
                    Tente ajustar os filtros ou 
                    <a href="{{ route('segurados.index') }}">limpar a busca</a>
                @else
                    Comece cadastrando seu primeiro segurado
                @endif
            </p>
            @if(!request()->hasAny(['search', 'tipo_pessoa', 'com_cotacoes']))
                <a href="{{ route('segurados.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Criar Primeiro Segurado
                </a>
            @endif
        </div>
    @endif
</div>

<!-- Estatísticas resumidas -->
@if($segurados->count() > 0)
<div class="row mt-4">
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-person-check text-primary fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $segurados->total() }}</h4>
            <p class="text-muted mb-0">Total de Segurados</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-info bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-person text-info fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $segurados->where('tipo_pessoa', 'F')->count() }}</h4>
            <p class="text-muted mb-0">Pessoas Físicas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-building text-success fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $segurados->where('tipo_pessoa', 'J')->count() }}</h4>
            <p class="text-muted mb-0">Pessoas Jurídicas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center">
            <div class="bg-warning bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                <i class="bi bi-file-earmark-text text-warning fs-4"></i>
            </div>
            <h4 class="mb-1">{{ $segurados->sum('cotacoes_count') }}</h4>
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