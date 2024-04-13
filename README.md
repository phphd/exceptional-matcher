# PhdExceptionalValidationBundle

🧰 Provides [Symfony Messenger](https://symfony.com/doc/current/messenger.html) middleware allowing to capture any thrown
exception and map it into [Symfony Validator](https://symfony.com/doc/current/validation.html) violations format in
accordance with message property path.

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

When the exception is thrown from the handler, the message that is mapped by `#[ExceptionalValidation]` attribute is
analyzed for `#[Capture]` properties attributes. If a mapping defines this exception, it will be processed.

Here is an example of mapped message:

```php
use PhPhD\ExceptionalValidation;

#[ExceptionalValidation]
final readonly class CreateVacationRequestCommand
{
    public function __construct(
        public Employee $employee,
        
        #[ExceptionalValidation\Capture(VacationTypeNotFoundException::class, 'vacation.type_not_found')]
        public int $vacationTypeId,
        
        #[Assert\DateTime]
        public string $startDate,

        #[Assert\DateTime]
        #[ExceptionalValidation\Capture(InsufficientVacationBalanceException::class, 'vacation.insufficient_balance')]
        public string $endDate,
    ) {
    }
}
```

As you can see, certain properties have `#[Capture]` attributes defined. These specify the specific exception class to
be intercepted and the corresponding validation message to be shown when the exception occurs.

Finally, when the exception has been captured, `ExceptionalValidationFailedException` is thrown:

```php
$message = new CreateVacationRequestCommand($user, $vacationTypeId, $startDate, $endDate);

try {
    $this->commandBus->dispatch($message);
} catch (ExceptionalValidationFailedException $exception) {
    // Is thrown when handler failed with VacationTypeNotFoundException or InsufficientVacationBalanceException

    return $this->render('vacationForm.html.twig', ['errors' => $exception->getViolations()]);
} 
```

As you can see in the example above, `$exception` object has constraint violation list with respectively mapped error
messages. This error list may be used in various ways such as displaying on an HTML page, formatting into a JSON
response, logging into file, rethrowing different exception, or any other specific requirement you might have.

## Advanced usage ⚙️

The `ExceptionalValidation` and `Capture` attributes can be used in more complex scenarios to provide robust error
handling and validation for your application. Here's an example of how you can use these attributes for advanced use
cases.

### Capturing Multiple Exceptions

You can capture multiple exceptions for a single property by adding multiple `Capture` attributes. Each `Capture`
attribute can specify a different exception class and validation message.

```php
use PhPhD\ExceptionalValidation;

#[ExceptionalValidation]
final class AdvancedMessage
{
    #[ExceptionalValidation\Capture(FirstException::class, 'first_error')]
    #[ExceptionalValidation\Capture(SecondException::class, 'second_error')]
    private string $property;
}
```

In this example, if `FirstException` or `SecondException` is thrown, it will be captured and mapped to the property with
the corresponding validation message.

### Nested Exception Handling

The `Capture` attribute can also be used on nested objects to handle exceptions at different levels of your object
hierarchy.

```php
use PhPhD\ExceptionalValidation;
use Symfony\Component\Validator\Constraints\Valid;

#[ExceptionalValidation]
final class ParentMessage
{
    #[Valid]
    private NestedMessage $nestedMessage;
}

#[ExceptionalValidation]
final class NestedMessage
{
    #[ExceptionalValidation\Capture(NestedException::class, 'nested_error')]
    private string $nestedProperty;
}
```

In this example, if `NestedException` is thrown, it will be captured and mapped to the `nestedProperty` of the
`NestedMessage` object. Hence, violation property path would be `nestedMessage.nestedProperty`.

Besides that, you can also apply validation rules for nested iterable objects. Here's an example:

```php
use PhPhD\ExceptionalValidation;
use Symfony\Component\Validator\Constraints\Valid;

#[ExceptionalValidation]
final class ParentMessage
{
    #[Valid]
    private array $nestedItems;
}

#[ExceptionalValidation]
final class NestedItem
{
    #[ExceptionalValidation\Capture(NestedItemException::class, 'nested_item_error')]
    private string $itemProperty;
}
```

In this example, if `NestedItemException` is thrown, it will be captured and mapped to the `itemProperty` of
the `NestedItem` object. Hence, violation property path would be `nestedItems[*].itemProperty`, where `*` stands for
index.

### Conditional Exception Capturing with Callbacks

The `Capture` attribute can also accept a callback function that determines whether the exception should be
captured or not. This allows for more complex and dynamic exception handling scenarios.

Here's an example:

```php
use PhPhD\ExceptionalValidation;

#[ExceptionalValidation]
final class ConditionalMessage
{
    #[ExceptionalValidation\Capture(ConditionallyCapturedException::class, 'oops', when: [self::class, 'firstPropertyMatchesException'])]
    private int $firstProperty;

    #[ExceptionalValidation\Capture(ConditionallyCapturedException::class, 'oops', when: [self::class, 'secondPropertyMatchesException'])]
    private int $secondProperty;

    public function firstPropertyMatchesException(ConditionallyCapturedException $exception): bool
    {
        return $exception->getConditionValue() === $this->firstProperty;
    }

    public function secondPropertyMatchesException(ConditionallyCapturedException $exception): bool
    {
        return $exception->getConditionValue() === $this->secondProperty;
    }
}
```

In this example the `when` option of the `Capture` attribute specifies a callback
function (`firstPropertyMatchesException` and `secondPropertyMatchesException`) that is called when the exception is
processed. If the callback returns `true`, the exception is captured; if it returns `false`, it is not captured.
