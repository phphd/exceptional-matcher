<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\Tests\Stub;

use InvalidArgumentException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[Try_]
final class OriginConditionMessage
{
    /** @psalm-suppress ArgumentTypeCoercion */
    public function __construct(
        #[Catch_(ValidationFailedException::class, from: Email::class)]
        public string $email,
        #[Catch_(InvalidArgumentException::class, from: [Uuid::class, 'fromString'])]
        public string $uid,
    ) {
    }
}
