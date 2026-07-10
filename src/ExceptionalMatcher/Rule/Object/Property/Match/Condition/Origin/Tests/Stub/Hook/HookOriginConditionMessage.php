<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\Tests\Stub\Hook;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionalMatcher\Validator\Formatter\Validator\validator_violations;

#[Try_]
final class HookOriginConditionMessage
{
    /** @psalm-suppress ArgumentTypeCoercion */
    public function __construct(
        #[Catch_(ValidationFailedException::class, from: [ProductHookedEntity::class, '$title::set'], format: validator_violations)]
        public string $title,
    ) {
    }
}
