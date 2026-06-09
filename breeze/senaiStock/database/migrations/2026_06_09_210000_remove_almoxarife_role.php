<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $coordenadorId = DB::table('cargos')->where('Nome_cargo', 'Coordenador')->value('Id_cargo');
        $removedRoleIds = DB::table('cargos')->whereNotIn('Nome_cargo', ['Professor', 'Coordenador'])->pluck('Id_cargo');

        if ($coordenadorId && $removedRoleIds->isNotEmpty()) {
            DB::table('funcionarios')
                ->whereIn('Id_cargo_FK', $removedRoleIds)
                ->update(['Id_cargo_FK' => $coordenadorId]);
        }

        DB::table('cargos')->whereIn('Id_cargo', $removedRoleIds)->delete();
    }

    public function down(): void
    {
        //
    }
};
