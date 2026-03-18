<?php

namespace Ability\ComposerReader\Tests;

use Ability\ComposerReader\Context;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ContextTest extends TestCase
{
    private Context $context;

    protected function setUp(): void
    {
        $this->context = new Context([
            'name'    => 'test/package',
            'version' => '1.0.0',
            'require' => [
                'php'            => '^8.1',
                'vendor/package' => '^1.0',
            ],
            'authors' => [
                ['name' => 'John', 'email' => 'john@example.com'],
            ],
        ]);
    }

    #[Test]
    public function get_returns_all_items_when_key_is_null(): void
    {
        $result = $this->context->get();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('require', $result);
    }

    #[Test]
    #[DataProvider('topLevelKeyProvider')]
    public function get_returns_top_level_value(string $key, string $expected): void
    {
        $this->assertSame($expected, $this->context->get($key));
    }

    public static function topLevelKeyProvider(): array
    {
        return [
            'name key'    => ['name', 'test/package'],
            'version key' => ['version', '1.0.0'],
        ];
    }

    #[Test]
    #[DataProvider('nestedDotKeyProvider')]
    public function get_returns_nested_value_with_dot_notation(string $key, string $expected): void
    {
        $this->assertSame($expected, $this->context->get($key));
    }

    public static function nestedDotKeyProvider(): array
    {
        return [
            'php version'    => ['require.php', '^8.1'],
            'vendor package' => ['require.vendor/package', '^1.0'],
        ];
    }

    #[Test]
    #[DataProvider('missingKeyProvider')]
    public function get_returns_default_for_missing_key(string $key, mixed $default, mixed $expected): void
    {
        $this->assertSame($expected, $this->context->get($key, $default));
    }

    public static function missingKeyProvider(): array
    {
        return [
            'top-level, null default'              => ['missing', null, null],
            'top-level, custom default'            => ['missing', 'default', 'default'],
            'nested, null default'                 => ['require.missing', null, null],
            'nested, custom default'               => ['require.missing', 'fallback', 'fallback'],
            'non-array intermediate, null default' => ['name.foo', null, null],
            'non-array intermediate, custom default' => ['name.foo', 'fallback', 'fallback'],
        ];
    }

    #[Test]
    public function get_returns_array_for_existing_nested_key(): void
    {
        $require = $this->context->get('require');

        $this->assertIsArray($require);
        $this->assertArrayHasKey('php', $require);
    }

    #[Test]
    #[DataProvider('existingKeyProvider')]
    public function has_returns_true_for_existing_key(string|array $key): void
    {
        $this->assertTrue($this->context->has($key));
    }

    public static function existingKeyProvider(): array
    {
        return [
            'top-level key'   => ['name'],
            'nested dot key'  => ['require.php'],
            'multiple keys'   => [['name', 'version', 'require']],
        ];
    }

    #[Test]
    #[DataProvider('missingKeyForHasProvider')]
    public function has_returns_false_for_missing_key(mixed $key): void
    {
        $this->assertFalse($this->context->has($key));
    }

    public static function missingKeyForHasProvider(): array
    {
        return [
            'top-level missing'          => ['missing'],
            'nested missing'             => ['require.missing'],
            'empty array'                => [[]],
            'partial match'              => [['name', 'missing']],
            'non-array intermediate'     => ['name.foo'],
        ];
    }

    #[Test]
    public function has_returns_false_when_items_are_empty(): void
    {
        $context = new Context([]);

        $this->assertFalse($context->has('name'));
    }

    #[Test]
    #[DataProvider('offsetExistsProvider')]
    public function offset_exists_returns_correct_value(string $key, bool $expected): void
    {
        $this->assertSame($expected, isset($this->context[$key]));
    }

    public static function offsetExistsProvider(): array
    {
        return [
            'existing key'     => ['name', true],
            'existing dot key' => ['require.php', true],
            'missing key'      => ['missing', false],
        ];
    }

    #[Test]
    #[DataProvider('offsetGetProvider')]
    public function offset_get_returns_value(string $key, mixed $expected): void
    {
        $this->assertSame($expected, $this->context[$key]);
    }

    public static function offsetGetProvider(): array
    {
        return [
            'top-level key' => ['name', 'test/package'],
            'nested dot key' => ['require.php', '^8.1'],
        ];
    }

    #[Test]
    public function offset_set_throws_runtime_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Array modification not allowed');

        $this->context['name'] = 'new/package';
    }

    #[Test]
    public function offset_unset_throws_runtime_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Array modification not allowed');

        unset($this->context['name']);
    }

    #[Test]
    public function json_serialize_returns_items_array(): void
    {
        $json = json_encode($this->context);
        $decoded = json_decode($json, true);

        $this->assertSame('test/package', $decoded['name']);
        $this->assertSame('1.0.0', $decoded['version']);
        $this->assertArrayHasKey('require', $decoded);
    }
}
