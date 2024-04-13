<?php

declare(strict_types=1);

use PhPhD\CodingStandard\ValueObject\Set\PhdSetList;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([PhdSetList::ecs()->getPath()]);

    $ecsConfig->paths([__DIR__.'/src', __DIR__.'/tests']);
    $ecsConfig->skip([__DIR__.'/vendor']);
};
