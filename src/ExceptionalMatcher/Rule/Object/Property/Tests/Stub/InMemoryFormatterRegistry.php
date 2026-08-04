<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub;

use LogicException;
use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Main\MainExceptionViolationFormatter;
use Psr\Container\ContainerInterface;

use function in_array;

/**
 * Answers the only question the mapping compiler asks of the formatter registry: is this id registered?
 *
 * @implements ContainerInterface<class-string<MatchedExceptionFormatter>,MatchedExceptionFormatter>
 */
final class InMemoryFormatterRegistry implements ContainerInterface
{
    public function __construct(
        /** @var list<class-string> */
        private readonly array $formatterIds = [MainExceptionViolationFormatter::class],
    ) {
    }

    public function get(string $id): MatchedExceptionFormatter
    {
        throw new LogicException('Compiling a mapping plan never resolves the formatter itself.');
    }

    public function has(string $id): bool
    {
        return in_array($id, $this->formatterIds, true);
    }
}
