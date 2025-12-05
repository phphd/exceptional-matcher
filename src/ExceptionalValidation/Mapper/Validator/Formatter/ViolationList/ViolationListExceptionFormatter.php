<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ViolationList;

use LogicException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
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
     * @param PropriatedException<ViolationListException> $propriatedException
     *
     * @return non-empty-list<ConstraintViolation>
     */
    public function format(PropriatedException $propriatedException): array
    {
        $exception = $propriatedException->getException();
        Assert::isInstanceOf($exception, ViolationListException::class); // @phpstan-ignore staticMethod.alreadyNarrowedType

        $rule = $propriatedException->getMatchedRule();
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
