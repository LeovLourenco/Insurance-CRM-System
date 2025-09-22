@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $produto->nome }}</h1>
        <p class="text-muted mb-0">Detalhes do produto</p>
    </div>
    <div class="d-flex gap-2">
        @can('update', $produto)
            <a href="{{ route('produtos.edit', $produto) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-2"></i>Editar
            </a>
        @endcan
        <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>
</div>

<!-- Informações do Produto -->
<div class="row g-4">
    <!-- Card Principal -->
    <div class="col-lg-8">
        <div class="modern-card p-4">
            <div class="row g-4">
                <!-- Nome -->
                <div class="col-12">
                    <h4 class="border-bottom pb-3 mb-3">
                        <i class="bi bi-box-seam text-primary me-2"></i>
                        Informações do Produto
                    </h4>
                </div>
                
                <div class="col-md-8">
                    <label class="form-label fw-bold text-muted">Nome do Produto</label>
                    <p class="fs-5 mb-0">{{ $produto->nome }}</p>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted">Linha</label>
                    @if($produto->linha)
                        <p class="mb-0">
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                {{ $produto->linha }}
                            </span>
                        </p>
                    @else
                        <p class="text-muted mb-0">Não informada</p>
                    @endif
                </div>

                @if($produto->descricao)
                <div class="col-12">
                    <label class="form-label fw-bold text-muted">Descrição</label>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $produto->descricao }}</p>
                    </div>
                </div>
                @endif

                <!-- Datas -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Criado em</label>
                    <p class="mb-0">
                        <i class="bi bi-calendar3 text-muted me-2"></i>
                        {{ $produto->created_at->format('d/m/Y') }} às {{ $produto->created_at->format('H:i') }}
                    </p>
                    <small class="text-muted">{{ $produto->created_at->diffForHumans() }}</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Última atualização</label>
                    <p class="mb-0">
                        <i class="bi bi-clock text-muted me-2"></i>
                        {{ $produto->updated_at->format('d/m/Y') }} às {{ $produto->updated_at->format('H:i') }}
                    </p>
                    <small class="text-muted">{{ $produto->updated_at->diffForHumans() }}</small>
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
                    <i class="bi bi-building text-info me-2"></i>
                    <span>Seguradoras</span>
                </div>
                <span class="badge bg-info fs-6">{{ $produto->seguradoras->count() }}</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <i class="bi bi-file-earmark-text text-warning me-2"></i>
                    <span>Cotações</span>
                </div>
                <span class="badge bg-warning fs-6">{{ $produto->cotacoes->count() }}</span>
            </div>
        </div>

        <!-- Card de Ações -->
        @unless(auth()->user()->hasRole('comercial'))
        <div class="modern-card p-4 mt-4">
            <h6 class="mb-3">
                <i class="bi bi-lightning text-warning me-2"></i>
                Ações Rápidas
            </h6>
            
            <div class="d-grid gap-2">
                <a href="{{ route('produtos.edit', $produto) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-2"></i>Editar Produto
                </a>
                
                @if($produto->seguradoras->count() == 0)
                    <button class="btn btn-outline-danger btn-sm" 
                            onclick="confirmDelete()">
                        <i class="bi bi-trash me-2"></i>Excluir Produto
                    </button>
                @else
                    <button class="btn btn-outline-secondary btn-sm" 
                            disabled 
                            title="Produto possui seguradoras vinculadas">
                        <i class="bi bi-shield-check me-2"></i>Produto em Uso
                    </button>
                @endif
            </div>
        </div>
        @endunless
    </div>
</div>

<!-- Seguradoras que oferecem este produto -->
@if($produto->seguradoras->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="modern-card p-4">
            <h5 class="border-bottom pb-3 mb-4">
                <i class="bi bi-building text-primary me-2"></i>
                Seguradoras que oferecem este produto
                <span class="badge bg-primary ms-2">{{ $produto->seguradoras->count() }}</span>
            </h5>
            
            <div class="row g-3">
                @foreach($produto->seguradoras as $seguradora)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title mb-2">{{ $seguradora->nome }}</h6>
                                <div class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    Vinculado em {{ $seguradora->pivot->created_at->format('d/m/Y') }}
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $seguradora->pivot->created_at->diffForHumans() }}
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

<!-- Cotações recentes -->
@if($produto->cotacoes->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="modern-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-text text-success me-2"></i>
                    Cotações Recentes
                    <span class="badge bg-success ms-2">{{ $produto->cotacoes->count() }}</span>
                </h5>
                @if($produto->cotacoes->count() > 5)
                    <small class="text-muted">Mostrando as 5 mais recentes</small>
                @endif
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Corretora</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produto->cotacoes->take(5) as $cotacao)
                            <tr>
                                <td>#{{ $cotacao->id }}</td>
                                <td>{{ $cotacao->corretora->nome ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $cotacao->status == 'aprovada' ? 'success' : ($cotacao->status == 'pendente' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($cotacao->status) }}
                                    </span>
                                </td>
                                <td>{{ $cotacao->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('cotacoes.show', $cotacao) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o produto <strong>{{ $produto->nome }}</strong>?</p>
                <p class="text-danger small mb-0">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('produtos.destroy', $produto) }}" method="POST" class="d-inline">
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