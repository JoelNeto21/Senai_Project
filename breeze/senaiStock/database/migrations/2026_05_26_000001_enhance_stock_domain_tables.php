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
        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'description')) {
                $table->text('description')->nullable()->after('subject');
            }

            if (!Schema::hasColumn('books', 'minimum_stock')) {
                $table->unsignedInteger('minimum_stock')->default(10)->after('quantity');
            }

            if (!Schema::hasColumn('books', 'location')) {
                $table->string('location')->nullable()->after('minimum_stock');
            }

            if (!Schema::hasColumn('books', 'status')) {
                $table->string('status')->default('ativo')->after('location');
            }
        });

        Schema::table('teacher_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_requests', 'protocol')) {
                $table->string('protocol')->nullable()->after('id');
            }

            if (!Schema::hasColumn('teacher_requests', 'course_name')) {
                $table->string('course_name')->nullable()->after('class_name');
            }

            if (!Schema::hasColumn('teacher_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('teacher_requests', 'prepared_at')) {
                $table->timestamp('prepared_at')->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('teacher_requests', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('prepared_at');
            }

            if (!Schema::hasColumn('teacher_requests', 'notified_at')) {
                $table->timestamp('notified_at')->nullable()->after('rejected_at');
            }
        });

        DB::table('teacher_requests')
            ->whereNull('protocol')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (object $request): void {
                DB::table('teacher_requests')
                    ->where('id', $request->id)
                    ->update([
                        'protocol' => 'SS-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                    ]);
            });

        Schema::table('teacher_requests', function (Blueprint $table) {
            $table->unique('protocol');
            $table->index(['status', 'created_at']);
            $table->index(['teacher_email', 'protocol']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->index(['status', 'subject']);
            $table->index(['quantity', 'minimum_stock']);
        });
    }

    public function down(): void
    {
        Schema::table('teacher_requests', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['teacher_email', 'protocol']);
            $table->dropUnique(['protocol']);
            $table->dropColumn([
                'protocol',
                'course_name',
                'approved_at',
                'prepared_at',
                'rejected_at',
                'notified_at',
            ]);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['status', 'subject']);
            $table->dropIndex(['quantity', 'minimum_stock']);
            $table->dropColumn([
                'description',
                'minimum_stock',
                'location',
                'status',
            ]);
        });
    }
};
