# Mapping Linter 🕵🏻‍♂️

Mapping Linter checks exception mappings for every statically detectable problem.

For example, look at a class that has broken mappings:
```php
#[Try_]
class DivideByZeroCommand
{
    #[Catch_(InvalidUidException::class, match: uid_value)]
    public int $id;

    #[Catch_(DivisionByZeroError::class, format: 'bad-formatter')]
    public int $divisor = 0;
}
```

The linter will report problems:

```text
-------------------------------------------------------------------------
src/YourProject/DivideByZeroCommand.php                          
-------------------------------------------------------------------------
 ✏️       App\YourProject\DivideByZeroCommand::$id                     
 [ERROR]  UidValueMatchCondition requires a string property, got: int   
          ↳ #[Catch_] exception mapping compilation failed.             
 
 ✏️       App\YourProject\DivideByZeroCommand::$divisor          
 [ERROR]  Exception Formatter is not registered: "bad-formatter"   
          ↳ #[Catch_] exception mapping compilation failed.         

 1 class scanned: 2 errors found.
```

## Install Deps 📥

Linter requires a dev-dependency to scan classes:
```shell
composer require --dev composer/class-map-generator
```

## Add to Continuous Integration 🔄

Set up the script to run in your CI flow:

### \[Symfony\]

Lint with a Console command:

```shell
bin/console lint:exceptional-matcher -o json src/
```

### \[Standalone\]

Lint with a PHPUnit test w/o frameworks:

```php
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Port\LintMappingUseCase;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class LintMappingsTest extends TestCase
{
    private LintMappingUseCase $linter;

    protected function setUp(): void
    {
        parent::setUp();

        // Use your project's configured container instead:
        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var LintMappingUseCase $linter */
        $linter = $container->get(LintMappingUseCase::class);

        $this->linter = $linter;
    }

    #[TestWith([[__DIR__.'/../src']])]
    public function testProjectHasNoBrokenMappings(array $paths): void
    {
        /** @var array $reportedProblems */
        [$reportedProblems] = $this->linter->lint('path-string', $paths, 'array');

        self::assertSame([], $reportedProblems);
    }
}
```
