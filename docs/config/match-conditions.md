# Match Conditions 🖇️

## Exception Class Condition

A bare minimum condition.

Matches the exception by its class name with `instanceof` check, \
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

Code that throws:

```php
if (!$this->isSubmissionPeriodOpen()) {
    // This will be linked to Command's id property:
    throw new OrderSubmissionPeriodClosedException();
}
```

```php
if (!$connection->ping()) {
    // This will not be linked to anything (e.g. it's 500):
    throw new DriverException('oops');
}
```

## If-Closure Condition

`#[Catch_]` attribute allows specifying a callback to determine \
whether instance of the exception is related to a given property or not.

For example, `CardDeactivatedException` could be related to `$depositToCardId` or `$withdrawFromCardId`:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

#[Try_]
class TransferMoneyCommand
{
    #[Catch_(CardDeactivatedException::class, if: [self::class, 'isWithdrawalCard'])]
    public int $withdrawFromCardId;

    #[Catch_(CardDeactivatedException::class, if: [self::class, 'isDepositCard'])]
    public int $depositToCardId;

    public function isWithdrawalCard(CardDeactivatedException $exception): bool
    {
        return $this->withdrawFromCardId === $exception->getCardId();
    }

    public function isDepositCard(CardDeactivatedException $exception): bool
    {
        return $this->depositToCardId === $exception->getCardId();
    }
}
```

Once `CardDeactivatedException`  is matched by class, custom closure is called:

- If `isWithdrawalCard()` callback returns `true`, the exception is matched for `withdrawalCardId` property.
- Otherwise, we analyse `depositCardId`, and if `isDepositCard()` callback returns `true`, \
  then the exception is matched for this property.

If neither of them returned `true`, the exception is considered "unmatched" and Matcher returns `null`.

## Origin Source Condition

Specifies whence the exception had to be raised from. \
Matches the exception by its origin class name and method name.

For example, `\InvalidArgumentException` is a generic one and could possibly originate from multiple places. \
If you want to catch it only if it belongs to `Uuid` class, specify `from:` clause:

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

Therefore, Exception Matcher will analyse `InvalidArgumentException`'s trace \
and only match it if it's originated from `Uuid::fromString()` method.

The origin may as well be a [property hook](https://www.php.net/manual/en/language.oop5.property-hooks.php) (PHP 8.4+). \
It is referenced the same way it appears in the exception trace: `$property::set` or `$property::get`:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionalMatcher\Validator\Formatter\Validator\validator_violations;

#[Try_]
class RenameProductCommand
{
    #[Catch_(ValidationFailedException::class, from: [Product::class, '$title::set'], format: validator_violations)]
    public string $title;
}
```

Here the exception is only matched when it originates from the `set` hook of the `Product::$title` property.

## Uid Condition

Matches Symfony's `Uid\Exception\InvalidArgumentException` by value comparison.

> This condition is registered only when `symfony/uid` is installed and exposes
> `Symfony\Component\Uid\Exception\InvalidArgumentException::$invalidValue`.

For example, `InvalidUidException` could be related to `$withdrawFromCardId` or `$depositToCardId`:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid\uid_value;

#[Try_]
class TransferMoneyCommand
{
    #[Catch_(InvalidUidException::class, match: uid_value)]
    public string $withdrawFromCardId;

    #[Catch_(InvalidUidException::class, match: uid_value)]
    public string $depositToCardId;
}
```

> Only string property values are allowed for this condition.

Thus, Matcher performs an additional property value comparison against `InvalidUidException::$invalidValue`:

- If the two are equal, the exception is linked to this property.
- Otherwise, other properties are analysed (if any).

If neither property is matched, the Matcher returns `null`.

## ValueException Condition

Since in most cases matching conditions come down to the simple value comparison, \
it's easier to make the exception implement `ValueException` interface and specify `match: exception_value` \
instead of implementing `if:` closure every time.

Thus, it's possible to avoid much of the boilerplate code, keeping it clean:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\exception_value;

#[Try_]
class TransferMoneyCommand
{
    #[Catch_(CardDeactivatedException::class, match: exception_value)]
    public int $withdrawalCardId;

    #[Catch_(CardDeactivatedException::class, match: exception_value)]
    public int $depositCardId;
}
```

In this example `CardDeactivatedException` could be linked either to `withdrawalCardId` or to `depositCardId`, \
depending on the value of `cardId` from the exception.

And `CardDeactivatedException` must implement `ValueException` interface:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ValueException;

class CardDeactivatedException extends RuntimeException implements ValueException
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

Matches Symfony's native `ValidationFailedException` similarly
to [ValueException Condition](#valueexception-condition), \
comparing a property's value against the value of the exception.

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

> Normally, you should format it with [`validator_violations`](./violation-formatters.md#validationfailedexception-formatter) to keep all contained violations.

## Enum Condition

Matches native `ValueError` thrown by `BackendEnum::from()` method.

Specify `enum_value` match condition to compare property's value against the invalid value of thrown exception:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\enum_value;

#[Try_]
class ImportScheduleCommand
{
    #[Catch_(\ValueError::class, from: WeekDay::class, match: enum_value, message: 'schedule.weekday.invalid')]
    public string $weekDay;
}
```

Enum class:

```php
enum WeekDay: string
{
    case MONDAY = 'monday';
    case TUESDAY = 'tuesday';
}
```

Code that throws:

```php
$command = new ImportScheduleCommand('thursday');

// ValueError thrown hence will match Command's weekDay
$weekDay = WeekDay::from($command->weekDay);

// ValueError thrown hence won't match anything
$weekDay = WeekDay::from('unrelated');
```

> Normally, you should specify `message:` with your custom message. \
> Otherwise, exception's system message will be exposed revealing details like this: \
> `"thursday" is not a valid backing value for enum App\WeekDay`
