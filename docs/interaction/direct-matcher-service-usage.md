# Direct Matcher Service Usage 🔌

There are few Matcher Services available you can inject into your code for matching the exception into a particular format. 

## Available Services

If you're using Symfony, check the available exception matchers with this command.

```shell
bin/console debug:container ExceptionMatcher
```

> If using this library without frameworks, you can check the definitions of `services.php` and find available matchers.

It should provide you with a similar list to this:

```text
[0] PhPhD\ExceptionalMatcher\ExceptionMatcher<PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList>
[1] PhPhD\ExceptionalMatcher\ExceptionMatcher<Symfony\Component\Validator\ConstraintViolationListInterface>
```

These matchers format the Exception to their specified format, defined as a generic parameter. \
They can format into `MatchedExceptionList`, or `ConstraintViolationList`, or you can create your custom.

The command dumps all the configured formatters.

## Usage

You can inject the wanted service into your code using generics syntax:

```php
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class SignDocumentActivity
{
    public function __construct(
        /** @var ExceptionMatcher<ConstraintViolationListInterface> */
        #[Autowire(service: ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>')]
        private ExceptionMatcher $exceptionMatcher,
    ) {
    }

    public function sign(SignCommand $command): string
    {
        try {
            return $command->businessLogic($this);
        } catch (Exception $e) {
            throw $this->failure($e, $command);
        }
    }

    private function failure(Throwable $e, SignCommand $command): Throwable
    {
        /** @var ?ConstraintViolationListInterface $violationList */
        $violationList = $this->exceptionMatcher->match($e, $command);

        if (null === $violationList) {
            return $e;
        }

        return new ApplicationFailure('Validation Failed', $this->encode($violationList), previous: $e);    
    }
}
```

In this example, we use `ExceptionMatcher<ConstraintViolationListInterface>` that relates thrown exceptions to the
properties of the `$command`, producing `ConstraintViolationListInterface` as an outcome.

You can write your own custom implementations of `ExceptionMatcher` that will output violations in a desired format
specific to your use-case. 
