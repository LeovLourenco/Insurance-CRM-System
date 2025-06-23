@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Perfil do Usuário</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('usuario.atualizar') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome', $usuario->nome) }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('home') }}" class="btn btn-secondary">Voltar</a>
                <button type="submit" class="btn btn-success">Atualizar Perfil</button>
            </div>
        </form>
    </div>
</div>
@endsection
