# Changelog

All notable changes to this package are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

First release. The French, Indonesian, and Chinese developer-facing exception messages move out of the PRADO framework (`framework/Exceptions/messages/messages-{fr,id,zh}.txt`) into this package, and 35 further languages are added. The baseline is the audited English master of pradosoft/prado#1372 (the branch for pradosoft/prado#1292): 635 keys, of which the 17 `THttpException` keys stay in the framework. See the [migration guide](docs/migration.md).

### Added

- `messages/messages-<lang>.txt` for 38 languages: ar, bg, ca, cs, da, de, el, es, et, fa, fi, fr, he, hi, hr, hu, id, it, ja, ko, lt, lv, ms, nb, nl, pl, pt, ro, ru, sk, sl, sr, sv, th, tr, uk, vi, zh. Each file translates every developer-tier key of the English master, 618 entries.
- `composer.json` declares `extra.prado.error-messages` (`messages/messages.txt`) for PRADO 4.4; the framework resolves the path to the client's language file.
- Tests that lint every file against the installed framework's English master and render every entry through `TException`.

### Changed

- The French, Indonesian, and Chinese files are complete. In PRADO 4.3 they covered about half of the keys, and the French and Chinese files carried English text for two thirds of their entries.
- The `THttpException` keys (`pageservice_page_unknown`, `application_unavailable`, the page-state and CSRF keys, and the service keys) are not in this package. The framework translates them in its own `messages-<lang>.txt`.
