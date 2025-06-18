{{-- resources/views/cotacoes/show_seguradoras.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Seguradoras Disponíveis para a Cotação #{{ $cotacao->id }}</h1>

    <p><strong>Corretora:</strong> {{ $cotacao->corretora->nome }}</p>
    <p><strong>Produto:</strong> {{ $cotacao->produto }}</p>

    @if ($seguradorasDisponiveis->isEmpty())
        <p>Nenhuma seguradora disponível para essa cotação.</p>
    @else
        <ul>
            @foreach ($seguradorasDisponiveis as $seguradora)
                <li>{{ $seguradora->nome }}</li>
            @endforeach
        </ul>
    @endif

    <a href="{{ route('cotacoes.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
