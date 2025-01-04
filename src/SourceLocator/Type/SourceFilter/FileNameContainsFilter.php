<?php

declare(strict_types=1);

namespace Roave\BetterReflection\SourceLocator\Type\SourceFilter;

class FileNameContainsFilter implements SourceFilter
{
    public function __construct(private readonly array $substrings)
    {
    }

    public function getKey(): string
    {

        return 'fileNameContains_' . md5(serialize($this->substrings));
    }

    public function isAllowed(string $source, ?string $name, ?string $filename = null): bool
    {
        foreach ($this->substrings as $substring) {

            if (is_null($filename) && is_null($substring)) {
                return true;
            }

            if (is_null($filename) || is_null($substring)) {
                return false;
            }

            if (str_contains($filename, $substring)) {
                return true;
            }
        }
        return false;
    }
}
