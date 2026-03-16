<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Uid\Tests\Stub;

use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Catch_;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Uid\InvalidUidExceptionMatchCondition;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

#[ExceptionalValidation]
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
