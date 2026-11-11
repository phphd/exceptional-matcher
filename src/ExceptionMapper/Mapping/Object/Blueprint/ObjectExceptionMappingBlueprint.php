<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Blueprint;

use AppendIterator;
use Iterator;
use PhPhD\ExceptionMapper\Mapping\Blueprint\ExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Object\ObjectExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Composite\MemoizingIteratorAggregate;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @template T of object
 *
 * @implements ExceptionMappingBlueprint<ExceptionMappingNode,ObjectExceptionMappingNode>
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
    public function bind(?ExceptionMappingNode $embracingNode = null, ?object $object = null): ObjectExceptionMappingNode
    {
        Assert::notNull($object);
        Assert::isInstanceOf($object, $this->className); // @phpstan-ignore staticMethod.alreadyNarrowedType

        /** @var AppendIterator<int,ExceptionMappingNode,Iterator<int,ExceptionMappingNode>> $properties */
        $properties = new AppendIterator();

        $objectNode = new ObjectExceptionMappingNode($object, $embracingNode, new MemoizingIteratorAggregate($properties));

        $properties->append($this->bindProperties($objectNode));

        return $objectNode;
    }

    /** @return class-string<T> */
    public function getClassName(): string
    {
        return $this->className;
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
