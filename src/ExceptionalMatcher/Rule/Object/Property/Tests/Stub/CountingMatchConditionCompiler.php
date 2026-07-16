<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
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

    public function compile(Catch_ $catch): ?MatchConditionBlueprint
    {
        ++$this->compilations;

        return $this->innerCompiler->compile($catch);
    }

    public function getCompilations(): int
    {
        return $this->compilations;
    }
}
