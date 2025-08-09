@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $corretora->nome }}</h1>
        <p class="text-muted mb-0">
            <i class="bi bi-calendar3 me-1"></i>
            Criada em {{ $corretora->created_at->format('d/m/Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('corretoras.edit', $corretora) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-2"></i>Editar
        </a>
        <a href="{{ route('corretoras.index') }}" class="btn btn-outline-secondary">
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

<!-- Informações da Corretora -->
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="modern-card p-4">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 rounded-circle p-4 d-inline-flex mb-3">
                    <i class="bi bi-person-badge text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h4 class="mb-1">{{ $corretora->nome }}</h4>
                <p class="text-muted mb-0">Corretora</p>
            </div>
            
            <hr>
            
            <div class="mb-3">
                <h6 class="small text-muted text-uppercase fw-bold mb-2">Email</h6>
                @if($corretora->email)
                    <a href="mailto:{{ $corretora->email }}" class="text-decoration-none">
                        <i class="bi bi-envelope me-2"></i>{{ $corretora->email }}
                    </a>
                @else
                    <span class="text-muted">Não informado</span>
                @endif
            </div>
            
            <div class="mb-3">
                <h6 class="small text-muted text-uppercase fw-bold mb-2">Telefone</h6>
                @if($corretora->telefone)
                    <a href="tel:{{ $corretora->telefone }}" class="text-decoration-none">
                        <i class="bi bi-telephone me-2"></i>{{ $corretora->telefone_formatado }}
                    </a>
                @else
                    <span class="text-muted">Não informado</span>
                @endif
            </div>
            
            <hr>
            
            <div class="row text-center">
                <div class="col-6">
                    <div class="border-end">
                        <h4 class="text-primary mb-1">{{ $corretora->seguradoras->count() }}</h4>
                        <small class="text-muted">Seguradoras</small>
                    </div>
                </div>
                <div class="col-6">
                    <h4 class="text-success mb-1">{{ $cotacoes->count() }}</h4>
                    <small class="text-muted">Cotações</small>
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
                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-building text-success fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $corretora->seguradoras->count() }}</h5>
                            <p class="text-muted mb-0">Seguradoras Parceiras</p>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" 
                             style="width: {{ $corretora->seguradoras->count() > 0 ? min(($corretora->seguradoras->count() / 10) * 100, 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="modern-card p-4 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-file-earmark-text text-warning fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $cotacoes->count() }}</h5>
                            <p class="text-muted mb-0">Cotações Realizadas</p>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" 
                             style="width: {{ $cotacoes->count() > 0 ? min(($cotacoes->count() / 50) * 100, 100) : 0 }}%"></div>
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
                    <div class="col-md-3 mb-2">
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

<!-- Seguradoras Parceiras -->
@if($seguradoras->count() > 0)
<div class="modern-card mb-4">
    <div class="p-4 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-building me-2"></i>Seguradoras Parceiras
            </h5>
            <span class="badge bg-primary bg-opacity-10 text-primary">
                {{ $seguradoras->total() }} {{ $seguradoras->total() == 1 ? 'parceria' : 'parcerias' }}
            </span>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Seguradora</th>
                    <th>Site</th>
                    <th>Produtos</th>
                    <th>Parceria desde</th>
                    <th width="120">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($seguradoras as $seguradora)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="bi bi-building text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $seguradora->nome }}</div>
                                    @if($seguradora->observacoes)
                                        <small class="text-muted">
                                            {{ Str::limit($seguradora->observacoes, 50) }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($seguradora->site)
                                <a href="{{ $seguradora->site }}" 
                                   target="_blank" 
                                   class="text-decoration-none">
                                    <i class="bi bi-globe me-1"></i>
                                    {{ $seguradora->site_formatado }}
                                    <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                {{ $seguradora->produtos_count ?? 0 }}
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($seguradora->pivot->created_at)->format('d/m/Y') }}
                            </small>
                        </td>
                        <td>
                            <a href="{{ route('seguradoras.show', $seguradora) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    @if($seguradoras->hasPages())
        <div class="p-4 border-top">
            {{ $seguradoras->links() }}
        </div>
    @endif
</div>
@endif

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
                    <th>Segurado</th>
                    <th>Produto</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th width="120">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cotacoes as $cotacao)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $cotacao->segurado->nome ?? 'N/A' }}</div>
                        </td>
                        <td>{{ $cotacao->produto->nome ?? 'N/A' }}</td>
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
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Estado vazio se não há seguradoras nem cotações -->
@if($seguradoras->count() == 0 && $cotacoes->count() == 0)
<div class="modern-card p-5 text-center">
    <i class="bi bi-inbox display-1 text-muted"></i>
    <h5 class="mt-3 text-muted">Nenhuma atividade ainda</h5>
    <p class="text-muted">
        Esta corretora ainda não possui seguradoras parceiras ou cotações realizadas.
    </p>
    <a href="{{ route('corretoras.edit', $corretora) }}" class="btn btn-primary">
        <i class="bi bi-pencil me-2"></i>Editar Corretora
    </a>
</div>
@endif

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
</style>
@endsection