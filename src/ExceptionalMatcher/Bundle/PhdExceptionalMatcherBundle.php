<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Bundle;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\Autoload\ConstantsAutoloadingCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/** @api */
final class PhdExceptionalMatcherBundle extends Bundle
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
    protected function createContainerExtension(): PhdExceptionalMatcherExtension
    {
        return new PhdExceptionalMatcherExtension(true);
    }
}
