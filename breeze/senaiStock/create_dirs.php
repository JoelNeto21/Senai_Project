<?php
// This file creates the necessary directories for the views
$dirs = [
    'resources/views/funcionarios',
    'resources/views/cargos'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "Created directory: $dir\n";
    }
}
echo "Done!\n";
