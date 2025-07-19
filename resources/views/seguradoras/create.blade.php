@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Nova Seguradora</h1>
        <p class="text-muted mb-0">Cadastre uma nova seguradora parceira</p>
    </div>
    <a href="{{ route('seguradoras.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Voltar
    </a>
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
    <form action="{{ route('seguradoras.store') }}" method="POST">
        @csrf
        
        <div class="row g-4">
            <!-- Informações Básicas -->
            <div class="col-12">
                <h5 class="border-bottom pb-3 mb-3">
                    <i class="bi bi-building text-primary me-2"></i>
                    Informações Básicas
                </h5>
            </div>
            
            <!-- Nome da Seguradora -->
            <div class="col-md-8">
                <label for="nome" class="form-label">
                    Nome da Seguradora <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control @error('nome') is-invalid @enderror" 
                       id="nome" 
                       name="nome" 
                       value="{{ old('nome') }}" 
                       placeholder="Ex: Seguradora Nacional S.A."
                       maxlength="191"
                       required>
                <div class="form-text">Digite o nome completo da seguradora</div>
                @error('nome')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Site -->
            <div class="col-md-4">
                <label for="site" class="form-label">Site</label>
                <input type="text" 
                       class="form-control @error('site') is-invalid @enderror" 
                       id="site" 
                       name="site" 
                       value="{{ old('site') }}" 
                       placeholder="www.seguradora.com.br"
                       maxlength="191">
                <div class="form-text">Digite apenas o endereço (https:// será adicionado automaticamente)</div>
                @error('site')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Observações -->
            <div class="col-12">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea class="form-control @error('observacoes') is-invalid @enderror" 
                          id="observacoes" 
                          name="observacoes" 
                          rows="4" 
                          placeholder="Informações adicionais sobre a seguradora, características especiais, contatos específicos..."
                          maxlength="2000">{{ old('observacoes') }}</textarea>
                <div class="form-text">
                    <span id="charCount">{{ strlen(old('observacoes', '')) }}</span>/2000 caracteres
                </div>
                @error('observacoes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Produtos -->
            <div class="col-12">
                <h5 class="border-bottom pb-3 mb-3 mt-4">
                    <i class="bi bi-box-seam text-success me-2"></i>
                    Produtos Oferecidos
                </h5>
                <div class="form-text mb-3">Selecione os produtos que esta seguradora oferece (opcional)</div>
                
                @error('produtos')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                
                <div class="row g-3">
                    @forelse($produtos as $produto)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 produto-card">
                                <div class="card-body p-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="produtos[]" 
                                               value="{{ $produto->id }}" 
                                               id="produto_{{ $produto->id }}"
                                               {{ in_array($produto->id, old('produtos', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium" for="produto_{{ $produto->id }}">
                                            {{ $produto->nome }}
                                        </label>
                                    </div>
                                    
                                    @if($produto->linha)
                                        <div class="mt-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                {{ $produto->linha }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if($produto->descricao)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                {{ Str::limit($produto->descricao, 80) }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Nenhum produto cadastrado ainda. 
                                <a href="{{ route('produtos.create') }}" class="alert-link">Cadastre um produto</a> 
                                primeiro.
                            </div>
                        </div>
                    @endforelse
                </div>
                
                @if($produtos->count() > 0)
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="selectAllProdutos()">
                            <i class="bi bi-check-all me-1"></i>Selecionar Todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllProdutos()">
                            <i class="bi bi-x-circle me-1"></i>Desmarcar Todos
                        </button>
                    </div>
                    
                    <!-- Dica para produtos não encontrados -->
                    <div class="alert alert-light border mt-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-question-circle text-warning me-2"></i>
                            <div class="flex-grow-1">
                                <strong>Não encontrou o produto que procura?</strong>
                                <div class="small text-muted mt-1">
                                    Verifique se o produto já existe ou cadastre um novo produto primeiro.
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('produtos.index') }}" class="btn btn-sm btn-outline-primary me-2">
                                    <i class="bi bi-search me-1"></i>Verificar
                                </a>
                                <a href="{{ route('produtos.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i>Cadastrar
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Botões -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Salvar Seguradora
                    </button>
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-2"></i>Limpar
                    </button>
                    <a href="{{ route('seguradoras.index') }}" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Dicas -->
<div class="modern-card p-4 mt-4">
    <h6 class="mb-3">
        <i class="bi bi-lightbulb text-warning me-2"></i>Dicas para cadastro
    </h6>
    <div class="row">
        <div class="col-md-6">
            <ul class="list-unstyled">
                <li class="mb-2">
                    <i class="bi bi-check text-success me-2"></i>
                    Use o nome oficial da seguradora
                </li>
                <li class="mb-2">
                    <i class="bi bi-check text-success me-2"></i>
                    Inclua o site para facilitar consultas
                </li>
                <li class="mb-2">
                    <i class="bi bi-check text-success me-2"></i>
                    Selecione apenas produtos que oferece
                </li>
            </ul>
        </div>
        <div class="col-md-6">
            <ul class="list-unstyled">
                <li class="mb-2">
                    <i class="bi bi-info text-info me-2"></i>
                    Parcerias com corretoras são configuradas após o cadastro
                </li>
                <li class="mb-2">
                    <i class="bi bi-check text-success me-2"></i>
                    Use observações para informações importantes
                </li>
                <li class="mb-2">
                    <i class="bi bi-check text-success me-2"></i>
                    Produtos podem ser editados depois
                </li>
            </ul>
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

/* Cards de produtos */
.produto-card {
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.produto-card:hover {
    border-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
}

.produto-card:has(.form-check-input:checked) {
    border-color: #16a34a;
    background: rgba(22, 163, 74, 0.05);
}

.produto-card .form-check-input {
    transform: scale(1.2);
}

.produto-card .form-check-label {
    cursor: pointer;
    color: #374151;
}
</style>

<script>
// Contador de caracteres para observações
document.addEventListener('DOMContentLoaded', function() {
    const observacoesTextarea = document.getElementById('observacoes');
    const charCount = document.getElementById('charCount');
    
    if (observacoesTextarea && charCount) {
        observacoesTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCount.textContent = currentLength;
            
            // Mudança de cor conforme aproxima do limite
            if (currentLength > 1800) {
                charCount.className = 'text-danger fw-bold';
            } else if (currentLength > 1500) {
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

    // Interação com cards de produtos
    document.querySelectorAll('.produto-card').forEach(card => {
        card.addEventListener('click', function() {
            const checkbox = this.querySelector('.form-check-input');
            checkbox.checked = !checkbox.checked;
        });
    });
});

// Funções para manipular produtos
function selectAllProdutos() {
    document.querySelectorAll('input[name="produtos[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllProdutos() {
    document.querySelectorAll('input[name="produtos[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Confirmação antes de limpar formulário
document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
    if (!confirm('Tem certeza que deseja limpar todos os campos?')) {
        e.preventDefault();
    }
});
</script>
@endsection