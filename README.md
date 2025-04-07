# Exceptional Validation

🧰 Transform domain exceptions into validation errors.

This library bridges the gap between your business logic and user interface by _capturing domain exceptions_ and
converting them into ordered validation errors.

With Exceptional Validation, you don't have to duplicate validation rules in your application/ui layers,
since it enables you _automatically map any violations to the relevant form fields_.

[![Build Status](https://img.shields.io/github/actions/workflow/status/phphd/exceptional-validation/ci.yaml?branch=main)](https://github.com/phphd/exceptional-validation/actions?query=branch%3Amain)
[![Codecov](https://codecov.io/gh/phphd/exceptional-validation/graph/badge.svg?token=GZRXWYT55Z)](https://codecov.io/gh/phphd/exceptional-validation)
[![Psalm coverage](https://shepherd.dev/github/phphd/exceptional-validation/coverage.svg)](https://shepherd.dev/github/phphd/exceptional-validation)
[![Psalm level](https://shepherd.dev/github/phphd/exceptional-validation/level.svg)](https://shepherd.dev/github/phphd/exceptional-validation)
[![Packagist Downloads](https://img.shields.io/packagist/dt/phphd/exceptional-validation.svg)](https://packagist.org/packages/phphd/exceptional-validation)
[![Licence](https://img.shields.io/github/license/phphd/exceptional-validation.svg)](https://github.com/phphd/exceptional-validation/blob/main/LICENSE)

It primarily works as a [Command Bus](https://symfony.com/doc/current/messenger.html#multiple-buses-command-event-buses)
middleware that intercepts exceptions, maps them to the relevant form properties, and then formats them as
standard [SF Validator](https://symfony.com/doc/current/validation.html) violations. Besides that, `ExceptionHandler` is
also available for direct use, since you can use `@phd_exceptional_validation.exception_handler` service directly.

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

## Configuration ⚒️

The recommended way to use this package is via Symfony Messenger middleware.

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

Once you have done this, middleware will take care of capturing exceptions and processing them.

> If you are not using Messenger component, you can still leverage features of this package, by writing your own
> implementation of the middleware for the specific command bus you are using. Concerning `symfony/messenger`
> component, this dependency is optional, so it won't be installed automatically if you don't need it.

## Usage 🚀

The first thing necessary is to mark your message with `#[ExceptionalValidation]` attribute. It is used to include the
message for processing by the middleware.

Then you define `#[Capture]` attributes on the properties of the message. These are used to map thrown exceptions to
the corresponding properties of the class:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
final class RegisterUserCommand
{
    #[Capture(LoginAlreadyTakenException::class, 'auth.login.already_taken')]
    private string $login;

    #[Capture(WeakPasswordException::class, 'auth.password.weak')]
    private string $password;
}
```

In this example, whenever `LoginAlreadyTakenException` or `WeakPasswordException` is thrown, it will be captured and
mapped to the `login` or `password` property with the respective error message translation.

> Please, note that by default `validators` messages translation domain is used, as it is inherited from
> `%validator.translation_domain%` parameter. If you would like to change it, you could either change this, or
> specifically set `%phd_exceptional_validation.translation_domain%`.

Eventually when `phd_exceptional_validation` middleware has processed the exception, it will
throw `ExceptionalValidationFailedException` so that it can be caught and processed as needed:

```php
$command = new RegisterUserCommand($login, $password);

try {
    $this->commandBus->dispatch($command);
} catch (ExceptionalValidationFailedException $exception) {
    $violationList = $exception->getViolationList();

    return $this->render('registrationForm.html.twig', ['errors' => $violationList]);
} 
```

The `$exception` object enfolds constraint violations with the respectively mapped constraint violations. This
violation list can be used for example to render errors into html-form or to serialize them into a json-response.

## Advanced usage ⚙️

`#[ExceptionalValidation]` and `#[Capture]` attributes allow you to implement very flexible mappings.
Here are just few examples of how you can use them.

### Capturing exceptions on nested objects

`#[ExceptionalValidation]` attribute works side-by-side with Symfony Validator `#[Valid]` attribute. Once you have
defined these, `#[Capture]` attribute can be specified on the nested objects.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use Symfony\Component\Validator\Constraints as Assert;

#[ExceptionalValidation]
final class OrderProductCommand
{
    #[Assert\Valid]
    private ProductDetails $product;
}

#[ExceptionalValidation]
final class ProductDetails
{
    private int $id;

    #[Capture(InsufficientStockException::class, 'order.insufficient_stock')]
    private string $quantity;

    // ...
}
```

In this example, whenever `InsufficientStockException` is thrown, it will be captured and mapped to the
`product.quantity` property with the corresponding message translation.

### Capture When-Conditions

`#[Capture]` attribute accepts the callback function to determine whether particular exception instance should
be captured for a given property or not.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
final class TransferMoneyCommand
{
    #[Capture(
        BlockedCardException::class,
        'wallet.blocked_card',
        when: [self::class, 'isWithdrawalCardBlocked'],
    )]
    private int $withdrawalCardId;

    #[Capture(
        BlockedCardException::class,
        'wallet.blocked_card',
        when: [self::class, 'isDepositCardBlocked'],
    )]
    private int $depositCardId;

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

In this example, `when:` option of the `#[Capture]` attribute is used to specify the callback functions that are called
when exception is processed. If `isWithdrawalCardBlocked` callback returns `true`, then exception is captured for
`withdrawalCardId` property; otherwise if `isDepositCardBlocked` callback returns `true`, then exception is captured for
`depositCardId` property. If neither of them return `true`, then exception is re-thrown upper in the stack.

### Capture Value-Conditions

Since in most cases capture conditions come down to the simple value comparison, it's easier to make your exception
implement `ValueException` interface and specify `condition: ExceptionValueMatchCondition::class` rather than
implementing `when:` closure every time.

> You can also match Symfony's native `ValidationFailedException` with `ValidationFailedExceptionValueMatchCondition`.

This way, it's possible to avoid much of boilerplate code, keeping it clean:

```php
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition;

#[ExceptionalValidation]
final class TransferMoneyCommand
{
    #[Capture(BlockedCardException::class, 'wallet.blocked_card', condition: ExceptionValueMatchCondition::class)]
    private int $withdrawalCardId;

    #[Capture(BlockedCardException::class, 'wallet.blocked_card', condition: ExceptionValueMatchCondition::class)]
    private int $depositCardId;
}
```

Following this `BlockedCardException` should implement `ValueException` interface:

```php
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ValueException;

final class BlockedCardException extends DomainException implements ValueException
{
    public function __construct(
        private Card $card,
    ) {
        parent::__construct();
    }

    public function getValue(): int
    {
        return $this->card->getId();    
    }
}
```

In this example `BlockedCardException` could be captured either for `withdrawalCardId` or `depositCardId` properties
depending on the `cardId` value from the exception.

### Capturing exceptions on nested array items

It is perfectly allowed to map the violations for the nested array items given that you have defined `#[Valid]`
attribute on the iterable property. For example:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use Symfony\Component\Validator\Constraints as Assert;

#[ExceptionalValidation]
final class CreateOrderCommand
{
    /** @var ProductDetails[] */
    #[Assert\Valid]
    private array $products;
}

#[ExceptionalValidation]
final class ProductDetails
{
    private int $id;

    #[Capture(
        InsufficientStockException::class, 
        'order.insufficient_stock', 
        when: [self::class, 'isStockExceptionForThisProduct'],
    )]
    private string $quantity;

    public function isStockExceptionForThisProduct(InsufficientStockException $exception): bool
    {
        return $exception->getProductId() === $this->id;
    }
}
```

In this example, when `InsufficientStockException` is captured, it will be mapped to the `products[*].quantity`
property, where `*` stands for the index of the particular `ProductDetails` instance from the `products` array on which
the exception was captured.

### Capturing multiple exceptions

Typically, during the validation process, it is expected that all validation errors will be shown to the user and not
just the first one.

Yet, due to the limitations of the sequential computing model, only one exception could be thrown at a
time. This leads to the situation where only the first exception is thrown, while the rest are not even reached.

This limitation could still be overcome by implementing some of the concepts of interaction combinators model in
sequential PHP environment. The key concept is to use semi-parallel execution flow instead of sequential.

Let's consider the example of user registration and `RegisterUserCommand` with `login` and `password` properties shown
above, where we want to capture both `LoginAlreadyTakenException` and `WeakPasswordException` at the same time.

In the main code we must collect these exceptions into some kind of "composite exception" that will eventually
be thrown. While one could've implemented this manually, it's much easier to use `amphp/amp` library, where it has been
implemented in a lot better way using async Futures:

```php
/**
 * @var Login $login 
 * @var Password $password 
 */
[$login, $password] = awaitAnyN([
    // validate and create Login instance
    async($this->createLogin(...), $command->getLogin()),
    // validate and create Password instance
    async($this->createPassword(...), $command->getPassword()),
]);
```

In this example, `createLogin()` method could throw `LoginAlreadyTakenException` and `createPassword()` method could
throw `WeakPasswordException`. By using `async` and `awaitAnyN` functions, we are able to leverage semi-parallel
execution flow instead of sequential. Therefore, both `createLogin()` and `createPassword()` methods will get executed
regardless of thrown exceptions.

If no exceptions were thrown, then `$login` and `$password` variables will be populated with the respective return
values from the Futures. But if there indeed were some exceptions, then `Amp\CompositeException` will be thrown with all
wrapped exceptions inside.

> If you would like to use custom composite exception, read
> about [ExceptionUnwrapper](https://github.com/phphd/exception-toolkit?tab=readme-ov-file#exception-unwrapper)

Since current library is capable of processing composite exceptions (actually there are un-wrappers for Amp and
Messenger exceptions), all our thrown exceptions will be processed and user will have the full stack of validation
errors at hand.

### Violation formatters

There are two built-in violation formatters that you can use - `DefaultViolationFormatter`
and `ViolationListExceptionFormatter`. If needed, you can create your own custom violation formatter as described below.

#### Default

`DefaultViolationFormatter` is used by default if other formatter is not specified.

It provides a very basic way to format violations, building `ConstraintViolation` with such parameters
as: `$message`, `$root`, `$propertyPath`, `$value`.

#### Constraint Violation List Formatter

`ViolationListExceptionFormatter` is used to format violations for the exceptions that
implement `ViolationListException`
interface. It allows to easily capture the exception that has `ConstraintViolationList` obtained from the validator.

> You can also format Symfony's native `ValidationFailedException` with `ValidationFailedExceptionFormatter`.

The typical exception class implementing `ViolationListException` interface would look like this:

```php
use PhPhD\ExceptionalValidation\Formatter\Item\ViolationList\ViolationListException;
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
use PhPhD\ExceptionalValidation\Formatter\Item\ViolationList\ViolationListExceptionFormatter;

#[ExceptionalValidation]
final class IssueCreditCardCommand
{
    #[Capture(
        exception: CardNumberValidationFailedException::class, 
        formatter: ViolationListExceptionFormatter::class,
    )]
    private string $cardNumber;
}
```

In this example, `CardNumberValidationFailedException` is captured on the `cardNumber` property and all the constraint
violations from this exception are mapped to this property. If there's message specified on the `#[Capture]` attribute,
it is ignored in favor of the messages from `ConstraintViolationList`.

#### Custom violation formatters

In some cases, you might want to customize the violations, such as passing additional parameters to the message
translation. You can create your own violation formatter by implementing `ExceptionViolationFormatter` interface:

```php
use PhPhD\ExceptionalValidation\Formatter\Item\ExceptionViolationFormatter;
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
        // and then adjust only necessary parts
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

To upgrade project to the latest version of `exceptional-validation`,
you should add the following line to your `rector.php` file:

```php
return static function (RectorConfig $rectorConfig): void {
    // Upgrading from the version 1.4 to the latest version
    $rectorConfig->sets(ExceptionalValidationSetList::fromVersion('1.4')->getSetList());
}
```

Make sure to specify your current version of library so that upgrade sets will be matched correctly.
