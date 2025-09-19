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
        @can('update', $corretora)
            <a href="{{ route('corretoras.edit', $corretora) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-2"></i>Editar
            </a>
        @endcan
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

<!-- Layout Principal -->
<div class="row mb-4">
    <!-- Card Principal: Dados Gerais + Documentação -->
    <div class="col-lg-8">
        <div class="modern-card p-4">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="bi bi-person-badge text-primary fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h4 class="mb-1">{{ $corretora->nome }}</h4>
                    <p class="text-muted mb-0">Informações da Corretora</p>
                </div>
                <div class="d-flex gap-4 text-center">
                    <div>
                        <h5 class="text-primary mb-0">{{ $corretora->seguradoras->count() }}</h5>
                        <small class="text-muted">Seguradoras</small>
                    </div>
                    <div>
                        <h5 class="text-success mb-0">{{ $cotacoes->count() }}</h5>
                        <small class="text-muted">Cotações</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Dados Gerais -->
                <div class="col-md-6">
                    <h6 class="text-uppercase text-muted fw-bold mb-3 border-bottom pb-2">
                        <i class="bi bi-info-circle me-2"></i>Dados Gerais
                    </h6>
                    
                    @if($corretora->estado || $corretora->cidade)
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                            <i class="bi bi-geo-alt text-info"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium mb-1">Localização</div>
                            <div class="text-muted">
                                {{ $corretora->cidade ? $corretora->cidade : '' }}{{ $corretora->cidade && $corretora->estado ? ' - ' : '' }}{{ $corretora->estado ? $corretora->estado : 'Não informado' }}
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($corretora->suc_cpd)
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                            <i class="bi bi-building text-warning"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium mb-1">SUC-CPD</div>
                            <div class="text-muted">{{ $corretora->suc_cpd }}</div>
                        </div>
                    </div>
                    @endif

                    @if($corretora->usuario)
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                            <i class="bi bi-person text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium mb-1">Comercial Responsável</div>
                            <div class="text-muted">{{ $corretora->usuario->name }}</div>
                            <small class="text-muted">{{ $corretora->usuario->email }}</small>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Documentação -->
                <div class="col-md-6">
                    <h6 class="text-uppercase text-muted fw-bold mb-3 border-bottom pb-2">
                        <i class="bi bi-file-text me-2"></i>Documentação
                    </h6>
                    
                    @if($corretora->cpf_cnpj)
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                            <i class="bi bi-card-text text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium mb-1">CPF/CNPJ</div>
                            <div class="text-muted font-monospace">{{ $corretora->cpf_cnpj }}</div>
                        </div>
                    </div>
                    @endif
                    
                    @if($corretora->susep)
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-secondary bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                            <i class="bi bi-shield-check text-secondary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium mb-1">SUSEP</div>
                            <div class="text-muted font-monospace">{{ $corretora->susep }}</div>
                        </div>
                    </div>
                    @endif

                    @if(!$corretora->cpf_cnpj && !$corretora->susep)
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-file-earmark-x display-6 text-muted mb-2"></i>
                        <p class="mb-0">Nenhum documento cadastrado</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Card Lateral: Contatos -->
    <div class="col-lg-4">
        <div class="modern-card p-4 h-100">
            <h6 class="text-uppercase text-muted fw-bold mb-3 border-bottom pb-2">
                <i class="bi bi-telephone me-2"></i>Contatos
            </h6>
            
            @if($corretora->telefone)
            <div class="d-flex align-items-start mb-3">
                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                    <i class="bi bi-telephone text-primary"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-medium mb-1">Telefone</div>
                    <a href="tel:{{ $corretora->telefone }}" class="text-decoration-none">
                        <div class="text-muted">{{ $corretora->telefone_formatado ?? $corretora->telefone }}</div>
                    </a>
                </div>
            </div>
            @endif
            
            @if($corretora->email)
            <div class="d-flex align-items-start mb-3">
                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                    <i class="bi bi-envelope text-info"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-medium mb-1">Email Principal</div>
                    <a href="mailto:{{ $corretora->email }}" class="text-decoration-none">
                        <div class="text-muted text-break">{{ $corretora->email }}</div>
                    </a>
                </div>
            </div>
            @endif
            
            @if($corretora->email1 || $corretora->email2 || $corretora->email3)
            <div class="border-top pt-3">
                <div class="fw-medium mb-2 text-muted">
                    <i class="bi bi-envelope-plus me-2"></i>Emails Adicionais
                </div>
                
                @if($corretora->email1)
                <div class="mb-2">
                    <small class="text-muted d-block">Email 1:</small>
                    <div class="text-break small">{{ $corretora->email1 }}</div>
                </div>
                @endif
                
                @if($corretora->email2)
                <div class="mb-2">
                    <small class="text-muted d-block">Email 2:</small>
                    <div class="text-break small">{{ $corretora->email2 }}</div>
                </div>
                @endif
                
                @if($corretora->email3)
                <div class="mb-2">
                    <small class="text-muted d-block">Email 3:</small>
                    <div class="text-break small">{{ $corretora->email3 }}</div>
                </div>
                @endif
            </div>
            @endif

            @if(!$corretora->telefone && !$corretora->email && !$corretora->email1 && !$corretora->email2 && !$corretora->email3)
            <div class="text-center text-muted py-4">
                <i class="bi bi-telephone-x display-6 text-muted mb-2"></i>
                <p class="mb-0">Nenhum contato cadastrado</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Seguradoras Parceiras -->
@if($seguradoras->count() > 0)
<div class="modern-card mb-4">
    <div class="p-4 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                    <i class="bi bi-building text-success"></i>
                </div>
                <div>
                    <h5 class="mb-0">Seguradoras Parceiras</h5>
                    <small class="text-muted">Parcerias ativas da corretora</small>
                </div>
            </div>
            <span class="badge bg-success bg-opacity-10 text-success fs-6">
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
<div class="modern-card mb-4">
    <div class="p-4 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                    <i class="bi bi-file-earmark-text text-warning"></i>
                </div>
                <div>
                    <h5 class="mb-0">Cotações Recentes</h5>
                    <small class="text-muted">Últimas 10 cotações da corretora</small>
                </div>
            </div>
            
            @if($cotacoesPorStatus->count() > 0)
            <div class="d-flex gap-2">
                @foreach($cotacoesPorStatus as $status => $total)
                    @switch($status)
                        @case('pendente')
                            <span class="badge bg-warning">{{ $total }} Pendente{{ $total > 1 ? 's' : '' }}</span>
                            @break
                        @case('aprovada')
                            <span class="badge bg-success">{{ $total }} Aprovada{{ $total > 1 ? 's' : '' }}</span>
                            @break
                        @case('rejeitada')
                            <span class="badge bg-danger">{{ $total }} Rejeitada{{ $total > 1 ? 's' : '' }}</span>
                            @break
                    @endswitch
                @endforeach
            </div>
            @endif
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


.text-break {
    word-break: break-word !important;
}
</style>
@endsection