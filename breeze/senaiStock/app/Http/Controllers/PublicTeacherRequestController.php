<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Curso;
use App\Models\TeacherRequest;
use App\Models\Turma;
use App\Services\TeacherRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicTeacherRequestController extends Controller
{
    public function create(): View
    {
        return view('teacher-requests.create', [
            'books' => Book::query()
                ->where('status', 'ativo')
                ->orderBy('subject')
                ->orderBy('title')
                ->get(),
            'courses' => Curso::query()->orderBy('nome_curso')->get(),
            'classes' => Turma::query()->orderBy('nome_turma')->get(),
        ]);
    }

    public function store(Request $request, TeacherRequestService $service): RedirectResponse
    {
        $data = $request->validate([
            'teacher_name' => ['required', 'string', 'max:255'],
            'teacher_email' => ['required', 'email', 'max:255'],
            'class_name' => ['required', 'string', 'max:255'],
            'course_name' => ['required', 'string', 'max:255'],
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $teacherRequest = $service->create($data);

        return redirect()
            ->route('teacher-requests.show', $teacherRequest->protocol)
            ->with('status', 'Solicitacao registrada com sucesso. Protocolo: ' . $teacherRequest->protocol);
    }

    public function show(string $protocol): View
    {
        $teacherRequest = TeacherRequest::with(['book', 'messages' => fn ($query) => $query->latest()])
            ->where('protocol', $protocol)
            ->firstOrFail();

        return view('teacher-requests.show', [
            'teacherRequest' => $teacherRequest,
        ]);
    }
}
