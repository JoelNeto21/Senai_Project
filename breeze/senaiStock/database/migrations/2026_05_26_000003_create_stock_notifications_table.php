<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('info');
            $table->string('severity')->default('info');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('action_url')->nullable();
            $table->unsignedBigInteger('teacher_request_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('teacher_request_id')
                ->references('id')
                ->on('teacher_requests')
                ->nullOnDelete();
            $table->foreign('book_id')
                ->references('id')
                ->on('books')
                ->nullOnDelete();
            $table->index(['read_at', 'created_at']);
            $table->index(['type', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_notifications');
    }
};
