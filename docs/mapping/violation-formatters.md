# Violation Formatters 🎨

Violation Formatters represent the exception in a desired output format.

If needed, create a [tailored formatter](#tailored-formatters-) to your format.

## Symfony

Built-in violation formatters:
- [`ExceptionMatchToViolationFormatter`](#default) (default);
- [`ViolationsEmbeddedExceptionFormatter`](#embedded-violations-formatter).

### Main

`MainExceptionViolationFormatter` is used if another violation formatter is not specified.

It creates a basic `ConstraintViolation` with these parameters:
- `$root` - the given object;
- `$message` - translated message (`#[Catch`-message or exception message);
- `$propertyPath` - property path from the root to the matched property;
- `$value` - property value.

> The default messages translation domain is `validators`, \
> inherited from `validator.translation_domain` parameter.
>
> You can change it by setting `phd_exception_mapper.translation_domain` parameter.

### Embedded Violations Formatter

Retrieves a prebuilt `ConstraintViolationList` straight from exception.

The exception is either an implementation of `ViolationsEmbeddedException`, or a child of [`ValidationFailedException`](#validationfailedexception-formatter), embedding a `ConstraintViolationList` received from the validator:

```php
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Embedded\ViolationsEmbeddedException;
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

Finally, the DTO must qualify `format: embedded_violations` for the `#[Catch_]` attribute:

```php
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Embedded\embedded_violations;

#[Try_]
class IssueCreditCardCommand
{
    #[Catch_(CardNumberValidationFailedException::class, format: embedded_violations)]
    private string $cardNumber;
}
```

> If `#[Catch_]` attribute specified a message, \
> it would be ignored since `ConstraintViolationList` is used directly.

Thus, the resulting violation(s) for `cardNumber` property are retrieved from
`CardNumberValidationFailedException::getViolations()` rather than created from the exception itself.

#### ValidationFailedException Formatter

The same `embedded_violations` formatter integrates Symfony's
`Symfony\Component\Validator\Exception\ValidationFailedException` the same way as
[ViolationsEmbeddedException](#embedded-violations-formatter) does it -
it picks up the `ConstraintViolationList` embedded in it.

Specify `format: embedded_violations` for the `#[Catch_]` attribute:

```php
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Symfony\Validator\validated_value;
use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Embedded\embedded_violations;

#[Try_]
class RegisterUserCommand
{
    #[Catch_(ValidationFailedException::class, from: [User::class, '$login::set'], match: validated_value, format: embedded_violations)]
    public string $login;
}
```

> Normally, you'd use `format: embedded_violations` along
> with [`from: [...]`](match-conditions.md#trace-origin-condition) and
> [`match: validated_value`](match-conditions.md#validationfailedexception-condition) conditions.

## Tailored Formatters 🎨🖌️

You might want to introduce a custom result format, or to adjust the default-formatted violations (e.g.
to pass additional message context for the translation).

Create a custom formatter by implementing `ExceptionMatchFormatter` interface:

```php
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionMatch;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

// Used for reference: #[Catch(format: out_of_stock)]
const out_of_stock = OutOfStockProductExceptionMatchFormatter::class;

/** @implements ExceptionMatchFormatter<OutOfStockProductException,ConstraintViolationInterface> */
#[AsAlias(ExceptionMatchFormatter::class.'<'.OutOfStockProductException::class.','.ConstraintViolationInterface::class.'>')]
final class OutOfStockProductExceptionMatchFormatter implements ExceptionMatchFormatter
{
    public function __construct(
        #[Autowire(service: ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>')]
        private ExceptionMatchFormatter $defaultFormatter,
        private TranslatorInterface $translator,
    ) {
    }

    /** @return array{ConstraintViolationInterface} */
    public function format(ExceptionMatch $exceptionMatch): array
    {
        // Format the $violation using a default formatter; then adjust
        [$violation] = $this->defaultFormatter->format($exceptionMatch);

        /** @var AddProductToCartCommand $command */
        $command = $exceptionMatch->getNode()->getLeafObject();

        /** @var OutOfStockProductException $exception */
        $exception = $exceptionMatch->getException();
        
        if (0 === $exception->getAvailableQuantity()) {
            return [$violation]; // 'The product is out of stock'
        }

        $shortage = $command->quantity - $exception->getAvailableQuantity();

        $messageTemplate = 'Only {{ available }} left in stock. Reduce the quantity by {{ shortage }} item.|Only {{ available }} left in stock. Reduce the quantity by {{ shortage }} items.';
        $parameters = [
            '{{ available }}' => $exception->getAvailableQuantity(),
            '{{ shortage }}' => $shortage,
        ];

        $violation = new ConstraintViolation(
            $this->translator->trans($messageTemplate, ['%count%' => $shortage] + $parameters, 'validators'),
            $messageTemplate,
            $parameters,
            $violation->getRoot(),
            $violation->getPropertyPath(),
            $violation->getInvalidValue(),
            plural: $shortage,
        );

        return [$violation];
    }
}
```

Register the service in the container:

```yaml
services:
    App\Cart\Support\Validation\OutOfStockProductExceptionMatchFormatter:
        autoconfigure: true
```

> For exception match formatter to be recognised by the bundle, the service must:
> 1. be registered under a generic service ID:
>    `#[AsAlias(ExceptionMatchFormatter::class.'<TException,TResult>')]`, as above;
> 2. be tagged with `ExceptionMatchFormatter` class-name tag:
>    using [autoconfiguration](https://symfony.com/doc/current/service_container.html#the-autoconfigure-option) automates it;
>
> A tagged formatter w/o the generic type fails the container compilation.

Finally, specify the formatter in the `#[Catch_]` attribute's `format:`:

```php
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;

use const App\Cart\Support\Validation\out_of_stock;

#[Try_]
final class AddProductToCartCommand
{
    public string $productId;

    #[Catch_(OutOfStockProductException::class, if: [self::class, 'catchStockException'], format: out_of_stock)]
    public int $quantity;

    /** Make sure that the other product's exception doesn't map to this command */
    public function catchStockException(OutOfStockProductException $e): bool
    {
        return $e->getProductId() === $this->productId;
    }
}
```

In this example, `OutOfStockProductExceptionMatchFormatter` accepts `OutOfStockProductException`, and creates a \
specific constraint violation that informs the user by how many items to reduce the requested `$quantity`.
