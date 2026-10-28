<?php

/**
 * @author Brad Anderson <belisoful@icloud.com>
 * @link https://github.com/pradosoft/prado-dev-errormessages
 * @license https://github.com/pradosoft/prado-dev-errormessages/blob/master/LICENSE
 */

namespace Prado\Test\Unit\DevErrorMessages;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Prado\Exceptions\TException;

/**
 * A TException that reads one package message file in place of the framework master.
 */
class TMessageFileException extends TException
{
	/** @var string the message file to read. */
	public static string $messageFile = '';

	protected function getErrorMessageFile()
	{
		return self::$messageFile;
	}
}

/**
 * Renders every entry of every language file through {@see \Prado\Exceptions\TException},
 * and checks the `extra.prado.error-messages` path resolves to the client language.
 */
class TranslationTest extends TestCase
{
	/**
	 * @return array<string, array{string}> each language code as a data set.
	 */
	public static function languageProvider(): array
	{
		return MessageFilesTest::languageProvider();
	}

	/**
	 * @param string $text a message text.
	 * @return array<string, string> `{n}` => `arg<n>` for each placeholder of the text.
	 */
	protected static function tokens(string $text): array
	{
		$tokens = [];
		foreach (MessageFilesTest::placeholders($text) as $placeholder) {
			$tokens[$placeholder] = 'arg' . trim($placeholder, '{}');
		}
		return $tokens;
	}

	#[DataProvider('languageProvider')]
	public function testTranslatesAMessageWithPlaceholders(string $lang): void
	{
		TMessageFileException::$messageFile = MessageFilesTest::messageFile($lang);
		[$entries] = MessageFilesTest::parse(TMessageFileException::$messageFile);
		[$english] = MessageFilesTest::parse(MessageFilesTest::getEnglishMasterFile());

		$e = new TMessageFileException('component_property_undefined', 'TFoo', 'Bar');

		$this->assertSame('component_property_undefined', $e->getErrorCode());
		$this->assertSame(strtr($entries['component_property_undefined'], ['{0}' => 'TFoo', '{1}' => 'Bar']), $e->getMessage());
		$this->assertNotSame(strtr($english['component_property_undefined'], ['{0}' => 'TFoo', '{1}' => 'Bar']), $e->getMessage());
		$this->assertStringContainsString('TFoo', $e->getMessage());
		$this->assertStringContainsString('Bar', $e->getMessage());
	}

	#[DataProvider('languageProvider')]
	public function testEveryEntryRendersThroughTException(string $lang): void
	{
		TMessageFileException::$messageFile = MessageFilesTest::messageFile($lang);
		[$entries] = MessageFilesTest::parse(TMessageFileException::$messageFile);

		foreach ($entries as $key => $value) {
			$tokens = self::tokens($value);
			$e = new TMessageFileException($key, ...array_values($tokens));
			$this->assertSame(strtr($value, $tokens), $e->getMessage(), "'$key' of messages-$lang.txt renders differently through TException");
			$this->assertStringNotContainsString('{0}', $e->getMessage(), "'$key' of messages-$lang.txt keeps an unfilled placeholder");
		}
	}

	#[DataProvider('languageProvider')]
	public function testUnknownKeyIsReturnedAsIs(string $lang): void
	{
		TMessageFileException::$messageFile = MessageFilesTest::messageFile($lang);
		$e = new TMessageFileException('prado_dev_errormessages_no_such_key', 'x');
		$this->assertSame('prado_dev_errormessages_no_such_key', $e->getMessage());
	}

	#[DataProvider('languageProvider')]
	public function testUserTierKeyIsNotTranslatedByThePackage(string $lang): void
	{
		TMessageFileException::$messageFile = MessageFilesTest::messageFile($lang);
		$e = new TMessageFileException('pageservice_page_unknown', 'Home');
		$this->assertSame('pageservice_page_unknown', $e->getMessage());
	}

	/**
	 * The path in `extra.prado.error-messages`, as {@see \Prado\Exceptions\TException::addMessageFile()}
	 * receives it from the application configuration.
	 * @return string the manifest message file path.
	 */
	protected static function manifestMessageFile(): string
	{
		return dirname(__DIR__, 2) . '/messages/messages.txt';
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function testManifestPathResolvesToTheClientLanguage(): void
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.9,en;q=0.5';
		[$german] = MessageFilesTest::parse(MessageFilesTest::messageFile('de'));

		TException::addMessageFile(self::manifestMessageFile());
		$e = new TException('component_property_undefined', 'TFoo', 'Bar');

		$this->assertSame(strtr($german['component_property_undefined'], ['{0}' => 'TFoo', '{1}' => 'Bar']), $e->getMessage());
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function testEnglishClientKeepsTheFrameworkMessage(): void
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
		[$english] = MessageFilesTest::parse(MessageFilesTest::getEnglishMasterFile());

		TException::addMessageFile(self::manifestMessageFile());
		$e = new TException('component_property_undefined', 'TFoo', 'Bar');

		$this->assertSame(strtr($english['component_property_undefined'], ['{0}' => 'TFoo', '{1}' => 'Bar']), $e->getMessage());
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function testUnsupportedClientLanguageKeepsTheFrameworkMessage(): void
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'xx';
		[$english] = MessageFilesTest::parse(MessageFilesTest::getEnglishMasterFile());

		TException::addMessageFile(self::manifestMessageFile());
		$e = new TException('component_property_undefined', 'TFoo', 'Bar');

		$this->assertSame(strtr($english['component_property_undefined'], ['{0}' => 'TFoo', '{1}' => 'Bar']), $e->getMessage());
	}
}
