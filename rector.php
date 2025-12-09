<?php

declare(strict_types=1);

use PhPhD\CodingStandard\ValueObject\Set\PhdSetList;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\If_\ReduceAlwaysFalseIfOrRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/upgrade'])
    ->withSets([PhdSetList::rector()->getPath()])
    ->withPhpVersion(PhpVersion::PHP_82)
    ->withSkip([
        ...stubExclusions(),
        ArrayToFirstClassCallableRector::class => [
            __DIR__.'/src/*/services.php',
        ],
        ClassPropertyAssignToConstructorPromotionRector::class => [
            __DIR__.'/src/ExceptionalValidation/Rule/Exception/ExceptionReciprocal.php',
            __DIR__.'/src/ExceptionalValidation/Rule/LazyRuleSet.php',
        ],
        StringClassNameToClassConstantRector::class => [
            __DIR__.'/upgrade',
        ],
        ReduceAlwaysFalseIfOrRector::class => [
            __DIR__.'/upgrade',
        ]
    ]);

function stubExclusions(): array
{
    return [
        RemoveUnusedPrivatePropertyRector::class => [
            __DIR__ .'/*/Stub/*',
        ],
        RemoveUnusedPromotedPropertyRector::class => [
            __DIR__ .'/*/Stub/*',
        ],
        ReadOnlyPropertyRector::class => [
            __DIR__.'/*/Stub/*',
        ],
    ];
}
