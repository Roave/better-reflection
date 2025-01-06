<?php

# example of class iterator operation

require_once __DIR__ . '/../../vendor/autoload.php';

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\SourceFilter\FileSizeFilter;
use Roave\BetterReflection\SourceLocator\Type\SourceFilter\AggregateFilter;
use Roave\BetterReflection\SourceLocator\Type\SourceFilter\SourceContainsFilter;

$directories = [__DIR__ . '/../../src', __DIR__ . '/../../demo'];

$sourceLocator = new AggregateSourceLocator([
    new DirectoriesSourceLocator(
        $directories,
        (new BetterReflection())->astLocator()
    ),
]);

$reflector = new DefaultReflector($sourceLocator);

$classReflections = $reflector->iterateClasses(
    new AggregateFilter(
        new FileSizeFilter(10000),
        new SourceContainsFilter(['class ReflectionMethod', 'class ReflectionClass'])
    ),
);

print iterator_count($classReflections);

$classReflections = $reflector->iterateClasses(new SourceContainsFilter(['class MyClass']));

print iterator_count($classReflections);
