<?php

declare(strict_types=1);

namespace Roave\BetterReflection\SourceLocator\Type;

use Generator;
use Iterator;
use Roave\BetterReflection\Identifier\Identifier;
use Roave\BetterReflection\Identifier\IdentifierType;
use Roave\BetterReflection\Reflection\Reflection;
use Roave\BetterReflection\Reflector\Reflector;
use Roave\BetterReflection\SourceLocator\Type\SourceFilter\SourceFilter;

use function array_key_exists;
use function spl_object_id;
use function sprintf;

final class MemoizingSourceLocator implements SourceLocator
{
    /** @var array<string, Reflection|null> indexed by reflector key and identifier cache key */
    private array $cacheByIdentifierKeyAndOid = [];

    /** @var array<string, list<Reflection>> indexed by reflector key and identifier type cache key */
    private array $cacheByIdentifierTypeKeyAndOid = [];

    /** @var array<string, list<Reflection>> indexed by reflector key, filter key and identifier type cache key */
    private array $cacheIteratorByIdentifierTypeKeyAndOid = [];

    public function __construct(private SourceLocator $wrappedSourceLocator)
    {
    }

    public function locateIdentifier(Reflector $reflector, Identifier $identifier): Reflection|null
    {
        $cacheKey = sprintf('%s_%s', $this->reflectorCacheKey($reflector), $this->identifierToCacheKey($identifier));

        if (array_key_exists($cacheKey, $this->cacheByIdentifierKeyAndOid)) {
            return $this->cacheByIdentifierKeyAndOid[$cacheKey];
        }

        return $this->cacheByIdentifierKeyAndOid[$cacheKey]
            = $this->wrappedSourceLocator->locateIdentifier($reflector, $identifier);
    }

    /** @return list<Reflection> */
    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType): array
    {
        $cacheKey = sprintf('%s_%s', $this->reflectorCacheKey($reflector), $this->identifierTypeToCacheKey($identifierType));

        if (array_key_exists($cacheKey, $this->cacheByIdentifierTypeKeyAndOid)) {
            return $this->cacheByIdentifierTypeKeyAndOid[$cacheKey];
        }

        return $this->cacheByIdentifierTypeKeyAndOid[$cacheKey]
            = $this->wrappedSourceLocator->locateIdentifiersByType($reflector, $identifierType);
    }

    /** @return Generator<Reflection> */
    public function iterateIdentifiersByType(
        Reflector $reflector,
        IdentifierType $identifierType,
        ?SourceFilter $sourceFilter,
    ): Generator {
        $cacheKey = sprintf('%s_%s_%s',
            $this->reflectorCacheKey($reflector),
            $this->identifierTypeToCacheKey($identifierType),
            $sourceFilter?->getKey() ?? ''
        );

        if (array_key_exists($cacheKey, $this->cacheIteratorByIdentifierTypeKeyAndOid)) {
            yield from $this->cacheIteratorByIdentifierTypeKeyAndOid[$cacheKey];
            return;
        }

        foreach (
            $this->wrappedSourceLocator->iterateIdentifiersByType($reflector, $identifierType, $sourceFilter) as $item
        ) {
            $this->cacheIteratorByIdentifierTypeKeyAndOid[$cacheKey][] = $item;
            yield $item;
        }
    }

    private function reflectorCacheKey(Reflector $reflector): string
    {
        return sprintf('type:%s#oid:%d', $reflector::class, spl_object_id($reflector));
    }

    private function identifierToCacheKey(Identifier $identifier): string
    {
        return sprintf(
            '%s#name:%s',
            $this->identifierTypeToCacheKey($identifier->getType()),
            $identifier->getName(),
        );
    }

    private function identifierTypeToCacheKey(IdentifierType $identifierType): string
    {
        return sprintf('type:%s', $identifierType->getName());
    }
}
