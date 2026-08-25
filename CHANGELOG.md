# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.5.1] - 2026-08-25

### Changed

- Expanded the development compatibility matrix to cover Laravel 10 through 13, including Orchestra Testbench 9.
- Updated Pest constraints and added test-impact analysis support for faster local test runs.

### Fixed

- Custom OpenAI base URI clients no longer crash with a `TypeError` when no API key is set. `Factory::withApiKey()` is not nullable, so a keyless local provider (Ollama, LM Studio) previously failed to build a client.
- Custom OpenAI base URI clients now forward `config('openai.project')`, matching the behaviour of the default `openai-php/laravel` client.

### Removed

- The `OpenAI-Beta: assistants=v2` header from the custom base URI client. The package only calls the Chat Completions API, so the header was inert; it is dropped ahead of the Assistants API removal on 2026-08-26.

## [0.5.0] - 2026-03-10

### Added

- Configurable default model via `config('extractor.model')` / `EXTRACTOR_MODEL` env var (defaults to `gpt-4o-mini`)
- System prompt support — extractions can include a system message for better JSON output quality
    - Configure globally via `config('extractor.system_prompt')` / `EXTRACTOR_SYSTEM_PROMPT` env var
    - Override per-extractor via `systemPrompt()` method
    - Disabled by default (set `EXTRACTOR_SYSTEM_PROMPT` to enable)
- New model constants for current OpenAI models:
    - GPT-5 family: `GPT_5_4`, `GPT_5_4_PRO`, `GPT_5_3`, `GPT_5_2`, `GPT_5_2_PRO`, `GPT_5_1`, `GPT_5`, `GPT_5_PRO`, `GPT_5_MINI`, `GPT_5_NANO` (with automatic parameter handling — uses `max_completion_tokens`, omits `temperature`)
    - GPT-4.1 family: `GPT_4_1`, `GPT_4_1_MINI`, `GPT_4_1_NANO`
    - O-series: `O4_MINI`, `O3`, `O3_MINI`, `O3_PRO`, `O1`, `O1_PRO` (with automatic parameter handling for reasoning models)
    - Renamed: `GPT_4O`, `GPT_4O_MINI` (cleaner naming)
- `ImageContent` `detail` parameter for OpenAI vision API (`low`, `high`, `auto`)
- Laravel 13 compatibility
- JSON `response_format` now sent with all requests for more reliable structured output
- Added `UPGRADING.md` migration guide for breaking changes
- Added comprehensive tests for HasValidation, HasDto, Contacts extractor, system prompts

### Changed

- **BREAKING**: Default model changed from `gpt-3.5-turbo-1106` to `gpt-4o-mini`
- **BREAKING**: `$model`, `$maxTokens`, and `$temperature` parameters in `ExtractorManager` methods are now nullable with config/extractor fallback chains
- **BREAKING**: Removed legacy OpenAI Completions API support — all requests now use Chat Completions API
- **BREAKING**: `Engine::extractResponseText()` now accepts only `ChatResponse` and returns `string`
- Engine simplified to single chat-only codepath with input-driven vision support
- Widened `openai-php/laravel` constraint from `^v0.10.2|^0.11` to `>=0.10.2 <1.0` for broader compatibility
- Feature tests updated to use current model constants

### Removed

- **BREAKING**: Removed deprecated/shutdown model constants: `TEXT_DAVINCI_002`, `TEXT_DAVINCI_003`, `GPT_3_TURBO_INSTRUCT`, `GPT_3_TURBO`, `GPT_3_TURBO_16K`, `GPT_3_TURBO_1106`, `GPT_4`, `GPT4_32K`, `GPT_4_1106_PREVIEW`, `GPT_4_VISION`, `GPT_O1_MINI`, `GPT_O1_PREVIEW`
- Removed `isCompletionModel()`, `isJsonModeCompatibleModel()`, `isHybridModel()`, `isOhOne()` methods from Engine
- Removed `CompletionResponse` import and handling from Engine
- Removed unused `Contracts\Engine` interface

### Deprecated

- `Engine::GPT_4_OMNI` — use `Engine::GPT_4O` instead (will be removed in v0.6.0)
- `Engine::GPT_4_OMNI_MINI` — use `Engine::GPT_4O_MINI` instead (will be removed in v0.6.0)
- `Engine::GPT_4o` alias — use `Engine::GPT_4O` instead (will be removed in v0.6.0)

## [0.4.0] - 2025-12-17

### Added

- Added `declare(strict_types=1)` to all source files for improved type safety
- Added comprehensive test suite with 127+ tests covering Engine, ExtractorManager, Extractors, and Text loaders
- Added Laravel 10/11/12 integration test script (`test-laravel-install.sh`)
- Added file size validation in Word loader (50MB limit)
- Added proper error messages for vision model input validation
- Added justfile for simplified development commands
- Added CONTRIBUTING.md with contributor guidelines
- Added pint.json for consistent code formatting configuration
- Added support for custom OpenAI API endpoints (Ollama, Together.ai, Groq, Azure)

### Changed

- Improved JSON decode error handling with `json_last_error_msg()` for better debugging
- Fixed `Factory::fromMime()` rescue fallbacks to use lazy evaluation (closures)
- Fixed loose equality operators (`==`) to strict equality (`===`) in `ImageContent`
- Improved temp file cleanup with try-finally blocks in Word loader
- Updated temp file prefixes from legacy names to `extractor_*`
- Fixed `Extractor::prompt()` to explicitly call `->render()` on View objects
- Reorganized test structure into Unit/Feature/Integration directories
- Updated CLAUDE.md with comprehensive architecture documentation

### Fixed

- Fixed placeholder exception message in `Engine.php` for vision model errors
- Fixed eager evaluation bug in `Factory::fromMime()` fallback handling

## [0.3.0] - 2025-08-09

### Added

- Added support for Google Docs exported Word files
- Added new test case for Google Docs compatibility

### Changed

- Improved Word document extraction reliability with better fallback handling
- Enhanced `Word.php` loader implementation with PHPWord integration
- Improved error handling with proper exception catching
- Better resource cleanup after document processing

## [0.2.2] - 2025-03-03

### Added

- Laravel 12 compatibility

### Changed

- Bumped dependencies for Laravel 12 support

### Contributors

- @laravel-shift

## [0.2.1] - 2024-11-20

### Fixed

- Minor bug fix release

## [0.2.0] - 2024-11-20

### Changed

- Fixed ability to use GPT-4o for vision tasks
- Deprecated the vision preview model (`gpt-4-vision-preview`) that no longer works
- Updated documentation to reference new model

### Contributors

- Thanks to @blorange2 for providing a fix

## [0.1.3] - 2024-10-30

### Changed

- Updated dependencies
- README improvements

## [0.1.2] - 2024-09-10

### Added

- Support for GPT-4o file handling

### Contributors

- @andreascreten

## [0.1.1] - 2024-05-09

### Changed

- Bumped `smalot/pdfparser` from `v2.9.0` to `^2.10`

## [0.1.0] - 2024-04-19

### Added

- Laravel 11 support

## [0.0.11] - 2024-01-23

### Fixed

- Fixed broken tests

### Changed

- Removed unused `prinsfrank/standards` dependency
- Bumped dependencies:
    - `league/flysystem-aws-s3-v3`: `^3.16` → `^3.22.0`
    - `openai-php/laravel`: `^0.7.0` → `^v0.8.1`
    - `smalot/pdfparser`: `*` → `v2.8.0`
    - `spatie/laravel-data`: `^3.9` → `^3.11.0`
    - `spatie/laravel-package-tools`: `^1.14.0` → `^1.16.2`

## [0.0.10] - 2024-01-04

### Added

- Hook to enable deletion of files after processing with Textract (`cleanupFileUsing`)
- Hook to customize file path generation (`generateFilePathUsing`)
- Added Mockery as dev dependency

## [0.0.9] - 2023-12-25

### Fixed

- Corrected default value mix-up between `textract_version` and `textract_secret` in config

## [0.0.8] - 2023-12-15

### Added

- Added `fromMime($mime, $content)` method to `Text\Factory` for automatic loader selection based on MIME type

## [0.0.7] - 2023-12-07

### Added

- Support for GPT-4-Vision API
- `ImageContent` class for handling image inputs

### Changed

- Bumped `symfony/dom-crawler` to `^7.0.0`

## [0.0.6] - 2023-12-03

### Fixed

- Fixed OpenAI package installation instructions (config publishing)

## [0.0.5] - 2023-12-03

### Changed

- Set a default model for extractions

## [0.0.4] - 2023-12-03

### Changed

- Moved images out of `.github` folder for better visibility
- Added example in README header
- Reorganized README structure

### Fixed

- Removed unused config options (defer to openai package)

## [0.0.3] - 2023-12-03

### Fixed

- Fixed autoloading configuration

## [0.0.2] - 2023-12-02

### Changed

- Specify output key via variable in prompts

## [0.0.1] - 2023-12-02

### Added

- Initial release
- OpenAI Chat and Completion endpoint wrapper
- Support for Plain Text, PDF, RTF, Images, Word documents, and Web content
- Field Extractor for arbitrary data extraction
- Integration with AWS Textract for OCR
- JSON Mode support for GPT-3.5 and GPT-4 models
- Spatie/data DTO integration
- Built-in extractors: Fields, Contacts, Receipt

[Unreleased]: https://github.com/HelgeSverre/extractor/compare/v0.5.1...HEAD
[0.5.1]: https://github.com/HelgeSverre/extractor/compare/v0.5.0...v0.5.1
[0.5.0]: https://github.com/HelgeSverre/extractor/compare/v0.4.0...v0.5.0
[0.4.0]: https://github.com/HelgeSverre/extractor/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/HelgeSverre/extractor/compare/v0.2.2...v0.3.0
[0.2.2]: https://github.com/HelgeSverre/extractor/compare/v0.2.1...v0.2.2
[0.2.1]: https://github.com/HelgeSverre/extractor/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/HelgeSverre/extractor/compare/v0.1.3...v0.2.0
[0.1.3]: https://github.com/HelgeSverre/extractor/compare/v0.1.2...v0.1.3
[0.1.2]: https://github.com/HelgeSverre/extractor/compare/v0.1.1...v0.1.2
[0.1.1]: https://github.com/HelgeSverre/extractor/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/HelgeSverre/extractor/compare/v0.0.11...v0.1.0
[0.0.11]: https://github.com/HelgeSverre/extractor/compare/v0.0.10...v0.0.11
[0.0.10]: https://github.com/HelgeSverre/extractor/compare/v0.0.9...v0.0.10
[0.0.9]: https://github.com/HelgeSverre/extractor/compare/v0.0.8...v0.0.9
[0.0.8]: https://github.com/HelgeSverre/extractor/compare/v0.0.7...v0.0.8
[0.0.7]: https://github.com/HelgeSverre/extractor/compare/v0.0.6...v0.0.7
[0.0.6]: https://github.com/HelgeSverre/extractor/compare/v0.0.5...v0.0.6
[0.0.5]: https://github.com/HelgeSverre/extractor/compare/v0.0.4...v0.0.5
[0.0.4]: https://github.com/HelgeSverre/extractor/compare/v0.0.3...v0.0.4
[0.0.3]: https://github.com/HelgeSverre/extractor/compare/v0.0.2...v0.0.3
[0.0.2]: https://github.com/HelgeSverre/extractor/compare/v0.0.1...v0.0.2
[0.0.1]: https://github.com/HelgeSverre/extractor/releases/tag/v0.0.1
