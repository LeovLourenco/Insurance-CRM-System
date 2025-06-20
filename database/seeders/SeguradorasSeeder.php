<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seguradora;

class SeguradorasSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('app/dados/seguradoras.csv');

        if (!file_exists($path)) {
            echo "❌ Arquivo não encontrado: {$path}\n";
            return;
        }

        $linhas = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Remove BOM da primeira linha (se existir)
        if (isset($linhas[0])) {
            $linhas[0] = preg_replace('/^\xEF\xBB\xBF/', '', $linhas[0]);
        }

        $linhas = array_map('str_getcsv', $linhas);

        $header = array_map('trim', array_shift($linhas));

        if (!in_array('nome', $header)) {
            echo "❌ Cabeçalho inválido: coluna 'nome' não encontrada\n";
            return;
        }

        foreach ($linhas as $index => $linha) {
            if (count($linha) !== count($header)) {
                echo "⚠ Linha " . ($index + 2) . " ignorada: número de colunas incompatível\n";
                continue;
            }

            $data = array_combine($header, $linha);

            if (!isset($data['nome']) || empty(trim($data['nome']))) {
                echo "⚠ Linha " . ($index + 2) . " ignorada: nome vazio\n";
                continue;
            }

            Seguradora::firstOrCreate([
                'nome' => trim($data['nome'])
            ]);

            echo "✔ Seguradora importada: " . trim($data['nome']) . "\n";
        }

        echo "✅ Importação concluída.\n";
    }
}
