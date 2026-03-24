# Usage with Command Bus 📇

If you are using [Messenger Component](https://symfony.com/doc/current/components/messenger.html), you can create
a [Command Bus](https://symfony.com/doc/current/messenger.html#multiple-buses-command-event-buses) that will automate
validation, transactions, and exception handling.

> If you are not using `Messenger` component, it won't be installed automatically.
>
> Exceptional Matcher provides a rigorously structured set of tools w/o requiring any particular third-party.

## Middleware 🔂

It's recommended to involve this package as a
dedicated [Middleware](https://symfony.com/doc/current/messenger.html#middleware). \
Add `phd_exceptional_validation` middleware to the list:

```diff
 framework:
     messenger:
         buses:
             command.bus:
                 middleware:
                     - validation
+                    - phd_exceptional_validation
                     - doctrine_transaction
```

A `phd_exceptional_validation` middleware intercepts exceptions and performs their matching to object's properties by an
exception matcher, eventually formatting matched exceptions as
standard [SF Validator](https://symfony.com/doc/current/validation.html)
violations.

### How it works ⚙️

Once added, the middleware:

- runs next middleware;
- catches the exception if thrown;
    - matches it with `ExceptionMatcher<ConstraintViolationListInterface>`;
    - re-throws `ExceptionalValidationFailedException`.

This diagram represents the concept:

![Exceptional Validation.svg](https://raw.githubusercontent.com/phphd/exceptional-validation/refs/heads/main/assets/Exceptional%20Validation.svg)

## Catch 🏈

Thus, you can finally catch the exception and process as needed:

```php
$command = new RegisterUserCommand($login, $password);

try {
    $this->commandBus->dispatch($command);
} catch (ExceptionalValidationFailedException $exception) {
    $violationList = $exception->getViolationList();

    return $this->render('registrationForm.html.twig', ['errors' => $violationList]);
} 
```

This exception just wraps accordingly created `ConstraintViolationList` that contains your exception messages with
property paths.

> Tip: you can depend on the base `Symfony\Component\Messenger\Exception\ValidationFailedException`, \
> as `ExceptionalValidationFailedMessengerException` extends it. \
> Add a global listener to convert it to a response, and make no configurations specific about this library.

## Custom Middleware ✍🏻

You can create custom middleware for your format using [Cutom Matcher Service](./direct-matcher-service-usage.md)
wrapped into a [Messenger Middleware](https://symfony.com/doc/current/messenger.html#middleware) as just shown.

A good base point for implementation to build off on is `ExceptionMatcher<MatchedExceptionList>`. \
It gives you a high-level object representation of matched exceptions you can format. 
