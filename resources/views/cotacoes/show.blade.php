@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-eye me-2"></i>Cotação #{{ $cotacao->id }}
            </h1>
            <p class="text-muted mb-0">
                Status: <span class="badge bg-primary">{{ $cotacao->status }}</span>
            </p>
        </div>
        <div>
            <a href="{{ route('cotacoes.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <a href="{{ route('cotacoes.edit', $cotacao->id) }}" class="btn btn-primary ms-2">
                <i class="bi bi-pencil me-1"></i>Editar
            </a>
            @if($cotacao->isPendente())
                <button class="btn btn-success ms-2" onclick="enviarTodas()">
                    <i class="bi bi-send me-1"></i>Enviar Todas
                </button>
            @endif
        </div>
    </div>
    </div>

    <div class="row">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <!-- Informações da Cotação -->
            <div class="modern-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Informações da Cotação
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Segurado:</label>
                            <div class="info-box">
                                <i class="bi bi-person-check text-primary me-2"></i>
                                {{ $cotacao->segurado->nome ?? 'N/A' }}
                                @if($cotacao->segurado && $cotacao->segurado->email)
                                    <br><small class="text-muted">{{ $cotacao->segurado->email }}</small>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Corretora:</label>
                            <div class="info-box">
                                <i class="bi bi-person-badge text-primary me-2"></i>
                                {{ $cotacao->corretora->nome ?? 'N/A' }}
                                @if($cotacao->corretora && $cotacao->corretora->codigo)
                                    <br><small class="text-muted">Código: {{ $cotacao->corretora->codigo }}</small>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Produto:</label>
                            <div class="info-box">
                                <i class="bi bi-box-seam text-primary me-2"></i>
                                {{ $cotacao->produto->nome ?? 'N/A' }}
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Data de Criação:</label>
                            <div class="info-box">
                                <i class="bi bi-calendar3 text-primary me-2"></i>
                                @if($cotacao->created_at)
                                    {{ $cotacao->created_at->format('d/m/Y H:i') }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        
                        @if($cotacao->observacoes)
                            <div class="col-12">
                                <label class="form-label fw-bold">Observações Gerais:</label>
                                <div class="info-box">
                                    <i class="bi bi-chat-text text-primary me-2"></i>
                                    {{ $cotacao->observacoes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Seguradoras Cotadas -->
            <div class="modern-card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>Seguradoras Cotadas ({{ $cotacao->cotacaoSeguradoras->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @foreach($cotacao->cotacaoSeguradoras as $cs)
                        <div class="seguradora-card border-bottom p-3">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="seguradora-avatar">
                                            {{ substr($cs->seguradora->nome, 0, 2) }}
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">{{ $cs->seguradora->nome }}</h6>
                                            <small class="text-muted">ID: {{ $cs->seguradora->id }}</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <span class="badge bg-primary">{{ $cs->status }}</span>
                                </div>
                                
                                <div class="col-md-3">
                                    @if($cs->data_envio)
                                        <small class="text-success">
                                            <i class="bi bi-send me-1"></i>
                                            {{ $cs->data_envio->format('d/m H:i') }}
                                        </small>
                                    @else
                                        <span class="text-muted">Não enviado</span>
                                    @endif
                                </div>
                                
                                <div class="col-md-3">
                                    @if($cs->valor_premio)
                                        <strong class="text-success">
                                            R$ {{ number_format($cs->valor_premio, 2, ',', '.') }}
                                        </strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($cs->observacoes)
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-chat-dots me-1"></i>{{ $cs->observacoes }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            <!-- Métricas -->
            <div class="modern-card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Métricas Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="metric-item">
                        <span>Total de Seguradoras:</span>
                        <strong>{{ $cotacao->cotacaoSeguradoras->count() }}</strong>
                    </div>
                    <hr>
                    <div class="metric-item">
                        <span>Aprovadas:</span>
                        <strong class="text-success">
                            {{ $cotacao->cotacaoSeguradoras->where('status', 'aprovada')->count() }}
                        </strong>
                    </div>
                    <hr>
                    <div class="metric-item">
                        <span>Pendentes:</span>
                        <strong class="text-warning">
                            {{ $cotacao->cotacaoSeguradoras->whereIn('status', ['aguardando', 'em_analise'])->count() }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="modern-card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Ações Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-send me-1"></i>Enviar Todas
                    </button>
                    <button class="btn btn-outline-secondary w-100 mb-2">
                        <i class="bi bi-download me-1"></i>Exportar
                    </button>
                    <button class="btn btn-outline-info w-100">
                        <i class="bi bi-pencil me-1"></i>Editar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.info-box {
    background: #f8fafc;
    padding: 0.75rem;
    border-radius: 0.5rem;
    border-left: 3px solid #3b82f6;
}

.seguradora-avatar {
    width: 40px;
    height: 40px;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}

.seguradora-card:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.02);
}

.metric-item {
    display: flex;
    justify-content: between;
    align-items: center;
}

.metric-item span {
    flex: 1;
}
</style>
@endpush
@endsection