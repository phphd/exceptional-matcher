## What is Exceptional Validation? ⏪

Exceptional Validation is the approach of relying solely on exception checks, fully omitting any upfront validation.

Of itself, validation is part of the business code, not ad-hoc code. \
The best enforcement of business rules is only done via exceptions.

It's similar to _sending money_ to a friend:

- You don't question if their card's active.
- You don't question if their limit is not exceeded.
- You don't check if a proper transfer route is set up in the bank.

Instead, just send them money and watch. None of these are any of your business in the first place. \
If it doesn't work out, then you'll "catch" the issue and will undertake some action.

![Sending money to a friend.png](./Sending%20money%20to%20a%20friend.png)

Similarly, exceptional validation brings the rule enforcement responsibility back to the code where it belongs. \
&emsp; It takes the weight off the client code and puts it to the domain layer – where the rules live.

Otherwise, you'll face `Client -> (Validation < Scenario)` problem: \
&emsp; The `Scenario` expects `Validation` to have already been performed
(instead of performing it within itself). \
&emsp; The responsibility for `Validation` is on the `Client`.

This is fraught with another `Client` calling same `Scenario`, having:

- skipped part of the `Validation`;
- executed a corrupt copy of `Validation`;
- excluded the `Validation` at all.

> Imagine if in bank transfers the clients 💸 were responsible for rule validation. \
> What mess would that result in?
>
> Do you know that this's the case for many applications?

Exceptional Validation shifts that focus. Clients are lightened, Scenarios are loadened.
Validation moves off on a Scenario – until the very moment it's inescapable.

> Note: A similar strategy is used for Optimistic Concurrency Control. \
> You don't lock out everything that might go wrong. \
> You go ahead with the flow, and only at commit time check if something went wrong, \
> rolling back the changes in the case.

## Why Exceptional Validation? ✨

Ordinarily, validation flows through two different layers:

`(Controller / Form / Dto) ----> Domain`

leading to duplication and entropy (possible inconsistencies) of validation rules across Controllers / Forms / Dtos.

### Single Source of Truth ☝️

Oftentimes there are multiple actions that use the same validation rules. \
Exceptional Validation shifts focus totally into the `Domain`.

For example, consider a password validation. \
It is used both in registration (`Dto`) and in password reset (`Dto`).

```php
class RegisterUserDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 31)]
    #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM)]
    #[AppAssert\PasswordNotCompromised]
    public string $password;
}
```

```php
class ResetPasswordDto
{
    #[Assert\NotBlank] // 🤔 Deja vu
    #[Assert\Length(min: 8, max: 31)]
    #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM)]
    #[AppAssert\PasswordNotCompromised]
    public string $password;
}
```

> The rest of the fields are omitted for the sake of demonstration.

Using the validation assert attributes results in totally duplicated assertions across the board. \
Did you notice how they are scattered through many dto classes?

All these `NotBlank`, `Length()`, `PasswordStrength()` etc. are the direct business rules.

What's wrong with _duplication_ of business logic:

- makes rules harder to change (due to many places);
- encourages entropy (corrupt rules, variations).

Consider we'd want to _increase_&thinsp; the _min length_ to `10`. \
We'd do it in two places:

```diff
--- a/RegisterUserDto.php
+++ b/RegisterUserDto.php
@@
-    #[Assert\Length(min: 8, max: 31)]
+    #[Assert\Length(min: 10, max: 31)]
```

```diff
--- a/ResetPasswordDto.php
+++ b/ResetPasswordDto.php
@@
-    #[Assert\Length(min: 8, max: 31)]
+    #[Assert\Length(min: 10, max: 31)]
```

Let's say we'd want to _change_&thinsp; the _password strength threshold_ to `STRENGTH_STRONG`. \
We'd have to do it in two places:

```diff
--- a/RegisterUserDto.php
+++ b/RegisterUserDto.php
@@
-    #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM)]
+    #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_STRONG)]
```

```diff
--- a/ResetPasswordDto.php
+++ b/ResetPasswordDto.php
@@
-    #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM)]
+    #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_STRONG)]
```

The more use cases we have, the more code is needed to change.

Such rules fragmentation poses serious fragility to the design. \
It's like bank clients being obliged to verify their transactions themselves.

Idiomatically, it's a single concept used in both `RegisterUserDto::$password` and `ResetPasswordDto::$password`. \
Thus, it should be represented as a `Password` [Value Object](https://www.google.com/search?q=value+object):

```php
final readonly class Password
{
    private function __construct(
        public string $hash,
    ) {
    }

    public static function fromString(
        #[SensitiveParameter]
        string $password,
        ValidatorInterface $validator,
        PasswordHasherInterface $passwordHasher,
    ): self {
        $violationList = $validator->validate($password, new Assert\Sequentially([
            new Assert\NotBlank(),
            new Assert\Length(min: 8, max: 31),
            new Assert\PasswordStrength(minScore: PasswordStrength::STRENGTH_MEDIUM),
        ]));

        if (0 !== $violationList->count()) {
            throw new ValidationFailedException($password, $violationList);
        }

        return new self($passwordHasher->hash($password));
    }
}
```

Then rule is centralised. It's not scattered across many places. It's kept in place, locked in.

The positions of previous duplication now point to the well-organised value object:

```php
#[Try_]
class RegisterUserDto
{
    #[Catch_(ValidationFailedException::class, from: Password::class, match: validated_value, format: embedded_violations)]
    public string $password;
}
```

```php
#[Try_]
class ResetPasswordDto
{
    #[Catch_(ValidationFailedException::class, from: Password::class, match: validated_value, format: embedded_violations)]
    public string $password;
}
```

These dto classes do not drive validation but respond to it. \
They are outer to the logic, Password is the logic.

> Furthermore, with value objects, you can have such nifty methods like this:
>
> ```php
> public function verify(#[SensitiveParameter] string $password, PasswordHasherInterface $passwordHasher): void
> {
>     if (!$passwordHasher->verify($this->hash, $password)) {
>         throw new PasswordMismatchException($password);
>     }
> }
> ```
>
> Then, calling `$user->password->verify($password, $hasher)` produces extreme fluency.

Now, an attentive reader might have noticed that our last snippet didn't include `PasswordNotCompromised` rule. \
It's distinct from value object rules.

All those rules that go beyond just basic validation (e.g. connect to an external system) should expose custom
exceptions:

```diff
@@
+   #[Catch_(PasswordCompromisedException::class, match: exception_value)]
+   #[Catch_(PasswordCannotBeReusedException::class, match: exception_value)]
    public string $password;
```

From the first glance, one might think it's the same problem to map every single exception to every single dto class. \
Although it's the whole truth, even with this approach it already makes a tremendous difference to what was before:

1. This mapping is _retroactive_ – the core business logic won't be violated if the attribute is missing;
2. Missing attribute is _autocorrected_ – you'll detect that the attribute is missing by your error-tracking software
   once a user comes across a 500 due to a non-handled exception that must've been mapped. \
   On the contrary, we can't say the same about attribute-driven validation, in which a missing attribute implies
   incomplete validation which nobody has any idea of until it's too late (when the rule is broken).
3. The logic is much more _explicit_ – you can easily find all password validation places
   by searching `ValidationFailedException::class` that comes `from: Password::class`. \
   You can't really do this, for example, with `Assert\Length()` – it's too generic to surely know it's
   about password when searching.

Finally, it's not really necessary to write the full list of `#[Catch_]` for all possible exceptions. \
Instead, we can introduce an `interface PasswordException` and "catch" it:

```diff
@@
+   #[Catch_(PasswordException::class, match: exception_value)]
-   #[Catch_(PasswordCompromisedException::class, match: exception_value)]
-   #[Catch_(PasswordCannotBeReusedException::class, match: exception_value)]
    public string $password;
```

As `PasswordCompromisedException` and `PasswordCannotBeReusedException` implement `PasswordException`, \
it simplifies Dtos even further.

Thus, at the end of the day, each `Controller / Form / Dto` is kept intact whenever busines requirements change. \
Truly now they do support business rules, not drive them.

### Hit the nail on the head 🎯

When running "normal" validation, for each custom use-case you usually create custom validators. \
Exceptional Validation relieves you from writing business logic in such validators.

Consider, for example, an `OrderItemDto`.

```diff
@@
+   #[AppAssert\ProductStockIsSufficient]
    class OrderItemDto
    {
+       #[AppAssert\ProductExistsAndActive]
        public string $productId;
@@
        #[Assert\Positive]
        public int $quantity;
    }
```

At the moment of adding 2 validation asserts, we've now created 4 additional classes (2 constraint and 2 validator) for
very little value.

```php
use App\Repository\ProductRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class ProductExistsAndActiveValidator extends ConstraintValidator
{
    public function __construct(
        private ProductRepository $productRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductExistsAndActive) {
            throw new UnexpectedTypeException($constraint, ProductExistsAndActive::class);
        }

        if (empty($value)) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $product = $this->productRepository->find($value);

        if (null === $product) {
            $this->context
                ->buildViolation('product.not_found')
                ->addViolation();

            return;
        }

        if (!$product->isActive()) {
            $this->context
                ->buildViolation('product.inactive')
                ->addViolation();
        }
    }
}
```

A WHOLE LOT of beating around the bush. \
A very little of real value.

This's too much technical code that solves the problem of representation of the violation to the user. \
This is mixed with the code of true value – that solves the problem of business.

Let's add a second validator:

```php
use App\Repository\ProductRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class ProductStockIsSufficientValidator extends ConstraintValidator
{
    public function __construct(
        private ProductRepository $productRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductStockIsSufficient) {
            throw new UnexpectedTypeException($constraint, ProductStockIsSufficient::class);
        }

        if (!$value instanceof OrderItemDto) {
            throw new UnexpectedValueException($value, OrderItemDto::class);
        }

        if (empty($value->productId)) {
            return;
        }

        $product = $this->productRepository->find($value->productId);

        if (null === $product) {
            return; // let ProductExistsAndActive handle not-found
        }

        if ($product->getAvailableStock() < $value->quantity) {
            $this->context
                ->buildViolation('product.stock_insufficient')
                ->atPath('quantity')
                ->addViolation();
        }
    }
}
```

Coding in circles. \
It's necessary to perform duplicate code (e.g. `$this->productRepository->find(...)`, checks for `empty()`) in all
validators.

The main problem with this is that domain logic leaks into validators, bloating them wildly. \
Standard validation robs some of your actual business code.

This same logic you would've normally implemented in a service:

```php
$product = $productRepository->find($itemDto->productId) ?? throw new ProductNotFoundException($itemDto->productId);

if (!$product->isActive()) {
    throw new ProductInactiveException($product);
}

if (!$product->hasEnoughStock($itemDto->quantity)) {
    throw new ProductStockInsufficientException($product);
}
```

Should I tell you that these five lines of code are worth the same amount of code as was written before?

> Simple things should be done simple way.

With exceptional validation you write business logic naturally "as is" and then retroactively relate violations to the
fields. \
<u>Retroactively</u> — after your business logic has worked out — it means later (not earlier).

```diff
+   #[Try_]
    class OrderItemDto
    {
+       #[Catch_(ProductNotFoundException::class, match: exception_value)]
+       #[Catch_(ProductInactiveException::class, match: exception_value)]
        public string $productId;
@@
+       #[Catch_(ProductStockInsufficientException::class, if: [self::class, 'isExceptionRelatedToThisDto'])]
        public int $quantity;
@@
+       public function isExceptionRelatedToThisDto(ProductStockInsufficientException $exception): bool
+       {
+           return $exception->product->id === $this->productId; 
+       }
    }
```

Validation focus was shifted from dto into the domain code. \
Domain Code you write expresses the rules of business much better than any framework solution.

This approach to validation gives a lot of flexibility for your business logic, \
removing the need of custom validators, validation groups, ad-hoc duplicate codes.

Exceptional Validation, as Jesus, breaks the bondage, delivering your captive domain code, setting it free.

### Liberty 🗽

Exceptional Validation liberates you to implement the business code in the domain objects with anything you like.

It's not a strict requirement to use Symfony Validator (or any other validation mechanism),
although this library integrates it well.

Representation of the validation errors is separate from the business logic concern. \
Keeping them apart breaks the chains of dependency on particular tools, allowing to easily unit-test your logic.

Instead of chaining yourself to a particular tool that will handle both, you can use this library to bridge them.
This gives you freedom of choice, resulting in much more supple design of the system.

Ultimately, you can validate business logic with any third-party library (or even plain PHP), \
while exceptional matcher will **_correlate_** these **validation exceptions** to the fields \
whose values caused them.

## Recap: Exceptional vs Standard Validation? ⚖️

### Standard Validation 🕯️

The traditional validation uses an **attribute-driven** approach, \
which strips the domain layer from most business logic and results in duplicated client validation.

Besides that, any custom validation that would normally be implemented in a service \
is expelled into **custom validators** that pose much boilerplate in implementation.

The root of the problem is that two **responsibilities are amalgamated** into one that does both validation and
formatting.
It's all for the sake of being able to display a nice validation message on the form.

Thus, the **domain model _ends up_ naked**, \
all business rules having been leaked elsewhere.

### Exceptional Validation 💡

On the other hand, Exceptional Validation is a **domain-driven** approach that expects domain objects to be responsible
for their own validation:

- `Email` validates its own format and throws an exception if value is not valid;
- `RegisterUser` verifies that login is unique and naturally throws an exception if it's not.

Validation formatting is rather **attribute-retroactive** – attributes define just formatting for what's already
violated, not the behaviour.

Not using upfront validation, the library serves as a bridge toward contextual properly formatted validation errors.

With this design, **the validation _is_&thinsp; freehand** – you can implement logic in a service, in a value object –
with this tool or with that tool.\
Finally, you can ultimately express business code w/o being impeded by external factors.

Using exception-driven validation, you maintain a **single source of truth** for the business rules.

Ultimately, domain code that enforces its invariants via exceptions, itself constitutes a **rich domain model** \
that thoroughly demarcates responsibility contours, delegating contextual validation error formatting to the library.

### Key Takeaways 👉

Exceptional Validation:

- Settles business rules where they naturally habitate — in the domain;
- Factors out duplicate `Controller / Form / Dto` client validation into a central place;
- Eliminates the need for validation groups and custom validators;
- Makes validation easily unit-testable;
- Reduces the complexity of nested validation.
