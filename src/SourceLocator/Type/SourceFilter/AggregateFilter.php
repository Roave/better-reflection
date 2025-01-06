<?php

declare(strict_types=1);

namespace Roave\BetterReflection\SourceLocator\Type\SourceFilter;

class AggregateFilter implements SourceFilter
{
    private array $filters;

    public function __construct(SourceFilter ...$filters)
    {
        $this->filters = $filters;
    }

    public function getKey(): string
    {
        $keys = array_map(static fn(SourceFilter $filter) => $filter->getKey(), $this->filters);

        return 'group_' . md5(serialize($keys));
    }

    public function isAllowed(string $source, ?string $name, ?string $filename = null): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->isAllowed($source, $name, $filename)) {
                return false;
            }
        }
        return true;
    }
}
