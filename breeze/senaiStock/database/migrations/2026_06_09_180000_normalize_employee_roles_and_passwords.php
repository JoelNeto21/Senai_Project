<?php

use App\Models\Cargo;
use App\Models\Funcionario;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $allowedNames = ['Professor', 'Coordenador'];
            $allowedCargos = collect($allowedNames)
                ->mapWithKeys(function (string $name): array {
                    $cargos = Cargo::where('Nome_cargo', $name)->orderBy('Id_cargo')->get();
                    $cargo = $cargos->shift() ?? Cargo::create(['Nome_cargo' => $name]);

                    $cargos->each(function (Cargo $duplicate) use ($cargo): void {
                        Funcionario::where('Id_cargo_FK', $duplicate->Id_cargo)
                            ->update(['Id_cargo_FK' => $cargo->Id_cargo]);
                        $duplicate->delete();
                    });

                    return [$name => $cargo];
                });

            $coordenador = $allowedCargos->get('Coordenador');

            Cargo::whereNotIn('Nome_cargo', $allowedNames)->each(function (Cargo $cargo) use ($coordenador): void {
                Funcionario::where('Id_cargo_FK', $cargo->Id_cargo)
                    ->update(['Id_cargo_FK' => $coordenador->Id_cargo]);
                $cargo->delete();
            });

            Funcionario::query()->each(
                fn (Funcionario $funcionario) => $funcionario->update(['password' => 'senai123'])
            );
        });
    }

    public function down(): void
    {
        //
    }
};
