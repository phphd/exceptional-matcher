<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid\Tests\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid\InvalidUidExceptionMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

#[Try_]
final class MessageWithInvalidUidCondition
{
    public function __construct(
        #[Catch_(
            InvalidUidException::class,
            match: InvalidUidExceptionMatchCondition::class,
        )]
        public mixed $uid,
    ) {
    }
}
