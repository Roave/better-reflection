<?php

declare(strict_types=1);

namespace Roave\BetterReflection\SourceLocator\Type\SourceFilter;

class SourceContainsFilter implements SourceFilter
{
    public function __construct(private readonly array $substrings)
    {
    }

    public function getKey(): string
    {

        return 'sourceContains_' . md5(serialize($this->substrings));
    }

    public function isAllowed(string $source, ?string $name, ?string $filename = null): bool
    {
        foreach ($this->substrings as $substring) {
            if (str_contains($source, $substring)) {
                return true;
            }
        }
        return false;
    }
}
