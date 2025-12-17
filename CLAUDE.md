# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Quick Reference

```bash
# Using justfile (recommended)
just help          # Show all available commands
just test          # Run tests
just format        # Format code
just workflow      # Full dev cycle: install, format, test

# Using composer directly
composer test      # Run tests
composer format    # Format code
composer serve     # Start dev server
```

## Development Commands

### Using Justfile (Preferred)

This project uses [just](https://just.systems/) as a command runner:

```bash
# Setup
just install              # Install PHP dependencies
just env                  # Copy .env.example to .env

# Testing
just test                 # Run all tests
just coverage             # Run tests with coverage (uses herd if available)
just test-unit            # Run unit tests only
just test-feature         # Run feature tests only
just test-integration     # Run integration tests only
just test-file <path>     # Run a specific test file
just test-filter <name>   # Run tests matching a filter
just test-laravel         # Test Laravel 10/11/12 compatibility

# Code Quality
just format               # Format code with Laravel Pint
just format-check         # Check formatting without changes
just analyse              # Run PHPStan (if configured)
just lint                 # Format code
just check                # Format + test

# Development
just build                # Build the package
just serve                # Start development server
just update               # Update composer dependencies

# Workflows
just workflow             # Full cycle: install, format, test
just quick                # Quick check: format, test
just pre-commit           # Pre-commit check
just ci                   # Simulate CI: format-check, test

# Cleanup
just clean                # Remove generated files
just fresh                # Clean + install
```

### Using Composer

```bash
composer test              # Run the full Pest test suite
composer test-coverage     # Run tests with code coverage
composer format            # Format code using Laravel Pint
composer lint              # Alias for format
composer analyse           # Run PHPStan (requires phpstan.neon)
composer serve             # Build and serve via Testbench
composer build             # Build the package
```

## High-Level Architecture

This is a Laravel package that provides AI-powered data extraction using OpenAI's API. The architecture follows these key patterns:

### Core Components

1. **Engine (`src/Engine.php`)**: Orchestrates all AI interactions with OpenAI. Handles different model types (completion, chat, vision, JSON mode) through a unified `run()` method. Model selection determines payload format (vision models get image URLs, JSON-mode models get response_format settings).

2. **ExtractorManager (`src/ExtractorManager.php`)**: Main entry point via the `Extractor` facade. Manages extractor registration (`extend()`), resolution, and execution. Delegates actual AI calls to the Engine.

3. **Extractor Pipeline (`src/Extraction/Extractor.php`)**: Base class for all extractors using a template method pattern:
   - `preprocess($input)` → Transform input before AI processing (via registered preprocessors)
   - `prompt($input)` → Generate AI prompt using Blade templates
   - Engine execution → Send to OpenAI API
   - `process($response)` → Transform AI response into desired format (via registered processors)

4. **Text Loading System (`src/Text/`)**: Factory pattern for loading various file formats:
   - `Factory` class provides `create(type)` and convenience methods (`pdf()`, `html()`, etc.)
   - `TextLoader` interface implemented by format-specific loaders
   - Supports PDF, Word, RTF, HTML, images, and web content
   - AWS Textract integration for OCR functionality

### Extension Points

- **Custom Extractors**: Extend `Extractor` class, override `prompt()` method, optionally use `HasValidation` or `HasDto` traits
- **Custom Text Loaders**: Implement `TextLoader` interface, register in `Factory::create()` match statement
- **Custom Prompts**: Publish and modify Blade templates in `resources/prompts/`
- **Processors/Preprocessors**: Register via `registerProcessor()` and `registerPreprocessor()` with priority ordering

### Design Patterns Used

| Pattern | Implementation | Location |
|---------|---------------|----------|
| **Strategy** | Different extraction strategies via Extractor subclasses | `src/Extraction/Builtins/` |
| **Factory** | TextLoader factory creates appropriate loaders by type/MIME | `src/Text/Factory.php` |
| **Template Method** | Extractor base class defines extraction workflow hooks | `src/Extraction/Extractor.php` |
| **Facade** | Laravel facades for convenient static access | `src/Facades/` |
| **Pipeline** | Composable processors/preprocessors with priority ordering | `src/Extraction/Extractor.php` |
| **Trait Composition** | Mixins for validation, DTO conversion, response decoding | `src/Extraction/Concerns/` |

### Key Files

```
src/
├── Engine.php              # AI model orchestration
├── ExtractorManager.php    # Main API, extractor registry
├── ExtractorServiceProvider.php  # Laravel service provider
├── Extraction/
│   ├── Extractor.php       # Base extractor class
│   ├── Concerns/           # Traits (HasDto, HasValidation, DecodesResponse)
│   └── Builtins/           # Built-in extractors (Fields, Contacts, Receipt)
├── Text/
│   ├── Factory.php         # Text loader factory
│   ├── TextContent.php     # Value object for text
│   ├── ImageContent.php    # Value object for images (extends TextContent)
│   └── Loaders/            # Format-specific loaders
└── Facades/                # Extractor and Text facades
```

## Testing Approach

- Tests use Pest PHP framework with Laravel plugin
- Real sample files are located in `tests/samples/`
- Test structure:
  - `tests/Unit/` - Unit tests (Engine, Extraction, Text loaders)
  - `tests/Feature/` - Feature tests (Receipt, Field, Vision extraction)
  - `tests/Integration/` - Integration tests (Ollama)
- Some tests require OpenAI API key (set `OPENAI_API_KEY` in `.env`)
- Long-running OCR tests and flaky vision tests are skipped by default
- Architecture tests prevent debugging functions in production code

## Configuration

The package configuration is published to `config/extractor.php` and includes:

- **AWS Textract settings**: Region, credentials, timeout, polling interval, S3 disk
- **OpenAI settings**: Custom base URI for alternative providers (Ollama, Azure, Groq)

### Environment Variables

```dotenv
# OpenAI (required)
OPENAI_API_KEY=your-key

# OpenAI alternatives (optional)
OPENAI_BASE_URI=http://localhost:11434/v1  # For Ollama

# AWS Textract (optional, for OCR)
TEXTRACT_KEY=your-aws-key
TEXTRACT_SECRET=your-aws-secret
TEXTRACT_REGION=us-east-1
TEXTRACT_DISK=textract
TEXTRACT_TIMEOUT=60
```

## Code Style

- PHP 8.2+ with `declare(strict_types=1)` in all files
- Laravel Pint for code formatting (PSR-12 based)
- Strict type comparisons (`===` not `==`)
- No debugging functions in source code (enforced by ArchTest)

## Release Management

### GitHub Releases

**Release Titles:**
- Use **version number only**: `v0.4.0`
- Do NOT add emojis or extra text to the title
- Save emojis and descriptive text for the release body/notes

**Example:**
```bash
# Correct
gh release create v0.4.0 --title "v0.4.0"

# Incorrect
gh release create v0.4.0 --title "v0.4.0 - Vision Support Improvements"
```

### Git Tags

- Use annotated tags: `git tag -a v0.4.0 -m "..."`
- Tag message can include emojis and descriptions
- Tag name should be version only: `v0.4.0`

### Versioning

- Follow [Semantic Versioning](https://semver.org/)
- MAJOR.MINOR.PATCH format (e.g., `0.4.0`)
- Always prefix with `v` in git tags and releases
- Update CHANGELOG.md for all releases

## General Preferences

### Commit Messages

- Use conventional commits format preferred
- Include emojis in commit messages is acceptable
- Keep messages clear and descriptive

### Documentation

- Keep documentation in sync with code
- Update CHANGELOG.md for all releases
- Include migration guides for breaking changes
- Update README.md when adding new features

### Adding New Extractors

When adding a new built-in extractor:

1. Create class in `src/Extraction/Builtins/`
2. Extend `Extractor` base class
3. Add Blade prompt template in `resources/prompts/`
4. Register in `ExtractorManager::$builtins` array
5. Add tests in `tests/Feature/` or `tests/Unit/Extraction/`
6. Document in README.md

### Adding New Text Loaders

When adding a new file format loader:

1. Create class in `src/Text/Loaders/`
2. Implement `TextLoader` interface
3. Add to `Factory::create()` match statement
4. Add MIME type mapping in `Factory::fromMime()` if applicable
5. Add convenience method to `Factory` class
6. Add tests in `tests/Unit/Text/`

## Security Considerations

- Web loader validates URLs to prevent SSRF attacks
- Internal/private IP addresses are blocked
- File size limits prevent resource exhaustion
- API keys should never be logged or exposed

---

_Last updated: 2025-12-17_
