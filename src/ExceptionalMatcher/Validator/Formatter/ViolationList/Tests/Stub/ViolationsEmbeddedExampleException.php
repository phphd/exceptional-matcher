<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\Tests\Stub;

use PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationsEmbeddedException;
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
