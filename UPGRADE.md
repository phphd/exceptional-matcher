This file contains notable (mostly breaking) changes to the library and migrating instructions \
for the changes not covered by automatic upgrade via Rector (see the "Upgrading" section in README.md).

## 2.0

* Removed: `PhPhD\ExceptionalValidation\Handler\ExceptionHandler` interface, \
  Removed: `phd_exceptional_validation.exception_handler` service.

  If you used these, you should implement `Symfony\Component\Messenger\Middleware\MiddlewareInterface` interface, \
  and reference `@phd_exceptional_validation` middleware service instead.

* Removed: `PhPhD\ExceptionalValidation\Formatter\ExceptionListViolationFormatter`. \
  Use `PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList::format()` instead. 

* Renamed: `PhPhD\ExceptionalValidation\Capture` \
  was renamed into `PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_`. 

* Renamed: `PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_::$when` \
  was renamed into `$if`.

* Renamed: `PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_::$condition` \
  was renamed into `$match`.

* Renamed: `PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_::$formatter` \
  was renamed into `$format`.

* Parameter Moved: `PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_::$message` \
  was moved to be after `$format`.

  Not moving it will cause this error:
  > Parameter #2 `$from` of attribute class `PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_` constructor expects `array{class-string, non-empty-string}|class-string|null`,  
  > `'exception.message'` given.

  Fix it by passing it as a named parameter: `message: 'exception.message'`.

* Changed: `match: enum_value` static mapping checks (`from:` must reference a `BackedEnum` \
  with the `'from'` method) are no longer skipped when the property value happens to be `null`.

* Changed: matching runs through compiled per-class matching plans (`ClassMatchingPlanRegistry`) now. \
  The `#[Catch_]` attributes of a property are compiled once per process and memoized on success; \
  error timing and exception types/messages are unchanged (a broken mapping still throws on the \
  first `match()` call that reaches the property).
