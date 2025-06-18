@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nova Cotação</h2>
    <form action="{{ route('cotacoes.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Corretora:</label>
            <select name="corretora_id" class="form-control" required>
                @foreach($corretoras as $c)
                    <option value="{{ $c->id }}">{{ $c->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Produto:</label>
            <select name="produto_id" class="form-control" required>
                @foreach($produtos as $p)
                    <option value="{{ $p->id }}">{{ $p->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Observações:</label>
            <textarea name="observacoes" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar Cotação</button>
    </form>
</div>
@endsection
