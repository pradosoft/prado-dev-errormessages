# prado-dev-errormessages

Translations of the developer-facing exception messages of the [PRADO PHP Framework](https://github.com/pradosoft/prado).

PRADO 4.4 raises every framework exception with an English message. This package adds a `messages-<lang>.txt` file per language. When it is installed, a `TException` whose client prefers one of these languages carries the translated text instead.

The messages are written for the developer who reads an exception page or a log. Messages written for the site visitor, the `THttpException` texts for the 400, 403, 404, and 503 pages, stay in the framework and are translated there.

## Requirements

- PHP 8.2 or later
- PRADO 4.4 or later (`pradosoft/prado`)

## Installation

Within a PRADO application:

```bash
composer require pradosoft/prado-dev-errormessages
```

Nothing else is needed. PRADO reads the package's `extra.prado.error-messages` entry from Composer and registers the message files with `TException::addMessageFile()` while the application configures itself.

Applications upgrading from a PRADO version that bundled French, Indonesian, and Chinese messages: see the [migration guide](docs/migration.md). Release notes are in the [changelog](CHANGELOG.md).

## How it works

| Step | Behavior |
|---|---|
| Client sends `Accept-Language: de-DE,de;q=0.9` | `Prado::getPreferredLanguage()` returns `de` |
| `TException::addMessageFile('.../messages/messages.txt')` | the file resolves to `messages/messages-de.txt` when that file exists |
| `new TConfigurationException('component_property_undefined', 'TPage', 'Foo')` | the German text with `{0}` and `{1}` filled in |
| No file for the language, or no `Accept-Language` | the English text of the framework |
| Key not in the package file | the English text of the framework |

The lookup order is the framework's English master first, then the files registered by `addMessageFile()`. A key defined in the package overrides the English definition; a key the package does not define keeps its English text. `getErrorCode()` is the message key in every language.

The package has no PHP code. It contains the message files, their tests, and this documentation.

## Languages

| Code | Language | Code | Language |
|---|---|---|---|
| `ar` | Arabic | `ko` | Korean |
| `bg` | Bulgarian | `lt` | Lithuanian |
| `ca` | Catalan | `lv` | Latvian |
| `cs` | Czech | `ms` | Malay |
| `da` | Danish | `nb` | Norwegian Bokmål |
| `de` | German | `nl` | Dutch |
| `el` | Greek | `pl` | Polish |
| `es` | Spanish | `pt` | Portuguese |
| `et` | Estonian | `ro` | Romanian |
| `fa` | Persian | `ru` | Russian |
| `fi` | Finnish | `sk` | Slovak |
| `fr` | French | `sl` | Slovenian |
| `he` | Hebrew | `sr` | Serbian |
| `hi` | Hindi | `sv` | Swedish |
| `hr` | Croatian | `th` | Thai |
| `hu` | Hungarian | `tr` | Turkish |
| `id` | Indonesian | `uk` | Ukrainian |
| `it` | Italian | `vi` | Vietnamese |
| `ja` | Japanese | `zh` | Chinese (Simplified) |

A file name carries the two-letter primary language subtag only. `pt-BR` and `pt-PT` clients read `messages-pt.txt`; `zh-CN` and `zh-TW` clients read `messages-zh.txt`.

The French, Indonesian, and Chinese files build on the translations that shipped inside PRADO through 4.3. Every other file, and the untranslated remainder of those three, was machine translated from the English master and checked by the test suite for structure. Native-speaker review is welcome; open a pull request against the file.

## Message file format

```
# comment
component_property_undefined			= Die Komponenteneigenschaft '{0}.{1}' ist nicht definiert.
```

- One `key = text` entry per line. The key is the error code of the English master, `framework/Exceptions/messages/messages.txt` in `pradosoft/prado`.
- `{0}`, `{1}`, ... are replaced by the exception arguments. Each entry carries the same placeholders as its English counterpart; their order may change.
- Class, interface, property, and method names, quoted literals, and file extensions stay as in English.
- Lines starting with `#` or `;` are comments. Blank lines group the entries as the English master does.
- Files are UTF-8 without BOM, with LF line endings.
- The entries of every file are the developer-tier keys of the English master, in the master's order. The `THttpException` keys are left out.

## Tests

```bash
composer install
composer test
```

The tests lint each language file against the English master of the installed `pradosoft/prado`: key set, order, placeholders, preserved identifiers, and that every entry is translated. They then render every entry through `TException` and check that the Composer manifest path resolves to each language file. A file falls out of sync with the English master when the framework adds or removes an error code; the tests report the keys.

## Release cadence

The package version follows the PRADO release it is synced to: `master` is `4.4.x-dev` and is tagged `4.4.0` together with PRADO 4.4.0. The package follows PRADO releases, not PRADO `master`. The English master changes between releases as keys are added, removed, or reworded, so the files here are brought up to date in one batch just before each PRADO release and the two are tagged together. Between releases the package may lag `master`; the tests report the difference as missing or stale keys. A key missing from the package renders in English, so an application loses nothing in the meantime.

## Adding a language

1. Copy an existing `messages/messages-<lang>.txt` to the new two-letter code and translate every entry.
2. Add the language to the table above.
3. Run `composer test`.

## License

BSD-3-Clause. See [LICENSE](LICENSE).
