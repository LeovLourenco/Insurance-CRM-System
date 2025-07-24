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
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status:</label>
                        <select class="form-select" id="filtro-status">
                            <option value="">Todos os status</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="parcialmente_aprovada">Parcialmente Aprovada</option>
                            <option value="finalizada_com_aprovacao">Finalizada com Aprovação</option>
                            <option value="finalizada_sem_aprovacao">Finalizada sem Aprovação</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Corretora:</label>
                        <select class="form-select" id="filtro-corretora">
                            <option value="">Todas as corretoras</option>
                            @foreach(\App\Models\Corretora::all() as $corretora)
                                <option value="{{ $corretora->id }}">{{ $corretora->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Produto:</label>
                        <select class="form-select" id="filtro-produto">
                            <option value="">Todos os produtos</option>
                            @foreach(\App\Models\Produto::all() as $produto)
                                <option value="{{ $produto->id }}">{{ $produto->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Buscar:</label>
                        <input type="text" class="form-control" id="filtro-busca" placeholder="ID, nome do segurado...">
                    </div>
                </div>
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
                        <h4 class="mb-0 text-primary">{{ $cotacoes->count() }}</h4>
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
                        <h4 class="mb-0 text-warning">{{ $cotacoes->where('status_consolidado', 'em_andamento')->count() }}</h4>
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
                        <h6 class="mb-0">Com Aprovação</h6>
                        <h4 class="mb-0 text-success">{{ $cotacoes->where('status_consolidado', 'finalizada_com_aprovacao')->count() }}</h4>
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
                        <h6 class="mb-0">Taxa Média</h6>
                        <h4 class="mb-0 text-info">{{ number_format($cotacoes->avg('porcentagem_aprovacao'), 1) }}%</h4>
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
                        <tr data-cotacao-id="{{ $cotacao->id }}">
                            <td>
                                <span class="fw-bold text-primary">#{{ $cotacao->id }}</span>
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
                                    <span class="me-2">{{ $cotacao->cotacaoSeguradoras->count() }}</span>
                                    <div class="progress" style="width: 60px; height: 6px;">
                                        @php
                                            $aprovadas = $cotacao->cotacaoSeguradoras->where('status', 'aprovada')->count();
                                            $total = $cotacao->cotacaoSeguradoras->count();
                                            $porcentagem = $total > 0 ? ($aprovadas / $total) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar bg-success" style="width: {{ $porcentagem }}%"></div>
                                    </div>
                                </div>
                                <small class="text-muted">{{ $aprovadas }}/{{ $total }} aprovadas</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $cotacao->status == 'cancelada' ? 'danger' : ($cotacao->status == 'finalizada' ? 'success' : 'primary') }}">
                                {{ ucfirst(str_replace('_', ' ', $cotacao->status)) }}
                            </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <strong class="text-{{ $cotacao->porcentagem_aprovacao > 0 ? 'success' : 'muted' }}">
                                        {{ $cotacao->porcentagem_aprovacao }}%
                                    </strong>
                                    @if($cotacao->melhor_proposta)
                                        <div class="ms-2">
                                            <small class="text-muted">
                                                R$ {{ number_format($cotacao->melhor_proposta->valor_premio, 2, ',', '.') }}
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
                                            <a class="dropdown-item" href="{{ route('cotacoes.show', $cotacao) }}">
                                                <i class="bi bi-eye me-2"></i>Visualizar
                                            </a>
                                        </li>
                                        @if($cotacao->isPendente())
                                            <li>
                                                <button class="dropdown-item" onclick="enviarTodas({{ $cotacao->id }})">
                                                    <i class="bi bi-send me-2"></i>Enviar Todas
                                                </button>
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
                                                        <span class="badge bg-{{ $cs->status_classe }}">
                                                            {{ $cs->status_formatado }}
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
                                    <p>Clique no botão "Nova Cotação" para começar.</p>
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
                        Mostrando {{ $cotacoes->count() }} cotações
                    </span>
                    <div>
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
            
            // Toggle Bootstrap collapse
            const bsCollapse = new bootstrap.Collapse(detalhesRow, {
                toggle: true
            });
        });
    });
});

// Filtros em tempo real
document.getElementById('filtro-busca').addEventListener('input', function() {
    filtrarTabela();
});

document.getElementById('filtro-status').addEventListener('change', function() {
    filtrarTabela();
});

document.getElementById('filtro-corretora').addEventListener('change', function() {
    filtrarTabela();
});

function filtrarTabela() {
    const busca = document.getElementById('filtro-busca').value.toLowerCase();
    const status = document.getElementById('filtro-status').value;
    const corretora = document.getElementById('filtro-corretora').value;
    
    const rows = document.querySelectorAll('tbody tr[data-cotacao-id]');
    
    rows.forEach(row => {
        const texto = row.textContent.toLowerCase();
        const statusRow = row.querySelector('.badge').textContent.toLowerCase();
        
        let mostrar = true;
        
        if (busca && !texto.includes(busca)) {
            mostrar = false;
        }
        
        if (status && !statusRow.includes(status.replace('_', ' '))) {
            mostrar = false;
        }
        
        // Esconder a linha de detalhes também
        const cotacaoId = row.dataset.cotacaoId;
        const detalhesRow = document.getElementById(`detalhes-${cotacaoId}`);
        
        if (mostrar) {
            row.style.display = '';
            if (detalhesRow) detalhesRow.style.display = '';
        } else {
            row.style.display = 'none';
            if (detalhesRow) detalhesRow.style.display = 'none';
        }
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
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao enviar cotações');
    });
}

// Exportar dados
function exportarTodos() {
    window.open('/cotacoes/relatorio?formato=excel', '_blank');
}
</script>
@endpush
@endsection