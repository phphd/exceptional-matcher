<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation;

use Attribute;
use Exception;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Throwable;
use Webmozart\Assert\Assert;

use function is_array;
use function is_string;

/** @api */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class Capture
{
    public function __construct(
        /** @var class-string<Exception> */
        private readonly string $exception,
        private readonly ?string $message = null,
        /** @var null|class-string|array{class-string,string} The origin of the exception */
        private readonly null|array|string $from = null,
        /** @var null|class-string<MatchCondition>|non-empty-string */
        private readonly ?string $condition = null,
        /** @var ?array{object|class-string,string} */
        private readonly ?array $when = null,
        /** @var class-string<ExceptionViolationFormatter>|non-empty-string */
        private readonly string $formatter = 'default',
    ) {
        if (null !== $this->when) {
            Assert::count($this->when, 2);
        }

        if (is_array($this->from)) {
            Assert::count($this->from, 2);
        }
    }

    /** @return class-string<Throwable> */
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

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    /** @return ?array{object|class-string,string} */
    public function getWhen(): ?array
    {
        return $this->when;
    }

    public function getFormatter(): string
    {
        return $this->formatter;
    }
}
