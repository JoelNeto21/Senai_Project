<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'isbn' => [
                'required',
                'string',
                Rule::unique('books', 'isbn')->ignore($this->route('book')),
            ],
            'subject' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título do livro é obrigatório.',
            'title.string' => 'O título deve ser um texto.',
            'isbn.required' => 'O ISBN é obrigatório.',
            'isbn.unique' => 'Este ISBN já está registrado.',
            'subject.required' => 'O assunto/disciplina é obrigatória.',
            'subject.string' => 'O assunto deve ser um texto.',
            'quantity.required' => 'A quantidade é obrigatória.',
            'quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'quantity.min' => 'A quantidade não pode ser negativa.',
        ];
    }
}
