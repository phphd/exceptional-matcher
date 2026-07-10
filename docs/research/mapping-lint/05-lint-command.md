# 05 — The Lint Command

User-facing design of the linter, layered so the core works without Symfony (matching the library's
standalone-usage story).

## Layering

```
MappingLinter (framework-agnostic core service)
  = class discovery ∘ MappingPlanCompiler(CollectingDefectHandler) ∘ defect report

LintExceptionalMatcherCommand (thin optional Symfony Console wrapper)
```

`MappingLinter` takes an iterable of class names and returns `list<MappingDefect>` — it contains **no
checks of its own**; every defect originates in the plan compiler ([04-target-model.md](04-target-model.md)).

## Command UX

Mirrors the `lint:*` family (`lint:yaml config/`, `lint:twig templates/`):

```
bin/console lint:exceptional-matcher src/ [more paths…]
    [--format=txt|json]
    [--fail-on-warning]
```

- **Arguments**: one or more files/directories, required (like `lint:yaml`/`lint:twig`). No bundle
  configuration needed to start using it.
- **Exit codes**: `0` clean, `1` any error-severity defect (or warning with `--fail-on-warning`),
  `2` usage/discovery failure (path missing, discovery dependency absent).
- A `--format=github` workflow-annotations mode (as `lint:yaml` has) is a follow-up, not part of the
  initial delivery; class + property location is already carried by every defect (line numbers best-effort
  via reflection).
- Output groups defects by class, one line per `Catch_`:

```
 ✗ App\Command\TransferMoneyCommand
     $withdrawFromCardId  #[Catch_] #1  [EM-B03] from: method Card::witdraw() does not exist
     $depositToCardId     #[Catch_] #0  [EM-D01] enum_value on bool property can never match
 ✓ App\Command\RegisterUserCommand

 2 classes scanned, 1 with errors (2 errors, 0 warnings)
```

Defect codes are the catalog ids ([01-failure-catalog.md](01-failure-catalog.md)) — stable identifiers
that make CI baselines and docs cross-references possible; the human message is the original assertion
message (webmozart texts are already good).

## Class discovery

Requirements: find every class in the given paths, without executing side-effectful code beyond autoloading
(same tolerance as `debug:router` and friends).

Decision (maintainer-confirmed): **`composer/class-map-generator`** — the scanner composer itself uses; handles PSR
violations, hidden files, syntax-error tolerance. As a dev-time-only need it goes to `require-dev` +
`suggest`; the command degrades with an actionable error if the package is absent. Hand-rolling a
`token_get_all` scanner (à la Symfony Routing's `AttributeFileLoader::findClass()`) remains the zero-dependency
fallback; not preferred — parsing PHP is exactly the kind of code this library should not maintain.

Per discovered name: `class_exists($name)` inside try/catch (autoload failures become EM-B9-style
defects), skip interfaces/traits/enums, then lint every class that has `#[Try_]` **or any property with
`#[Catch_]`** — the latter is what catches C1 (`Catch_` without `Try_`), which pure `#[Try_]` filtering
would silently skip, repeating the runtime's blind spot.

Two operational caveats, documented in the command help:

- **Lint on the production PHP version** — hook existence (B4) and version-guarded code paths are
  PHP-version-dependent, identical to runtime behavior.
- **Lint with the real container** — `match:`/`format:` registry checks (B8/B10) validate against the
  application's actual registered services, so the command must run in the same environment/kernel the app
  uses (this is a feature: it validates *your* configuration, not a simulation of it).

## Registration

- Command registered in the bundle only when Symfony Console is installed
  (`class_exists(Application::class)`), lazy via `#[AsCommand]`.
- `composer.json`: add `symfony/console` to `require-dev` and `suggest`; extend the `conflict` section with
  the same version envelope used for the other Symfony components (`<6.0 || >8.4`).

## Optional: cache-warmup validation

The `lint:container` philosophy — broken config should fail the *build*, not the first request. Once the
command exists, an opt-in warmer is trivial since it reuses `MappingLinter` verbatim:

```yaml
phd_exceptional_matcher:
    lint:
        paths: ['%kernel.project_dir%/src']   # enables a CacheWarmer that fails cache:warmup on errors
```

Off by default (directory scanning during warmup is a cost some deployments won't want; CI lint covers most
teams). This is a follow-up, not part of the initial delivery.

## Standalone (non-Symfony) usage

`MappingLinter` + `CollectingDefectHandler` are plain services available from
`PhdExceptionalMatcherExtension::getContainer()`, so non-Symfony users can run lint in a test:

```php
$defects = $linter->lint([TransferMoneyCommand::class, RegisterUserCommand::class]);
self::assertSame([], $defects);
```

That "lint as a unit test" pattern is worth documenting even for Symfony users — it pins mapping validity to
the test suite without any console invocation, and gives the library its own dogfooding surface (the shared
stubs under `tests/Unit/Stub/` are linted for zero defects, while the intentionally-broken
`Tests/Stub/Invalid/` fixtures assert their specific defect codes).
