@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Apólices</h1>
            <p class="text-muted mb-0">Gerencie as apólices do sistema</p>
        </div>
        <div class="d-flex gap-2">
            @can('create', App\Models\Apolice::class)
                <a href="{{ route('apolices.import.form') }}" class="btn btn-outline-primary">
                    <i class="bi bi-upload me-2"></i>Importar Excel
                </a>
                <a href="{{ route('apolices.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nova Apólice
                </a>
            @endcan
        </div>
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
        <form method="GET" action="{{ route('apolices.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="busca" class="form-label">Buscar</label>
                <input type="text" 
                       class="form-control" 
                       id="busca" 
                       name="busca" 
                       value="{{ request('busca') }}" 
                       placeholder="Número, segurado, corretor...">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="pendente_emissao" {{ request('status') == 'pendente_emissao' ? 'selected' : '' }}>
                        Pendente Emissão
                    </option>
                    <option value="ativa" {{ request('status') == 'ativa' ? 'selected' : '' }}>
                        Ativa
                    </option>
                    <option value="renovacao" {{ request('status') == 'renovacao' ? 'selected' : '' }}>
                        Em Renovação
                    </option>
                    <option value="cancelada" {{ request('status') == 'cancelada' ? 'selected' : '' }}>
                        Cancelada
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="corretora" class="form-label">Corretora</label>
                <select name="corretora" id="corretora" class="form-select">
                    <option value="">Todas</option>
                    @foreach($corretoras as $corretora)
                        <option value="{{ $corretora->id }}" 
                            {{ request('corretora') == $corretora->id ? 'selected' : '' }}>
                            {{ $corretora->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="seguradora" class="form-label">Seguradora</label>
                <select name="seguradora" id="seguradora" class="form-select">
                    <option value="">Todas</option>
                    @foreach($seguradoras as $seguradora)
                        <option value="{{ $seguradora->id }}" 
                            {{ request('seguradora') == $seguradora->id ? 'selected' : '' }}>
                            {{ $seguradora->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Apólices -->
    <div class="modern-card">
        @if($apolices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Número</th>
                            <th>Segurado</th>
                            <th>Corretora</th>
                            <th>Seguradora</th>
                            <th>Status</th>
                            <th>Vigência</th>
                            <th>Origem</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($apolices as $apolice)
                            <tr>
                                <td>
                                    <div class="fw-medium">
                                        {{ $apolice->numero_apolice ?? 'N/A' }}
                                    </div>
                                    @if($apolice->endosso)
                                        <small class="text-muted">End: {{ $apolice->endosso }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium">
                                        {{ $apolice->segurado->nome ?? $apolice->nome_segurado ?? 'N/A' }}
                                    </div>
                                    @if($apolice->cnpj_segurado)
                                        <small class="text-muted">{{ $apolice->cnpj_segurado_formatado }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $apolice->corretora->nome ?? $apolice->nome_corretor ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ $apolice->seguradora->nome ?? 'N/A' }}
                                </td>
                                <td>
                                    @php
                                        $statusClasses = [
                                            'pendente_emissao' => 'bg-warning',
                                            'ativa' => 'bg-success',
                                            'renovacao' => 'bg-info',
                                            'cancelada' => 'bg-danger'
                                        ];
                                        $statusClass = $statusClasses[$apolice->status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ $apolice->status_formatado }}
                                    </span>
                                </td>
                                <td>
                                    @if($apolice->inicio_vigencia && $apolice->fim_vigencia)
                                        <div class="text-nowrap">
                                            <small class="text-muted d-block">
                                                {{ $apolice->inicio_vigencia->format('d/m/Y') }}
                                            </small>
                                            <small class="text-muted">
                                                até {{ $apolice->fim_vigencia->format('d/m/Y') }}
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($apolice->origem === 'cotacao')
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-file-earmark-text me-1"></i>Cotação
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            <i class="bi bi-upload me-1"></i>Importada
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('apolices.show', $apolice) }}">
                                                    <i class="bi bi-eye me-2"></i>Visualizar
                                                </a>
                                            </li>
                                            @can('update', $apolice)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('apolices.edit', $apolice) }}">
                                                    <i class="bi bi-pencil me-2"></i>Editar
                                                </a>
                                            </li>
                                            @endcan
                                            @can('delete', $apolice)
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('apolices.destroy', $apolice) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Tem certeza que deseja excluir esta apólice?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i>Excluir
                                                    </button>
                                                </form>
                                            </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            @if($apolices->hasPages())
                <div class="p-4 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando {{ $apolices->firstItem() }} a {{ $apolices->lastItem() }} 
                            de {{ $apolices->total() }} apólices
                        </div>
                        {{ $apolices->links() }}
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-shield-check display-1 text-muted"></i>
                <h5 class="mt-3 text-muted">Nenhuma apólice encontrada</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['busca', 'status', 'corretora', 'seguradora']))
                        Tente ajustar os filtros ou 
                        <a href="{{ route('apolices.index') }}">limpar a busca</a>
                    @else
                        Comece cadastrando sua primeira apólice ou importando dados
                    @endif
                </p>
                @if(!request()->hasAny(['busca', 'status', 'corretora', 'seguradora']))
                    @can('create', App\Models\Apolice::class)
                        <div class="mt-3">
                            <a href="{{ route('apolices.create') }}" class="btn btn-primary me-2">
                                <i class="bi bi-plus-circle me-2"></i>Criar Apólice
                            </a>
                            <a href="{{ route('apolices.import.form') }}" class="btn btn-outline-primary">
                                <i class="bi bi-upload me-2"></i>Importar Excel
                            </a>
                        </div>
                    @endcan
                @endif
            </div>
        @endif
    </div>
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

.badge {
    font-weight: 500;
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endsection