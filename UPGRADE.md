This file contains notable (mostly breaking) changes to the library and migrating instructions \
for the changes not covered by automatic upgrade via Rector (see the "Upgrading" section in README.md).

## 2.0

* Removed: `PhPhD\ExceptionalValidation\Handler\ExceptionHandler` interface, \
  Removed: `phd_exceptional_validation.exception_handler` service.

  If you used these, you should implement `Symfony\Component\Messenger\Middleware\MiddlewareInterface` interface, \
  and reference `@phd_exceptional_validation` middleware service instead.

* Removed: `PhPhD\ExceptionalValidation\Formatter\ExceptionListViolationFormatter`. \
  Use `PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList::format()` instead. 

* Renamed: `PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_::$when` \
  was renamed into `$if`

* Renamed: `PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_::$condition` \
  was renamed into `$match`
