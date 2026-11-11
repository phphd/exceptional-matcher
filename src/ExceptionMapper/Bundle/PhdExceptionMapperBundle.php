<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Bundle;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\ConstantsAutoloadingCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/** @api */
final class PhdExceptionMapperBundle extends Bundle
{
    /** @override */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new ConstantsAutoloadingCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            ConstantsAutoloadingCompilerPass::PRIORITY,
        );
    }

    /** @override */
    protected function createContainerExtension(): PhdExceptionMapperExtension
    {
        return new PhdExceptionMapperExtension(generatedProxiesAllowed: true);
    }
}
