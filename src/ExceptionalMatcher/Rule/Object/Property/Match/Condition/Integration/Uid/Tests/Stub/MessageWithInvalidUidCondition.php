<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Integration\Uid\Tests\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Integration\Uid\uid_value;

#[Try_]
final class MessageWithInvalidUidCondition
{
    /** @psalm-suppress ArgumentTypeCoercion */
    public function __construct(
        #[Catch_(InvalidUidException::class, match: uid_value)]
        public mixed $uid,
    ) {
    }
}
