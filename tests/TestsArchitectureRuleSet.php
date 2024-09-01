<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests;

use PHPat\Selector\Selector;
use PHPat\Test\Attributes\TestRule;
use PHPat\Test\Builder\Rule;
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
    public function testsMustBeIncludedForPHPUnit(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::AND(
                Selector::extends(TestCase::class),
                Selector::NOT(Selector::isAbstract()),
                Selector::NOT(Selector::classname('/UnitTest$/', true)),
                Selector::NOT(Selector::classname('/IntegrationTest$/', true)),
            ))
            ->shouldNotExtend()
            ->classes(Selector::classname(TestCase::class))
            ->because("Do you know what's worse than no tests? Tests that never run! The test is not included for PHPUnit!")
        ;
    }
}
