<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\ento\Autoload;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/** @internal */
interface ConstantsAutoloadingClassDiscovery
{
    /** @return array<class-string,true> */
    public function getClassNames(ContainerBuilder $container): array;
}
