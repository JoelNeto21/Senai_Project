#!/bin/bash

# SenaiStock - Test Runner Script
# Run this from project root: ./run-tests.sh

echo "================================"
echo "SenaiStock API Test Runner"
echo "================================"
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if artisan exists
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: artisan not found. Run from project root.${NC}"
    exit 1
fi

# Parse arguments
TEST_FILTER=${1:-""}
COVERAGE=${2:-false}

if [ "$COVERAGE" == "--coverage" ]; then
    echo -e "${YELLOW}Running tests WITH coverage report...${NC}"
    php artisan test $TEST_FILTER --coverage
else
    echo -e "${YELLOW}Running tests...${NC}"
    php artisan test $TEST_FILTER --verbose
fi

echo ""
echo "================================"
echo "Test run completed!"
echo "================================"
