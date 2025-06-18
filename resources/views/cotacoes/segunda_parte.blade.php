@extends('layouts.app') {{-- Ajuste para seu layout principal --}}

@section('content')
<div class="container">
    <h1>Seguradoras disponíveis para a cotação #{{ $cotacao->id }}</h1>

    <p><strong>Corretora:</strong> {{ $cotacao->corretora->nome }}</p>
    <p><strong>Produto:</strong> {{ $cotacao->produto->nome }}</p>

    @if ($seguradoras->isEmpty())
        <p>Nenhuma seguradora disponível para este produto e corretora.</p>
    @else
        <ul class="list-group">
            @foreach ($seguradoras as $seguradora)
                <li class="list-group-item">{{ $seguradora->nome }}</li>
            @endforeach
        </ul>
    @endif

    <a href="{{ route('cotacoes.index') }}" class="btn btn-secondary mt-3">Voltar para lista de cotações</a>
</div>
@endsection
