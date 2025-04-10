<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Bundle\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\AbstractExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface as MessengerMiddlewareInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function interface_exists;

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
        $builder->setParameter('phd_exceptional_validation.validator_enabled', interface_exists(ValidatorInterface::class));
        $builder->setParameter('phd_exceptional_validation.messenger_enabled', interface_exists(MessengerMiddlewareInterface::class));

        $container->import(__DIR__.'/../../**/services.php');
    }

    /** @override */
    public function getAlias(): string
    {
        return self::ALIAS;
    }
}
