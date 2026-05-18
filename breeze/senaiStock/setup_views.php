<?php
// Create directories if they don't exist
$dirs = [
    'resources/views/funcionarios',
    'resources/views/cargos'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// List of files to move from temp
$files_to_move = [
    ['from' => 'resources/views/temp_funcionarios_index.blade.php', 'to' => 'resources/views/funcionarios/index.blade.php'],
    ['from' => 'resources/views/temp_funcionarios_create.blade.php', 'to' => 'resources/views/funcionarios/create.blade.php'],
    ['from' => 'resources/views/temp_funcionarios_edit.blade.php', 'to' => 'resources/views/funcionarios/edit.blade.php'],
    ['from' => 'resources/views/temp_cargos_index.blade.php', 'to' => 'resources/views/cargos/index.blade.php'],
];

foreach ($files_to_move as $file) {
    if (file_exists($file['from'])) {
        rename($file['from'], $file['to']);
        echo "Moved: {$file['from']} -> {$file['to']}\n";
    }
}

echo "Setup complete!\n";
