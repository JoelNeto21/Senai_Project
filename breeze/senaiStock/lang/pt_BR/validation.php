<?php

return [
    'after_or_equal' => 'O campo :attribute deve conter uma data igual ou posterior a :date.',
    'date' => 'O campo :attribute deve conter uma data válida.',
    'exists' => 'O valor selecionado para :attribute é inválido.',
    'image' => 'O campo :attribute deve ser uma imagem.',
    'in' => 'O valor selecionado para :attribute é inválido.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'max' => [
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
        'file' => 'O arquivo enviado em :attribute não pode ser maior que :max quilobytes.',
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
    ],
    'min' => [
        'numeric' => 'O campo :attribute deve ser pelo menos :min.',
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'unique' => 'Este valor de :attribute já está em uso.',

    'attributes' => [
        'book_id' => 'livro',
        'curso_id' => 'curso',
        'description' => 'descrição',
        'due_date' => 'prazo desejado',
        'image' => 'imagem',
        'isbn' => 'ISBN',
        'minimum_stock' => 'estoque mínimo',
        'nome_curso' => 'nome do curso',
        'nome_turma' => 'nome da turma',
        'notes' => 'observações',
        'quantity' => 'quantidade',
        'status' => 'status',
        'subject' => 'curso / área',
        'title' => 'título',
        'turma_id' => 'turma',
    ],
];
