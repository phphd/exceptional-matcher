# Exceptional Validation 🏹

🧰 Transform Domain Exceptions Into Validation Errors

[![Build Status](https://img.shields.io/github/actions/workflow/status/phphd/exceptional-validation/ci.yaml?branch=main)](https://github.com/phphd/exceptional-validation/actions?query=branch%3Amain)
[![Codecov](https://codecov.io/gh/phphd/exceptional-validation/graph/badge.svg?token=GZRXWYT55Z)](https://codecov.io/gh/phphd/exceptional-validation)
[![Psalm coverage](https://shepherd.dev/github/phphd/exceptional-validation/coverage.svg)](https://shepherd.dev/github/phphd/exceptional-validation)
[![Psalm level](https://shepherd.dev/github/phphd/exceptional-validation/level.svg)](https://shepherd.dev/github/phphd/exceptional-validation)
[![Packagist Downloads](https://img.shields.io/packagist/dt/phphd/exceptional-validation.svg)](https://packagist.org/packages/phphd/exceptional-validation)
[![Licence](https://img.shields.io/github/license/phphd/exceptional-validation.svg)](https://github.com/phphd/exceptional-validation/blob/main/LICENSE)

Exceptional Validation bridges your domain validation exceptions with the user interface by _capturing business
exceptions_ and converting them into ordered validation errors. You don't have to run duplicate validation in your
application/ui layers, nor even create custom validators, since you can _declaratively map the exceptions to
their relevant form fields_ by the means of this library instead.

## Another Validation Library? 🤔

No, it's not a validation library and never intended to be. It doesn't provide any validation rules, validators,
constraints whatsoever. Instead, it is more of an exception handling library that formats exceptions in the validator
format.

Your domain validation logic could be implemented with any kind of third-party library, or even plain PHP, while
Exceptional Validation will provide an easy way to accurately map validation exceptions to the particular properties
they relate to.

Even though it's not a strict requirement, it's recommended to use Symfony Validator as the main validation tool,
since this library integrates it quite well.

## Why Exceptional Validation? ✨

Ordinarily, validation flows through two different layers - one at the HTTP/form level and another
within domain objects - leading to duplication and potential inconsistencies.

The traditional approach usually makes high use of attribute-based validation, which strips down the domain layer from
most
business logic it must've implemented on its own. Also, we don't have any other way to get a nice message on the form,
but to create a custom validator for every special check we need. This way, the domain model ends up naked, since all
business rules have leaked elsewhere.

On the other hand, there's a common practice in DDD that domain objects should be responsible for their own validation
rules. `Email` value object validates its own format by itself, and it naturally throws an exception that represents
validation failure. `RegisterUserService` normally verifies that there's no duplicate user in the system and naturally
throws an exception. That is the kind of code that consummately expresses the model of the business, and therefore it
should not be stripped down.

Yet, with this domain-driven approach, it's a good question how to make these validation errors get shown to the user?
In order for us to be able to return a neat Frontend response with `email` as a property path, it's necessary to match
`EmailAlreadyTakenException` with `$email` property of the original `RegisterUserCommand`.

That's exactly what Exceptional Validation is intended to do.

By capturing exceptions like `EmailValidationFailedException` and mapping them to their particular form fields as
`$email`, you maintain a single source of truth for domain validation logic. Your domain enforces its invariants through
value objects and services, while this library ensures that any validation failures will appear properly in your forms
and API responses.

This approach:

- Eliminates duplicate validation code across HTTP/application and domain layers;
- Keeps business rules where they belong - in the domain;
- Makes validation logic easily unit-testable;
- Simplifies complex nested validation scenarios;
- Eliminates the need for validation groups.

## How does it work? ⚙️

Primarily it works as a [Command Bus](https://symfony.com/doc/current/messenger.html#multiple-buses-command-event-buses)
middleware that intercepts exceptions, uses exception mapper to map them to the relevant form properties, and then
formats captured exceptions as standard [SF Validator](https://symfony.com/doc/current/validation.html) violations.

> Besides that, `ExceptionMapper` is also available for direct use w/o any middleware. You can
> reference it as `@phd_exceptional_validation.exception_mapper.validator` service.

![Exceptional Validation.svg](https://raw.githubusercontent.com/phphd/exceptional-validation/refs/heads/main/assets/Exceptional%20Validation.svg)

## Installation 📥

1. Install via composer

    ```sh
    composer require phphd/exceptional-validation
    ```

2. Enable the bundles in the `bundles.php`

    ```php
    PhPhD\ExceptionalValidation\Bundle\PhdExceptionalValidationBundle::class => ['all' => true],
    PhPhD\ExceptionToolkit\Bundle\PhdExceptionToolkitBundle::class => ['all' => true],
    ```

   > Note: The PhdExceptionToolkitBundle is a required dependency that provides exception unwrapping functionality used
   by this library.

## Configuration 🔧

The recommended way to use this package is
via [Symfony Messenger Middleware](https://symfony.com/doc/current/messenger.html#middleware).

To start off, you should add `phd_exceptional_validation` middleware to the list:

```diff
framework:
    messenger:
        buses:
            command.bus:
                middleware:
                    - validation
+                   - phd_exceptional_validation
                    - doctrine_transaction
```

Once you have done this, the middleware will take care of capturing exceptions and processing them.

> If you are not using `Messenger` component, you can still leverage features of this package, since it gives you a
> rigorously structured set of tools w/o depending on any particular implementation. Since `symfony/messenger` component
> is optional, it won't be installed automatically if you don't need it.

## Quick Start 🎯

First off, mark your message with `#[ExceptionalValidation]` attribute, as it is used by mapper to include the
object for processing.

Then you can define exceptions to the properties mapping using `#[Capture]` attributes.
They declaratively describe what exceptions should match to what properties under what conditions.

The basic example looks like this:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
class RegisterUserCommand
{
    #[Capture(LoginAlreadyTakenException::class, 'auth.login.already_taken')]
    public string $login;

    #[Capture(WeakPasswordException::class, 'auth.password.weak')]
    public string $password;
}
```

In this example we say that whenever `LoginAlreadyTakenException` is thrown, it will be matched with `login` property,
resulting in created `ConstraintViolation` object with `login` as a property path, and `auth.login.already_taken` as a
message.
The same comes to `WeakPasswordException` at `password` property path as well.

> Please note that by default messages translation domain is `validators`, since it is inherited from
> `validator.translation_domain` parameter. You can change it by setting `phd_exceptional_validation.translation_domain`
> parameter.

Finally, when `phd_exceptional_validation` middleware processes the exception, it throws
`ExceptionalValidationFailedException` so that client code can catch it and process as needed:

```php
$command = new RegisterUserCommand($login, $password);

try {
    $this->commandBus->dispatch($command);
} catch (ExceptionalValidationFailedException $exception) {
    $violationList = $exception->getViolationList();

    return $this->render('registrationForm.html.twig', ['errors' => $violationList]);
} 
```

Exception object contains both message and respectively mapped `ConstraintViolationList`.
This violation list can be used, for example, to render errors into html-form or to serialize them into a json-response.

### How is it different from the standard validation? ⚖️

You might be wondering why we would not just use simple validation asserts right in the command?

Let's see it with the same `RegisterUserCommand` example above.
The traditional validation approach for the same rules would look something like this:

```php
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

class RegisterUserCommand
{
    #[AppAssert\UniqueLogin]
    public string $login;

    #[Assert\PasswordStrength(minScore: 2)]
    public string $password;
}
```

The main difference between the two is that standard validation runs before your actual business logic. This alone
means that for every domain-specific rule like "login must be unique" it's necessary to create a custom
validation constraint and a validator to implement this business logic. Thereby domain leaks into validators.
That code, which you would've normally implemented in the service, you have to implement in the validator.

One more point is that oftentimes multiple actions duplicate subset of validations. For example, password reset
action normally validates password in the same way as registration action, usually resulting in validation asserts being
duplicated between the two, while this business logic should've belonged to `Password` concept, properly represented as
a value object, being used in both actions.

With exceptional validation, you just retroactively map violations dictated by the domain. Herewith business logic
has already worked out, and all you have to do is display its result to the end user. This gives a lot of flexibility,
removing the need for custom validators, validation groups, and allowing you to keep the domain code in
the domain objects, resulting in overall improvement of the design of the system.

Thus, you focus on the domain and let the library take care of the exception presentation:

```php
// RegisterUserService

if ($this->userRepository->loginExists($command->login)) {
    throw new LoginAlreadyTakenException($command->login);
}
```

## Features 📖

`#[ExceptionalValidation]` and `#[Capture]` attributes allow you to implement very flexible mappings.
Here are examples of how you can use them.

### Capture Conditions

#### Exception Class Condition

A minimum required condition. Matches the exception by its class name using `instanceof` operator, making it
similar to `catch` block.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
class PublishMessageCommand
{
    #[Capture(MessageNotFoundException::class)]
    public string $messageId;
}
```

#### Origin Place Condition

Besides filtering by exception class, it's possible to filter by the origin class name and method name where the
exception was raised from.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use Symfony\Component\Uid\Uuid;

#[ExceptionalValidation]
class ConfirmPackageCommand
{
    #[Capture(\InvalidArgumentException::class, from: [Uuid::class, 'fromString'])]
    public string $uid;
}
```

In this example `InvalidArgumentException` is generic, and it can originate from multiple places.
To catch the exceptions that particularly belong to `Uuid` class, specify `from:` clause with class / method names.

Exception mapper will analyse exception trace and check whether it originated from the place specified. 

#### When-Closure Condition

`#[Capture]` attribute allows to specify `when:` argument with a callback function to be used to determine whether
particular instance of the exception should be captured for a given property or not. This is particularly useful when
the same exception could be originated from multiple places:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
class TransferMoneyCommand
{
    #[Capture(BlockedCardException::class, when: [self::class, 'isWithdrawalCardBlocked'])]
    public int $withdrawalCardId;

    #[Capture(BlockedCardException::class, when: [self::class, 'isDepositCardBlocked'])]
    public int $depositCardId;

    public function isWithdrawalCardBlocked(BlockedCardException $exception): bool
    {
        return $exception->getCardId() === $this->withdrawalCardId;
    }

    public function isDepositCardBlocked(BlockedCardException $exception): bool
    {
        return $exception->getCardId() === $this->depositCardId;
    }
}
```

In this example, once we've matched `BlockedCardException` by class, custom closure is called.

If `isWithdrawalCardBlocked()` callback returns `true`, then exception is captured for `withdrawalCardId` property.

Otherwise, we analyse `depositCardId`, and if `isDepositCardBlocked()` callback returns `true`, then the exception is
captured on this property.

If neither of them returned `true`, then exception is re-thrown upper in the stack.

#### ValueException Condition

Since in most cases capture conditions come down to the simple value comparison, it's easier to make the exception
implement `ValueException` interface and specify `condition: ExceptionValueMatchCondition::class` instead of
implementing `when:` closure every time.

This way it's possible to avoid much of the boilerplate code, keeping it clean:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition;

#[ExceptionalValidation]
class TransferMoneyCommand
{
    #[Capture(BlockedCardException::class, condition: ExceptionValueMatchCondition::class)]
    public int $withdrawalCardId;

    #[Capture(BlockedCardException::class, condition: ExceptionValueMatchCondition::class)]
    public int $depositCardId;
}
```

In this example `BlockedCardException` could be captured either to `withdrawalCardId` or `depositCardId`, depending on
the `cardId` value from the exception.

And `BlockedCardException` itself must implement `ValueException` interface:

```php
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ValueException;

class BlockedCardException extends DomainException implements ValueException
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

#### ValidationFailedException Condition

This is very similar to ValueException Condition with the difference that it integrates Symfony's native
`ValidationFailedException`.

You can specify `ValidationFailedExceptionValueMatchCondition` to match validation exception based on the value:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionValueMatchCondition;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[ExceptionalValidation]
class RegisterUserCommand
{
    #[Capture(ValidationFailedException::class, from: Password::class, condition: ValidationFailedExceptionValueMatchCondition::class)]
    public string $password;
}
```

### Capturing for nested structures

`#[ExceptionalValidation]` attribute works side-by-side with Symfony Validator's `#[Valid]` attribute. Once you
define `#[Valid]` on an object/iterable property, the mapper will pick it up for the nested exception mapping analysis,
providing a respective property path for the created violations.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use Symfony\Component\Validator\Constraints as Assert;

#[ExceptionalValidation]
class CreateOrderCommand
{
    /** @var OrderItemDto[] */
    #[Assert\Valid]
    public array $items;
}

#[ExceptionalValidation]
class OrderItemDto
{
    public int $productId;

    #[Capture(InsufficientStockException::class, when: [self::class, 'isStockExceptionForThisItem'])]
    public string $quantity;

    public function isStockExceptionForThisItem(InsufficientStockException $exception): bool
    {
        return $exception->getProductId() === $this->productId;
    }
}
```

In this example, every time exception is processed, it will also be matched with inner objects from `items` property,
until it finally arrives at `items[*].quantity` (`*` stands for the particular array item index) property, being matched
by `InsufficientStockException` class name, and custom closure condition that makes sure that it was this particular
`OrderItemDto` that caused the exception.

The resulting property path of the caught violation includes all intermediary items, starting from the root of the tree,
proceeding down to the leaf item, where the exception was actually caught.

### Capturing multiple exceptions

Typically, validation is expected to return all present violations at once (not just the first one) so they can be shown
to the user.

Though due to the limitations of the sequential computation model, only one instruction can be executed at a time, and
therefore, only one exception can be thrown at a time. This leads to a situation where validation ends up in only the
first exception being thrown, while the rest are not even reached.

For example, if we consider user registration with `RegisterUserCommand` from the code above, we'd like to capture both
`LoginAlreadyTakenException` and `WeakPasswordException` at once, so that the user can fix all the form errors at once,
rather than sorting them out one by one.

This limitation can be overcome by implementing some concepts from an Interaction Calculus model in a sequential PHP
environment. The key idea is to use a semi-parallel execution flow instead of a purely sequential.

In practice, if validation is split into multiple functions, each of which may throw an exception, the concept can be
implemented by calling them one by one and collecting any exceptions as they raise. If there were any, they are wrapped
into a composite exception that is eventually thrown.

Fortunately, you don't need to implement this manually, since `amphp/amp` library already provides a more efficient
solution than one you'd likely write yourself, using async Futures:

```php
/**
 * @var Login $login 
 * @var Password $password 
 */
[$login, $password] = await([
    // validate and create an instance of Login
    async($this->createLogin(...), $command->getLogin()),
    // validate and create an instance of Password
    async($this->createPassword(...), $command->getPassword()),
]);
```

In this example, `createLogin()` method could throw `LoginAlreadyTakenException` and `createPassword()` method could
throw `WeakPasswordException`.

By using `async` and `awaitAnyN` functions, we are leveraging semi-parallel execution flow instead of sequential, so
that both `createLogin()` and `createPassword()` methods are executed regardless of thrown exceptions.

If no exceptions were thrown, then `$login` and `$password` variables are populated with the respective return
values. But if there were indeed some exceptions then `Amp\CompositeException` will be thrown with all the wrapped
exceptions inside.

> If you would like to use a custom composite exception, make sure to read
> about [ExceptionUnwrapper](https://github.com/phphd/exception-toolkit?tab=readme-ov-file#exception-unwrapper)

Since the library is capable of processing composite exceptions (with unwrappers for Amp and Messenger exceptions), all
of our thrown exceptions will be processed, and the user will get the complete stack of validation errors at hand.

### Violation formatters

There are two built-in violation formatters that you can use - `DefaultViolationFormatter`
and `ViolationListExceptionFormatter`. If needed, you can create your own custom violation formatter as described below.

#### Default

`DefaultViolationFormatter` is used by default if another formatter is not specified.

It provides a very basic way to format violations, building `ConstraintViolation` with these parameters: `$message`,
`$root`, `$propertyPath`, `$value`.

#### Constraint Violation List Formatter

`ViolationListExceptionFormatter` is used to format violations for the exceptions that implement
`ViolationListException` interface. It allows you to easily capture the exception that has a `ConstraintViolationList`
obtained from the validator.

> You can also format Symfony's native `ValidationFailedException` with `ValidationFailedExceptionFormatter`.

The typical exception class implementing `ViolationListException` interface would look like this:

```php
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ViolationList\ViolationListException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class CardNumberValidationFailedException extends \RuntimeException implements ViolationListException
{
    public function __construct(
        private readonly string $cardNumber,
        private readonly ConstraintViolationListInterface $violationList,
    ) {
        parent::__construct((string)$this->violationList);
    }

    public function getViolationList(): ConstraintViolationListInterface
    {
        return $this->violationList;
    }
}
```

Then you can use `ViolationListExceptionFormatter` on the `#[Capture]` attribute of the property:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ViolationList\ViolationListExceptionFormatter;

#[ExceptionalValidation]
class IssueCreditCardCommand
{
    #[Capture(
        exception: CardNumberValidationFailedException::class, 
        formatter: ViolationListExceptionFormatter::class,
    )]
    private string $cardNumber;
}
```

In this example, `CardNumberValidationFailedException` is captured on the `cardNumber` property and all the constraint
violations from this exception are mapped to this property. If there's a message specified on the `#[Capture]`
attribute, it is ignored in favor of the messages from `ConstraintViolationList`.

#### Custom violation formatters

In some cases, you might want to customize the violations, such as passing additional parameters to the message
translation. You can create your own violation formatter by implementing `ExceptionViolationFormatter` interface:

```php
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class RegistrationViolationsFormatter implements ExceptionViolationFormatter
{
    public function __construct(
        #[Autowire('@phd_exceptional_validation.violation_formatter.default')]
        private ExceptionViolationFormatter $defaultFormatter,
    ) {
    }

    /** @return array{ConstraintViolationInterface} */
    public function format(CapturedException $capturedException): ConstraintViolationInterface
    {
        // format violation with the default formatter
        // and then adjust only the necessary parts
        [$violation] = $this->defaultFormatter->format($capturedException);

        $exception = $capturedException->getException();

        if ($exception instanceof LoginAlreadyTakenException) {
            $violation = new ConstraintViolation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                ['loginHolder' => $exception->getLoginHolder()],
                // ...
            );
        }

        if ($exception instanceof WeakPasswordException) {
            // ...
        }

        return [$violation];
    }
}
```

Then you should register your custom formatter as a service:

```yaml
services:
    App\AuthBundle\ViolationFormatter\RegistrationViolationsFormatter:
        tags: [ 'exceptional_validation.violation_formatter' ]
```

> In order for your custom violation formatter to be recognized by this bundle, its service must be tagged
> with `exceptional_validation.violation_formatter` tag. If you
> use [autoconfiguration](https://symfony.com/doc/current/service_container.html#the-autoconfigure-option), this is done
> automatically by the service container owing to the fact that `ExceptionViolationFormatter` interface is implemented.

Finally, your custom formatter should be specified in the `#[Capture]` attribute:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
final class RegisterUserCommand
{
    #[Capture(
        LoginAlreadyTakenException::class, 
        'auth.login.already_taken', 
        formatter: RegistrationViolationsFormatter::class,
    )]
    private string $login;

    #[Capture(
        WeakPasswordException::class, 
        'auth.password.weak', 
        formatter: RegistrationViolationsFormatter::class,
    )]
    private string $password;
}
```

In this example, `RegistrationViolationsFormatter` is used to format constraint violations for
both `LoginAlreadyTakenException` and `WeakPasswordException` (though you are perfectly fine to use separate
formatters), enriching them with additional context.

## Upgrading

Project comes with `ExceptionalValidationSetList` class that containing rules for automatic upgrade.

To upgrade a project to the latest version of `exceptional-validation`,
you should add the following line to your `rector.php` file:

```php
return RectorConfig::configure()
    ->withPaths([ __DIR__ . '/src'])
    ->withImportNames(removeUnusedImports: true)
    // Upgrading from the version 1.4 to the latest version
    ->withSets(ExceptionalValidationSetList::fromVersion('1.4')->getSetList());
```

Make sure to specify your current version of the library so that upgrade sets will be matched correctly.

You should also check [UPGRADE.md](UPGRADE.md) file for additional upgrade instructions and breaking changes.
