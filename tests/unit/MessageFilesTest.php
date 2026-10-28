<?php

/**
 * @author Brad Anderson <belisoful@icloud.com>
 * @link https://github.com/pradosoft/prado-dev-errormessages
 * @license https://github.com/pradosoft/prado-dev-errormessages/blob/master/LICENSE
 */

namespace Prado\Test\Unit\DevErrorMessages;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prado\Prado;

/**
 * Lints every `messages/messages-<lang>.txt` against the English master of the
 * installed PRADO framework: same developer-tier key set in the same order, same
 * placeholders, translated text, preserved class names, and a two-letter language
 * code in the file name.
 */
class MessageFilesTest extends TestCase
{
	/**
	 * Keys the framework throws as `THttpException`. The framework translates them in its
	 * own `messages-<lang>.txt` files; this package leaves them out.
	 */
	public const USER_TIER_KEYS = [
		'application_unavailable',
		'application_service_unknown',
		'application_service_invalid',
		'application_service_unavailable',
		'pagestatepersister_pagestate_corrupted',
		'cachepagestatepersister_pagestate_corrupted',
		'sessionpagestatepersister_pagestate_corrupted',
		'page_csmanagerclass_invalid',
		'page_csrf_token_invalid',
		'juioptions_control_invalid',
		'soapservice_request_invalid',
		'jsonservice_provider_unknown',
		'feedservice_feed_unknown',
		'pageservice_page_required',
		'pageservice_security_violation',
		'pageservice_page_unknown',
		'pageservice_pageclass_unknown',
	];

	/**
	 * Keys whose translation is allowed to equal the English text, per language code.
	 * @var array<string, string[]>
	 */
	public const IDENTICAL_ALLOWED = [];

	/**
	 * @param string $lang the language code.
	 * @return string[] the keys allowed to equal the English text in that language.
	 */
	public static function identicalAllowed(string $lang): array
	{
		/** @var array<string, string[]> $allowed */
		$allowed = self::IDENTICAL_ALLOWED;
		return $allowed[$lang] ?? [];
	}

	/**
	 * @return string the directory of the package message files.
	 */
	public static function getMessagesDir(): string
	{
		return dirname(__DIR__, 2) . '/messages';
	}

	/**
	 * @return string the English master message file of the installed framework.
	 */
	public static function getEnglishMasterFile(): string
	{
		return Prado::getFrameworkPath() . '/Exceptions/messages/messages.txt';
	}

	/**
	 * @return string[] the language codes of the package message files.
	 */
	public static function getLanguages(): array
	{
		$languages = [];
		foreach (glob(self::getMessagesDir() . '/messages-*.txt') as $file) {
			$languages[] = substr(basename($file, '.txt'), strlen('messages-'));
		}
		sort($languages);
		return $languages;
	}

	/**
	 * @return array<string, array{string}> each language code as a data set.
	 */
	public static function languageProvider(): array
	{
		$sets = [];
		foreach (self::getLanguages() as $lang) {
			$sets[$lang] = [$lang];
		}
		return $sets;
	}

	/**
	 * Parses a message file the way {@see \Prado\Exceptions\TException::translateErrorMessage()}
	 * does: a trimmed line is skipped when empty or starting with `#` or `;`, and split
	 * once at the first `=`.
	 * @param string $file the message file.
	 * @return array{array<string, string>, string[]} the entries in file order, and the keys defined more than once.
	 */
	public static function parse(string $file): array
	{
		$entries = [];
		$duplicates = [];
		foreach (file($file) as $line) {
			$line = trim($line);
			if ($line === '' || strncmp($line, '#', 1) === 0 || strncmp($line, ';', 1) === 0) {
				continue;
			}
			if (strpos($line, '=') === false) {
				continue;
			}
			[$key, $value] = explode('=', $line, 2);
			$key = trim($key);
			if (isset($entries[$key])) {
				$duplicates[] = $key;
			}
			$entries[$key] = trim($value);
		}
		return [$entries, $duplicates];
	}

	/**
	 * @param string $text the message text.
	 * @return string[] the sorted distinct placeholders, `{0}`, `{1}`, ...
	 */
	public static function placeholders(string $text): array
	{
		preg_match_all('/\{\d+\}/', $text, $matches);
		$found = array_unique($matches[0]);
		sort($found);
		return $found;
	}

	/**
	 * @param string $lang the language code.
	 * @return string the package message file of the language.
	 */
	public static function messageFile(string $lang): string
	{
		return self::getMessagesDir() . '/messages-' . $lang . '.txt';
	}

	/**
	 * @return array<string, string> the developer-tier entries of the English master.
	 */
	public static function englishDeveloperEntries(): array
	{
		[$entries] = self::parse(self::getEnglishMasterFile());
		return array_diff_key($entries, array_flip(self::USER_TIER_KEYS));
	}

	public function testPackageHasLanguageFiles(): void
	{
		$this->assertNotEmpty(self::getLanguages());
	}

	public function testEnglishMasterIsInstalled(): void
	{
		$this->assertFileExists(self::getEnglishMasterFile());
	}

	public function testUserTierKeysAreInTheEnglishMaster(): void
	{
		[$entries] = self::parse(self::getEnglishMasterFile());
		foreach (self::USER_TIER_KEYS as $key) {
			$this->assertArrayHasKey($key, $entries, "user-tier key '$key' is not in the English master");
		}
	}

	#[DataProvider('languageProvider')]
	public function testFileNameIsATwoLetterLanguageCode(string $lang): void
	{
		$this->assertMatchesRegularExpression('/^[a-z]{2}$/', $lang, 'Prado::getPreferredLanguage() returns the two-letter primary subtag');
	}

	#[DataProvider('languageProvider')]
	public function testFileIsUtf8WithoutBom(string $lang): void
	{
		$content = file_get_contents(self::messageFile($lang));
		$this->assertTrue(mb_check_encoding($content, 'UTF-8'), "messages-$lang.txt is not valid UTF-8");
		$this->assertStringStartsNotWith("\xEF\xBB\xBF", $content, "messages-$lang.txt starts with a BOM");
		$this->assertStringNotContainsString("\r", $content, "messages-$lang.txt has CR line endings");
	}

	#[DataProvider('languageProvider')]
	public function testFileHasNoDuplicateKeys(string $lang): void
	{
		[, $duplicates] = self::parse(self::messageFile($lang));
		$this->assertSame([], $duplicates, "messages-$lang.txt defines keys more than once");
	}

	#[DataProvider('languageProvider')]
	public function testEveryKeyIsInTheEnglishMaster(string $lang): void
	{
		[$entries] = self::parse(self::messageFile($lang));
		[$english] = self::parse(self::getEnglishMasterFile());
		$this->assertSame([], array_keys(array_diff_key($entries, $english)), "messages-$lang.txt has keys the English master does not define");
	}

	#[DataProvider('languageProvider')]
	public function testEveryDeveloperKeyIsTranslated(string $lang): void
	{
		[$entries] = self::parse(self::messageFile($lang));
		$missing = array_keys(array_diff_key(self::englishDeveloperEntries(), $entries));
		$this->assertSame([], $missing, "messages-$lang.txt is missing developer-tier keys of the English master");
	}

	#[DataProvider('languageProvider')]
	public function testUserTierKeysAreLeftToTheFramework(string $lang): void
	{
		[$entries] = self::parse(self::messageFile($lang));
		$this->assertSame([], array_values(array_intersect(self::USER_TIER_KEYS, array_keys($entries))), "messages-$lang.txt translates THttpException keys");
	}

	#[DataProvider('languageProvider')]
	public function testKeyOrderFollowsTheEnglishMaster(string $lang): void
	{
		[$entries] = self::parse(self::messageFile($lang));
		$this->assertSame(array_keys(self::englishDeveloperEntries()), array_keys($entries), "messages-$lang.txt is not in English master order");
	}

	#[DataProvider('languageProvider')]
	public function testPlaceholdersMatchTheEnglishMaster(string $lang): void
	{
		[$entries] = self::parse(self::messageFile($lang));
		foreach (self::englishDeveloperEntries() as $key => $english) {
			$this->assertSame(self::placeholders($english), self::placeholders($entries[$key] ?? ''), "placeholders of '$key' differ in messages-$lang.txt");
		}
	}

	#[DataProvider('languageProvider')]
	public function testValuesAreTranslated(string $lang): void
	{
		[$entries] = self::parse(self::messageFile($lang));
		$allowed = self::identicalAllowed($lang);
		foreach (self::englishDeveloperEntries() as $key => $english) {
			$value = $entries[$key] ?? '';
			$this->assertNotSame('', $value, "'$key' is empty in messages-$lang.txt");
			if (!in_array($key, $allowed, true)) {
				$this->assertNotSame($english, $value, "'$key' is untranslated in messages-$lang.txt");
			}
		}
	}

	#[DataProvider('languageProvider')]
	public function testClassAndInterfaceNamesArePreserved(string $lang): void
	{
		[$entries] = self::parse(self::messageFile($lang));
		foreach (self::englishDeveloperEntries() as $key => $english) {
			preg_match_all('/\b(?:T|I)[A-Z][a-z]\w*(?:\.[A-Za-z]\w*)?/', $english, $matches);
			foreach (array_unique($matches[0]) as $identifier) {
				$this->assertStringContainsString($identifier, $entries[$key] ?? '', "'$key' in messages-$lang.txt lost the identifier '$identifier'");
			}
		}
	}

	#[DataProvider('languageProvider')]
	public function testValuesHaveNoControlCharacters(string $lang): void
	{
		[$entries] = self::parse(self::messageFile($lang));
		foreach ($entries as $key => $value) {
			$this->assertDoesNotMatchRegularExpression('/[\x00-\x1F\x7F]/', $value, "'$key' in messages-$lang.txt contains a control character");
		}
	}

	#[DataProvider('languageProvider')]
	public function testReadmeListsTheLanguage(string $lang): void
	{
		$readme = file_get_contents(dirname(__DIR__, 2) . '/README.md');
		$this->assertMatchesRegularExpression('/\|\s*`' . $lang . '`\s*\|/', $readme, "README.md does not list '$lang'");
	}
}
