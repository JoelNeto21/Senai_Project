<?php
/**
 * Helper script para reorganizar estrutura PSR-4
 * Esse script move OptimizedQueries.php para a pasta Queries
 */

$appPath = __DIR__ . '/app';
$oldFile = $appPath . '/OptimizedQueries.php';
$queriesDir = $appPath . '/Queries';
$newFile = $queriesDir . '/OptimizedQueries.php';

// Criar diretório se não existir
if (!is_dir($queriesDir)) {
    mkdir($queriesDir, 0755, true);
    echo "[✓] Diretório criado: $queriesDir\n";
}

// Mover arquivo
if (file_exists($oldFile)) {
    if (rename($oldFile, $newFile)) {
        echo "[✓] Arquivo movido: $oldFile -> $newFile\n";
    } else {
        echo "[✗] Erro ao mover arquivo\n";
        exit(1);
    }
} else {
    echo "[!] Arquivo não encontrado: $oldFile\n";
}

// Verificar se arquivo existe no novo local
if (file_exists($newFile)) {
    echo "[✓] Verificação: arquivo existe em $newFile\n";
} else {
    echo "[✗] Erro: arquivo não encontrado em $newFile\n";
    exit(1);
}

echo "\n✅ PSR-4 reorganizado com sucesso!\n";
echo "Execute: composer dump-autoload\n";
?>
