<?php

/**
 * @author Brad Anderson <belisoful@icloud.com>
 * @link https://github.com/pradosoft/prado-dev-errormessages
 * @license https://github.com/pradosoft/prado-dev-errormessages/blob/master/LICENSE
 */

namespace Prado\Test\Unit\DevErrorMessages;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Checks the `composer.json` manifest PRADO reads to register the message files.
 */
class ComposerManifestTest extends TestCase
{
	/**
	 * @return array the decoded composer.json.
	 */
	protected static function manifest(): array
	{
		return json_decode(file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
	}

	/**
	 * @return array<string, array{string}> each language code as a data set.
	 */
	public static function languageProvider(): array
	{
		return MessageFilesTest::languageProvider();
	}

	public function testManifestIsAPradoExtension(): void
	{
		$manifest = self::manifest();
		$this->assertSame('pradosoft/prado-dev-errormessages', $manifest['name']);
		$this->assertSame('prado4-extension', $manifest['type']);
	}

	public function testManifestDeclaresTheMessageFileBaseName(): void
	{
		$this->assertSame('messages/messages.txt', self::manifest()['extra']['prado']['error-messages']);
	}

	public function testThereIsNoEnglishBaseFile(): void
	{
		$this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/messages/messages.txt', 'the English master belongs to the framework');
	}

	#[DataProvider('languageProvider')]
	public function testBaseNameResolvesToTheLanguageFile(string $lang): void
	{
		$file = dirname(__DIR__, 2) . '/' . self::manifest()['extra']['prado']['error-messages'];
		// The pattern of TException::addMessageFile().
		$this->assertSame(1, preg_match('/^(.*)(?:-(.{2,5}))?\.(.{2,4})$/', $file, $matching));
		$this->assertFileExists($matching[1] . '-' . $lang . '.' . $matching[3]);
	}
}
