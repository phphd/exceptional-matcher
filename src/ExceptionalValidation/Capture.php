<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation;

use Attribute;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default\DefaultExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Throwable;
use Webmozart\Assert\Assert;

use function is_array;
use function is_string;

/**
 * @api
 *
 * @template T1 of Throwable
 * @template T2 of Throwable (redundant, but can't be omitted due to {@see https://github.com/phpstan/phpstan/issues/13875})
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final readonly class Capture
{
    /**
     * @phpstan-param ?class-string<MatchCondition<T1>> $condition
     * @phpstan-param class-string<ExceptionViolationFormatter<T2>> $formatter
     *
     * @psalm-param ?class-string<MatchCondition> $condition
     * @psalm-param class-string<ExceptionViolationFormatter> $formatter
     */
    public function __construct(
        /** @var class-string<T1&T2> */
        private string $exception,
        private ?string $message = null,
        /** @var null|class-string|array{class-string,string} The origin of the exception */
        private array|string|null $from = null,
        /** @note condition type is contravariant to the exception */
        private ?string $condition = null,
        /** @var ?array{object|class-string,string} */
        private ?array $when = null,
        /** @note formatter type is contravariant to the exception */
        private string $formatter = DefaultExceptionViolationFormatter::class,
    ) {
        if (null !== $this->when) {
            Assert::count($this->when, 2);
        }

        if (is_array($this->from)) {
            Assert::count($this->from, 2);
        }
    }

    /** @return class-string<T1&T2> */
    public function getExceptionClass(): string
    {
        return $this->exception;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    /** @return ?array{class-string,?string} */
    public function getFrom(): ?array
    {
        if (is_string($this->from)) {
            return [$this->from, null];
        }

        return $this->from;
    }

    /**
     * @phpstan-return ?class-string<MatchCondition<T1>>
     *
     * @psalm-return ?class-string<MatchCondition>
     */
    public function getCondition(): ?string
    {
        return $this->condition;
    }

    /** @return ?array{object|class-string,string} */
    public function getWhen(): ?array
    {
        return $this->when;
    }

    /**
     * @phpstan-return class-string<ExceptionViolationFormatter<T2>>
     *
     * @psalm-return class-string<ExceptionViolationFormatter>
     */
    public function getFormatter(): string
    {
        return $this->formatter;
    }
}
