<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests;

use Composer\InstalledVersions;
use PHPat\Selector\Selector;
use PHPat\Selector\SelectorInterface;
use PHPat\Test\Attributes\TestRule;
use PHPat\Test\Builder\BuildStep;
use PHPat\Test\PHPat;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\CatchExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsAutoloadingClassDiscovery;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsClassLoader;
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
    public function testNodeDependencies(): BuildStep
    {
        return $this->layerRule('node');
    }

    #[TestRule]
    public function testPlanDependencies(): BuildStep
    {
        return $this->layerRule('plan');
    }

    #[TestRule]
    public function testPlanCompilerDependencies(): BuildStep
    {
        return $this->layerRule('planCompiler');
    }

    #[TestRule]
    public function testMatchConditionDependencies(): BuildStep
    {
        return $this->layerRule('matchCondition');
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
        $layer = $this->layers()[$name];

        /** @var SelectorInterface $layerClassesSelector */
        $layerClassesSelector = $this->{$name}(); // @phpstan-ignore method.dynamicName

        return PHPat::rule()
            ->classes(Selector::AllOf(
                $layerClassesSelector,
                Selector::Not(Selector::classname('/\\\Tests\\\/', true)),
                Selector::Not(Selector::extends(TestCase::class)),
            ))
            ->canOnly()
            ->dependOn()
            ->classes($layerClassesSelector, ...$layer['deps'])
            ->because($layer['description'] ?? 'See its dependency rules in '.self::class.'::layers()')
        ;
    }

    /** @return array<string,array{deps:list<SelectorInterface>,description?: string}> */
    public function layers(): array
    {
        return [
            'node' => [
                'deps' => [
                    $this->exception(),
                    $this->matchCondition(),
                    // Lazy nesting: a node holds a nested object's plan, or resolves one through the registry
                    Selector::classname(ObjectExceptionMappingPlan::class),
                    Selector::classname(ObjectExceptionMappingPlanRegistry::class),
                    Selector::classname(Assert::class),
                ],
                'description' => 'Mapping nodes are the residual model layer: a class that no other layer claims lands here, and must depend on almost nothing',
            ],
            'plan' => [
                'deps' => [
                    $this->node(),
                    $this->exception(),
                    $this->matchCondition(),
                    // A plan resolving nested plans lazily is how nested matching works
                    Selector::classname(ObjectExceptionMappingPlanRegistry::class),
                    Selector::classname(Assert::class),
                ],
            ],
            'planCompiler' => [
                'deps' => [
                    $this->node(),
                    $this->plan(),
                    $this->exception(),
                    $this->matchCondition(),
                    Selector::classname(Assert::class),
                    Selector::inNamespace('Psr\Container'),
                    Selector::inNamespace('Psr\Log'),
                ],
            ],
            'matchCondition' => [
                'deps' => [
                    $this->node(),
                    $this->exception(),
                    Selector::classname(Assert::class),
                    Selector::inNamespace('Psr\Container'),
                    // Third-party
                    Selector::classname(ValidationFailedException::class),
                    Selector::classname(InvalidUidException::class),
                ],
            ],
            'exception' => [
                'deps' => [
                    Selector::classname(CatchExceptionMappingNode::class),
                    Selector::classname(Assert::class),
                    Selector::inNamespace('Psr\Container'), // formatter
                ],
                'description' => 'Exception models must not depend on anything else',
            ],
            'linter' => [
                'deps' => [
                    $this->node(),
                    $this->plan(),
                    $this->planCompiler(),
                    $this->exception(),
                    $this->matchCondition(),
                    Selector::classname(Assert::class),
                    Selector::inNamespace('Psr\Container'),
                    Selector::inNamespace('Psr\Log'),
                    Selector::inNamespace('Composer\ClassMapGenerator'),
                    Selector::inNamespace('Symfony\Component\Console'),
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
            'validatorMatcher' => [
                'deps' => [
                    $this->matcher(),
                    $this->node(),
                    $this->exception(),
                    Selector::inNamespace('Symfony\Component\Validator'),
                    Selector::classname(TranslatorInterface::class),
                    Selector::classname(Assert::class),
                    Selector::inNamespace('Psr\Container'),
                ],
            ],
            'validatorMiddleware' => [
                'deps' => [
                    Selector::inNamespace('Symfony\Component\Validator'),
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
            // the service whose argument the autoloading pass rewrites
            Selector::classname(ConstantsClassLoader::class),
        );
    }

    /** The residual model layer: everything under `Mapping` that no other layer claims. */
    public function node(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalMatcher\Mapping'),
            Selector::Not($this->plan()),
            Selector::Not($this->planCompiler()),
            Selector::Not($this->matchCondition()),
            Selector::Not($this->exception()),
            Selector::Not($this->linter()),
            Selector::Not($this->bundle()),
        );
    }

    public function plan(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::classname('/ExceptionMappingPlan$/', true),
            Selector::Not($this->bundle()),
        );
    }

    public function planCompiler(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::AnyOf(
                Selector::classname('/ExceptionMappingPlan(Compiler|Registry)$/', true),
                Selector::inNamespace('/\\\Plan\\\Compiler$/', true),
            ),
            Selector::Not($this->bundle()),
        );
    }

    public function matchCondition(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::AnyOf(
                Selector::implements(MatchCondition::class),
                Selector::classname(MatchConditionCompiler::class),
                Selector::implements(MatchConditionCompiler::class),
                Selector::classname(MatchConditionPlan::class),
                Selector::implements(MatchConditionPlan::class),
            ),
            Selector::Not($this->bundle()),
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
