@scoped(['tests/**'])
# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change: `vendor/bin/pest --compact --filter=testName` or a file path.
- Rerun a test after each change to it.
- Before handoff, run the project check yourself: `vendor/bin/pest --compact && bun run test && bun run typecheck`.
@endscoped
