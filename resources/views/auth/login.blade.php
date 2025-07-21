@extends('layouts.auth')

@section('content')
<!-- Alerts -->
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        @foreach ($errors->all() as $error)
            {{ $error }}
        @endforeach
    </div>
@endif

@if (session('status'))
    <div class="alert alert-success" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('status') }}
    </div>
@endif

<!-- Formulário de Login -->
<form method="POST" action="{{ route('login') }}">
    @csrf
    
    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="bi bi-envelope me-1"></i>Email
        </label>
        <input type="email" 
               class="form-control @error('email') is-invalid @enderror" 
               id="email" 
               name="email" 
               value="{{ old('email') }}" 
               required 
               autofocus
               placeholder="seu@email.com">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="bi bi-lock me-1"></i>Senha
        </label>
        <input type="password" 
               class="form-control @error('password') is-invalid @enderror" 
               id="password" 
               name="password" 
               required
               placeholder="••••••••">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" 
               class="form-check-input" 
               id="remember" 
               name="remember" 
               {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label" for="remember">
            Lembrar de mim
        </label>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-right me-2"></i>
            Entrar
        </button>
    </div>

    @if (Route::has('password.request'))
        <div class="text-center mt-3">
            <a href="{{ route('password.request') }}" class="text-decoration-none">
                <small>Esqueceu sua senha?</small>
            </a>
        </div>
    @endif
</form>
@endsection