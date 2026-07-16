<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use Psr\Container\ContainerInterface;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @template T of Throwable
 *
 * @implements ContainerInterface<class-string<MatchCondition<T>>,MatchConditionCompiler<T>>
 */
final class InMemoryCompilerRegistry implements ContainerInterface
{
    public function __construct(
        /** @var array<class-string<MatchCondition<T>>,MatchConditionCompiler<T>> */
        private readonly array $compilers = [],
    ) {
    }

    /** @return MatchConditionCompiler<T> */
    public function get(string $id): MatchConditionCompiler
    {
        Assert::keyExists($this->compilers, $id);

        /** @psalm-suppress PossiblyInvalidArrayOffset asserted right above */
        return $this->compilers[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->compilers[$id]);
    }
}
