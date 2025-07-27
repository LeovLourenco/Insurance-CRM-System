@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header da página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>Cotações
            </h1>
            <p class="text-muted mb-0">Gerencie todas as cotações do sistema</p>
        </div>
        <div>
            <a href="{{ route('cotacoes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Nova Cotação
            </a>
        </div>
    </div>

    <!-- Filtros rápidos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="modern-card p-3">
                <form method="GET" action="{{ route('cotacoes.index') }}" id="form-filtros">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status Geral:</label>
                            <select class="form-select" name="status_geral" id="filtro-status">
                                <option value="">Todos os status</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="finalizada">Finalizada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status Seguradoras:</label>
                            <select class="form-select" name="status_consolidado" id="filtro-status-consolidado">
                                <option value="">Todos</option>
                                <option value="aguardando">Aguardando Resposta</option>
                                <option value="em_analise">Em Análise</option>
                                <option value="aprovada">Aprovada</option>
                                <option value="rejeitada">Rejeitada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Corretora:</label>
                            <select class="form-select" name="corretora_id" id="filtro-corretora">
                                <option value="">Todas as corretoras</option>
                                @foreach(\App\Models\Corretora::all() as $corretora)
                                    <option value="{{ $corretora->id }}">
                                        {{ $corretora->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Buscar:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="busca" id="filtro-busca" 
                                       placeholder="ID, nome do segurado...">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button class="btn btn-outline-danger" type="button" onclick="limparFiltros()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Métricas rápidas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="modern-card p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Total de Cotações</h6>
                        <h4 class="mb-0 text-primary">{{ $metricas['total'] ?? $cotacoes->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="modern-card p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-clock text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Em Andamento</h6>
                        <h4 class="mb-0 text-warning">{{ $metricas['em_andamento'] ?? $cotacoes->where('status', 'em_andamento')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="modern-card p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Finalizadas</h6>
                        <h4 class="mb-0 text-success">{{ $metricas['finalizadas'] ?? $cotacoes->where('status', 'finalizada')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="modern-card p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-percent text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Taxa de Sucesso</h6>
                        <h4 class="mb-0 text-info">{{ $metricas['taxa_sucesso'] ?? '0' }}%</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de cotações -->
    <div class="modern-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Segurado</th>
                        <th>Corretora</th>
                        <th>Produto</th>
                        <th>Seguradoras</th>
                        <th>Status</th>
                        <th>Aprovação</th>
                        <th>Criada em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cotacoes as $cotacao)
                        <tr data-cotacao-id="{{ $cotacao->id }}" class="cotacao-row" style="cursor: pointer;">
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-chevron-right text-muted me-2 expand-icon" id="icon-{{ $cotacao->id }}"></i>
                                    <span class="fw-bold text-primary">#{{ $cotacao->id }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2">
                                        {{ $cotacao->segurado ? substr($cotacao->segurado->nome, 0, 1) : '?' }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $cotacao->segurado->nome ?? 'Segurado não encontrado' }}</div>
                                        <small class="text-muted">{{ $cotacao->segurado->email ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $cotacao->corretora->nome ?? 'Corretora não encontrada' }}</div>
                                <small class="text-muted">{{ $cotacao->corretora->codigo ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $cotacao->produto->nome ?? 'Produto não encontrado' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @php $stats = $cotacao->getSeguradoraStats(); @endphp
                                    <span class="me-2">{{ $stats['total'] }}</span>
                                    <div class="progress" style="width: 60px; height: 6px;">
                                        @php
                                            $aprovadas = $stats['aprovadas'];
                                            $total = $stats['total'];
                                            $porcentagem = $total > 0 ? ($aprovadas / $total) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar bg-success" style="width: {{ $porcentagem }}%"></div>
                                    </div>
                                </div>
                                <small class="text-muted">{{ $aprovadas }}/{{ $total }} aprovadas</small>
                            </td>
                            <td>
                                @include('cotacoes.partials.status', ['cotacao' => $cotacao, 'tipo' => 'simples'])
                                @if($cotacao->status === 'em_andamento')
                                    <br><small class="text-muted">{{ $cotacao->status_consolidado_formatado }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @php
                                        $stats = $cotacao->getSeguradoraStats();
                                        $aprovadas = $stats['aprovadas'];
                                        $total = $stats['total'];
                                        $porcentagemAprovacao = $total > 0 ? round(($aprovadas / $total) * 100, 1) : 0;
                                        
                                        // Buscar melhor proposta usando accessor
                                        $melhorProposta = $cotacao->getMelhorProposta();
                                    @endphp
                                    <strong class="text-{{ $porcentagemAprovacao > 0 ? 'success' : 'muted' }}">
                                        {{ $porcentagemAprovacao }}%
                                    </strong>
                                    @if($melhorProposta)
                                        <div class="ms-2">
                                            <small class="text-muted">
                                                R$ {{ number_format($melhorProposta->valor_premio, 2, ',', '.') }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div>{{ $cotacao->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $cotacao->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('cotacoes.show', $cotacao->id) }}">
                                                <i class="bi bi-eye me-2"></i>Visualizar
                                            </a>
                                        </li>
                                        @if($cotacao->pode_enviar)
                                            <li>
                                                <button class="dropdown-item" onclick="enviarTodas({{ $cotacao->id }})">
                                                    <i class="bi bi-send me-2"></i>Enviar Todas
                                                </button>
                                            </li>
                                        @endif
                                        @if($cotacao->pode_editar)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('cotacoes.edit', $cotacao->id) }}">
                                                    <i class="bi bi-pencil me-2"></i>Editar
                                                </a>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-primary" href="#">
                                                <i class="bi bi-download me-2"></i>Exportar
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- Linha expandível com detalhes das seguradoras -->
                        <tr class="collapse" id="detalhes-{{ $cotacao->id }}">
                            <td colspan="9" class="bg-light">
                                <div class="p-3">
                                    <h6>Detalhes por Seguradora:</h6>
                                    <div class="row">
                                        @foreach($cotacao->cotacaoSeguradoras as $cs)
                                            <div class="col-md-4 mb-2">
                                                <div class="border rounded p-2 bg-white">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong>{{ $cs->seguradora->nome }}</strong>
                                                        @php
                                                            $statusClasses = [
                                                                'aguardando' => 'warning',
                                                                'em_analise' => 'info',
                                                                'aprovada' => 'success',
                                                                'rejeitada' => 'danger',
                                                                'repique' => 'warning'
                                                            ];
                                                            $statusTextos = [
                                                                'aguardando' => 'Aguardando',
                                                                'em_analise' => 'Em Análise',
                                                                'aprovada' => 'Aprovada',
                                                                'rejeitada' => 'Rejeitada',
                                                                'repique' => 'Repique'
                                                            ];
                                                        @endphp
                                                        <span class="badge bg-{{ $statusClasses[$cs->status] ?? 'secondary' }}">
                                                            {{ $statusTextos[$cs->status] ?? ucfirst($cs->status) }}
                                                        </span>
                                                    </div>
                                                    @if($cs->data_envio)
                                                        <small class="text-muted">
                                                            Enviado: {{ $cs->data_envio->format('d/m H:i') }}
                                                        </small>
                                                    @endif
                                                    @if($cs->valor_premio)
                                                        <div class="mt-1">
                                                            <small class="fw-bold text-success">
                                                                R$ {{ number_format($cs->valor_premio, 2, ',', '.') }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                    @if($cs->observacoes)
                                                        <div class="mt-1">
                                                            <small class="text-muted">{{ $cs->observacoes }}</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    <h5>Nenhuma cotação encontrada</h5>
                                    @if(request()->hasAny(['status_geral', 'status_consolidado', 'busca']))
                                        <p>Tente ajustar os filtros ou <a href="{{ route('cotacoes.index') }}">limpar a busca</a>.</p>
                                    @else
                                        <p>Clique no botão "Nova Cotação" para começar.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cotacoes->count() > 0)
            <div class="p-3 border-top bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $cotacoes->count() }} de {{ $cotacoes->total() }} cotações
                    </span>
                    <div class="d-flex gap-2">
                        {{ $cotacoes->links() }}
                        <button class="btn btn-sm btn-outline-primary" onclick="exportarTodos()">
                            <i class="bi bi-download me-1"></i>Exportar Todos
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.avatar {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}

.table tbody tr {
    cursor: pointer;
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modern-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    border: none;
}
</style>
@endpush

@push('scripts')
<script>
// Expandir/recolher detalhes
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('tbody tr[data-cotacao-id]');
    
    rows.forEach(row => {
        row.addEventListener('click', function(e) {
            // Não expandir se clicou em botão/dropdown
            if (e.target.closest('.dropdown') || e.target.closest('button')) {
                return;
            }
            
            const cotacaoId = this.dataset.cotacaoId;
            const detalhesRow = document.getElementById(`detalhes-${cotacaoId}`);
            const icon = document.getElementById(`icon-${cotacaoId}`);
            
            // Toggle Bootstrap collapse
            const bsCollapse = new bootstrap.Collapse(detalhesRow, {
                toggle: true
            });
            
            // Mudar ícone
            if (detalhesRow.classList.contains('show')) {
                icon.className = 'bi bi-chevron-right text-muted me-2 expand-icon';
            } else {
                icon.className = 'bi bi-chevron-down text-muted me-2 expand-icon';
            }
        });
    });
});

// Função para mostrar toast (substitui alert)
function showToast(message, type = 'success') {
    // Criar toast dinamicamente
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // Adicionar ao container de toasts
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        document.body.appendChild(toastContainer);
    }
    
    const toastElement = document.createElement('div');
    toastElement.innerHTML = toastHtml;
    toastContainer.appendChild(toastElement.firstElementChild);
    
    // Mostrar toast
    const toast = new bootstrap.Toast(toastContainer.lastElementChild);
    toast.show();
    
    // Remover elemento após esconder
    toastContainer.lastElementChild.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Enviar para todas as seguradoras
function enviarTodas(cotacaoId) {
    if (!confirm('Enviar cotação para todas as seguradoras pendentes?')) {
        return;
    }
    
    fetch(`/cotacoes/${cotacaoId}/enviar-todas`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao enviar cotações', 'danger');
    });
}

// Limpar filtros
function limparFiltros() {
    // Resetar o formulário
    document.getElementById('form-filtros').reset();
    
    // Redirecionar para página limpa
    window.location.href = '{{ route('cotacoes.index') }}';
}

// Exportar dados
function exportarTodos() {
    window.open('/cotacoes/relatorio?formato=excel', '_blank');
}
</script>
@endpush
@endsection