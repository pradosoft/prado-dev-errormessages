[prado-dev-errormessages](../README.md) -> Migration

# Migration guide

PRADO through 4.3 shipped French, Indonesian, and Chinese exception messages inside the framework and selected the file by the client's `Accept-Language` in `TException`. PRADO 4.4 raises every framework exception in English and keeps only the visitor-facing `THttpException` messages translated. The developer-facing translations are this package.

## Who needs this package

| Application | Action |
|---|---|
| Developers read exception pages and logs in English | none |
| Developers want exception messages in their language | `composer require pradosoft/prado-dev-errormessages` |
| Visitors see translated 400, 403, 404, 503 pages | none; the framework keeps those translations |

## Install the package

```sh
composer require pradosoft/prado-dev-errormessages
```

PRADO reads the package's `extra.prado` section and registers the message files. No configuration entry is needed.

## What changed in PRADO 4.4

- `TException::getErrorMessageFile()` returns the English master in every language. Before 4.4 it returned `messages-<lang>.txt` when that file existed, and a key missing from that file rendered as the raw key.
- Message files registered with `TException::addMessageFile()`, by the `errormessages` application configuration, or by a Composer extension's `extra.prado.error-messages`, override the framework's English text. Before 4.4 the framework file was read last and won.
- A `THttpException` reads the framework's `messages-<lang>.txt` after the English master. Those files now hold only the `THttpException` keys.
- `getMessage()` of a developer-facing exception is English unless this package, or an application message file, defines the key for the client's language.

## What is unchanged

- Error codes: `getErrorCode()` returns the message key in every language.
- `{0}`, `{1}`, ... placeholders and the exception argument order.
- The message file format: `key = text`, `#` and `;` comments.
- `TErrorHandler` templates: `error404-fr.html` and the other localized templates stay in the framework.

## Behavior differences after installing

| Situation | PRADO 4.3 | PRADO 4.4 with this package |
|---|---|---|
| French client, key translated in 4.3 | French | French (same text, reviewed) |
| French client, key not in the 4.3 French file | raw key, for example `component_method_undefined` | French |
| French client, key with English text in the 4.3 French file | English | French |
| German client | English | German |
| Client without `Accept-Language` | English | English |
| Log records | the client's language | the client's language |

Log records and `getMessage()` follow the client's language once the package is installed, as they did in 4.3. An application that wants English logs in production installs the package with `--dev`.

## Application message files

An application file registered by `errormessages` in the application configuration, or by `TException::addMessageFile()`, is read after this package's file. It can override any key of the package or of the framework. Its `-<lang>` variant resolves as before: `messages/app.txt` reads `messages/app-fr.txt` for a French client when that file exists.

## Verifying the move

1. Set the browser language to one of the package languages and open a page that throws, in Debug mode.
2. Confirm the exception page shows the translated message and the log shows the same text.
3. Remove the package, or request with `Accept-Language: en`, and confirm the English text.
