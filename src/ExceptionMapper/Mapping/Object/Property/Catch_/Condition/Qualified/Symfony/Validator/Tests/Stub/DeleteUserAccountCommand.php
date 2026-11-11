<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Validator\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Validator\Tests\ValidatedValueConditionRule;
use PhPhD\ExceptionMapper\Tests\Probe\ProbeOf;
use RuntimeException;

use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Validator\validated_value;

final class DeleteUserAccountCommand
{
    #[ProbeOf(ValidatedValueConditionRule::NonValidationFailedExceptionClassThrows)]
    #[Catch_(RuntimeException::class, match: validated_value)] // @phpstan-ignore argument.type (specifically test the case of missing static analysis)
    private mixed $userId;
}
