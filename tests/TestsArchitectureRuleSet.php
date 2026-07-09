<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests;

use PHPat\Selector\Selector;
use PHPat\Test\Attributes\TestRule;
use PHPat\Test\Builder\BuildStep;
use PHPat\Test\PHPat;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @api
 */
final class TestsArchitectureRuleSet
{
    #[TestRule]
    public function testsMustBeIncludedForPHPUnit(): BuildStep
    {
        return PHPat::rule()
            ->classes(Selector::AllOf(
                Selector::extends(TestCase::class),
                Selector::Not(Selector::isAbstract()),
                Selector::Not(Selector::classname('/UnitTest$/', true)),
                Selector::Not(Selector::classname('/IntegrationTest$/', true)),
                Selector::Not(Selector::classname('/ServiceTest$/', true)),
            ))
            ->shouldNot()
            ->extend()
            ->classes(Selector::classname(TestCase::class))
            ->because("Do you know what's worse than no tests? Tests that never run! The test is not included for PHPUnit!")
        ;
    }
}
