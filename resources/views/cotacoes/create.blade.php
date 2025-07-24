@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header da página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-plus-circle me-2"></i>Nova Cotação
            </h1>
            <p class="text-muted mb-0">Crie uma nova cotação e distribua para as seguradoras</p>
        </div>
        <div>
            <a href="{{ route('cotacoes.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>
    </div>

    <!-- Progress Steps -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="modern-card p-3">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="step-indicator d-flex align-items-center">
                        <div class="step active">
                            <span class="step-number">1</span>
                            <span class="step-text">Dados Básicos</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step">
                            <span class="step-number">2</span>
                            <span class="step-text">Selecionar Seguradoras</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step">
                            <span class="step-number">3</span>
                            <span class="step-text">Confirmação</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário Principal -->
    <form id="form-cotacao" method="POST" action="{{ route('cotacoes.store') }}">
        @csrf
        
        <div class="row">
            <!-- Coluna Principal -->
            <div class="col-lg-8">
                <div class="modern-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text me-2"></i>Informações da Cotação
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Corretora -->
                            <div class="col-md-6">
                                <label for="corretora_id" class="form-label">
                                    <i class="bi bi-person-badge me-1"></i>Corretora *
                                </label>
                                <select name="corretora_id" id="corretora_id" class="form-select @error('corretora_id') is-invalid @enderror" 
                                        required onchange="console.log('Corretora mudou:', this.value); atualizarResumo(); buscarSeguradoras();">
                                    <option value="">Selecione uma corretora</option>
                                    @foreach($corretoras as $corretora)
                                        <option value="{{ $corretora->id }}" {{ old('corretora_id', $corretoraId) == $corretora->id ? 'selected' : '' }}>
                                            {{ $corretora->nome }}
                                            @if($corretora->codigo)
                                                ({{ $corretora->codigo }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('corretora_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Produto -->
                            <div class="col-md-6">
                                <label for="produto_id" class="form-label">
                                    <i class="bi bi-box-seam me-1"></i>Produto *
                                </label>
                                <select name="produto_id" id="produto_id" class="form-select @error('produto_id') is-invalid @enderror" 
                                        required onchange="console.log('Produto mudou:', this.value); atualizarResumo(); buscarSeguradoras();">
                                    <option value="">Selecione um produto</option>
                                    @foreach($produtos as $produto)
                                        <option value="{{ $produto->id }}" {{ old('produto_id', $produtoId) == $produto->id ? 'selected' : '' }}>
                                            {{ $produto->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('produto_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Segurado -->
                            <div class="col-12">
                                <label for="segurado_id" class="form-label">
                                    <i class="bi bi-person-check me-1"></i>Segurado *
                                </label>
                                <select name="segurado_id" id="segurado_id" class="form-select @error('segurado_id') is-invalid @enderror" 
                                        required onchange="console.log('Segurado mudou:', this.value); atualizarResumo();">
                                    <option value="">Selecione um segurado</option>
                                    @foreach($segurados as $segurado)
                                        <option value="{{ $segurado->id }}" {{ old('segurado_id') == $segurado->id ? 'selected' : '' }}>
                                            {{ $segurado->nome }}
                                            @if($segurado->cpf_cnpj)
                                                - {{ $segurado->cpf_cnpj }}
                                            @endif
                                            @if($segurado->email)
                                                ({{ $segurado->email }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('segurado_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Não encontrou o segurado? 
                                    <a href="{{ route('segurados.create') }}" target="_blank" class="text-decoration-none">
                                        Cadastre um novo segurado
                                    </a>
                                </div>
                            </div>

                            <!-- Observações -->
                            <div class="col-12">
                                <label for="observacoes" class="form-label">
                                    <i class="bi bi-chat-text me-1"></i>Observações Gerais
                                </label>
                                <textarea name="observacoes" id="observacoes" rows="4" 
                                          class="form-control @error('observacoes') is-invalid @enderror"
                                          placeholder="Informações adicionais sobre a cotação...">{{ old('observacoes') }}</textarea>
                                @error('observacoes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Estas observações serão visíveis para todas as seguradoras
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral -->
            <div class="col-lg-4">
                <!-- Seguradoras Elegíveis -->
                <div class="modern-card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-building me-2"></i>Seguradoras Elegíveis
                        </h6>
                    </div>
                    <div class="card-body" id="seguradoras-container">
                        <div class="text-center text-muted py-4" id="seguradoras-placeholder">
                            <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                            <p class="mb-0">Selecione corretora e produto para ver as seguradoras disponíveis</p>
                        </div>
                        
                        <div id="seguradoras-list" style="display: none;">
                            <p class="text-muted mb-3">
                                <small>Selecione as seguradoras para cotação:</small>
                            </p>
                            <div id="seguradoras-checkboxes">
                                <!-- Será preenchido via JavaScript -->
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selecionarTodas()">
                                    <i class="bi bi-check-all me-1"></i>Selecionar Todas
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-1" onclick="deselecionarTodas()">
                                    <i class="bi bi-x-circle me-1"></i>Limpar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo -->
                <div class="modern-card mt-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-list-check me-2"></i>Resumo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="resumo-item">
                            <strong>Corretora:</strong>
                            <span id="resumo-corretora" class="text-muted">Não selecionada</span>
                        </div>
                        <hr>
                        <div class="resumo-item">
                            <strong>Produto:</strong>
                            <span id="resumo-produto" class="text-muted">Não selecionado</span>
                        </div>
                        <hr>
                        <div class="resumo-item">
                            <strong>Segurado:</strong>
                            <span id="resumo-segurado" class="text-muted">Não selecionado</span>
                        </div>
                        <hr>
                        <div class="resumo-item">
                            <strong>Seguradoras:</strong>
                            <span id="resumo-seguradoras" class="badge bg-primary">0 selecionadas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="modern-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <small>
                                    <i class="bi bi-info-circle me-1"></i>
                                    Certifique-se de preencher todos os campos obrigatórios
                                </small>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-success" id="btn-criar" disabled>
                                    <i class="bi bi-check-circle me-1"></i>Criar Cotação
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
/* Steps Indicator */
.step-indicator {
    width: 100%;
    max-width: 500px;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background: var(--bs-primary);
    color: white;
    box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.2);
}

.step-text {
    font-size: 0.85rem;
    font-weight: 500;
    color: #6c757d;
    text-align: center;
}

.step.active .step-text {
    color: var(--bs-primary);
}

.step-connector {
    flex: 1;
    height: 2px;
    background: #e9ecef;
    margin: 0 20px;
    margin-top: -20px;
    z-index: 1;
}

/* Seguradoras Cards */
.seguradora-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.seguradora-item:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.seguradora-item.selected {
    background-color: rgba(var(--bs-success-rgb), 0.1);
    border-color: var(--bs-success);
}

/* Form Controls */
.form-select:focus,
.form-control:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}

/* Card Headers */
.card-header {
    border-radius: 1rem 1rem 0 0 !important;
}

/* Resumo */
.resumo-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.resumo-item:last-child {
    margin-bottom: 0;
}

/* Loading */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.3s ease-out;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado - inicializando...'); // DEBUG
    
    const corretoraSelect = document.getElementById('corretora_id');
    const produtoSelect = document.getElementById('produto_id');
    const seguradoSelect = document.getElementById('segurado_id');
    const btnCriar = document.getElementById('btn-criar');
    
    console.log('Elementos encontrados:', {
        corretora: !!corretoraSelect,
        produto: !!produtoSelect,
        segurado: !!seguradoSelect,
        btnCriar: !!btnCriar
    }); // DEBUG
    
    if (!corretoraSelect || !produtoSelect || !seguradoSelect) {
        console.error('Alguns elementos não foram encontrados!');
        return;
    }
    
    // Event listeners para buscar seguradoras
    corretoraSelect.addEventListener('change', function() {
        console.log('Corretora alterada para:', this.value); // DEBUG
        buscarSeguradoras();
    });
    
    produtoSelect.addEventListener('change', function() {
        console.log('Produto alterado para:', this.value); // DEBUG
        buscarSeguradoras();
    });
    
    // Event listeners para resumo
    corretoraSelect.addEventListener('change', function() {
        console.log('Atualizando resumo - corretora'); // DEBUG
        atualizarResumo();
    });
    
    produtoSelect.addEventListener('change', function() {
        console.log('Atualizando resumo - produto'); // DEBUG
        atualizarResumo();
    });
    
    seguradoSelect.addEventListener('change', function() {
        console.log('Atualizando resumo - segurado'); // DEBUG
        atualizarResumo();
    });
    
    // Verificar se pode habilitar botão
    document.addEventListener('change', verificarFormulario);
    
    // Inicializar se já tem dados
    if (corretoraSelect.value && produtoSelect.value) {
        console.log('Dados iniciais encontrados, buscando seguradoras...'); // DEBUG
        buscarSeguradoras();
    }
    
    console.log('Atualizando resumo inicial...'); // DEBUG
    atualizarResumo();
});

async function buscarSeguradoras() {
    const corretoraId = document.getElementById('corretora_id').value;
    const produtoId = document.getElementById('produto_id').value;
    
    console.log('Debug - Corretora ID:', corretoraId, 'Produto ID:', produtoId); // DEBUG
    
    const container = document.getElementById('seguradoras-container');
    const placeholder = document.getElementById('seguradoras-placeholder');
    const list = document.getElementById('seguradoras-list');
    const checkboxes = document.getElementById('seguradoras-checkboxes');
    
    if (!corretoraId || !produtoId) {
        console.log('Debug - Faltam dados, saindo...'); // DEBUG
        placeholder.style.display = 'block';
        list.style.display = 'none';
        atualizarContadorSeguradoras();
        return;
    }
    
    // Loading state
    container.classList.add('loading');
    placeholder.innerHTML = `
        <div class="text-center text-muted py-4">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <p class="mt-2 mb-0">Buscando seguradoras...</p>
        </div>
    `;
    
    try {
        const url = `{{ url('cotacoes/seguradoras') }}?corretora_id=${corretoraId}&produto_id=${produtoId}`;
        console.log('Debug - URL da requisição:', url); // DEBUG
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('Debug - Response status:', response.status); // DEBUG
        console.log('Debug - Response headers:', response.headers.get('content-type')); // DEBUG
        
        // Verificar se é realmente JSON
        const responseText = await response.text();
        console.log('Debug - Response text (primeiros 200 chars):', responseText.substring(0, 200)); // DEBUG
        
        let data;
        try {
            data = JSON.parse(responseText);
            console.log('Debug - Dados recebidos:', data); // DEBUG
        } catch (parseError) {
            console.error('Debug - Erro ao fazer parse do JSON:', parseError);
            console.error('Debug - Resposta completa:', responseText);
            throw new Error(`Resposta inválida do servidor: ${responseText.substring(0, 100)}...`);
        }
        
        if (data.seguradoras && data.seguradoras.length > 0) {
            // Mostrar seguradoras
            checkboxes.innerHTML = '';
            
            data.seguradoras.forEach(seguradora => {
                const div = document.createElement('div');
                div.className = 'seguradora-item border rounded p-3 mb-2';
                div.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="seguradoras[]" 
                               value="${seguradora.id}" id="seg_${seguradora.id}"
                               onchange="atualizarContadorSeguradoras()">
                        <label class="form-check-label w-100 cursor-pointer" for="seg_${seguradora.id}">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <strong>${seguradora.nome}</strong>
                                    <div class="text-muted small">ID: ${seguradora.id}</div>
                                </div>
                                <div class="text-primary">
                                    <i class="bi bi-building"></i>
                                </div>
                            </div>
                        </label>
                    </div>
                `;
                checkboxes.appendChild(div);
            });
            
            placeholder.style.display = 'none';
            list.style.display = 'block';
            list.classList.add('fade-in');
            
        } else {
            // MELHORAR: Mensagem mais informativa quando não há seguradoras
            placeholder.innerHTML = `
                <div class="text-center text-warning py-4">
                    <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>
                    <h6 class="mb-2">Nenhuma seguradora disponível</h6>
                    <p class="text-muted mb-3">
                        Não existem seguradoras vinculadas para esta combinação:<br>
                        <strong>Corretora:</strong> ${document.getElementById('corretora_id').options[document.getElementById('corretora_id').selectedIndex].text}<br>
                        <strong>Produto:</strong> ${document.getElementById('produto_id').options[document.getElementById('produto_id').selectedIndex].text}
                    </p>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Entre em contato com o administrador para configurar os vínculos necessários
                        </small>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="buscarSeguradoras()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Tentar Novamente
                        </button>
                    </div>
                </div>
            `;
            placeholder.style.display = 'block';
            list.style.display = 'none';
        }
        
    } catch (error) {
        console.error('Erro completo:', error); // DEBUG MELHORADO
        
        // Verificar se é erro de CORS/403
        if (error.message && error.message.includes('permission')) {
            placeholder.innerHTML = `
                <div class="text-center text-danger py-4">
                    <i class="bi bi-shield-x fs-1 d-block mb-2"></i>
                    <h6 class="mb-2">Erro de Permissão</h6>
                    <p class="mb-3">Problema de autenticação na requisição</p>
                    <div class="alert alert-warning text-start">
                        <small><strong>Debug:</strong> ${error.message}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="buscarSeguradoras()">
                        Tentar novamente
                    </button>
                </div>
            `;
        } else {
            // Outros erros
            placeholder.innerHTML = `
                <div class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-circle fs-1 d-block mb-2"></i>
                    <p class="mb-0">Erro ao carregar seguradoras</p>
                    <div class="mt-2">
                        <small class="text-muted">Erro: ${error.message || 'Erro desconhecido'}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="buscarSeguradoras()">
                        Tentar novamente
                    </button>
                </div>
            `;
        }
        list.style.display = 'none';
    }
    
    container.classList.remove('loading');
    atualizarContadorSeguradoras();
}

function selecionarTodas() {
    const checkboxes = document.querySelectorAll('input[name="seguradoras[]"]');
    checkboxes.forEach(cb => {
        cb.checked = true;
        cb.closest('.seguradora-item').classList.add('selected');
    });
    atualizarContadorSeguradoras();
}

function deselecionarTodas() {
    const checkboxes = document.querySelectorAll('input[name="seguradoras[]"]');
    checkboxes.forEach(cb => {
        cb.checked = false;
        cb.closest('.seguradora-item').classList.remove('selected');
    });
    atualizarContadorSeguradoras();
}

function atualizarContadorSeguradoras() {
    const selecionadas = document.querySelectorAll('input[name="seguradoras[]"]:checked').length;
    const resumo = document.getElementById('resumo-seguradoras');
    
    resumo.textContent = `${selecionadas} selecionadas`;
    resumo.className = selecionadas > 0 ? 'badge bg-success' : 'badge bg-primary';
    
    // Atualizar visual dos cards
    document.querySelectorAll('input[name="seguradoras[]"]').forEach(cb => {
        const item = cb.closest('.seguradora-item');
        if (cb.checked) {
            item.classList.add('selected');
        } else {
            item.classList.remove('selected');
        }
    });
    
    verificarFormulario();
}

function atualizarResumo() {
    console.log('Função atualizarResumo chamada'); // DEBUG
    
    const corretora = document.getElementById('corretora_id');
    const produto = document.getElementById('produto_id');
    const segurado = document.getElementById('segurado_id');
    
    const resumoCorretora = document.getElementById('resumo-corretora');
    const resumoProduto = document.getElementById('resumo-produto');
    const resumoSegurado = document.getElementById('resumo-segurado');
    
    console.log('Valores atuais:', {
        corretora: corretora.value,
        produto: produto.value,
        segurado: segurado.value
    }); // DEBUG
    
    console.log('Elementos de resumo:', {
        resumoCorretora: !!resumoCorretora,
        resumoProduto: !!resumoProduto,
        resumoSegurado: !!resumoSegurado
    }); // DEBUG
    
    if (resumoCorretora) {
        resumoCorretora.textContent = 
            corretora.value ? corretora.options[corretora.selectedIndex].text : 'Não selecionada';
    }
        
    if (resumoProduto) {
        resumoProduto.textContent = 
            produto.value ? produto.options[produto.selectedIndex].text : 'Não selecionado';
    }
        
    if (resumoSegurado) {
        resumoSegurado.textContent = 
            segurado.value ? segurado.options[segurado.selectedIndex].text : 'Não selecionado';
    }
    
    console.log('Resumo atualizado!'); // DEBUG
}

function verificarFormulario() {
    const corretora = document.getElementById('corretora_id').value;
    const produto = document.getElementById('produto_id').value;
    const segurado = document.getElementById('segurado_id').value;
    const seguradoras = document.querySelectorAll('input[name="seguradoras[]"]:checked').length;
    
    const btnCriar = document.getElementById('btn-criar');
    
    if (corretora && produto && segurado && seguradoras > 0) {
        btnCriar.disabled = false;
        btnCriar.classList.remove('btn-secondary');
        btnCriar.classList.add('btn-success');
    } else {
        btnCriar.disabled = true;
        btnCriar.classList.remove('btn-success');
        btnCriar.classList.add('btn-secondary');
    }
}
</script>
@endpush
@endsection