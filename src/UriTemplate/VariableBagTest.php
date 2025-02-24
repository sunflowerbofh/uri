<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Uri\UriTemplate;

use ArrayIterator;
use League\Uri\Exceptions\TemplateCanNotBeExpanded;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;
use function var_export;

/**
 * @coversDefaultClass \League\Uri\UriTemplate\VariableBag
 */
final class VariableBagTest extends TestCase
{
    /**
     * @covers ::assign
     * @covers ::__construct
     * @covers ::all
     * @covers ::normalizeValue
     *
     * @param array<string, string|array<string>> $expected
     *
     * @dataProvider provideValidIterable
     */
    public function testItCanBeInstantiatedWithAnIterable(iterable $iterable, array $expected): void
    {
        $bag = new VariableBag($iterable);

        self::assertEquals($expected, $bag->all());
    }

    public function provideValidIterable(): iterable
    {
        return [
            'array' => [
                'iterable' => ['name' => 'value'],
                'expected' => ['name' => 'value'],
            ],
            'iterable' => [
                'iterable' => new ArrayIterator(['name' => 'value']),
                'expected' => ['name' => 'value'],
            ],
            'empty array' =>  [
                'iterable' => [],
                'expected' => [],
            ],
        ];
    }

    /**
     * @covers ::assign
     * @covers ::normalizeValue
     * @covers ::fetch
     *
     * @param int|float|string|bool|array<string|bool|string|float> $value    the value to be assigned to the name
     * @param string|array<string>                                  $expected
     *
     * @dataProvider provideValidAssignParameters
     */
    public function testItCanAssignNameAndValuesToTheBag(string $name, $value, $expected): void
    {
        $bag = new VariableBag();
        $bag->assign($name, $value);

        self::assertSame($expected, $bag->fetch($name));
    }

    public function provideValidAssignParameters(): iterable
    {
        return [
            'string' => [
                'name' => 'foo',
                'value' => 'bar',
                'expected' => 'bar',
            ],
            'integer' => [
                'name' => 'foo',
                'value' => 12,
                'expected' => '12',
            ],
            'bool' => [
                'name' => 'foo',
                'value' => false,
                'expected' => '0',
            ],
            'list' => [
                'name' => 'foo',
                'value' => ['bar', true, 42],
                'expected' => ['bar', '1', '42'],
            ],
            'empty string' => [
                'name' => 'foo',
                'value' => '',
                'expected' => '',
            ],
        ];
    }

    /**
     * @covers ::assign
     * @covers ::normalizeValue
     * @covers ::__construct
     */
    public function testItWillFailToAssignUnsupportedType(): void
    {
        self::expectException(TypeError::class);

        new VariableBag(['name' => new stdClass()]);
    }

    /**
     * @covers ::assign
     * @covers ::normalizeValue
     * @covers ::__construct
     */
    public function testItWillFailToAssignNestedList(): void
    {
        self::expectException(TemplateCanNotBeExpanded::class);

        new VariableBag(['name' => ['foo' => ['bar' => 'baz']]]);
    }

    /**
     * @covers ::__set_state
     */
    public function testSetState(): void
    {
        $bag = new VariableBag(['foo' => 'bar', 'yolo' => 42, 'list' => [1, 2, 'three']]);

        self::assertEquals($bag, eval('return '.var_export($bag, true).';'));
    }

    public function testItCanReplaceItsValueWithThatOfAnotherInstance(): void
    {
        $bag = new VariableBag([
            'foo' => 'bar',
            'list' => [1, 2, 'three'],
        ]);

        $defaultBag = new VariableBag([
            'foo' => 'bar',
            'yolo' => 42,
            'list' => 'this is a list',
        ]);

        $expected = new VariableBag([
            'foo' => 'bar',
            'yolo' => 42,
            'list' => [1, 2, 'three'],
        ]);

        self::assertEquals($expected, $bag->replace($defaultBag));
        self::assertEquals($defaultBag, $defaultBag->replace($bag));
    }
}
