@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Cadastro</h2>

    <!-- Seletor de tipo de cadastro -->
    <div class="mb-3">
        <label for="tipoCadastro" class="form-label">Escolha o que deseja cadastrar:</label>
        <select class="form-select" id="tipoCadastro">
            <option value="" selected>Selecione...</option>
            <option value="segurado">Segurado</option>
            <option value="corretora">Corretora</option>
            <option value="seguradora">Seguradora</option>
        </select>
    </div>

    <!-- Formulário de Segurado -->
    <div id="formSegurado" class="collapse">
        <form action="{{ route('segurados.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="documento" class="form-label">CPF ou CNPJ</label>
                <input type="text" name="documento" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar Segurado</button>
        </form>
    </div>

    <!-- Formulário de Corretora -->
    <div id="formCorretora" class="collapse">
        <form action="{{ route('corretoras.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Corretora</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="vincularSeguradora">
                <label class="form-check-label" for="vincularSeguradora">Deseja vincular seguradoras?</label>
            </div>
            <div class="mb-3 collapse" id="selectSeguradoras">
                <label for="seguradoras" class="form-label">Seguradoras</label>
                <select name="seguradoras[]" class="form-select" multiple>
                    @foreach($seguradoras as $s)
                        <option value="{{ $s->id }}">{{ $s->nome }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar Corretora</button>
        </form>
    </div>

    <!-- Formulário de Seguradora -->
    <div id="formSeguradora" class="collapse">
        <form action="{{ route('seguradoras.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Seguradora</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="vincularProduto">
                <label class="form-check-label" for="vincularProduto">Deseja vincular produtos?</label>
            </div>
            <div class="mb-3 collapse" id="selectProdutos">
                <label for="produtos" class="form-label">Produtos</label>
                <select name="produtos[]" class="form-select" multiple>
                    @foreach($produtos as $p)
                        <option value="{{ $p->id }}">{{ $p->nome }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar Seguradora</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('tipoCadastro').addEventListener('change', function () {
        const tipo = this.value;
        document.getElementById('formSegurado').classList.remove('show');
        document.getElementById('formCorretora').classList.remove('show');
        document.getElementById('formSeguradora').classList.remove('show');

        if (tipo === 'segurado') {
            document.getElementById('formSegurado').classList.add('show');
        } else if (tipo === 'corretora') {
            document.getElementById('formCorretora').classList.add('show');
        } else if (tipo === 'seguradora') {
            document.getElementById('formSeguradora').classList.add('show');
        }
    });

    document.getElementById('vincularSeguradora').addEventListener('change', function () {
        document.getElementById('selectSeguradoras').classList.toggle('show', this.checked);
    });

    document.getElementById('vincularProduto').addEventListener('change', function () {
        document.getElementById('selectProdutos').classList.toggle('show', this.checked);
    });
</script>
@endsection
