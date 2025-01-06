<?php

declare(strict_types=1);

namespace Roave\BetterReflection\SourceLocator\Type\SourceFilter;

class FileSizeFilter implements SourceFilter
{
    public function __construct(private readonly int $maxFileSize)
    {
    }

    public function getKey(): string
    {
        return "fileSize_{$this->maxFileSize}";
    }

    public function isAllowed(string $source, ?string $name, ?string $filename = null): bool
    {
        return mb_strlen($source) <= $this->maxFileSize;
    }
}
