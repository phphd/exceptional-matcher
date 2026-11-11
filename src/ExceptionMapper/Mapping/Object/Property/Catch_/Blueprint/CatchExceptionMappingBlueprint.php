<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint;

use PhPhD\ExceptionMapper\Mapping\Blueprint\ExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\CatchExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\PropertyExceptionMappingNode;
use Throwable;

/**
 * @internal
 *
 * @template TException of Throwable
 *
 * @implements ExceptionMappingBlueprint<PropertyExceptionMappingNode,CatchExceptionMappingNode<TException>>
 */
final class CatchExceptionMappingBlueprint implements ExceptionMappingBlueprint
{
    public function __construct(
        /** @var MatchConditionBlueprint<TException> */
        private readonly MatchConditionBlueprint $conditionPrint,
        /** @var ?class-string<MatchedExceptionFormatter<TException,mixed>> */
        private readonly ?string $formatterId,
        private readonly ?string $messageTemplate,
    ) {
    }

    /**
     * @param PropertyExceptionMappingNode $embracingNode
     *
     * @return CatchExceptionMappingNode<TException>
     */
    public function bind(ExceptionMappingNode $embracingNode): CatchExceptionMappingNode
    {
        return new CatchExceptionMappingNode(
            $embracingNode,
            $this->conditionPrint->bind($embracingNode),
            $this->formatterId,
            $this->messageTemplate,
        );
    }

    /** @return MatchConditionBlueprint<TException> */
    public function getConditionPrint(): MatchConditionBlueprint
    {
        return $this->conditionPrint;
    }
}
