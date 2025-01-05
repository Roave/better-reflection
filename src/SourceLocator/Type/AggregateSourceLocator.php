<?php

declare(strict_types=1);

namespace Roave\BetterReflection\SourceLocator\Type;

use Generator;
use Roave\BetterReflection\Identifier\Identifier;
use Roave\BetterReflection\Identifier\IdentifierType;
use Roave\BetterReflection\Reflection\Reflection;
use Roave\BetterReflection\Reflector\Reflector;

class AggregateSourceLocator implements SourceLocator
{
    /** @param list<SourceLocator> $sourceLocators */
    public function __construct(private array $sourceLocators = [])
    {
    }

    public function locateIdentifier(Reflector $reflector, Identifier $identifier): Reflection|null
    {
        foreach ($this->sourceLocators as $sourceLocator) {
            $located = $sourceLocator->locateIdentifier($reflector, $identifier);

            if ($located) {
                return $located;
            }
        }

        return null;
    }

    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType): Generator
    {
        foreach ($this->sourceLocators as $sourceLocator) {
            yield from $sourceLocator->locateIdentifiersByType($reflector, $identifierType);
        }
    }
}
