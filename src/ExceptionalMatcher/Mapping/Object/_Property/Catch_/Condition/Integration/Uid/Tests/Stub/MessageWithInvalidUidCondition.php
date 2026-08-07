<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Integration\Uid\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

use const PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Integration\Uid\uid_value;

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
