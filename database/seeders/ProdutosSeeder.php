<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produto;

class ProdutosSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/dados/produtos.csv');
        $linhas = array_map('str_getcsv', file($path));

        // Corrige o possível BOM (Byte Order Mark) no cabeçalho
        $header = array_map(function ($value) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value));
        }, array_shift($linhas));

        foreach ($linhas as $linha) {
            $data = array_combine($header, $linha);

            Produto::firstOrCreate(
                ['nome' => trim($data['nome'])]
            );
        }
    }
}
