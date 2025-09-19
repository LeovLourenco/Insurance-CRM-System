@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $seguradora->nome }}</h1>
        <p class="text-muted mb-0">Detalhes da seguradora</p>
    </div>
    <div class="d-flex gap-2">
        @can('update', $seguradora)
            <a href="{{ route('seguradoras.edit', $seguradora) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-2"></i>Editar
            </a>
        @endcan
        <a href="{{ route('seguradoras.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>
</div>

<!-- Informações da Seguradora -->
<div class="row g-4">
    <!-- Card Principal -->
    <div class="col-lg-8">
        <div class="modern-card p-4">
            <div class="row g-4">
                <!-- Informações básicas -->
                <div class="col-12">
                    <h4 class="border-bottom pb-3 mb-3">
                        <i class="bi bi-building text-primary me-2"></i>
                        Informações da Seguradora
                    </h4>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Nome da Seguradora</label>
                    <p class="fs-5 mb-0">{{ $seguradora->nome }}</p>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Site</label>
                    @if($seguradora->site)
                        <p class="mb-0">
                            <a href="{{ $seguradora->site }}" 
                               target="_blank" 
                               class="text-decoration-none">
                                <i class="bi bi-globe me-2"></i>
                                {{ $seguradora->site_formatado }}
                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                        </p>
                    @else
                        <p class="text-muted mb-0">Não informado</p>
                    @endif
                </div>

                @if($seguradora->observacoes)
                <div class="col-12">
                    <label class="form-label fw-bold text-muted">Observações</label>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $seguradora->observacoes }}</p>
                    </div>
                </div>
                @endif

                <!-- Datas -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Criada em</label>
                    @if($seguradora->created_at)
                        <p class="mb-0">
                            <i class="bi bi-calendar3 text-muted me-2"></i>
                            {{ $seguradora->created_at->format('d/m/Y') }} às {{ $seguradora->created_at->format('H:i') }}
                        </p>
                        <small class="text-muted">{{ $seguradora->created_at->diffForHumans() }}</small>
                    @else
                        <p class="text-muted mb-0">Data não disponível</p>
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Última atualização</label>
                    @if($seguradora->updated_at)
                        <p class="mb-0">
                            <i class="bi bi-clock text-muted me-2"></i>
                            {{ $seguradora->updated_at->format('d/m/Y') }} às {{ $seguradora->updated_at->format('H:i') }}
                        </p>
                        <small class="text-muted">{{ $seguradora->updated_at->diffForHumans() }}</small>
                    @else
                        <p class="text-muted mb-0">Data não disponível</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Card de Estatísticas -->
    <div class="col-lg-4">
        <div class="modern-card p-4">
            <h5 class="border-bottom pb-3 mb-3">
                <i class="bi bi-graph-up text-success me-2"></i>
                Estatísticas
            </h5>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <i class="bi bi-box-seam text-success me-2"></i>
                    <span>Produtos</span>
                </div>
                <span class="badge bg-success fs-6">{{ $seguradora->produtos->count() }}</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <i class="bi bi-people text-info me-2"></i>
                    <span>Corretoras</span>
                </div>
                <span class="badge bg-info fs-6">{{ $seguradora->corretoras->count() }}</span>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-file-earmark-text text-warning me-2"></i>
                    <span>Cotações</span>
                </div>
                <span class="badge bg-warning fs-6">{{ $seguradora->cotacoes->count() }}</span>
            </div>
        </div>

        <!-- Estatísticas de Cotações -->
        @if($cotacoesPorStatus->count() > 0)
        <div class="modern-card p-4 mt-4">
            <h6 class="mb-3">
                <i class="bi bi-pie-chart text-primary me-2"></i>
                Cotações por Status
            </h6>
            
            @foreach($cotacoesPorStatus as $status => $total)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-capitalize">{{ $status }}</span>
                    <span class="badge bg-{{ $status == 'aprovada' ? 'success' : ($status == 'pendente' ? 'warning' : 'secondary') }}">
                        {{ $total }}
                    </span>
                </div>
            @endforeach
        </div>
        @endif

        <!-- Card de Ações -->
        <div class="modern-card p-4 mt-4">
            <h6 class="mb-3">
                <i class="bi bi-lightning text-warning me-2"></i>
                Ações Rápidas
            </h6>
            
            <div class="d-grid gap-2">
                <a href="{{ route('seguradoras.edit', $seguradora) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-2"></i>Editar Seguradora
                </a>
                
                @if($seguradora->site)
                    <a href="{{ $seguradora->site }}" target="_blank" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-globe me-2"></i>Visitar Site
                    </a>
                @endif
                
                @if($seguradora->cotacoes->count() == 0)
                    <button class="btn btn-outline-danger btn-sm" 
                            onclick="confirmDelete()">
                        <i class="bi bi-trash me-2"></i>Excluir Seguradora
                    </button>
                @else
                    <button class="btn btn-outline-secondary btn-sm" 
                            disabled 
                            title="Seguradora possui cotações">
                        <i class="bi bi-shield-check me-2"></i>Seguradora em Uso
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Produtos oferecidos -->
@if($seguradora->produtos->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="modern-card p-4">
            <h5 class="border-bottom pb-3 mb-4">
                <i class="bi bi-box-seam text-success me-2"></i>
                Produtos Oferecidos
                <span class="badge bg-success ms-2">{{ $seguradora->produtos->count() }}</span>
            </h5>
            
            <div class="row g-3">
                @foreach($seguradora->produtos as $produto)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">{{ $produto->nome }}</h6>
                                    @if($produto->linha)
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ $produto->linha }}
                                        </span>
                                    @endif
                                </div>
                                
                                @if($produto->descricao)
                                    <p class="card-text text-muted small">
                                        {{ Str::limit($produto->descricao, 100) }}
                                    </p>
                                @endif
                                
                                <div class="text-muted small mt-2">
                                    @if($produto->pivot->created_at)
                                        <i class="bi bi-calendar3 me-1"></i>
                                        Vinculado em {{ $produto->pivot->created_at->format('d/m/Y') }}
                                    @else
                                        <i class="bi bi-calendar3 me-1"></i>
                                        Data de vínculo não disponível
                                    @endif
                                </div>
                                
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#produtoModal{{ $produto->id }}">
                                        <i class="bi bi-eye me-1"></i>Ver Produto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Corretoras parceiras -->
@if($corretoras->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="modern-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="bi bi-people text-info me-2"></i>
                    Corretoras Parceiras
                    <span class="badge bg-info ms-2">{{ $seguradora->corretoras()->count() }}</span>
                </h5>
                @if($seguradora->corretoras()->count() > 10)
                    <small class="text-muted">Mostrando {{ $corretoras->count() }} de {{ $seguradora->corretoras()->count() }}</small>
                @endif
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nome da Corretora</th>
                            <th>Data da Parceria</th>
                            <th>Tempo de Parceria</th>
                            <th width="100">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($corretoras as $corretora)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                                            <i class="bi bi-person-badge text-info"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $corretora->nome }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($corretora->pivot->created_at)
                                        {{ $corretora->pivot->created_at->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($corretora->pivot->created_at)
                                        <span class="text-muted">{{ $corretora->pivot->created_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#corretoraModal{{ $corretora->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação das corretoras -->
            @if($corretoras->hasPages())
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando {{ $corretoras->firstItem() }} a {{ $corretoras->lastItem() }} 
                            de {{ $corretoras->total() }} corretoras
                        </div>
                        {{ $corretoras->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Cotações recentes -->
@if($cotacoes->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="modern-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-text text-warning me-2"></i>
                    Cotações Recentes
                    <span class="badge bg-warning ms-2">{{ $seguradora->cotacoes()->count() }}</span>
                </h5>
                @if($seguradora->cotacoes()->count() > 10)
                    <small class="text-muted">Mostrando as 10 mais recentes</small>
                @endif
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Corretora</th>
                            <th>Produto</th>
                            <th>Segurado</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cotacoes as $cotacao)
                            <tr>
                                <td>#{{ $cotacao->id }}</td>
                                <td>{{ $cotacao->corretora->nome ?? 'N/A' }}</td>
                                <td>{{ $cotacao->produto->nome ?? 'N/A' }}</td>
                                <td>{{ $cotacao->segurado->nome ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $cotacao->status == 'aprovada' ? 'success' : ($cotacao->status == 'pendente' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($cotacao->status) }}
                                    </span>
                                </td>
                                <td>{{ $cotacao->created_at->format('d/m/Y') }}</td>
                                <td>
                                    {{-- Temporário: botão desabilitado até criar show de cotações --}}
                                    <button class="btn btn-sm btn-outline-primary" disabled>
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    {{-- 
                                    <a href="{{ route('cotacoes.show', $cotacao) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($seguradora->cotacoes()->count() > 10)
                <div class="mt-3 text-center">
                    {{-- Temporário: link desabilitado até criar filtro de cotações --}}
                    <button class="btn btn-outline-primary" disabled>
                        <i class="bi bi-eye me-2"></i>Ver Todas as Cotações
                    </button>
                    {{-- 
                    <a href="{{ route('cotacoes.index') }}?seguradora_id={{ $seguradora->id }}" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-eye me-2"></i>Ver Todas as Cotações
                    </a>
                    --}}
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Modais para resumo dos produtos -->
@foreach($seguradora->produtos as $produto)
<div class="modal fade" id="produtoModal{{ $produto->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam text-primary me-2"></i>
                    {{ $produto->nome }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <strong>Nome do Produto:</strong>
                        <p class="mb-2">{{ $produto->nome }}</p>
                    </div>
                    
                    @if($produto->linha)
                        <div class="col-md-6">
                            <strong>Linha:</strong>
                            <p class="mb-2">
                                <span class="badge bg-primary">{{ $produto->linha }}</span>
                            </p>
                        </div>
                    @endif
                    
                    <div class="col-md-6">
                        <strong>Vinculado em:</strong>
                        <p class="mb-2">
                            @if($produto->pivot->created_at)
                                {{ $produto->pivot->created_at->format('d/m/Y') }}
                                <br><small class="text-muted">{{ $produto->pivot->created_at->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">Data não disponível</span>
                            @endif
                        </p>
                    </div>
                    
                    @if($produto->descricao)
                        <div class="col-12">
                            <strong>Descrição:</strong>
                            <p class="mb-2 text-muted">{{ $produto->descricao }}</p>
                        </div>
                    @endif
                    
                    <div class="col-12">
                        <hr>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-success">
                                    <i class="bi bi-building fs-4"></i>
                                    <div class="fw-bold">{{ $produto->seguradoras()->count() }}</div>
                                    <small class="text-muted">Seguradoras</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-warning">
                                    <i class="bi bi-file-earmark-text fs-4"></i>
                                    <div class="fw-bold">{{ $produto->cotacoes()->count() }}</div>
                                    <small class="text-muted">Cotações</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-info">
                                    <i class="bi bi-calendar3 fs-4"></i>
                                    <div class="fw-bold">{{ $produto->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">Criado</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($produto->seguradoras()->count() > 1)
                        <div class="col-12">
                            <strong>Outras seguradoras que oferecem:</strong>
                            <div class="mt-2">
                                @foreach($produto->seguradoras()->where('seguradoras.id', '!=', $seguradora->id)->get() as $outraSeguradora)
                                    <span class="badge bg-secondary me-1">{{ $outraSeguradora->nome }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="{{ route('produtos.show', $produto) }}" class="btn btn-primary">
                    <i class="bi bi-eye me-2"></i>Ver Completo
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Modais para resumo das corretoras -->
@foreach($corretoras as $corretora)
<div class="modal fade" id="corretoraModal{{ $corretora->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-badge text-info me-2"></i>
                    {{ $corretora->nome }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <strong>Nome:</strong>
                        <p class="mb-2">{{ $corretora->nome }}</p>
                    </div>
                    
                    @if($corretora->cnpj)
                        <div class="col-md-6">
                            <strong>CNPJ:</strong>
                            <p class="mb-2">{{ $corretora->cnpj }}</p>
                        </div>
                    @endif
                    
                    @if($corretora->telefone)
                        <div class="col-md-6">
                            <strong>Telefone:</strong>
                            <p class="mb-2">{{ $corretora->telefone }}</p>
                        </div>
                    @endif
                    
                    @if($corretora->email)
                        <div class="col-md-6">
                            <strong>Email:</strong>
                            <p class="mb-2">
                                <a href="mailto:{{ $corretora->email }}">{{ $corretora->email }}</a>
                            </p>
                        </div>
                    @endif
                    
                    @if($corretora->endereco)
                        <div class="col-md-6">
                            <strong>Endereço:</strong>
                            <p class="mb-2">{{ $corretora->endereco }}</p>
                        </div>
                    @endif
                    
                    @if($corretora->cidade)
                        <div class="col-md-6">
                            <strong>Cidade:</strong>
                            <p class="mb-2">{{ $corretora->cidade }}</p>
                        </div>
                    @endif
                    
                    @if($corretora->estado)
                        <div class="col-md-6">
                            <strong>Estado:</strong>
                            <p class="mb-2">{{ $corretora->estado }}</p>
                        </div>
                    @endif
                    
                    <div class="col-12">
                        <hr>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-primary">
                                    <i class="bi bi-building fs-4"></i>
                                    <div class="fw-bold">{{ $corretora->seguradoras()->count() }}</div>
                                    <small class="text-muted">Seguradoras</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-success">
                                    <i class="bi bi-file-earmark-text fs-4"></i>
                                    <div class="fw-bold">{{ $corretora->cotacoes()->count() }}</div>
                                    <small class="text-muted">Cotações</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-info">
                                    <i class="bi bi-calendar3 fs-4"></i>
                                    <div class="fw-bold">
                                        @if($corretora->pivot->created_at)
                                            {{ $corretora->pivot->created_at->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                    <small class="text-muted">Parceria</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($corretora->observacoes)
                        <div class="col-12">
                            <strong>Observações:</strong>
                            <p class="mb-2 text-muted">{{ $corretora->observacoes }}</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                {{-- Quando criarmos CRUD de corretoras, descomente: --}}
                {{-- 
                <a href="{{ route('corretoras.show', $corretora) }}" class="btn btn-primary">
                    <i class="bi bi-eye me-2"></i>Ver Completo
                </a>
                --}}
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a seguradora <strong>{{ $seguradora->nome }}</strong>?</p>
                <p class="text-danger small mb-0">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('seguradoras.destroy', $seguradora) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Sim, Excluir</button>
                </form>
            </div>
        </div>
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

.badge {
    font-weight: 500;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}
</style>

<script>
function confirmDelete() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endsection