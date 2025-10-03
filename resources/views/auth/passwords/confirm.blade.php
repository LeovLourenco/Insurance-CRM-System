@extends('layouts.auth')

@section('title', 'Confirmar Senha - Sistema de Cotações')

@section('content')
<div class="text-center mb-4">
    <h4 class="mb-2">Confirmar Senha</h4>
    <p class="text-muted">Por favor, confirme sua senha antes de continuar</p>
</div>

<form method="POST" action="{{ route('password.confirm') }}">
    @csrf
    
    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="bi bi-lock me-2"></i>Senha Atual
        </label>
        <input id="password" type="password" 
               class="form-control @error('password') is-invalid @enderror" 
               name="password" 
               required autocomplete="current-password"
               placeholder="Digite sua senha atual">
        @error('password')
            <div class="invalid-feedback">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
            </div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">
        <i class="bi bi-check-circle me-2"></i>Confirmar Senha
    </button>
</form>

<div class="auth-links">
    @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}">
            <i class="bi bi-question-circle me-2"></i>Esqueceu sua senha?
        </a>
    @endif
</div>
@endsection
