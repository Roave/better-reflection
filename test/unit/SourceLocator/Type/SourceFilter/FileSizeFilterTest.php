<?php

declare(strict_types=1);

namespace Roave\BetterReflectionTest\SourceLocator\Type\SourceFilter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\SourceLocator\Type\SourceFilter\FileSizeFilter;

#[CoversClass(FileSizeFilter::class)]
class FileSizeFilterTest extends TestCase
{
    #[DataProvider('getKeyProvider')]
    public function testGetKey(
        int $fileSize,
        string $expected,
    ): void {

        $filter = new FileSizeFilter($fileSize);

        $this->assertSame($expected, $filter->getKey());
    }

    public static function getKeyProvider(): array
    {
        return [
            'Key 1' => [
                'fileSize' => 0,
                'expected' => 'fileSize_0',
            ],
            'Key 2' => [
                'fileSize' => 19324,
                'expected' => 'fileSize_19324',
            ],
        ];
    }

    #[DataProvider('isAllowedProvider')]
    public function testIsAllowed(
        int $maxFileSize,
        string $source,
        bool $expected,
    ): void {

        $filter = new FileSizeFilter($maxFileSize);

        $this->assertSame($expected, $filter->isAllowed($source, null));
    }

    public static function isAllowedProvider(): array
    {
        return [
            'Allowed size' => [
                'maxFileSize' => 10,
                'source' => 'qqq',
                'expected' => true,
            ],
            'Allowed size (max size)' => [
                'maxFileSize' => 4,
                'source' => 'qqqq',
                'expected' => true,
            ],
            'Not allowed size' => [
                'maxFileSize' => 4,
                'source' => 'qqqqq',
                'expected' => false,
            ],
        ];
    }
}
