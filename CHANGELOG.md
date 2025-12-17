# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Added `declare(strict_types=1)` to all source files for improved type safety
- Added comprehensive test suite with 127+ tests covering Engine, ExtractorManager, Extractors, and Text loaders
- Added Laravel 10/11/12 integration test script (`test-laravel-install.sh`)
- Added file size validation in Word loader (50MB limit)
- Added proper error messages for vision model input validation

### Changed
- Improved JSON decode error handling with `json_last_error_msg()` for better debugging
- Fixed `Factory::fromMime()` rescue fallbacks to use lazy evaluation (closures)
- Fixed loose equality operators (`==`) to strict equality (`===`) in `ImageContent`
- Improved temp file cleanup with try-finally blocks in Word loader
- Updated temp file prefixes from legacy names to `extractor_*`
- Fixed `Extractor::prompt()` to explicitly call `->render()` on View objects

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

[Unreleased]: https://github.com/HelgeSverre/extractor/compare/v0.3.0...HEAD
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
