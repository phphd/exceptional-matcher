# Match Conditions 🖇️

## Exception Class Condition

A bare minimum condition.

Matches the exception by its class name using `instanceof` check, \
acting similarly to `catch` operation.

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

#[Try_]
class SubmitOrderCommand
{
    #[Catch_(OrderSubmissionPeriodClosedException::class)]
    public string $id;
}
```

## Origin Source Condition

Filters the exception by its origin place, \
specifying whence it was to be raised from (class name and method name).

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use Symfony\Component\Uid\Uuid;

#[Try_]
class ConfirmParcelDeliveryCommand
{
    #[Catch_(\InvalidArgumentException::class, from: [Uuid::class, 'fromString'])]
    public string $uid;
}
```

In this example `InvalidArgumentException` is a generic one, possibly originating from multiple places. \
If you want to catch only those that belong to `Uuid` class, specify `from:` clause with class and method name.

Therefore, Exception Matcher will analyse the exception trace \
and check whether the exception was originated from that origin `from:` place.

## When-Closure Condition

`#[Catch_]` attribute allows to specify `if:` argument with a callback function to be used to determine \
whether particular instance of the exception should be matched with a given property or not. \
This is particularly useful when the same exception could be originated from multiple places:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

#[Try_]
class TransferMoneyCommand
{
    #[Catch_(CardBlockedException::class, if: [self::class, 'isWithdrawalCard'])]
    public int $withdrawFromCardId;

    #[Catch_(CardBlockedException::class, if: [self::class, 'isDepositCard'])]
    public int $depositToCardId;

    public function isWithdrawalCard(CardBlockedException $exception): bool
    {
        return $this->withdrawFromCardId === $exception->getCardId();
    }

    public function isDepositCard(CardBlockedException $exception): bool
    {
        return $this->depositToCardId === $exception->getCardId();
    }
}
```

In this example, once we've matched `CardBlockedException` by class, custom closure is called.

If `isWithdrawalCardBlocked()` callback returns `true`, the exception is matched for `withdrawalCardId` property.

Otherwise, we analyse `depositCardId`, and if `isDepositCardBlocked()` callback returns `true`, \
then the exception is matched for this property.

If neither of them returned `true`, then exception is re-thrown upper in the stack.

## Uid Condition

You can match Symfony's `InvalidArgumentException` from the `Uid` component
using `InvalidUidExceptionMatchCondition`:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid\uid_value;

#[Try_]
class ApproveVerificationCommand
{
    #[Catch_(InvalidUidException::class, match: uid_value)]
    public string $id;
}
```

This condition compares exception's `invalidValue` with the property value. \
If they are equal, the exception is matched for this property, otherwise other properties are analysed (if any).

Only string property values are allowed for this condition.

> This condition is registered only when `symfony/uid` is installed and exposes
> `Symfony\Component\Uid\Exception\InvalidArgumentException::$invalidValue`.

## ValueException Condition

Since in most cases matching conditions come down to the simple value comparison, it's easier to make the exception
implement `ValueException` interface and specify `match: ExceptionValueMatchCondition::class` instead of
implementing `if:` closure every time.

This way it's possible to avoid much of the boilerplate code, keeping it clean:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\exception_value;

#[Try_]
class TransferMoneyCommand
{
    #[Catch_(CardBlockedException::class, match: exception_value)]
    public int $withdrawalCardId;

    #[Catch_(CardBlockedException::class, match: exception_value)]
    public int $depositCardId;
}
```

In this example `CardBlockedException` could be matched either with `withdrawalCardId` or with `depositCardId`, \
depending on the `cardId` value from the exception.

And `CardBlockedException` itself must implement `ValueException` interface:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ValueException;

class CardBlockedException extends DomainException implements ValueException
{
    public function __construct(private Card $card) 
    {
        parent::__construct('card.blocked');
    }

    public function getValue(): int
    {
        return $this->card->getId();    
    }
}
```

## ValidationFailedException Condition

This one is very similar to `ValueException` condition \
with the difference that it integrates Symfony's native `ValidationFailedException`.

Specify `validated_value` match condition to compare property's value against exception's validated value:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Validator\validated_value;
use const PhPhD\ExceptionalMatcher\Validator\Formatter\Validator\validator_violations;

#[Try_]
class RegisterUserCommand
{
    #[Catch_(ValidationFailedException::class, from: Password::class, match: validated_value, format: validator_violations)]
    public string $password;
}
```
