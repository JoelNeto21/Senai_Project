<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovementRequest extends FormRequest
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
            'type' => 'required|in:entrada,saida',
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
            'justification' => 'required_if:type,saida|string|max:1000',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'O tipo de movimento é obrigatório.',
            'type.in' => 'O tipo deve ser "entrada" ou "saida".',
            'book_id.required' => 'O livro é obrigatório.',
            'book_id.exists' => 'O livro selecionado não existe.',
            'quantity.required' => 'A quantidade é obrigatória.',
            'quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'quantity.min' => 'A quantidade deve ser pelo menos 1.',
            'justification.required_if' => 'A justificativa é obrigatória para saídas.',
            'justification.string' => 'A justificativa deve ser um texto.',
            'justification.max' => 'A justificativa não pode exceder 1000 caracteres.',
        ];
    }
}
