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
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportarTodos()">
                <i class="bi bi-download me-1"></i>Exportar
            </button>
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
                                <option value="em_andamento" {{ request('status_geral') == 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                                <option value="finalizada" {{ request('status_geral') == 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                                <option value="cancelada" {{ request('status_geral') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status Seguradoras:</label>
                            <select class="form-select" name="status_consolidado" id="filtro-status-consolidado">
                                <option value="">Todos</option>
                                <option value="aguardando" {{ request('status_consolidado') == 'aguardando' ? 'selected' : '' }}>Aguardando Resposta</option>
                                <option value="em_analise" {{ request('status_consolidado') == 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                                <option value="aprovada" {{ request('status_consolidado') == 'aprovada' ? 'selected' : '' }}>Aprovada</option>
                                <option value="rejeitada" {{ request('status_consolidado') == 'rejeitada' ? 'selected' : '' }}>Rejeitada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Corretora:</label>
                            <select class="form-select" name="corretora_id" id="filtro-corretora">
                                <option value="">Todas as corretoras</option>
                                @foreach(\App\Models\Corretora::all() as $corretora)
                                    <option value="{{ $corretora->id }}" {{ request('corretora_id') == $corretora->id ? 'selected' : '' }}>
                                        {{ $corretora->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Buscar:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="busca" id="filtro-busca" 
                                       value="{{ request('busca') }}" placeholder="ID, nome do segurado...">
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
                        <th>Quick Actions</th>
                        <th>Menu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cotacoes as $cotacao)
                        <tr data-cotacao-id="{{ $cotacao->id }}" class="cotacao-row">
                            <td onclick="toggleDetalhes({{ $cotacao->id }})" style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-chevron-right text-muted me-2 expand-icon" id="icon-{{ $cotacao->id }}"></i>
                                    <span class="fw-bold text-primary">#{{ $cotacao->id }}</span>
                                </div>
                            </td>
                            <td onclick="toggleDetalhes({{ $cotacao->id }})" style="cursor: pointer;">
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
                            <td onclick="toggleDetalhes({{ $cotacao->id }})" style="cursor: pointer;">
                                <div class="fw-medium">{{ $cotacao->corretora->nome ?? 'Corretora não encontrada' }}</div>
                                <small class="text-muted">{{ $cotacao->corretora->codigo ?? '' }}</small>
                            </td>
                            <td onclick="toggleDetalhes({{ $cotacao->id }})" style="cursor: pointer;">
                                <span class="badge bg-light text-dark">{{ $cotacao->produto->nome ?? 'Produto não encontrado' }}</span>
                            </td>
                            <td onclick="toggleDetalhes({{ $cotacao->id }})" style="cursor: pointer;">
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
                            <td onclick="toggleDetalhes({{ $cotacao->id }})" style="cursor: pointer;">
                                <!-- Status apenas informativo -->
                                <div class="status-container">
                                    @include('cotacoes.partials.status', ['cotacao' => $cotacao, 'tipo' => 'simples'])
                                    @if($cotacao->status === 'em_andamento')
                                        <small class="text-muted d-block">{{ $cotacao->status_consolidado_formatado }}</small>
                                    @endif
                                </div>
                            </td>
                            <td onclick="toggleDetalhes({{ $cotacao->id }})" style="cursor: pointer;">
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
                            <td onclick="toggleDetalhes({{ $cotacao->id }})" style="cursor: pointer;">
                                <div>{{ $cotacao->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $cotacao->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <!-- Quick Actions - Apenas ações seguras -->
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" 
                                            onclick="adicionarComentario({{ $cotacao->id }})"
                                            title="Adicionar comentário rápido">
                                        <i class="bi bi-chat-dots"></i>
                                    </button>
                                    
                                    <button class="btn btn-outline-info" 
                                            onclick="window.location.href='{{ route('cotacoes.show', $cotacao->id) }}'"
                                            title="Ver detalhes e gerenciar workflow">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    @if($cotacao->pode_enviar && $cotacao->status === 'em_andamento')
                                        <button class="btn btn-outline-success" 
                                                onclick="marcarComoEnviada({{ $cotacao->id }})"
                                                title="Marcar como enviada (requer confirmação)">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <!-- Menu de ações secundárias -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><h6 class="dropdown-header">Visualizar</h6></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('cotacoes.show', $cotacao->id) }}">
                                                <i class="bi bi-eye me-2"></i>Detalhes Completos
                                            </a>
                                        </li>
                                        
                                        @if($cotacao->status === 'em_andamento')
                                            <li><hr class="dropdown-divider"></li>
                                            <li><h6 class="dropdown-header">Workflow Avançado</h6></li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('cotacoes.show', $cotacao->id) }}">
                                                    <i class="bi bi-gear me-2"></i>Gerenciar Workflow
                                                </a>
                                            </li>
                                        @endif
                                        
                                        @if($cotacao->pode_editar)
                                            <li><hr class="dropdown-divider"></li>
                                            <li><h6 class="dropdown-header">Correções</h6></li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('cotacoes.edit', $cotacao->id) }}">
                                                    <i class="bi bi-pencil me-2"></i>Corrigir Dados
                                                </a>
                                            </li>
                                        @endif
                                        
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Exportar</h6></li>
                                        <li>
                                            <button class="dropdown-item" onclick="exportarCotacao({{ $cotacao->id }})">
                                                <i class="bi bi-download me-2"></i>PDF desta Cotação
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- Linha expandível com detalhes das seguradoras -->
                        <tr class="collapse" id="detalhes-{{ $cotacao->id }}">
                            <td colspan="10" class="bg-light">
                                <div class="p-3">
                                    <h6>Detalhes por Seguradora:</h6>
                                    <div class="row">
                                        @foreach($cotacao->cotacaoSeguradoras as $cs)
                                            <div class="col-md-4 mb-2">
                                                <div class="border rounded p-2 bg-white">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
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
                                                        
                                                        <!-- Status apenas informativo -->
                                                        <span class="badge bg-{{ $statusClasses[$cs->status] ?? 'secondary' }}">
                                                            {{ $statusTextos[$cs->status] ?? ucfirst($cs->status) }}
                                                        </span>
                                                    </div>
                                                    
                                                    <!-- Quick actions por seguradora -->
                                                    <div class="btn-group btn-group-sm mb-2 w-100">
                                                        @if($cs->status === 'aguardando')
                                                            <button class="btn btn-outline-success btn-sm" 
                                                                    onclick="marcarSeguradoraEnviada({{ $cs->id }})">
                                                                <i class="bi bi-send"></i> Enviar
                                                            </button>
                                                        @endif
                                                        <button class="btn btn-outline-primary btn-sm" 
                                                                onclick="editarSeguradora({{ $cs->id }})">
                                                            <i class="bi bi-pencil"></i> Editar
                                                        </button>
                                                    </div>
                                                    
                                                    @if($cs->data_envio)
                                                        <small class="text-muted d-block">
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
                                                            <small class="text-muted">{{ Str::limit($cs->observacoes, 50) }}</small>
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
                            <td colspan="10" class="text-center py-5">
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

<!-- Modal para comentário rápido -->
<div class="modal fade" id="modalComentario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Comentário Rápido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea class="form-control" id="comentarioTexto" rows="3" placeholder="Digite seu comentário..."></textarea>
                <input type="hidden" id="comentarioCotacaoId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarComentario()">Salvar</button>
            </div>
        </div>
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

.table tbody tr.cotacao-row:hover {
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

.status-container {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.btn-group-sm .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.dropdown-header {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
</style>
@endpush

@push('scripts')
<script>
// Toggle detalhes
function toggleDetalhes(cotacaoId) {
    const detalhesRow = document.getElementById(`detalhes-${cotacaoId}`);
    const icon = document.getElementById(`icon-${cotacaoId}`);
    
    // Toggle Bootstrap collapse
    const bsCollapse = new bootstrap.Collapse(detalhesRow, { toggle: true });
    
    // Mudar ícone
    if (detalhesRow.classList.contains('show')) {
        icon.className = 'bi bi-chevron-right text-muted me-2 expand-icon';
    } else {
        icon.className = 'bi bi-chevron-down text-muted me-2 expand-icon';
    }
}

// Toast helper
function showToast(message, type = 'success') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
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
    
    const toast = new bootstrap.Toast(toastContainer.lastElementChild);
    toast.show();
    
    toastContainer.lastElementChild.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Marcar como enviada (novo nome do botão)
function marcarComoEnviada(cotacaoId) {
    if (!confirm('Marcar cotação como enviada para todas as seguradoras pendentes?')) {
        return;
    }
    
    fetch(`/cotacoes/${cotacaoId}/marcar-enviada`, {
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
        showToast('Erro ao marcar cotação como enviada', 'danger');
    });
}

// Adicionar comentário rápido
function adicionarComentario(cotacaoId) {
    document.getElementById('comentarioCotacaoId').value = cotacaoId;
    document.getElementById('comentarioTexto').value = '';
    new bootstrap.Modal(document.getElementById('modalComentario')).show();
}

// Salvar comentário
function salvarComentario() {
    const cotacaoId = document.getElementById('comentarioCotacaoId').value;
    const comentario = document.getElementById('comentarioTexto').value.trim();
    
    if (!comentario) {
        showToast('Digite um comentário', 'warning');
        return;
    }
    
    fetch(`/cotacoes/${cotacaoId}/comentario`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ comentario: comentario })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Comentário adicionado', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalComentario')).hide();
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao salvar comentário', 'danger');
    });
}

// Atualizar status da cotação
document.addEventListener('DOMContentLoaded', function() {
    const statusSelects = document.querySelectorAll('.status-select');
    
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const cotacaoId = this.dataset.cotacaoId;
            const novoStatus = this.value;
            const originalValue = this.dataset.originalValue;
            
            if (novoStatus === originalValue) return;
            
            let confirmMessage = '';
            switch(novoStatus) {
                case 'finalizada':
                    confirmMessage = 'Finalizar esta cotação? Esta ação não pode ser desfeita.';
                    break;
                case 'cancelada':
                    confirmMessage = 'Cancelar esta cotação? Esta ação não pode ser desfeita.';
                    break;
                default:
                    confirmMessage = `Alterar status para "${novoStatus}"?`;
            }
            
            if (!confirm(confirmMessage)) {
                this.value = originalValue;
                return;
            }
            
            fetch(`/cotacoes/${cotacaoId}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: novoStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    this.dataset.originalValue = novoStatus;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.message, 'danger');
                    this.value = originalValue;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro ao atualizar status', 'danger');
                this.value = originalValue;
            });
        });
    });
});

// Atualizar status da seguradora
document.addEventListener('DOMContentLoaded', function() {
    const statusSeguradoraSelects = document.querySelectorAll('.status-seguradora-select');
    
    statusSeguradoraSelects.forEach(select => {
        const originalValue = select.value;
        
        select.addEventListener('change', function() {
            const csId = this.dataset.csId;
            const novoStatus = this.value;
            
            fetch(`/cotacao-seguradoras/${csId}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: novoStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'danger');
                    this.value = originalValue;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro ao atualizar status da seguradora', 'danger');
                this.value = originalValue;
            });
        });
    });
});

// Marcar seguradora como enviada
function marcarSeguradoraEnviada(csId) {
    if (!confirm('Marcar como enviada para esta seguradora?')) {
        return;
    }
    
    fetch(`/cotacao-seguradoras/${csId}/marcar-enviada`, {
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
        showToast('Erro ao marcar como enviada', 'danger');
    });
}

// Editar seguradora (vai para página específica)
function editarSeguradora(csId) {
    window.location.href = `/cotacao-seguradoras/${csId}/edit`;
}

// Exportar cotação específica
function exportarCotacao(cotacaoId) {
    window.open(`/cotacoes/${cotacaoId}/pdf`, '_blank');
}

// Limpar filtros
function limparFiltros() {
    document.getElementById('form-filtros').reset();
    window.location.href = '{{ route('cotacoes.index') }}';
}

// Exportar todos
function exportarTodos() {
    const params = new URLSearchParams(window.location.search);
    const url = '/cotacoes/relatorio?formato=excel&' + params.toString();
    window.open(url, '_blank');
}

// Auto-submit formulário de filtros
document.addEventListener('DOMContentLoaded', function() {
    const filtros = document.querySelectorAll('#form-filtros select');
    
    filtros.forEach(filtro => {
        filtro.addEventListener('change', function() {
            document.getElementById('form-filtros').submit();
        });
    });
});
</script>
@endpush
@endsection