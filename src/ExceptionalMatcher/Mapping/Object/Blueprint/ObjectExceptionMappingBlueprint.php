<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint;

use AppendIterator;
use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\ExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\ObjectExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\MemoizingIteratorAggregate;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @template T of object
 */
final class ObjectExceptionMappingBlueprint implements ExceptionMappingBlueprint
{
    public function __construct(
        /** @var class-string<T> */
        private readonly string $className,
        /** @var iterable<PropertyExceptionMappingBlueprint> */
        private readonly iterable $propertyPrints,
    ) {
    }

    /** @param ?T $object */
    public function bind(?ExceptionMappingNode $owner = null, object $object = null): ObjectExceptionMappingNode
    {
        Assert::notNull($object);
        Assert::isInstanceOf($object, $this->className);

        /** @var AppendIterator<int,ExceptionMappingNode,Iterator<int,ExceptionMappingNode>> $properties */
        $properties = new AppendIterator();

        $objectNode = new ObjectExceptionMappingNode($object, $owner, new MemoizingIteratorAggregate($properties));

        $properties->append($this->bindProperties($objectNode));

        return $objectNode;
    }

    /** @return iterable<PropertyExceptionMappingBlueprint> */
    public function getPropertyPrints(): iterable
    {
        return $this->propertyPrints;
    }

    /** @psalm-suppress UnusedForeachValue */
    public function hasPropertyPrints(): bool
    {
        /** @noinspection PhpLoopNeverIteratesInspection */
        foreach ($this->propertyPrints as $print) {
            return true;
        }

        return false;
    }

    /** @return Iterator<int,ExceptionMappingNode> */
    private function bindProperties(ObjectExceptionMappingNode $objectNode): Iterator
    {
        foreach ($this->propertyPrints as $propertyPrint) {
            yield $propertyPrint->bind($objectNode);
        }
    }
}
