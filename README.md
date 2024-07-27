# PhdExceptionalValidationBundle

🧰 Provides exception-to-violation mapper bundled as [Symfony Messenger](https://symfony.com/doc/current/messenger.html)
middleware. It captures thrown exceptions, mapping them
into [Symfony Validator](https://symfony.com/doc/current/validation.html)
violations format based on message mapping attributes.

[![Build Status](https://img.shields.io/github/actions/workflow/status/phphd/exceptional-validation-bundle/ci.yaml?branch=main)](https://github.com/phphd/exceptional-validation-bundle/actions?query=branch%3Amain)
[![Codecov](https://codecov.io/gh/phphd/exceptional-validation-bundle/graph/badge.svg?token=GZRXWYT55Z)](https://codecov.io/gh/phphd/exceptional-validation-bundle)
[![Psalm coverage](https://shepherd.dev/github/phphd/exceptional-validation-bundle/coverage.svg)](https://shepherd.dev/github/phphd/exceptional-validation-bundle)
[![Psalm level](https://shepherd.dev/github/phphd/exceptional-validation-bundle/level.svg)](https://shepherd.dev/github/phphd/exceptional-validation-bundle)
[![Packagist Downloads](https://img.shields.io/packagist/dt/phphd/exceptional-validation-bundle.svg)](https://packagist.org/packages/phphd/exceptional-validation-bundle)
[![Licence](https://img.shields.io/badge/licence-Apache--2.0--with--CC-blue.svg)](https://github.com/phphd/exceptional-validation-bundle/blob/main/LICENSE)

## Installation 📥

1. Install via composer

    ```sh
    composer require phphd/exceptional-validation-bundle
    ```

2. Enable the bundle in the `bundles.php`

    ```php
    PhPhD\ExceptionalValidationBundle\PhdExceptionalValidationBundle::class => ['all' => true],
    ```

## Configuration ⚒️

The recommended way to use this package is via Symfony Messenger.

To leverage features of this bundle, you should add `phd_exceptional_validation` middleware to the list:

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

## Usage 🚀

The first thing necessary is to mark your message with `#[ExceptionalValidation]` attribute. It is used to include the
message for processing by the middleware.

Then you define `#[Capture]` attributes on the properties of the message. These attributes are used to specify mapping
for the thrown exceptions to the corresponding properties of the class with the respective error message translation.

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
mapped to the `login` or `password` property.

Eventually when `phd_exceptional_validation` middleware has processed the exception, it will
throw `ExceptionalValidationFailedException` so that it can be caught and processed as needed:

```php
$command = new RegisterUserCommand($login, $password);

try {
    $this->commandBus->dispatch($command);
} catch (ExceptionalValidationFailedException $exception) {
    $constraintViolationList = $exception->getViolations();

    return $this->render('registrationForm.html.twig', ['errors' => $constraintViolationList]);
} 
```

The `$exception` object enfolds constraint violations with respectively mapped error messages. This
violation list can be used for example to render errors into html-form or to serialize them for a json-response.

## Advanced usage ⚙️

`#[ExceptionalValidation]` and `#[Capture]` attributes allow you to implement very flexible mappings.
Here are just few examples of how you can use them.

### Exception mapping on nested objects

`#[ExceptionalValidation]` attribute works side-by-side with Symfony Validator `#[Valid]` attribute. Once you have
defined these, the `#[Capture]` attribute can be defined on the nested objects.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use Symfony\Component\Validator\Constraints\Valid;

#[ExceptionalValidation]
final class OrderProductCommand
{
    #[Valid]
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

### Capture Conditions

`#[Capture]` attribute accepts the callback function to determine whether particular exception instance should
be captured for the given property or not. It allows more dynamic exception handling scenarios:

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

    #[Capture(
        InsufficientFundsException::class, 
        'wallet.insufficient_funds',
    )]
    private int $unitAmount;

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

In this example, `when: ` option of the `#[Capture]` attribute is used to specify the callback functions that are called
when exception is processed. If `isWithdrawalCardBlocked` callback returns `true`, then exception is captured for
`withdrawalCardId` property; if `isDepositCardBlocked` callback returns `true`, then exception is captured for
`depositCardId` property. If neither of the callbacks return `true`, then exception is re-thrown upper in the stack.

### Exception mapping on nested arrays

You are perfectly allowed to map the violations for the nested array items given that you have `#[Valid]` attribute
on the iterable property. For example:

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

### Custom violation formatters

In some cases, you might need to customize the way the violations are formatted, such as passing additional
parameters to the message translation. You can achieve this by implementing your own violation formatter service that
implements `ExceptionViolationFormatter` interface:

```php
use PhPhD\ExceptionalValidation\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class RegistrationViolationsFormatter implements ExceptionViolationFormatter
{
    public function __construct(
        #[Autowire('@phd_exceptional_validation.violation_formatter.default')]
        private ExceptionViolationFormatter $formatter,
    ) {
    }

    public function formatViolation(CapturedException $capturedException): ConstraintViolationInterface
    {
        $violation = $this->formatter->formatViolation($capturedException);

        $exception = $capturedException->getException();

        if ($exception instanceof LoginAlreadyTakenException) {
            return new ConstraintViolation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                ['loginHolder' => $exception->getLoginHolder()],
                // ...
            );
        }

        if ($exception instanceof WeakPasswordException) {
            // ...
        }

        return $violation;
    }
}
```

In order for your custom violation formatter service to be recognized by this bundle, it must be tagged
with `exceptional_validation.violation_formatter` tag. If you
use [autoconfiguration](https://symfony.com/doc/current/service_container.html#the-autoconfigure-option), this is done
automatically by service container owing to the fact that `ExceptionViolationFormatter` interface is implemented.

Finally, custom formatter can be specified in the `#[Capture]` attribute:

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

In this example, `RegistrationViolationsFormatter` is used to format error messages for
both `LoginAlreadyTakenException` and `WeakPasswordException`, enriching constraint violations with additional
exception context.

## Limitations

### Capturing multiple exceptions at once

Typically, validation process is expected to capture all errors at once and return them as a list of violations.
However, the whole concept of exceptional processing in PHP is based on the idea that only one exception is thrown at a
time, since only one instruction is executed at a time.

In case of Symfony Messenger, it is partially overcome by the fact that `HandlerFailedException` can wrap multiple
exceptions collected from the underlying handlers. Though, currently there's no way to collect more than one
exception from the same handler because of the limitations of sequential computing model.

We are currently working on this issue and trying to implement a solution that will allow capturing multiple exceptions.
Most likely the solution will be based on some ideas from the system of interaction combinators, where code is no longer
considered as a sequence of instructions, but rather as a graph of interactions that are combined and reduced on each
step.
