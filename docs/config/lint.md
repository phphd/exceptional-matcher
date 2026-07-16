# Linting Mappings 🔍

A broken `#[Catch_]` mapping — a mistyped `from:` method, a missing `if:` callback, an unknown `match:`
condition — surfaces at the worst possible moment: while a real exception is being handled in production.
Some mistakes never surface at all: `#[Catch_]` properties on a class that lacks `#[Try_]` are silently
skipped.

The `lint:exceptional-matcher` command checks the mappings of every class within the given paths
**ahead of time**, in the spirit of `lint:yaml`, `lint:twig`, and `lint:container`:

```shell
bin/console lint:exceptional-matcher src/
```

```
 ✗ App\Command\TransferMoneyCommand
     [error] $withdrawCardId: Expected the key "from" to contain a valid method reference...
 ✗ App\Command\RegisterUserCommand
     [warning] #[Try_] class declares no #[Catch_] properties; it only matches through nested objects or iterable items.

 152 classes scanned: 1 errors, 1 warnings.
```

There is **no duplicated validation**: the linter forces the compilation of every property's catch plans —
the very same compilation the matcher runs in production — and turns the failures into the report. On top
of that, it adds the structural observations the runtime deliberately ignores:

| Check | Severity |
|---|---|
| `#[Catch_]` reference errors: nonexistent `exception:`/`from:` classes, methods, property hooks, functions; missing `if:` methods; unknown `match:` conditions; invalid `enum_value` mappings; undefined `match:` constants | error |
| Properties declare `#[Catch_]`, but the class is not marked with `#[Try_]` | error |
| `#[Try_]` on an abstract class (attributes are not inherited) | warning |
| `#[Try_]` class with no `#[Catch_]` properties | warning |
| Private parent-class properties with `#[Catch_]` (invisible to the subclass) | warning |
| `format:` formatter not registered in the formatter registry | warning |

## Options

- `--format=txt|json` — output format (defaults to `txt`);
- `--fail-on-warning` — exit with a non-zero code when warnings are reported (recommended for CI).

Exit codes: `0` — no errors, `1` — errors found (or warnings with `--fail-on-warning`),
`2` — invalid usage (nonexistent path, missing discovery dependency, unknown format).

## Installation 📥

The command needs `symfony/console` and `composer/class-map-generator` (class discovery):

```shell
composer require --dev symfony/console composer/class-map-generator
```

## CI Recipe 🤖

```yaml
- run: bin/console lint:exceptional-matcher src/ --fail-on-warning
```

Two caveats worth knowing:

- **Lint on the production PHP version** — some checks (e.g. `from:` property-hook existence) are
  PHP-version-dependent, exactly like their runtime counterparts.
- **Lint with the real container** — `match:` and `format:` registrations are validated against your
  application's actual services, so run the command in the same environment the application uses.

## Lint as a Unit Test 🔧

Without Symfony framework (or without the console at all), `MappingLinter` is a plain service — pin your
mappings' validity right in the test suite:

```php
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Integration\Linter\MappingLinter;

$container = (new PhdExceptionalMatcherExtension())->getContainer([
    'kernel.environment' => 'test',
    'kernel.build_dir' => __DIR__.'/var',
]);

$container->compile();

/** @var MappingLinter $linter */
$linter = $container->get(MappingLinter::class);

self::assertSame([], $linter->lint([TransferMoneyCommand::class, RegisterUserCommand::class]));
```
