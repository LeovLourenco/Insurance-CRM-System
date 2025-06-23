@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cotação registrada com sucesso!</h2>

    <p><strong>Corretora:</strong> {{ $cotacao->corretora->nome }}</p>
    <p><strong>Produto:</strong> {{ $cotacao->produto->nome }}</p>

    <h4>Seguradoras disponíveis:</h4>
    @if($seguradoras->count())
        <ul class="list-group mb-4">
            @foreach($seguradoras as $s)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $s->nome }}
                    <span class="badge bg-secondary">{{ $s->site }}</span>
                </li>
            @endforeach
        </ul>
    @else
        <div class="alert alert-warning">Nenhuma seguradora disponível para esta corretora e produto.</div>
    @endif

    <h4>Histórico de Atividades</h4>
    @if ($cotacao->atividades->isEmpty())
        <p>Nenhuma atividade registrada.</p>
    @else
        <ul class="list-group">
            @foreach ($cotacao->atividades as $atividade)
                <li class="list-group-item">
                    {{ $atividade->created_at->format('d/m/Y H:i') }} – {{ $atividade->descricao }}
                </li>
            @endforeach
        </ul>
    @endif

    <a href="{{ route('cotacoes.index') }}" class="btn btn-outline-secondary mt-4">Ver todas as cotações</a>
</div>
@endsection
