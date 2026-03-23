<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Bundle\DependencyInjection;

use Composer\InstalledVersions;
use Exception;
use LogicException;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\Autoload\ConstantsAutoloadingCompilerPass;
use PhPhD\ExceptionToolkit\Bundle\DependencyInjection\PhdExceptionToolkitExtension;
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
use function sprintf;
use function version_compare;

/** @api */
final class PhdExceptionalMatcherExtension extends AbstractExtension implements CompilerPassInterface
{
    public const ALIAS = 'phd_exceptional_matcher';

    private readonly bool $nativeProxiesSupported;

    public function __construct(
        private readonly bool $allowGeneratedProxies = false,
    ) {
        $this->nativeProxiesSupported = self::nativeProxiesAreSupported();
    }

    /**
     * @param array<string,mixed> $parameters required by {@see \Symfony\Component\DependencyInjection\Extension\ExtensionTrait::executeConfiguratorCallback()}:
     *                                        - kernel.environment
     *                                        - kernel.build_dir
     */
    public function getContainer(array $parameters): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->setResourceTracking(false);
        $container->getCompilerPassConfig()->setBeforeOptimizationPasses([]);
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->getCompilerPassConfig()->setAfterRemovingPasses([]);

        array_map($container->setParameter(...), array_keys($parameters), $parameters); // @phpstan-ignore argument.type

        $this->configureContainer($container);

        return $container;
    }

    /** @internal PhPhD */
    public function configureContainer(ContainerBuilder $container): void
    {
        $container->registerExtension($this);
        $container->loadFromExtension($this->getAlias());

        $container->addCompilerPass($this, PassConfig::TYPE_BEFORE_OPTIMIZATION, -1000);
        $container->addCompilerPass(new ConstantsAutoloadingCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, ConstantsAutoloadingCompilerPass::PRIORITY);
        $container->addCompilerPass(new ResolveInstanceofConditionalsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);
        $container->addCompilerPass(new ResolveChildDefinitionsPass(), PassConfig::TYPE_OPTIMIZE);
        $container->addCompilerPass(new ServiceLocatorTagPass(), PassConfig::TYPE_OPTIMIZE);

        (new PhdExceptionToolkitExtension())->configureContainer($container);
    }

    /**
     * @param array<array-key,mixed> $config
     *
     * @throws Exception
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->set('phd_exceptional_matcher.lazy_proxy', $this->lazyProxy(...));
        $builder->setParameter('phd_exceptional_matcher.validator_available', interface_exists(ValidatorInterface::class));
        $builder->setParameter('phd_exceptional_matcher.messenger_available', interface_exists(MessengerMiddlewareInterface::class));

        $container->import(__DIR__.'/../../**/services.php');

        $builder->set('phd_exceptional_matcher.lazy_proxy', null);
        $builder->setParameter('phd_exceptional_matcher.validator_available', null);
        $builder->setParameter('phd_exceptional_matcher.messenger_available', null);
    }

    /** @override */
    public function getAlias(): string
    {
        return self::ALIAS;
    }

    public function process(ContainerBuilder $container): void
    {
        $this->checkTranslatorDependency($container);
        $this->failOnUnresolvedBackwardCompatibilityBreaks($container);
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

    private function checkTranslatorDependency(ContainerBuilder $container): void
    {
        if ($container->has('translator')) {
            return;
        }

        $container->removeDefinition('phd_exceptional_matcher.translator');
        $container->getParameterBag()->remove('phd_exceptional_matcher.translation_domain');
    }

    private function failOnUnresolvedBackwardCompatibilityBreaks(ContainerBuilder $container): void
    {
        if ($container->has($id = 'phd_exceptional_validation.translator')) {
            throw new LogicException(sprintf(
                'Translator service %s is not available anymore. Please use %s instead.',
                $id,
                'phd_exceptional_matcher.translator',
            ));
        }

        if ($container->hasParameter('phd_exceptional_validation.translation_domain')) {
            throw new LogicException(sprintf(
                'Parameter %s is not available anymore. Please use %s instead.',
                'phd_exceptional_validation.translation_domain',
                'phd_exceptional_matcher.translation_domain',
            ));
        }

        if ($container->has($id = 'phd_exceptional_validation.exception_unwrapper')) {
            throw new LogicException(sprintf(
                'Service %s is not available anymore. Please use %s instead.',
                $id,
                'phd_exceptional_matcher.exception_unwrapper',
            ));
        }
    }
}
