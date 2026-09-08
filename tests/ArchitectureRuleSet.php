<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests;

use Composer\InstalledVersions;
use PHPat\Selector\Modifier\AnyOfSelectorModifier;
use PHPat\Selector\Selector;
use PHPat\Selector\SelectorInterface;
use PHPat\Test\Attributes\TestRule;
use PHPat\Test\Builder\BuildStep;
use PHPat\Test\PHPat;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\CompilingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\CatchExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\ReusableIteratorAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\ValueException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Matcher\ExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsAutoloadingClassDiscovery;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsClassLoader;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Path\PropertyPath;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompiler;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @api
 */
final class ArchitectureRuleSet
{
    #[TestRule]
    public function testPlanCompilerDependencies(): BuildStep
    {
        return $this->layerRule('planCompiler');
    }

    #[TestRule]
    public function testPlanDependencies(): BuildStep
    {
        return $this->layerRule('plan');
    }

    #[TestRule]
    public function testNodeDependencies(): BuildStep
    {
        return $this->layerRule('node');
    }

    #[TestRule]
    public function testMatchConditionDependencies(): BuildStep
    {
        return $this->layerRule('matchCondition');
    }

    #[TestRule]
    public function testMatchConditionCompilerDependencies(): BuildStep
    {
        return $this->layerRule('matchConditionCompiler');
    }

    #[TestRule]
    public function testExceptionDependencies(): BuildStep
    {
        return $this->layerRule('exception');
    }

    #[TestRule]
    public function testLinterDependencies(): BuildStep
    {
        return $this->layerRule('linter');
    }

    #[TestRule]
    public function testMatcherDependencies(): BuildStep
    {
        return $this->layerRule('matcher');
    }

    #[TestRule]
    public function testValidatorMatcherDependencies(): BuildStep
    {
        return $this->layerRule('validatorMatcher');
    }

    #[TestRule]
    public function testValidatorMiddlewareDependencies(): BuildStep
    {
        return $this->layerRule('validatorMiddleware');
    }

    #[TestRule]
    public function testMessengerValidatorMiddlewareDependencies(): BuildStep
    {
        return $this->layerRule('messengerValidatorMiddleware');
    }

    #[TestRule]
    public function testBundleDependencies(): BuildStep
    {
        return $this->layerRule('bundle');
    }

    public function layerRule(string $name): BuildStep
    {
        $layers = $this->layers();

        $layer = $layers[$name];

        /** @var SelectorInterface $layerClassesSelector */
        $layerClassesSelector = $this->{$name}(); // @phpstan-ignore method.dynamicName

        $deps = [$layerClassesSelector, ...$layer['deps']];

        if (isset($layer['wraps'])) {
            $wrappedLayerNames = $layer['wraps'];

            foreach ($wrappedLayerNames as $wrappedLayerName) {
                /** @var SelectorInterface $wrappedLayerSelector */
                $wrappedLayerSelector = $this->{$wrappedLayerName}(); // @phpstan-ignore method.dynamicName

                $wrappedLayerDeps = $layers[$wrappedLayerName]['deps'];

                $deps = [...$deps, $wrappedLayerSelector, ...$wrappedLayerDeps];
            }
        }
        $deps = [...$deps, self::coreDeps()];

        return PHPat::rule()
            ->classes(Selector::AllOf(
                $layerClassesSelector,
                Selector::Not(Selector::classname('/\\\Tests\\\/', true)),
                Selector::Not(Selector::extends(TestCase::class)),
            ))
            ->canOnly()
            ->dependOn()
            ->classes(...$deps)
            ->because($layer['description'] ?? 'See its dependency rules in '.self::class.'::layers()')
        ;
    }

    public static function coreDeps(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::classname(Assert::class),
            Selector::classname(ReusableIteratorAggregate::class),
        );
    }

    /** @return array<string,array{deps:list<SelectorInterface>, wraps?: list<string>, description?: string}> */
    public function layers(): array
    {
        return [
            'bundle' => [
                'deps' => [
                    Selector::inNamespace('Symfony\Component'),
                    Selector::classname(InstalledVersions::class),
                    Selector::inNamespace('PhPhD\ExceptionToolkit'),
                    // Container tags the autoloading discovery scans for
                    Selector::classname(MatchConditionCompiler::class),
                    Selector::classname(MatchedExceptionFormatter::class),
                ],
                'description' => 'Container wiring must not reach into the mapping model',
            ],
            'linter' => [
                'deps' => [
                    $this->node(),
                    $this->plan(),
                    $this->planCompiler(),
                    $this->exception(),
                    $this->matchCondition(),
                    Selector::inNamespace('Psr'),
                    Selector::inNamespace('Composer\ClassMapGenerator'),
                    Selector::inNamespace('Symfony\Component\Console'),
                ],
            ],
            'validatorMatcher' => [
                'deps' => [
                    $this->matcher(),
                    $this->node(),
                    $this->exception(),
                    Selector::inNamespace('Symfony\Component\Validator'),
                    Selector::classname(TranslatorInterface::class),
                    Selector::inNamespace('Psr\Container'),
                ],
            ],
            'messengerValidatorMiddleware' => [
                'deps' => [
                    Selector::AllOf(
                        Selector::isInterface(),
                        $this->matcher(),
                    ),
                    $this->validatorMiddleware(),
                    Selector::inNamespace('Symfony\Component\Messenger'),
                    Selector::classname(ConstraintViolationListInterface::class),
                ],
            ],
            'validatorMiddleware' => [
                'deps' => [
                    Selector::inNamespace('Symfony\Component\Validator'),
                ],
            ],
            'matcher' => [
                'deps' => [
                    $this->node(),
                    $this->plan(),
                    $this->planCompiler(),
                    $this->exception(),
                    Selector::classname(ExceptionUnwrapper::class),
                    Selector::inNamespace('Psr\Container'),
                    Selector::inNamespace('Psr\Log'),
                ],
            ],
            'planCompiler' => [
                'wraps' => ['plan', 'node'],
                'deps' => [
                    $this->matchConditionCompiler(),
                    Selector::inNamespace('Psr'),
                ],
            ],
            'plan' => [
                'wraps' => ['node'],
                'deps' => [],
            ],
            'node' => [
                'deps' => [
                    $this->exception(),
                    $this->matchCondition(),
                    // Lazy nesting: a node holds a nested object's plan, or resolves one through the registry
                    Selector::classname(ObjectExceptionMappingPlan::class),
                    Selector::classname(ObjectExceptionMappingPlanRegistry::class),
                ],
                'description' => 'Mapping nodes are the residual model layer: a class that no other layer claims lands here, and must depend on almost nothing',
            ],
            'matchConditionCompiler' => [
                'wraps' => ['matchCondition'],
                'deps' => [
                    Selector::inNamespace('Psr'),
                ],
            ],
            'matchCondition' => [
                'deps' => [
                    $this->node(),
                    $this->exception(),
                ],
            ],
            'exception' => [
                'deps' => [
                    Selector::classname(CatchExceptionMappingNode::class),
                    Selector::inNamespace('Psr\Container'), // formatter
                ],
                'description' => 'Exception models must not depend on anything else',
            ],
        ];
    }

    /**
     * Container wiring: the bundle extension, the compiler passes, and the class discovery they drive.
     *
     * @psalm-suppress UnusedMethod
     */
    public function bundle(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::inNamespace('PhPhD\ExceptionalMatcher\Bundle'),
            Selector::implements(CompilerPassInterface::class),
            Selector::classname(ConstantsAutoloadingClassDiscovery::class),
            Selector::implements(ConstantsAutoloadingClassDiscovery::class),
        );
    }

    /** The residual model layer: everything under `Mapping` that no other layer claims. */
    public function node(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::classname(ExceptionMatcher::class),
            Selector::implements(ExceptionMatcher::class),

            Selector::classname(ExceptionMappingNode::class),
            Selector::implements(ExceptionMappingNode::class),

            Selector::classname(ExceptionMatcherAggregate::class),
            Selector::implements(ExceptionMatcherAggregate::class),

            Selector::classname(PropertyPath::class),
        );
    }

    public function plan(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::classname('/ExceptionMappingPlan$/', true),
            Selector::classname(ObjectExceptionMappingPlanRegistry::class),
        );
    }

    public function planCompiler(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::AnyOf(
                Selector::inNamespace('/\\\Plan\\\(Registry\\\)?Compiler/', true),
                Selector::classname(Try_::class),
                Selector::classname(Catch_::class),
                Selector::classname(CompilingObjectExceptionMappingPlanRegistry::class),
            ),
            Selector::Not($this->bundle()),
        );
    }

    public function matchConditionCompiler(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::classname(MatchConditionCompiler::class),
            Selector::implements(MatchConditionCompiler::class),
            Selector::classname(Catch_::class),
        );
    }

    public function matchCondition(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::classname(MatchCondition::class),
            Selector::implements(MatchCondition::class),
            Selector::classname(MatchConditionPlan::class),
            Selector::AllOf(
                Selector::implements(MatchConditionPlan::class),
                Selector::Not($this->matchConditionCompiler())
            ),
            // Contract
            Selector::classname(ValueException::class),
            // Third-party
            Selector::classname(ValidationFailedException::class),
            Selector::classname(InvalidUidException::class),
        );
    }

    public function exception(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception'),
            Selector::Not($this->bundle()),
        );
    }

    public function linter(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalMatcher\Mapping\Linter'),
            Selector::Not($this->bundle()),
        );
    }

    public function matcher(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalMatcher'),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionalMatcher\Mapping')),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionalMatcher\Integration\Validator')),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionalMatcher\Upgrade')),
            Selector::Not($this->bundle()),
        );
    }

    public function validatorMatcher(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalMatcher\Integration\Validator'),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionalMatcher\Integration\Validator\Middleware')),
        );
    }

    public function validatorMiddleware(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalMatcher\Integration\Validator\Middleware'),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionalMatcher\Integration\Validator\Middleware\Messenger')),
        );
    }

    /** @psalm-suppress UnusedMethod */
    public function messengerValidatorMiddleware(): SelectorInterface
    {
        return Selector::inNamespace('PhPhD\ExceptionalMatcher\Integration\Validator\Middleware\Messenger');
    }
}
