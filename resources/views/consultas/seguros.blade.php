@extends('layouts.app')

@section('title', 'Consulta de Relacionamentos')

@section('content')
<div class="container-fluid">
    <!-- Header com título e breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-search me-2"></i>Consulta de Relacionamentos
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Consultas</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-funnel me-2"></i>Filtros de Consulta
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('consultas.buscar') }}" method="POST" id="consultaForm">
                @csrf
                <div class="row">
                    <div class="col-md-5">
                        <label for="corretora_id" class="form-label">
                            <i class="bi bi-building me-1"></i>Corretora
                        </label>
                        <select name="corretora_id" id="corretora_id" class="form-select">
                            <option value="">Selecione uma corretora (opcional)</option>
                            @foreach($corretoras as $c)
                                <option value="{{ $c->id }}" {{ (isset($corretoraId) && $corretoraId == $c->id) ? 'selected' : '' }}>
                                    {{ $c->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-5">
                        <label for="produto_id" class="form-label">
                            <i class="bi bi-box me-1"></i>Produto
                        </label>
                        <select name="produto_id" id="produto_id" class="form-select">
                            <option value="">Selecione um produto (opcional)</option>
                            @foreach($produtos as $p)
                                <option value="{{ $p->id }}" {{ (isset($produtoId) && $produtoId == $p->id) ? 'selected' : '' }}>
                                    {{ $p->nome }}
                                    @if($p->linha)
                                        <span class="text-muted">({{ $p->linha }})</span>
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-grid gap-2 w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>Consultar
                            </button>
                            <a href="{{ route('consultas.seguros') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Limpar
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Selecione pelo menos uma corretora ou produto para realizar a consulta
                    </small>
                </div>
            </form>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Resultados -->
    @if(isset($seguradoras))
        <!-- Card de Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Seguradoras Encontradas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $estatisticas['total_seguradoras'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-shield-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Tipo de Consulta
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">
                                    @if($estatisticas['tipo_consulta'] == 'ambos')
                                        <i class="bi bi-intersect text-info"></i> Corretora + Produto
                                    @elseif($estatisticas['tipo_consulta'] == 'corretora')
                                        <i class="bi bi-building text-info"></i> Apenas Corretora
                                    @else
                                        <i class="bi bi-box text-info"></i> Apenas Produto
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if(isset($corretora))
            <div class="col-md-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Corretora Selecionada
                                </div>
                                <div class="small font-weight-bold text-gray-800">
                                    {{ Str::limit($corretora->nome, 20) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            @if(isset($produto))
            <div class="col-md-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Produto Selecionado
                                </div>
                                <div class="small font-weight-bold text-gray-800">
                                    {{ Str::limit($produto->nome, 20) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-box fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Card de Resultados -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-list-ul me-2"></i>Resultados da Consulta
                    <span class="badge bg-primary ms-2">{{ $seguradoras->count() }}</span>
                </h6>
            </div>
            <div class="card-body">
                @if($seguradoras->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-shield-check me-1"></i>Seguradora</th>
                                    @if($estatisticas['tipo_consulta'] != 'corretora')
                                        <th><i class="bi bi-building me-1"></i>Corretoras Parceiras</th>
                                    @endif
                                    @if($estatisticas['tipo_consulta'] != 'produto')
                                        <th><i class="bi bi-box me-1"></i>Produtos Oferecidos</th>
                                    @endif
                                    <th><i class="bi bi-info-circle me-1"></i>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($seguradoras as $seguradora)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    {{ substr($seguradora->nome, 0, 1) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $seguradora->nome }}</strong>
                                                    @if($seguradora->site)
                                                        <br><small class="text-muted">
                                                            <i class="bi bi-globe me-1"></i>
                                                            <a href="{{ $seguradora->site }}" target="_blank" class="text-decoration-none">
                                                                {{ $seguradora->site_formatado }}
                                                            </a>
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        
                                        @if($estatisticas['tipo_consulta'] != 'corretora')
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @forelse($seguradora->corretoras->take(3) as $c)
                                                        <span class="badge bg-info text-dark">{{ $c->nome }}</span>
                                                    @empty
                                                        <span class="text-muted">Nenhuma</span>
                                                    @endforelse
                                                    @if($seguradora->corretoras->count() > 3)
                                                        <span class="badge bg-secondary">+{{ $seguradora->corretoras->count() - 3 }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                        
                                        @if($estatisticas['tipo_consulta'] != 'produto')
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @forelse($seguradora->produtos->take(3) as $p)
                                                        <span class="badge bg-success">{{ $p->nome }}</span>
                                                    @empty
                                                        <span class="text-muted">Nenhum</span>
                                                    @endforelse
                                                    @if($seguradora->produtos->count() > 3)
                                                        <span class="badge bg-secondary">+{{ $seguradora->produtos->count() - 3 }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                        
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('seguradoras.show', $seguradora->id) }}" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye me-1"></i>Ver Detalhes
                                                </a>
                                                <button type="button" class="btn btn-outline-info btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalRelacionamentos{{ $seguradora->id }}">
                                                    <i class="bi bi-diagram-3 me-1"></i>Relacionamentos
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">Nenhuma seguradora encontrada</h5>
                        <p class="text-muted">
                            Não foram encontradas seguradoras que atendam aos critérios selecionados.
                            <br>Tente alterar os filtros ou verificar os relacionamentos cadastrados.
                        </p>
                        <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Nova Consulta
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Modais de Relacionamentos -->
        @foreach($seguradoras as $seguradora)
            <div class="modal fade" id="modalRelacionamentos{{ $seguradora->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-diagram-3 me-2"></i>
                                Relacionamentos - {{ $seguradora->nome }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">
                                        <i class="bi bi-building me-1"></i>Corretoras Parceiras ({{ $seguradora->corretoras->count() }})
                                    </h6>
                                    <div class="list-group list-group-flush">
                                        @forelse($seguradora->corretoras as $c)
                                            <div class="list-group-item border-0 px-0">
                                                <i class="bi bi-check-circle text-success me-2"></i>{{ $c->nome }}
                                            </div>
                                        @empty
                                            <div class="text-muted">Nenhuma corretora cadastrada</div>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-success">
                                        <i class="bi bi-box me-1"></i>Produtos Oferecidos ({{ $seguradora->produtos->count() }})
                                    </h6>
                                    <div class="list-group list-group-flush">
                                        @forelse($seguradora->produtos as $p)
                                            <div class="list-group-item border-0 px-0">
                                                <i class="bi bi-check-circle text-success me-2"></i>
                                                {{ $p->nome }}
                                                @if($p->linha)
                                                    <small class="text-muted">({{ $p->linha }})</small>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-muted">Nenhum produto cadastrado</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.avatar-sm {
    width: 2rem;
    height: 2rem;
    font-size: 0.875rem;
}
</style>

@endsection