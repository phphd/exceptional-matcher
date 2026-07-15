# Violation Formatters 🎨

Violation Formatters are used to represent the exception in a desired format.

There are two built-in violation formatters you can use:
- `MainExceptionViolationFormatter`;
- `ViolationsEmbeddedExceptionFormatter`.

If needed, you can create a custom violation formatter as described below.

## Main

`MainExceptionViolationFormatter` is used by default if another formatter is not specified.

It creates a basic `ConstraintViolation` with these parameters: \
`$root`, `$message`, `$propertyPath`, `$value`.

> The default messages translation domain is `validators`, \
> inherited from `validator.translation_domain` parameter.
>
> You can change it by setting `phd_exceptional_matcher.translation_domain` parameter.

## Embedded Violations Formatter

Allows the retrieval of prebuilt `ConstraintViolationList` directly from the exception.

The exception must implement `ViolationsEmbeddedException`, embedding `ConstraintViolationList` from the validator:

```php
use PhPhD\ExceptionalMatcher\Validator\Formatter\Embedded\ViolationsEmbeddedException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class CardNumberValidationFailedException extends \RuntimeException implements ViolationsEmbeddedException
{
    public function __construct(
        private readonly string $cardNumber,
        private readonly ConstraintViolationListInterface $violations,
    ) {
        parent::__construct('Card Number Validation Failed');
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
```

Finally, the DTO must specify `format: embedded_violations` for the `#[Catch_]` attribute:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

use const PhPhD\ExceptionalMatcher\Validator\Formatter\Embedded\embedded_violations;

#[Try_]
class IssueCreditCardCommand
{
    #[Catch_(CardNumberValidationFailedException::class, format: embedded_violations)]
    private string $cardNumber;
}
```

> If `#[Catch_]` attribute specified a message, \
> it would be ignored since `ConstraintViolationList` is used directly.

Thus, the resulting violation(s) for `cardNumber` property will be retrieved from
`CardNumberValidationFailedException::getViolations()` rather than created afresh for the exception itself.

### ValidationFailedException Formatter

The same `embedded_violations` formatter integrates Symfony's
`Symfony\Component\Validator\Exception\ValidationFailedException` similarly to how it
does [ViolationsEmbeddedException](#embedded-violations-formatter) -
picking up the embedded `ConstraintViolationList`.

Specify `format: embedded_violations` for the `#[Catch_]` attribute:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Validator\validated_value;
use const PhPhD\ExceptionalMatcher\Validator\Formatter\Embedded\embedded_violations;

#[Try_]
class RegisterUserCommand
{
    #[Catch_(ValidationFailedException::class, from: [User::class, '$login::set'], match: validated_value, format: embedded_violations)]
    public string $login;
}
```

> Normally, you should match it with [`validated_value`](match-conditions.md#validationfailedexception-condition)
> condition to prevent collisions.

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

> For violation formatter to be recognized by the bundle, \
> its service must be tagged with `MatchedExceptionFormatter` class-name tag.
>
> If you're using [autoconfiguration](https://symfony.com/doc/current/service_container.html#the-autoconfigure-option),
> it will be done automatically by the service container.

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
