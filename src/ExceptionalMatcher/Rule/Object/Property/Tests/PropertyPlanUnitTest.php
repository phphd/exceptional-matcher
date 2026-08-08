<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\TestServicesCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Object\ento\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\ento\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\ento\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\ento\Plan\Compiler\_Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\CountingMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\LazilyBrokenCatchMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\MultiCatchMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\DecoratorServicePass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Reference;
use Throwable;
use Webmozart\Assert\Assert;

use function count;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\ento\Plan\PropertyExceptionMappingPlan
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\ento\Plan\Compiler\ObjectExceptionMappingPlanCompiler
 */
final class PropertyPlanUnitTest extends TestCase
{
    /** @var ObjectExceptionMappingPlanRegistry<object> */
    private ObjectExceptionMappingPlanRegistry $registry;

    private CountingMatchConditionCompiler $conditionCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension(true))->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->addCompilerPass(new TestServicesCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, TestServicesCompilerPass::PRIORITY);
        $container->addCompilerPass(new DecoratorServicePass(), PassConfig::TYPE_OPTIMIZE);

        // counting every condition it compiles is what makes the laziness of the plans observable
        $container
            ->register(CountingMatchConditionCompiler::class, CountingMatchConditionCompiler::class)
            ->setArguments([new Reference('.inner')])
            ->setDecoratedService(MatchConditionCompiler::class.'<'.Throwable::class.'>')
            ->setPublic(true)
        ;

        $container->compile();

        /** @var ObjectExceptionMappingPlanRegistry<object> $registry */
        $registry = $container->get(ObjectExceptionMappingPlanRegistry::class);
        $this->registry = $registry;

        /** @var CountingMatchConditionCompiler $conditionCompiler */
        $conditionCompiler = $container->get(CountingMatchConditionCompiler::class);
        $this->conditionCompiler = $conditionCompiler;
    }

    public function testCompilesCatchPlansLazilyAndMemoizesThem(): void
    {
        $compiler = $this->conditionCompiler;

        $plan = $this->registry->getPlan(MultiCatchMessage::class);

        Assert::notNull($plan);

        $compilations = [];
        $compilations['skeletal plan'] = $compiler->getCompilations();

        [$propertyPlan] = [...$plan->getPropertyPlans()];
        $compilations['property plans traversed'] = $compiler->getCompilations();

        $catchPlans = [...$propertyPlan->getCatchPlans()];
        $compilations['catch plans traversed'] = $compiler->getCompilations();

        self::assertCount(1, [...$plan->getPropertyPlans()]);
        self::assertCount(3, [...$propertyPlan->getCatchPlans()]);
        $compilations['everything re-traversed'] = $compiler->getCompilations();

        self::assertCount(3, $catchPlans);
        self::assertSame([
            // The emptiness check reads the first property plan,
            // which in turn, checks first catch plan, and one plan ahead of the caching iterator
            'skeletal plan' => 2,
            'property plans traversed' => 2,
            'catch plans traversed' => 3,
            'everything re-traversed' => 3,
        ], $compilations);
    }

    public function testFailedCompilationIsRetriedOnNextIteration(): void
    {
        $plan = $this->getLazilyBrokenCatchPlan();

        // the property with a broken catch mapping is still planned - the failure surfaces on access
        [$propertyPlan] = [...$plan->getPropertyPlans()];

        try {
            $catchPlans = [...$propertyPlan->getCatchPlans()];

            self::fail('The broken catch mapping must have failed the compilation, got '.count($catchPlans).' catch plans.');
        } catch (CatchExceptionMappingPlanCompilationFailedException $exception) {
            $previous = $exception->getPrevious();

            self::assertNotNull($previous);
            self::assertStringContainsString('EnumValueMatchCondition requires `from:`', $previous->getMessage());
        }

        $this->expectException(CatchExceptionMappingPlanCompilationFailedException::class);
        $this->expectExceptionMessage('#[Catch_] attribute compilation has failed.');

        self::assertCount(0, [...$propertyPlan->getCatchPlans()]);
    }

    private function getLazilyBrokenCatchPlan(): ObjectExceptionMappingPlan
    {
        $plan = $this->registry->getPlan(LazilyBrokenCatchMessage::class);

        Assert::notNull($plan);

        return $plan;
    }
}
