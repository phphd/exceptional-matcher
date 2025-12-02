<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Validation;

final class Email
{
    private function __construct(
        private readonly string $email,
    ) {
    }

    public static function fromString(string $uuid): self
    {
        $validate = Validation::createCallable(new EmailConstraint());

        /** @var string $email */
        $email = $validate($uuid);

        return new self($email);
    }

    /** @psalm-suppress PossiblyUnusedReturnValue */
    public function getEmail(): string
    {
        return $this->email;
    }
}
