# Exceptional Matcher 🏹

💼 Match the Exceptions to the Object's Properties

[![Build Status](https://img.shields.io/github/actions/workflow/status/phphd/exceptional-validation/ci.yaml?branch=main&logo=github&logoColor=024C1A&cacheSeconds=3600)](https://github.com/phphd/exceptional-validation/actions?query=branch%3Amain)
[![Codecov](https://codecov.io/gh/phphd/exceptional-validation/graph/badge.svg?token=GZRXWYT55Z)](https://codecov.io/gh/phphd/exceptional-validation)
[![PHPStan level](https://img.shields.io/badge/dynamic/yaml?url=https%3A%2F%2Fraw.githubusercontent.com%2Fphphd%2Fexceptional-validation%2Frefs%2Fheads%2Fmain%2Fphpstan.dist.neon&query=%24.parameters.level&label=PHPStan%20level&color=%23516CB3&cacheSeconds=300&logo=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACwAAAAlCAMAAAAOVfv7AAABxVBMVEUAAABRa7JQa7NQa7NQa7NQbLNQa7JQa7JQa7NQa7JQa7NQa7NQa7NQa7NQbLJQa7NRbLNQa7NQa7NQa7NQa7JQa7JQa7NQa7NQa7NRa7NQa7MICQsjHx9Qa7NQa7NQa7JQa7NRa7JRbLJQa7NRbLNQa7JQa7NRbLNRa7NQbLPq6urn5ubf399ubGxqaGlHRkZQa7NQa7NRbLNQa7P6+vqoqKilpKSXlpc1PVokMFAsLCy1tLRKYqJycnI1MzQyMDGMi4t5eHgjHyBQa7NQbLJRa7NQa7JQa7IDBAdRbLNQa7JQa7NRa7P7+/vR0dHJyMi+vr6CgIArOV8pN1xbW1tOTk5NTU1AQEDz8/OtrKwuMkQTExNVVVUJDRUNER0AAABJYaIAAAAAAABRbLI1RnUjHh8AAAAAAABKYZ4jHh9RbLIAAAAAAAArLTsjHyAjHiAiHh8iHiAsPGMAAABRbLMAAAD///9Qa7IjHyD+/v4CAwRParAEBgpOaKxMZqkwQWwWHjIPFCJEW5c7T4UgK0gTGiwkJCXHxsZHXppCWJFBVIotPWYmMlQcJT4ZITgcHBxCWJORkZE9S3U6RmomM1UxNk0oJzC4QKvnAAAAdHRSTlMA/QUz+hwXDvDXysYT7JxVOyH06Ofj1KumkQz+/fbz3861sJ99dXFKNyf+/v79/f25hEEI/v7+/v7+/v39/f39/Pzswr6Yi4FzZ2FFLP7+/v7+/v7+/v7+/f39/fzy7+bgzr+8vLyri31xaV9ZVSopIxILC4tc+BMAAALhSURBVDjLhZJld+JQEIYnCdYiRYvsIoVSSt3d3dbd3X33JiFQobr19f29O9zcBM7poft8mtx5GCZvLmik/F7OE6indchkMtNiMW7nogn1sIixhlAsAgjB6gjHRSqmnSBY6KEhYSt1631EJZVu4wiDmxSc7KHaVXSdFk2oxKpI2CqzypfW3LSHHeU2D4/zRwcbTCmlgm3SGWPq2hlRJb93Uq/hqdxOKKvHok527aS9AIjLTeuV7YL17drVG3T+SdtbSDNIS/mwoHZ3SZLSMJzF2XuaZNCKOgCznVa7KOw0SRSlBx/yMnONUe0deahSB//GYV8lpbH30uA5SWnGv9lgafOVWu4CTKvyuii2KlLTSCaTaVakxmXcmsi5FRxvmyruYVVfD7s/cGIGOXtBUkZFcUve/7vTlyPGNk0OgEfNDbfslpTWTIEeLETxiPz6uftnlXPVaLIVDPrkXklqoZNx9xGcTGSZyKRErgZO3/k67jyKbosiNWDWB0zBNXTZrhb9orh8XpIax8ZvdUnKOKbxnWUQsuq3DFgwG7j0QAPLeRCX2pYLbT+m69VkPzhYtYXDdi4qqN4cwyWy+wSpSsqvU0QjyT4K5rEuojLQMnz3johcLgy2m60Bc0yXBTBjdpwl7ogNbZfcuisyDdZYG/LL+vvxhRsqT5mMQqjjYVZz1zepwaXAZSU68wBg83lNswbOWzWH2vPH9/P9qFKiPAic7sZ4QDpm8Yhz5+6J4iPgfUQnic249mBZAgqfxE9pfIqD38Bi0bXYsGeqIBSPExi1uF/w84vsgy+QKMpB2rM53ESOOIyaq8b3xPR2DkJ23Y1ofZuz3gxFTGGC+Jz0ZwxuBsoQoH33PC6kYTeVk5cM6jSHuVatiFwHZQmy/650OdgN48vL74aIiludHBagPM9u50gpdae4H89m+mRSJAGn8GkiM/HSoKfm74TT+PDqPXSwLxtu5+H/dC7EK6KTM+mywj+gq7vpNKT05QAAAABJRU5ErkJggg==)](https://github.com/phphd/exceptional-validation/blob/main/phpstan.dist.neon)
[![Psalm level](https://img.shields.io/badge/dynamic/xml?url=https%3A%2F%2Fraw.githubusercontent.com%2Fphphd%2Fexceptional-validation%2Frefs%2Fheads%2Fmain%2Fpsalm.xml&query=%2F%2F%40errorLevel&label=Psalm%20level&logo=data%3Aimage%2Fpng%3Bbase64%2CiVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAyVBMVEXAwMCurq7ExMSysrLGxsbv7%2B8xMTH7%2B%2Fv9%2Ff3z8%2FPzmIv6%2Bvr39%2Ffo6Ojx8fHp6en19fXt7e3r6%2Butra2%2Fv7%2FwrqX7Ujr7SjGzs7O4uLi2tra8vLywsLDX19c0NDTh4eH0mozr5%2BfOzs66urqvr6%2Fj4%2BO%2Bvr5jY2Pk5OTKysrIyMjDw8OUlJSNjY1SUlJQUFA7Ozvd3d3S0tKkpKSgoKCAgIBra2tZWVlJSUlCQkLZ2dnFxcWqqqqnp6ebm5t7e3tcXFxbW1s%2FPz9ZaoUcAAAABXRSTlPu7qCgCW2MECkAAAGBSURBVDjLfY4HcoMwEEXlgmwFIRBxEjeMa3DvNT25%2F6HyFzQM8Zg8idXf1WOAlYuslvCMnZxpz4plVtzKai5yW2SM7vMNxlqVaiWmaurfvgXhCtmO1utoQtEIdgUbJdn%2BFyc6s7iHMLAzvG9s3eExq2QyyAriwAPbjs771hLG2AgNVCnpGX1zCHKibDmHMLJpCkEaphcOIY7%2BmfNlMm2kwo6%2BfXI8RBXRT6aCJz3Psz7pz%2BfCA%2FKAWMeUIAGMT7i%2F9JNZgLzAaYQmqhALDo6BQPTeEPuCxlhN1hQxjTV9Y6ERx6HzYgmDEUCw4vGbRIgnIyilBJZzNMJoyVehUAAFgkqZQ8Cxg7hXhjqrK2VZFFFaPxPkKYQZ9UawMgQbqtOP13RCQj5G0BY2ltYmZnsImni8gSbarK21r%2FXTDXq4IMHXvu93u3cpXVN6PiCBQH9FKgxR3RwBF0M2dImHG%2FRcMGT37n9AKIwc1yFcU9xsHxZYqRY6uYS1EiuXCve5FErlX%2BGRPmznPMokAAAAAElFTkSuQmCC&cacheSeconds=300&color=%23f75c31)](https://shepherd.dev/github/phphd/exceptional-validation)
[![Type coverage](https://img.shields.io/badge/dynamic/regex?url=https%3A%2F%2Fshepherd.dev%2Fgithub%2Fphphd%2Fexceptional-validation&search=%3Cth%3E[\w\s]*coverage.*%3F%3C%2Fth%3E.*%3F%3Ctd%3E(\d%2B)(\.\d{1})%3F\d*\%25%3C%2Ftd%3E&replace=%241%25&flags=is&style=flat&logo=data%3Aimage%2Fpng%3Bbase64%2CiVBORw0KGgoAAAANSUhEUgAAACwAAAAsCAMAAAApWqozAAABblBMVEUAAAAAAAAAAAAAAAABAQEBAQELCwsAAAAAAAAAAAAGBgYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAICAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD%2F%2F%2F8AAAD9%2Ff0FBQX39%2Ff8%2FPwQEBAUFBT09PQgICAYGBjq6uo6Ojrj4%2BPd3d2%2Fv7%2B0tLSmpqZvb289PT3x8fHi4uKxsbGSkpJ0dHQwMDAtLS3KysrExMS8vLy4uLigoKCNjY2IiIiFhYV7e3tkZGRfX19cXFxMTExBQUEnJycdHR0JCQn29vbs7Oze3t7a2trX19fOzs7Hx8fBwcGtra2oqKiZmZl3d3dWVlZJSUkyMjIqKirn5%2BfR0dG7u7ujo6OBgYFUVFRFRUX5%2Bfn19fXl5eXT09O2tradnZ1%2Bfn5ra2tOTk64D2w2AAAALnRSTlMA%2Bjz99%2FX%2B6soc%2B%2FFuUBYG2qtlV0H37sSzhXxgSCoRDQoE4dPOeTAuI7yjl48yeQ609QAAAylJREFUOMuNlWdX4lAQhm8IHVRU7Lr27uYmQCD0XpUiHaUjxd5199%2FvBQIEjLLvl5z7znPmTiZzJoBXsrVN8J%2BalQqxrZWpMdTkxMysYHURS3k8Kbl0VoCOa9%2BxapWQlotEgaqGIDRnJkyEjpIZfnZ%2Fyxk0misVF9GRvlIxGwuN%2BQk%2BdmM6UyC%2B6Ma08%2BsrO6WcCxM88swtjdJ7S9K5uoYP1pwLt4%2FVnNZsLksMSQ%2FLksarpCmd8L5pu%2BeLaiKjOBpknxA5LGyI0FYDsCMm%2BsJapLsuX%2B7D0y0juu%2B1aLkktXYdZEXFXMVgKFjUo840t%2FuwKk4a7alrJuuPRxDbE21icjBHpyMuIja%2F0Wua5IF4oHr5TI%2F%2BzqPR6lnpG%2BJDIet9DYWdcLdY9vO1ZktA6LurWeJd2l8kiDORoMv%2Bms66CSJk6EQyZh%2FU2cW6Ux30Bem2EyihdzRmpJ3Uk4d0jUTnoJNqpzE2IeXVMWEKpm4NFMU4zJ2GWLM7kwg%2Bos8Qi%2BQKPzqdAdupMxmhcrVE%2Bu7Dee%2Bx9NpXyk4DsIbFENvXpyFie0AdMXjfY7SPEyDDuAysYiGCo1PIkYcbyeOr4EB4xc1sZgasLs%2FNfIbvAbCT4g6QPj2AnS5OQJtUAQCUYjc3QR3CaweEjyYI69wrXeJdBAuE1pGic96QtT0iQ74bm0DwjNzCNY3tj%2BOIUOiCS65%2FwbQHT%2Fp0wTW1qIYn%2B3tDDM%2FJIT%2BhAmw3OHoTU8lAJk7TeWJIVfw32BeGhk1ttDtRXnLYt%2BCzQCY61w67F74c6nFUM%2ByS9zgavBOdbSTHcznqvdWOsGXD4iaaugUGTdIYkVaGndFFQ34cfOlfkLFLQ2EfB1tFatDVuiQ6Dr5STAJWWw5yTMnnkqn%2B3jBd%2Fgzrn6RDG0nzQ96bmFw52KDLiuzfsJ4f1Zz6AorjdTCQYEkqir%2Fwb1H59pJgdD%2Fvzt3zlWATn6zzbP7Fa%2FdX2Nxa4GFRKZLmrb5gLevZ%2B0ulgt7cmFcDXq3M42Icwwy2ZzSod35MiIkpyQr4RgfK3RmB%2BhBv2sp%2FqAXB2qpSKQNjtKKCUKLk%2BSH%2FA%2B%2BQHmpvMd%2BAAAAAAElFTkSuQmCC&label=Type%20coverage&color=f8f8f8&cacheSeconds=3600)](https://shepherd.dev/github/phphd/exceptional-validation)
[![Packagist downloads](https://img.shields.io/packagist/dt/phphd/exceptional-validation?logo=data%3Aimage%2Fpng%3Bbase64%2CiVBORw0KGgoAAAANSUhEUgAAACwAAAAsCAMAAAApWqozAAAC%2FVBMVEUAAAAFBQVVWF8DBAQAAAABAQEMDAy1u8slJioDBAQCAQEAAAAAAAAAAACPk6CDg4RJS1I6PEFjZ28uLzMmEQIlDwAJCAgKCAcDBAQAAAABAQEAAAAAAAAAAAAAAABxdX4oDgAjDgAfHyEcHSAXFxgWFhYEBAUZGRwdHiAJBAEHAgAODg8EBAUEBQUAAABMTEyYnatxdYCJjpqFipV9gYwhIiUdHR5scHlmanN8gIsfDABucnthZG0bHB5DRUsvMTUTBgBWWWBDRktMT1YRERMkDQAkJSkVFhgrEAA3OT5ERkwrLDBSVVwuMDMqEQEGAQAfHyIRBwAGBgYpFAYsEAAKCgsNDQ8xMzgmJysODxATBwAICAkZGh0fHyI5O0ArLTEXFxoHBwdOTk7Z4PPZ4fTb4vXQ1%2BnW3e%2FU2%2B3CyNnc4%2FbY3%2FLX3vDN1OXM0uSUmaZfYmra4fXIz%2BHGzN2lq7mBOADT2uzEy9y%2Fxtaorr2Wm6h%2Bgo1bXmZQU1o2OD0CAQDHzt%2B9w9S5vs%2Bxt8ado7Can6yMkJ1scHhnanNhZG5dYGhGSU8%2FQkhfKAD%2F%2F%2F%2FS2erK0OK2vMuvtcW8vLyiqLeepLKIjJeEiJSBhZF1eYRwdH5kaHFSVVxJS1FDRUs%2BQEU9PkIwMTYlJipSIwBEHQDd5Pfw8PDU2%2B7P1efDytm%2BxNK7wdG5wNCus8OsssGTl6OJjZl7f4p6foh5fIZ2e4V%2BfoBgYmVhYWJWWWFOUFhLTlUfHyEUFBYODg4DBghtLgBXJAD29fXs7Oy0ucmhp7SoqKiQkJF2eoJ4eXpqbXZycnIPQl1KTFNOTk8FNU9KS046PEE0NDchIiUYGx8CEBlwMABkKgBZJgD6%2Bvrd5fff39%2FU0tHIycisscAAeLeysrOsrKwGcqmmpKMEaaCQk52YmJkAYZWHiYtxdYAGT3YmU2sVTmsfTWZdXV0AOVgySldVVVYDK0IbMD0AJTssLjIqKiopIyWGOwB8NgB4NABLIABBGQA8GQA3FgAsEgCoWa7%2BAAAAY3RSTlMACf5REQ4H%2FpiQgkQtIf7%2B7ejm1aqainFjXTkzJRsW%2Fv38sKainZyQfn5zbmtNNQr9%2B%2Fr4%2BPf39vbz8%2B7u7uzr6%2Bbj4N%2Fd29XV0s7My8vKyr29ubi4s6ypo5ycmJOLeHNsXg8rMEp9AAAD%2FElEQVQ4y6WUZXDbUBCElaRtmqTMzMzMzMzM7T2Z2a4ZQmY7nIY5ZWZuU2ZmZmbmTiXbsV1wnJnujzen0aedu5V02L%2FUel5rrJAqNndM%2BZEzSxaK9WnQLkQiuDHIrzBw8TA%2BEArtGFi0AKpkk0YlsBIN4wCRMKJoyzVu5QEtMaOcP7d3vV5asAsBUxhTtqVf8SmVqhT7w3VqD%2FlaZQxFSE0ysmwwX5tmoES192dHRWdW%2FY31K1uKp088%2FV4MQNXxKCQsN7GJk5ayVbEvdIB790UGL9sWiUO0iRGi3S%2BV0BEhKgvEdHbw9nWqs6LEQDe4Zl%2BZHAE%2FWZSwKTZ24wLc1jNTt%2FHgYcXxxbjQuKRMdTe4u4SGYGH6shOL9lxbtFlog7kH9%2B65XHobUa5aI2rhgmt0xsnp01eXvv783tPDOhJemKo%2FduzRVR75XFRYU7fYapMwx7z%2F6KubJ5%2Bd44ZzZRJQvzt558ndUxwyxRWVXHC12jhCEJ1Jy3jzctfOXdv0hrTTmdmanW8ffsjaggAhemMXHHSEdFZuwP3P7dJoNlDJzMKyNJrz57dSLGsIH0Fxt5j1OPBXEylEblHz4pD9dQefMabIcFieLUawZLILnh5O3E2iAUpW4Ay6SCalko2u5B1iI2CkcBCw%2BtfKZ2v1ZANQlTSIVFNiTMnhIWwWCQMzTk0BhoFNlG1a5sNVVQwAvjGeY96hsLBtnP24khUNFPViopQ4m26gIydeoVZtPJNtSwrZBMylO1RSE5esWeXzv7yG8aQTg3ciYvNqAJdzcMbRA9mJthKV9XHAlRXkJVr3IOKFv50L1Yn4AEtOLYo10QVkOjSncytfMT0GGMbYiNfr7bCYrreEsaibDmSaN6TKCJhe3%2Fk7%2B81qPq7UUtWtiM2PkaMNxoLtlvU7zCtxJNzCZSFuZcxNNefMbl567yblQsdwXEPY%2Bp1rWETJFNwvM6JJEewP1bt9SLmUiJeMOARwldyWjLRDmRY%2B2F%2Bq1ilh31b6WoojjSjipIb3q1LDhbqrWQKeeFYG4MyPciSjqsfNUQ44aTQXHGlY0bWGx801jImS5M6XQvOViod6Xkv12SCxCB2BCFLkaPkkzKOCfFmwjBeC275n5XKAUlUwzwrgUUHg6xsvoGuPhxIDDi%2BKFaBGqcSAoYrkpLXBCPAuQQVv54C25Gw4vli0gBEX4G2XT4znI1iVnprAkX6s5gUuMmQ7B0CYTkWcrG%2FNvMBN69ZJC1%2B1zrxSlKH5OcGLcUBdqzXna05e3iXrhTpjixZMB1789Hm3NSf3e651d26QF%2BfRA%2Fv8yM3Z%2FeXCxbxulap76bkWFnhpvl2jfDCvqj6tYsWKFSpUGF8Z%2Bx%2F9Aipg0qzWNVY9AAAAAElFTkSuQmCC&color=%23F28D1A&cacheSeconds=3600)](https://packagist.org/packages/phphd/exceptional-validation)
[![Licence](https://img.shields.io/github/license/phphd/exceptional-validation.svg?color=3DA639)](https://github.com/phphd/exceptional-validation/blob/main/LICENSE)

A lightweight bridge from domain exceptions to validation violations.

![Exceptional Matcher.svg](https://raw.githubusercontent.com/phphd/exceptional-validation/refs/heads/main/assets/Exceptional%20Matcher.svg)

Your domain code that processes Dto (e.g. services / value objects) can throw a business exception. \
Using Matcher, you can correlate it to the property that originated it –
allowing to return precise field-specific validation errors inferred from the exceptions.

Thence it makes up for what was lacking in tools for relating validation exceptions to their originator fields.

## Quick Start ⚡

### Install 📥

1. Require via composer:

    ```sh
    composer require phphd/exceptional-validation
    ```

2. \[Symfony\] enable the bundles in the `bundles.php`:

   ```php
   PhPhD\ExceptionalMatcher\Bundle\PhdExceptionalMatcherBundle::class => ['all' => true],
   PhPhD\ExceptionToolkit\Bundle\PhdExceptionToolkitBundle::class => ['all' => true],
   ```

   > Note: `PhdExceptionToolkitBundle` is a required dependency \
   > that provides exception unwrapping needful for this library.

3. \[Non-Symfony\] configure the container:

   You can use features of this library outside frameworks. \
   See [Standalone Usage](#standalone-usage-).

### Define the Mapping 🔗

Mark a command or dto with `#[Try_]` attribute to let the matcher know it's included for processing.

Define `#[Catch_]` attributes with rules for your properties.

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

#[Try_]
class RegisterUserDto
{
    #[Catch_(LoginAlreadyTakenException::class)]
    public string $login;

    #[Catch_(PasswordCompromisedException::class)]
    public string $password;
}
```

> Note: we've named this class as `RegisterUserDto` for the sake of demonstration. \
> Normally, we'd name it as `RegisterUserCommand` ([CQS](https://martinfowler.com/bliki/CommandQuerySeparation.html)).


These describe what exceptions what properties correlate with.

Here, `LoginAlreadyTakenException` is bound to the `login` property, \
while `PasswordCompromisedException` is bound to the `password` property.

> You can have additional matching conditions beyond just the exception class name. \
> See [Match Conditions 🖇️](docs/config/match-conditions.md).

The equivalent (very simplified) rough manual logic if not using this library:

```php
$errors = [];

try {
    return $this->register($dto);
} catch (LoginAlreadyTakenException $e) {
    $errors['login'] = $e->getMessage();
} catch (PasswordCompromisedException $e) {
    $errors['password'] = $e->getMessage();
}
```

### Match the Exception 🎯

Matching takes place wherever the matcher is used. \
Exception, matched against an object, results in a `ConstraintViolation` list (or custom format):

```php
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RegisterUserApiPoint
{
    public function __construct(
        /** @var ExceptionMatcher<ConstraintViolationListInterface> */
        #[Autowire(service: ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>')]
        private ExceptionMatcher $matcher,
    ) {}

    #[Route(path: '/register', methods: ['POST'])]
    public function __invoke(RegisterUserDto $dto): Response
    {
        try {
            return $this->register($dto);
        } catch (Throwable $exception) {
            return $this->handleError($exception, $dto);
        }
    }

    private function handleError(Throwable $exception, RegisterUserDto $dto): Response
    {
        /** @var ?ConstraintViolationListInterface $violationList */
        $violationList = $this->matcher->match($exception, $dto);

        if (null === $violationList) {
            throw $exception;
        }

        return new JsonResponse($violationList, HTTP_UNPROCESSABLE_ENTITY);   
    }
}
```

> Note: response formatting is simplified for the demonstration's sake.

Created `ConstraintViolationList` contains violation-objects with matched property path, message translation, and
invalid value.

You can serialize it into a json-response or render on a form.

```json
{
    "propertyPath": "login",
    "invalidValue": "jzs",
    "message": "Login is already taken. Try another one."
}
```

## Why Exceptional Matcher ✨

Exceptional Matcher aims for a full-fledged expressive domain-embedded **validation** that makes **full use of
exceptions**. \

With Exceptional Matcher you can **omit** any **peripheral validation** off of your dto objects, \
and rely solely on validation in real code (services, value objects) – that belongs to and resides in the domain.

Read more in: [Exceptional Validation](docs/exceptional-validation/exceptional-validation.md).

### Where is the Power 🚀

Consider another use-case: \
After registration, the user should be able to _update_ his _profile_ (login, password). \
Updating the login must ensure its uniqueness in spite of the current user.

Here's what we'd have to do with an upfront attribute-driven validation:

```php
#[UniqueEntity(
    fields: ['login'],
    entityClass: User::class,
    identifierFieldNames: ['user' => 'id'],
)]
class UpdateUserProfileDto
{ ... }
```

Compare this to `#[Catch_]` and discern which communicates the intent better.

```php
#[Catch_(LoginAlreadyTakenException::class)]
public string $login;
```

The first approach is very imperative, verbose. \
The second declaratively states the fact.

Moreover, now you don't restrain yourself by the framework's limitations. \
You can implement just anything you need just as fast and just as good as possible.

Now, the mapping for profile update `Dto` is just as high-level as with [registration `Dto`](#define-the-mapping-):

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

#[Try_]
class UpdateUserProfileDto
{
    public User $user;

    #[Catch_(LoginAlreadyTakenException::class)]
    public string $login;

    #[Catch_(PasswordCompromisedException::class)]
    #[Catch_(PasswordCannotBeReusedException::class)]
    public string $password;
}
```

No custom validators, no attribute-driven-rules - just pure business description.

The main code is just as simple as it could be:

```php
$userWithTheSameLogin = $userRepository->whereLogin($dto->login)->firstOrNull();

if ($userWithTheSameLogin?->is($currentUser) === false) {
    throw new LoginAlreadyTakenException($dto->login);
}
```

We've reused the same `LoginAlreadyTakenException` as used in registration, yet under another condition.

This communicates the design much better than what we've seen thus far.

This is where the power comes from. You don't cram the validation into the framework. \
You broaden the framework so that it embraces your validation in a way that it naturally fits in.

## Interaction approaches 🔁

The library provides a few interaction points:

- [Matcher Service](docs/interaction/direct-matcher-service-usage.md) – manual handling
  (just [as shown](#match-the-exception-));
- [Bus Middleware](docs/interaction/command-bus-middleware.md) – automated handling.

## Features 💎

`#[Try_]` and `#[Catch_]` attributes allow implementation of very flexible matching rules. \
It's highly recommended to get acquainted with the examples to apprehend the full power of these solutions.

There are two configuration features:

- [Match Conditions 🖇️](docs/config/match-conditions.md) – determine whether a given exception should match the given
  property;
- [Violation Formatters 🎨](docs/config/violation-formatters.md) – represent the exception in a desired format.

That's really all this library does – matches the exception and formats it.

#### Cheat Sheet 📝

For a cheat-sheet example of configuration, check the following:

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\enum_value;
use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid\uid_value;
use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\exception_value;
use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Validator\validated_value;
use const PhPhD\ExceptionalMatcher\Validator\Formatter\Validator\validator_violations;

#[Try_]
class ImportProductDto
{
    #[Catch_(InvalidUidException::class, match: uid_value, message: 'This is not a valid UUID.')]
    public string $id;

    #[Catch_(CategoryNotFoundException::class, match: exception_value)] // Message is derived from Exception
    public string $categoryId;

    #[Catch_(\ValueError::class, from: ProductStatus::class, match: enum_value, message: 'The value you selected is not a valid choice.')]
    public string $status;

    #[Catch_(ValidationFailedException::class, from: ProductDescription::class, match: validated_value, format: validator_violations)]
    public string $description;

    #[Catch_(BackorderDisabledForCategoryException::class, if: [self::class, 'thisProductViolatesBackorder'])]
    public ?int $backorderLimit;

    /**
     * Needed in case of deep analysis.
     * 
     * If this method returns TRUE, the exception is linked to $backorderLimit of *this object*;
     * otherwise this exception has nothing to do with this object. 
     */
    public function thisProductViolatesBackorder(BackorderDisabledForCategoryException $exception): bool
    {
        if ($exception->categoryId !== $this->categoryId) {
            return false; // Backorder configuration of the given category has nothing to do with this category.
        }

        if (null === $this->backorderLimit) {
            return false; // The product didn't even enable backorder, much less violated it.
        }

        return true;
    }
}
```

### Deep analysis 🌊

The matcher automatically picks all nested objects for analysis, provided that they define `#[Try_]` attribute.

```php
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use Symfony\Component\Validator\Constraints as Assert;

#[Try_]
class ImportProductBatchDto
{
    /** @var ImportProductDto[] */
    public array $items;
}
```

With nested matching with array properties, property paths are formatted differently.

In the example above, when the exception is matched, the path would be `items[<index>].<filed>`:

- `<index>` - a particular array index;
- `<field>` - a particualr property name of that object.

When nesting is really deep, the resulting property path of the formatted violation
would include all intermediary properties in its path,
starting from the root, down to the leaf item where the exception was actually matched.

#### Need for conditions

Finding a match for the exception in `array` field is like finding your luggage in the _baggage claim_ \
when everyone else took just the _same alike red backpack_ as you did.

<img src="https://raw.githubusercontent.com/phphd/exceptional-validation/refs/heads/main/assets/Red Backpack.jpeg" alt="Red Backpack" width="75px">

When many backpacks are as yours, you must know which one is yours.

Similarly, finding a match for `BackorderDisabledForCategoryException` across `ImportProductDto[]` must know which
one to relate to, lest it would choose the first object by exception's `class:` condition (i.e. "grab the first red one
and go").

To find your backpack, you would look at some other characteristics that discern it from the rest, \
yea, up to the point of opening it and discovering (or not discovering) your stuff in there.

```php
if ($exception->categoryId !== $this->categoryId) {
    // not my backpack
}
```

That's what `if:` condition is there for – to relate an exception to `$this` particular object.

In our example, we check that the object's category (e.g. stuff in a backpack) is the same as the one we seek for of the
exception.

If the category is different, the object is skipped and another is taken for consideration.

The same applies to the products that don't enable backorder (`backorderLimit` is not filled):

```php
if (null === $this->backorderLimit) {
    // It's not my BackorderDisabledForCategoryException! I didn't enable backorder! 
}
```

Thus, we prevent false attribution of the exception to an object that had nothing to do with it.

## Advanced 🛠️

- [Matching multiple exceptions 🕎](docs/multi-match/matching-multiple-exceptions.md) – beyond just one
  thrown exception.

## Standalone Usage 🔧

If you are not using a Symfony framework, you can still have a great advantage of this library.

In your vanilla project, create a Service Container (`symfony/dependency-injection` is required) \
and use it to get necessary services:

```php
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList;

$container = (new PhdExceptionalMatcherExtension())->getContainer([
    // These are not used but still required by Symfony DI
    'kernel.environment' => 'prod',
    'kernel.build_dir' => __DIR__.'/var/cache',
]);

$container->compile();

/** @var ExceptionMatcher<MatchedExceptionList> $matcher */
$matcher = $container->get(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>');
```

Herein, you create a Container, compile it, and use to get `ExceptionMatcher`.

## Upgrading 👻

The basic upgrade should be performed by [Rector](https://getrector.com/documentation) using
`ExceptionalMatcherSetList` \
that comes with the library and contains automatic upgrade rules.

To upgrade a project to the latest version of `exceptional-validation`, \
make the following configuration to your `rector.php` file:

```php
use PhPhD\ExceptionalMatcher\Upgrade\ExceptionalMatcherSetList;

return RectorConfig::configure()
    ->withPaths([ __DIR__ . '/src'])
    ->withImportNames(removeUnusedImports: true)
    // Upgrading from your version (e.g. 1.4) to the latest version
    ->withSets(ExceptionalMatcherSetList::fromVersion('1.4')->getSetList());
```

Make sure to specify your current version of the library so that upgrade sets will be matched correctly.

You should also check [UPGRADE.md](UPGRADE.md) for the list of breaking changes and additional instructions.
