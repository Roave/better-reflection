<?php

declare(strict_types=1);

namespace Roave\BetterReflection\SourceLocator\Type;

use Generator;
use Iterator;
use Roave\BetterReflection\Identifier\Identifier;
use Roave\BetterReflection\Identifier\IdentifierType;
use Roave\BetterReflection\Reflection\Reflection;
use Roave\BetterReflection\Reflector\Reflector;
use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Exception\InvalidFileInfo;
use Roave\BetterReflection\SourceLocator\Exception\InvalidFileLocation;
use Roave\BetterReflection\SourceLocator\Type\SourceFilter\SourceFilter;
use SplFileInfo;

use function pathinfo;
use const PATHINFO_EXTENSION;

/**
 * This source locator loads all php files from \FileSystemIterator
 */
class FileIteratorSourceLocator implements SourceLocator
{
    private const PHP_FILE_EXTENSION = 'php';

    private AggregateSourceLocator|null $aggregateSourceLocator = null;

    /** @var Iterator<SplFileInfo> */
    private Iterator $fileSystemIterator;

    /**
     * @param Iterator<SplFileInfo> $fileInfoIterator note: only SplFileInfo allowed in this iterator
     *
     * @throws InvalidFileInfo In case of iterator not contains only SplFileInfo.
     */
    public function __construct(Iterator $fileInfoIterator, private Locator $astLocator)
    {
        foreach ($fileInfoIterator as $fileInfo) {
            /** @phpstan-ignore instanceof.alwaysTrue */
            if (! $fileInfo instanceof SplFileInfo) {
                throw InvalidFileInfo::fromNonSplFileInfo($fileInfo);
            }
        }

        $this->fileSystemIterator = $fileInfoIterator;
    }

    /** @throws InvalidFileLocation */
    private function getAggregatedSourceLocator(): AggregateSourceLocator
    {
        if ($this->aggregateSourceLocator) {
            return $this->aggregateSourceLocator;
        }

        $sourceLocators = [];
        foreach ($this->fileSystemIterator as $fileInfo) {
            $realPath = $fileInfo->getRealPath();
            if (
                $fileInfo->isFile() &&
                pathinfo($fileInfo->getRealPath(), PATHINFO_EXTENSION) === self::PHP_FILE_EXTENSION
            ) {
                $sourceLocators[] = new SingleFileSourceLocator($realPath, $this->astLocator);
            }
        }

        return $this->aggregateSourceLocator = new AggregateSourceLocator($sourceLocators);
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidFileLocation
     */
    public function locateIdentifier(Reflector $reflector, Identifier $identifier): Reflection|null
    {
        return $this->getAggregatedSourceLocator()->locateIdentifier($reflector, $identifier);
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidFileLocation
     */
    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType): array
    {
        return $this->getAggregatedSourceLocator()->locateIdentifiersByType($reflector, $identifierType);
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidFileLocation
     */
    public function iterateIdentifiersByType(
        Reflector $reflector,
        IdentifierType $identifierType,
        ?SourceFilter $sourceFilter,
    ): Generator {
        return $this->getAggregatedSourceLocator()->iterateIdentifiersByType(
            $reflector,
            $identifierType,
            $sourceFilter,
        );
    }
}
