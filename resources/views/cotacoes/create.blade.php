@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nova Cotação</h2>
    <form action="{{ route('cotacoes.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Corretora:</label>
            <select name="corretora_id" class="form-select" required>
                <option value="">Selecione</option>
                @foreach ($corretoras as $c)
                    <option value="{{ $c->id }}" {{ $c->id == $corretoraId ? 'selected' : '' }}>
                        {{ $c->nome }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Produto:</label>
            <select name="produto_id" class="form-select" required>
                <option value="">Selecione</option>
                @foreach ($produtos as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $produtoId ? 'selected' : '' }}>
                        {{ $p->nome }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Seguradora:</label>
            <select name="seguradora_id" class="form-select" required>
                <option value="">Selecione</option>
                @foreach ($seguradoras as $s)
                    <option value="{{ $s->id }}" {{ $s->id == $seguradoraId ? 'selected' : '' }}>
                        {{ $s->nome }}
                    </option>
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
