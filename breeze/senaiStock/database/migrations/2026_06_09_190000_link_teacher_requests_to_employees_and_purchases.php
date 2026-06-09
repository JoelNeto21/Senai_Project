<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('requested_by_funcionario_id')->nullable()->after('protocol');
            $table->foreign('requested_by_funcionario_id')
                ->references('Id_funcionario')
                ->on('funcionarios')
                ->nullOnDelete();
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('teacher_request_id')
                ->nullable()
                ->after('purchase_order_id')
                ->constrained('teacher_requests')
                ->nullOnDelete();
        });

        $supplierId = DB::table('suppliers')->where('status', 'ativo')->value('id');

        DB::table('teacher_requests')
            ->whereIn('status', ['pendente', 'compra'])
            ->whereNotNull('book_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $teacherRequest) use ($supplierId): void {
                $available = (int) DB::table('books')->where('id', $teacherRequest->book_id)->value('quantity');
                $missing = max((int) $teacherRequest->quantity - $available, 0);

                if ($missing === 0) {
                    return;
                }

                $purchaseOrderId = DB::table('purchase_orders')->insertGetId([
                    'order_number' => 'PED-'.now()->format('ymd-His').'-'.Str::upper(Str::random(5)),
                    'supplier_id' => $supplierId,
                    'requested_by_funcionario_id' => $teacherRequest->requested_by_funcionario_id,
                    'status' => 'pendente_aprovacao',
                    'generated_at' => now(),
                    'notes' => "Compra automatica para a solicitacao {$teacherRequest->protocol}.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('purchase_order_items')->insert([
                    'purchase_order_id' => $purchaseOrderId,
                    'teacher_request_id' => $teacherRequest->id,
                    'book_id' => $teacherRequest->book_id,
                    'title' => $teacherRequest->title,
                    'quantity' => $missing,
                    'unit_cost' => null,
                    'type' => 'restock',
                    'justification' => "Quantidade faltante para atender {$teacherRequest->teacher_name}.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('teacher_requests')->where('id', $teacherRequest->id)->update([
                    'status' => 'compra',
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_request_id');
        });

        Schema::table('teacher_requests', function (Blueprint $table) {
            $table->dropForeign(['requested_by_funcionario_id']);
            $table->dropColumn('requested_by_funcionario_id');
        });
    }
};
