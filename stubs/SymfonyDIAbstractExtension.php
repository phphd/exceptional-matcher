<?php

namespace Symfony\Component\DependencyInjection\Extension;

use Composer\InstalledVersions;
use Symfony\Component\Config\Definition\ConfigurableInterface;
use Symfony\Component\Config\Definition\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

$diVersion = InstalledVersions::getVersion('symfony/dependency-injection');

if (version_compare($diVersion, '8.0.9', '<')) {
    /**
     * This is a snapshot of an old {@see ConfigurableExtensionInterface} class with the adjusted parameter naming.
     * In the latter versions of Symfony, they have renamed $container into $configurator and $builder into $container.
     * Thus, to prevent static analysis from throwing issues on older versions, we add this code.
     *
     * @see \Symfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface
     */
    interface ConfigurableExtensionInterface extends ConfigurableInterface
    {
        public function prependExtension(ContainerConfigurator $configurator, ContainerBuilder $container): void;

        public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void;
    }

    /**
     * This is a snapshot of an old {@see AbstractExtension} class with the adjusted parameter naming.
     *
     * @see \Symfony\Component\DependencyInjection\Extension\AbstractExtension
     */
    abstract class AbstractExtension extends Extension implements ConfigurableExtensionInterface, PrependExtensionInterface
    {
        use ExtensionTrait;

        public function configure(DefinitionConfigurator $definition): void
        {
        }

        public function prependExtension(ContainerConfigurator $configurator, ContainerBuilder $container): void
        {
        }

        public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void
        {
        }

        public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
        {
            return new Configuration($this, $container, $this->getAlias());
        }

        final public function prepend(ContainerBuilder $container): void
        {
            $callback = function (ContainerConfigurator $configurator) use ($container) {
                $this->prependExtension($configurator, $container);
            };

            $this->executeConfiguratorCallback($container, $callback, $this, true);
        }

        final public function load(array $configs, ContainerBuilder $container): void
        {
            $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

            $callback = function (ContainerConfigurator $configurator) use ($config, $container) {
                $this->loadExtension($config, $configurator, $container);
            };

            $this->executeConfiguratorCallback($container, $callback, $this);
        }
    }
}
