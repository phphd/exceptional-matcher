<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests;

use PHPat\Selector\ClassNamespace;
use PHPat\Selector\Selector;
use PHPat\Selector\SelectorInterface;
use PHPat\Test\Attributes\TestRule;
use PHPat\Test\Builder\BuildStep;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\IterableOfObjectsRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
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
    public function testBundleDependencies(): Rule
    {
        return $this->layerRule('bundle');
    }

    #[TestRule]
    public function testMiddlewareDependencies(): Rule
    {
        return $this->layerRule('middleware');
    }

    #[TestRule]
    public function testExceptionHandlerDependencies(): Rule
    {
        return $this->layerRule('exceptionHandler');
    }

    #[TestRule]
    public function testViolationFormatterDependencies(): Rule
    {
        return $this->layerRule('formatter');
    }

    #[TestRule]
    public function testMatchConditionFactoryDependencies(): Rule
    {
        return $this->layerRule('matchConditionFactory');
    }

    #[TestRule]
    public function testCaptureRuleSetAssemblerDependencies(): Rule
    {
        return $this->layerRule('captureRuleSetAssembler');
    }

    #[TestRule]
    public function testModelDependencies(): Rule
    {
        return $this->layerRule('model');
    }

    public function layerRule(string $name): BuildStep
    {
        $layer = $this->layers()[$name];

        $layerClasses = $this->{$name}();

        return PHPat::rule()
            ->classes(Selector::AllOf(
                $layerClasses,
                Selector::NOT(Selector::classname('/\\\Tests\\\/', true)),
            ))
            ->canOnlyDependOn()
            ->classes($layerClasses, ...$layer['deps'])
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
                    $this->formatter(),
                ],
            ],
            'middleware' => [
                'deps' => [
                    Selector::AllOf(
                        Selector::isInterface(),
                        $this->exceptionHandler(),
                    ),
                    Selector::inNamespace('Symfony\Component\Messenger'),
                ],
            ],
            'exceptionHandler' => [
                'deps' => [
                    Selector::classname(ObjectRuleSetAssembler::class),
                    $this->model(),
                    Selector::AllOf(
                        Selector::isInterface(),
                        $this->formatter(),
                    ),
                    Selector::classname(ConstraintViolationListInterface::class),
                    Selector::classname(ExceptionUnwrapper::class),
                ],
            ],
            'formatter' => [
                'deps' => [
                    $this->model(),
                    Selector::inNamespace('Symfony\Component\Validator'),
                    Selector::classname(TranslatorInterface::class),
                    Selector::inNamespace('Psr\Container'),
                ],
            ],
            'captureRuleSetAssembler' => [
                'deps' => [
                    $this->model(),
                    Selector::classname(ExceptionalValidation::class),
                    Selector::classname(Capture::class),
                    Selector::classname(Valid::class),
                    Selector::classname(MatchConditionFactory::class),
                    Selector::classname(Assert::class),
                ],
            ],
            'matchConditionFactory' => [
                'deps' => [
                    $this->model(),
                    Selector::classname(Capture::class),
                    Selector::classname(ValidationFailedException::class),
                    Selector::inNamespace('Psr\Container'),
                ],
            ],
            'model' => [
                'deps' => [
                    Selector::classname(Assert::class),
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

    /** @psalm-suppress UnusedMethod */
    public function middleware(): ClassNamespace
    {
        return Selector::inNamespace('PhPhD\ExceptionalValidation\Middleware');
    }

    public function exceptionHandler(): ClassNamespace
    {
        return Selector::inNamespace('PhPhD\ExceptionalValidation\Handler');
    }

    public function formatter(): ClassNamespace
    {
        return Selector::inNamespace('PhPhD\ExceptionalValidation\Formatter');
    }

    public function captureRuleSetAssembler(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::classname(CaptureRuleSetAssembler::class),
            Selector::implements(CaptureRuleSetAssembler::class),
            Selector::classname(IterableOfObjectsRuleSetAssembler::class),
            Selector::classname(ObjectRuleSetAssembler::class),
        );
    }

    public function matchConditionFactory(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::classname(MatchConditionFactory::class),
            Selector::implements(MatchConditionFactory::class),
        );
    }

    public function model(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::inNamespace('PhPhD\ExceptionalValidation\Rule'),
            Selector::NOT($this->matchConditionFactory()),
            Selector::NOT($this->captureRuleSetAssembler()),
        );
    }
}
