<?php

declare(strict_types=1);

use PhPhD\CodingStandard\ValueObject\Set\PhdSetList;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\ReduceAlwaysFalseIfOrRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/upgrade'])
    ->withSkip([__DIR__.'/tests/*/Stub/*'])
    ->withSets([PhdSetList::rector()->getPath()])
    ->withPhpVersion(PhpVersion::PHP_81)
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class => [
            __DIR__.'/src/ExceptionalValidation/Rule/Exception/ExceptionPackage.php',
        ],
        StringClassNameToClassConstantRector::class => [
            __DIR__.'/upgrade',
        ],
        ReduceAlwaysFalseIfOrRector::class => [
            __DIR__.'/upgrade',
        ]
    ]);
