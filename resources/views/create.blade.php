{{-- resources/views/cotacoes/create.blade.php --}}
@extends('layouts.app') {{-- se tiver um layout principal, se não, pode tirar --}}

@section('content')
<div class="container">
    <h1>Nova Cotação</h1>

    {{-- Exibir erros de validação --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                   <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('cotacoes.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="corretora_id" class="form-label">Corretora</label>
            <select name="corretora_id" id="corretora_id" class="form-select" required>
                <option value="">Selecione uma corretora</option>
                @foreach($corretoras as $corretora)
                    <option value="{{ $corretora->id }}">{{ $corretora->nome }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="produto" class="form-label">Produto</label>
            <input type="text" name="produto" id="produto" class="form-control" required>
            {{-- Pode ser select também, dependendo dos seus produtos --}}
        </div>

        {{-- Outros campos que queira adicionar aqui --}}

        <button type="submit" class="btn btn-primary">Cadastrar Cotação</button>
    </form>
</div>
@endsection
