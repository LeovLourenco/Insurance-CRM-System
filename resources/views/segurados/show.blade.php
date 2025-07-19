@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $segurado->nome }}</h1>
        <p class="text-muted mb-0">
            <i class="bi bi-calendar3 me-1"></i>
            Cadastrado em {{ $segurado->created_at->format('d/m/Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('segurados.edit', $segurado) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-2"></i>Editar
        </a>
        <a href="{{ route('segurados.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
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

<!-- Informações do Segurado -->
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="modern-card p-4">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 rounded-circle p-4 d-inline-flex mb-3">
                    <i class="bi bi-person-check text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h4 class="mb-1">{{ $segurado->nome }}</h4>
                <p class="text-muted mb-0">{{ $segurado->tipo_pessoa_texto ?? 'Cliente' }}</p>
            </div>
            
            <hr>
            
            <div class="mb-3">
                <h6 class="small text-muted text-uppercase fw-bold mb-2">Documento</h6>
                @if($segurado->documento)
                    <div class="font-monospace fs-6">{{ $segurado->documento_formatado }}</div>
                    <small class="text-muted">{{ $segurado->tipo_pessoa_texto }}</small>
                @else
                    <span class="text-muted">Não informado</span>
                @endif
            </div>
            
            <div class="mb-3">
                <h6 class="small text-muted text-uppercase fw-bold mb-2">Telefone</h6>
                @if($segurado->telefone)
                    <a href="tel:{{ $segurado->telefone }}" class="text-decoration-none">
                        <i class="bi bi-telephone me-2"></i>{{ $segurado->telefone_formatado }}
                    </a>
                @else
                    <span class="text-muted">Não informado</span>
                @endif
            </div>
            
            <hr>
            
            <div class="row text-center">
                <div class="col-12">
                    <h4 class="text-primary mb-1">{{ $cotacoes->count() }}</h4>
                    <small class="text-muted">Cotações Realizadas</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estatísticas -->
    <div class="col-lg-8">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="modern-card p-4 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-file-earmark-text text-warning fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $cotacoes->count() }}</h5>
                            <p class="text-muted mb-0">Total de Cotações</p>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" 
                             style="width: {{ $cotacoes->count() > 0 ? min(($cotacoes->count() / 10) * 100, 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="modern-card p-4 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-calendar-check text-info fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $segurado->created_at->diffInDays() }}</h5>
                            <p class="text-muted mb-0">Dias como Cliente</p>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" 
                             style="width: {{ min(($segurado->created_at->diffInDays() / 365) * 100, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Status das Cotações -->
        @if($cotacoesPorStatus->count() > 0)
        <div class="modern-card p-4">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-pie-chart me-2"></i>Status das Cotações
            </h6>
            <div class="row">
                @foreach($cotacoesPorStatus as $status => $total)
                    <div class="col-md-4 mb-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-2">
                                @switch($status)
                                    @case('pendente')
                                        <span class="badge bg-warning">{{ $total }}</span>
                                        @break
                                    @case('aprovada')
                                        <span class="badge bg-success">{{ $total }}</span>
                                        @break
                                    @case('rejeitada')
                                        <span class="badge bg-danger">{{ $total }}</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $total }}</span>
                                @endswitch
                            </div>
                            <small class="text-muted">{{ ucfirst($status) }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Cotações Recentes -->
@if($cotacoes->count() > 0)
<div class="modern-card">
    <div class="p-4 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>Cotações Recentes
            </h5>
            <small class="text-muted">Últimas 10 cotações</small>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Produto</th>
                    <th>Seguradora</th>
                    <th>Corretora</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th width="120">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cotacoes as $cotacao)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $cotacao->produto->nome ?? 'N/A' }}</div>
                            @if($cotacao->produto && $cotacao->produto->linha)
                                <small class="text-muted">{{ $cotacao->produto->linha }}</small>
                            @endif
                        </td>
                        <td>{{ $cotacao->seguradora->nome ?? 'N/A' }}</td>
                        <td>{{ $cotacao->corretora->nome ?? 'N/A' }}</td>
                        <td>
                            @switch($cotacao->status ?? 'pendente')
                                @case('aprovada')
                                    <span class="badge bg-success">Aprovada</span>
                                    @break
                                @case('rejeitada')
                                    <span class="badge bg-danger">Rejeitada</span>
                                    @break
                                @default
                                    <span class="badge bg-warning">Pendente</span>
                            @endswitch
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $cotacao->created_at->format('d/m/Y') }}
                            </small>
                        </td>
                        <td>
                            @if(Route::has('cotacoes.show'))
                                <a href="{{ route('cotacoes.show', $cotacao) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Estado vazio se não há cotações -->
@if($cotacoes->count() == 0)
<div class="modern-card p-5 text-center">
    <i class="bi bi-inbox display-1 text-muted"></i>
    <h5 class="mt-3 text-muted">Nenhuma cotação ainda</h5>
    <p class="text-muted">
        Este segurado ainda não possui cotações realizadas.
    </p>
    @if(Route::has('cotacoes.create'))
        <a href="{{ route('cotacoes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nova Cotação
        </a>
    @endif
</div>
@endif

<!-- Informações Adicionais -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="modern-card p-4">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-clock-history me-2"></i>Histórico
            </h6>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker bg-primary"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Cadastro realizado</h6>
                        <p class="text-muted small mb-0">
                            {{ $segurado->created_at->format('d/m/Y \à\s H:i') }}
                        </p>
                    </div>
                </div>
                
                @if($segurado->updated_at != $segurado->created_at)
                <div class="timeline-item">
                    <div class="timeline-marker bg-info"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Última atualização</h6>
                        <p class="text-muted small mb-0">
                            {{ $segurado->updated_at->format('d/m/Y \à\s H:i') }}
                        </p>
                    </div>
                </div>
                @endif
                
                @if($cotacoes->count() > 0)
                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Primeira cotação</h6>
                        <p class="text-muted small mb-0">
                            {{ $cotacoes->sortBy('created_at')->first()->created_at->format('d/m/Y \à\s H:i') }}
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="modern-card p-4">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-info-circle me-2"></i>Resumo
            </h6>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Cliente desde:</span>
                    <strong>{{ $segurado->created_at->format('d/m/Y') }}</strong>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Tipo de pessoa:</span>
                    <strong>{{ $segurado->tipo_pessoa_texto ?? 'Não identificado' }}</strong>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total de cotações:</span>
                    <strong>{{ $cotacoes->count() }}</strong>
                </div>
            </div>
            
            @if($cotacoes->count() > 0)
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Última cotação:</span>
                    <strong>{{ $cotacoes->first()->created_at->format('d/m/Y') }}</strong>
                </div>
            </div>
            @endif
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
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
}

.progress {
    background-color: #f1f5f9;
}

.font-monospace {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 1.5rem;
    bottom: -1.5rem;
    width: 2px;
    background: #e2e8f0;
}

.timeline-marker {
    position: absolute;
    left: -1.75rem;
    top: 0.25rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #e2e8f0;
}

.timeline-content h6 {
    font-size: 0.875rem;
    font-weight: 600;
}
</style>
@endsection