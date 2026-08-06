<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Bundle\DependencyInjection;

use Composer\InstalledVersions;
use Exception;
use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\_Plan\_Compiler\ExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Autoload\ConstantsAutoloadingCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionToolkit\Bundle\DependencyInjection\PhdExceptionToolkitExtension;
use ReflectionClass;
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

    public const LOGGER_CHANNEL = 'phd_exceptional_matcher';

    private readonly bool $nativeProxiesSupported;

    public function __construct(
        /** Pass true if proxies are dumped. */
        private readonly bool $allowGeneratedProxies = false,
    ) {
        $this->nativeProxiesSupported = self::nativeProxiesAreSupported();
    }

    /**
     * @param array<string,mixed> $parameters required by {@see \Symfony\Component\DependencyInjection\Extension\ExtensionTrait::executeConfiguratorCallback()}:
     *                                        - kernel.environment
     *                                        - kernel.build_dir
     *                                        - kernel.debug: false makes broken mappings to be reported to the `logger` service instead of thrown
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
    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        $container->set('phd_exceptional_matcher.lazy_proxy', $this->lazyProxy(...));
        $container->set('phd_exceptional_matcher.hint_lazy', $this->hintLazy(...));
        $container->setParameter('phd_exceptional_matcher.validator_available', interface_exists(ValidatorInterface::class));
        $container->setParameter('phd_exceptional_matcher.messenger_available', interface_exists(MessengerMiddlewareInterface::class));

        $configurator->import(__DIR__.'/../../**/services.php');

        $container->set('phd_exceptional_matcher.lazy_proxy', null);
        $container->set('phd_exceptional_matcher.hint_lazy', null);
        $container->setParameter('phd_exceptional_matcher.validator_available', null);
        $container->setParameter('phd_exceptional_matcher.messenger_available', null);
    }

    /** @override */
    public function getAlias(): string
    {
        return self::ALIAS;
    }

    public function process(ContainerBuilder $container): void
    {
        $this->wireTranslatorDependency($container);
        $this->wireLoggerDependency($container);
        $this->failOnUnresolvedBackwardCompatibilityBreaks($container);
    }

    /** For those services, which are better to be lazy. */
    public function hintLazy(string $interface): bool|string
    {
        if (!$this->allowGeneratedProxies && !$this->nativeProxiesSupported) {
            return false;
        }

        return $this->lazyProxy($interface);
    }

    /** For those services, which cannot be built eagerly at all */
    public function lazyProxy(string $interface): bool|string
    {
        if (!$this->nativeProxiesSupported) {
            return $interface;
        }

        // Not returning interface so that Symfony uses native proxy
        return true;
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

    private function wireTranslatorDependency(ContainerBuilder $container): void
    {
        if ($container->has('translator')) {
            return;
        }

        $container->removeDefinition('phd_exceptional_matcher.translator');
        $container->getParameterBag()
            ->remove('phd_exceptional_matcher.translation_domain')
        ;
    }

    /** With nowhere to report a broken mapping to, the compiler keeps throwing it, as it does by default. */
    private function wireLoggerDependency(ContainerBuilder $container): void
    {
        if ($container->has('logger')) {
            return;
        }

        $container->getDefinition(ExceptionMappingPlanCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingPlan::class.'>')
            ->removeMethodCall('reportingTo')
        ;
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
