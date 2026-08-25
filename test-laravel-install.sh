#!/usr/bin/env bash

# Don't exit on errors - we want to test all versions
# set -e

requested_version="${1:-}"

if [ -n "$requested_version" ]; then
    case "$requested_version" in
        10|11|12|13) ;;
        *)
            echo "Unsupported Laravel version: $requested_version" >&2
            echo "Supported versions: 10, 11, 12, 13" >&2
            exit 2
            ;;
    esac
fi

if [ -n "$requested_version" ]; then
    echo "🧪 Testing extractor integration with Laravel ${requested_version}"
else
    echo "🧪 Testing extractor Laravel integration across versions"
fi
echo "==========================================================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Arrays to track results
declare -a PASSED_VERSIONS
declare -a FAILED_VERSIONS
declare -a SKIPPED_VERSIONS
declare -a TESTED_DIRS

# Create wip directory if it doesn't exist
mkdir -p wip

# Function to test installation
test_laravel_version() {
    local version=$1
    local stability="${2:-stable}"
    local project_dir="wip/laravel-${version}"
    local test_failed=0

    echo -e "${YELLOW}Testing Laravel ${version}...${NC}"
    echo "----------------------------------------"

    # Remove existing directory if it exists
    if [ -d "$project_dir" ]; then
        echo "Removing existing ${project_dir}"
        rm -rf "$project_dir"
    fi

    # Create Laravel project — try stable first, fall back to dev stability
    echo "Creating Laravel ${version} project..."
    local created=0
    if [ "$stability" = "dev" ]; then
        echo -e "${BLUE}Using dev stability for Laravel ${version}${NC}"
        if composer create-project laravel/laravel="${version}.*" "$project_dir" --stability=dev --quiet --no-interaction 2>/dev/null; then
            created=1
        fi
    else
        if composer create-project laravel/laravel="${version}.*" "$project_dir" --quiet --no-interaction 2>/dev/null; then
            created=1
        fi
    fi

    if [ $created -eq 0 ]; then
        echo -e "${YELLOW}⊘ Skipping Laravel ${version} (not available)${NC}"
        SKIPPED_VERSIONS+=("$version")
        echo ""
        return 0
    fi

    TESTED_DIRS+=("$project_dir")
    cd "$project_dir" || { FAILED_VERSIONS+=("$version"); return 1; }

    # Install the package from local path
    echo "Installing extractor package..."
    composer config repositories.local '{"type": "path", "url": "../../"}' --quiet
    local install_output
    install_output=$(composer require helgesverre/extractor:@dev --with-all-dependencies --no-interaction 2>&1)
    if [ $? -ne 0 ]; then
        echo -e "${RED}✗ Failed to install package${NC}"
        # Check if failure is due to a third-party dependency not supporting this Laravel version
        if echo "$install_output" | grep -q "it conflicts with your root composer.json"; then
            echo -e "${YELLOW}  → A dependency does not yet support Laravel ${version}${NC}"
            echo "$install_output" | grep "requires laravel/framework" | head -3 | sed 's/^/  /'
            cd ../..
            SKIPPED_VERSIONS+=("$version (dependency conflict)")
            echo ""
            return 0
        fi
        echo "$install_output" | tail -5 | sed 's/^/  /'
        cd ../..
        FAILED_VERSIONS+=("$version")
        test_failed=1
    fi

    if [ $test_failed -eq 0 ]; then
        # Publish config file
        echo "Publishing config file..."
        if php artisan vendor:publish --tag="extractor-config" --no-interaction > /dev/null 2>&1; then
            echo -e "${GREEN}✓ Config published successfully${NC}"
        else
            echo -e "${RED}✗ Failed to publish config${NC}"
            cd ../..
            FAILED_VERSIONS+=("$version")
            test_failed=1
        fi
    fi

    if [ $test_failed -eq 0 ]; then
        # Verify config file exists
        if [ -f "config/extractor.php" ]; then
            echo -e "${GREEN}✓ Config file exists at config/extractor.php${NC}"
        else
            echo -e "${RED}✗ Config file not found${NC}"
            cd ../..
            FAILED_VERSIONS+=("$version")
            test_failed=1
        fi
    fi

    if [ $test_failed -eq 0 ]; then
        # Create a test script to verify service provider and facade
        cat > test_extractor.php << 'EOF'
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Test 1: Service provider registered
    $providers = $app->getLoadedProviders();
    if (!isset($providers['HelgeSverre\Extractor\ExtractorServiceProvider'])) {
        echo "FAIL: Service provider not registered\n";
        exit(1);
    }
    echo "PASS: Service provider registered\n";

    // Test 2: Extractor Facade resolves
    $extractor = \HelgeSverre\Extractor\Facades\Extractor::getFacadeRoot();
    if (!($extractor instanceof \HelgeSverre\Extractor\ExtractorManager)) {
        echo "FAIL: Extractor facade did not resolve to ExtractorManager\n";
        exit(1);
    }
    echo "PASS: Extractor facade resolves correctly\n";

    // Test 3: Text Facade resolves
    $text = \HelgeSverre\Extractor\Facades\Text::getFacadeRoot();
    if (!($text instanceof \HelgeSverre\Extractor\Text\Factory)) {
        echo "FAIL: Text facade did not resolve to Factory\n";
        exit(1);
    }
    echo "PASS: Text facade resolves correctly\n";

    // Test 4: Direct instantiation from container
    $engine = $app->make(\HelgeSverre\Extractor\Engine::class);
    if (!($engine instanceof \HelgeSverre\Extractor\Engine)) {
        echo "FAIL: Container did not resolve Engine instance\n";
        exit(1);
    }
    echo "PASS: Container resolves Engine instance\n";

    // Test 5: Config values loaded
    $config = config('extractor');
    if (!is_array($config)) {
        echo "FAIL: Config not loaded\n";
        exit(1);
    }
    echo "PASS: Config loaded\n";

    // Test 6: Text loader works
    $textContent = \HelgeSverre\Extractor\Facades\Text::text("Hello World");
    if (!($textContent instanceof \HelgeSverre\Extractor\Text\TextContent)) {
        echo "FAIL: Text loader did not return TextContent\n";
        exit(1);
    }
    echo "PASS: Text loader works\n";

    // Test 7: ImageContent can be created
    $imageContent = \HelgeSverre\Extractor\Text\ImageContent::url("https://example.com/image.jpg");
    if (!($imageContent instanceof \HelgeSverre\Extractor\Text\ImageContent)) {
        echo "FAIL: ImageContent creation failed\n";
        exit(1);
    }
    echo "PASS: ImageContent creation works\n";

    // Test 8: Engine model constants exist
    $models = [
        \HelgeSverre\Extractor\Engine::GPT_4O,
        \HelgeSverre\Extractor\Engine::GPT_4O_MINI,
        \HelgeSverre\Extractor\Engine::GPT_4_TURBO,
        \HelgeSverre\Extractor\Engine::GPT_4_1,
    ];
    echo "PASS: Engine model constants defined\n";

    // Test 9: Extractor registration works
    \HelgeSverre\Extractor\Facades\Extractor::extend('test-extractor', function() {
        return new \HelgeSverre\Extractor\Extraction\Builtins\Fields();
    });
    echo "PASS: Custom extractor registration works\n";

    echo "ALL_TESTS_PASSED\n";
    exit(0);
} catch (Exception $e) {
    echo "FAIL: Exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
EOF

        # Run the test script
        echo "Testing service provider and facades..."
        local test_output=$(php test_extractor.php 2>&1)

        if echo "$test_output" | grep -q "ALL_TESTS_PASSED"; then
            echo -e "${GREEN}✓ Service provider registered${NC}"
            echo -e "${GREEN}✓ Extractor facade resolves correctly${NC}"
            echo -e "${GREEN}✓ Text facade resolves correctly${NC}"
            echo -e "${GREEN}✓ Container binding works${NC}"
            echo -e "${GREEN}✓ Config loaded${NC}"
            echo -e "${GREEN}✓ Text loader works${NC}"
            echo -e "${GREEN}✓ ImageContent creation works${NC}"
            echo -e "${GREEN}✓ Engine constants defined${NC}"
            echo -e "${GREEN}✓ Custom extractor registration works${NC}"
        else
            echo -e "${RED}✗ Integration tests failed${NC}"
            echo -e "${BLUE}Output:${NC}"
            echo "$test_output" | sed 's/^/  /'
            cd ../..
            FAILED_VERSIONS+=("$version")
            test_failed=1
        fi

        # Clean up test file
        rm -f test_extractor.php
    fi

    # Return to project root
    cd ../..

    if [ $test_failed -eq 0 ]; then
        echo -e "${GREEN}✓ Laravel ${version} test completed successfully${NC}"
        PASSED_VERSIONS+=("$version")
    fi

    echo ""
    return $test_failed
}

# Test the requested Laravel version, or all supported versions by default
if [ -n "$requested_version" ]; then
    if [ "$requested_version" = "13" ]; then
        test_laravel_version "$requested_version" "dev"
    else
        test_laravel_version "$requested_version"
    fi
else
    test_laravel_version "10"
    test_laravel_version "11"
    test_laravel_version "12"
    test_laravel_version "13" "dev"
fi

# Print summary
echo ""
echo "==========================================================================="
echo "📊 Test Summary"
echo "==========================================================================="
echo ""

if [ ${#PASSED_VERSIONS[@]} -gt 0 ]; then
    echo -e "${GREEN}✓ Passed (${#PASSED_VERSIONS[@]})${NC}"
    for version in "${PASSED_VERSIONS[@]}"; do
        echo -e "  ${GREEN}●${NC} Laravel ${version}"
    done
    echo ""
fi

if [ ${#SKIPPED_VERSIONS[@]} -gt 0 ]; then
    echo -e "${YELLOW}⊘ Skipped (${#SKIPPED_VERSIONS[@]})${NC}"
    for version in "${SKIPPED_VERSIONS[@]}"; do
        echo -e "  ${YELLOW}●${NC} Laravel ${version} (not yet released)"
    done
    echo ""
fi

if [ ${#FAILED_VERSIONS[@]} -gt 0 ]; then
    echo -e "${RED}✗ Failed (${#FAILED_VERSIONS[@]})${NC}"
    for version in "${FAILED_VERSIONS[@]}"; do
        echo -e "  ${RED}●${NC} Laravel ${version}"
    done
    echo ""
fi

echo "Test projects are located in:"
for dir in "${TESTED_DIRS[@]}"; do
    echo "  - $dir"
done
echo ""
echo "To clean up: rm -rf wip"
echo ""

# Exit with error if any tests failed
if [ ${#FAILED_VERSIONS[@]} -gt 0 ]; then
    exit 1
else
    echo -e "${GREEN}All tests passed!${NC}"
    exit 0
fi
