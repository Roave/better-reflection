<?php

declare(strict_types=1);

namespace Roave\BetterReflection\Reflector;

use Generator;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionConstant;
use Roave\BetterReflection\Reflection\ReflectionFunction;
use Roave\BetterReflection\Reflector\Exception\IdentifierNotFound;

interface Reflector
{
    /**
     * Create a ReflectionClass for the specified $className.
     *
     * @throws IdentifierNotFound
     */
    public function reflectClass(string $identifierName): ReflectionClass;

    /**
     * Get all the classes available in the scope specified by the SourceLocator.
     *
     * @return Generator<ReflectionClass>
     */
    public function reflectAllClasses(): Generator;

    /**
     * Create a ReflectionFunction for the specified $functionName.
     *
     * @throws IdentifierNotFound
     */
    public function reflectFunction(string $identifierName): ReflectionFunction;

    /**
     * Get all the functions available in the scope specified by the SourceLocator.
     *
     * @return Generator<ReflectionFunction>
     */
    public function reflectAllFunctions(): Generator;

    /**
     * Create a ReflectionConstant for the specified $constantName.
     *
     * @throws IdentifierNotFound
     */
    public function reflectConstant(string $identifierName): ReflectionConstant;

    /**
     * Get all the constants available in the scope specified by the SourceLocator.
     *
     * @return Generator<ReflectionConstant>
     */
    public function reflectAllConstants(): Generator;
}
