<?php

declare(strict_types=1);

namespace Roave\BetterReflection\Reflection\StringCast;

use Roave\BetterReflection\Reflection\ReflectionClassConstant;
use Roave\BetterReflection\Reflection\ReflectionEnumCase;
use Roave\BetterReflection\Reflection\ReflectionProperty;

use function assert;
use function preg_replace;

/** @internal */
final class ReflectionStringCastHelper
{
    /** @psalm-pure */
    public static function docCommentToString(ReflectionProperty|ReflectionClassConstant|ReflectionEnumCase $reflection, bool $indent): string
    {
        $docComment = $reflection->getDocComment();

        if ($docComment === null) {
            return '';
        }

        $indentedDocComment = $indent ? preg_replace('/(\n)(?!\n)/', '\1    ', $docComment) : $docComment;
        assert($indentedDocComment !== null);

        return $indentedDocComment . "\n";
    }
}
