<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        if (!Supplier::query()->exists()) {
            Supplier::create([
                'name' => 'Editora SENAI-SP',
                'contact_name' => 'Atendimento Corporativo',
                'email' => 'pedidos@editorasenai.com.br',
                'phone' => '(11) 3000-0101',
                'lead_time_days' => 5,
                'status' => 'ativo',
            ]);
        }
    }
}
