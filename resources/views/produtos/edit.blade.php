@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Editar Produto</h1>
        <p class="text-muted mb-0">Altere as informações do produto <strong>{{ $produto->nome }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('produtos.show', $produto) }}" class="btn btn-outline-info">
            <i class="bi bi-eye me-2"></i>Visualizar
        </a>
        <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>
</div>

<!-- Alerts de erro -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Ops! Há alguns problemas:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Formulário -->
<div class="modern-card p-4">
    <form action="{{ route('produtos.update', $produto) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            <!-- Nome do Produto -->
            <div class="col-md-8">
                <label for="nome" class="form-label">
                    Nome do Produto <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control @error('nome') is-invalid @enderror" 
                       id="nome" 
                       name="nome" 
                       value="{{ old('nome', $produto->nome) }}" 
                       placeholder="Ex: Seguro Auto Completo"
                       maxlength="191"
                       required>
                <div class="form-text">Digite o nome completo do produto</div>
                @error('nome')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Linha do Produto -->
            <div class="col-md-4">
                <label for="linha" class="form-label">Linha</label>
                <select class="form-select @error('linha') is-invalid @enderror" 
                        id="linha" 
                        name="linha">
                    <option value="">Selecione a linha</option>
                    <option value="Transportes" {{ old('linha', $produto->linha) == 'Transportes' ? 'selected' : '' }}>Transportes</option>
                    <option value="Automóvel" {{ old('linha', $produto->linha) == 'Automóvel' ? 'selected' : '' }}>Automóvel</option>
                    <option value="Vida" {{ old('linha', $produto->linha) == 'Vida' ? 'selected' : '' }}>Vida</option>
                    <option value="Patrimonial" {{ old('linha', $produto->linha) == 'Patrimonial' ? 'selected' : '' }}>Patrimonial</option>
                    <option value="Responsabilidade" {{ old('linha', $produto->linha) == 'Responsabilidade' ? 'selected' : '' }}>Responsabilidade</option>
                    <option value="Saúde" {{ old('linha', $produto->linha) == 'Saúde' ? 'selected' : '' }}>Saúde</option>
                    <option value="Previdência" {{ old('linha', $produto->linha) == 'Previdência' ? 'selected' : '' }}>Previdência</option>
                    <option value="Rural" {{ old('linha', $produto->linha) == 'Rural' ? 'selected' : '' }}>Rural</option>
                    <option value="Habitacional" {{ old('linha', $produto->linha) == 'Habitacional' ? 'selected' : '' }}>Habitacional</option>
                    <option value="Marítimo" {{ old('linha', $produto->linha) == 'Marítimo' ? 'selected' : '' }}>Marítimo</option>
                    <option value="Aviação" {{ old('linha', $produto->linha) == 'Aviação' ? 'selected' : '' }}>Aviação</option>
                </select>
                <div class="form-text">Família/categoria do produto</div>
                @error('linha')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Descrição -->
            <div class="col-12">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control @error('descricao') is-invalid @enderror" 
                          id="descricao" 
                          name="descricao" 
                          rows="4" 
                          placeholder="Descreva as características, coberturas e benefícios do produto..."
                          maxlength="1000">{{ old('descricao', $produto->descricao) }}</textarea>
                <div class="form-text">
                    <span id="charCount">{{ strlen(old('descricao', $produto->descricao ?? '')) }}</span>/1000 caracteres
                </div>
                @error('descricao')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Seguradoras que oferecem o produto -->
            <div class="col-12">
                <label class="form-label">
                    Seguradoras que oferecem este produto <span class="text-danger">*</span>
                </label>
                <div class="form-text mb-3">Selecione pelo menos uma seguradora que oferece este produto</div>
                
                @error('seguradoras')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                
                <div class="row g-3">
                    @forelse($seguradoras ?? [] as $seguradora)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 seguradora-card">
                                <div class="card-body p-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="seguradoras[]" 
                                               value="{{ $seguradora->id }}" 
                                               id="seguradora_{{ $seguradora->id }}"
                                               {{ in_array($seguradora->id, old('seguradoras', $produto->seguradoras->pluck('id')->toArray())) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium" for="seguradora_{{ $seguradora->id }}">
                                            {{ $seguradora->nome }}
                                        </label>
                                    </div>
                                    @if($produto->seguradoras->contains($seguradora->id))
                                        <div class="mt-2">
                                            <small class="text-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Vinculado em {{ $produto->seguradoras->find($seguradora->id)->pivot->created_at->format('d/m/Y') }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Nenhuma seguradora cadastrada. 
                                <a href="{{ route('cadastro') }}" class="alert-link">Cadastre uma seguradora</a> 
                                primeiro.
                            </div>
                        </div>
                    @endforelse
                </div>
                
                @if(isset($seguradoras) && $seguradoras->count() > 0)
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                            <i class="bi bi-check-all me-1"></i>Selecionar Todas
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                            <i class="bi bi-x-circle me-1"></i>Desmarcar Todas
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="selectOriginal()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Restaurar Original
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Botões -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                    </button>
                    <button type="reset" class="btn btn-outline-secondary" onclick="resetForm()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Restaurar
                    </button>
                    <a href="{{ route('produtos.show', $produto) }}" class="btn btn-outline-info">
                        <i class="bi bi-eye me-2"></i>Visualizar
                    </a>
                    <a href="{{ route('produtos.index') }}" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Informações adicionais -->
<div class="modern-card p-4 mt-4">
    <div class="row">
        <div class="col-md-8">
            <h6 class="mb-3">
                <i class="bi bi-info-circle text-info me-2"></i>Informações importantes
            </h6>
            <ul class="list-unstyled">
                <li class="mb-2">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Alterar o nome pode afetar cotações existentes
                </li>
                <li class="mb-2">
                    <i class="bi bi-shield-check text-success me-2"></i>
                    Remover seguradoras pode limitar futuras cotações
                </li>
                <li class="mb-2">
                    <i class="bi bi-clock text-info me-2"></i>
                    As alterações são aplicadas imediatamente
                </li>
            </ul>
        </div>
        <div class="col-md-4">
            <h6 class="mb-3">Estatísticas atuais</h6>
            <div class="d-flex justify-content-between mb-2">
                <span>Seguradoras:</span>
                <span class="badge bg-info">{{ $produto->seguradoras->count() }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Cotações:</span>
                <span class="badge bg-success">{{ $produto->cotacoes->count() }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Criado em:</span>
                <span class="text-muted">{{ $produto->created_at->format('d/m/Y') }}</span>
            </div>
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

.form-control:focus,
.form-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    transform: translateY(-1px);
}

.invalid-feedback {
    display: block;
}

.form-text {
    font-size: 0.875rem;
    color: #6b7280;
}

.alert {
    border: none;
    border-radius: 0.75rem;
}

/* Cards de seguradoras */
.seguradora-card {
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.seguradora-card:hover {
    border-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
}

.seguradora-card:has(.form-check-input:checked) {
    border-color: #2563eb;
    background: rgba(37, 99, 235, 0.05);
}

.seguradora-card .form-check-input {
    transform: scale(1.2);
}

.seguradora-card .form-check-label {
    cursor: pointer;
    color: #374151;
}
</style>

<script>
// Armazenar valores originais
const originalValues = {
    nome: {!! json_encode($produto->nome) !!},
    linha: {!! json_encode($produto->linha) !!},
    descricao: {!! json_encode($produto->descricao) !!},
    seguradoras: {!! json_encode($produto->seguradoras->pluck('id')->toArray()) !!}
};

// Contador de caracteres para descrição
document.addEventListener('DOMContentLoaded', function() {
    const descricaoTextarea = document.getElementById('descricao');
    const charCount = document.getElementById('charCount');
    
    if (descricaoTextarea && charCount) {
        descricaoTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCount.textContent = currentLength;
            
            // Mudança de cor conforme aproxima do limite
            if (currentLength > 900) {
                charCount.className = 'text-danger fw-bold';
            } else if (currentLength > 700) {
                charCount.className = 'text-warning fw-bold';
            } else {
                charCount.className = '';
            }
        });
    }
    
    // Formatação automática do nome (Title Case)
    const nomeInput = document.getElementById('nome');
    if (nomeInput) {
        nomeInput.addEventListener('blur', function() {
            this.value = this.value.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
        });
    }

    // Interação com cards de seguradoras
    document.querySelectorAll('.seguradora-card').forEach(card => {
        card.addEventListener('click', function() {
            const checkbox = this.querySelector('.form-check-input');
            checkbox.checked = !checkbox.checked;
        });
    });
});

// Funções para manipular seguradoras
function selectAll() {
    document.querySelectorAll('input[name="seguradoras[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAll() {
    document.querySelectorAll('input[name="seguradoras[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

function selectOriginal() {
    document.querySelectorAll('input[name="seguradoras[]"]').forEach(checkbox => {
        checkbox.checked = originalValues.seguradoras.includes(parseInt(checkbox.value));
    });
}

// Função para restaurar formulário
function resetForm() {
    if (confirm('Tem certeza que deseja restaurar os valores originais?')) {
        document.getElementById('nome').value = originalValues.nome;
        document.getElementById('linha').value = originalValues.linha;
        document.getElementById('descricao').value = originalValues.descricao;
        selectOriginal();
        
        // Atualizar contador
        document.getElementById('charCount').textContent = originalValues.descricao ? originalValues.descricao.length : 0;
    }
}
</script>
@endsection