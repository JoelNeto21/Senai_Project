<?php

namespace Database\Seeders;

use App\Models\Cargo;
use Illuminate\Database\Seeder;

class CargoSeeder extends Seeder
{
    public function run(): void
    {
        collect(['Administrador', 'Almoxarife', 'Professor', 'Coordenador'])
            ->each(fn (string $cargo) => Cargo::firstOrCreate(['Nome_cargo' => $cargo]));
    }
}
