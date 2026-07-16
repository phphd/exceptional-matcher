<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub;

use DateTimeImmutable;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;

#[Try_]
final class TypedPropertiesMessage
{
    private int $scalarValue;

    private FinalItem $finalItem;

    private DateTimeImmutable $internalItem;

    private ExtensibleItem $extensibleItem;

    private ItemInterface $interfaceItem;

    private ExtensibleItem|FinalItem $unionItem;

    private array $arrayItems;

    private PlannedItem $plannedItem;

    #[Catch_(PlanStubException::class, message: 'oops')]
    private string $caughtValue;
}
