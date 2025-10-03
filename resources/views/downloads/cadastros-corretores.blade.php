@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Page Header -->
    <div class="col-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-download me-2"></i>Downloads de Cadastros
                    <span class="badge bg-danger ms-2">Admin</span>
                </h1>
                <p class="text-muted mb-0">
                    Baixe relatórios em CSV dos cadastros de corretores
                </p>
            </div>
            <div>
                <a href="{{ route('admin.downloads-cadastros.csv', request()->all()) }}" 
                   class="btn btn-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Baixar CSV Completo
                </a>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="col-md-3 mb-4">
        <div class="modern-card p-4 h-100">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                    <i class="bi bi-collection text-primary fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($estatisticas['total']) }}</h3>
                    <p class="text-muted mb-0 small">Total de Cadastros</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="modern-card p-4 h-100">
            <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                    <i class="bi bi-calendar-check text-success fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($estatisticas['hoje']) }}</h3>
                    <p class="text-muted mb-0 small">Hoje</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="modern-card p-4 h-100">
            <div class="d-flex align-items-center">
                <div class="bg-info bg-opacity-10 p-3 rounded-3 me-3">
                    <i class="bi bi-calendar-week text-info fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($estatisticas['semana']) }}</h3>
                    <p class="text-muted mb-0 small">Esta Semana</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="modern-card p-4 h-100">
            <div class="d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                    <i class="bi bi-calendar-month text-warning fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($estatisticas['mes']) }}</h3>
                    <p class="text-muted mb-0 small">Este Mês</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="col-12 mb-4">
        <div class="modern-card p-4">
            <h5 class="mb-3">
                <i class="bi bi-funnel me-2"></i>Filtros de Busca
            </h5>
            
            <form method="GET" action="{{ route('admin.downloads-cadastros') }}" id="filtroForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="data_inicio" class="form-label">Data Início</label>
                        <input type="date" 
                               class="form-control" 
                               id="data_inicio" 
                               name="data_inicio"
                               value="{{ $filtros['data_inicio'] }}">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="data_fim" class="form-label">Data Fim</label>
                        <input type="date" 
                               class="form-control" 
                               id="data_fim" 
                               name="data_fim"
                               value="{{ $filtros['data_fim'] }}">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="corretora" class="form-label">Corretora</label>
                        <input type="text" 
                               class="form-control" 
                               id="corretora" 
                               name="corretora"
                               placeholder="Nome da corretora..."
                               value="{{ $filtros['corretora'] }}">
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin.downloads-cadastros') }}" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-arrow-clockwise me-1"></i>Limpar
                        </a>
                        <a href="{{ route('admin.downloads-cadastros.csv', request()->all()) }}" 
                           class="btn btn-success ms-2">
                            <i class="bi bi-download me-1"></i>Baixar Filtrados
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Cadastros -->
    <div class="col-12">
        <div class="modern-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="bi bi-table me-2"></i>Cadastros de Corretores
                    <span class="badge bg-secondary ms-2">{{ $cadastros->total() }} registros</span>
                </h5>
            </div>
            
            @if($cadastros->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="150">Data/Hora</th>
                                <th>Corretora</th>
                                <th width="150">CNPJ</th>
                                <th>Email</th>
                                <th>Responsável</th>
                                <th width="130">Telefone</th>
                                <th>Seguradoras</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cadastros as $cadastro)
                                <tr>
                                    <td>
                                        <div class="small">
                                            <div class="fw-medium">{{ $cadastro->data_hora->format('d/m/Y') }}</div>
                                            <div class="text-muted">{{ $cadastro->data_hora->format('H:i:s') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $cadastro->corretora }}</div>
                                    </td>
                                    <td>
                                        <span class="font-monospace small">
                                            {{ $cadastro->cnpj_formatado ?? $cadastro->cnpj ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($cadastro->email)
                                            <a href="mailto:{{ $cadastro->email }}" class="text-decoration-none">
                                                {{ $cadastro->email }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $cadastro->responsavel ?? 'N/A' }}</td>
                                    <td>
                                        @if($cadastro->telefone)
                                            <span class="font-monospace small">
                                                {{ $cadastro->telefone_formatado ?? $cadastro->telefone }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($cadastro->seguradoras)
                                            <span class="small">{{ $cadastro->seguradoras_formatada }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Mostrando {{ $cadastros->firstItem() }} a {{ $cadastros->lastItem() }} 
                        de {{ number_format($cadastros->total()) }} registros
                    </div>
                    <div>
                        {{ $cadastros->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <h5 class="mt-3 text-muted">Nenhum cadastro encontrado</h5>
                    <p class="text-muted">
                        @if(array_filter($filtros))
                            Tente ajustar os filtros para encontrar os cadastros desejados.
                        @else
                            Não há cadastros de corretores na base de dados.
                        @endif
                    </p>
                    @if(array_filter($filtros))
                        <a href="{{ route('admin.downloads-cadastros') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-clockwise me-1"></i>Limpar Filtros
                        </a>
                    @endif
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
    transform: translateY(-2px);
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    color: #64748b;
    border-bottom: 2px solid #f1f5f9;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
}

.font-monospace {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
}

.badge {
    font-size: 0.75rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .modern-card {
        margin-bottom: 1rem;
    }
}
</style>

<script>
// Auto-submit form quando filtros mudam (opcional)
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filtroForm');
    const selects = form.querySelectorAll('select');
    const dates = form.querySelectorAll('input[type="date"]');
    
    // Auto-submit em mudança de select e data (opcional)
    // selects.forEach(select => {
    //     select.addEventListener('change', () => form.submit());
    // });
    
    // dates.forEach(date => {
    //     date.addEventListener('change', () => form.submit());
    // });
});
</script>
@endsection