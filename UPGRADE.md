This file contains notable (mostly breaking) changes to the library and migrating instructions \
for the changes not covered by automatic upgrade via Rector (see the "Upgrading" section in README.md).

## 2.0

* Removed: `PhPhD\ExceptionalValidation\Handler\ExceptionHandler` interface, \
  Removed: `phd_exceptional_validation.exception_handler` service.

  If you used these, you should implement `Symfony\Component\Messenger\Middleware\MiddlewareInterface` interface, \
  and reference `@phd_exceptional_validation` middleware service instead.

* Removed: `PhPhD\ExceptionalValidation\Formatter\ExceptionListViolationFormatter`. \
  Use `PhPhD\ExceptionMapper\Exception\MatchedExceptionList::format()` instead. 

* Renamed: `PhPhD\ExceptionalValidation\Capture` \
  was renamed into `PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_`. 

* Renamed: `PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_::$when` \
  was renamed into `$if`.

* Renamed: `PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_::$condition` \
  was renamed into `$match`.

* Renamed: `PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_::$formatter` \
  was renamed into `$format`.

* Parameter Moved: `PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_::$message` \
  was moved to be after `$format`.

  Not moving it will cause this error:
  > Parameter #2 `$from` of attribute class `PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_` constructor expects `array{class-string, non-empty-string}|class-string|null`,  
  > `'exception.message'` given.

  Fix it by passing it as a named parameter: `message: 'exception.message'`.

* Added: the `lint:exception-mapper` command to validate the exception mappings have no errors. \
  See [Linter Docs](docs/mapping/linter.md).
