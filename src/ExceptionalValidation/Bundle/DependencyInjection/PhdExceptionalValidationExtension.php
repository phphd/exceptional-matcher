<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Bundle\DependencyInjection;

use Exception;
use PhPhD\ExceptionalValidation\Formatter\Item\ExceptionViolationFormatter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface as MessengerMiddlewareInterface;

use function interface_exists;

final class PhdExceptionalValidationExtension extends Extension
{
    public const ALIAS = 'phd_exceptional_validation';

    /**
     * @param array<array-key,mixed> $configs
     *
     * @override
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var ?string $env */
        $env = $container->getParameter('kernel.environment');

        $loader = new YamlFileLoader($container, new FileLocator(), $env);

        if (interface_exists(MessengerMiddlewareInterface::class)) {
            $loader->load(__DIR__.'/../../Middleware/Messenger/services.yaml');
        }

        $loader->load(__DIR__.'/../../Handler/services.yaml');
        $loader->load(__DIR__.'/../../Rule/Assembler/services.yaml');
        $loader->load(__DIR__.'/../../Rule/Object/Property/Capture/Condition/services.yaml');
        $loader->load(__DIR__.'/../../Rule/Object/Property/Capture/Condition/Value/services.yaml');
        $loader->load(__DIR__.'/../../Formatter/services.yaml');

        $container
            ->registerForAutoconfiguration(ExceptionViolationFormatter::class)
            ->addTag('exceptional_validation.violation_formatter')
        ;
    }

    /** @override */
    public function getAlias(): string
    {
        return self::ALIAS;
    }
}
