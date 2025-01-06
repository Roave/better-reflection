<?php

declare(strict_types=1);

namespace Roave\BetterReflectionTest\SourceLocator\Type\SourceFilter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\SourceLocator\Type\SourceFilter\FileNameContainsFilter;

#[CoversClass(FileNameContainsFilter::class)]
class FileNameContainsFilterTest extends TestCase
{
    #[DataProvider('getKeyProvider')]
    public function testGetKey(
        array $substrings,
        string $expected,
    ): void {

        $filter = new FileNameContainsFilter($substrings);

        $this->assertSame($expected, $filter->getKey());
    }

    public static function getKeyProvider(): array
    {
        return [
            'Without substrings' => [
                'substrings' => [],
                'expected' => 'fileNameContains_40cd750bba9870f18aada2478b24840a',
            ],
            'With one substring' => [
                'substrings' => ['2d4f4'],
                'expected' => 'fileNameContains_c1b5c5d2b3c7d6af36c3a6469ec32678',
            ],
            'With null substring' => [
                'substrings' => [null],
                'expected' => 'fileNameContains_38017a839aaeb8ff1a658fce9af6edd3',
            ],
            'With several substring' => [
                'substrings' => ['vvvx', '64g22', 'gwqw', '0000', null],
                'expected' => 'fileNameContains_62ed0da44ff7d9fe6312fd10dfa3a4fc',
            ],
        ];
    }

    #[DataProvider('isAllowedProvider')]
    public function testIsAllowed(
        array $substrings,
        ?string $fileName,
        bool $expected,
    ): void {

        $filter = new FileNameContainsFilter($substrings);

        $this->assertSame($expected, $filter->isAllowed('', null, $fileName));
    }

    public static function isAllowedProvider(): array
    {
        return [
            'Without substrings' => [
                'substrings' => [],
                'fileName' => 'sqfw',
                'expected' => false,
            ],
            'Without substrings and null fileName' => [
                'substrings' => [],
                'fileName' => null,
                'expected' => false,
            ],
            'With null substring' => [
                'substrings' => [null],
                'fileName' => 'sqfw',
                'expected' => false,
            ],
            'With null substring and null fileName' => [
                'substrings' => [null],
                'fileName' => null,
                'expected' => true,
            ],
            'With substring' => [
                'substrings' => ['qq'],
                'fileName' => 'qqq',
                'expected' => true,
            ],
            'With several substring. The fileName contains one of them' => [
                'substrings' => ['cc', 'aaa', 'bb'],
                'fileName' => 'qqq bb cc',
                'expected' => true,
            ],
            'With several substring. The fileName does not contain them' => [
                'substrings' => ['11', 'aaa', '22'],
                'fileName' => 'qqq bb cc',
                'expected' => false,
            ],
        ];
    }
}
