<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $canonicalCourses = [
            'Administração' => ['administracao', 'admnisitracao'],
            'Eletroeletrônica' => ['eletroeletronica'],
            'Desenvolvimento de Sistemas' => ['desenvolvimentodesistemas'],
        ];

        foreach ($canonicalCourses as $canonicalName => $aliases) {
            $courses = DB::table('cursos')
                ->orderBy('id')
                ->get(['id', 'nome_curso'])
                ->filter(fn (object $course) => in_array($this->normalize($course->nome_curso), $aliases, true))
                ->values();

            if ($courses->isNotEmpty()) {
                $primaryCourse = $courses->first();
                $duplicateIds = $courses->skip(1)->pluck('id');

                if ($duplicateIds->isNotEmpty()) {
                    DB::table('turmas')->whereIn('curso_id', $duplicateIds)->update(['curso_id' => $primaryCourse->id]);
                    DB::table('cursos')->whereIn('id', $duplicateIds)->delete();
                }

                DB::table('cursos')->where('id', $primaryCourse->id)->update(['nome_curso' => $canonicalName]);
            }

            $this->normalizeTextColumn('books', 'subject', $canonicalName, $aliases);
            $this->normalizeTextColumn('teacher_requests', 'course_name', $canonicalName, $aliases);
        }
    }

    public function down(): void
    {
        // Canonicalizing existing names and merging duplicates is intentionally irreversible.
    }

    private function normalizeTextColumn(string $table, string $column, string $canonicalName, array $aliases): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)
            ->whereNotNull($column)
            ->distinct()
            ->pluck($column)
            ->filter(fn (string $value) => in_array($this->normalize($value), $aliases, true))
            ->each(fn (string $value) => DB::table($table)->where($column, $value)->update([$column => $canonicalName]));
    }

    private function normalize(?string $value): string
    {
        return Str::of($value ?? '')
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }
};
