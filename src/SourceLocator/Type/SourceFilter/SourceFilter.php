<?php

declare(strict_types=1);

namespace Roave\BetterReflection\SourceLocator\Type\SourceFilter;

interface SourceFilter
{
    public function getKey(): string;

    public function isAllowed(string $source, ?string $name, ?string $filename = null): bool;
}
