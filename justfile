# Extractor - AI-Powered Data Extraction for Laravel
# Run `just` or `just --list` to see available commands

# Load environment variables from .env if present
set dotenv-load := true

# Default recipe: show available commands
[private]
default:
    @just --list --unsorted

# ─────────────────────────────────────────────────────────────────────────────
# Setup
# ─────────────────────────────────────────────────────────────────────────────

# Install PHP dependencies
[group('setup')]
install:
    composer install

# Copy .env.example to .env
[group('setup')]
env:
    @cp -n .env.example .env || echo ".env already exists"

# ─────────────────────────────────────────────────────────────────────────────
# Testing
# ─────────────────────────────────────────────────────────────────────────────

# Run unit tests (fast, no external services)
[group('test')]
test:
    composer test

# Run all tests (unit + feature + integration)
[group('test')]
test-all:
    composer test:all

# Run tests with coverage report (uses herd if available for pcov extension)
[group('test')]
coverage *args:
    #!/usr/bin/env bash
    if command -v herd &> /dev/null; then
        herd coverage vendor/bin/pest --coverage --coverage-html=coverage {{ args }}
    else
        composer test-coverage
    fi
    open coverage/index.html 2>/dev/null || xdg-open coverage/index.html 2>/dev/null || echo "Coverage report: coverage/index.html"

# Run a specific test file
[group('test')]
test-file file:
    vendor/bin/pest "{{ file }}"

# Run tests matching a filter
[group('test')]
test-filter filter:
    vendor/bin/pest --filter "{{ filter }}"

# Run unit tests only
[group('test')]
test-unit:
    composer test

# Run only unit tests affected by local changes
[group('test')]
test-tia:
    composer test:tia

# Run feature tests only (requires OPENAI_API_KEY)
[group('test')]
test-feature:
    composer test:feature

# Run integration tests only (requires Ollama)
[group('test')]
test-integration:
    composer test:integration

# Test Laravel integration across versions 10, 11, 12, 13 (optionally specify one)
[group('test')]
test-laravel version="":
    ./test-laravel-install.sh {{ quote(version) }}

# ─────────────────────────────────────────────────────────────────────────────
# Code Quality
# ─────────────────────────────────────────────────────────────────────────────

# Format code with Laravel Pint
[group('quality')]
format:
    composer format

# Check code formatting without making changes
[group('quality')]
format-check:
    vendor/bin/pint --test

# Run static analysis with PHPStan (if configured)
[group('quality')]
analyse:
    composer analyse

# Run both formatting and analysis
[group('quality')]
lint: format

# Run all quality checks (format, test)
[group('quality')]
check: format test

# ─────────────────────────────────────────────────────────────────────────────
# Development
# ─────────────────────────────────────────────────────────────────────────────

# Build the package
[group('dev')]
build:
    composer build

# Start the development server
[group('dev')]
serve: build
    php vendor/bin/testbench serve

# Update composer dependencies
[group('dev')]
update:
    composer update

# ─────────────────────────────────────────────────────────────────────────────
# Workflows
# ─────────────────────────────────────────────────────────────────────────────

# Full development cycle: install deps, format, test
[group('workflow')]
workflow: install format test

# Quick check: format and test
[group('workflow')]
quick: format test

# Pre-commit check
[group('workflow')]
pre-commit: format test

# Simulate CI pipeline (format-check, test)
[group('workflow')]
ci: format-check test

# ─────────────────────────────────────────────────────────────────────────────
# Cleanup
# ─────────────────────────────────────────────────────────────────────────────

# Clean up generated files and directories
[group('cleanup')]
clean:
    rm -rf wip
    rm -rf .phpunit.cache
    rm -rf build
    rm -rf coverage

# Fresh setup (clean + install)
[group('cleanup')]
fresh: clean install
