# 01 — Failure Catalog

An exhaustive inventory of the ways a `#[Try_]` / `#[Catch_]` mapping can be wrong. This catalog is the
specification for the linter: **every entry marked statically-detectable must produce a diagnostic**, and each
entry names the single place its check should live in the target model (see
[04-target-model.md](04-target-model.md)).

Legend for *"Surfaces today"*:

- **silent** — no error is ever raised; the rule simply never matches (the worst class of bug: a domain
  exception that should become a 422 turns into a 500, with nothing pointing at the mapping).
- **mid-match** — an exception (`InvalidArgumentException` / `LogicException` / `Error`) is thrown while the
  matcher is processing a *real* production exception. Additionally, because
  `CompositeMatchingRule::process()` short-circuits once all exceptions are reciprocated, assembly of later
  properties may be skipped entirely — so even "mid-match" errors can stay hidden for a long time.
- **post-match** — thrown after a successful match, during formatting.

## A. Declaration shape

| # | Mistake | Surfaces today | Where the check lives today | Target home |
|---|---|---|---|---|
| A1 | `if:` array does not have exactly 2 elements | mid-match (`Catch_::__construct`) | `Catch_::__construct` (`Assert::count`) | unchanged — attribute constructor (fires during plan compile) |
| A2 | `from:` array does not have exactly 2 elements | mid-match (`Catch_::__construct`) | `Catch_::__construct` (`Assert::count`) | unchanged |

## B. Broken references

| # | Mistake | Surfaces today | Where the check lives today | Target home |
|---|---|---|---|---|
| B1 | `exception:` class/interface does not exist (typo, moved class) | **silent** — `instanceof` against an unknown class-string is `false` forever | *nowhere* | `CaughtExceptionType` value object constructor (new; also hardens runtime) |
| B2 | `from: [Foo::class, 'bar']` — class `Foo` missing | mid-match | `ExceptionOriginMatchCondition::__construct` (`Assert::methodExists`) | `ExceptionOrigin` value object (extracted from the condition constructor) |
| B3 | `from:` method missing on existing class | mid-match | same as B2 | `ExceptionOrigin` VO |
| B4 | `from:` property hook missing (`'$title::set'` with no such property/hook), or running on PHP < 8.4 | mid-match | `ExceptionOriginMatchCondition::__construct` (`propertyHookExists`) | `ExceptionOrigin` VO. Note: hook existence is **PHP-version-dependent** — lint must run on the same PHP version as production, exactly like the runtime check |
| B5 | `from: Foo::class` (string form) — class missing | mid-match | `ExceptionOriginMatchCondition::__construct` (`Assert::classExists`) | `ExceptionOrigin` VO |
| B6 | `from: [null, 'someFunction']` — function missing | mid-match | `ExceptionOriginMatchCondition::__construct` (`function_exists`) | `ExceptionOrigin` VO |
| B7 | `if: [self::class, 'method']` — method missing | mid-match | `SimpleIfClosureMatchConditionFactory` (`Assert::methodExists`) | `IfMethodReference` VO used by the if-condition compiler |
| B8 | `match:` id not registered in the condition-factory registry | mid-match | `DelegatingMatchConditionFactory` (`LogicException`) | delegating condition compiler (same registry, same check, earlier) |
| B9 | `match:` constant undefined — e.g. `uid_value` used while `symfony/uid` is not installed (factory not registered ⇒ constant never autoloaded) | mid-match (`Error: Undefined constant` at attribute instantiation) | PHP engine | plan compiler catches `Throwable` around attribute instantiation and reports it with class/property context |
| B10 | `format:` formatter class not registered | **post-match** (`DelegatingMatchedExceptionFormatter`) — even later than assembly | `DelegatingMatchedExceptionFormatter::format()` (`has()` + `LogicException`) | plan compiler asserts `has($catch->getFormat())` against the same tagged-locator registry; the formatter-side check remains as a safety net |
| B11 | enum condition: `from:` absent, not a `BackedEnum`, or `from: [Enum::class, 'tryFrom']` instead of `'from'` | mid-match, **and only if the property value happens to be non-null** — the null-value early return in `EnumValueMatchConditionFactory` currently skips the static checks | `EnumValueMatchConditionFactory::getEnumClassName()` | enum condition compiler — static checks run unconditionally at compile; only value coercion remains bind-time |

## C. Structural / topology

These have **no runtime check at all** — they are not errors the runtime could throw, they are mappings that
can never do anything. Only a linter can report them.

| # | Mistake | Surfaces today | Detectable | Target home |
|---|---|---|---|---|
| C1 | Properties carry `#[Catch_]` but the class lacks `#[Try_]` | **silent** — the whole class is skipped (`ObjectMatchingRuleSetAssembler::isMarkedWithAnAttribute()`) | statically | plan compiler structural pass — **error** |
| C2 | `#[Try_]` on an abstract class | **silent** — class attributes are not inherited; no instance of the abstract ever exists | statically | plan compiler structural pass — **warning** |
| C3 | `#[Try_]` with zero `#[Catch_]` properties | **silent** — the plan matches nothing, matcher always returns `null` for it | statically | plan compiler structural pass — **warning** (may be work-in-progress code) |
| C4 | `#[Catch_]` on a **private property of a parent class** | **silent** — `ReflectionClass::getProperties()` on the child does not return parent privates; runtime and lint agree in ignoring it | statically (walk parent chain) | plan compiler structural pass — **warning** |

## D. Type compatibility (best-effort static approximation)

The actual checks are value-dependent and stay at bind time; but the *declared property type* often proves a
mapping can never succeed. These are lint **warnings** (a `mixed`/undeclared type produces no diagnostic).

| # | Mistake | Surfaces today | Target home |
|---|---|---|---|
| D1 | `match: enum_value` on a property whose declared type can never yield `int\|string\|Stringable` (e.g. `bool`, `array`) | mid-match `Assert` failure, only when the value is set | type-compat pass in the enum condition compiler |
| D2 | `match: uid_value` on a property whose declared type can never yield `string\|Stringable` | mid-match, only when the value is set | type-compat pass in the uid condition compiler |
| D3 | `if:` method signature incompatible: first parameter type is not a supertype of `exception:`, or return type is not `bool` | **silent wrong behavior or `TypeError`** when the closure is invoked | type-compat pass in the if-condition compiler (nothing validates this today) |
| D4 | `exception:` names an existing **class** (not interface) that does not implement `Throwable` | **silent** — `instanceof` never true | `CaughtExceptionType` VO — **error** for classes; interfaces are exempt (an interface not extending `Throwable` can still be implemented by exceptions) |

## E. Inherently runtime — out of lint scope

For honesty of the lint contract, these can *not* be caught statically and must be documented as such:

- **E1** — actual runtime value shape (enum/uid coercion asserts on concrete values); mitigated by D1/D2.
- **E2** — logic errors inside user `if:` methods.
- **E3** — behavior of custom `MatchCondition::matches()` implementations.
- **E4** — formatter runtime failures (missing translations, etc.).
- **E5** — trace-dependent behavior of `from:` matching (e.g. exotic SAPI/trace configurations).

## Coverage summary

| Category | Entries | Lint verdict |
|---|---|---|
| A. Declaration shape | 2 | error |
| B. Broken references | 11 | error |
| C. Structural | 4 | 1 error, 3 warnings |
| D. Type compatibility | 4 | 1 error, 3 warnings |
| E. Runtime-only | 5 | documented limitation |

Two facts worth emphasizing, because they justify the whole effort:

1. **The most dangerous failure modes (B1, C1, D4) are silent today.** No amount of production traffic
   reveals them; they *only* manifest as "the API returned 500 instead of 422".
2. **Every mid-match failure fires while handling a real user-facing error**, i.e. the mapping bug converts
   one incident into two.
