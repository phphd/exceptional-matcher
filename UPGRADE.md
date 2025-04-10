This file contains notable (mostly breaking) changes to the library, and migrating instructions.

## 2.0

`PhPhD\ExceptionalValidation\Handler\ExceptionHandler` interface, and `phd_exceptional_validation.exception_handler`
service were removed.

If you used it, you should implement `Symfony\Component\Messenger\Middleware\MiddlewareInterface` interface,
and reference `@phd_exceptional_validation` middleware service instead.
