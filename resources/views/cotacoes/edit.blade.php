@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-pencil-square me-2"></i>Editar Cotação #{{ $cotacao->id }}
            </h1>
            <p class="text-muted mb-0">
                Modifique os dados da cotação e gerencie as seguradoras
            </p>
        </div>
        <div>
            <a href="{{ route('cotacoes.show', $cotacao->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>
    </div>

    <!-- Formulário Principal -->
    <form method="POST" action="{{ route('cotacoes.update', $cotacao->id) }}">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Coluna Principal -->
            <div class="col-lg-8">
                <!-- Informações Básicas -->
                <div class="modern-card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>Informações da Cotação
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Corretora:</label>
                                <div class="info-box-readonly">
                                    <i class="bi bi-person-badge text-muted me-2"></i>
                                    {{ $cotacao->corretora->nome }}
                                    <small class="text-muted d-block">Não pode ser alterada</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Produto:</label>
                                <div class="info-box-readonly">
                                    <i class="bi bi-box-seam text-muted me-2"></i>
                                    {{ $cotacao->produto->nome }}
                                    <small class="text-muted d-block">Não pode ser alterado</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="segurado_id" class="form-label fw-bold">
                                    <i class="bi bi-person-check me-1"></i>Segurado: *
                                </label>
                                <select name="segurado_id" id="segurado_id" class="form-select @error('segurado_id') is-invalid @enderror" required>
                                    @foreach($segurados as $segurado)
                                        <option value="{{ $segurado->id }}" {{ $cotacao->segurado_id == $segurado->id ? 'selected' : '' }}>
                                            {{ $segurado->nome }}
                                            @if($segurado->cpf_cnpj)
                                                - {{ $segurado->cpf_cnpj }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('segurado_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-bold">
                                    <i class="bi bi-flag me-1"></i>Status Geral: *
                                </label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="em_andamento" {{ $cotacao->status == 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                                    <option value="finalizada" {{ $cotacao->status == 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                                    <option value="cancelada" {{ $cotacao->status == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label for="observacoes" class="form-label fw-bold">
                                    <i class="bi bi-chat-text me-1"></i>Observações Gerais:
                                </label>
                                <textarea name="observacoes" id="observacoes" rows="4" 
                                          class="form-control @error('observacoes') is-invalid @enderror"
                                          placeholder="Informações adicionais sobre a cotação...">{{ old('observacoes', $cotacao->observacoes) }}</textarea>
                                @error('observacoes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seguradoras Atuais -->
                <div class="modern-card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-building me-2"></i>Seguradoras Cotadas ({{ $cotacao->cotacaoSeguradoras->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($cotacao->cotacaoSeguradoras->count() > 0)
                            @foreach($cotacao->cotacaoSeguradoras as $cs)
                                <div class="seguradora-item border rounded p-3 mb-3">
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
                                        
                                        <div class="col-md-3">
                                            <span class="badge bg-{{ $cs->status == 'aprovada' ? 'success' : ($cs->status == 'rejeitada' ? 'danger' : 'primary') }}">
                                                {{ $cs->status }}
                                            </span>
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
                                        
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="remover_seguradoras[]" 
                                                       value="{{ $cs->id }}" 
                                                       id="remover_{{ $cs->id }}">
                                                <label class="form-check-label text-danger" for="remover_{{ $cs->id }}">
                                                    <small>Remover</small>
                                                </label>
                                            </div>
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
                        @else
                            <p class="text-muted text-center py-3">Nenhuma seguradora cotada ainda.</p>
                        @endif
                    </div>
                </div>

                <!-- Adicionar Seguradoras -->
                @if($seguradoresDisponiveis->count() > 0)
                    <div class="modern-card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-plus-circle me-2"></i>Adicionar Seguradoras
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Selecione as seguradoras que deseja adicionar à cotação:</p>
                            
                            <div class="row">
                                @foreach($seguradoresDisponiveis as $seguradora)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card seguradora-card h-100">
                                            <div class="card-body p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="novas_seguradoras[]" 
                                                           value="{{ $seguradora->id }}" 
                                                           id="nova_{{ $seguradora->id }}">
                                                    <label class="form-check-label w-100" for="nova_{{ $seguradora->id }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="seguradora-avatar seguradora-avatar-sm me-2">
                                                                {{ substr($seguradora->nome, 0, 2) }}
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $seguradora->nome }}</h6>
                                                                <small class="text-muted">ID: {{ $seguradora->id }}</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="selecionarTodasNovas()">
                                    <i class="bi bi-check-all me-1"></i>Selecionar Todas
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-1" onclick="deselecionarTodasNovas()">
                                    <i class="bi bi-x-circle me-1"></i>Limpar Seleção
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Coluna Lateral -->
            <div class="col-lg-4">
                <!-- Resumo das Alterações -->
                <div class="modern-card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>Resumo das Alterações
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="resumo-alteracoes">
                            <p class="text-muted">Nenhuma alteração ainda.</p>
                        </div>
                    </div>
                </div>

                <!-- Informações da Cotação Original -->
                <div class="modern-card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>Informações Originais
                        </h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Criada em:</strong><br>
                            {{ $cotacao->created_at->format('d/m/Y H:i') }}<br><br>
                            
                            <strong>Segurado Original:</strong><br>
                            {{ $cotacao->segurado->nome }}<br><br>
                            
                            <strong>Total de Seguradoras:</strong><br>
                            {{ $cotacao->cotacaoSeguradoras->count() }}
                        </small>
                    </div>
                </div>

                <!-- Ações -->
                <div class="modern-card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-check-circle me-1"></i>Salvar Alterações
                        </button>
                        <a href="{{ route('cotacoes.show', $cotacao->id) }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
.info-box-readonly {
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.5rem;
    border-left: 3px solid #6c757d;
    color: #6c757d;
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

.seguradora-avatar-sm {
    width: 30px;
    height: 30px;
    font-size: 0.7rem;
}

.seguradora-item {
    transition: all 0.2s ease;
}

.seguradora-item:hover {
    background-color: rgba(var(--bs-danger-rgb), 0.05);
}

.seguradora-card {
    transition: all 0.2s ease;
    cursor: pointer;
}

.seguradora-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.seguradora-card .form-check-input:checked ~ .form-check-label {
    color: var(--bs-success);
}

#resumo-alteracoes {
    min-height: 60px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monitorar mudanças no formulário
    const form = document.querySelector('form');
    const resumoDiv = document.getElementById('resumo-alteracoes');
    
    form.addEventListener('change', atualizarResumo);
    
    function atualizarResumo() {
        const alteracoes = [];
        
        // Verificar segurado alterado
        const seguradoSelect = document.getElementById('segurado_id');
        const seguradoOriginal = '{{ $cotacao->segurado->nome }}';
        if (seguradoSelect.options[seguradoSelect.selectedIndex].text !== seguradoOriginal) {
            alteracoes.push('• Segurado alterado');
        }
        
        // Verificar status alterado
        const statusSelect = document.getElementById('status');
        const statusOriginal = '{{ $cotacao->status }}';
        if (statusSelect.value !== statusOriginal) {
            alteracoes.push('• Status alterado');
        }
        
        // Verificar observações alteradas
        const observacoes = document.getElementById('observacoes');
        const observacoesOriginais = `{{ $cotacao->observacoes ?? '' }}`;
        if (observacoes.value !== observacoesOriginais) {
            alteracoes.push('• Observações alteradas');
        }
        
        // Verificar seguradoras para adicionar
        const novasSeguradoras = document.querySelectorAll('input[name="novas_seguradoras[]"]:checked');
        if (novasSeguradoras.length > 0) {
            alteracoes.push(`• ${novasSeguradoras.length} seguradora(s) para adicionar`);
        }
        
        // Verificar seguradoras para remover
        const removerSeguradoras = document.querySelectorAll('input[name="remover_seguradoras[]"]:checked');
        if (removerSeguradoras.length > 0) {
            alteracoes.push(`• ${removerSeguradoras.length} seguradora(s) para remover`);
        }
        
        // Atualizar resumo
        if (alteracoes.length > 0) {
            resumoDiv.innerHTML = '<div class="alert alert-warning py-2 mb-0"><small>' + alteracoes.join('<br>') + '</small></div>';
        } else {
            resumoDiv.innerHTML = '<p class="text-muted mb-0">Nenhuma alteração ainda.</p>';
        }
    }
});

function selecionarTodasNovas() {
    const checkboxes = document.querySelectorAll('input[name="novas_seguradoras[]"]');
    checkboxes.forEach(cb => {
        cb.checked = true;
        cb.dispatchEvent(new Event('change'));
    });
}

function deselecionarTodasNovas() {
    const checkboxes = document.querySelectorAll('input[name="novas_seguradoras[]"]');
    checkboxes.forEach(cb => {
        cb.checked = false;
        cb.dispatchEvent(new Event('change'));
    });
}
</script>
@endpush
@endsection