# AGENTS.md

Agent instructions for working with prado-dev-errormessages. Supplements `CLAUDE.md`.

## Build, Lint, and Test Commands

```bash
vendor/bin/php-cs-fixer fix --dry-run          # lint tests/ (no changes)
vendor/bin/php-cs-fixer fix                    # apply style fixes
vendor/bin/phpstan analyse --memory-limit=512M # static analysis of tests/
vendor/bin/phpunit --testsuite unit            # all tests
vendor/bin/phpunit --testsuite unit --filter <ClassName or methodName>
```

### Composer shortcuts

```bash
composer fix       # php-cs-fixer fix
composer stan      # phpstan analyse --memory-limit=512M
composer test      # phpunit --testsuite unit
composer fulltest  # full pre-commit check: php -l, cs-fixer dry-run, phpstan, phpunit
```

> **Never** add `--verbose` or other extra flags to phpunit commands not listed above.

## Translating

The source of every entry is the English master of the installed framework: `vendor/pradosoft/prado/framework/Exceptions/messages/messages.txt`.

Procedure for a new or updated key:

1. Read the English entry and the code that throws it (`grep -rn "'<key>'" vendor/pradosoft/prado/framework`) to see what each placeholder holds.
2. Write the entry in every `messages/messages-<lang>.txt`, at the position of the key in the English master.
3. Keep `{n}` placeholders, class and interface names (`TPage`, `IService`, `TPageService.BasePath`), quoted literals (`'auto'`, `'.xml'`), and acronyms (PHP, SQL, HTTP, JSON, XML, CSRF) unchanged.
4. Run `composer test`. `MessageFilesTest` names each file and key that is missing, misplaced, untranslated, or loses a placeholder or identifier.

Procedure for a PRADO release sync (one batch per release):

1. `composer update pradosoft/prado` to the release candidate.
2. Run `composer test`. `MessageFilesTest` lists the keys missing from or stale in each file and any key whose placeholders changed.
3. Translate the missing keys in every file, drop the stale ones, fix the placeholders, run `composer test` until green.
4. Set the CHANGELOG heading, then tag the package together with the PRADO release.

Procedure for a new language:

1. Copy `messages/messages-de.txt` (or any complete file) to `messages/messages-<xx>.txt`, `xx` being the two-letter code `Prado::getPreferredLanguage()` returns for that language.
2. Translate every entry. Leave no English text.
3. Add a `| `xx` | Name |` row to the Languages table in `README.md`.
4. Run `composer test`.

## Documentation Standards

Language and National Variety: English, American.
Qualities: clear, thorough, easy to comprehend, brief, timeless, integrated.
Tense: present.

Banned constructions: antithesis ("does not just X, it Ys"), em-dash dramatic asides, editorializing and filler, rule-of-three rhetorical build-ups. One fact per sentence. Prefer subject–verb–object declaratives and `condition → result` tables.

## Anti-Patterns and Required Safeguards

- **Never** run `git clone/mv/restore/rm/add/commit/merge/rebase/reset/pull/push/fetch` without developer approval first.
- **Never** run `rm` on any path without developer approval first.
- **Never** remove `composer --dev` dependencies.
- **Never** erase or overwrite files during unit testing.
- **Never** add `--no-verify` to git commands.
- **Never** translate a `THttpException` key in this package; the framework owns those translations.
- **Never** add an English `messages/messages.txt` base file.
