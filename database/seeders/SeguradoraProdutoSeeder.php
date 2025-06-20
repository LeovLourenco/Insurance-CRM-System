<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seguradora;
use App\Models\Produto;
use App\Models\SeguradoraProduto;
use Illuminate\Support\Facades\DB;

class SeguradoraProdutoSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/dados/relação_seguradora_produtos2.csv');

        if (!file_exists($path)) {
            echo "❌ Arquivo não encontrado: {$path}\n";
            return;
        }

        // Usa ; como delimitador, já que seu CSV vem assim
        $linhas = array_map(function ($linha) {
            return str_getcsv($linha, ';');
        }, file($path));

        // Normaliza o cabeçalho (remove BOM e espaços estranhos)
        $header = array_map(function ($value) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value));
        }, array_shift($linhas));

        foreach ($linhas as $linha) {
            $data = array_combine($header, $linha);

            if (!$data || empty($data['seguradora_nome']) || empty($data['produto_nome'])) {
                echo "⚠️ Linha inválida ou incompleta: " . json_encode($linha) . "\n";
                continue;
            }

            $seguradoraNome = trim($data['seguradora_nome']);
            $produtoNome = trim($data['produto_nome']);

            $seguradora = Seguradora::where('nome', $seguradoraNome)->first();
            $produto = Produto::where('nome', $produtoNome)->first();

            if ($seguradora && $produto) {
                $existe = DB::table('seguradora_produto')
                    ->where('seguradora_id', $seguradora->id)
                    ->where('produto_id', $produto->id)
                    ->exists();

                if (!$existe) {
                    DB::table('seguradora_produto')->insert([
                        'seguradora_id' => $seguradora->id,
                        'produto_id' => $produto->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    echo "✔ Vínculo criado: {$seguradoraNome} → {$produtoNome}\n";
                } else {
                    echo "ℹ Já existe: {$seguradoraNome} → {$produtoNome}\n";
                }
            } else {
                echo "❌ Não encontrado: {$seguradoraNome} ou {$produtoNome}\n";
            }
        }

        echo "✅ Importação concluída.\n";
    }
}
