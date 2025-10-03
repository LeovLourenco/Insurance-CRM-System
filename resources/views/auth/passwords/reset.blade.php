@extends('layouts.auth')

@section('title', 'Redefinir Senha - Sistema de Cotações')

@section('content')
<div class="text-center mb-4">
    <h4 class="mb-2">Redefinir Senha</h4>
    <p class="text-muted">Digite sua nova senha abaixo</p>
</div>

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    
    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="bi bi-envelope me-2"></i>E-mail
        </label>
        <input id="email" type="email" 
               class="form-control @error('email') is-invalid @enderror" 
               name="email" 
               value="{{ $email ?? old('email') }}" 
               required autocomplete="email" autofocus
               placeholder="Digite seu e-mail">
        @error('email')
            <div class="invalid-feedback">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="bi bi-lock me-2"></i>Nova Senha
        </label>
        <input id="password" type="password" 
               class="form-control @error('password') is-invalid @enderror" 
               name="password" 
               required autocomplete="new-password"
               placeholder="Digite sua nova senha">
        @error('password')
            <div class="invalid-feedback">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
            </div>
        @enderror
        <small class="form-text text-muted">Mínimo 8 caracteres</small>
    </div>

    <div class="mb-3">
        <label for="password-confirm" class="form-label">
            <i class="bi bi-lock-fill me-2"></i>Confirmar Nova Senha
        </label>
        <input id="password-confirm" type="password" 
               class="form-control" 
               name="password_confirmation" 
               required autocomplete="new-password"
               placeholder="Confirme sua nova senha">
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">
        <i class="bi bi-key me-2"></i>Redefinir Senha
    </button>
</form>

<div class="auth-links">
    <a href="{{ route('login') }}">
        <i class="bi bi-arrow-left me-2"></i>Voltar ao Login
    </a>
</div>
@endsection
