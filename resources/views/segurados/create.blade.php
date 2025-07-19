@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Novo Segurado</h1>
        <p class="text-muted mb-0">Cadastre um novo cliente segurado no sistema</p>
    </div>
    <a href="{{ route('segurados.index') }}" class="btn btn-outline-secondary">
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

<div class="row">
    <div class="col-lg-8">
        <div class="modern-card p-4">
            <form action="{{ route('segurados.store') }}" method="POST" id="formSegurado">
                @csrf
                
                <!-- Informações Básicas -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2 mb-3">
                        <i class="bi bi-person-check me-2"></i>Informações do Segurado
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nome" class="form-label">
                                Nome Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('nome') is-invalid @enderror" 
                                   id="nome" 
                                   name="nome" 
                                   value="{{ old('nome') }}" 
                                   placeholder="Digite o nome completo"
                                   required>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="documento" class="form-label">CPF ou CNPJ</label>
                            <input type="text" 
                                   class="form-control @error('documento') is-invalid @enderror" 
                                   id="documento" 
                                   name="documento" 
                                   value="{{ old('documento') }}" 
                                   placeholder="000.000.000-00 ou 00.000.000/0000-00"
                                   maxlength="18">
                            <div id="documentoFeedback" class="form-text"></div>
                            @error('documento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" 
                                   class="form-control @error('telefone') is-invalid @enderror" 
                                   id="telefone" 
                                   name="telefone" 
                                   value="{{ old('telefone') }}" 
                                   placeholder="(11) 99999-9999"
                                   maxlength="15">
                            @error('telefone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <i class="bi bi-check-circle me-2"></i>Criar Segurado
                    </button>
                    <a href="{{ route('segurados.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Sidebar com informações -->
    <div class="col-lg-4">
        <div class="modern-card p-4 mb-4">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-info-circle me-2"></i>Informações
            </h6>
            
            <div class="mb-3">
                <h6 class="small text-muted text-uppercase fw-bold">Campos Obrigatórios</h6>
                <ul class="list-unstyled small">
                    <li><i class="bi bi-check-circle text-success me-1"></i> Nome completo</li>
                </ul>
            </div>
            
            <div class="mb-3">
                <h6 class="small text-muted text-uppercase fw-bold">Campos Opcionais</h6>
                <ul class="list-unstyled small">
                    <li><i class="bi bi-dash-circle text-muted me-1"></i> CPF ou CNPJ</li>
                    <li><i class="bi bi-dash-circle text-muted me-1"></i> Telefone</li>
                </ul>
            </div>
            
            <hr>
            
            <div class="mb-3">
                <h6 class="small text-muted text-uppercase fw-bold">Validações</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <i class="bi bi-shield-check text-success me-1"></i> 
                        CPF e CNPJ são validados automaticamente
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-shield-check text-success me-1"></i> 
                        Documentos únicos no sistema
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-shield-check text-success me-1"></i> 
                        Formatação automática dos campos
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="modern-card p-4">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-lightbulb me-2"></i>Dicas
            </h6>
            
            <ul class="list-unstyled small">
                <li class="mb-2">
                    <i class="bi bi-lightbulb text-warning me-1"></i> 
                    Digite apenas números no CPF/CNPJ
                </li>
                <li class="mb-2">
                    <i class="bi bi-lightbulb text-warning me-1"></i> 
                    A formatação é aplicada automaticamente
                </li>
                <li class="mb-2">
                    <i class="bi bi-lightbulb text-warning me-1"></i> 
                    O sistema identifica PF ou PJ automaticamente
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

.documento-valido {
    border-color: #198754 !important;
    background-color: #f8fff9 !important;
}

.documento-invalido {
    border-color: #dc3545 !important;
    background-color: #fff8f8 !important;
}

.documento-existe {
    border-color: #fd7e14 !important;
    background-color: #fff8f0 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const documentoInput = document.getElementById('documento');
    const telefoneInput = document.getElementById('telefone');
    const feedback = document.getElementById('documentoFeedback');
    
    // Formatação do documento (CPF/CNPJ)
    documentoInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length <= 11) {
            // CPF: 000.000.000-00
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else {
            // CNPJ: 00.000.000/0000-00
            value = value.replace(/(\d{2})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1/$2');
            value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
        }
        
        e.target.value = value;
        validarDocumento(value);
    });
    
    // Formatação do telefone
    telefoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length >= 11) {
            // Celular: (11) 99999-9999
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 10) {
            // Fixo: (11) 9999-9999
            value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 6) {
            value = value.replace(/(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
        } else if (value.length >= 2) {
            value = value.replace(/(\d{2})(\d+)/, '($1) $2');
        }
        
        e.target.value = value;
    });
    
    function validarDocumento(documento) {
        const documentoLimpo = documento.replace(/\D/g, '');
        
        if (documentoLimpo.length === 0) {
            feedback.textContent = '';
            documentoInput.className = documentoInput.className.replace(/documento-(valido|invalido|existe)/g, '');
            return;
        }
        
        if (documentoLimpo.length === 11) {
            const valido = validarCPF(documentoLimpo);
            if (valido) {
                feedback.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>CPF válido</span>';
                documentoInput.className = documentoInput.className.replace(/documento-(invalido|existe)/g, '') + ' documento-valido';
            } else {
                feedback.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>CPF inválido</span>';
                documentoInput.className = documentoInput.className.replace(/documento-(valido|existe)/g, '') + ' documento-invalido';
            }
        } else if (documentoLimpo.length === 14) {
            const valido = validarCNPJ(documentoLimpo);
            if (valido) {
                feedback.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>CNPJ válido</span>';
                documentoInput.className = documentoInput.className.replace(/documento-(invalido|existe)/g, '') + ' documento-valido';
            } else {
                feedback.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>CNPJ inválido</span>';
                documentoInput.className = documentoInput.className.replace(/documento-(valido|existe)/g, '') + ' documento-invalido';
            }
        } else if (documentoLimpo.length > 0) {
            feedback.innerHTML = '<span class="text-muted"><i class="bi bi-info-circle me-1"></i>Digite 11 (CPF) ou 14 (CNPJ) dígitos</span>';
            documentoInput.className = documentoInput.className.replace(/documento-(valido|invalido|existe)/g, '');
        }
    }
    
    function validarCPF(cpf) {
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        
        let soma = 0;
        for (let i = 0; i < 9; i++) {
            soma += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let resto = 11 - (soma % 11);
        let digito1 = resto === 10 || resto === 11 ? 0 : resto;
        
        soma = 0;
        for (let i = 0; i < 10; i++) {
            soma += parseInt(cpf.charAt(i)) * (11 - i);
        }
        resto = 11 - (soma % 11);
        let digito2 = resto === 10 || resto === 11 ? 0 : resto;
        
        return digito1 === parseInt(cpf.charAt(9)) && digito2 === parseInt(cpf.charAt(10));
    }
    
    function validarCNPJ(cnpj) {
        if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) return false;
        
        let soma = 0;
        let peso = 2;
        for (let i = 11; i >= 0; i--) {
            soma += parseInt(cnpj.charAt(i)) * peso;
            peso = peso === 9 ? 2 : peso + 1;
        }
        let digito1 = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        
        soma = 0;
        peso = 2;
        for (let i = 12; i >= 0; i--) {
            soma += parseInt(cnpj.charAt(i)) * peso;
            peso = peso === 9 ? 2 : peso + 1;
        }
        let digito2 = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        
        return digito1 === parseInt(cnpj.charAt(12)) && digito2 === parseInt(cnpj.charAt(13));
    }
});
</script>
@endsection