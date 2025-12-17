# Contributing

Thanks for your interest in contributing! We welcome contributions of all kinds and aim to make the process straightforward.

## Quick Start

```bash
# Fork and clone the repository
git clone https://github.com/YOUR-USERNAME/extractor.git
cd extractor

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Run tests
composer test
```

## Development Tools

We use modern PHP tooling to maintain code quality:

```bash
# Using justfile (recommended)
just help              # Show all commands
just test              # Run tests
just format            # Format code
just workflow          # Full dev cycle

# Using composer directly
composer test          # Run Pest tests
composer test-coverage # Run tests with coverage report
composer format        # Format code with Laravel Pint
```

## Code Standards

- **PHP 8.2+** features encouraged
- **PSR-12** coding standard (enforced by Laravel Pint)
- **Strict types** required (`declare(strict_types=1)`)
- **Tests required** for all changes

## Automated Checks

We run automated checks on every PR via GitHub Actions:

- **Tests**: Full test suite runs on PHP 8.2 and 8.3
- **Code Formatting**: Laravel Pint verifies PSR-12 compliance

All checks must pass before a PR can be merged. You can run these locally before pushing:

```bash
composer test      # Run tests
vendor/bin/pint    # Auto-fix code formatting
```

## Making Changes

1. Create a feature branch: `git checkout -b feature/my-feature`
2. Write your code
3. Add tests covering your changes
4. Ensure all tests pass: `composer test`
5. Format your code: `composer format`
6. Commit with clear messages
7. Push and open a pull request

## Using AI Tools

We encourage the use of AI development tools (Claude Code, GitHub Copilot, etc.) as part of your workflow. When using AI assistance:

- **Verify correctness**: Ensure generated code works as intended
- **Include tests**: Test coverage proves the code behaves correctly
- **Review output**: Understand what the AI generated before submitting

If the tests pass and the code is correct, we don't care how you wrote it.

## Pull Request Guidelines

- One feature or fix per PR
- Include tests demonstrating the change works
- Update documentation if needed (README, docblocks)
- Reference related issues with `Fixes #123`
- Keep commits clean and meaningful

We typically review PRs within a few days.

## Testing Philosophy

We value meaningful tests that verify behavior:

- Test the happy path and edge cases
- Make tests readable and maintainable
- Use descriptive test names that explain what's being tested
- Don't test framework code, test your logic

## What We're Looking For

Good contributions to consider:

- Bug fixes with tests proving the fix
- New extractors with documentation and tests
- New text loaders for additional file formats
- Documentation improvements
- Performance improvements with benchmarks

## Need Help?

- **Questions**: Open an issue labeled "question"
- **Bugs**: Create an issue with reproduction steps
- **Feature ideas**: Open an issue to discuss before implementing
- **Security**: Email the maintainer privately (don't open public issues)

## Adding New Extractors

When adding a new built-in extractor:

1. Create class in `src/Extraction/Builtins/`
2. Extend `Extractor` base class
3. Add Blade prompt template in `resources/prompts/`
4. Register in `ExtractorManager::$builtins` array
5. Add tests in `tests/Feature/` or `tests/Unit/Extraction/`
6. Document in README.md

## Adding New Text Loaders

When adding a new file format loader:

1. Create class in `src/Text/Loaders/`
2. Implement `TextLoader` interface
3. Add to `Factory::create()` match statement
4. Add MIME type mapping in `Factory::fromMime()` if applicable
5. Add convenience method to `Factory` class
6. Add tests in `tests/Unit/Text/`

## License

By contributing, your work becomes part of this project under the MIT License.

---

Happy coding! We appreciate your contribution to making Extractor better.
