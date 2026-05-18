<?php

namespace Database\Seeders;

use App\Models\Cargo;
use Illuminate\Database\Seeder;

class CargoSeeder extends Seeder
{
    public function run(): void
    {
        Cargo::create(['Nome_cargo' => 'Almoxarife']);
        Cargo::create(['Nome_cargo' => 'Professor']);
        Cargo::create(['Nome_cargo' => 'Coordenador']);
    }
}