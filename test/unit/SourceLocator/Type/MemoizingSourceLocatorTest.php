<?php

declare(strict_types=1);

namespace Roave\BetterReflectionTest\SourceLocator\Type;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Identifier\Identifier;
use Roave\BetterReflection\Identifier\IdentifierType;
use Roave\BetterReflection\Reflection\Reflection;
use Roave\BetterReflection\Reflector\Reflector;
use Roave\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\SourceLocator;

use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function count;
use function in_array;
use function iterator_count;
use function random_int;
use function range;
use function spl_object_id;
use function uniqid;

#[CoversClass(MemoizingSourceLocator::class)]
class MemoizingSourceLocatorTest extends TestCase
{
    private Reflector|MockObject $reflector1;

    private Reflector|MockObject $reflector2;

    private SourceLocator|MockObject $wrappedLocator;

    private MemoizingSourceLocator $memoizingLocator;

    /** @var list<string> */
    private array $identifierNames;

    private int $identifierCount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reflector1       = $this->createMock(Reflector::class);
        $this->reflector2       = $this->createMock(Reflector::class);
        $this->wrappedLocator   = $this->createMock(SourceLocator::class);
        $this->memoizingLocator = new MemoizingSourceLocator($this->wrappedLocator);
        $this->identifierNames  = array_unique(array_map(
            static fn (): string => uniqid('identifier', true),
            range(1, 20),
        ));
        $this->identifierCount  = count($this->identifierNames);
    }

    public function testLocateIdentifierIsMemoized(): void
    {
        $this->assertMemoization(
            array_map(
                static fn (string $identifier): Identifier => new Identifier(
                    $identifier,
                    new IdentifierType(
                        [IdentifierType::IDENTIFIER_CLASS, IdentifierType::IDENTIFIER_FUNCTION][random_int(0, 1)],
                    ),
                ),
                $this->identifierNames,
            ),
            $this->identifierCount,
            [$this->reflector1],
        );
    }

    public function testLocateIdentifiersDistinguishesBetweenIdentifierTypes(): void
    {
        $classIdentifiers    = array_map(
            static fn (string $identifier): Identifier => new Identifier($identifier, new IdentifierType(IdentifierType::IDENTIFIER_CLASS)),
            $this->identifierNames,
        );
        $functionIdentifiers = array_map(
            static fn (string $identifier): Identifier => new Identifier($identifier, new IdentifierType(IdentifierType::IDENTIFIER_FUNCTION)),
            $this->identifierNames,
        );

        $this->assertMemoization(
            array_merge($classIdentifiers, $functionIdentifiers),
            $this->identifierCount * 2,
            [$this->reflector1],
        );
    }

    public function testLocateIdentifiersDistinguishesBetweenReflectorInstances(): void
    {
        $this->assertMemoization(
            array_map(
                static fn (string $identifier): Identifier => new Identifier(
                    $identifier,
                    new IdentifierType(
                        [IdentifierType::IDENTIFIER_CLASS, IdentifierType::IDENTIFIER_FUNCTION][random_int(0, 1)],
                    ),
                ),
                $this->identifierNames,
            ),
            $this->identifierCount * 2,
            [$this->reflector1, $this->reflector2],
        );
    }

    public function testMemoizationByTypeDistinguishesBetweenSourceLocatorsAndType(): void
    {
        $types    = [
            new IdentifierType(IdentifierType::IDENTIFIER_FUNCTION),
            new IdentifierType(IdentifierType::IDENTIFIER_CLASS),
        ];
        $symbols1 = [
            IdentifierType::IDENTIFIER_FUNCTION => [$this->createMock(Reflection::class)],
            IdentifierType::IDENTIFIER_CLASS    => [$this->createMock(Reflection::class)],
        ];
        $symbols2 = [
            IdentifierType::IDENTIFIER_FUNCTION => [$this->createMock(Reflection::class)],
            IdentifierType::IDENTIFIER_CLASS    => [$this->createMock(Reflection::class)],
        ];

        $this
            ->wrappedLocator
            ->expects(self::exactly(4))
            ->method('locateIdentifiersByType')
            ->with(self::logicalOr($this->reflector1, $this->reflector2))
            ->willReturnCallback(function (
                Reflector $reflector,
                IdentifierType $identifierType,
            ) use (
                $symbols1,
                $symbols2,
            ): Generator {
                if ($reflector === $this->reflector1) {
                    yield from $symbols1[$identifierType->getName()];

                    return;
                }

                yield from $symbols2[$identifierType->getName()];
            });

        foreach ($types as $type) {
            $generator = $this->memoizingLocator->locateIdentifiersByType($this->reflector1, $type);

            $reflections1 = [];
            foreach ($generator as $reflection) {
                $reflections1[] = $reflection;
            }

            self::assertSame(
                $symbols1[$type->getName()],
                $reflections1,
            );

            $generator = $this->memoizingLocator->locateIdentifiersByType($this->reflector2, $type);

            $reflections2 = [];
            foreach ($generator as $reflection) {
                $reflections2[] = $reflection;
            }

            self::assertSame(
                $symbols2[$type->getName()],
                $reflections2,
            );

            // second execution - ensures that memoization is in place
            $generator = $this->memoizingLocator->locateIdentifiersByType($this->reflector1, $type);

            $reflections1 = [];
            foreach ($generator as $reflection) {
                $reflections1[] = $reflection;
            }

            self::assertSame(
                $symbols1[$type->getName()],
                $reflections1,
            );

            $generator = $this->memoizingLocator->locateIdentifiersByType($this->reflector2, $type);

            $reflections2 = [];
            foreach ($generator as $reflection) {
                $reflections2[] = $reflection;
            }

            self::assertSame(
                $symbols2[$type->getName()],
                $reflections2,
            );
        }
    }

    public function testNotCompletedMemoization(): void
    {
        $this
            ->wrappedLocator
            ->expects($this->exactly(2))
            ->method('locateIdentifiersByType')
            ->with($this->reflector1)
            ->willReturnCallback(function (): Generator {
                yield from [
                    $this->createMock(Reflection::class),
                    $this->createMock(Reflection::class),
                    $this->createMock(Reflection::class),
                ];
            });

        $classType = new IdentifierType(IdentifierType::IDENTIFIER_CLASS);

        $generator = $this->memoizingLocator->locateIdentifiersByType($this->reflector1, $classType);

        // Started iterating but did not complete the operation
        $generator->next();

        // The cache will not be saved until we have completed all iterations
        $generator2 = $this->memoizingLocator->locateIdentifiersByType($this->reflector1, $classType);
        $this->assertSame(3, iterator_count($generator2));

        $generator3 = $this->memoizingLocator->locateIdentifiersByType($this->reflector1, $classType);
        $this->assertSame(3, iterator_count($generator3));
    }

    /**
     * @param list<Identifier> $identifiers
     * @param list<Reflector>  $reflectors
     */
    private function assertMemoization(
        array $identifiers,
        int $expectedFetchOperationsCount,
        array $reflectors,
    ): void {
        $fetchedSymbolsCount = [];

        $this
            ->wrappedLocator
            ->expects(self::exactly($expectedFetchOperationsCount))
            ->method('locateIdentifier')
            ->with(
                self::logicalOr(...$reflectors),
                self::callback(static fn (Identifier $identifier): bool => in_array($identifier, $identifiers, true)),
            )
            ->willReturnCallback(function (
                Reflector $reflector,
                Identifier $identifier,
            ) use (
                &$fetchedSymbolsCount,
            ): Reflection|null {
                $identifierId = spl_object_id($identifier);
                $reflectorId  = spl_object_id($reflector);
                $hash         = $reflectorId . $identifierId;

                $fetchedSymbolsCount[$hash] = ($fetchedSymbolsCount[$hash] ?? 0) + 1;

                return [
                    $this->createMock(Reflection::class),
                    null,
                ][random_int(0, 1)];
            });

        $memoizedSymbols = $this->locateIdentifiers($reflectors, $identifiers);
        $cachedSymbols   = $this->locateIdentifiers($reflectors, $identifiers);

        self::assertCount($expectedFetchOperationsCount, $memoizedSymbols);

        foreach ($fetchedSymbolsCount as $fetchedSymbolCount) {
            self::assertSame(1, $fetchedSymbolCount, 'Each fetch is unique');
        }

        self::assertSame($memoizedSymbols, $cachedSymbols);

        $memoizedSymbolsIds = array_map('spl_object_id', array_filter($memoizedSymbols));
        self::assertCount(count($memoizedSymbolsIds), array_unique($memoizedSymbolsIds), 'No duplicate symbols');
    }

    /**
     * @param list<Reflector>  $reflectors
     * @param list<Identifier> $identifiers
     *
     * @return list<Reflection|null>
     */
    private function locateIdentifiers(array $reflectors, array $identifiers): array
    {
        $memoizedSymbols = [];

        foreach ($reflectors as $reflector) {
            foreach ($identifiers as $identifier) {
                $memoizedSymbols[] = $this->memoizingLocator->locateIdentifier($reflector, $identifier);
            }
        }

        return $memoizedSymbols;
    }
}
