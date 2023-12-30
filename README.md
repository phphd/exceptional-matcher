# PhdExceptionalValidationBundle

🧰 Provides [Symfony Messenger](https://symfony.com/doc/current/messenger.html) middleware to capture any thrown
exception and map it into [Symfony Validator](https://symfony.com/doc/current/validation.html) violations format in
accordance with message property path.

[![Build Status](https://img.shields.io/github/actions/workflow/status/phphd/exceptional-validation-bundle/ci.yaml?branch=main)](https://github.com/phphd/exceptional-validation-bundle/actions?query=branch%3Amain)
[![Codecov](https://codecov.io/gh/phphd/exceptional-validation-bundle/graph/badge.svg?token=GZRXWYT55Z)](https://codecov.io/gh/phphd/exceptional-validation-bundle)
[![Psalm coverage](https://shepherd.dev/github/phphd/exceptional-validation-bundle/coverage.svg)](https://shepherd.dev/github/phphd/exceptional-validation-bundle)
[![Psalm level](https://shepherd.dev/github/phphd/exceptional-validation-bundle/level.svg)](https://shepherd.dev/github/phphd/exceptional-validation-bundle)
[![Licence](https://img.shields.io/badge/licence-Apache--2.0%2FCommons--Clause-blue.svg)](https://github.com/phphd/exceptional-validation-bundle/blob/main/LICENSE)
[![Packagist Downloads](https://img.shields.io/packagist/dt/phphd/exceptional-validation-bundle.svg)](https://packagist.org/packages/phphd/exceptional-validation-bundle)

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

In addition to standard symfony validator constraints, certain properties also have `#[Capture]` attributes. These
attributes specify the particular exception class to be intercepted and the corresponding validation message to be shown
when this exception occurs.

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
messages. This errors list may be used in various ways such as displaying on an HTML page, formatting them into a JSON
response, logging into file, rethrowing different exception, or any other specific requirement you might have.
