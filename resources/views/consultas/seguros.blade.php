@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Consulta de Seguros Disponíveis</h2>

    <form action="{{ route('consultas.buscar') }}" method="POST" class="mb-4">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label>Corretora</label>
                <select name="corretora_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach ($corretoras as $c)
                        <option value="{{ $c->id }}">{{ $c->nome }}</option>
                    @endforeach

                </select>
            </div>
            <div class="col-md-6">
                <label>Produto</label>
                <select name="produto_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach ($produtos as $p)
                        <option value="{{ $p->id }}">{{ $p->nome }}</option>
                    @endforeach

                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Buscar</button>
    </form>

    @isset($seguradoras)
        <h4>Seguradoras que atendem:</h4>
        <p><strong>Corretora:</strong> {{ $corretora->nome }} <br>
        <strong>Produto:</strong> {{ $produto->nome }}</p>

        @if ($seguradoras->isEmpty())
            <div class="alert alert-warning mt-3">Nenhuma seguradora encontrada para esses critérios.</div>
        @else
            <table class="table table-bordered mt-3">
                <thead class="table-light">
                    <tr>
                        <th>Seguradora</th>
                        <th>Capacidade</th>
                        <th>Taxa</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($seguradoras as $s)
                        <tr>
                            <td>{{ $s->nome }}</td>
                            <td>{{ $s->capacidade ?? '---' }}</td>
                            <td>{{ $s->taxa ?? '---' }}</td>
                            <td>
                                <a href="{{ route('cotacoes.create', [
                                    'corretora_id' => $corretora->id,
                                    'produto_id' => $produto->id,
                                    'seguradora_id' => $s->id
                                ]) }}" class="btn btn-sm btn-primary">Gerar Cotação</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

    @endisset
</div>
@endsection
