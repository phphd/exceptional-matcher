<?php

declare(strict_types=1);

use PhPhD\CodingStandard\ValueObject\Set\PhdSetList;
use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__.'/']);
    $rectorConfig->skip([__DIR__.'/vendor']);
    $rectorConfig->skip([__DIR__.'/tests/*/Stub/*']);

    $rectorConfig->sets([PhdSetList::rector()->getPath()]);
    $rectorConfig->phpVersion(PhpVersion::PHP_81);
};
