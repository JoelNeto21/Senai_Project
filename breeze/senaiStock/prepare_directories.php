<?php
// Script para preparar o projeto

$basePath = 'c:\\laragon\\www\\Senai_Project\\breeze\\senaiStock';

// Criar diretórios necessários
$dirs = [
    'app\\Http\\Controllers\\Api',
    'app\\Http\\Resources',
];

foreach ($dirs as $dir) {
    $fullPath = $basePath . '\\' . str_replace('/', '\\', $dir);
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "Diretório criado: $fullPath\n";
    }
}

echo "Preparação concluída.\n";
?>
