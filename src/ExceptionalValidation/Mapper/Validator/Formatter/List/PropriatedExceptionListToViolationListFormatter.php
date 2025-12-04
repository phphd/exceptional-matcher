<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List;

use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Throwable;

use function array_merge;

/** @internal */
final readonly class PropriatedExceptionListToViolationListFormatter implements PropriatedExceptionListFormatter
{
    /** @api */
    public function __construct(
        /** @var ExceptionViolationFormatter<Throwable> */
        private ExceptionViolationFormatter $violationFormatter,
    ) {
    }

    /** @param non-empty-list<PropriatedException<Throwable>> $propriatedExceptionList */
    public function format(array $propriatedExceptionList): ConstraintViolationList
    {
        /** @var list<list<ConstraintViolationInterface>> $violations */
        $violations = [];

        foreach ($propriatedExceptionList as $propriatedException) {
            $violations[] = $this->violationFormatter->format($propriatedException);
        }

        return new ConstraintViolationList(array_merge(...$violations));
    }
}
