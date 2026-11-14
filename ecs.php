<?php

declare(strict_types=1);

use PhPhD\CodingStandard\ValueObject\Set\PhdSetList;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSets([PhdSetList::ecs()->getPath()])
    ->withPaths([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/upgrade'])
    ->withSkip([
        Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer::class => $shortArrayFiles = [
            __DIR__.'/**/services.php',
            // Single-line PSR-3 log context reads better than an expounded array
            __DIR__.'/src/*/*ExceptionMappingCompiler.php',
        ],
        Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer::class => $shortArrayFiles,
    ]);
