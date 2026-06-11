<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_requests', function (Blueprint $table) {
            $table->timestamp('notifications_dismissed_at')->nullable()->after('notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_requests', function (Blueprint $table) {
            $table->dropColumn('notifications_dismissed_at');
        });
    }
};
