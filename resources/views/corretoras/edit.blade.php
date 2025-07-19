@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Editar Corretora</h1>
        <p class="text-muted mb-0">Atualize as informações da corretora {{ $corretora->nome }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('corretoras.show', $corretora) }}" class="btn btn-outline-primary">
            <i class="bi bi-eye me-2"></i>Visualizar
        </a>
        <a href="{{ route('corretoras.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>
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
            <form action="{{ route('corretoras.update', $corretora) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Informações Básicas -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2 mb-3">
                        <i class="bi bi-person-badge me-2"></i>Informações da Corretora
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nome" class="form-label">
                                Nome da Corretora <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('nome') is-invalid @enderror" 
                                   id="nome" 
                                   name="nome" 
                                   value="{{ old('nome', $corretora->nome) }}" 
                                   placeholder="Digite o nome da corretora"
                                   required>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $corretora->email) }}" 
                                   placeholder="email@corretora.com.br">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" 
                                   class="form-control @error('telefone') is-invalid @enderror" 
                                   id="telefone" 
                                   name="telefone" 
                                   value="{{ old('telefone', $corretora->telefone_formatado) }}" 
                                   placeholder="(11) 99999-9999">
                            @error('telefone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seguradoras Parceiras -->
                @if($seguradoras->count() > 0)
                <div class="mb-4">
                    <h5 class="border-bottom pb-2 mb-3">
                        <i class="bi bi-building me-2"></i>Seguradoras Parceiras
                    </h5>
                    
                    <p class="text-muted small mb-3">
                        Selecione as seguradoras que esta corretora trabalha (opcional)
                    </p>
                    
                    <div class="row">
                        @foreach($seguradoras as $seguradora)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           value="{{ $seguradora->id }}" 
                                           id="seguradora_{{ $seguradora->id }}" 
                                           name="seguradoras[]"
                                           {{ in_array($seguradora->id, old('seguradoras', $corretora->seguradoras->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="seguradora_{{ $seguradora->id }}">
                                        <strong>{{ $seguradora->nome }}</strong>
                                        @if($seguradora->site)
                                            <br><small class="text-muted">{{ $seguradora->site_formatado }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @error('seguradoras')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                    @error('seguradoras.*')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                @endif

                <!-- Botões -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Atualizar Corretora
                    </button>
                    <a href="{{ route('corretoras.show', $corretora) }}" class="btn btn-outline-secondary">
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
                <i class="bi bi-info-circle me-2"></i>Informações Atuais
            </h6>
            
            <div class="mb-3">
                <h6 class="small text-muted text-uppercase fw-bold">Criada em</h6>
                <p class="mb-0">{{ $corretora->created_at->format('d/m/Y \à\s H:i') }}</p>
            </div>
            
            @if($corretora->updated_at != $corretora->created_at)
            <div class="mb-3">
                <h6 class="small text-muted text-uppercase fw-bold">Última atualização</h6>
                <p class="mb-0">{{ $corretora->updated_at->format('d/m/Y \à\s H:i') }}</p>
            </div>
            @endif
            
            <hr>
            
            <div class="mb-3">
                <h6 class="small text-muted text-uppercase fw-bold">Estatísticas</h6>
                <ul class="list-unstyled small">
                    <li class="d-flex justify-content-between">
                        <span>Seguradoras parceiras:</span>
                        <strong>{{ $corretora->seguradoras->count() }}</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span>Cotações realizadas:</span>
                        <strong>{{ $corretora->cotacoes->count() ?? 0 }}</strong>
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
                    <i class="bi bi-check-circle text-success me-1"></i> 
                    O telefone será formatado automaticamente
                </li>
                <li class="mb-2">
                    <i class="bi bi-check-circle text-success me-1"></i> 
                    O email deve ser único no sistema
                </li>
                <li class="mb-2">
                    <i class="bi bi-check-circle text-success me-1"></i> 
                    Alterações nas seguradoras são salvas automaticamente
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

.form-check {
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    height: 100%;
}

.form-check:hover {
    border-color: #cbd5e1;
    background-color: #f8fafc;
}

.form-check-input:checked + .form-check-label {
    color: #1e40af;
}

.form-check-input:checked ~ .form-check {
    border-color: #3b82f6;
    background-color: #eff6ff;
}
</style>

<script>
// Formatação do telefone
document.getElementById('telefone').addEventListener('input', function (e) {
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
</script>
@endsection