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

The exception, mapped by the command we be intercepted:

```php
if (!$this->isSubmissionPeriodOpen()) {
    // This exception will be linked to Command's id property:
    throw new OrderSubmissionPeriodClosedException();
}
```

On the other hand, any unrelated exceptions will be re-thrown (not matched):

```php
if (!$connection->ping()) {
    // This exception will not be linked to command (i.e. it's 500):
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
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;use PhPhD\ExceptionalMatcher\Rule\Object\Try_;use Symfony\Component\Validator\Exception\ValidationFailedException;use const PhPhD\ExceptionalMatcher\Validator\Formatter\Embedded\embedded_violations;

#[Try_]
class RenameProductCommand
{
    #[Catch_(ValidationFailedException::class, from: [Product::class, '$title::set'], format: embedded_violations)]
    public string $title;
}
```

Here the exception is only matched when it originates from the `set` hook of the `Product::$title` property.

```php
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    /** The title is always valid, being validated right off */
    #[ORM\Column]
    public string $title {
        set => Validation::createCallable(
            new Assert\NotBlank(),
            new Assert\Length(min: 4, max: 255),
        )($value);
    }
}
```

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
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;use PhPhD\ExceptionalMatcher\Rule\Object\Try_;use Symfony\Component\Validator\Exception\ValidationFailedException;use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Validator\validated_value;use const PhPhD\ExceptionalMatcher\Validator\Formatter\Embedded\embedded_violations;

#[Try_]
class RegisterUserCommand
{
    #[Catch_(ValidationFailedException::class, from: Password::class, match: validated_value, format: embedded_violations)]
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
$command = new ImportScheduleCommand('fifthday');

$weekDay = WeekDay::from($command->weekDay); // This ValueError will match Command's weekDay

$weekDay = WeekDay::from('unrelated'); // This ValueError won't match anything
```

> Normally, you should specify `message:` with your custom message. \
> Otherwise, exception's system message will be exposed revealing details like this: \
> `"fifthday" is not a valid backing value for enum App\WeekDay`

## Custom Conditions 📝

When the relation between an exception and a property cannot be reasonably expressed with built-in conditions, \
you can create a custom condition, and reference it with `match:`.

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use App\Identity\Role\Exception\ConflictingRolesException;

use const App\Identity\Role\Validation\disqualified_roles;

#[Try_]
class GrantRolesCommand
{
    /** @var list<string> */
    #[Catch_(ConflictingRolesException::class, match: disqualified_roles)]
    public array $roleIds;
}
```

The domain logic of granting roles could throw `ConflictingRolesException` with the list of disqualifying roles. \
The exception belongs to the property when every disqualified role is among the requested `$roleIds` —
a subset check no built-in condition expresses.

A custom condition requires three pieces:

| Piece     | Interface                 | Responsibility                                                                                         |
|-----------|---------------------------|--------------------------------------------------------------------------------------------------------|
| Condition | `MatchCondition`          | decides whether the exception matches:<br> `matches($exception): bool`                                 |
| Blueprint | `MatchConditionBlueprint` | a compiled blueprint of the condition;<br> applying it to the property produces the Condition          |
| Compiler  | `MatchConditionCompiler`  | compiles the `#[Catch_]` declaration into a blueprint,<br> validating the declaration at the same time |

So, in our example, the condition itself:

```php
use App\Identity\Role\Exception\ConflictingRolesException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use Throwable;

/** @implements MatchCondition<ConflictingRolesException> */
final class DisqualifiedRolesMatchCondition implements MatchCondition
{
    public function __construct(
        /** @var list<string> */
        private readonly array $roleIds,
    ) {
    }

    /** @param ConflictingRolesException $exception */
    public function matches(Throwable $exception): bool
    {
        // every disqualified role is among the requested $roleIds
        return [] === array_diff($exception->getDisqualifiedRoleIds(), $this->roleIds);
    }
}
```

Then the compiler and the blueprint to create the condition (both implemented in one class, as the blueprint is stateless):

```php
use App\Identity\Role\Exception\ConflictingRolesException;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Bool\FalseCondition;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/** Configuration to use in `match: disqualified_roles` */
const disqualified_roles = DisqualifiedRolesMatchCondition::class;

/**
 * @implements MatchConditionCompiler<ConflictingRolesException>
 * @implements MatchConditionBlueprint<ConflictingRolesException>
 */
#[AutoconfigureTag(MatchConditionCompiler::class, ['id' => disqualified_roles])]
final class DisqualifiedRolesMatchConditionCompiler implements MatchConditionCompiler, MatchConditionBlueprint
{
    /** @return MatchConditionBlueprint<ConflictingRolesException> */
    public function compile(Catch_ $catch): MatchConditionBlueprint
    {
        if (!is_a($catch->getExceptionClass(), ConflictingRolesException::class, true)) {
            throw new LogicException('DisqualifiedRolesMatchCondition can only be used for '.ConflictingRolesException::class);
        }

        return $this;
    }

    /** @return MatchCondition<ConflictingRolesException> */
    public function bind(MatchingRule $rule): MatchCondition
    {
        /** @var list<string> $roleIds */
        $roleIds = $rule->getValue();

        // If no roles, nothing could conflict
        if ([] === $roleIds) {
            /** @psalm-var FalseCondition<ConflictingRolesException> */
            return new FalseCondition();
        }

        return new DisqualifiedRolesMatchCondition($roleIds);
    }
}
```

It has two parts:

- `compile()` — validates the **declaration** (the `Catch_` attribute), throwing `LogicException` on anything wrong.

  > Validating only in `compile()` is what lets a mapping be checked without running any code.

- `bind()` — read the **runtime context** via `MatchingRule` (`getValue()`, `getEnclosingObject()`, etc.).
  > If you need no property value for the condition, you can return the condition \
  > right from the `compile()` method by wrapping it into `new PreCompiledMatchConditionBlueprint()`.

Register the compiler service, so it's available for `match:` to resolve:

```yaml
services:
    App\Identity\Role\Validation\DisqualifiedRolesMatchConditionCompiler:
        autoconfigure: true
```

> For compiler to be recognized by the bundle, \
> its service must be tagged with `MatchConditionCompiler` class-name tag.
>
> To do it, you can use `#[AutoconfigureTag(MatchConditionCompiler::class, ['id' => your_condition_id])]` \
> along with [autoconfiguration](https://symfony.com/doc/current/service_container.html#the-autoconfigure-option), as shown above.


Finally, the condition is ready:

```php
#[Try_]
class GrantRolesCommand
{
    /** @var list<string> */
    #[Catch_(ConflictingRolesException::class, match: disqualified_roles)]
    public array $roleIds;
}
```

It will be combined (logical AND) with any other clauses of the `#[Catch_]` in this order: \
the `exception:` class check, `from:` origin, your `match:` condition, then `if:` condition.
