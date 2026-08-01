<?php

namespace PhPhD\ExceptionalMatcher\Rule\Object\Autoload;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ConstantsClassDiscovery
{
    /** @return array<class-string,true> */
    public function getClassNames(ContainerBuilder $container): array;
}
