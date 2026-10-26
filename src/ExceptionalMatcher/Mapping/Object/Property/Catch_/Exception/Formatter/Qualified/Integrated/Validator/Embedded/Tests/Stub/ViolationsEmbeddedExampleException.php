<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Integrated\Validator\Embedded\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Integrated\Validator\Embedded\ViolationsEmbeddedException;
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
