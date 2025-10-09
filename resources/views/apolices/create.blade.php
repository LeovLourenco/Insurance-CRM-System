@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Nova Apólice</h1>
            <p class="text-muted mb-0">Cadastre uma nova apólice no sistema</p>
        </div>
        <a href="{{ route('apolices.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <!-- Alerts -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="modern-card">
        <div class="card-body p-4">
            <form action="{{ route('apolices.store') }}" method="POST" id="apoliceForm">
                @csrf

                <!-- Seção: Identificação -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-shield-check me-2 text-primary"></i>
                            Identificação da Apólice
                        </h5>
                    </div>
                    <div class="col-md-4">
                        <label for="numero_apolice" class="form-label">Número da Apólice</label>
                        <input type="text" 
                               class="form-control @error('numero_apolice') is-invalid @enderror" 
                               id="numero_apolice" 
                               name="numero_apolice" 
                               value="{{ old('numero_apolice') }}"
                               placeholder="Ex: 123456789">
                        @error('numero_apolice')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="">Selecione o status</option>
                            <option value="pendente_emissao" {{ old('status') == 'pendente_emissao' ? 'selected' : '' }}>
                                Pendente de Emissão
                            </option>
                            <option value="ativa" {{ old('status') == 'ativa' ? 'selected' : '' }}>
                                Ativa
                            </option>
                            <option value="renovacao" {{ old('status') == 'renovacao' ? 'selected' : '' }}>
                                Em Renovação
                            </option>
                            <option value="cancelada" {{ old('status') == 'cancelada' ? 'selected' : '' }}>
                                Cancelada
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="origem" class="form-label">Origem <span class="text-danger">*</span></label>
                        <select class="form-select @error('origem') is-invalid @enderror" 
                                id="origem" 
                                name="origem" 
                                required>
                            <option value="">Selecione a origem</option>
                            <option value="cotacao" {{ old('origem') == 'cotacao' ? 'selected' : '' }}>
                                Cotação
                            </option>
                            <option value="importacao" {{ old('origem') == 'importacao' ? 'selected' : '' }}>
                                Importação
                            </option>
                        </select>
                        @error('origem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Seção: Relacionamentos -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-people me-2 text-primary"></i>
                            Relacionamentos
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <label for="corretora_id" class="form-label">Corretora</label>
                        <select class="form-select @error('corretora_id') is-invalid @enderror" 
                                id="corretora_id" 
                                name="corretora_id">
                            <option value="">Selecione uma corretora</option>
                            @foreach($corretoras as $corretora)
                                <option value="{{ $corretora->id }}" {{ old('corretora_id') == $corretora->id ? 'selected' : '' }}>
                                    {{ $corretora->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('corretora_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="seguradora_id" class="form-label">Seguradora</label>
                        <select class="form-select @error('seguradora_id') is-invalid @enderror" 
                                id="seguradora_id" 
                                name="seguradora_id">
                            <option value="">Selecione uma seguradora</option>
                            @foreach($seguradoras as $seguradora)
                                <option value="{{ $seguradora->id }}" {{ old('seguradora_id') == $seguradora->id ? 'selected' : '' }}>
                                    {{ $seguradora->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('seguradora_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="produto_id" class="form-label">Produto</label>
                        <select class="form-select @error('produto_id') is-invalid @enderror" 
                                id="produto_id" 
                                name="produto_id">
                            <option value="">Selecione um produto</option>
                            @foreach($produtos as $produto)
                                <option value="{{ $produto->id }}" {{ old('produto_id') == $produto->id ? 'selected' : '' }}>
                                    {{ $produto->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('produto_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="segurado_id" class="form-label">Segurado</label>
                        <select class="form-select @error('segurado_id') is-invalid @enderror" 
                                id="segurado_id" 
                                name="segurado_id">
                            <option value="">Selecione um segurado</option>
                            @foreach($segurados as $segurado)
                                <option value="{{ $segurado->id }}" {{ old('segurado_id') == $segurado->id ? 'selected' : '' }}>
                                    {{ $segurado->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('segurado_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Seção: Dados para Importação (opcional) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-file-text me-2 text-primary"></i>
                            Dados Manuais (Opcional)
                        </h5>
                        <p class="text-muted small mb-3">Preencha apenas se não houver relacionamentos acima</p>
                    </div>
                    <div class="col-md-6">
                        <label for="nome_segurado" class="form-label">Nome do Segurado</label>
                        <input type="text" 
                               class="form-control @error('nome_segurado') is-invalid @enderror" 
                               id="nome_segurado" 
                               name="nome_segurado" 
                               value="{{ old('nome_segurado') }}"
                               placeholder="Nome do segurado">
                        @error('nome_segurado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="cnpj_segurado" class="form-label">CNPJ do Segurado</label>
                        <input type="text" 
                               class="form-control @error('cnpj_segurado') is-invalid @enderror" 
                               id="cnpj_segurado" 
                               name="cnpj_segurado" 
                               value="{{ old('cnpj_segurado') }}"
                               placeholder="00.000.000/0000-00"
                               maxlength="18">
                        @error('cnpj_segurado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="nome_corretor" class="form-label">Nome do Corretor</label>
                        <input type="text" 
                               class="form-control @error('nome_corretor') is-invalid @enderror" 
                               id="nome_corretor" 
                               name="nome_corretor" 
                               value="{{ old('nome_corretor') }}"
                               placeholder="Nome do corretor">
                        @error('nome_corretor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="cnpj_corretor" class="form-label">CNPJ do Corretor</label>
                        <input type="text" 
                               class="form-control @error('cnpj_corretor') is-invalid @enderror" 
                               id="cnpj_corretor" 
                               name="cnpj_corretor" 
                               value="{{ old('cnpj_corretor') }}"
                               placeholder="00.000.000/0000-00"
                               maxlength="18">
                        @error('cnpj_corretor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Seção: Datas e Valores -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-calendar me-2 text-primary"></i>
                            Datas e Valores
                        </h5>
                    </div>
                    <div class="col-md-4">
                        <label for="data_emissao" class="form-label">Data de Emissão</label>
                        <input type="date" 
                               class="form-control @error('data_emissao') is-invalid @enderror" 
                               id="data_emissao" 
                               name="data_emissao" 
                               value="{{ old('data_emissao') }}">
                        @error('data_emissao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="inicio_vigencia" class="form-label">Início da Vigência</label>
                        <input type="date" 
                               class="form-control @error('inicio_vigencia') is-invalid @enderror" 
                               id="inicio_vigencia" 
                               name="inicio_vigencia" 
                               value="{{ old('inicio_vigencia') }}">
                        @error('inicio_vigencia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="fim_vigencia" class="form-label">Fim da Vigência</label>
                        <input type="date" 
                               class="form-control @error('fim_vigencia') is-invalid @enderror" 
                               id="fim_vigencia" 
                               name="fim_vigencia" 
                               value="{{ old('fim_vigencia') }}">
                        @error('fim_vigencia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="premio_liquido" class="form-label">Prêmio Líquido</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" 
                                   class="form-control @error('premio_liquido') is-invalid @enderror" 
                                   id="premio_liquido" 
                                   name="premio_liquido" 
                                   value="{{ old('premio_liquido') }}"
                                   step="0.01"
                                   min="0"
                                   placeholder="0,00">
                        </div>
                        @error('premio_liquido')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="endosso" class="form-label">Endosso</label>
                        <input type="text" 
                               class="form-control @error('endosso') is-invalid @enderror" 
                               id="endosso" 
                               name="endosso" 
                               value="{{ old('endosso') }}"
                               placeholder="Ex: END-001"
                               maxlength="20">
                        @error('endosso')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="data_pagamento" class="form-label">Data de Pagamento</label>
                        <input type="date" 
                               class="form-control @error('data_pagamento') is-invalid @enderror" 
                               id="data_pagamento" 
                               name="data_pagamento" 
                               value="{{ old('data_pagamento') }}">
                        @error('data_pagamento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Seção: Observações -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="observacoes_endosso" class="form-label">Observações</label>
                        <textarea class="form-control @error('observacoes_endosso') is-invalid @enderror" 
                                  id="observacoes_endosso" 
                                  name="observacoes_endosso" 
                                  rows="3" 
                                  placeholder="Observações sobre a apólice ou endosso...">{{ old('observacoes_endosso') }}</textarea>
                        @error('observacoes_endosso')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Botões -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('apolices.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Salvar Apólice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modern-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para CNPJ
    function formatCNPJ(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{2})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1/$2')
            .replace(/(\d{4})(\d)/, '$1-$2')
            .replace(/(-\d{2})\d+?$/, '$1');
    }

    const cnpjInputs = ['cnpj_segurado', 'cnpj_corretor'];
    cnpjInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', function(e) {
                e.target.value = formatCNPJ(e.target.value);
            });
        }
    });

    // Validação de datas
    const inicioVigencia = document.getElementById('inicio_vigencia');
    const fimVigencia = document.getElementById('fim_vigencia');

    if (inicioVigencia && fimVigencia) {
        inicioVigencia.addEventListener('change', function() {
            if (this.value) {
                fimVigencia.min = this.value;
            }
        });

        fimVigencia.addEventListener('change', function() {
            if (inicioVigencia.value && this.value < inicioVigencia.value) {
                alert('A data de fim da vigência deve ser posterior ao início da vigência.');
                this.value = '';
            }
        });
    }
});
</script>
@endsection