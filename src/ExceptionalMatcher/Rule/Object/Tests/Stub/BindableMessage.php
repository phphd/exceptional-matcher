<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;

#[Try_]
final class BindableMessage
{
    #[Catch_(PlanStubException::class, message: 'oops')]
    private ?string $caughtValue = null;

    private PlannedItem $uninitializedItem;

    private ?PlannedItem $nestedItem = null;

    /** @var array<array-key,PlannedItem> */
    private array $listItems = [];

    public static function create(): self
    {
        return new self();
    }

    public function withNestedItem(PlannedItem $nestedItem): self
    {
        $message = clone $this;
        $message->nestedItem = $nestedItem;

        return $message;
    }

    /** @param array<array-key,PlannedItem> $listItems */
    public function withListItems(array $listItems): self
    {
        $message = clone $this;
        $message->listItems = $listItems;

        return $message;
    }
}
