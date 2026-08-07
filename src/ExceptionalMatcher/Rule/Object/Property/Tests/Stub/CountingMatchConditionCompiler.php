<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\entos\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\entos\Compiler\MatchConditionPlan;
use Throwable;

/** @implements MatchConditionCompiler<Throwable> */
final class CountingMatchConditionCompiler implements MatchConditionCompiler
{
    private int $compilations = 0;

    public function __construct(
        /** @var MatchConditionCompiler<Throwable> */
        private readonly MatchConditionCompiler $innerCompiler,
    ) {
    }

    public function compile(Catch_ $catch): ?MatchConditionPlan
    {
        ++$this->compilations;

        return $this->innerCompiler->compile($catch);
    }

    public function getCompilations(): int
    {
        return $this->compilations;
    }
}
