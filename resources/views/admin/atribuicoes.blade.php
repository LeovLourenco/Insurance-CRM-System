@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Mensagens de Feedback --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-person-badge me-2"></i>Atribuições de Comerciais
            </h1>
            <p class="text-muted mb-0">Gerencie qual comercial é responsável por cada corretora</p>
        </div>
    </div>

    {{-- Estatísticas --}}
    <div class="row mb-4">
        @foreach($estatisticas as $comercial => $total)
        <div class="col-md-3 col-lg-2 mb-3">
            <div class="modern-card">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        @if($comercial === 'Sem Responsável')
                            <i class="bi bi-exclamation-triangle text-warning fs-4 me-2"></i>
                        @else
                            <i class="bi bi-person-check text-primary fs-4 me-2"></i>
                        @endif
                    </div>
                    <h3 class="mb-1 {{ $comercial === 'Sem Responsável' ? 'text-warning' : 'text-primary' }}">
                        {{ $total }}
                    </h3>
                    <h6 class="card-subtitle text-muted mb-0">{{ $comercial }}</h6>
                    <small class="text-muted">corretoras</small>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filtros --}}
    <div class="modern-card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>Filtros
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.atribuicoes') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Buscar Corretora</label>
                        <input type="text" name="busca" class="form-control" 
                               placeholder="Digite o nome da corretora..." 
                               value="{{ request('busca') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filtrar por Comercial</label>
                        <select name="comercial" class="form-select">
                            <option value="">Todos os comerciais</option>
                            @foreach($comerciais as $comercial)
                            <option value="{{ $comercial->id }}" 
                                {{ request('comercial') == $comercial->id ? 'selected' : '' }}>
                                {{ $comercial->name }}
                            </option>
                            @endforeach
                            <option value="sem_responsavel" {{ request('comercial') === 'sem_responsavel' ? 'selected' : '' }}>
                                Sem Responsável
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>Filtrar
                            </button>
                            <a href="{{ route('admin.atribuicoes') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela de Atribuições --}}
    <div class="modern-card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>Corretoras e Responsáveis
                <span class="badge bg-light text-dark ms-2">{{ $corretoras->total() }} registros</span>
            </h5>
        </div>
        <div class="card-body p-0">
            @if($corretoras->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="30%">Corretora</th>
                                <th width="20%">Responsável Atual</th>
                                <th width="25%">Alterar Para</th>
                                <th width="25%">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($corretoras as $corretora)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $corretora->nome }}</strong>
                                        @if($corretora->cpf_cnpj)
                                            <br><small class="text-muted">{{ $corretora->cpf_cnpj }}</small>
                                        @endif
                                        @if($corretora->cidade)
                                            <br><small class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $corretora->cidade }}
                                                @if($corretora->estado), {{ $corretora->estado }}@endif
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($corretora->usuario)
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-check text-success me-2"></i>
                                            <div>
                                                <strong>{{ $corretora->usuario->name }}</strong>
                                                <br><small class="text-muted">{{ $corretora->usuario->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Sem Responsável
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.atribuicoes.update', $corretora) }}" 
                                          class="atribuicao-form" data-corretora="{{ $corretora->nome }}">
                                        @csrf
                                        <select name="usuario_id" class="form-select form-select-sm" required>
                                            <option value="">Selecione o responsável...</option>
                                            @foreach($comerciais as $comercial)
                                            <option value="{{ $comercial->id }}" 
                                                {{ $corretora->usuario_id == $comercial->id ? 'selected' : '' }}>
                                                {{ $comercial->name }}
                                                @if($comercial->hasRole('diretor'))
                                                    (Diretor)
                                                @elseif($comercial->hasRole('admin'))
                                                    (Admin)
                                                @else
                                                    (Comercial)
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>
                                </td>
                                <td>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-sm btn-success btn-salvar" 
                                                    title="Salvar alteração">
                                                <i class="bi bi-check"></i> Salvar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-cancelar" 
                                                    onclick="resetForm(this)" title="Cancelar alteração">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 border-top">
                    {{ $corretoras->appends(request()->query())->links() }}
                </div>
            @else
                <div class="p-5 text-center">
                    <i class="bi bi-search display-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhuma corretora encontrada</h5>
                    <p class="text-muted">Ajuste os filtros para encontrar corretoras.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Confirmar alterações
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.atribuicao-form');
    
    forms.forEach(form => {
        const select = form.querySelector('select[name="usuario_id"]');
        const originalValue = select.value;
        const corretoraName = form.dataset.corretora;
        
        form.addEventListener('submit', function(e) {
            const selectedOption = select.options[select.selectedIndex];
            const newResponsavel = selectedOption.text;
            
            if (select.value === originalValue) {
                e.preventDefault();
                alert('Nenhuma alteração foi feita.');
                return false;
            }
            
            const confirmMessage = `Confirma a alteração do responsável pela corretora "${corretoraName}" para "${newResponsavel}"?`;
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Habilitar/desabilitar botão salvar baseado na mudança
        select.addEventListener('change', function() {
            const btnSalvar = form.querySelector('.btn-salvar');
            const btnCancelar = form.querySelector('.btn-cancelar');
            
            if (this.value !== originalValue && this.value !== '') {
                btnSalvar.disabled = false;
                btnCancelar.style.display = 'inline-block';
            } else {
                btnSalvar.disabled = this.value === '';
                btnCancelar.style.display = this.value === originalValue ? 'none' : 'inline-block';
            }
        });
        
        // Estado inicial
        select.dispatchEvent(new Event('change'));
    });
});

function resetForm(button) {
    const form = button.closest('.atribuicao-form');
    const select = form.querySelector('select[name="usuario_id"]');
    const originalValue = select.value;
    
    // Reset para valor original
    select.value = originalValue;
    select.dispatchEvent(new Event('change'));
}
</script>

<style>
.btn-cancelar {
    display: none;
}

.atribuicao-form select:invalid {
    border-color: #dc3545;
}

.table td {
    vertical-align: middle;
}

.modern-card {
    transition: all 0.3s ease;
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
}
</style>
@endsection