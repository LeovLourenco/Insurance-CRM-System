@extends('layouts.auth')

@section('title', 'Recuperar Senha - Sistema de Cotações')

@section('content')
<div class="text-center mb-4">
    <h4 class="mb-2">Recuperar Senha</h4>
    <p class="text-muted">Digite seu email para receber o link de redefinição</p>
</div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf
    
    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="bi bi-envelope me-2"></i>E-mail
        </label>
        <input id="email" type="email" 
               class="form-control @error('email') is-invalid @enderror" 
               name="email" 
               value="{{ old('email') }}" 
               required autocomplete="email" autofocus
               placeholder="Digite seu e-mail">
        @error('email')
            <div class="invalid-feedback">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
            </div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">
        <i class="bi bi-send me-2"></i>Enviar Link de Recuperação
    </button>
</form>

<div class="auth-links">
    <a href="{{ route('login') }}">
        <i class="bi bi-arrow-left me-2"></i>Voltar ao Login
    </a>
</div>
@endsection
