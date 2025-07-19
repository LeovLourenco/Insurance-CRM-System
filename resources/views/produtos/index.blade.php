@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Produtos</h1>
        <p class="text-muted mb-0">Gerencie os produtos de seguro disponíveis</p>
    </div>
    <a href="{{ route('produtos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Novo Produto
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
    <form method="GET" action="{{ route('produtos.index') }}" class="row g-3">
        <div class="col-md-4">
            <label for="search" class="form-label">Buscar produto</label>
            <input type="text" 
                   class="form-control" 
                   id="search" 
                   name="search" 
                   value="{{ request('search') }}" 
                   placeholder="Nome ou descrição...">
        </div>
        <div class="col-md-3">
            <label for="linha" class="form-label">Linha</label>
            <select class="form-select" id="linha" name="linha">
                <option value="">Todas as linhas</option>
                @foreach($linhas as $linha)
                    <option value="{{ $linha }}" {{ request('linha') == $linha ? 'selected' : '' }}>
                        {{ $linha }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-search me-1"></i>Filtrar
            </button>
            <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Limpar
            </a>
        </div>
    </form>
</div>

<!-- Lista de Produtos -->
<div class="modern-card">
    @if($produtos->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produto</th>
                        <th>Linha</th>
                        <th>Seguradoras</th>
                        <th>Cotações</th>
                        <th>Criado em</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produtos as $produto)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-box-seam text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $produto->nome }}</div>
                                        @if($produto->descricao)
                                            <small class="text-muted">
                                                {{ Str::limit($produto->descricao, 50) }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($produto->linha)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ $produto->linha }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    {{ $produto->seguradoras->count() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    {{ $produto->cotacoes->count() }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $produto->created_at->format('d/m/Y') }}
                                </small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('produtos.show', $produto) }}">
                                                <i class="bi bi-eye me-2"></i>Visualizar
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('produtos.edit', $produto) }}">
                                                <i class="bi bi-pencil me-2"></i>Editar
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('produtos.destroy', $produto) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir este produto?')">
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
        @if($produtos->hasPages())
            <div class="p-4 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $produtos->firstItem() }} a {{ $produtos->lastItem() }} 
                        de {{ $produtos->total() }} produtos
                    </div>
                    {{ $produtos->links() }}
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h5 class="mt-3 text-muted">Nenhum produto encontrado</h5>
            <p class="text-muted">
                @if(request()->hasAny(['search', 'linha']))
                    Tente ajustar os filtros ou 
                    <a href="{{ route('produtos.index') }}">limpar a busca</a>
                @else
                    Comece cadastrando seu primeiro produto
                @endif
            </p>
            @if(!request()->hasAny(['search', 'linha']))
                <a href="{{ route('produtos.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Criar Primeiro Produto
                </a>
            @endif
        </div>
    @endif
</div>

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
</style>
@endsection