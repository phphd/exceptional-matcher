<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests;

use PHPat\Selector\ClassNamespace;
use PHPat\Selector\Modifier\AllOfSelectorModifier;
use PHPat\Selector\Modifier\AnyOfSelectorModifier;
use PHPat\Selector\Selector;
use PHPat\Selector\SelectorInterface;
use PHPat\Test\Attributes\TestRule;
use PHPat\Test\Builder\BuildStep;
use PHPat\Test\PHPat;
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\IterableOfObjectsRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Validator\Constraints\Valid;
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
    public function testBundleDependencies(): BuildStep
    {
        return $this->layerRule('bundle');
    }

    #[TestRule]
    public function testMessengerValidatorMiddlewareDependencies(): BuildStep
    {
        return $this->layerRule('messengerValidatorMiddleware');
    }

    #[TestRule]
    public function testValidatorMiddlewareDependencies(): BuildStep
    {
        return $this->layerRule('validatorMiddleware');
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
    public function testMatchConditionFactoryDependencies(): BuildStep
    {
        return $this->layerRule('matchConditionFactory');
    }

    #[TestRule]
    public function testCaptureRuleSetAssemblerDependencies(): BuildStep
    {
        return $this->layerRule('captureRuleSetAssembler');
    }

    #[TestRule]
    public function testModelDependencies(): BuildStep
    {
        return $this->layerRule('model');
    }

    public function layerRule(string $name): BuildStep
    {
        $layer = $this->layers()[$name];

        /** @var SelectorInterface $layerClassesSelector */
        $layerClassesSelector = $this->{$name}(); // @phpstan-ignore method.dynamicName

        return PHPat::rule()
            ->classes(Selector::AllOf(
                $layerClassesSelector,
                Selector::NOT(Selector::classname('/\\\Tests\\\/', true)),
            ))
            ->canOnlyDependOn()
            ->classes($layerClassesSelector, ...$layer['deps'])
            ->because($layer['description'] ?? 'See its dependency rules in '.self::class.'::layers()')
        ;
    }

    /** @return array<string,array{deps:list<SelectorInterface>,description?: string}> */
    public function layers(): array
    {
        return [
            'bundle' => [
                'deps' => [
                    Selector::inNamespace('Symfony\Component'),
                ],
            ],
            'messengerValidatorMiddleware' => [
                'deps' => [
                    Selector::AllOf(
                        Selector::isInterface(),
                        $this->mapper(),
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
            'validatorMapper' => [
                'deps' => [
                    $this->mapper(),
                    $this->model(),
                    Selector::inNamespace('Symfony\Component\Validator'),
                    Selector::classname(TranslatorInterface::class),
                    Selector::classname(Assert::class),
                    Selector::inNamespace('Psr\Container'),
                ],
            ],
            'mapper' => [
                'deps' => [
                    Selector::classname(ObjectRuleSetAssembler::class),
                    $this->model(),
                    Selector::AllOf(
                        Selector::isInterface(),
                        $this->validatorMapper(),
                    ),
                    Selector::classname(ExceptionUnwrapper::class),
                ],
            ],
            'captureRuleSetAssembler' => [
                'deps' => [
                    $this->model(),
                    $this->matchConditionFactory(),
                    Selector::classname(ExceptionalValidation::class),
                    Selector::classname(Capture::class),
                    Selector::classname(Valid::class),
                    Selector::classname(Assert::class),
                ],
            ],
            'matchConditionFactory' => [
                'deps' => [
                    $this->model(),
                    Selector::classname(Capture::class),
                    Selector::classname(ValidationFailedException::class),
                    Selector::inNamespace('Psr\Container'),
                    Selector::classname(Container::class),
                    Selector::classname(Assert::class),
                ],
            ],
            'model' => [
                'deps' => [
                    Selector::classname(Assert::class),
                    Selector::classname(ValidationFailedException::class),
                ],
                'description' => 'Model classes must not depend on anything else',
            ],
        ];
    }

    /** @psalm-suppress UnusedMethod */
    public function bundle(): ClassNamespace
    {
        return Selector::inNamespace('PhPhD\ExceptionalValidation\Bundle');
    }

    public function mapper(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalValidation\Mapper'),
            Selector::NOT(Selector::inNamespace('PhPhD\ExceptionalValidation\Mapper\Validator')),
            Selector::NOT(Selector::extends(TestCase::class)),
        );
    }

    public function validatorMapper(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalValidation\Mapper\Validator'),
            Selector::NOT(Selector::inNamespace('PhPhD\ExceptionalValidation\Mapper\Validator\Middleware')),
            Selector::NOT(Selector::extends(TestCase::class)),
        );
    }

    public function validatorMiddleware(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalValidation\Mapper\Validator\Middleware'),
            Selector::NOT(Selector::inNamespace('PhPhD\ExceptionalValidation\Mapper\Validator\Middleware\Messenger')),
        );
    }

    /** @psalm-suppress UnusedMethod */
    public function messengerValidatorMiddleware(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalValidation\Mapper\Validator\Middleware\Messenger'),
            Selector::NOT(Selector::extends(TestCase::class)),
        );
    }

    public function captureRuleSetAssembler(): AnyOfSelectorModifier
    {
        return Selector::AnyOf(
            Selector::classname(CaptureRuleSetAssembler::class),
            Selector::implements(CaptureRuleSetAssembler::class),
            Selector::classname(IterableOfObjectsRuleSetAssembler::class),
            Selector::classname(ObjectRuleSetAssembler::class),
        );
    }

    public function matchConditionFactory(): AnyOfSelectorModifier
    {
        return Selector::AnyOf(
            Selector::classname(MatchConditionFactory::class),
            Selector::implements(MatchConditionFactory::class),
        );
    }

    public function model(): AllOfSelectorModifier
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalValidation\Rule'),
            Selector::NOT($this->matchConditionFactory()),
            Selector::NOT($this->captureRuleSetAssembler()),
            Selector::NOT(Selector::extends(TestCase::class)),
        );
    }
}
