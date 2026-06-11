<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('notifications_dismissed_message_id')
                ->nullable()
                ->after('notifications_dismissed_at');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_requests', function (Blueprint $table) {
            $table->dropColumn('notifications_dismissed_message_id');
        });
    }
};
