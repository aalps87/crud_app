<?php

namespace Database\Seeders;

use App\Models\Aeronave;
use Illuminate\Database\Seeder;

class AeronaveSeeder extends Seeder
{
    public function run(): void
    {
        $aeronaves = [
            ['prefixo' => 'PR-AXB', 'foto' => 'assets/aeronaves/pr-axb.jpg', 'modelo' => 'Embraer E195-E2', 'tripulacao' => 6, 'data_fab' => '2021-03-14', 'publico' => true],
            ['prefixo' => 'PS-GLQ', 'foto' => 'assets/aeronaves/ps-glq.jpg', 'modelo' => 'Airbus A320neo', 'tripulacao' => 6, 'data_fab' => '2019-08-02', 'publico' => true],
            ['prefixo' => 'PR-MHV', 'foto' => 'assets/aeronaves/pr-mhv.jpg', 'modelo' => 'Boeing 737 MAX 8', 'tripulacao' => 7, 'data_fab' => '2022-11-21', 'publico' => true],
            ['prefixo' => 'PP-PTK', 'foto' => 'assets/aeronaves/pp-ptk.jpg', 'modelo' => 'ATR 72-600', 'tripulacao' => 4, 'data_fab' => '2016-05-09', 'publico' => false],
            ['prefixo' => 'PT-ZEN', 'foto' => 'assets/aeronaves/pt-zen.jpg', 'modelo' => 'Embraer Phenom 300E', 'tripulacao' => 2, 'data_fab' => '2023-01-30', 'publico' => true],
            ['prefixo' => 'PR-KLO', 'foto' => 'assets/aeronaves/pr-klo.jpg', 'modelo' => 'Cessna 208B Grand Caravan', 'tripulacao' => 1, 'data_fab' => '2012-09-17', 'publico' => false],
        ];

        foreach ($aeronaves as $data) {
            $aeronave = Aeronave::updateOrCreate(
                ['prefixo' => $data['prefixo']],
                ['modelo' => $data['modelo'], 'tripulacao' => $data['tripulacao'], 'data_fab' => $data['data_fab'], 'foto' => $data['foto']],
            );
            $aeronave->publicacao()->updateOrCreate([], ['publico' => $data['publico']]);
        }
    }
}
