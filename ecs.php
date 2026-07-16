<?php

declare(strict_types=1);

use PhPhD\CodingStandard\ValueObject\Set\PhdSetList;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSets([PhdSetList::ecs()->getPath()])
    ->withPaths([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/upgrade'])
    ->withSkip([
        Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer::class => [
            __DIR__.'/**/services.php',
        ],
        PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer::class => [ // Conflicts with rector's removeUnusedImports()
            __DIR__.'/src/ExceptionalMatcher/Bundle/DependencyInjection/PhdExceptionalMatcherExtension.php',
        ],
        Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer::class => [
            __DIR__.'/src/ExceptionalMatcher/Bundle/DependencyInjection/PhdExceptionalMatcherExtension.php',
        ],
    ]);
