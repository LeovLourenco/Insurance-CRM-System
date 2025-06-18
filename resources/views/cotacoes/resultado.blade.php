@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cotação registrada com sucesso!</h2>

    <p><strong>Corretora:</strong> {{ $cotacao->corretora->nome }}</p>
    <p><strong>Produto:</strong> {{ $cotacao->produto->nome }}</p>

    <h4>Seguradoras disponíveis:</h4>
    @if($seguradoras->count())
        <ul class="list-group">
            @foreach($seguradoras as $s)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $s->nome }}
                    <span class="badge bg-secondary">{{ $s->site }}</span>
                </li>
            @endforeach
        </ul>
    @else
        <div class="alert alert-warning mt-3">Nenhuma seguradora disponível para esta corretora e produto.</div>
    @endif

    <a href="{{ route('cotacoes.index') }}" class="btn btn-outline-secondary mt-4">Ver todas as cotações</a>
</div>
@endsection
