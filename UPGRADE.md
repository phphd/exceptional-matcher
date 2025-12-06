This file contains notable (mostly breaking) changes to the library and migrating instructions.

## 2.0

* Removed: `PhPhD\ExceptionalValidation\Handler\ExceptionHandler` interface, \
  Removed: `phd_exceptional_validation.exception_handler` service.

  If you used these, you should implement `Symfony\Component\Messenger\Middleware\MiddlewareInterface` interface, \
  and reference `@phd_exceptional_validation` middleware service instead.

* Removed: `PhPhD\ExceptionalValidation\Formatter\ExceptionListViolationFormatter`. \
  Use `PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList::format()` instead. 
