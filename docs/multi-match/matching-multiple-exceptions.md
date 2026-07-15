# Matching multiple exceptions 🕎

Typically, validation should return all violations at once (not one by one), so they can all be shown to the user.

Yet, in a sequential computation model, only one exception is thrown at a time – as only one instruction is executed at
a time.
This leads to a situation that exceptional validation might end up with only the first violation, the rest not even
being evaluated.

For example, consider `RegisterUserDto` that catches validation exceptions from value-objects:

```php
#[Try_]
class RegisterUserDto
{
    #[Catch_(ValidationFailedException::class, from: Login::class, format: embedded_violations)]
    public string $login;

    #[Catch_(ValidationFailedException::class, from: Password::class, format: embedded_violations)]
    public string $password;
}
```

We'd want to have both `$login` and `$password` validation errors in a go. \
We'd not like to catch only the first `ValidationFailedException`:

```php
$login = Login::fromString($loginString); // could throw ValidationFailedException
$password = Password::fromString($passwordString); // could throw ValidationFailedException, too 👀
$user = new User($login, $password);
```

This issue can be likened to a visit to a car mechanic that fixes only one issue per check-up.

![Mechanic at work under the vehicle.png](./Mechanic%20at%20work%20under%20the%20vehicle.png)

Even though you get a high-quality fix, it's frustrating to have it only one at a time – you'd want
the full fixup right off!

In the Lord's Programming Language (see [HVM Bend](https://www.youtube.com/watch?v=HCOQmKTFzYY)) this limitation is
overcome by an absolutely different approach to code evaluation – Interaction Calculus.

The idea is projected into our world as the dispersed execution function:

```php
// pseudo-code
[$login, $password] = disperse([ // could throw CompositeException
    fn() => Login::fromString($loginString), // could throw ValidationFailedException
    fn() => Password::fromString($passwordString), // could throw ValidationFailedException
]);
```

> In Bend, this would probably be no different from a usual code:
> ```python
> def createUser(dto: RegisterUserDto) -> User:
>     login = Login.fromString(dto.loginString) # could raise ValidationFailedException
>     password = Password.fromString(dto.passwordString) # could raise ValidationFailedException
>     return User(login, password) # could raise CompositeException as evaluated
> ```

Since in practice validation is split into distinct functions (each perhaps throwing the exception), \
it's possible to call them sequentially one by one and collect the exceptions:

```php
function disperse(array $tasks): array
{
    foreach ($tasks as [$fn, ...$args]) {
        try {
            $results[] = $fn(...$args);
        } catch (Throwable $e) {
            $errors[] = $e;
        }
    }
    if ($errors) {
        throw new CompositeException($errors);
    }
    return $results;
}
```

There's no need to do this manually, since `amphp/amp` library provides an efficient solution using async tasks:

```php
use function Amp\Future\awaitAnyN;
use function Amp\async;

[$login, $password] = awaitAnyN(count($tasks = [ // could throw CompositeException
    async(fn() => Login::fromString($loginString)), // could throw ValidationFailedException
    async(fn() => Password::fromString($passwordString)), // could throw ValidationFailedException
]), $tasks);
```

By using `async` and `awaitAnyN` functions, we are leveraging dispersed execution flow: \
both `Login::fromString()` and `Password::fromString()` are executed regardless of each other's thrown exceptions.

If no exceptions are thrown, `$login` and `$password` variables are assigned with the returned values, \
whereas if exceptions are thrown, an `Amp\CompositeException` wraps them over and is thrown instead.

The library adds support for unwrapping composite exceptions (e.g. Amp, Messenger exceptions). \
All inner exceptions are analysed so that the user can get a complete stack of validation errors.

> If you want to register a custom composite exception unwrapper, \
> take a look
> on [ExceptionUnwrapper](https://github.com/phphd/exception-toolkit?tab=readme-ov-file#exception-unwrapper).

