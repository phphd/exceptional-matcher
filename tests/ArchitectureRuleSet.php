<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests;

use Composer\InstalledVersions;
use PHPat\Selector\Selector;
use PHPat\Selector\SelectorInterface;
use PHPat\Test\Attributes\TestRule;
use PHPat\Test\Builder\BuildStep;
use PHPat\Test\PHPat;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\MatchExceptionRule;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
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
    public function testMatcherDependencies(): BuildStep
    {
        return $this->layerRule('matcher');
    }

    #[TestRule]
    public function testExceptionDependencies(): BuildStep
    {
        return $this->layerRule('exception');
    }

    #[TestRule]
    public function testMatchingRuleSetAssemblerDependencies(): BuildStep
    {
        return $this->layerRule('matchingRuleSetAssembler');
    }

    #[TestRule]
    public function testModelDependencies(): BuildStep
    {
        return $this->layerRule('model');
    }

    #[TestRule]
    public function testMatchConditionDependencies(): BuildStep
    {
        return $this->layerRule('matchCondition');
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
            'matcher' => [
                'deps' => [
                    Selector::classname(MatchingRuleSetAssemblerService::class),
                    Selector::classname(ObjectMatchingRuleSetAssembler::class),
                    $this->exception(),
                    $this->model(),
                    Selector::classname(ExceptionUnwrapper::class),
                ],
            ],
            'exception' => [
                'deps' => [
                    Selector::classname(MatchExceptionRule::class),
                    Selector::classname(Assert::class),
                    Selector::classname(ContainerInterface::class), // formatter
                ],
                'description' => 'Exception Models must not depend on anything else',
            ],
            'matchingRuleSetAssembler' => [
                'deps' => [
                    $this->model(),
                    $this->matchCondition(),
                    Selector::classname(Assert::class),
                ],
            ],
            'model' => [
                'deps' => [
                    $this->exception(),
                    Selector::classname(Assert::class),
                    Selector::classname(ContainerInterface::class),
                ],
                'description' => 'Model classes must not depend on anything else',
            ],
            'matchCondition' => [
                'deps' => [
                    $this->model(),
                    Selector::classname(Assert::class),
                    Selector::inNamespace('Psr\Container'),
                    // Third-party
                    Selector::classname(ValidationFailedException::class),
                    Selector::classname(InvalidUidException::class),
                ],
            ],
            'validatorMatcher' => [
                'deps' => [
                    $this->matcher(),
                    $this->exception(),
                    $this->model(),
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
                    Selector::implements(CompilerPassInterface::class),
                    Selector::inNamespace('Symfony\Component'),
                    Selector::classname(InstalledVersions::class),
                    Selector::inNamespace('PhPhD\ExceptionToolkit'),
                ],
            ],
        ];
    }

    /** @psalm-suppress UnusedMethod */
    public function bundle(): SelectorInterface
    {
        return Selector::inNamespace('PhPhD\ExceptionalMatcher\Bundle');
    }

    public function matcher(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalMatcher'),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionalMatcher\Bundle')),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionalMatcher\Exception')),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionalMatcher\Rule')),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionalMatcher\Integration\Validator')),
            Selector::Not(Selector::inNamespace('PhPhD\ExceptionalMatcher\Upgrade')),
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

    public function matchingRuleSetAssembler(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::classname(MatchingRuleSetAssembler::class),
            Selector::implements(MatchingRuleSetAssembler::class),
            Selector::classname(MatchingRuleSetAssemblerService::class),
            Selector::implements(MatchingRuleSetAssemblerService::class),
        );
    }

    public function matchCondition(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::implements(MatchCondition::class),
            Selector::classname(MatchConditionCompiler::class),
            Selector::implements(MatchConditionCompiler::class),
            Selector::classname(MatchConditionBlueprint::class),
            Selector::implements(MatchConditionBlueprint::class),
        );
    }

    public function exception(): SelectorInterface
    {
        return Selector::inNamespace('PhPhD\ExceptionalMatcher\Exception');
    }

    public function model(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalMatcher\Rule'),
            Selector::Not($this->matchCondition()),
            Selector::Not($this->matchingRuleSetAssembler()),
            Selector::Not(Selector::implements(CompilerPassInterface::class)),
        );
    }
}
