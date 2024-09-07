<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception;

use PhPhD\ExceptionalValidation\Formatter\ViolationListException;
use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ViolationListExampleException extends RuntimeException implements ViolationListException
{
    public function __construct(
        private readonly ConstraintViolationListInterface $violationList,
    ) {
        parent::__construct();
    }

    public function getViolationList(): ConstraintViolationListInterface
    {
        return $this->violationList;
    }
}
