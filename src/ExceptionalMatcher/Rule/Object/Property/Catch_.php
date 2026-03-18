<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property;

use Attribute;
use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Validator\Formatter\Main\MainExceptionViolationFormatter;
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
     * @phpstan-param ?class-string<MatchCondition<T1>> $match
     * @phpstan-param class-string<MatchedExceptionFormatter<T2,mixed>> $formatter
     *
     * @psalm-param ?class-string<MatchCondition> $match
     * @psalm-param class-string<MatchedExceptionFormatter> $formatter
     */
    public function __construct(
        /** @var class-string<T1&T2> */
        private readonly string $exception,
        private readonly ?string $message = null,
        /** @var null|class-string|array{class-string,non-empty-string} The origin of the exception */
        private readonly array|string|null $from = null,
        /** @note match condition class is contravariant to the exception */
        private readonly ?string $match = null,
        /** @var ?array{object|class-string,string} */
        private readonly ?array $if = null,
        /** @note formatter class is contravariant to the exception */
        private readonly string $formatter = MainExceptionViolationFormatter::class, // @phpstan-ignore phpat.testModelDependencies (really, this's a fair catch - it should not depend on the formatter)
    ) {
        if (null !== $this->if) {
            Assert::count($this->if, 2);
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
    public function getMatch(): ?string
    {
        return $this->match;
    }

    /** @return ?array{object|class-string,string} */
    public function getIf(): ?array
    {
        return $this->if;
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
