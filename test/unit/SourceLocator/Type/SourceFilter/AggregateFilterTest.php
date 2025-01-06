<?php

declare(strict_types=1);

namespace Roave\BetterReflectionTest\SourceLocator\Type\SourceFilter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\SourceLocator\Type\SourceFilter\AggregateFilter;
use Roave\BetterReflection\SourceLocator\Type\SourceFilter\SourceFilter;

#[CoversClass(AggregateFilter::class)]
class AggregateFilterTest extends TestCase
{
    #[DataProvider('getKeyProvider')]
    public function testGetKey(
        array $subFiltersKeys,
        string $expected,
    ): void {

        $subFilters = [];

        foreach ($subFiltersKeys as $subFilterKey) {
            $subFilters[] = new class($subFilterKey) implements SourceFilter {

                public function __construct(
                    private readonly string $subFilterKey,
                ) {
                }

                public function getKey(): string
                {
                    return $this->subFilterKey;
                }

                public function isAllowed(string $source, ?string $name, ?string $filename = null): bool
                {
                    return true;
                }
            };
        }

        $filter = new AggregateFilter(...$subFilters);

        $this->assertSame($expected, $filter->getKey());
    }

    public static function getKeyProvider(): array
    {
        return [
            'Without sub filters' => [
                'subFiltersKeys' => [],
                'expected' => 'group_40cd750bba9870f18aada2478b24840a',
            ],
            'With sub filters case 1' => [
                'subFiltersKeys' => [
                    '111',
                ],
                'expected' => 'group_a4628f8734c9d9f1c0af618f5f2e9109',
            ],
            'With sub filters case 2' => [
                'subFiltersKeys' => [
                    '111',
                    '333',
                    'def11',
                ],
                'expected' => 'group_93519df5c4a8d75f8fc858f126563cb7',
            ],
        ];
    }

    #[DataProvider('isAllowedProvider')]
    public function testIsAllowed(
        array $results,
        bool $expected,
    ): void {

        $source = 'testSource';
        $name = 'testName';
        $fileName = 'testFileName';

        $subFilters = [];
        foreach ($results as $result) {

            $sourceFilter = $this->createMock(SourceFilter::class);
            $sourceFilter
                ->method('isAllowed')
                ->with($source, $name, $fileName)
                ->willReturn($result);

            $subFilters[] = $sourceFilter;
        }

        $filter = new AggregateFilter(...$subFilters);

        $this->assertSame($expected, $filter->isAllowed($source, $name, $fileName));
    }

    public static function isAllowedProvider(): array
    {
        return [
            'Without sub filters' => [
                'results' => [],
                'expected' => true,
            ],
            'With one false sub filter' => [
                'results' => [false],
                'expected' => false,
            ],
            'With different filters case 1' => [
                'results' => [true, false],
                'expected' => false,
            ],
            'With different filters case 2' => [
                'results' => [true, false, false],
                'expected' => false,
            ],
            'With true filter' => [
                'results' => [true],
                'expected' => true,
            ],
            'With same filters case 1' => [
                'results' => [true, true],
                'expected' => true,
            ],
            'With same filters case 2' => [
                'results' => [false, false],
                'expected' => false,
            ],
        ];
    }
}
