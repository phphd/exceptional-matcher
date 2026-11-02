<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use ReflectionProperty;
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
    public function compile(ReflectionProperty $property, Catch_ $catch): ?MatchConditionBlueprint;
}
