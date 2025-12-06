# Exceptional Validation 🏹

🧰 Correlate Domain Exceptions with Object Properties

[![Build Status](https://img.shields.io/github/actions/workflow/status/phphd/exceptional-validation/ci.yaml?branch=main&logo=github&logoColor=024C1A&cacheSeconds=3600)](https://github.com/phphd/exceptional-validation/actions?query=branch%3Amain)
[![Codecov](https://codecov.io/gh/phphd/exceptional-validation/graph/badge.svg?token=GZRXWYT55Z)](https://codecov.io/gh/phphd/exceptional-validation)
[![PHPStan level](https://img.shields.io/badge/dynamic/yaml?url=https%3A%2F%2Fraw.githubusercontent.com%2Fphphd%2Fexceptional-validation%2Frefs%2Fheads%2Fmain%2Fphpstan.dist.neon&query=%24.parameters.level&label=PHPStan%20level&color=%23516CB3&cacheSeconds=300&logo=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACwAAAAlCAMAAAAOVfv7AAABxVBMVEUAAABRa7JQa7NQa7NQa7NQbLNQa7JQa7JQa7NQa7JQa7NQa7NQa7NQa7NQbLJQa7NRbLNQa7NQa7NQa7NQa7JQa7JQa7NQa7NQa7NRa7NQa7MICQsjHx9Qa7NQa7NQa7JQa7NRa7JRbLJQa7NRbLNQa7JQa7NRbLNRa7NQbLPq6urn5ubf399ubGxqaGlHRkZQa7NQa7NRbLNQa7P6+vqoqKilpKSXlpc1PVokMFAsLCy1tLRKYqJycnI1MzQyMDGMi4t5eHgjHyBQa7NQbLJRa7NQa7JQa7IDBAdRbLNQa7JQa7NRa7P7+/vR0dHJyMi+vr6CgIArOV8pN1xbW1tOTk5NTU1AQEDz8/OtrKwuMkQTExNVVVUJDRUNER0AAABJYaIAAAAAAABRbLI1RnUjHh8AAAAAAABKYZ4jHh9RbLIAAAAAAAArLTsjHyAjHiAiHh8iHiAsPGMAAABRbLMAAAD///9Qa7IjHyD+/v4CAwRParAEBgpOaKxMZqkwQWwWHjIPFCJEW5c7T4UgK0gTGiwkJCXHxsZHXppCWJFBVIotPWYmMlQcJT4ZITgcHBxCWJORkZE9S3U6RmomM1UxNk0oJzC4QKvnAAAAdHRSTlMA/QUz+hwXDvDXysYT7JxVOyH06Ofj1KumkQz+/fbz3861sJ99dXFKNyf+/v79/f25hEEI/v7+/v7+/v39/f39/Pzswr6Yi4FzZ2FFLP7+/v7+/v7+/v7+/f39/fzy7+bgzr+8vLyri31xaV9ZVSopIxILC4tc+BMAAALhSURBVDjLhZJld+JQEIYnCdYiRYvsIoVSSt3d3dbd3X33JiFQobr19f29O9zcBM7poft8mtx5GCZvLmik/F7OE6indchkMtNiMW7nogn1sIixhlAsAgjB6gjHRSqmnSBY6KEhYSt1631EJZVu4wiDmxSc7KHaVXSdFk2oxKpI2CqzypfW3LSHHeU2D4/zRwcbTCmlgm3SGWPq2hlRJb93Uq/hqdxOKKvHok527aS9AIjLTeuV7YL17drVG3T+SdtbSDNIS/mwoHZ3SZLSMJzF2XuaZNCKOgCznVa7KOw0SRSlBx/yMnONUe0deahSB//GYV8lpbH30uA5SWnGv9lgafOVWu4CTKvyuii2KlLTSCaTaVakxmXcmsi5FRxvmyruYVVfD7s/cGIGOXtBUkZFcUve/7vTlyPGNk0OgEfNDbfslpTWTIEeLETxiPz6uftnlXPVaLIVDPrkXklqoZNx9xGcTGSZyKRErgZO3/k67jyKbosiNWDWB0zBNXTZrhb9orh8XpIax8ZvdUnKOKbxnWUQsuq3DFgwG7j0QAPLeRCX2pYLbT+m69VkPzhYtYXDdi4qqN4cwyWy+wSpSsqvU0QjyT4K5rEuojLQMnz3johcLgy2m60Bc0yXBTBjdpwl7ogNbZfcuisyDdZYG/LL+vvxhRsqT5mMQqjjYVZz1zepwaXAZSU68wBg83lNswbOWzWH2vPH9/P9qFKiPAic7sZ4QDpm8Yhz5+6J4iPgfUQnic249mBZAgqfxE9pfIqD38Bi0bXYsGeqIBSPExi1uF/w84vsgy+QKMpB2rM53ESOOIyaq8b3xPR2DkJ23Y1ofZuz3gxFTGGC+Jz0ZwxuBsoQoH33PC6kYTeVk5cM6jSHuVatiFwHZQmy/650OdgN48vL74aIiludHBagPM9u50gpdae4H89m+mRSJAGn8GkiM/HSoKfm74TT+PDqPXSwLxtu5+H/dC7EK6KTM+mywj+gq7vpNKT05QAAAABJRU5ErkJggg==)](https://github.com/phphd/exceptional-validation/blob/main/phpstan.dist.neon)
[![Psalm level](https://img.shields.io/badge/dynamic/xml?url=https%3A%2F%2Fraw.githubusercontent.com%2Fphphd%2Fexceptional-validation%2Frefs%2Fheads%2Fmain%2Fpsalm.xml&query=%2F%2F%40errorLevel&label=Psalm%20level&logo=data%3Aimage%2Fpng%3Bbase64%2CiVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAyVBMVEXAwMCurq7ExMSysrLGxsbv7%2B8xMTH7%2B%2Fv9%2Ff3z8%2FPzmIv6%2Bvr39%2Ffo6Ojx8fHp6en19fXt7e3r6%2Butra2%2Fv7%2FwrqX7Ujr7SjGzs7O4uLi2tra8vLywsLDX19c0NDTh4eH0mozr5%2BfOzs66urqvr6%2Fj4%2BO%2Bvr5jY2Pk5OTKysrIyMjDw8OUlJSNjY1SUlJQUFA7Ozvd3d3S0tKkpKSgoKCAgIBra2tZWVlJSUlCQkLZ2dnFxcWqqqqnp6ebm5t7e3tcXFxbW1s%2FPz9ZaoUcAAAABXRSTlPu7qCgCW2MECkAAAGBSURBVDjLfY4HcoMwEEXlgmwFIRBxEjeMa3DvNT25%2F6HyFzQM8Zg8idXf1WOAlYuslvCMnZxpz4plVtzKai5yW2SM7vMNxlqVaiWmaurfvgXhCtmO1utoQtEIdgUbJdn%2BFyc6s7iHMLAzvG9s3eExq2QyyAriwAPbjs771hLG2AgNVCnpGX1zCHKibDmHMLJpCkEaphcOIY7%2BmfNlMm2kwo6%2BfXI8RBXRT6aCJz3Psz7pz%2BfCA%2FKAWMeUIAGMT7i%2F9JNZgLzAaYQmqhALDo6BQPTeEPuCxlhN1hQxjTV9Y6ERx6HzYgmDEUCw4vGbRIgnIyilBJZzNMJoyVehUAAFgkqZQ8Cxg7hXhjqrK2VZFFFaPxPkKYQZ9UawMgQbqtOP13RCQj5G0BY2ltYmZnsImni8gSbarK21r%2FXTDXq4IMHXvu93u3cpXVN6PiCBQH9FKgxR3RwBF0M2dImHG%2FRcMGT37n9AKIwc1yFcU9xsHxZYqRY6uYS1EiuXCve5FErlX%2BGRPmznPMokAAAAAElFTkSuQmCC&cacheSeconds=300&color=%23f75c31)](https://shepherd.dev/github/phphd/exceptional-validation)
[![Type coverage](https://img.shields.io/badge/dynamic/regex?url=https%3A%2F%2Fshepherd.dev%2Fgithub%2Fphphd%2Fexceptional-validation&search=%3Cth%3E[\w\s]*coverage.*%3F%3C%2Fth%3E.*%3F%3Ctd%3E(\d%2B)(\.\d{1})%3F\d*\%25%3C%2Ftd%3E&replace=%241%25&flags=is&style=flat&logo=data%3Aimage%2Fpng%3Bbase64%2CiVBORw0KGgoAAAANSUhEUgAAACwAAAAsCAMAAAApWqozAAABblBMVEUAAAAAAAAAAAAAAAABAQEBAQELCwsAAAAAAAAAAAAGBgYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAICAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD%2F%2F%2F8AAAD9%2Ff0FBQX39%2Ff8%2FPwQEBAUFBT09PQgICAYGBjq6uo6Ojrj4%2BPd3d2%2Fv7%2B0tLSmpqZvb289PT3x8fHi4uKxsbGSkpJ0dHQwMDAtLS3KysrExMS8vLy4uLigoKCNjY2IiIiFhYV7e3tkZGRfX19cXFxMTExBQUEnJycdHR0JCQn29vbs7Oze3t7a2trX19fOzs7Hx8fBwcGtra2oqKiZmZl3d3dWVlZJSUkyMjIqKirn5%2BfR0dG7u7ujo6OBgYFUVFRFRUX5%2Bfn19fXl5eXT09O2tradnZ1%2Bfn5ra2tOTk64D2w2AAAALnRSTlMA%2Bjz99%2FX%2B6soc%2B%2FFuUBYG2qtlV0H37sSzhXxgSCoRDQoE4dPOeTAuI7yjl48yeQ609QAAAylJREFUOMuNlWdX4lAQhm8IHVRU7Lr27uYmQCD0XpUiHaUjxd5199%2FvBQIEjLLvl5z7znPmTiZzJoBXsrVN8J%2BalQqxrZWpMdTkxMysYHURS3k8Kbl0VoCOa9%2BxapWQlotEgaqGIDRnJkyEjpIZfnZ%2Fyxk0misVF9GRvlIxGwuN%2BQk%2BdmM6UyC%2B6Ma08%2BsrO6WcCxM88swtjdJ7S9K5uoYP1pwLt4%2FVnNZsLksMSQ%2FLksarpCmd8L5pu%2BeLaiKjOBpknxA5LGyI0FYDsCMm%2BsJapLsuX%2B7D0y0juu%2B1aLkktXYdZEXFXMVgKFjUo840t%2FuwKk4a7alrJuuPRxDbE21icjBHpyMuIja%2F0Wua5IF4oHr5TI%2F%2BzqPR6lnpG%2BJDIet9DYWdcLdY9vO1ZktA6LurWeJd2l8kiDORoMv%2Bms66CSJk6EQyZh%2FU2cW6Ux30Bem2EyihdzRmpJ3Uk4d0jUTnoJNqpzE2IeXVMWEKpm4NFMU4zJ2GWLM7kwg%2Bos8Qi%2BQKPzqdAdupMxmhcrVE%2Bu7Dee%2Bx9NpXyk4DsIbFENvXpyFie0AdMXjfY7SPEyDDuAysYiGCo1PIkYcbyeOr4EB4xc1sZgasLs%2FNfIbvAbCT4g6QPj2AnS5OQJtUAQCUYjc3QR3CaweEjyYI69wrXeJdBAuE1pGic96QtT0iQ74bm0DwjNzCNY3tj%2BOIUOiCS65%2FwbQHT%2Fp0wTW1qIYn%2B3tDDM%2FJIT%2BhAmw3OHoTU8lAJk7TeWJIVfw32BeGhk1ttDtRXnLYt%2BCzQCY61w67F74c6nFUM%2ByS9zgavBOdbSTHcznqvdWOsGXD4iaaugUGTdIYkVaGndFFQ34cfOlfkLFLQ2EfB1tFatDVuiQ6Dr5STAJWWw5yTMnnkqn%2B3jBd%2Fgzrn6RDG0nzQ96bmFw52KDLiuzfsJ4f1Zz6AorjdTCQYEkqir%2Fwb1H59pJgdD%2Fvzt3zlWATn6zzbP7Fa%2FdX2Nxa4GFRKZLmrb5gLevZ%2B0ulgt7cmFcDXq3M42Icwwy2ZzSod35MiIkpyQr4RgfK3RmB%2BhBv2sp%2FqAXB2qpSKQNjtKKCUKLk%2BSH%2FA%2B%2BQHmpvMd%2BAAAAAAElFTkSuQmCC&label=Type%20coverage&color=f8f8f8&cacheSeconds=3600)](https://shepherd.dev/github/phphd/exceptional-validation)
[![Packagist downloads](https://img.shields.io/packagist/dt/phphd/exceptional-validation?logo=data%3Aimage%2Fpng%3Bbase64%2CiVBORw0KGgoAAAANSUhEUgAAACwAAAAsCAMAAAApWqozAAAC%2FVBMVEUAAAAFBQVVWF8DBAQAAAABAQEMDAy1u8slJioDBAQCAQEAAAAAAAAAAACPk6CDg4RJS1I6PEFjZ28uLzMmEQIlDwAJCAgKCAcDBAQAAAABAQEAAAAAAAAAAAAAAABxdX4oDgAjDgAfHyEcHSAXFxgWFhYEBAUZGRwdHiAJBAEHAgAODg8EBAUEBQUAAABMTEyYnatxdYCJjpqFipV9gYwhIiUdHR5scHlmanN8gIsfDABucnthZG0bHB5DRUsvMTUTBgBWWWBDRktMT1YRERMkDQAkJSkVFhgrEAA3OT5ERkwrLDBSVVwuMDMqEQEGAQAfHyIRBwAGBgYpFAYsEAAKCgsNDQ8xMzgmJysODxATBwAICAkZGh0fHyI5O0ArLTEXFxoHBwdOTk7Z4PPZ4fTb4vXQ1%2BnW3e%2FU2%2B3CyNnc4%2FbY3%2FLX3vDN1OXM0uSUmaZfYmra4fXIz%2BHGzN2lq7mBOADT2uzEy9y%2Fxtaorr2Wm6h%2Bgo1bXmZQU1o2OD0CAQDHzt%2B9w9S5vs%2Bxt8ado7Can6yMkJ1scHhnanNhZG5dYGhGSU8%2FQkhfKAD%2F%2F%2F%2FS2erK0OK2vMuvtcW8vLyiqLeepLKIjJeEiJSBhZF1eYRwdH5kaHFSVVxJS1FDRUs%2BQEU9PkIwMTYlJipSIwBEHQDd5Pfw8PDU2%2B7P1efDytm%2BxNK7wdG5wNCus8OsssGTl6OJjZl7f4p6foh5fIZ2e4V%2BfoBgYmVhYWJWWWFOUFhLTlUfHyEUFBYODg4DBghtLgBXJAD29fXs7Oy0ucmhp7SoqKiQkJF2eoJ4eXpqbXZycnIPQl1KTFNOTk8FNU9KS046PEE0NDchIiUYGx8CEBlwMABkKgBZJgD6%2Bvrd5fff39%2FU0tHIycisscAAeLeysrOsrKwGcqmmpKMEaaCQk52YmJkAYZWHiYtxdYAGT3YmU2sVTmsfTWZdXV0AOVgySldVVVYDK0IbMD0AJTssLjIqKiopIyWGOwB8NgB4NABLIABBGQA8GQA3FgAsEgCoWa7%2BAAAAY3RSTlMACf5REQ4H%2FpiQgkQtIf7%2B7ejm1aqainFjXTkzJRsW%2Fv38sKainZyQfn5zbmtNNQr9%2B%2Fr4%2BPf39vbz8%2B7u7uzr6%2Bbj4N%2Fd29XV0s7My8vKyr29ubi4s6ypo5ycmJOLeHNsXg8rMEp9AAAD%2FElEQVQ4y6WUZXDbUBCElaRtmqTMzMzMzMzM7T2Z2a4ZQmY7nIY5ZWZuU2ZmZmbmTiXbsV1wnJnujzen0aedu5V02L%2FUel5rrJAqNndM%2BZEzSxaK9WnQLkQiuDHIrzBw8TA%2BEArtGFi0AKpkk0YlsBIN4wCRMKJoyzVu5QEtMaOcP7d3vV5asAsBUxhTtqVf8SmVqhT7w3VqD%2FlaZQxFSE0ysmwwX5tmoES192dHRWdW%2FY31K1uKp088%2FV4MQNXxKCQsN7GJk5ayVbEvdIB790UGL9sWiUO0iRGi3S%2BV0BEhKgvEdHbw9nWqs6LEQDe4Zl%2BZHAE%2FWZSwKTZ24wLc1jNTt%2FHgYcXxxbjQuKRMdTe4u4SGYGH6shOL9lxbtFlog7kH9%2B65XHobUa5aI2rhgmt0xsnp01eXvv783tPDOhJemKo%2FduzRVR75XFRYU7fYapMwx7z%2F6KubJ5%2Bd44ZzZRJQvzt558ndUxwyxRWVXHC12jhCEJ1Jy3jzctfOXdv0hrTTmdmanW8ffsjaggAhemMXHHSEdFZuwP3P7dJoNlDJzMKyNJrz57dSLGsIH0Fxt5j1OPBXEylEblHz4pD9dQefMabIcFieLUawZLILnh5O3E2iAUpW4Ay6SCalko2u5B1iI2CkcBCw%2BtfKZ2v1ZANQlTSIVFNiTMnhIWwWCQMzTk0BhoFNlG1a5sNVVQwAvjGeY96hsLBtnP24khUNFPViopQ4m26gIydeoVZtPJNtSwrZBMylO1RSE5esWeXzv7yG8aQTg3ciYvNqAJdzcMbRA9mJthKV9XHAlRXkJVr3IOKFv50L1Yn4AEtOLYo10QVkOjSncytfMT0GGMbYiNfr7bCYrreEsaibDmSaN6TKCJhe3%2Fk7%2B81qPq7UUtWtiM2PkaMNxoLtlvU7zCtxJNzCZSFuZcxNNefMbl567yblQsdwXEPY%2Bp1rWETJFNwvM6JJEewP1bt9SLmUiJeMOARwldyWjLRDmRY%2B2F%2Bq1ilh31b6WoojjSjipIb3q1LDhbqrWQKeeFYG4MyPciSjqsfNUQ44aTQXHGlY0bWGx801jImS5M6XQvOViod6Xkv12SCxCB2BCFLkaPkkzKOCfFmwjBeC275n5XKAUlUwzwrgUUHg6xsvoGuPhxIDDi%2BKFaBGqcSAoYrkpLXBCPAuQQVv54C25Gw4vli0gBEX4G2XT4znI1iVnprAkX6s5gUuMmQ7B0CYTkWcrG%2FNvMBN69ZJC1%2B1zrxSlKH5OcGLcUBdqzXna05e3iXrhTpjixZMB1789Hm3NSf3e651d26QF%2BfRA%2Fv8yM3Z%2FeXCxbxulap76bkWFnhpvl2jfDCvqj6tYsWKFSpUGF8Z%2Bx%2F9Aipg0qzWNVY9AAAAAElFTkSuQmCC&color=%23F28D1A&cacheSeconds=3600)](https://packagist.org/packages/phphd/exceptional-validation)
[![Licence](https://img.shields.io/github/license/phphd/exceptional-validation.svg?color=3DA639)](https://github.com/phphd/exceptional-validation/blob/main/LICENSE)

A library that captures validation exceptions and maps them to validated object properties.

No longer do you need custom validators in your object \
nor any validation in application/ui layers.

Instead, **declaratively _relate_ domain exceptions** with their relevant form fields \
and yield validation failed response as you do normally.

## A Validation Library? 🤔

It's not a validation library. Not ever intended to be. \
It doesn't provide validation rules, constraints, or validators.

Instead, it provides **exception handling** functionality, specific to a validation task.

You can validate business logic with any third-party library (or even plain PHP), \
while the library will be **_correlating_** these **validation exceptions** to the specific properties \
whose invalid values caused them.

It's not a strict requirement to use Symfony Validator as a validation component, \
though this library integrates it well.

## Why Exceptional Validation? ✨

Ordinarily, validation flows through two different layers:

- HTTP/form level;
- domain layer.

It leads to duplication and potential inconsistencies of validation rules.

### Traditional Validation 🕯️

The traditional validation uses an attribute-based approach, \
which strips the domain layer from most business logic.

Besides that, any custom validation you'd normally implement in a service \
must be wrapped in a custom validator attribute and moved away from the service.

It's all for the sake of being able to display a nice validation message on the form.

Thus, the domain services and model end up naked, \
all business rules having been leaked elsewhere.

### Exceptional Validation 💡

On the other hand, it's a common practice in DDD for domain objects to be responsible for their own validation rules.

- `Email` value object validates its own format and naturally throws an exception that represents validation failure.
- `RegisterUserService` normally verifies email is not yet taken and naturally throws an exception.

That is the kind of code that utterly expresses the model of the business, \
which should not be stripped down.

Yet, with a domain-driven approach, it's not possible to use standard validation tools, \
as these drain domain from all logic.

How then do we show contextual validation errors to the users? \
It's a task of relating thrown exception with the property which value caused this exception.

To return a neat json-response with `email` as a property path and validation error description, \
it's necessary to match `EmailAlreadyTakenException` with a `$email` property of the original `RegisterUserCommand`.

This is what Exceptional Validation was designed for.

Capturing exceptions like `EmailValidationFailedException` and mapping them to the particular form fields as `$email`, \
you maintain a single source of truth for the domain validation logic.

Domain enforces its invariants through value objects and services, \
while this library ensures that validation failures will properly appear in your forms and API responses.

### Bottom line

Exceptional Validation:

- Eliminates duplicate validation across HTTP/application and domain layers;
- Keeps business rules where they belong — in the domain;
- Makes validation logic easily unit-testable;
- Reduces complexity of nested validation scenarios;
- Eliminates the need for validation groups and custom validators.

## Installation 📥

1. Install via composer:

    ```sh
    composer require phphd/exceptional-validation
    ```

2. Enable bundles in the `bundles.php`:

    ```php
    PhPhD\ExceptionalValidation\Bundle\PhdExceptionalValidationBundle::class => ['all' => true],
    PhPhD\ExceptionToolkit\Bundle\PhdExceptionToolkitBundle::class => ['all' => true],
    ```

   > Note: `PhdExceptionToolkitBundle` is a required dependency\
   > that provides exception unwrapping needful for this library.

## Get Started 🎯

Mark a message with `#[ExceptionalValidation]` attribute. \
It's used by mapper to include this object for processing.

Then, define `#[Capture]` exception mappings on your properties. \
These declaratively describe what exceptions correlate to what properties:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
class RegisterUserCommand
{
    #[Capture(LoginAlreadyTakenException::class, 'auth.login.already_taken')]
    public string $login;

    #[Capture(WeakPasswordException::class, 'auth.password.weak')]
    public string $password;
}
```

Here, we say that `LoginAlreadyTakenException` is related to `login` property, \
while `WeakPasswordException` is related to `password` property.

The actual mapping takes place when the mapper is used:

```php
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;

/** @var ExceptionMapper<ConstraintViolationListInterface> $mapper */

try {
    $command = new RegisterUserCommand($login, $password);

    $this->service->register($command);
} catch (DomainException $exception) {
    $violationList = $mapper->map($command, $exception);

    return new JsonResponse($violationList, 422);
}
```

Each exception, when mapped, results in a `ConstraintViolation` object, \
which contains a property path, and an exception message translation.

You can use it to render form with validation errors or serialize these into a json-response.

> Note that the default messages translation domain is `validators`, \
> being inherited from `validator.translation_domain` parameter.
>
> You can change it by setting `phd_exceptional_validation.translation_domain` parameter.

## How is this different from a standard validation? ⚖️

You might be wondering why we wouldn't just use simple validation asserts right in the command?

This is a logical question. A simple answer is that this's not always convenient / best.

For example, let's take the same `RegisterUserCommand` as used before.

A comparison of the approaches would look something like this:

```diff
+#[ExceptionalValidation]
 class RegisterUserCommand
 {
-    #[App\Assert\UniqueLogin]
+    #[Capture(LoginAlreadyTakenException::class, 'auth.login.already_taken')]
     public string $login;

-    #[Assert\PasswordStrength(minScore: 2)]
+    #[Capture(WeakPasswordException::class, 'auth.password.weak')]
     public string $password;
 }
```

The main difference between the two is that standard validation runs before your actual business logic. \
This alone means that for every domain-specific rule like "login must be unique" it's necessary to create \
a custom validation constraint and a validator that implements this business logic.

Thereby, the main problem with the standard approach is that domain leaks into validators. \
That code, which you would've normally implemented in the service, you are obliged to wrap into the validator.

One more point is that oftentimes there are multiple actions that use the same validations.

For example, login uniqueness is validated both during registration and during profile update. \
Even though a "login is unique" rule is conceptually obvious, \
a validator approach is fraught with problems to check that a user's own login isn't taken into account when validating.

Exceptional validation doesn't force you to write business logic in any validators. \
Instead, you can throw an instance of exception in whatever scenario you would like to, \
and then the library will retroactively analyse it.

Another example is a password validation, which's used both during registration and during password reset. \
Using the validation attributes results in duplicated asserts between the two, \
while this business conceptually belongs to `Password`, \
which most properly would be represented as a value object, used in both actions.

With exceptional validation you just write business logic in your domain and then retroactively map violations. \
Retroactively — after your business logic has worked out. \
Representation of the errors to the user is separate from the business logic concern which's managed by this library.

Finally, this approach gives a lot of flexibility, \
removing the need for custom validators, validation groups, duplicate validation rules, \
allowing you to keep the domain code in the domain objects, \
resulting in a better design of the system.

Focus on the domain and let the library take care of the exception representation:

```php
// RegisterUserService

if ($this->userRepository->loginExists($command->login)) {
    throw new LoginAlreadyTakenException($command->login);
}
```

## Usage with Command Bus 📇

If you are using Symfony Messenger as a Command Bus, \
it's recommended to use this package
as [Symfony Messenger Middleware](https://symfony.com/doc/current/messenger.html#middleware).

> If you are not using `Messenger` component, you can still leverage features of this library, \
> as it provides a rigorously structured set of tools w/o depending on any particular implementation. \
> Installation of third-party dependencies is optional — they won't be installed unless you need it.

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

Once you have done this, the middleware will take care of capturing exceptions and re-throwing
`ExceptionalValidationFailedException`.

You can use it to catch and process it:

```php
$command = new RegisterUserCommand($login, $password);

try {
    $this->commandBus->dispatch($command);
} catch (ExceptionalValidationFailedException $exception) {
    $violationList = $exception->getViolationList();

    return $this->render('registrationForm.html.twig', ['errors' => $violationList]);
} 
```

This exception just wraps respectively mapped `ConstraintViolationList` with all your messages and property paths.

### How it works ⚙️

Primarily, it works as
a [Command Bus](https://symfony.com/doc/current/messenger.html#multiple-buses-command-event-buses)
middleware that intercepts exceptions and uses exception mapper to perform their mapping to the relevant form
properties, eventually formatting captured exceptions as
standard [SF Validator](https://symfony.com/doc/current/validation.html) violations.

> Besides that, `ExceptionMapper` is also available for direct use w/o any middleware. You can
> reference it as `ExceptionMapper<ConstraintViolationListInterface>` service.

This diagram represents the concept:

![Exceptional Validation.svg](https://raw.githubusercontent.com/phphd/exceptional-validation/refs/heads/main/assets/Exceptional%20Validation.svg)

## Custom Usage 🔌

It's possible to use features of this bundle without necessarily depending on Command Bus middleware, nor on the
Messenger component.

If you're using Symfony, you can check what exception mappers are available using this command:

```shell
bin/console debug:container ExceptionMapper
```

This should provide you with a list, similar to this:

```text
[0] PhPhD\ExceptionalValidation\Mapper\ExceptionMapper<PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList>
[1] PhPhD\ExceptionalValidation\Mapper\ExceptionMapper<Symfony\Component\Validator\ConstraintViolationListInterface>
```

These mappers allow you to map the Exception to any available format, specified as a generic parameter.
It could be `ConstraintViolationList`, or a list of `MatchedException`, or anything else.

Therefore, you can inject the needed service into your own code:

```php
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class SignDocumentActivity
{
    public function __construct(
        /** @var ExceptionMapper<ConstraintViolationListInterface> */
        #[Autowire(service: ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>')]
        private ExceptionMapper $exceptionMapper,
    ) {
    }

    public function sign(SignCommand $command): string
    {
        try {
            return $command->process();
        } catch (DomainException $e) {
            /** @var ConstraintViolationListInterface $violationList */
            $violationList = $this->exceptionMapper->map($message, $e);

            throw new ApplicationFailure(
                'Validation Failed',
                $this->encode($violationList),
                previous: $e,
            );
        }
    }
}
```

In this example, we use `ExceptionMapper` to relate the caught exception to some property of the `$message`, \
producing `ConstraintViolationListInterface` that can be used however you want to.

## Standalone Usage 🔧

If you are not using Symfony framework, you can still take advantage of this library.

Create a Service Container (`symfony/dependency-injection` is required) with a DI Extension \
and then use it to create necessary services:

```php
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;

$container = (new PhdExceptionalValidationExtension())->getContainer([
    'kernel.environment' => 'prod',
    'kernel.build_dir' => __DIR__.'/var/cache',
]);

$container->compile();

/** @var ExceptionMapper<ConstraintViolationListInterface> $mapper */
$mapper = $container->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
```

Herein, you create a Container, compile it, and use it to retrieve `ExceptionMapper`.

## Features 📙

`#[ExceptionalValidation]` and `#[Capture]` attributes allow you to implement very flexible mappings. \
Here are the examples of how you can use them.

### Capture Conditions

#### Exception Class Condition

A minimum required condition. \
Matches the exception by its class name using `instanceof` check, \
making it similar to `catch` operation.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
class PublishMessageCommand
{
    #[Capture(MessageNotFoundException::class)]
    public string $messageId;
}
```

#### Origin Source Condition

Besides filtering by exception class, \
it's possible to filter by the origin class and method name \
whence the exception raised from.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use Symfony\Component\Uid\Uuid;

#[ExceptionalValidation]
class ConfirmPackageCommand
{
    #[Capture(\InvalidArgumentException::class, from: [Uuid::class, 'fromString'])]
    public string $uid;
}
```

In this example `InvalidArgumentException` is a generic one, which can originate from multiple places. \
To catch only those exceptions that belong to `Uuid` class, `from:` clause specifies class and method names.

Thus, Exception Mapper will analyse the exception trace \
and check whether it was originated from the `from:` place.

#### When-Closure Condition

`#[Capture]` attribute allows to specify `when:` argument with a callback function to be used to determine \
whether particular instance of the exception should be captured for a given property or not. \
This is particularly useful when the same exception could be originated from multiple places:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
class TransferMoneyCommand
{
    #[Capture(BlockedCardException::class, when: [self::class, 'isWithdrawalCardBlocked'])]
    public int $withdrawalCardId;

    #[Capture(BlockedCardException::class, when: [self::class, 'isDepositCardBlocked'])]
    public int $depositCardId;

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

In this example, once we've matched `BlockedCardException` by class, custom closure is called.

If `isWithdrawalCardBlocked()` callback returns `true`, then exception is captured for `withdrawalCardId` property.

Otherwise, we analyse `depositCardId`, and if `isDepositCardBlocked()` callback returns `true`, \
then the exception is captured on this property.

If neither of them returned `true`, then exception is re-thrown upper in the stack.

#### ValueException Condition

Since in most cases capture conditions come down to the simple value comparison, it's easier to make the exception
implement `ValueException` interface and specify `condition: ExceptionValueMatchCondition::class` instead of
implementing `when:` closure every time.

This way it's possible to avoid much of the boilerplate code, keeping it clean:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition;

#[ExceptionalValidation]
class TransferMoneyCommand
{
    #[Capture(BlockedCardException::class, condition: ExceptionValueMatchCondition::class)]
    public int $withdrawalCardId;

    #[Capture(BlockedCardException::class, condition: ExceptionValueMatchCondition::class)]
    public int $depositCardId;
}
```

In this example `BlockedCardException` could be captured either to `withdrawalCardId` or `depositCardId`, \
depending on the `cardId` value from the exception.

And `BlockedCardException` itself must implement `ValueException` interface:

```php
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ValueException;

class BlockedCardException extends DomainException implements ValueException
{
    public function __construct(private Card $card) 
    {
        parent::__construct('card.blocked');
    }

    public function getValue(): int
    {
        return $this->card->getId();    
    }
}
```

#### ValidationFailedException Condition

This one is very similar to `ValueException` condition \
with the difference that it integrates Symfony's native `ValidationFailedException`.

Specify `ValidationFailedExceptionMatchCondition` to correlate validation exception's value with a property value:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionMatchCondition;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[ExceptionalValidation]
class RegisterUserCommand
{
    #[Capture(
        exception: ValidationFailedException::class,
        from: Password::class,
        condition: ValidationFailedExceptionMatchCondition::class,
    )]
    public string $password;
}
```

### Violation Formatters 🎨

There are two main built-in violation formatters you can use: `DefaultExceptionViolationFormatter` and
`ViolationListExceptionFormatter`.

If needed, create a custom violation formatter as described below.

#### Main

`MainExceptionViolationFormatter` is used by default if another formatter is not specified.

It provides a basic way of creating a `ConstraintViolation` with these parameters: \
`$root`, `$message`, `$propertyPath`, `$value`.

#### Constraint Violation List Formatter

`ViolationListExceptionFormatter` allows formatting the exceptions \
that contain a `ConstraintViolationList` obtained from the validator.

Such exceptions should implement `ViolationListException` interface.

> Besides that, it's also possible to use `ValidationFailedExceptionFormatter`, \
> which can format Symfony's native `ValidationFailedException`.

A typical exception class would look like this:

```php
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ViolationList\ViolationListException;
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

Then, specify `ViolationListExceptionFormatter` as a `formatter:` for the `#[Capture]` attribute:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ViolationList\ViolationListExceptionFormatter;

#[ExceptionalValidation]
class IssueCreditCardCommand
{
    #[Capture(
        exception: CardNumberValidationFailedException::class, 
        formatter: ViolationListExceptionFormatter::class,
    )]
    private string $cardNumber;
}
```

Thus, `CardNumberValidationFailedException` is captured on a `cardNumber` property, \
and formatter makes sure all its constraint violations are mapped for this property.

> If `#[Capture]` attribute specified a message, \
> it would've been ignored in favour of `ConstraintViolationList` messages.

#### Custom Violation Formatters

In some cases, you might want to customize the created violations. \
For example, pass additional parameters to the message translation.

You can create custom violation formatter by implementing `ExceptionViolationFormatter` interface:

```php
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedException;
use Symfony\Component\Validator\ConstraintViolationInterface;

/** @implements ExceptionViolationFormatter<LoginAlreadyTakenException|WeakPasswordException> */
final class RegistrationViolationsFormatter implements ExceptionViolationFormatter
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

        $exception = $matchedException->getException();

        if ($exception instanceof LoginAlreadyTakenException) {
            $violation = new ConstraintViolation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                ['loginHolder' => $exception->getLoginHolder()],
                // ...
            );
        }

        if ($exception instanceof WeakPasswordException) {
            // ...
        }

        return [$violation];
    }
}
```

Then, register it as a service:

```yaml
services:
    App\Auth\User\Features\Registration\Validation\RegistrationViolationsFormatter:
        autoconfigure: true
```

> In order for violation formatter to be recognized by the bundle, \
> its service must be tagged with `MatchedExceptionFormatter` class-name tag.
>
> If you are using [autoconfiguration](https://symfony.com/doc/current/service_container.html#the-autoconfigure-option),
> this will be done automatically by the service container, \
> owing to the fact that `MatchedExceptionFormatter` interface is implemented.

Finally, specify formatter in the `#[Capture]` attribute:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
final class RegisterUserCommand
{
    #[Capture(LoginAlreadyTakenException::class, formatter: LoginAlreadyTakenViolationFormatter::class)]
    private string $login;

    #[Capture(WeakPasswordException::class, formatter: WeakPasswordViolationFormatter::class)]
    private string $password;
}
```

In this example, `LoginAlreadyTakenViolationFormatter` is used to format constraint violation for
`LoginAlreadyTakenException`, \
and `WeakPasswordViolationFormatter` formats `WeakPasswordException`.

Though not recommended, you might use a single formatter for the two.

### In-depth analysis

`#[ExceptionalValidation]` attribute works side-by-side with Symfony Validator's `#[Valid]` attribute.

Once you define `#[Valid]` on an object/iterable property, \
the mapper will pick it up for the nested exception mapping analysis, \
providing a respective property path for the created violations.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use Symfony\Component\Validator\Constraints as Assert;

#[ExceptionalValidation]
class CreateOrderCommand
{
    /** @var OrderItemDto[] */
    #[Assert\Valid]
    public array $items;
}

#[ExceptionalValidation]
class OrderItemDto
{
    public int $productId;

    #[Capture(InsufficientStockException::class, when: [self::class, 'isStockExceptionForThisItem'])]
    public string $quantity;

    public function isStockExceptionForThisItem(InsufficientStockException $exception): bool
    {
        return $exception->getProductId() === $this->productId;
    }
}
```

In this example, every time exception is processed, it will also be matched with inner objects from `items` property,
until it finally arrives at `items[*].quantity` (`*` stands for the particular array item index) property, being matched
by `InsufficientStockException` class name, and custom closure condition that makes sure that it was this particular
`OrderItemDto` that caused the exception.

The resulting property path of the caught violation includes all intermediary items, starting from the root of the tree,
proceeding down to the leaf item, where the exception was actually caught.

### Capturing multiple exceptions

Typically, validation is expected to return all present violations at once (not just the first one) so they can be shown
to the user.

Though due to the limitations of the sequential computation model, only one instruction can be executed at a time, and
therefore, only one exception can be thrown at a time. This leads to a situation where validation ends up in only the
first exception being thrown, while the rest are not even reached.

For example, if we consider user registration with `RegisterUserCommand` from the code above, we'd like to capture both
`LoginAlreadyTakenException` and `WeakPasswordException` at once, so that the user can fix all the form errors at once,
rather than sorting them out one by one.

This limitation can be overcome by implementing some concepts from an Interaction Calculus model in a sequential PHP
environment. The key idea is to use a semi-parallel execution flow instead of a purely sequential.

In practice, if validation is split into multiple functions, each of which may throw an exception, the concept can be
implemented by calling them one by one and collecting any exceptions as they raise. If there were any, they are wrapped
into a composite exception that is eventually thrown.

Fortunately, you don't need to implement this manually, since `amphp/amp` library already provides a more efficient
solution than one you'd likely write yourself, using async Futures:

```php
/**
 * @var Login $login 
 * @var Password $password 
 */
[$login, $password] = await([
    // validate and create an instance of Login
    async($this->createLogin(...), $service),
    // validate and create an instance of Password
    async($this->createPassword(...), $service),
]);
```

In this example, `createLogin()` method could throw `LoginAlreadyTakenException` and `createPassword()` method could
throw `WeakPasswordException`.

By using `async` and `awaitAnyN` functions, we are leveraging semi-parallel execution flow instead of sequential, so
that both `createLogin()` and `createPassword()` methods are executed regardless of thrown exceptions.

If no exceptions were thrown, then `$login` and `$password` variables are populated with the respective return
values. But if there were indeed some exceptions then `Amp\CompositeException` will be thrown with all the wrapped
exceptions inside.

> If you would like to use a custom composite exception, make sure to read
> about [ExceptionUnwrapper](https://github.com/phphd/exception-toolkit?tab=readme-ov-file#exception-unwrapper)

Since the library is capable of processing composite exceptions (with unwrappers for Amp and Messenger exceptions), all
of our thrown exceptions will be processed, and the user will get the complete stack of validation errors at hand.

## Upgrading 👻

The basic upgrade can be performed by [Rector](https://getrector.com/documentation) using
`ExceptionalValidationSetList` \
that comes with the library and contains automatic upgrade rules.

To upgrade a project to the latest version of `exceptional-validation`, \
add the following configuration to your `rector.php` file:

```php
return RectorConfig::configure()
    ->withPaths([ __DIR__ . '/src'])
    ->withImportNames(removeUnusedImports: true)
    // Upgrading from your version (e.g. 1.4) to the latest version
    ->withSets(ExceptionalValidationSetList::fromVersion('1.4')->getSetList());
```

Make sure to specify your current version of the library so that upgrade sets will be matched correctly.

You should also check [UPGRADE.md](UPGRADE.md) for additional instructions and breaking changes.
