<?php

declare(strict_types=1);

use PhPhD\CodingStandard\ValueObject\Set\PhdSetList;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__.'/src',__DIR__.'/tests']);
    $rectorConfig->skip([__DIR__.'/tests/*/Stub/*']);

    $rectorConfig->sets([PhdSetList::rector()->getPath()]);
    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->skip([
        ClassPropertyAssignToConstructorPromotionRector::class => [
            __DIR__.'/src/ExceptionalValidation/Model/Exception/ExceptionPackage.php',
        ],
    ]);
};
