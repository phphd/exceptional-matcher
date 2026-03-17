<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\Tests\Stub;

use PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationListException;
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
