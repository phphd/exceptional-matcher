<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Bundle\DependencyInjection;

use Composer\InstalledVersions;
use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\AbstractExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface as MessengerMiddlewareInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_keys;
use function array_map;
use function interface_exists;
use function version_compare;

/** @api */
final class PhdExceptionalValidationExtension extends AbstractExtension implements CompilerPassInterface
{
    public const ALIAS = 'phd_exceptional_validation';

    private readonly bool $nativeProxiesSupported;

    public function __construct(
        private readonly bool $allowGeneratedProxies = true,
    ) {
        $this->nativeProxiesSupported = self::nativeProxiesAreSupported();
    }

    /**
     * @param array<string,mixed> $parameters required by {@see \Symfony\Component\DependencyInjection\Extension\ExtensionTrait::executeConfiguratorCallback()}:
     *                                        - kernel.environment
     *                                        - kernel.build_dir
     */
    public static function getContainer(array $parameters, bool $allowGeneratedProxies = false): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->setResourceTracking(false);
        $container->getCompilerPassConfig()->setBeforeOptimizationPasses([]);
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->getCompilerPassConfig()->setAfterRemovingPasses([]);

        $container->registerExtension($extension = new self($allowGeneratedProxies));
        $container->loadFromExtension($extension->getAlias());

        $container->addCompilerPass($extension, PassConfig::TYPE_BEFORE_OPTIMIZATION, -1000);
        $container->addCompilerPass(new ResolveInstanceofConditionalsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);
        $container->addCompilerPass(new ResolveChildDefinitionsPass(), PassConfig::TYPE_OPTIMIZE);
        $container->addCompilerPass(new ServiceLocatorTagPass(), PassConfig::TYPE_OPTIMIZE);

        array_map($container->setParameter(...), array_keys($parameters), $parameters); // @phpstan-ignore argument.type

        return $container;
    }

    /**
     * @param array<array-key,mixed> $config
     *
     * @throws Exception
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->set('phd_exceptional_validation.lazy_proxy', $this->lazyProxy(...));
        $builder->setParameter('phd_exceptional_validation.validator_enabled', interface_exists(ValidatorInterface::class));
        $builder->setParameter('phd_exceptional_validation.messenger_enabled', interface_exists(MessengerMiddlewareInterface::class));

        $container->import(__DIR__.'/../../**/services.php');

        $builder->set('phd_exceptional_validation.lazy_proxy', null);
        $builder->setParameter('phd_exceptional_validation.validator_enabled', null);
        $builder->setParameter('phd_exceptional_validation.messenger_enabled', null);
    }

    /** @override */
    public function getAlias(): string
    {
        return self::ALIAS;
    }

    public function process(ContainerBuilder $container): void
    {
        if ($container->has('translator')) {
            return;
        }

        $container->removeDefinition('phd_exceptional_validation.translator');
    }

    public function lazyProxy(string $interface): bool|string
    {
        if ($this->nativeProxiesSupported) {
            // this will make sure that sf uses native proxy if available

            return true;
        }

        if (!$this->allowGeneratedProxies) {
            return false;
        }

        return $interface;
    }

    /** @internal */
    public static function nativeProxiesAreSupported(): bool
    {
        return \PHP_VERSION_ID >= 80400
            && version_compare(
                InstalledVersions::getVersion('symfony/dependency-injection') ?? '0',
                '7.3',
                '>=',
            );
    }
}
