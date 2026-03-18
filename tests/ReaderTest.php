<?php

namespace Ability\ComposerReader\Tests;

use Ability\ComposerReader\Context;
use Ability\ComposerReader\Reader;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ReaderTest extends TestCase
{
    #[Test]
    #[DataProvider('validPathProvider')]
    public function create_returns_context_for_valid_path(string $path): void
    {
        $context = Reader::create($path);

        $this->assertInstanceOf(Context::class, $context);
        $this->assertSame('fixture/package', $context->get('name'));
    }

    public static function validPathProvider(): array
    {
        $dir = __DIR__ . '/fixtures/valid';

        return [
            'full path to composer.json'    => [$dir . '/composer.json'],
            'directory path'                => [$dir],
            'directory with trailing slash' => [$dir . '/'],
        ];
    }

    #[Test]
    public function create_returns_context_with_parsed_data(): void
    {
        $context = Reader::create(__DIR__ . '/fixtures/valid');

        $this->assertTrue($context->has('require.php'));
        $this->assertSame('^8.1', $context->get('require.php'));
    }

    #[Test]
    public function create_throws_invalid_argument_exception_for_missing_file(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Composer file not found');

        Reader::create('/non/existent/path');
    }

    #[Test]
    public function create_throws_runtime_exception_for_invalid_json(): void
    {
        $tmpDir = sys_get_temp_dir() . '/composer_reader_test_' . uniqid('', true);
        mkdir($tmpDir, 0755, true);
        $tmpFile = $tmpDir . '/composer.json';
        file_put_contents($tmpFile, '{ invalid json content }');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Error parsing composer file');

            Reader::create($tmpFile);
        } finally {
            unlink($tmpFile);
            rmdir($tmpDir);
        }
    }
}
