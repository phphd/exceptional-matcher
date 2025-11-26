<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ViolationList;

use LogicException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Webmozart\Assert\Assert;

use function array_map;
use function iterator_to_array;

/**
 * @api
 *
 * @implements ExceptionViolationFormatter<ViolationListException>
 */
final class ViolationListExceptionFormatter implements ExceptionViolationFormatter
{
    /**
     * @param CapturedException<ViolationListException> $capturedException
     *
     * @return non-empty-list<ConstraintViolation>
     */
    public function format(CapturedException $capturedException): array
    {
        $exception = $capturedException->getException();
        Assert::isInstanceOf($exception, ViolationListException::class); // @phpstan-ignore staticMethod.alreadyNarrowedType

        $rule = $capturedException->getMatchedRule();
        $root = $rule->getRootObject();
        $propertyPath = $rule->getPropertyPath()->join('.');

        /** @var list<ConstraintViolationInterface> $violationList */
        $violationList = iterator_to_array($exception->getViolationList());

        if ([] === $violationList) {
            throw new LogicException('Violation list must not be empty');
        }

        return array_map(
            static fn (ConstraintViolationInterface $violation): ConstraintViolation => new ConstraintViolation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getParameters(),
                $root,
                $propertyPath,
                $violation->getInvalidValue(),
                $violation->getPlural(),
                $violation->getCode(),
                $violation->getConstraint(),
                $violation->getCause(),
            ),
            $violationList,
        );
    }
}
