<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub;

/**
 * A plain, final object that carries no #[Try_] mapping.
 * Because it is final, no plan-bearing subclass can be assigned in its place,
 * so a property typed with it can never hold a matchable object.
 */
final class NonMatchableObject
{
}
