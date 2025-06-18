@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Lista de Cotações</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Corretora</th>
                <th>Produto</th>
                <th>Status</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotacoes as $c)
                <tr>
                    <td>{{ $c->corretora->nome }}</td>
                    <td>{{ $c->produto->nome }}</td>
                    <td>{{ ucfirst($c->status) }}</td>
                    <td>{{ $c->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
