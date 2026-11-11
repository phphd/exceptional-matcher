<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Tests;

use Composer\InstalledVersions;
use PHPat\Selector\ClassNamespace;
use PHPat\Selector\Modifier\AllOfSelectorModifier;
use PHPat\Selector\Modifier\AnyOfSelectorModifier;
use PHPat\Selector\Selector;
use PHPat\Selector\SelectorInterface;
use PHPat\Test\Attributes\TestRule;
use PHPat\Test\Builder\BuildStep;
use PHPat\Test\PHPat;
use PhPhD\ExceptionMapper\Mapping\Blueprint\ExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Matcher\ExceptionMatcher;
use PhPhD\ExceptionMapper\Mapping\Matcher\ExceptionMatcherAggregate;
use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Compiler\CompilingObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\ConstantsAutoloadingClassDiscovery;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\ConstantsClassLoader;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\CatchExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\MemoizingIteratorAggregate;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Value\ValueException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\MatchedException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Path\PropertyPath;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
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
    public function testCompilerDependencies(): BuildStep
    {
        return $this->layerRule('compiler');
    }

    #[TestRule]
    public function testBlueprintDependencies(): BuildStep
    {
        return $this->layerRule('blueprint');
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
    public function testMatchConditionBlueprintDependencies(): BuildStep
    {
        return $this->layerRule('matchConditionBlueprint');
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
    public function testExceptionFormatterDependencies(): BuildStep
    {
        return $this->layerRule('exceptionFormatter');
    }

    #[TestRule]
    public function testValidatorExceptionFormatterDependencies(): BuildStep
    {
        return $this->layerRule('validatorExceptionFormatter');
    }

    #[TestRule]
    public function testLinterDependencies(): BuildStep
    {
        return $this->layerRule('linter');
    }

    #[TestRule]
    public function testMapperDependencies(): BuildStep
    {
        return $this->layerRule('mapper');
    }

    #[TestRule]
    public function testValidatorMapperDependencies(): BuildStep
    {
        return $this->layerRule('validatorMapper');
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

    #[TestRule]
    public function testPropertyTypeDependencies(): BuildStep
    {
        return $this->layerRule('propertyType');
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
                Selector::Not(Selector::classname('/\\\Tests\\\/', regex: true)),
                Selector::Not(Selector::extends(TestCase::class)),
            ))
            ->canOnly()
            ->dependOn()
            ->classes(...$deps)
            ->because($layer['description'] ?? 'See its dependency rules in '.self::class.'::layers()')
        ;
    }

    public static function coreDeps(): AnyOfSelectorModifier
    {
        return Selector::AnyOf(
            Selector::classname(Assert::class),
            Selector::classname(MemoizingIteratorAggregate::class),
        );
    }

    /** @return array<string,array{deps:list<SelectorInterface>, wraps?: list<string>, description?: string}> */
    public function layers(): array
    {
        return [
            'bundle' => [
                'deps' => [
                    Selector::inNamespace('PhPhD\ExceptionToolkit'),

                    Selector::classname(ConstantsClassLoader::class),
                    Selector::classname(MatchConditionCompiler::class),
                    Selector::classname(MatchedExceptionFormatter::class),
                    // Bundle & Extension
                    Selector::inNamespace('Symfony\Component'),
                    Selector::classname(InstalledVersions::class),
                ],
                'description' => 'Container wiring must not reach into the mapping model',
            ],
            'linter' => [
                'deps' => [
                    $this->compiler(),
                    $this->blueprint(),
                    $this->matchConditionBlueprint(),
                    $this->matchCondition(),
                    Selector::inNamespace('Psr'),
                    Selector::inNamespace('Composer\ClassMapGenerator'),
                    Selector::inNamespace('Symfony\Component\Console'),
                ],
            ],
            'messengerValidatorMiddleware' => [
                'wraps' => ['validatorMiddleware', 'validatorMapper'],
                'deps' => [
                    Selector::inNamespace('Symfony\Component\Messenger'),
                ],
            ],
            'validatorMiddleware' => [
                'deps' => [
                    Selector::inNamespace('Symfony\Component\Validator'),
                ],
            ],
            'validatorMapper' => [
                'deps' => [
                    $this->mapper(),
                    $this->node(),
                    $this->exception(),
                    Selector::inNamespace('Symfony\Component\Validator'),
                ],
            ],
            'mapper' => [
                'deps' => [
                    $this->blueprint(),
                    $this->node(),
                    $this->exception(),
                    Selector::classname(ExceptionUnwrapper::class),
                ],
            ],
            'compiler' => [
                'wraps' => ['blueprint', 'node'],
                'deps' => [
                    $this->propertyType(),
                    $this->matchConditionCompiler(),
                    $this->exceptionFormatter(),
                    Selector::inNamespace('Psr'),
                ],
            ],
            'blueprint' => [
                'wraps' => ['node'],
                'deps' => [
                    $this->propertyType(),
                    $this->matchConditionBlueprint(),
                ],
            ],
            'node' => [
                'deps' => [
                    $this->blueprint(),
                    $this->exception(),
                    $this->matchCondition(),
                ],
                'description' => 'Mapping nodes are the core model layer and must depend on almost nothing',
            ],
            'propertyType' => [
                'deps' => [],
                'description' => 'Property type analysis must be independent',
            ],
            'matchConditionCompiler' => [
                'wraps' => ['matchConditionBlueprint', 'matchCondition'],
                'deps' => [
                    $this->propertyType(),
                    Selector::inNamespace('Psr'),
                ],
            ],
            'matchConditionBlueprint' => [
                'wraps' => ['matchCondition'],
                'deps' => [
                    $this->propertyType(),
                ],
            ],
            'matchCondition' => [
                'deps' => [
                    $this->node(),
                    $this->exception(),
                ],
            ],
            'validatorExceptionFormatter' => [
                'wraps' => ['exceptionFormatter'],
                'deps' => [
                    Selector::inNamespace('Symfony\Component\Validator'),
                ],
            ],
            'exceptionFormatter' => [
                'deps' => [
                    Selector::classname(MatchedException::class),
                    Selector::classname(TranslatorInterface::class),
                    Selector::inNamespace('Psr\Container'), // Registry
                ],
            ],
            'exception' => [
                'deps' => [
                    Selector::classname(CatchExceptionMappingNode::class),
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
    public function bundle(): AnyOfSelectorModifier
    {
        return Selector::AnyOf(
            Selector::inNamespace('PhPhD\ExceptionMapper\Bundle'),
            Selector::implements(CompilerPassInterface::class),
            Selector::classname(ConstantsAutoloadingClassDiscovery::class),
            Selector::implements(ConstantsAutoloadingClassDiscovery::class),
        );
    }

    public function node(): AnyOfSelectorModifier
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

    public function blueprint(): AnyOfSelectorModifier
    {
        return Selector::AnyOf(
            Selector::classname(ExceptionMappingBlueprint::class),
            Selector::implements(ExceptionMappingBlueprint::class),
            Selector::classname(ObjectExceptionMappingBlueprintRegistry::class),
        );
    }

    public function compiler(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::AnyOf(
                Selector::inNamespace('/\\\Blueprint\\\(Registry\\\)?Compiler/', regex: true),
                Selector::classname(Try_::class),
                Selector::classname(Catch_::class),
                Selector::classname(CompilingObjectExceptionMappingBlueprintRegistry::class),
            ),
            Selector::Not($this->bundle()),
        );
    }

    public function matchConditionCompiler(): AnyOfSelectorModifier
    {
        return Selector::AnyOf(
            Selector::classname(MatchConditionCompiler::class),
            Selector::implements(MatchConditionCompiler::class),
            Selector::classname(Catch_::class),
        );
    }

    public function matchCondition(): AnyOfSelectorModifier
    {
        return Selector::AnyOf(
            Selector::classname(MatchCondition::class),
            Selector::implements(MatchCondition::class),
            // Contract
            Selector::classname(ValueException::class),
            // Third-party
            Selector::classname(ValidationFailedException::class),
            Selector::classname(InvalidUidException::class),
        );
    }

    public function matchConditionBlueprint(): AnyOfSelectorModifier
    {
        return Selector::AnyOf(
            Selector::classname(MatchConditionBlueprint::class),
            Selector::AllOf(
                Selector::implements(MatchConditionBlueprint::class),
                Selector::Not($this->matchConditionCompiler()),
            ),
        );
    }

    public function exceptionFormatter(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter'),
            Selector::Not($this->validatorExceptionFormatter()),
            Selector::Not($this->bundle()),
        );
    }

    public function validatorExceptionFormatter(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator'),
            Selector::Not($this->bundle()),
        );
    }

    public function exception(): AnyOfSelectorModifier
    {
        return Selector::AnyOf(
            Selector::AllOf(
                Selector::inNamespace('PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception'),
                Selector::Not($this->exceptionFormatter()),
                Selector::Not($this->validatorExceptionFormatter()),
                Selector::Not($this->bundle()),
            ),
            Selector::classname(MatchedExceptionFormatter::class),
        );
    }

    public function linter(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionMapper\Mapping\Linter'),
            Selector::Not($this->bundle()),
        );
    }

    public function mapper(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionMapper'),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionMapper\Mapping')),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionMapper\Port\Symfony\Validator')),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionMapper\Upgrade')),
            Selector::Not($this->bundle()),
        );
    }

    public function validatorMapper(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionMapper\Port\Symfony\Validator'),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionMapper\Port\Symfony\Validator\Middleware')),
        );
    }

    public function validatorMiddleware(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionMapper\Port\Symfony\Validator\Middleware'),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionMapper\Port\Symfony\Validator\Middleware\Messenger')),
        );
    }

    public function propertyType(): ClassNamespace
    {
        return Selector::inNamespace('PhPhD\ExceptionMapper\Mapping\Object\Property\Type');
    }

    /** @psalm-suppress UnusedMethod */
    public function messengerValidatorMiddleware(): ClassNamespace
    {
        return Selector::inNamespace('PhPhD\ExceptionMapper\Port\Symfony\Validator\Middleware\Messenger');
    }
}
