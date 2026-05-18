#!/bin/bash

# Script de Validação - Estrutura de Banco de Dados SenaiStock
# Verifica se todas as migrations, models e seeders foram criados corretamente

echo "=========================================="
echo "🔍 Validação de Estrutura - SenaiStock"
echo "=========================================="
echo ""

# Cores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para verificar arquivo
check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✅${NC} $1 encontrado"
        return 0
    else
        echo -e "${RED}❌${NC} $1 NÃO encontrado"
        return 1
    fi
}

# Função para verificar diretório
check_dir() {
    if [ -d "$1" ]; then
        echo -e "${GREEN}✅${NC} Diretório $1 encontrado"
        return 0
    else
        echo -e "${RED}❌${NC} Diretório $1 NÃO encontrado"
        return 1
    fi
}

echo "📁 Verificando Estrutura de Diretórios..."
echo ""
check_dir "database/migrations"
check_dir "app/Models"
check_dir "database/seeders"
check_dir "tests/Feature"

echo ""
echo "📄 Verificando Migrations..."
echo ""
check_file "database/migrations/2025_08_01_000001_create_books_table.php"
check_file "database/migrations/2025_08_01_000002_create_movements_table.php"

echo ""
echo "🏗️  Verificando Models..."
echo ""
check_file "app/Models/Book.php"
check_file "app/Models/Movement.php"

echo ""
echo "🌱 Verificando Seeders..."
echo ""
check_file "database/seeders/BookSeeder.php"
check_file "database/seeders/MovementSeeder.php"
check_file "database/seeders/DatabaseSeeder.php"

echo ""
echo "🧪 Verificando Testes..."
echo ""
check_file "tests/Feature/BookMovementStructureTest.php"

echo ""
echo "📋 Verificando Documentação..."
echo ""
check_file "BANCO_DADOS_ESTRUTURA.md"

echo ""
echo "=========================================="
echo "✨ Validação Concluída!"
echo "=========================================="
echo ""
echo "Próximos passos:"
echo "1. php artisan migrate"
echo "2. php artisan db:seed"
echo "3. php artisan test"
echo ""
