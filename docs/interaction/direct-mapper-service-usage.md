# Direct Mapper Service Usage 🔌

There are few Mapper Services available you can inject into your code for matching the exception into a particular format. 

## Available Services

If you're using Symfony, check the available exception mappers with this command.

```shell
bin/console debug:container ExceptionMapper
```

> If using this library without frameworks, you can check the definitions of `services.php` and find available mappers.

It should provide you with a similar list to this:

```text
[0] PhPhD\ExceptionMapper\ExceptionMapper<PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatchList>
[1] PhPhD\ExceptionMapper\ExceptionMapper<Symfony\Component\Validator\ConstraintViolationListInterface>
```

These mappers format the Exception to their specified format, defined as a generic parameter. \
They can format into `ExceptionMatchList`, or `ConstraintViolationList`, or you can create your custom.

The command dumps all the configured formatters.

## Usage

You can inject the wanted service into your code using generics syntax:

```php
use PhPhD\ExceptionMapper\ExceptionMapper;
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
            return $command->businessLogic($this);
        } catch (Throwable $e) {
            throw $this->failure($e, $command);
        }
    }

    private function failure(Throwable $e, SignCommand $command): Throwable
    {
        /** @var ?ConstraintViolationListInterface $violationList */
        $violationList = $this->exceptionMapper->map($e, $command);

        if (null === $violationList) {
            return $e;
        }

        return new ApplicationFailure('Validation Failed', $this->encode($violationList), previous: $e);    
    }
}
```

In this example, we use `ExceptionMapper<ConstraintViolationListInterface>` that relates thrown exceptions to the
properties of the `$command`, producing `ConstraintViolationListInterface` as an outcome.

You can write your own custom implementations of `ExceptionMapper` that will output violations in a desired format
specific to your use-case. 
