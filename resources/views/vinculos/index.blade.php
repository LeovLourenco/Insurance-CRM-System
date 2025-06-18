@extends('layouts.app') {{-- Use seu layout se houver --}}

@section('content')
<div class="container">
    <h2>Vínculos entre Corretoras, Seguradoras e Produtos</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('vinculos.store') }}" class="mb-4">
        @csrf
        <div class="row">
            <div class="col">
                <label>Corretora</label>
                <select name="corretora_id" class="form-control" required>
                    @foreach($corretoras as $c)
                        <option value="{{ $c->id }}">{{ $c->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <label>Produto</label>
                <select name="produto_id" class="form-control" required>
                    @foreach($produtos as $p)
                        <option value="{{ $p->id }}">{{ $p->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <label>Seguradora</label>
                <select name="seguradora_id" class="form-control" required>
                    @foreach($seguradoras as $s)
                        <option value="{{ $s->id }}">{{ $s->nome }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                <label>Canal</label>
                <input type="text" name="canal" class="form-control" placeholder="E-mail, Portal...">
            </div>
            <div class="col">
                <label>Observações</label>
                <input type="text" name="observacoes" class="form-control">
            </div>
            <div class="col">
                <label>&nbsp;</label>
                <button class="btn btn-primary d-block w-100">Cadastrar</button>
            </div>
        </div>
    </form>

    <h4>Vínculos cadastrados:</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Corretora</th>
                <th>Produto</th>
                <th>Seguradora</th>
                <th>Canal</th>
                <th>Observações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vinculos as $v)
                <tr>
                    <td>{{ $v->corretora->nome }}</td>
                    <td>{{ $v->produto->nome }}</td>
                    <td>{{ $v->seguradora->nome }}</td>
                    <td>{{ $v->canal }}</td>
                    <td>{{ $v->observacoes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
