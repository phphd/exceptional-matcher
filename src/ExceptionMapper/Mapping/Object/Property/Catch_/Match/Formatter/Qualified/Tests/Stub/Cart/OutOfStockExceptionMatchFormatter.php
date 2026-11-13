<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Cart;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionMatch;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

use function strtr;

const out_of_stock = OutOfStockExceptionMatchFormatter::class;

/** @implements ExceptionMatchFormatter<OutOfStockProductException,ConstraintViolationInterface> */
final class OutOfStockExceptionMatchFormatter implements ExceptionMatchFormatter
{
    /** @api */
    public function __construct(
        /** @var ExceptionMatchFormatter<Throwable,ConstraintViolationInterface> */
        private readonly ExceptionMatchFormatter $formatter,
    ) {
    }

    /** @return array{ConstraintViolation} */
    public function format(ExceptionMatch $exceptionMatch): array
    {
        [$violation] = $this->formatter->format($exceptionMatch);

        /** @var OutOfStockProductException $exception */
        $exception = $exceptionMatch->getException();

        /** @var AddProductToCartCommand $command */
        $command = $exceptionMatch->getNode()->getLeafObject();

        $shortage = $command->quantity - $exception->getAvailableQuantity();

        $parameters = [
            '{{ available }}' => (string)$exception->getAvailableQuantity(),
            '{{ shortage }}' => (string)$shortage,
        ];

        $messageTemplate = 1 === $shortage
            ? 'Only {{ available }} left in stock. Reduce the quantity by {{ shortage }} item.'
            : 'Only {{ available }} left in stock. Reduce the quantity by {{ shortage }} items.';

        return [
            new ConstraintViolation(
                strtr($messageTemplate, $parameters),
                $messageTemplate,
                $parameters,
                $violation->getRoot(),
                $violation->getPropertyPath(),
                $violation->getInvalidValue(),
                $shortage,
            ),
        ];
    }
}
