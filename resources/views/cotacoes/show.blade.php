@extends('layouts.app')

@section('title', 'Cotação #' . $cotacao->id)

@section('content')
<div class="container-fluid">
    {{-- Header Compacto com Actions Principais --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-file-earmark-text text-primary"></i> 
                Cotação #{{ $cotacao->id }}
                <span class="ms-2">
                    @include('cotacoes.partials.status', ['cotacao' => $cotacao, 'tipo' => 'badge'])
                </span>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('cotacoes.index') }}">Cotações</a></li>
                    <li class="breadcrumb-item active">Cotação #{{ $cotacao->id }}</li>
                </ol>
            </nav>
        </div>
        
        {{-- Actions Toolbar - Compacta e Contextual --}}
        <div class="actions-toolbar">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.history.back()">
                <i class="bi bi-arrow-left"></i> Voltar
            </button>
            
            @if($cotacao->status === 'em_andamento')
                {{-- Ações Principais --}}
                <button class="btn btn-primary btn-sm" onclick="adicionarComentarioGeral()">
                    <i class="bi bi-chat-dots"></i> Comentário Geral
                </button>
                
                @if($cotacao->pode_enviar)
                    <button class="btn btn-success btn-sm" onclick="marcarComoEnviada()">
                        <i class="bi bi-send"></i> Enviar Todas
                    </button>
                @endif
                
                {{-- Dropdown para Ações Secundárias --}}
                <div class="btn-group">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Status</h6></li>
                        <li><a class="dropdown-item" onclick="finalizarCotacao('finalizada')">
                            <i class="bi bi-check-circle text-success"></i> Finalizar Cotação
                        </a></li>
                        <li><a class="dropdown-item" onclick="finalizarCotacao('cancelada')">
                            <i class="bi bi-x-circle text-danger"></i> Cancelar Cotação
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Ações</h6></li>
                        <li><a class="dropdown-item" onclick="duplicarCotacao()">
                            <i class="bi bi-files"></i> Duplicar Cotação
                        </a></li>
                        <li><a class="dropdown-item" onclick="exportarPDF()">
                            <i class="bi bi-file-pdf"></i> Gerar PDF
                        </a></li>
                    </ul>
                </div>
            @else
                {{-- Cotação Finalizada - Ações Limitadas --}}
                <button class="btn btn-outline-primary btn-sm" onclick="duplicarCotacao()">
                    <i class="bi bi-files"></i> Duplicar
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="exportarPDF()">
                    <i class="bi bi-file-pdf"></i> PDF
                </button>
            @endif
        </div>
    </div>

    {{-- Métricas Compactas --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 col-6">
            <div class="metric-card bg-primary">
                <div class="metric-icon"><i class="bi bi-building"></i></div>
                <div class="metric-content">
                    <h4>{{ $cotacao->cotacaoSeguradoras->count() }}</h4>
                    <span>Seguradoras</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-6">
            <div class="metric-card bg-success">
                <div class="metric-icon"><i class="bi bi-check-circle"></i></div>
                <div class="metric-content">
                    <h4>{{ $cotacao->cotacaoSeguradoras->where('status', 'aprovada')->count() }}</h4>
                    <span>Aprovadas</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-6">
            <div class="metric-card bg-warning">
                <div class="metric-icon"><i class="bi bi-clock"></i></div>
                <div class="metric-content">
                    <h4>{{ $cotacao->cotacaoSeguradoras->whereIn('status', ['aguardando', 'em_analise'])->count() }}</h4>
                    <span>Pendentes</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-6">
            <div class="metric-card bg-info">
                <div class="metric-icon"><i class="bi bi-currency-dollar"></i></div>
                <div class="metric-content">
                    @php $melhorProposta = $cotacao->getMelhorProposta(); @endphp
                    <h4>{{ $melhorProposta ? 'R$ ' . number_format($melhorProposta->valor_premio, 0, ',', '.') : 'N/A' }}</h4>
                    <span>Melhor Oferta</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Coluna Principal --}}
        <div class="col-lg-8">
            {{-- Informações Gerais com Observações Estáticas --}}
            <div class="modern-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle text-primary"></i> Informações da Cotação
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Segurado</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2">
                                        {{ $cotacao->segurado ? substr($cotacao->segurado->nome, 0, 1) : '?' }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $cotacao->segurado->nome ?? 'N/A' }}</div>
                                        @if($cotacao->segurado && $cotacao->segurado->email)
                                            <small class="text-muted">{{ $cotacao->segurado->email }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Corretora</label>
                                <div class="fw-medium">{{ $cotacao->corretora->nome ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $cotacao->corretora->codigo ?? '' }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Produto</label>
                                <span class="badge bg-light text-dark fs-6">{{ $cotacao->produto->nome ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Criada em</label>
                                <div class="fw-medium">{{ $cotacao->created_at->format('d/m/Y H:i') }}</div>
                                <small class="text-muted">{{ $cotacao->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Observações Gerais Estáticas --}}
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="mb-0">Observações Gerais</label>
                            @if($cotacao->pode_editar)
                                <button class="btn btn-sm btn-outline-secondary" onclick="editarObservacoes()">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                            @endif
                        </div>
                        <div class="observacoes-container">
                            @if($cotacao->observacoes)
                                <div class="alert alert-light border">
                                    <i class="bi bi-file-text text-muted me-2"></i>
                                    {{ $cotacao->observacoes }}
                                </div>
                            @else
                                <div class="text-muted fst-italic p-3 bg-light rounded">
                                    <i class="bi bi-file-text me-2"></i> Nenhuma observação geral registrada
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gestão por Seguradora Otimizada --}}
            <div class="modern-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-buildings text-primary"></i> 
                        Seguradoras ({{ $cotacao->cotacaoSeguradoras->count() }})
                    </h5>
                    <div class="progress-indicator">
                        <small class="text-muted">{{ $cotacao->quantidade_respondida }}/{{ $cotacao->cotacaoSeguradoras->count() }} respondidas</small>
                        <div class="progress ms-2" style="width: 60px; height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $cotacao->percentual_resposta }}%;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($cotacao->cotacaoSeguradoras->count() > 0)
                        @foreach($cotacao->cotacaoSeguradoras as $cs)
                            <div class="seguradora-item border-bottom">
                                {{-- Header da Seguradora --}}
                                <div class="seguradora-header p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-5">
                                            <div class="d-flex align-items-center">
                                                <div class="seguradora-avatar me-3">
                                                    <i class="bi bi-building"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $cs->seguradora->nome }}</h6>
                                                    <div class="seguradora-meta">
                                                        @if($cs->data_envio)
                                                            <small class="text-muted">
                                                                <i class="bi bi-send"></i> {{ $cs->data_envio->format('d/m H:i') }}
                                                            </small>
                                                        @endif
                                                        @if($cs->valor_premio)
                                                            <span class="badge bg-success ms-2">
                                                                R$ {{ number_format($cs->valor_premio, 2, ',', '.') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select form-select-sm status-select" 
                                                    data-cs-id="{{ $cs->id }}">
                                                <option value="aguardando" {{ $cs->status === 'aguardando' ? 'selected' : '' }}>Aguardando</option>
                                                <option value="em_analise" {{ $cs->status === 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                                                <option value="aprovada" {{ $cs->status === 'aprovada' ? 'selected' : '' }}>Aprovada</option>
                                                <option value="rejeitada" {{ $cs->status === 'rejeitada' ? 'selected' : '' }}>Rejeitada</option>
                                                <option value="repique" {{ $cs->status === 'repique' ? 'selected' : '' }}>Repique</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="seguradora-actions">
                                                @if($cs->status === 'aguardando')
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            onclick="marcarSeguradoraEnviada({{ $cs->id }})">
                                                        <i class="bi bi-send"></i> Enviar
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="editarSeguradora({{ $cs->id }})">
                                                    <i class="bi bi-pencil"></i> Editar
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="toggleComentarios({{ $cs->id }})">
                                                    <i class="bi bi-chat-dots"></i> <span id="comment-count-{{ $cs->id }}">0</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Thread de Comentários por Seguradora --}}
                                <div class="comments-section collapse" id="comments-{{ $cs->id }}">
                                    <div class="comments-container p-3 bg-light border-top">
                                        <h6 class="mb-3">
                                            <i class="bi bi-chat-dots text-primary"></i> 
                                            Conversa com {{ $cs->seguradora->nome }}
                                        </h6>
                                        
                                        {{-- Lista de Comentários --}}
                                        <div class="comments-thread" id="thread-{{ $cs->id }}">
                                            {{-- Comentários serão carregados via AJAX --}}
                                            <div class="text-center text-muted py-2">
                                                <small>Carregando comentários...</small>
                                            </div>
                                        </div>
                                        
                                        {{-- Form para Novo Comentário --}}
                                        <div class="new-comment-form mt-3">
                                            <div class="input-group">
                                                <textarea class="form-control" 
                                                          id="comment-input-{{ $cs->id }}"
                                                          placeholder="Adicionar comentário sobre {{ $cs->seguradora->nome }}..."
                                                          rows="2"></textarea>
                                                <button class="btn btn-primary" 
                                                        onclick="adicionarComentario({{ $cs->id }})">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-buildings fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhuma seguradora associada</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar Mínima --}}
        <div class="col-lg-4">
            {{-- Timeline Limpa (Só Histórico) --}}
            <div class="modern-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history text-primary"></i> Histórico da Cotação
                    </h5>
                </div>
                <div class="card-body">
                    @if($cotacao->atividades->count() > 0)
                        <div class="timeline">
                            @foreach($cotacao->atividades->sortByDesc('created_at')->take(10) as $atividade)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-{{ $atividade->tipo === 'geral' ? 'primary' : 'info' }}"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <small class="text-muted">
                                                {{ $atividade->created_at->format('d/m H:i') }}
                                                @if($atividade->user)
                                                    - {{ $atividade->user->name }}
                                                @endif
                                            </small>
                                        </div>
                                        <div class="timeline-body">
                                            {{ $atividade->descricao }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($cotacao->atividades->count() > 10)
                            <div class="text-center mt-3">
                                <button class="btn btn-sm btn-outline-secondary" onclick="carregarMaisAtividades()">
                                    <i class="bi bi-chevron-down"></i> Ver mais atividades
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clock fs-2 text-muted mb-2"></i>
                            <p class="text-muted mb-0">Nenhuma atividade registrada</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Resumo Estatístico Compacto --}}
            <div class="modern-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up text-primary"></i> Resumo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">{{ round($cotacao->percentual_resposta) }}%</div>
                            <div class="stat-label">Taxa de Resposta</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value text-success">
                                {{ $melhorProposta ? 'R$ ' . number_format($melhorProposta->valor_premio, 0, ',', '.') : 'N/A' }}
                            </div>
                            <div class="stat-label">Melhor Oferta</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ $cotacao->tempo_medio_resposta ?? 'N/A' }}</div>
                            <div class="stat-label">Tempo Médio</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">
                                @include('cotacoes.partials.status', ['cotacao' => $cotacao, 'tipo' => 'simples'])
                            </div>
                            <div class="stat-label">Status</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal para Comentário Geral --}}
<div class="modal fade" id="modalComentarioGeral" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Comentário Geral da Cotação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    <i class="bi bi-info-circle"></i> 
                    Este comentário será registrado no histórico geral da cotação.
                </p>
                <textarea class="form-control" id="comentarioGeral" rows="4" 
                          placeholder="Digite seu comentário sobre a cotação em geral..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarComentarioGeral()">
                    <i class="bi bi-check-circle"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal para Editar Seguradora (Simplificado) --}}
<div class="modal fade" id="modalEditarSeguradora" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Seguradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarSeguradora">
                    <input type="hidden" id="csId">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Valor do Prêmio</label>
                                <input type="number" class="form-control" id="modalValorPremio" 
                                       step="0.01" min="0" placeholder="0,00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Valor IS</label>
                                <input type="number" class="form-control" id="modalValorIs" 
                                       step="0.01" min="0" placeholder="0,00">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Data de Retorno</label>
                        <input type="datetime-local" class="form-control" id="modalDataRetorno">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarSeguradora()">
                    <i class="bi bi-check-circle"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Modern Card */
.modern-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    border: none;
}

.card-header {
    background: rgba(0,0,0,0.02);
    border-bottom: 1px solid rgba(0,0,0,0.125);
    padding: 1rem 1.5rem;
    border-radius: 12px 12px 0 0;
}

.card-body {
    padding: 1.5rem;
}

/* Actions Toolbar */
.actions-toolbar {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

/* Metric Cards Compactas */
.metric-card {
    background: linear-gradient(135deg, var(--bs-primary), var(--bs-primary-dark, #0056b3));
    border-radius: 12px;
    padding: 1rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-height: 80px;
}

.metric-card.bg-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
}

.metric-card.bg-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #000;
}

.metric-card.bg-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
}

.metric-icon {
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.metric-content h4 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
}

.metric-content span {
    font-size: 0.8rem;
    opacity: 0.9;
}

/* Info Items */
.info-item {
    margin-bottom: 1rem;
}

.info-item label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    display: block;
}

/* Avatar */
.avatar {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Seguradora Items */
.seguradora-item {
    transition: all 0.2s ease;
}

.seguradora-item:hover {
    background-color: rgba(0,0,0,0.01);
}

.seguradora-item:last-child {
    border-bottom: none !important;
}

.seguradora-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1976d2;
    font-size: 1.1rem;
}

.seguradora-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.seguradora-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

/* Comments System */
.comments-section {
    border-top: 1px solid #dee2e6;
}

.comments-thread {
    max-height: 300px;
    overflow-y: auto;
}

.comment-item {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
}

.comment-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.comment-author {
    font-weight: 600;
    font-size: 0.85rem;
}

.comment-time {
    font-size: 0.75rem;
    color: #6c757d;
    margin-left: auto;
}

.comment-body {
    font-size: 0.9rem;
    line-height: 1.4;
    color: #495057;
}

.new-comment-form textarea {
    resize: none;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

/* Progress Indicator */
.progress-indicator {
    display: flex;
    align-items: center;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 24px;
}

.timeline-item {
    position: relative;
    margin-bottom: 16px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -26px;
    top: 10px;
    bottom: -16px;
    width: 2px;
    background-color: #e3e6f0;
}

.timeline-content {
    background: #f8f9fc;
    padding: 10px;
    border-radius: 6px;
    border-left: 3px solid var(--bs-primary);
}

.timeline-header {
    margin-bottom: 4px;
}

.timeline-body {
    font-size: 0.85rem;
    color: #5a5c69;
    line-height: 1.4;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.stat-item {
    text-align: center;
    padding: 0.75rem;
    background: rgba(0,0,0,0.02);
    border-radius: 8px;
}

.stat-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #495057;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Form Controls */
.form-select, .form-control {
    border-radius: 6px;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.form-select:focus, .form-control:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}

.status-select {
    min-width: 130px;
}

/* Buttons */
.btn {
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-sm {
    font-size: 0.8rem;
    padding: 0.25rem 0.75rem;
}

/* Dropdown */
.dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 8px;
}

.dropdown-header {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6c757d;
    padding: 0.5rem 1rem 0.25rem;
}

/* Observações Container */
.observacoes-container .alert {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    background: #f8f9fa;
    margin-bottom: 0;
    padding: 1rem;
}

.observacoes-container .bg-light {
    background-color: #f8f9fa !important;
    border: 1px solid #dee2e6;
    padding: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .actions-toolbar {
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .metric-card {
        margin-bottom: 0.75rem;
        padding: 0.75rem;
        min-height: 70px;
    }
    
    .metric-content h4 {
        font-size: 1.3rem;
    }
    
    .seguradora-actions {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .progress-indicator {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-end;
    }
}

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.comment-item {
    animation: fadeIn 0.3s ease-out;
}

/* Hover Effects */
.seguradora-item:hover .btn-outline-primary {
    background-color: var(--bs-primary);
    color: white;
}

.seguradora-item:hover .btn-outline-success {
    background-color: var(--bs-success);
    color: white;
}

.seguradora-item:hover .btn-outline-info {
    background-color: var(--bs-info);
    color: white;
}

/* Custom Scrollbar */
.comments-thread::-webkit-scrollbar {
    width: 4px;
}

.comments-thread::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.comments-thread::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.comments-thread::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
@endpush

@push('scripts')
<script>
let currentCsId = null;
let commentsCache = {};

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

// Comentário Geral
function adicionarComentarioGeral() {
    document.getElementById('comentarioGeral').value = '';
    new bootstrap.Modal(document.getElementById('modalComentarioGeral')).show();
}

function salvarComentarioGeral() {
    const comentario = document.getElementById('comentarioGeral').value.trim();
    
    if (!comentario) {
        showToast('Digite um comentário', 'warning');
        return;
    }
    
    // ✅ Salva como atividade geral - aparece na TIMELINE
    fetch(`/cotacoes/{{ $cotacao->id }}/atividade`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            tipo: 'geral',
            descricao: comentario
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Comentário geral adicionado ao histórico', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalComentarioGeral')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao salvar comentário', 'danger');
    });
}

// Sistema de Comentários por Seguradora
function toggleComentarios(csId) {
    const commentsSection = document.getElementById(`comments-${csId}`);
    const bsCollapse = new bootstrap.Collapse(commentsSection, { toggle: true });
    
    // Carregar comentários se ainda não foram carregados
    if (!commentsCache[csId]) {
        carregarComentarios(csId);
    }
}

function carregarComentarios(csId) {
    const thread = document.getElementById(`thread-${csId}`);
    
    // TODO: Implementar API real para buscar comentários
    // Por enquanto, mostrar que não há comentários
    setTimeout(() => {
        renderizarComentarios(csId, []); // ✅ Array vazio - sem comentários fixos
        atualizarContadorComentarios(csId, 0);
        commentsCache[csId] = [];
    }, 300);
}

function renderizarComentarios(csId, comentarios) {
    const thread = document.getElementById(`thread-${csId}`);
    
    if (comentarios.length === 0) {
        thread.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="bi bi-chat-dots fs-3 mb-2"></i>
                <p class="mb-0">Nenhum comentário ainda</p>
                <small>Seja o primeiro a comentar!</small>
            </div>
        `;
        return;
    }
    
    thread.innerHTML = comentarios.map(comment => `
        <div class="comment-item">
            <div class="comment-header">
                <span class="comment-author">${comment.author}</span>
                <span class="comment-time">${comment.time}</span>
            </div>
            <div class="comment-body">${comment.message}</div>
        </div>
    `).join('');
}

function adicionarComentario(csId) {
    const input = document.getElementById(`comment-input-${csId}`);
    const comentario = input.value.trim();
    
    if (!comentario) {
        showToast('Digite um comentário', 'warning');
        return;
    }
    
    // Simular envio (implementar API real depois)
    const novoComentario = {
        id: Date.now(),
        author: 'Você',
        time: new Date().toLocaleString('pt-BR'),
        message: comentario
    };
    
    // Adicionar à cache e re-renderizar
    if (!commentsCache[csId]) {
        commentsCache[csId] = [];
    }
    commentsCache[csId].push(novoComentario);
    
    renderizarComentarios(csId, commentsCache[csId]);
    atualizarContadorComentarios(csId, commentsCache[csId].length);
    
    // Limpar input
    input.value = '';
    
    showToast('Comentário adicionado', 'success');
}

function atualizarContadorComentarios(csId, count) {
    const counter = document.getElementById(`comment-count-${csId}`);
    if (counter) {
        counter.textContent = count;
    }
}

// Ações das Seguradoras
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

function editarSeguradora(csId) {
    currentCsId = csId;
    
    // Buscar dados atuais
    fetch(`/cotacao-seguradoras/${csId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cs = data.cotacao_seguradora;
            
            document.getElementById('csId').value = csId;
            document.getElementById('modalValorPremio').value = cs.valor_premio || '';
            document.getElementById('modalValorIs').value = cs.valor_is || '';
            document.getElementById('modalDataRetorno').value = cs.data_retorno || '';
            
            new bootstrap.Modal(document.getElementById('modalEditarSeguradora')).show();
        } else {
            showToast('Erro ao carregar dados', 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao carregar dados', 'danger');
    });
}

function salvarSeguradora() {
    if (!currentCsId) return;
    
    const formData = new FormData(document.getElementById('formEditarSeguradora'));
    const data = Object.fromEntries(formData);
    
    fetch(`/cotacao-seguradoras/${currentCsId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Seguradora atualizada', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalEditarSeguradora')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao salvar', 'danger');
    });
}

// Status inline change
document.addEventListener('DOMContentLoaded', function() {
    const statusSelects = document.querySelectorAll('.status-select');
    
    statusSelects.forEach(select => {
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
                showToast('Erro ao atualizar status', 'danger');
                this.value = originalValue;
            });
        });
    });
});

// Ações Globais
function marcarComoEnviada() {
    if (!confirm('Marcar cotação como enviada para todas as seguradoras pendentes?')) {
        return;
    }
    
    fetch(`/cotacoes/{{ $cotacao->id }}/marcar-enviada`, {
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

function finalizarCotacao(status) {
    const confirmText = status === 'finalizada' ? 
        'Finalizar esta cotação? Esta ação não pode ser desfeita.' :
        'Cancelar esta cotação? Esta ação não pode ser desfeita.';
        
    if (!confirm(confirmText)) {
        return;
    }
    
    fetch(`/cotacoes/{{ $cotacao->id }}/status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: status })
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
        showToast('Erro ao atualizar status', 'danger');
    });
}

function exportarPDF() {
    window.open(`/cotacoes/{{ $cotacao->id }}/pdf`, '_blank');
}

function duplicarCotacao() {
    if (!confirm('Criar uma nova cotação baseada nesta?')) {
        return;
    }
    
    fetch(`/cotacoes/{{ $cotacao->id }}/duplicar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Cotação duplicada', 'success');
            setTimeout(() => window.location.href = `/cotacoes/${data.nova_cotacao_id}`, 1500);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao duplicar cotação', 'danger');
    });
}

function editarObservacoes() {
    showToast('Funcionalidade em desenvolvimento', 'info');
}

function carregarMaisAtividades() {
    showToast('Carregando mais atividades...', 'info');
}
</script>
@endpush
@endsection