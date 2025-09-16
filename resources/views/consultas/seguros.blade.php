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
                        <label for="corretora_search" class="form-label">
                            <i class="bi bi-building me-1"></i>Corretora
                        </label>
                        <div class="position-relative">
                            <input type="text" 
                                   id="corretora_search" 
                                   name="corretora_search"
                                   class="form-control" 
                                   placeholder="Digite o nome da corretora..."
                                   autocomplete="off"
                                   value="{{ isset($corretora) ? $corretora->nome : '' }}">
                            <input type="hidden" name="corretora_id" id="corretora_id" value="{{ $corretoraId ?? '' }}">
                            <div id="corretora_suggestions" class="autocomplete-suggestions"></div>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <label for="produto_search" class="form-label">
                            <i class="bi bi-box me-1"></i>Produto
                        </label>
                        <div class="position-relative">
                            <input type="text" 
                                   id="produto_search" 
                                   name="produto_search"
                                   class="form-control" 
                                   placeholder="Digite o nome do produto..."
                                   autocomplete="off"
                                   value="{{ isset($produto) ? $produto->nome : '' }}">
                            <input type="hidden" name="produto_id" id="produto_id" value="{{ $produtoId ?? '' }}">
                            <div id="produto_suggestions" class="autocomplete-suggestions"></div>
                        </div>
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

/* Autocomplete Styles */
.autocomplete-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    display: none;
}

.autocomplete-suggestion {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.15s ease-in-out;
}

.autocomplete-suggestion:hover,
.autocomplete-suggestion.active {
    background-color: #f8f9fa;
}

.autocomplete-suggestion:last-child {
    border-bottom: none;
}

.autocomplete-suggestion .text-muted {
    font-size: 0.875rem;
}

.form-control:focus + .autocomplete-suggestions {
    border-color: #86b7fe;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dados para autocomplete
    const corretoras = @json($corretoras->map(function($c) { return ['id' => $c->id, 'nome' => $c->nome]; }));
    const produtos = @json($produtos->map(function($p) { return ['id' => $p->id, 'nome' => $p->nome, 'linha' => $p->linha]; }));

    // Função genérica para autocomplete
    function setupAutocomplete(inputId, hiddenId, suggestionsId, data, formatFunction) {
        const input = document.getElementById(inputId);
        const hiddenInput = document.getElementById(hiddenId);
        const suggestions = document.getElementById(suggestionsId);
        let activeIndex = -1;

        input.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            hiddenInput.value = '';
            
            if (query.length < 2) {
                suggestions.style.display = 'none';
                return;
            }

            const filteredData = data.filter(item => 
                item.nome.toLowerCase().includes(query)
            ).slice(0, 10); // Limitar a 10 resultados

            if (filteredData.length === 0) {
                suggestions.style.display = 'none';
                return;
            }

            suggestions.innerHTML = '';
            filteredData.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'autocomplete-suggestion';
                div.innerHTML = formatFunction(item, query);
                div.addEventListener('click', function() {
                    input.value = item.nome;
                    hiddenInput.value = item.id;
                    suggestions.style.display = 'none';
                });
                suggestions.appendChild(div);
            });

            suggestions.style.display = 'block';
            activeIndex = -1;
        });

        input.addEventListener('keydown', function(e) {
            const suggestionItems = suggestions.querySelectorAll('.autocomplete-suggestion');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, suggestionItems.length - 1);
                updateActiveItem(suggestionItems);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, -1);
                updateActiveItem(suggestionItems);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && suggestionItems[activeIndex]) {
                    suggestionItems[activeIndex].click();
                }
            } else if (e.key === 'Escape') {
                suggestions.style.display = 'none';
                activeIndex = -1;
            }
        });

        function updateActiveItem(items) {
            items.forEach((item, index) => {
                item.classList.toggle('active', index === activeIndex);
            });
        }

        // Fechar sugestões ao clicar fora
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.style.display = 'none';
            }
        });
    }

    // Função para destacar texto correspondente
    function highlightMatch(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }

    // Configurar autocomplete para corretoras
    setupAutocomplete(
        'corretora_search', 
        'corretora_id', 
        'corretora_suggestions', 
        corretoras,
        function(item, query) {
            return `<div>${highlightMatch(item.nome, query)}</div>`;
        }
    );

    // Configurar autocomplete para produtos
    setupAutocomplete(
        'produto_search', 
        'produto_id', 
        'produto_suggestions', 
        produtos,
        function(item, query) {
            let html = `<div>${highlightMatch(item.nome, query)}`;
            if (item.linha) {
                html += ` <span class="text-muted">(${item.linha})</span>`;
            }
            html += '</div>';
            return html;
        }
    );

    // Validar formulário antes do envio
    document.getElementById('consultaForm').addEventListener('submit', function(e) {
        const corretoraId = document.getElementById('corretora_id').value;
        const produtoId = document.getElementById('produto_id').value;

        if (!corretoraId && !produtoId) {
            e.preventDefault();
            alert('Selecione pelo menos uma corretora ou produto para realizar a consulta.');
            return false;
        }
    });
});
</script>

@endsection