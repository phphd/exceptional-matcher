<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Bundle\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\AbstractExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

final class PhdExceptionalValidationExtension extends AbstractExtension
{
    public const ALIAS = 'phd_exceptional_validation';

    /**
     * @param array<array-key,mixed> $config
     *
     * @throws Exception
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__.'/../../**/services.php');
    }

    /** @override */
    public function getAlias(): string
    {
        return self::ALIAS;
    }
}
