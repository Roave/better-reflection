<?php

declare(strict_types=1);

namespace Roave\BetterReflectionTest\SourceLocator\Type\SourceFilter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\SourceLocator\Type\SourceFilter\SourceContainsFilter;

#[CoversClass(SourceContainsFilter::class)]
class SourceContainsFilterTest extends TestCase
{
    #[DataProvider('getKeyProvider')]
    public function testGetKey(
        array $substrings,
        string $expected,
    ): void {

        $filter = new SourceContainsFilter($substrings);

        $this->assertSame($expected, $filter->getKey());
    }

    public static function getKeyProvider(): array
    {
        return [
            'Without substrings' => [
                'substrings' => [],
                'expected' => 'sourceContains_40cd750bba9870f18aada2478b24840a',
            ],
            'With one substring' => [
                'substrings' => ['eff'],
                'expected' => 'sourceContains_188297be5710cb65088c823bb6909954',
            ],
            'With several substring' => [
                'substrings' => ['eff', '333', 'gtre'],
                'expected' => 'sourceContains_c4f41f83316c44c3b24385808eadc245',
            ],
        ];
    }

    #[DataProvider('isAllowedProvider')]
    public function testIsAllowed(
        array $substrings,
        string $source,
        bool $expected,
    ): void {

        $filter = new SourceContainsFilter($substrings);

        $this->assertSame($expected, $filter->isAllowed($source, null));
    }

    public static function isAllowedProvider(): array
    {
        return [
            'Without substrings' => [
                'substrings' => [],
                'source' => 'qqq',
                'expected' => false,
            ],
            'With substring' => [
                'substrings' => ['qq'],
                'source' => 'qqq',
                'expected' => true,
            ],
            'With several substring. The source contains one of them' => [
                'substrings' => ['cc', 'aaa', 'bb'],
                'source' => 'qqq bb cc',
                'expected' => true,
            ],
            'With several substring. The source does not contain them' => [
                'substrings' => ['11', 'aaa', '22'],
                'source' => 'qqq bb cc',
                'expected' => false,
            ],
        ];
    }
}
