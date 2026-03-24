# Violation Formatters 🎨

There are two main built-in violation formatters you can use: `DefaultExceptionViolationFormatter` and
`ViolationListExceptionFormatter`.

If needed, create a custom violation formatter as described below.

## Main

`MainExceptionViolationFormatter` is used by default if another formatter is not specified.

It provides a basic way of creating a `ConstraintViolation` with these parameters: \
`$root`, `$message`, `$propertyPath`, `$value`.

## Constraint Violation List Formatter

`ViolationListExceptionFormatter` allows formatting the exceptions \
that contain a `ConstraintViolationList` from the validator.

Such exceptions should implement `ViolationListException` interface:

```php
use PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationListException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class CardNumberValidationFailedException extends \RuntimeException implements ViolationListException
{
    public function __construct(
        private readonly string $cardNumber,
        private readonly ConstraintViolationListInterface $violationList,
    ) {
        parent::__construct('Card Number Validation Failed');
    }

    public function getViolationList(): ConstraintViolationListInterface
    {
        return $this->violationList;
    }
}
```

Then, specify `ViolationListExceptionFormatter` as a `format:` for the `#[Catch_]` attribute:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

use const PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\included_violations;

#[Try_]
class IssueCreditCardCommand
{
    #[Catch_(CardNumberValidationFailedException::class, format: included_violations)]
    private string $cardNumber;
}
```

Thus, once `cardNumber` property gets a hold of `CardNumberValidationFailedException`, \
formatter makes sure that a proper representation of this exception in a `ConstraintViolation` form is created for this property.

> If `#[Catch_]` attribute specified a message, \
> it would've been ignored in favour of `ConstraintViolationList` messages.


> Besides that, it's also possible to use `validator_violations` formatter, \
> which can format Symfony's native `ValidationFailedException`.

## Custom Violation Formatters 🎨🖌️

In some cases, you might want to customize the created violations. \
For example, pass additional parameters to the message translation.

You can create custom violation formatter by implementing `ExceptionViolationFormatter` interface:

```php
use PhPhD\ExceptionalMatcher\Exception\MatchedException;
use PhPhD\ExceptionalMatcher\Validator\Formatter\ExceptionViolationFormatter;
use Symfony\Component\Validator\ConstraintViolationInterface;

/** @implements ExceptionViolationFormatter<LoginAlreadyTakenException> */
final class LoginAlreadyTakenViolationFormatter implements ExceptionViolationFormatter
{
    public function __construct(
        #[Autowire(service: ExceptionViolationFormatter::class.'<Throwable>')]
        private ExceptionViolationFormatter $formatter,
    ) {
    }

    /** @return array{ConstraintViolationInterface} */
    public function format(MatchedException $matchedException): ConstraintViolationInterface
    {
        // format violation with the default formatter
        // and then adjust only the necessary parts
        [$violation] = $this->formatter->format($matchedException);

        /** @var LoginAlreadyTakenException $exception */
        $exception = $matchedException->getException();

        $violation = new ConstraintViolation(
            $violation->getMessage(),
            $violation->getMessageTemplate(),
            ['loginHolder' => $exception->getLoginHolder()],
            // ...
        );

        return [$violation];
    }
}
```

Then, register it as a service:

```yaml
services:
    App\Auth\User\Support\Validation\LoginAlreadyTakenViolationFormatter:
        autoconfigure: true
```

> In order for violation formatter to be recognized by the bundle, \
> its service must be tagged with `MatchedExceptionFormatter` class-name tag.
>
> If you are using [autoconfiguration](https://symfony.com/doc/current/service_container.html#the-autoconfigure-option),
> this will be done automatically by the service container, \
> owing to the fact that `MatchedExceptionFormatter` interface is implemented.

Finally, specify formatter in the `#[Catch_]` attribute:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

#[Try_]
final class RegisterUserCommand
{
    #[Catch_(LoginAlreadyTakenException::class, format: LoginAlreadyTakenViolationFormatter::class)]
    private string $login;

    #[Catch_(WeakPasswordException::class, format: WeakPasswordViolationFormatter::class)]
    private string $password;
}
```

In this example, `LoginAlreadyTakenViolationFormatter` formats constraint violation for `LoginAlreadyTakenException`, \
while `WeakPasswordViolationFormatter` formats `WeakPasswordException`.
