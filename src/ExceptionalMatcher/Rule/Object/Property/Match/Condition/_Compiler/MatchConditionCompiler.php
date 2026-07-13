<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use Throwable;

/**
 * @api
 *
 * @template T of Throwable
 */
interface MatchConditionCompiler
{
    /**
     * @param Catch_<T,T> $catch
     *
     * @return ?MatchConditionBlueprint<T>
     */
    public function compile(Catch_ $catch): ?MatchConditionBlueprint;
}
