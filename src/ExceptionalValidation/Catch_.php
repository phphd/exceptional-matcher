<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation;

use Attribute;
use PhPhD\ExceptionalValidation\Matcher\Validator\Formatter\Main\MainExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\MatchedExceptionFormatter;
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
final class Catch_
{
    /**
     * @phpstan-param ?class-string<MatchCondition<T1>> $condition
     * @phpstan-param class-string<MatchedExceptionFormatter<T2,mixed>> $formatter
     *
     * @psalm-param ?class-string<MatchCondition> $condition
     * @psalm-param class-string<MatchedExceptionFormatter> $formatter
     */
    public function __construct(
        /** @var class-string<T1&T2> */
        private readonly string $exception,
        private readonly ?string $message = null,
        /** @var null|class-string|array{class-string,non-empty-string} The origin of the exception */
        private readonly array|string|null $from = null,
        /** @note condition type is contravariant to the exception */
        private readonly ?string $condition = null,
        /** @var ?array{object|class-string,string} */
        private readonly ?array $when = null,
        /** @note formatter type is contravariant to the exception */
        private readonly string $formatter = MainExceptionViolationFormatter::class,
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

    /** @return ?array{class-string,?non-empty-string} */
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
     * @phpstan-return class-string<MatchedExceptionFormatter<T2,mixed>>
     *
     * @psalm-return class-string<MatchedExceptionFormatter>
     */
    public function getFormatter(): string
    {
        return $this->formatter;
    }
}
