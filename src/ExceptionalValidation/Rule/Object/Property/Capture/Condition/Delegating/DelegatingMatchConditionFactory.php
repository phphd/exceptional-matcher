<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Delegating;

use LogicException;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionValueMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionValueMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchConditionFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;

/** @internal */
final class DelegatingMatchConditionFactory implements MatchConditionFactory
{
    public function __construct(
        private readonly ContainerInterface $conditionFactoryRegistry,
    ) {
    }

    public static function create(?ContainerInterface $conditionFactoryRegistry = null): self
    {
        if (null !== $conditionFactoryRegistry) {
            return new self($conditionFactoryRegistry);
        }

        $conditionFactoryRegistry = new Container();

        $conditionFactoryRegistry->set(ExceptionValueMatchCondition::class, new ExceptionValueMatchConditionFactory());
        $conditionFactoryRegistry->set(ValidationFailedExceptionValueMatchCondition::class, new ValidationFailedExceptionValueMatchConditionFactory());

        return new self($conditionFactoryRegistry);
    }

    public function getCondition(Capture $capture, CaptureRule $parent): ?MatchCondition
    {
        $conditionFactoryId = $capture->getCondition();

        if (null === $conditionFactoryId) {
            return null;
        }

        if (!$this->conditionFactoryRegistry->has($conditionFactoryId)) {
            throw new LogicException('Condition factory not found: '.$conditionFactoryId);
        }

        /** @var MatchConditionFactory $conditionFactory */
        $conditionFactory = $this->conditionFactoryRegistry->get($conditionFactoryId);

        return $conditionFactory->getCondition($capture, $parent);
    }
}
