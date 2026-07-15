<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\Tests\Stub;

use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\ViolationsEmbeddedException;
use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ViolationsEmbeddedExampleException extends RuntimeException implements ViolationsEmbeddedException
{
    public function __construct(
        private readonly ConstraintViolationListInterface $violations,
    ) {
        parent::__construct();
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
