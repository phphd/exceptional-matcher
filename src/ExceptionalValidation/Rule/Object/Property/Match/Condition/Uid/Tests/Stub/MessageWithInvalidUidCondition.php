<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Uid\Tests\Stub;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Uid\InvalidUidExceptionMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Try_;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

#[Try_]
final class MessageWithInvalidUidCondition
{
    public function __construct(
        #[Catch_(
            InvalidUidException::class,
            condition: InvalidUidExceptionMatchCondition::class,
        )]
        public mixed $uid,
    ) {
    }
}
