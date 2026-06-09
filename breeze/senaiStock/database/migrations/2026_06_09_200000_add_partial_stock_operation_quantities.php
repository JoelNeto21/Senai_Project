<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_requests', function (Blueprint $table) {
            $table->unsignedInteger('fulfilled_quantity')->default(0)->after('quantity');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->unsignedInteger('received_quantity')->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('received_quantity');
        });

        Schema::table('teacher_requests', function (Blueprint $table) {
            $table->dropColumn('fulfilled_quantity');
        });
    }
};
