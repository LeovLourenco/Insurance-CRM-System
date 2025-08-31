@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <!-- Profile Avatar Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-user-circle me-2"></i>Avatar do Perfil</h6>
                </div>
                <div class="card-body text-center">
                    <div class="avatar-container mb-3">
                        <x-avatar :name="$usuario->name" size="xl" class="mx-auto" />
                    </div>
                    <h5 class="mb-1">{{ $usuario->name }}</h5>
                    <p class="text-muted small mb-0">{{ $usuario->email }}</p>
                </div>
            </div>

            <!-- User Activity Card -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Informações da Conta</h6>
                </div>
                <div class="card-body">
                    <div class="row g-0">
                        <div class="col-6 text-center border-end">
                            <div class="p-2">
                                <h5 class="text-primary">{{ $usuario->created_at->format('d/m/Y') }}</h5>
                                <small class="text-muted">Cadastrado em</small>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="p-2">
                                <h5 class="text-success">{{ $usuario->updated_at->diffForHumans() }}</h5>
                                <small class="text-muted">Última atualização</small>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <span class="badge bg-{{ $usuario->email_verified_at ? 'success' : 'warning' }}">
                            <i class="fas fa-{{ $usuario->email_verified_at ? 'check-circle' : 'exclamation-triangle' }} me-1"></i>
                            E-mail {{ $usuario->email_verified_at ? 'Verificado' : 'Não Verificado' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Personal Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Informações Pessoais</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('usuario.atualizar') }}" method="POST" id="perfilForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nome') is-invalid @enderror" 
                                       id="nome" name="nome" value="{{ old('nome', $usuario->name) }}" required>
                                @error('nome')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control @error('telefone') is-invalid @enderror" 
                                       id="telefone" name="telefone" value="{{ old('telefone', $usuario->telefone ?? '') }}" 
                                       placeholder="(00) 00000-0000">
                                @error('telefone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cpf" class="form-label">CPF</label>
                                <input type="text" class="form-control @error('cpf') is-invalid @enderror" 
                                       id="cpf" name="cpf" value="{{ old('cpf', $usuario->cpf ?? '') }}" 
                                       placeholder="000.000.000-00" readonly>
                                @error('cpf')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">CPF não pode ser alterado</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="endereco" class="form-label">Endereço</label>
                                <input type="text" class="form-control @error('endereco') is-invalid @enderror" 
                                       id="endereco" name="endereco" value="{{ old('endereco', $usuario->endereco ?? '') }}">
                                @error('endereco')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cep" class="form-label">CEP</label>
                                <input type="text" class="form-control @error('cep') is-invalid @enderror" 
                                       id="cep" name="cep" value="{{ old('cep', $usuario->cep ?? '') }}" 
                                       placeholder="00000-000">
                                @error('cep')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('home') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Voltar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Atualizar Perfil
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Alterar Senha</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('usuario.alterar.senha') }}" method="POST" id="senhaForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="current_password" class="form-label">Senha Atual <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" name="current_password" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Nova Senha <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required minlength="8">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Mínimo 8 caracteres</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Nova Senha <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required minlength="8">
                                <small class="form-text text-muted">Repita a nova senha</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key me-1"></i>Alterar Senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.card-header h5, .card-header h6 {
    font-weight: 600;
}

.form-label {
    font-weight: 500;
}

.text-danger {
    font-size: 0.8em;
}

.avatar-inova {
    user-select: none;
    letter-spacing: 1px;
}
</style>

<script>

// Phone number formatting
document.getElementById('telefone').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 11) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (value.length >= 7) {
        value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    } else if (value.length >= 3) {
        value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
    }
    e.target.value = value;
});

// CEP formatting
document.getElementById('cep').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 5) {
        value = value.replace(/(\d{5})(\d{0,3})/, '$1-$2');
    }
    e.target.value = value;
});

// Password confirmation validation
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    
    if (password !== confirmation) {
        this.setCustomValidity('As senhas não coincidem');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});

// Form validation feedback
document.getElementById('perfilForm').addEventListener('submit', function(e) {
    const form = this;
    if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    form.classList.add('was-validated');
});

document.getElementById('senhaForm').addEventListener('submit', function(e) {
    const form = this;
    if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    form.classList.add('was-validated');
});
</script>
@endsection
