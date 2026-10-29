# CLAUDE.md

This file provides guidance to Agents when working with code in this repository.

## What This Is

**prado-dev-errormessages** is a PRADO 4 composer extension (`prado4-extension`) that translates the developer-facing exception messages of the PRADO PHP framework. The package has no PHP source. It holds one `messages/messages-<lang>.txt` per language, the tests that lint them against the framework's English master, and documentation. PRADO 4.4 registers the files from `extra.prado.error-messages` in `composer.json`.

## Commands

```bash
# Code style check (dry-run) and fix; the finder covers tests/
vendor/bin/php-cs-fixer fix --dry-run
vendor/bin/php-cs-fixer fix

# Static analysis of tests/
vendor/bin/phpstan analyse --memory-limit=512M

# Run the unit tests
composer test
# or:
vendor/bin/phpunit --testsuite unit

# Filter to a specific test class or method
vendor/bin/phpunit --testsuite unit --filter <ClassName or methodName>
```

### Full Check (required before git commit)

```bash
composer fulltest
```

Runs in order; all must pass:

1. PHP compile check: `find tests -name '*.php' | xargs php -l`
2. `vendor/bin/php-cs-fixer fix --dry-run`
3. `vendor/bin/phpstan analyse --memory-limit=512M`
4. `vendor/bin/phpunit --testsuite unit`

> **Never add or change phpunit command options** when unit testing. Code coverage does not apply: the package has no PHP source; the tests cover every message entry of every file.

## Architecture

```
prado-dev-errormessages/
├── composer.json               — prado4-extension; extra.prado.error-messages = messages/messages.txt
├── messages/
│   └── messages-<lang>.txt     — one file per two-letter language code; no English base file
├── docs/migration.md           — upgrading from the translations bundled in PRADO ≤ 4.3
└── tests/
    ├── test_tools/phpunit_bootstrap.php   — autoloader, Prado.php, TTestApplication from the prado vendor harness
    └── unit/
        ├── MessageFilesTest.php     — lint: key set, order, placeholders, identifiers, translated text, README listing
        ├── TranslationTest.php      — renders every entry through TException; Accept-Language resolution of the manifest path
        └── ComposerManifestTest.php — manifest fields and base-name resolution to each language file
```

### How PRADO uses the files

- `TApplicationConfiguration::captureComposerExtensions()` reads `extra.prado.error-messages` of every installed package and calls `TException::addMessageFile($path)`.
- `addMessageFile()` resolves `messages/messages.txt` to `messages/messages-<lang>.txt` for `Prado::getPreferredLanguage()` (the two-letter primary subtag of the client's `Accept-Language`) when that file exists.
- `TException::translateErrorMessage()` reads the framework's English master first, then the added files. A later file overrides an earlier definition. A key missing everywhere renders as the key.

## Important Rules

- **Keys** are the error codes of the English master, `framework/Exceptions/messages/messages.txt` in `pradosoft/prado`. Every file holds every developer-tier key, in the master's order, and none of the `THttpException` keys listed in `MessageFilesTest::USER_TIER_KEYS`. The framework translates those itself.
- **Placeholders** `{0}`, `{1}`, ... match the English entry. Position may change; the set may not.
- **Identifiers** stay as in English: class, interface, property, and method names, quoted literals, constants, file extensions.
- **No untranslated entries.** An entry equal to the English text fails `testValuesAreTranslated` unless listed in `MessageFilesTest::IDENTICAL_ALLOWED` for that language.
- **Encoding**: UTF-8 without BOM, LF line endings, no control characters in values.
- **File names** are `messages-<xx>.txt` with a two-letter lowercase code. `README.md` lists every language in its table; `testReadmeListsTheLanguage` checks it.
- **Backward compatibility**: a released key keeps its meaning. Rewording is fine; removing a key is driven by the English master.
- A new error code in the framework's English master fails `testEveryDeveloperKeyIsTranslated` for every file until each file gains the key.
- **Release cadence**: the package version follows the PRADO release it is synced to (`master` is `4.4.x-dev`, tagged `4.4.0` with PRADO 4.4.0). The package is synced to PRADO in one batch just before each PRADO tag and release and tagged with it. It is not kept in step with `master` commit by commit. In the PRADO repository, error codes are created, updated, and deleted in the English master only; nothing there edits this package. A red `master` CI between releases that reports only missing or stale keys means a sync is pending, not a defect.

## Test Bootstrap

`tests/test_tools/phpunit_bootstrap.php` loads the Composer autoloader, `Prado.php`, and boots a `TTestApplication` from the prado vendor harness (`Prado\Test\Unit\Harness`). The tests that set `Accept-Language` run in separate processes because `Prado::getPreferredLanguage()` caches its result for the process.

## Code Style

- Indentation: **tabs** (not spaces)
- Line endings: Unix (`\n`)
- PHP minimum: 8.2 (CI tests 8.2, 8.3, 8.4, 8.5)
- PSR-12 enforced via php-cs-fixer
- Docblocks: American English, present tense, direct technical statements. No antithesis ("not X, but Y"), no em-dash asides, no filler, one fact per sentence.

## Anti-Patterns (Required Safeguards)

- **Never** run `git clone/mv/restore/rm/add/commit/merge/rebase/reset/pull/push/fetch` without developer approval first.
- **Never** run `rm` on any path without developer approval first.
- **Never** remove `composer --dev` dependencies.
- **Never** erase or overwrite files during unit testing.
- **Never** add an English base file `messages/messages.txt`; with PRADO 4.4 precedence it would override the framework's English text.
