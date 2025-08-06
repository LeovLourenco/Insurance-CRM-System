@extends('layouts.app')

@section('title', 'Cotação #' . $cotacao->id)

@section('content')
<div class="container">
    {{-- Header Simplificado (apenas título e breadcrumb) --}}
    <div class="mb-3">
        <div>
            <h1 class="h3 mb-2">
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
    </div>

    {{-- Nova Actions Bar Dedicada --}}
    <div class="actions-bar-container mb-4">
        <div class="actions-bar">
            {{-- Grupo Principal (sempre visível) --}}
            <div class="actions-primary">
                <button class="btn btn-outline-secondary" onclick="window.history.back()">
                    <i class="bi bi-arrow-left"></i> 
                    <span class="btn-text">Voltar</span>
                </button>
                
                @if($cotacao->status === 'em_andamento')
                    @if($cotacao->pode_enviar)
                        <button class="btn btn-success" onclick="marcarComoEnviada()">
                            <i class="bi bi-send"></i> 
                            <span class="btn-text">Enviar Todas</span>
                        </button>
                    @endif
                    
                    <button class="btn btn-primary" onclick="adicionarComentarioGeral()">
                        <i class="bi bi-chat-dots"></i> 
                        <span class="btn-text">Comentário</span>
                    </button>
                @endif
            </div>

            {{-- Grupo Secundário (desktop: visível, mobile: no menu) --}}
            <div class="actions-secondary">
                @if($cotacao->status === 'em_andamento')
                    {{-- Desktop: Botões diretos --}}
                    <div class="desktop-actions">
                        <button class="btn btn-outline-success btn-sm" onclick="finalizarCotacao('finalizada')">
                            <i class="bi bi-check-circle"></i> 
                            <span class="btn-text">Finalizar</span>
                        </button>
                        
                        <button class="btn btn-outline-danger btn-sm" onclick="finalizarCotacao('cancelada')">
                            <i class="bi bi-x-circle"></i> 
                            <span class="btn-text">Cancelar</span>
                        </button>
                    </div>
                @else
                    {{-- Status finalizado/cancelado: sem ações específicas --}}
                    <div class="desktop-actions">
                        {{-- Sem botões específicos para cotações finalizadas --}}
                    </div>
                @endif

                {{-- Ações Extras (sempre disponíveis) --}}
                <div class="btn-group actions-menu">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                        <span class="btn-text">Mais</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @if($cotacao->status === 'em_andamento')
                            {{-- Mobile: Status actions --}}
                            <li class="mobile-only">
                                <h6 class="dropdown-header">Alterar Status</h6>
                            </li>
                            <li class="mobile-only">
                                <a class="dropdown-item" onclick="finalizarCotacao('finalizada')">
                                    <i class="bi bi-check-circle text-success"></i> Finalizar Cotação
                                </a>
                            </li>
                            <li class="mobile-only">
                                <a class="dropdown-item" onclick="finalizarCotacao('cancelada')">
                                    <i class="bi bi-x-circle text-danger"></i> Cancelar Cotação
                                </a>
                            </li>
                            <li class="mobile-only"><hr class="dropdown-divider"></li>
                        @endif
                        
                        <li><h6 class="dropdown-header">Ações Adicionais</h6></li>

                        <li>
                            <a class="dropdown-item" onclick="funcionalidadeEmConstrucao()">
                                <i class="bi bi-file-pdf text-danger"></i> Exportar PDF
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" onclick="funcionalidadeEmConstrucao()">
                                <i class="bi bi-file-earmark-excel text-success"></i> Exportar Excel
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <a class="dropdown-item" onclick="funcionalidadeEmConstrucao()">
                                <i class="bi bi-printer"></i> Imprimir
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" onclick="funcionalidadeEmConstrucao()">
                                <i class="bi bi-share"></i> Compartilhar
                            </a>
                        </li>
                        
                        @if(auth()->user()->can('delete', $cotacao))
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" onclick="excluirCotacao()">
                                    <i class="bi bi-trash"></i> Excluir Cotação
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        {{-- Indicador de Status Mobile --}}
        <div class="mobile-status-info">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i>
                @if($cotacao->status === 'em_andamento')
                    Cotação em andamento - você pode editar e enviar
                @elseif($cotacao->status === 'finalizada')
                    Cotação finalizada - apenas leitura
                @else
                    Cotação cancelada - apenas leitura
                @endif
            </small>
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

    {{-- Resto do conteúdo permanece igual... --}}
    <div class="row">
        {{-- Coluna Principal --}}
        <div class="col-lg-8">
            {{-- Informações Gerais --}}
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
                    
                    {{-- Observações Gerais --}}
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="mb-0">Observações Gerais</label>
                            @if($cotacao->pode_editar)
                                <button class="btn btn-sm btn-outline-secondary" onclick="editarObservacoesGerais()">
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

            {{-- Cards das Seguradoras (continua igual) --}}
            <div class="modern-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-buildings text-primary"></i> 
                        Seguradoras ({{ $cotacao->cotacaoSeguradoras->count() }})
                    </h5>
                    <div class="progress-indicator">
                        <small class="text-muted">{{ $cotacao->quantidade_respondida }}/{{ $cotacao->cotacaoSeguradoras->count() }} respondidas</small>
                        <div class="progress ms-2" style="width: 80px; height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $cotacao->percentual_resposta }}%;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-3">
                    @if($cotacao->cotacaoSeguradoras->count() > 0)
                        <div class="row g-3">
                            @foreach($cotacao->cotacaoSeguradoras as $cs)
                                @php
                                    $responsavel = $cotacao->user ?? auth()->user();
                                    $avatarLetras = $responsavel ? strtoupper(substr($responsavel->name, 0, 2)) : 'US';
                                    
                                    $statusConfig = [
                                        'aguardando' => ['texto' => 'Aguardando', 'cor' => 'warning', 'icone' => 'clock'],
                                        'em_analise' => ['texto' => 'Em Análise', 'cor' => 'info', 'icone' => 'hourglass-split'],
                                        'aprovada' => ['texto' => 'Aprovada', 'cor' => 'success', 'icone' => 'check-circle'],
                                        'rejeitada' => ['texto' => 'Rejeitada', 'cor' => 'danger', 'icone' => 'x-circle'],
                                        'repique' => ['texto' => 'Repique', 'cor' => 'primary', 'icone' => 'arrow-clockwise']
                                    ];
                                    
                                    $config = $statusConfig[$cs->status] ?? ['texto' => ucfirst($cs->status), 'cor' => 'secondary', 'icone' => 'circle'];
                                @endphp
                                
                                <div class="col-lg-4 col-md-6">
                                    <div class="modern-card p-3 h-100 seguradora-card-sistema" 
                                         onclick="abrirModalSeguradora({{ $cs->id }})" 
                                         style="cursor: pointer; transition: all 0.2s ease;">
                                        
                                        {{-- Header --}}
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0">
                                                <div class="bg-{{ $config['cor'] }} bg-opacity-10 rounded-3 p-2">
                                                    <i class="bi bi-{{ $config['icone'] }} text-{{ $config['cor'] }} fs-5"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0 fw-bold seguradora-name-compact">{{ $cs->seguradora->nome }}</h6>
                                                <small class="text-muted">ID: {{ $cs->id }}</small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="bi bi-chevron-right text-muted"></i>
                                            </div>
                                        </div>
                                        
                                        {{-- Status --}}
                                        <div class="mb-3">
                                            <span class="badge bg-{{ $config['cor'] }} status-compact status-{{ $cs->status }}">
                                                <i class="bi bi-{{ $config['icone'] }} me-1"></i>
                                                {{ $config['texto'] }}
                                            </span>
                                        </div>
                                        
                                        {{-- Valor --}}
                                        <div class="mb-3">
                                            @if($cs->valor_premio)
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="bi bi-currency-dollar text-success fs-5"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <div class="fw-bold text-success valor-compact">
                                                            R$ {{ number_format($cs->valor_premio, 2, ',', '.') }}
                                                        </div>
                                                        @if($cs->valor_is)
                                                            <small class="text-muted">I.S.: R$ {{ number_format($cs->valor_is, 2, ',', '.') }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="bi bi-dash-circle text-muted fs-5"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <div class="fw-medium text-muted valor-compact pendente">
                                                            @if($cs->status === 'aguardando')
                                                                Aguardando envio
                                                            @elseif($cs->status === 'em_analise')
                                                                Aguardando resposta
                                                            @elseif($cs->status === 'rejeitada')
                                                                Sem proposta
                                                            @else
                                                                {{ $cs->status_formatado }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Observação --}}
                                        <div class="mb-3">
                                            @if($cs->observacoes)
                                                <div class="bg-light rounded p-2 comentario-preview">
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-chat-quote text-primary me-2 mt-1 flex-shrink-0"></i>
                                                        <div class="flex-grow-1">
                                                            <p class="mb-1 comentario-texto" style="font-size: 0.85rem; line-height: 1.3;">
                                                                {{ Str::limit($cs->observacoes, 80) }}
                                                            </p>
                                                            <div class="comentario-meta">
                                                                <small class="text-muted">
                                                                    {{ $cs->updated_at->diffForHumans() }} • {{ $responsavel->name ?? 'Sistema' }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="bg-light rounded p-2 text-center">
                                                    <small class="text-muted sem-comentario">
                                                        <i class="bi bi-chat me-1"></i>Nenhuma observação
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Footer --}}
                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                            <div class="card-footer-compact">
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar3 me-1"></i>
                                                    {{ $cs->updated_at->format('d/m H:i') }}
                                                </small>
                                            </div>
                                            <div class="d-flex align-items-center responsavel-compact">
                                                <div class="avatar avatar-sm bg-primary text-white rounded-circle me-1 avatar-compact" 
                                                     style="width: 20px; height: 20px; font-size: 0.7rem;">
                                                    {{ $avatarLetras }}
                                                </div>
                                                <small class="text-muted">{{ $responsavel->name ?? 'Sistema' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-4 d-inline-block mb-3">
                                <i class="bi bi-buildings text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-muted mb-2">Nenhuma seguradora associada</h5>
                            <p class="text-muted mb-3">Adicione seguradoras para começar as cotações</p>
                            <button class="btn btn-primary" onclick="adicionarSeguradora()">
                                <i class="bi bi-plus-circle me-1"></i>Adicionar Seguradora
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar (continua igual) --}}
        <div class="col-lg-4 mb-4">
            {{-- Timeline --}}
            <div class="modern-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history text-primary"></i> Histórico da Cotação
                    </h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="abrirHistoricoCompleto()">
                        <i class="bi bi-list-ul"></i> Ver Tudo
                    </button>
                </div>
                <div class="card-body">
                    @if($cotacao->atividades->count() > 0)
                        <div class="timeline">
                            @foreach($cotacao->atividades->sortByDesc('created_at')->take(10) as $atividade)
                                <div class="timeline-item clickable" 
                                     onclick="expandirAtividade({{ $atividade->id }})"
                                     title="Clique para ver detalhes">
                                    <div class="timeline-marker bg-{{ $atividade->tipo === 'geral' ? 'primary' : 'info' }}"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <small class="text-muted">
                                                {{ $atividade->created_at->format('d/m H:i') }}
                                                @if($atividade->user)
                                                    - {{ $atividade->user->name }}
                                                @endif
                                            </small>
                                            @if(strlen($atividade->descricao) > 60)
                                                <i class="bi bi-chevron-right expand-indicator ms-2"></i>
                                            @endif
                                        </div>
                                        <div class="timeline-body">
                                            {{ Str::limit($atividade->descricao, 60) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($cotacao->atividades->count() > 10)
                            <div class="text-center mt-3">
                                <button class="btn btn-sm btn-outline-secondary" onclick="abrirHistoricoCompleto()">
                                    <i class="bi bi-chevron-down"></i> Ver todas as {{ $cotacao->atividades->count() }} atividades
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

            {{-- Resumo --}}
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
@endsection

{{-- ===== MODAIS ===== --}}

{{-- Modal Comentário Geral --}}
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

{{-- Modal Único da Seguradora com Views Alternáveis --}}
<div class="modal fade" id="modalSeguradoraDetalhes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {{-- Header Dinâmico --}}
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <button class="btn btn-sm btn-outline-secondary me-2" id="btnVoltarView" style="display: none;" onclick="voltarViewDetalhes()">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    <i class="bi bi-building text-primary me-2"></i>
                    <span id="modalSeguradoraNome">Nome da Seguradora</span>
                    <span class="badge ms-2" id="modalViewIndicator">Detalhes</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                {{-- ===== VIEW 1: DETALHES DA SEGURADORA ===== --}}
                <div id="viewDetalhes" class="modal-view active">
                    {{-- Status Atual --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="fw-semibold mb-0">Status Atual</label>
                            <button class="btn btn-sm btn-outline-primary" onclick="abrirViewStatus()">
                                <i class="bi bi-arrow-repeat me-1"></i>Alterar Status
                            </button>
                        </div>
                        <div id="modalStatus">
                            <span class="badge bg-secondary">Carregando...</span>
                        </div>
                    </div>

                    {{-- Informações da Proposta --}}
                    <div class="mb-4">
                        <label class="fw-semibold mb-2 d-block">
                            <i class="bi bi-currency-dollar text-success me-1"></i>
                            Informações da Proposta
                        </label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="bg-light rounded p-3 text-center">
                                    <div class="text-muted small mb-1">Valor do Prêmio</div>
                                    <div class="fw-bold text-success" id="modalValorPremio">Não informado</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded p-3 text-center">
                                    <div class="text-muted small mb-1">Valor I.S.</div>
                                    <div class="fw-bold" id="modalValorIs">Não informado</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded p-3 text-center">
                                    <div class="text-muted small mb-1">Data de Envio</div>
                                    <div class="fw-bold" id="modalDataEnvio">Não enviada</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Observações --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="fw-semibold mb-0">
                                <i class="bi bi-chat-quote text-primary me-1"></i>
                                Observações
                            </label>
                            <button class="btn btn-sm btn-outline-secondary" onclick="abrirViewComentario()">
                                <i class="bi bi-plus me-1"></i>Adicionar
                            </button>
                        </div>
                        <div id="modalObservacoes">
                            <em class="text-muted">
                                <i class="bi bi-chat-quote me-2"></i>
                                Nenhuma observação registrada
                            </em>
                        </div>
                    </div>

                    {{-- Timeline --}}
                    <div class="mb-3">
                        <label class="fw-semibold mb-2 d-block">
                            <i class="bi bi-clock-history text-info me-1"></i>
                            Histórico da Seguradora
                        </label>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <div id="modalTimeline">
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-hourglass-split"></i>
                                    Carregando histórico...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== VIEW 2: ALTERAR STATUS ===== --}}
                <div id="viewStatus" class="modal-view">
                    <form id="formMudarStatus">
                        <input type="hidden" id="statusCsId">
                        <input type="hidden" id="statusAtual">
                        
                        {{-- Status Atual → Novo Status --}}
                        <div class="status-transition mb-4">
                            <div class="d-flex align-items-center justify-content-center gap-3">
                                <div class="text-center">
                                    <label class="form-label small text-muted">Status Atual</label>
                                    <div>
                                        <span class="badge" id="statusFromBadge">Status Atual</span>
                                    </div>
                                </div>
                                <div class="align-self-end pb-2">
                                    <i class="bi bi-arrow-right fs-4 text-muted"></i>
                                </div>
                                <div class="text-center flex-grow-1">
                                    <label class="form-label small text-muted">Novo Status</label>
                                    <select class="form-select" id="novoStatus" required>
                                        <option value="">Selecione novo status</option>
                                        <option value="aguardando">🕐 Aguardando</option>
                                        <option value="em_analise">⏳ Em Análise</option>
                                        <option value="aprovada">✅ Aprovada</option>
                                        <option value="rejeitada">❌ Rejeitada</option>
                                        <option value="repique">🔄 Solicitar Repique</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Campos Dinâmicos --}}
                        <div id="camposCondicionais" class="mb-4"></div>

                        {{-- Observações Obrigatórias --}}
                        <div class="mb-3">
                            <label class="form-label required">O que aconteceu?</label>
                            <textarea class="form-control" id="observacoesMudanca" rows="3" 
                                      placeholder="Descreva o que motivou esta mudança de status (obrigatório)" 
                                      required></textarea>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> 
                                Esta informação será registrada no histórico da cotação
                            </div>
                        </div>

                        {{-- Data/Hora --}}
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Quando isso aconteceu?</label>
                                <input type="datetime-local" class="form-control" id="dataOcorrencia" 
                                       value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Responsável</label>
                                <input type="text" class="form-control" readonly 
                                       value="{{ auth()->user()->name }}">
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ===== VIEW 3: ADICIONAR COMENTÁRIO ===== --}}
                <div id="viewComentario" class="modal-view">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 d-inline-block">
                            <i class="bi bi-chat-plus text-primary fs-1"></i>
                        </div>
                        <h5 class="mt-2 mb-1">Adicionar Comentário</h5>
                        <p class="text-muted mb-0">Registre observações sobre esta seguradora</p>
                    </div>

                    <form id="formComentario">
                        <div class="mb-3">
                            <label class="form-label">Comentário</label>
                            <textarea class="form-control" id="novoComentario" rows="4" 
                                      placeholder="Digite sua observação sobre esta seguradora..." 
                                      required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Data</label>
                                <input type="datetime-local" class="form-control" id="dataComentario" 
                                       value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Autor</label>
                                <input type="text" class="form-control" readonly 
                                       value="{{ auth()->user()->name }}">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            {{-- Footer Dinâmico --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                
                {{-- Botões da View Detalhes --}}
                <div id="footerDetalhes" class="footer-view">
                    <button type="button" class="btn btn-outline-primary" onclick="abrirViewComentario()">
                        <i class="bi bi-chat-plus me-1"></i>Comentário
                    </button>
                </div>
                
                {{-- Botões da View Status --}}
                <div id="footerStatus" class="footer-view" style="display: none;">
                    <button type="button" class="btn btn-outline-secondary" onclick="voltarViewDetalhes()">
                        <i class="bi bi-arrow-left me-1"></i>Voltar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="salvarMudancaStatus()">
                        <i class="bi bi-check-circle me-1"></i>Confirmar Mudança
                    </button>
                </div>
                
                {{-- Botões da View Comentário --}}
                <div id="footerComentario" class="footer-view" style="display: none;">
                    <button type="button" class="btn btn-outline-secondary" onclick="voltarViewDetalhes()">
                        <i class="bi bi-arrow-left me-1"></i>Voltar
                    </button>
                    <button type="button" class="btn btn-success" onclick="salvarComentario()">
                        <i class="bi bi-check-circle me-1"></i>Salvar Comentário
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Histórico Completo --}}
<div class="modal fade" id="modalHistoricoCompleto" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history text-primary"></i>
                    Histórico Completo - Cotação #{{ $cotacao->id }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                <div class="timeline-completa">
                    @foreach($cotacao->atividades->sortByDesc('created_at') as $atividade)
                        <div class="timeline-item-completa" data-atividade-id="{{ $atividade->id }}">
                            <div class="d-flex gap-3">
                                <div class="timeline-marker-completa bg-{{ $atividade->tipo === 'geral' ? 'primary' : 'info' }}">
                                    @if($atividade->tipo === 'geral')
                                        <i class="bi bi-chat-dots"></i>
                                    @elseif($atividade->tipo === 'seguradora')
                                        <i class="bi bi-building"></i>
                                    @else
                                        <i class="bi bi-gear"></i>
                                    @endif
                                </div>
                                <div class="timeline-content-completa flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="timeline-meta">
                                            <span class="fw-semibold">
                                                {{ $atividade->created_at->format('d/m/Y H:i') }}
                                            </span>
                                            @if($atividade->user)
                                                <span class="text-muted">por {{ $atividade->user->name }}</span>
                                            @endif
                                            <span class="badge bg-light text-dark ms-2">{{ ucfirst($atividade->tipo) }}</span>
                                        </div>
                                        <small class="text-muted">{{ $atividade->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="timeline-body-completa">
                                        <p class="mb-0">{{ $atividade->descricao }}</p>
                                    </div>
                                    @if($atividade->cotacaoSeguradora)
                                        <div class="mt-2">
                                            <span class="badge bg-info">
                                                <i class="bi bi-building me-1"></i>
                                                {{ $atividade->cotacaoSeguradora->seguradora->nome ?? 'Seguradora' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-outline-primary" onclick="exportarHistorico()">
                    <i class="bi bi-download"></i> Exportar Histórico
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detalhe Atividade --}}
<div class="modal fade" id="modalDetalheAtividade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle text-primary"></i>
                    Detalhes da Atividade
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="conteudoDetalheAtividade">
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="toastConstrucao" class="toast align-items-center text-bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-tools me-2"></i>
                🚧 Esta funcionalidade está sendo desenvolvida e estará disponível em breve!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ===== BASE STYLES ===== */
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

/* ===== NOVA ACTIONS BAR ===== */
.actions-bar-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.08);
    position: relative;
    overflow: visible;
}

.actions-bar-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #007bff, #6610f2, #6f42c1);
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

.actions-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Grupos de ações */
.actions-primary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.actions-secondary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.desktop-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

/* Botões da Actions Bar */
.actions-bar .btn {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border-radius: 8px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    white-space: nowrap;
}

.actions-bar .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.actions-bar .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.actions-bar .btn:hover::before {
    left: 100%;
}

/* Ícones nos botões */
.actions-bar .btn i {
    font-size: 1rem;
    transition: transform 0.3s ease;
}

.actions-bar .btn:hover i {
    transform: scale(1.1);
}

/* Texto dos botões */
.btn-text {
    display: inline-block;
    font-size: 0.875rem;
}

/* Status info mobile */
.mobile-status-info {
    display: none;
    margin-top: 0.75rem;
    padding: 0.5rem;
    background: rgba(255,255,255,0.8);
    border-radius: 8px;
    border-left: 3px solid var(--bs-primary);
}

.mobile-status-info small {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Dropdown customizado */
.actions-menu .dropdown-menu {
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.12);
    border-radius: 12px;
    padding: 0.5rem 0;
    min-width: 220px;
    animation: fadeInDown 0.3s ease;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.actions-menu .dropdown-item {
    padding: 0.6rem 1.2rem;
    transition: all 0.2s ease;
    border-radius: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.actions-menu .dropdown-item:hover {
    background: linear-gradient(90deg, rgba(0,123,255,0.1) 0%, rgba(0,123,255,0.05) 100%);
    transform: translateX(4px);
}

.actions-menu .dropdown-item i {
    width: 20px;
    text-align: center;
}

.actions-menu .dropdown-header {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6c757d;
    padding: 0.5rem 1.2rem 0.25rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    margin-bottom: 0.25rem;
}

.actions-menu .dropdown-divider {
    margin: 0.5rem 0;
    border-top: 1px solid rgba(0,0,0,0.08);
}

.actions-menu .dropdown-item.text-danger:hover {
    background: linear-gradient(90deg, rgba(220,53,69,0.1) 0%, rgba(220,53,69,0.05) 100%);
    color: #dc3545;
}

/* Mobile only items (hidden on desktop) */
.mobile-only {
    display: none;
}

/* ===== RESPONSIVIDADE DA ACTIONS BAR ===== */
@media (max-width: 768px) {
    .actions-bar-container {
        padding: 0.75rem;
        margin-bottom: 1rem;
        position: sticky;
        top: 60px; /* Ajustar conforme altura do header */
        z-index: 100;
        background: rgba(248, 249, 250, 0.95);
        backdrop-filter: blur(10px);
    }
    
    .actions-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }
    
    .actions-primary {
        justify-content: center;
        order: 1;
    }
    
    .actions-secondary {
        justify-content: center;
        order: 2;
    }
    
    /* Esconder ações desktop no mobile */
    .desktop-actions {
        display: none;
    }
    
    /* Mostrar items mobile no dropdown */
    .mobile-only {
        display: block;
    }
    
    /* Ajustar botões para mobile */
    .actions-bar .btn {
        padding: 0.6rem 0.8rem;
        font-size: 0.875rem;
        min-height: 44px; /* Touch target mínimo */
    }
    
    /* Esconder texto em telas muito pequenas */
    @media (max-width: 380px) {
        .btn-text {
            display: none;
        }
        
        .actions-bar .btn {
            padding: 0.6rem;
            min-width: 44px;
            justify-content: center;
        }
    }
    
    /* Mostrar status info no mobile */
    .mobile-status-info {
        display: block;
        animation: slideInUp 0.3s ease;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Dropdown fullwidth no mobile */
    .actions-menu .dropdown-menu {
        position: fixed !important;
        left: 1rem !important;
        right: 1rem !important;
        top: auto !important;
        bottom: 1rem !important;
        width: auto !important;
        max-height: 70vh;
        overflow-y: auto;
        transform: none !important;
    }
    
    .actions-menu.show .dropdown-menu {
        animation: slideUp 0.3s ease;
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
}

/* ===== TABLETS ===== */
@media (min-width: 769px) and (max-width: 1024px) {
    .actions-bar-container {
        padding: 1rem 0.75rem;
    }
    
    .actions-bar .btn {
        padding: 0.4rem 0.75rem;
        font-size: 0.85rem;
    }
    
    .btn-text {
        font-size: 0.8rem;
    }
    
    /* Mostrar alguns itens mobile no tablet */
    .mobile-only:not(.dropdown-divider) {
        display: none;
    }
}

/* ===== TELAS GRANDES ===== */
@media (min-width: 1400px) {
    .actions-bar-container {
        padding: 1.25rem 1.5rem;
    }
    
    .actions-bar .btn {
        padding: 0.6rem 1.25rem;
    }
    
    .btn-text {
        font-size: 0.9rem;
    }
}

/* ===== ESTADOS DOS BOTÕES ===== */

/* Botão Enviar Todas */
.btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    color: white;
}

.btn-success:hover {
    background: linear-gradient(135deg, #20c997, #28a745);
}

/* Botão Comentário */
.btn-primary {
    background: linear-gradient(135deg, #007bff, #6610f2);
    border: none;
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #6610f2, #007bff);
}

/* Botões Outline */
.btn-outline-success {
    position: relative;
    overflow: hidden;
}

.btn-outline-success::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(40, 167, 69, 0.1);
    transform: translate(-50%, -50%);
    transition: width 0.5s ease, height 0.5s ease;
}

.btn-outline-success:hover::after {
    width: 100%;
    height: 100%;
}

.btn-outline-danger {
    position: relative;
    overflow: hidden;
}

.btn-outline-danger::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(220, 53, 69, 0.1);
    transform: translate(-50%, -50%);
    transition: width 0.5s ease, height 0.5s ease;
}

.btn-outline-danger:hover::after {
    width: 100%;
    height: 100%;
}

/* ===== ANIMAÇÕES EXTRAS ===== */

/* Pulse para botões importantes */
@media (min-width: 769px) {
    .btn-success {
        animation: subtle-pulse 2s infinite;
    }
    
    @keyframes subtle-pulse {
        0%, 100% {
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
        }
        50% {
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.5);
        }
    }
}

/* Loading state */
.actions-bar .btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.actions-bar .btn.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* ===== INDICADORES VISUAIS ===== */

/* Badge de contagem */
.actions-bar .btn .badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    font-size: 0.65rem;
    font-weight: 700;
    border-radius: 50%;
    background: #dc3545;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: bounceIn 0.5s ease;
}

@keyframes bounceIn {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

/* Tooltip customizado */
.actions-bar .btn[title] {
    position: relative;
}

.actions-bar .btn[title]:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.5rem 0.75rem;
    background: rgba(0,0,0,0.9);
    color: white;
    font-size: 0.75rem;
    border-radius: 6px;
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    animation: fadeInTooltip 0.3s ease forwards;
    margin-bottom: 0.5rem;
    z-index: 1000;
}

@keyframes fadeInTooltip {
    to {
        opacity: 1;
    }
}

/* ===== METRIC CARDS ===== */
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

/* ===== INFO ITEMS ===== */
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

.avatar {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}

/* ===== CARDS DAS SEGURADORAS ===== */
.seguradora-card-sistema:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}

.seguradora-card-sistema:hover .bi-chevron-right {
    transform: translateX(4px);
    color: var(--bs-primary) !important;
}

.status-compact {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.valor-compact {
    font-size: 1.1rem;
    font-weight: 700;
    color: #28a745;
}

.valor-compact.pendente {
    color: #6c757d;
    font-weight: 500;
    font-style: italic;
}

.comentario-preview {
    background: #f8f9fa;
    border-left: 3px solid #007bff;
    padding: 0.5rem;
    border-radius: 0 4px 4px 0;
}

.comentario-texto {
    font-size: 0.8rem;
    color: #495057;
    margin: 0;
    line-height: 1.3;
}

.comentario-meta {
    font-size: 0.7rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.sem-comentario {
    font-size: 0.8rem;
    color: #adb5bd;
    font-style: italic;
}

.avatar-compact {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 600;
}

/* ===== TIMELINE ===== */
.timeline {
    position: relative;
    padding-left: 24px;
}

.timeline-item {
    position: relative;
    margin-bottom: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.timeline-item.clickable:hover {
    transform: translateX(4px);
}

.timeline-item.clickable:hover .timeline-content {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
    transition: all 0.2s ease;
}

.timeline-header {
    margin-bottom: 4px;
    display: flex;
    align-items: center;
}

.timeline-body {
    font-size: 0.85rem;
    color: #5a5c69;
    line-height: 1.4;
}

.expand-indicator {
    font-size: 0.7rem;
    transition: transform 0.2s ease;
}

.timeline-item.clickable:hover .expand-indicator {
    transform: rotate(90deg);
}

/* ===== TIMELINE COMPLETA ===== */
.timeline-completa {
    position: relative;
}

.timeline-item-completa {
    position: relative;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.timeline-item-completa:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.timeline-marker-completa {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content-completa {
    background: #f8f9fc;
    border-radius: 8px;
    padding: 1rem;
    border-left: 3px solid var(--bs-primary);
}

.timeline-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.timeline-body-completa {
    font-size: 0.9rem;
    line-height: 1.5;
    color: #495057;
}

.timeline-body-completa p {
    white-space: pre-wrap;
}

.timeline-item-completa:hover .timeline-content-completa {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transform: translateX(2px);
}

/* ===== STATS GRID ===== */
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

/* ===== MODAIS ===== */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
}

.modal-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-bottom: 1px solid #dee2e6;
    border-radius: 12px 12px 0 0;
    padding: 1.5rem;
}

.modal-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

/* ===== MODAL ÚNICO COM VIEWS ALTERNÁVEIS ===== */

/* Transições suaves entre views */
.modal-view {
    opacity: 0;
    transition: all 0.3s ease-in-out;
    transform: translateX(10px);
    display: none;
}

.modal-view.active {
    opacity: 1;
    transform: translateX(0);
    display: block;
}

/* Header dinâmico */
.modal-header .modal-title {
    transition: all 0.3s ease;
}

#btnVoltarView {
    transition: all 0.2s ease;
    transform: scale(0.9);
}

#btnVoltarView:hover {
    transform: scale(1);
}

#modalViewIndicator {
    transition: all 0.3s ease;
    animation: fadeInBadge 0.3s ease;
}

@keyframes fadeInBadge {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}

/* Status transition visual */
.status-transition {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 12px;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
}

.status-transition::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
    transition: left 0.5s ease;
}

.status-transition:hover::before {
    left: 100%;
}

.status-transition .form-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

/* Badges de status */
.badge.status-aguardando {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #000;
}

.badge.status-em_analise {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

.badge.status-aprovada {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
}

.badge.status-rejeitada {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.badge.status-repique {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

/* Footer dinâmico */
.footer-view {
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.footer-view[style*="none"] {
    opacity: 0;
}

.footer-view:not([style*="none"]) {
    opacity: 1;
    animation: slideInUp 0.3s ease;
}

@keyframes slideInUp {
    from { transform: translateY(10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Formulários */
.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.form-control:focus, .form-select:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
    transform: translateY(-1px);
}

.form-label.required::after {
    content: " *";
    color: #dc3545;
}

/* View específicas */
#viewComentario {
    text-align: center;
}

#viewComentario .bg-primary {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(var(--bs-primary-rgb), 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(var(--bs-primary-rgb), 0); }
    100% { box-shadow: 0 0 0 0 rgba(var(--bs-primary-rgb), 0); }
}

#viewComentario form {
    text-align: left;
    animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Campos condicionais */
#camposCondicionais .alert {
    border-radius: 8px;
    animation: slideInRight 0.3s ease;
}

@keyframes slideInRight {
    from { transform: translateX(-20px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Botões com loading states */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn .bi-hourglass-split {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Timeline no modal */
#modalTimeline .timeline-item-modal {
    transition: all 0.2s ease;
    animation: fadeInTimeline 0.5s ease forwards;
    opacity: 0;
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid var(--bs-primary);
}

#modalTimeline .timeline-item-modal:nth-child(1) { animation-delay: 0.1s; }
#modalTimeline .timeline-item-modal:nth-child(2) { animation-delay: 0.2s; }
#modalTimeline .timeline-item-modal:nth-child(3) { animation-delay: 0.3s; }
#modalTimeline .timeline-item-modal:nth-child(4) { animation-delay: 0.4s; }
#modalTimeline .timeline-item-modal:nth-child(5) { animation-delay: 0.5s; }

@keyframes fadeInTimeline {
    to { opacity: 1; transform: translateX(0); }
}

#modalTimeline .timeline-item-modal:hover {
    transform: translateX(5px);
    background: #e9ecef;
}

#modalTimeline .timeline-marker-modal {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-top: 0.5rem;
    flex-shrink: 0;
}

#modalTimeline .timeline-content-modal {
    flex-grow: 1;
}

#modalTimeline .timeline-meta-modal {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

#modalTimeline .timeline-text-modal {
    color: #495057;
    line-height: 1.4;
}

/* Informações da proposta */
.bg-light {
    transition: all 0.2s ease;
}

.bg-light:hover {
    background-color: #e9ecef !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Observações */
#modalObservacoes .bg-light {
    border-left: 4px solid var(--bs-primary);
    position: relative;
    overflow: hidden;
}

#modalObservacoes .bg-light::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(to bottom, var(--bs-primary), var(--bs-info));
    animation: slideDown 0.6s ease;
}

@keyframes slideDown {
    from { height: 0; }
    to { height: 100%; }
}

/* PROGRESS */
.progress-indicator {
    display: flex;
    align-items: center;
}

/* OBSERVAÇÕES */
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

/* BUTTONS */
.btn {
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.btn-sm {
    font-size: 0.8rem;
    padding: 0.25rem 0.75rem;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.btn:hover::before {
    left: 100%;
}

/* DROPDOWN */
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

/* RESPONSIVO */
@media (max-width: 768px) {
    .metric-card {
        margin-bottom: 0.75rem;
        padding: 0.75rem;
        min-height: 70px;
    }
    
    .metric-content h4 {
        font-size: 1.3rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -26px;
    }
    
    .timeline-item:not(:last-child)::before {
        left: -22px;
    }
    
    .status-transition {
        padding: 1rem;
    }
    
    .status-transition .d-flex {
        flex-direction: column;
        gap: 1rem;
    }
    
    .status-transition .align-self-end {
        align-self: center !important;
        transform: rotate(90deg);
    }
    
    .modal-view {
        transform: translateY(10px);
    }
    
    .modal-view.active {
        transform: translateY(0);
    }
    
    #viewComentario .bg-primary {
        animation: none; /* Desabilitar pulse em mobile */
    }
}

/* UTILITY CLASSES */
.bi-chevron-right {
    transition: all 0.2s ease;
}

.fw-semibold {
    color: #495057;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
@endpush

@push('scripts')
<script>
// ===== VARIÁVEIS GLOBAIS =====
let modalSeguradoraDetalhes;
let currentView = 'detalhes';
let modalData = {
    csId: null,
    nomeSeguradora: '',
    statusAtual: '',
    statusTexto: '',
    valorTexto: '',
    comentarioTexto: ''
};

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando sistema de cotações...');
    
    // Inicializar Modal da Seguradora
    const modalSeguradoraElement = document.getElementById('modalSeguradoraDetalhes');
    if (modalSeguradoraElement) {
        modalSeguradoraDetalhes = new bootstrap.Modal(modalSeguradoraElement);
    }
    
    // Listener para mudança de status
    const novoStatusSelect = document.getElementById('novoStatus');
    if (novoStatusSelect) {
        novoStatusSelect.addEventListener('change', function() {
            carregarCamposCondicionais(this.value);
        });
    }
    
    console.log('✅ Sistema inicializado com sucesso');
});

// ===== TOAST HELPER =====
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

// ===== MODAL DA SEGURADORA COM VIEWS ALTERNÁVEIS =====
function abrirModalSeguradora(csId) {
    try {
        console.log('🎭 Abrindo modal para seguradora:', csId);
        
        // Buscar dados do card
        const cardElement = document.querySelector(`[onclick="abrirModalSeguradora(${csId})"]`);
        if (!cardElement) {
            showToast('Card da seguradora não encontrado', 'danger');
            return;
        }
        
        // Extrair dados do card
        const nomeElement = cardElement.querySelector('.seguradora-name-compact');
        const statusElement = cardElement.querySelector('.status-compact');
        const valorElement = cardElement.querySelector('.valor-compact');
        const comentarioElement = cardElement.querySelector('.comentario-texto');
        
        if (!nomeElement || !statusElement) {
            showToast('Dados do card incompletos', 'danger');
            return;
        }
        
        // Salvar dados no estado global
        modalData = {
            csId: csId,
            nomeSeguradora: nomeElement.textContent.trim(),
            statusAtual: statusElement.className.match(/status-(\w+)/)?.[1] || 'desconhecido',
            statusTexto: statusElement.textContent.trim(),
            valorTexto: valorElement ? valorElement.textContent.trim() : 'Não informado',
            comentarioTexto: comentarioElement ? comentarioElement.textContent.trim() : ''
        };
        
        // Resetar para view inicial
        mostrarView('detalhes');
        
        // Preencher dados
        preencherDadosModal();
        
        // Carregar timeline
        carregarTimelineSeguradora(csId);
        
        // Abrir modal
        if (modalSeguradoraDetalhes) {
            modalSeguradoraDetalhes.show();
            console.log('✅ Modal da seguradora aberto');
        }
        
    } catch (error) {
        console.error('🚨 Erro ao abrir modal da seguradora:', error);
        showToast('Erro ao abrir modal: ' + error.message, 'danger');
    }
}

// ===== CONTROLE DE VIEWS =====
function mostrarView(viewName) {
    console.log(`🔄 Alternando para view: ${viewName}`);
    
    // Esconder todas as views
    document.querySelectorAll('.modal-view').forEach(view => {
        view.style.display = 'none';
        view.classList.remove('active');
    });
    
    // Esconder todos os footers
    document.querySelectorAll('.footer-view').forEach(footer => {
        footer.style.display = 'none';
    });
    
    // Mostrar view selecionada
    const targetView = document.getElementById(`view${capitalize(viewName)}`);
    const targetFooter = document.getElementById(`footer${capitalize(viewName)}`);
    
    if (targetView) {
        targetView.style.display = 'block';
        
        // Animação suave
        setTimeout(() => {
            targetView.classList.add('active');
        }, 50);
    }
    
    if (targetFooter) {
        targetFooter.style.display = 'flex';
    }
    
    // Atualizar indicadores visuais
    atualizarIndicadoresView(viewName);
    
    // Salvar estado atual
    currentView = viewName;
}

function atualizarIndicadoresView(viewName) {
    const indicator = document.getElementById('modalViewIndicator');
    const btnVoltar = document.getElementById('btnVoltarView');
    
    const viewConfigs = {
        'detalhes': { texto: 'Detalhes', cor: 'bg-primary', mostrarVoltar: false },
        'status': { texto: 'Alterar Status', cor: 'bg-warning', mostrarVoltar: true },
        'comentario': { texto: 'Comentário', cor: 'bg-success', mostrarVoltar: true }
    };
    
    const config = viewConfigs[viewName] || viewConfigs['detalhes'];
    
    if (indicator) {
        indicator.textContent = config.texto;
        indicator.className = `badge ms-2 ${config.cor}`;
    }
    
    if (btnVoltar) {
        btnVoltar.style.display = config.mostrarVoltar ? 'inline-block' : 'none';
    }
}

// ===== NAVEGAÇÃO ENTRE VIEWS =====
function abrirViewStatus() {
    try {
        console.log('📝 Abrindo view de status');
        
        // Preencher dados do formulário de status
        document.getElementById('statusCsId').value = modalData.csId;
        document.getElementById('statusAtual').value = modalData.statusAtual;
        
        // Configurar status atual no formulário
        const statusTextos = {
            'aguardando': '🕐 Aguardando',
            'em_analise': '⏳ Em Análise', 
            'aprovada': '✅ Aprovada',
            'rejeitada': '❌ Rejeitada',
            'repique': '🔄 Repique'
        };
        
        const statusFromBadge = document.getElementById('statusFromBadge');
        if (statusFromBadge) {
            statusFromBadge.textContent = statusTextos[modalData.statusAtual] || modalData.statusAtual;
            statusFromBadge.className = `badge status-${modalData.statusAtual}`;
        }
        
        // Limpar formulário
        document.getElementById('novoStatus').value = '';
        document.getElementById('observacoesMudanca').value = '';
        document.getElementById('camposCondicionais').innerHTML = '';
        
        mostrarView('status');
        
    } catch (error) {
        console.error('🚨 Erro ao abrir view de status:', error);
        showToast('Erro ao abrir formulário de status', 'danger');
    }
}

function abrirViewComentario() {
    try {
        console.log('💬 Abrindo view de comentário');
        
        // Limpar formulário
        document.getElementById('novoComentario').value = '';
        document.getElementById('dataComentario').value = new Date().toISOString().slice(0, 16);
        
        mostrarView('comentario');
        
        // Focar no textarea
        setTimeout(() => {
            document.getElementById('novoComentario').focus();
        }, 300);
        
    } catch (error) {
        console.error('🚨 Erro ao abrir view de comentário:', error);
        showToast('Erro ao abrir formulário de comentário', 'danger');
    }
}

function voltarViewDetalhes() {
    console.log('🔙 Voltando para view de detalhes');
    mostrarView('detalhes');
}

// ===== PREENCHER DADOS DO MODAL =====
function preencherDadosModal() {
    try {
        // Nome da seguradora
        document.getElementById('modalSeguradoraNome').textContent = modalData.nomeSeguradora;
        
        // Status
        const statusContainer = document.getElementById('modalStatus');
        statusContainer.innerHTML = `<span class="badge bg-secondary">${modalData.statusTexto}</span>`;
        
        // Valor do prêmio
        document.getElementById('modalValorPremio').textContent = 
            modalData.valorTexto.includes('R$') ? modalData.valorTexto : 'Não informado';
        
        // Campos placeholder
        document.getElementById('modalValorIs').textContent = 'Não informado';
        document.getElementById('modalDataEnvio').textContent = 'Não enviada';
        
        // Observações
        const observacoesContainer = document.getElementById('modalObservacoes');
        if (modalData.comentarioTexto && modalData.comentarioTexto !== 'Nenhuma observação') {
            observacoesContainer.innerHTML = `
                <div class="p-3 bg-light rounded border">
                    <i class="bi bi-chat-quote text-primary me-2"></i>
                    <div style="white-space: pre-wrap;">${modalData.comentarioTexto}</div>
                </div>
            `;
        } else {
            observacoesContainer.innerHTML = `
                <em class="text-muted">
                    <i class="bi bi-chat-quote me-2"></i>
                    Nenhuma observação registrada
                </em>
            `;
        }
        
        console.log('✅ Dados do modal preenchidos');
        
    } catch (error) {
        console.error('🚨 Erro ao preencher dados do modal:', error);
    }
}

// ===== CARREGAR TIMELINE =====
function carregarTimelineSeguradora(csId) {
    const timelineContainer = document.getElementById('modalTimeline');
    
    if (!timelineContainer) {
        return;
    }
    
    // Loading inicial
    timelineContainer.innerHTML = `
        <div class="timeline-item-modal">
            <div class="timeline-marker-modal bg-info"></div>
            <div class="timeline-content-modal">
                <div class="timeline-meta-modal">Carregando histórico...</div>
                <div class="timeline-text-modal">🔍 Verificando atividades específicas</div>
            </div>
        </div>
    `;
    
    // Tentar buscar API
    fetch(`/cotacao-seguradoras/${csId}/atividades`)
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.atividades.length > 0) {
            let timelineHtml = '';
            
            data.atividades.forEach(atividade => {
                const corMarcador = atividade.cor ? `bg-${atividade.cor}` : 'bg-info';
                const icone = atividade.icone || 'bi-info-circle';
                
                timelineHtml += `
                    <div class="timeline-item-modal">
                        <div class="timeline-marker-modal ${corMarcador}">
                            <i class="${icone}"></i>
                        </div>
                        <div class="timeline-content-modal">
                            <div class="timeline-meta-modal">
                                ${atividade.data_formatada || formatarData(atividade.created_at)}
                                ${atividade.user ? ` - ${atividade.user.name}` : ''}
                            </div>
                            <div class="timeline-text-modal">${atividade.descricao}</div>
                        </div>
                    </div>
                `;
            });
            
            timelineContainer.innerHTML = timelineHtml;
        } else {
            mostrarTimelineVazia();
        }
    })
    .catch(error => {
        mostrarTimelineBasica();
    });
    
    function mostrarTimelineVazia() {
        timelineContainer.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-clock-history fs-2 mb-2"></i>
                <p class="mb-0">Nenhuma atividade específica registrada</p>
                <small>As atividades aparecerão aqui conforme as ações forem realizadas</small>
            </div>
        `;
    }
    
    function mostrarTimelineBasica() {
        timelineContainer.innerHTML = `
            <div class="timeline-item-modal">
                <div class="timeline-marker-modal bg-primary">
                    <i class="bi bi-plus-circle"></i>
                </div>
                <div class="timeline-content-modal">
                    <div class="timeline-meta-modal">Sistema</div>
                    <div class="timeline-text-modal">✅ Seguradora associada à cotação</div>
                </div>
            </div>
            <div class="timeline-item-modal">
                <div class="timeline-marker-modal bg-success">
                    <i class="bi bi-info-circle"></i>
                </div>
                <div class="timeline-content-modal">
                    <div class="timeline-meta-modal">Info</div>
                    <div class="timeline-text-modal">🎯 Timeline será alimentada quando implementar a rota de atividades</div>
                </div>
            </div>
        `;
    }
}

// ===== SALVAR MUDANÇA DE STATUS =====
function salvarMudancaStatus() {
    try {
        const novoStatus = document.getElementById('novoStatus').value;
        const observacoes = document.getElementById('observacoesMudanca').value.trim();
        
        if (!novoStatus) {
            showToast('Selecione o novo status', 'warning');
            return;
        }
        
        if (!observacoes) {
            showToast('Descreva o que aconteceu', 'warning');
            return;
        }
        
        const dados = {
            status: novoStatus,
            observacoes: observacoes,
            data_ocorrencia: document.getElementById('dataOcorrencia').value
        };
        
        // Mostrar loading
        const btnSalvar = event.target;
        const textoOriginal = btnSalvar.innerHTML;
        btnSalvar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Salvando...';
        btnSalvar.disabled = true;
        
        fetch(`/cotacao-seguradoras/${modalData.csId}/status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('Status atualizado com sucesso!', 'success');
                setTimeout(() => {
                    modalSeguradoraDetalhes.hide();
                    location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Erro ao atualizar status');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            if (error.message.includes('404')) {
                showToast('API ainda não implementada. Funcionalidade em desenvolvimento.', 'info');
                voltarViewDetalhes();
            } else {
                showToast('Erro ao conectar com servidor', 'danger');
            }
        })
        .finally(() => {
            btnSalvar.innerHTML = textoOriginal;
            btnSalvar.disabled = false;
        });
        
    } catch (error) {
        console.error('🚨 Erro ao salvar mudança de status:', error);
        showToast('Erro ao salvar status', 'danger');
    }
}

// ===== SALVAR COMENTÁRIO =====
function salvarComentario() {
    try {
        const comentario = document.getElementById('novoComentario').value.trim();
        
        if (!comentario) {
            showToast('Digite um comentário', 'warning');
            return;
        }
        
        // Mostrar loading
        const btnSalvar = event.target;
        const textoOriginal = btnSalvar.innerHTML;
        btnSalvar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Salvando...';
        btnSalvar.disabled = true;
        
        fetch(`/cotacao-seguradoras/${modalData.csId}/observacao`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                observacao: comentario
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('Comentário adicionado com sucesso!', 'success');
                
                // Atualizar observações na view de detalhes
                const observacoesContainer = document.getElementById('modalObservacoes');
                observacoesContainer.innerHTML = `
                    <div class="p-3 bg-light rounded border">
                        <i class="bi bi-chat-quote text-primary me-2"></i>
                        <div style="white-space: pre-wrap;">${data.observacao || comentario}</div>
                    </div>
                `;
                
                // Recarregar timeline
                carregarTimelineSeguradora(modalData.csId);
                
                // Voltar para detalhes
                setTimeout(() => {
                    voltarViewDetalhes();
                }, 1000);
                
            } else {
                throw new Error(data.message || 'Erro ao adicionar comentário');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            if (error.message.includes('404')) {
                showToast('Método não implementado ainda no controller', 'info');
                voltarViewDetalhes();
            } else if (error.message.includes('422')) {
                showToast('Erro de validação. Verifique os dados enviados.', 'warning');
            } else {
                showToast('Erro ao conectar com servidor', 'danger');
            }
        })
        .finally(() => {
            btnSalvar.innerHTML = textoOriginal;
            btnSalvar.disabled = false;
        });
        
    } catch (error) {
        console.error('🚨 Erro ao salvar comentário:', error);
        showToast('Erro ao salvar comentário', 'danger');
    }
}

// ===== CARREGAR CAMPOS CONDICIONAIS (STATUS) =====
function carregarCamposCondicionais(novoStatus) {
    const container = document.getElementById('camposCondicionais');
    let html = '';
    
    switch(novoStatus) {
        case 'aprovada':
            html = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <strong>Ótima notícia!</strong> A seguradora aprovou a cotação.
                </div>
            `;
            break;
            
        case 'rejeitada':
            html = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Cotação rejeitada.</strong> Descreva o motivo nas observações abaixo.
                </div>
            `;
            break;
            
        case 'repique':
            html = `
                <div class="alert alert-info">
                    <i class="bi bi-arrow-clockwise"></i>
                    <strong>Solicitar repique.</strong> A seguradora vai revisar a cotação.
                </div>
            `;
            break;
            
        case 'em_analise':
            html = `
                <div class="alert alert-info">
                    <i class="bi bi-hourglass-split"></i>
                    <strong>Em análise.</strong> A seguradora está analisando a cotação.
                </div>
            `;
            break;
            
        case 'aguardando':
            html = `
                <div class="alert alert-warning">
                    <i class="bi bi-clock"></i>
                    <strong>Aguardando envio.</strong> Cotação será enviada para análise.
                </div>
            `;
            break;
    }
    
    container.innerHTML = html;
}

// ===== COMENTÁRIO GERAL =====
function adicionarComentarioGeral() {
    try {
        document.getElementById('comentarioGeral').value = '';
        new bootstrap.Modal(document.getElementById('modalComentarioGeral')).show();
    } catch (error) {
        console.error('🚨 Erro ao abrir modal de comentário geral:', error);
        showToast('Erro ao abrir comentário geral', 'danger');
    }
}

function salvarComentarioGeral() {
    try {
        const comentario = document.getElementById('comentarioGeral').value.trim();
        
        if (!comentario) {
            showToast('Digite um comentário', 'warning');
            return;
        }
        
        const cotacaoId = document.querySelector('h1').textContent.match(/#(\d+)/)[1];
        
        fetch(`/cotacoes/${cotacaoId}/atividades`, {
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
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
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
            console.log('API não implementada:', error);
            if (error.message.includes('404')) {
                showToast('API ainda não implementada. Funcionalidade disponível em breve.', 'info');
                bootstrap.Modal.getInstance(document.getElementById('modalComentarioGeral')).hide();
            } else {
                showToast('Erro ao conectar com servidor', 'danger');
            }
        });
        
    } catch (error) {
        console.error('🚨 Erro ao salvar comentário geral:', error);
        showToast('Erro ao salvar comentário', 'danger');
    }
}

// ===== HISTÓRICO =====
function abrirHistoricoCompleto() {
    try {
        console.log('🎭 Tentando abrir histórico completo...');
        
        const modalElement = document.getElementById('modalHistoricoCompleto');
        if (!modalElement) {
            console.error('❌ Modal modalHistoricoCompleto não encontrado!');
            showToast('Modal de histórico não encontrado', 'danger');
            return;
        }
        console.log('✅ Modal encontrado');
        
        // Fechar outros modais antes
        const modaisAbertos = document.querySelectorAll('.modal.show');
        modaisAbertos.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                console.log('🔄 Fechando modal aberto:', modal.id);
                modalInstance.hide();
            }
        });
        
        // Aguardar e abrir
        setTimeout(() => {
            try {
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
                console.log('✅ Histórico completo aberto com sucesso!');
            } catch (error) {
                console.error('❌ Erro ao abrir modal:', error);
                showToast('Erro ao abrir histórico: ' + error.message, 'danger');
            }
        }, 300);
        
    } catch (error) {
        console.error('🚨 Erro em abrirHistoricoCompleto:', error);
        showToast('Erro inesperado ao abrir histórico', 'danger');
    }
}

function expandirAtividade(atividadeId) {
    try {
        console.log('🔍 Expandindo atividade:', atividadeId);
        
        const modalElement = document.getElementById('modalDetalheAtividade');
        if (!modalElement) {
            console.error('❌ Modal modalDetalheAtividade não encontrado!');
            showToast('Modal de detalhes não encontrado', 'danger');
            return;
        }
        
        // Buscar dados da atividade
        const conteudoContainer = document.getElementById('conteudoDetalheAtividade');
        
        // Loading inicial
        conteudoContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2 text-muted">Carregando detalhes da atividade...</p>
            </div>
        `;
        
        // Tentar buscar pela API
        fetch(`/atividades/${atividadeId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                preencherDetalheAtividade(data.atividade);
            } else {
                throw new Error('Atividade não encontrada');
            }
        })
        .catch(error => {
            console.log('API não implementada, usando fallback:', error);
            preencherDetalheAtividadeFallback(atividadeId);
        });
        
        // Abrir modal
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();
        
    } catch (error) {
        console.error('🚨 Erro ao expandir atividade:', error);
        showToast('Erro ao abrir detalhes da atividade', 'danger');
    }
}

function preencherDetalheAtividade(atividade) {
    const container = document.getElementById('conteudoDetalheAtividade');
    
    container.innerHTML = `
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Data/Hora</label>
                <div class="bg-light p-2 rounded">
                    <i class="bi bi-calendar3 text-primary me-2"></i>
                    ${atividade.data_formatada || formatarData(atividade.created_at)}
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Responsável</label>
                <div class="bg-light p-2 rounded">
                    <i class="bi bi-person text-primary me-2"></i>
                    ${atividade.user ? atividade.user.name : 'Sistema'}
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Tipo</label>
                <div class="bg-light p-2 rounded">
                    <span class="badge bg-${atividade.tipo === 'geral' ? 'primary' : 'info'}">
                        ${atividade.tipo ? atividade.tipo.charAt(0).toUpperCase() + atividade.tipo.slice(1) : 'Geral'}
                    </span>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Descrição Completa</label>
                <div class="bg-light p-3 rounded border-start border-primary border-3">
                    <div style="white-space: pre-wrap; line-height: 1.6;">
                        ${atividade.descricao || 'Sem descrição disponível'}
                    </div>
                </div>
            </div>
            ${atividade.cotacao_seguradora ? `
                <div class="col-12">
                    <label class="form-label fw-semibold">Seguradora Relacionada</label>
                    <div class="bg-light p-2 rounded">
                        <i class="bi bi-building text-info me-2"></i>
                        ${atividade.cotacao_seguradora.seguradora.nome}
                    </div>
                </div>
            ` : ''}
        </div>
    `;
}

function preencherDetalheAtividadeFallback(atividadeId) {
    const container = document.getElementById('conteudoDetalheAtividade');
    
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="bg-info bg-opacity-10 rounded-3 p-3 d-inline-block mb-3">
                <i class="bi bi-info-circle text-info" style="font-size: 2rem;"></i>
            </div>
            <h5 class="mb-2">Detalhes da Atividade #${atividadeId}</h5>
            <p class="text-muted mb-3">
                A funcionalidade de detalhes expandidos ainda está sendo implementada.
            </p>
            <div class="bg-light rounded p-3">
                <small class="text-muted">
                    <i class="bi bi-code-slash me-1"></i>
                    Rota necessária: <code>GET /atividades/{id}</code>
                </small>
            </div>
        </div>
    `;
}

// ===== OUTRAS FUNCIONALIDADES =====

function exportarHistorico() {
    try {
        console.log('📄 Exportando histórico...');
        
        const cotacaoId = document.querySelector('h1').textContent.match(/#(\d+)/)[1];
        
        fetch(`/cotacoes/${cotacaoId}/historico/export`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `historico-cotacao-${cotacaoId}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            
            showToast('Histórico exportado com sucesso!', 'success');
        })
        .catch(error => {
            console.log('API não implementada:', error);
            showToast('Funcionalidade de exportação ainda não implementada', 'info');
        });
        
    } catch (error) {
        console.error('🚨 Erro ao exportar histórico:', error);
        showToast('Erro ao exportar histórico', 'danger');
    }
}

function finalizarCotacao(novoStatus) {
    try {
        if (!confirm(`Tem certeza que deseja ${novoStatus === 'finalizada' ? 'finalizar' : 'cancelar'} esta cotação?`)) {
            return;
        }
        
        const cotacaoId = document.querySelector('h1').textContent.match(/#(\d+)/)[1];
        
        fetch(`/cotacoes/${cotacaoId}/status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: novoStatus
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(`Cotação ${novoStatus} com sucesso!`, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.log('API não implementada:', error);
            showToast('Funcionalidade ainda não implementada no backend', 'info');
        });
        
    } catch (error) {
        console.error('🚨 Erro ao finalizar cotação:', error);
        showToast('Erro ao alterar status da cotação', 'danger');
    }
}

function duplicarCotacao() {
    try {
        if (!confirm('Deseja criar uma nova cotação baseada nesta?')) {
            return;
        }
        
        const cotacaoId = document.querySelector('h1').textContent.match(/#(\d+)/)[1];
        
        fetch(`/cotacoes/${cotacaoId}/duplicar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('Cotação duplicada com sucesso!', 'success');
                setTimeout(() => {
                    window.location.href = `/cotacoes/${data.nova_cotacao_id}`;
                }, 1500);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.log('API não implementada:', error);
            showToast('Funcionalidade de duplicação ainda não implementada', 'info');
        });
        
    } catch (error) {
        console.error('🚨 Erro ao duplicar cotação:', error);
        showToast('Erro ao duplicar cotação', 'danger');
    }
}

function exportarPDF() {
    try {
        console.log('📄 Gerando PDF...');
        
        const cotacaoId = document.querySelector('h1').textContent.match(/#(\d+)/)[1];
        
        // Abrir em nova aba
        window.open(`/cotacoes/${cotacaoId}/pdf`, '_blank');
        
        showToast('PDF sendo gerado...', 'info');
        
    } catch (error) {
        console.error('🚨 Erro ao gerar PDF:', error);
        showToast('Erro ao gerar PDF', 'danger');
    }
}

function editarObservacoesGerais() {
    try {
        const observacoesAtual = document.querySelector('.observacoes-container .alert')?.textContent.trim() || '';
        
        const novaObservacao = prompt('Editar observações gerais:', observacoesAtual);
        
        if (novaObservacao === null) {
            return; // Cancelou
        }
        
        const cotacaoId = document.querySelector('h1').textContent.match(/#(\d+)/)[1];
        
        fetch(`/cotacoes/${cotacaoId}/observacoes`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                observacoes: novaObservacao
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('Observações atualizadas!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.log('API não implementada:', error);
            showToast('Funcionalidade ainda não implementada', 'info');
        });
        
    } catch (error) {
        console.error('🚨 Erro ao editar observações:', error);
        showToast('Erro ao editar observações', 'danger');
    }
}

function marcarComoEnviada() {
    try {
        if (!confirm('Confirma o envio de todas as cotações pendentes?')) {
            return;
        }
        
        const cotacaoId = document.querySelector('h1').textContent.match(/#(\d+)/)[1];
        
        fetch(`/cotacoes/${cotacaoId}/enviar-todas`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('Todas as cotações foram enviadas!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.log('API não implementada:', error);
            showToast('Funcionalidade ainda não implementada', 'info');
        });
        
    } catch (error) {
        console.error('🚨 Erro ao enviar cotações:', error);
        showToast('Erro ao enviar cotações', 'danger');
    }
}

function adicionarSeguradora() {
    try {
        // Implementar modal de adição de seguradora ou redirecionar
        showToast('Funcionalidade em desenvolvimento', 'info');
        
        // Exemplo de redirecionamento:
        // const cotacaoId = document.querySelector('h1').textContent.match(/#(\d+)/)[1];
        // window.location.href = `/cotacoes/${cotacaoId}/seguradoras/create`;
        
    } catch (error) {
        console.error('🚨 Erro ao adicionar seguradora:', error);
        showToast('Erro ao adicionar seguradora', 'danger');
    }
}

// ===== FUNÇÕES AUXILIARES =====

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatarData(dataString) {
    try {
        const data = new Date(dataString);
        return data.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return dataString;
    }
}

function limparFormularios() {
    // Limpar formulário de status
    const formStatus = document.getElementById('formMudarStatus');
    if (formStatus) {
        formStatus.reset();
    }
    
    // Limpar formulário de comentário
    const formComentario = document.getElementById('formComentario');
    if (formComentario) {
        formComentario.reset();
    }
    
    // Limpar campos condicionais
    const camposCondicionais = document.getElementById('camposCondicionais');
    if (camposCondicionais) {
        camposCondicionais.innerHTML = '';
    }
}

// ===== EVENTOS GLOBAIS =====

// Limpar formulários quando modal fechar
document.addEventListener('hidden.bs.modal', function (event) {
    if (event.target.id === 'modalSeguradoraDetalhes') {
        limparFormularios();
        currentView = 'detalhes';
        mostrarView('detalhes');
    }
});

// Atalhos de teclado
document.addEventListener('keydown', function(event) {
    // ESC para voltar à view anterior no modal
    if (event.key === 'Escape' && currentView !== 'detalhes') {
        const modalAtivo = document.querySelector('.modal.show');
        if (modalAtivo && modalAtivo.id === 'modalSeguradoraDetalhes') {
            voltarViewDetalhes();
            event.preventDefault();
        }
    }
});
function funcionalidadeEmConstrucao() {
    const toastElement = document.getElementById('toastConstrucao');
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 4000  // 4 segundos
    });
    toast.show();
}
console.log('🎉 Script show.blade.php carregado completamente!');
</script>
@endpush