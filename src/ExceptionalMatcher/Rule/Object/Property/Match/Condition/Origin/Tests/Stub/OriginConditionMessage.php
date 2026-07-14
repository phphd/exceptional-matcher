<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\Tests\Stub;

use InvalidArgumentException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;

#[Try_]
final class OriginConditionMessage
{
    /** @psalm-suppress ArgumentTypeCoercion */
    public function __construct(
        #[Catch_(ValidationFailedException::class, from: Email::class)]
        public string $email = 'some-email',
        #[Catch_(InvalidArgumentException::class, from: [Uuid::class, 'fromString'])]
        public string $uid = 'some-uid',
        #[Catch_(ValidationFailedException::class, from: __NAMESPACE__.'\validate_email_string')]
        public string $anotherEmail = 'another-email',
    ) {
    }
}

function validate_email_string(string $email): void
{
    $validate = Validation::createCallable(new EmailConstraint());

    $validate($email);
}
