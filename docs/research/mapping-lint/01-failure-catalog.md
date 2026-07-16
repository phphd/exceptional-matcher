# 01 — Failure Catalog

An exhaustive inventory of the ways a `#[Try_]` / `#[Catch_]` mapping can be wrong. This catalog is the
specification for the linter: **every entry marked statically-detectable must produce a defect report**, and each
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

| # | Mistake | Surfaces today | Where the check lives today | Target home                                                                                                                                                                 |
|---|---|---|---|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| B1 | `exception:` class/interface does not exist (typo, moved class) | mid-match — `ExceptionClassMatchCondition::__construct` throws `LogicException` (`is_a(..., Throwable::class, true)` is `false` for unknown names) | `ExceptionClassMatchCondition::__construct` | unchanged — the condition constructor, now firing at plan compile                                                                                                           |
| B2 | `from: [Foo::class, 'bar']` — class `Foo` missing | mid-match | `ExceptionOriginMatchCondition::__construct` (`Assert::methodExists`) | unchanged — the condition constructor, now firing at plan compile (the condition is built once per plan)                                                                    |
| B3 | `from:` method missing on existing class | mid-match | same as B2 | same as B2                                                                                                                                                                  |
| B4 | `from:` property hook missing (`'$title::set'` with no such property/hook), or running on PHP < 8.4 | mid-match | `ExceptionOriginMatchCondition::__construct` (`propertyHookExists`) | same as B2. Note: hook existence is **PHP-version-dependent** — lint must run on the same PHP version as production, exactly like the runtime check                         |
| B5 | `from: Foo::class` (string form) — class missing | mid-match | `ExceptionOriginMatchCondition::__construct` (`Assert::classExists`) | same as B2                                                                                                                                                                  |
| B6 | `from: [null, 'someFunction']` — function missing | mid-match | `ExceptionOriginMatchCondition::__construct` (`function_exists`) | same as B2                                                                                                                                                                  |
| B7 | `if: [self::class, 'method']` — method missing | mid-match | `SimpleIfClosureMatchConditionFactory` (`Assert::methodExists`) | the if-condition compiler (`Assert::methodExists` at plan compile)                                                                                                          |
| B8 | `match:` id not registered in the condition-factory registry | mid-match | `DelegatingMatchConditionFactory` (`LogicException`) | delegating condition compiler (same registry, same check, earlier)                                                                                                          |
| B9 | `match:` constant undefined — e.g. `uid_value` used while `symfony/uid` is not installed (factory not registered ⇒ constant never autoloaded) | mid-match (`Error: Undefined constant` at attribute instantiation) | PHP engine | plan compiler catches `Throwable` around attribute instantiation and rethrows it as `InvalidMatchingPlanException` with class/property context                                              |
| B10 | `format:` formatter class not registered | **post-match** (`DelegatingMatchedExceptionFormatter`) — even later than assembly | `DelegatingMatchedExceptionFormatter::format()` (`has()` + `LogicException`) | **linter** checks `has($catch->getFormat())` against the same tagged locator the delegating formatter uses (**warning** — the registry stays the single source of contents); the formatter-side check remains the runtime enforcement |
| B11 | enum condition: `from:` absent, not a `BackedEnum`, or `from: [Enum::class, 'tryFrom']` instead of `'from'` | mid-match, **and only if the property value happens to be non-null** — the null-value early return in `EnumValueMatchConditionFactory` currently skips the static checks | `EnumValueMatchConditionFactory::getEnumClassName()` | enum condition compiler — static checks run unconditionally at compile; only value coercion remains bind-time                                                               |

## C. Structural / topology

These have **no runtime check at all** — they are not errors the runtime could throw, they are mappings that
can never do anything. Only a linter can report them.

| # | Mistake | Surfaces today | Detectable | Target home |
|---|---|---|---|---|
| C1 | Properties carry `#[Catch_]` but the class lacks `#[Try_]` | **silent** — the whole class is skipped (`ObjectMatchingRuleSetAssembler::isMarkedWithAnAttribute()`) | statically | linter structural pass — **error** (lint-only by construction: at runtime `PlanRegistry` returns `null` for classes without `Try_` *before* any compilation, preserving today's silent-skip behavior) |
| C2 | `#[Try_]` on an abstract class | **silent** — class attributes are not inherited; no instance of the abstract ever exists | statically | linter structural pass — **warning** |
| C3 | `#[Try_]` with zero `#[Catch_]` properties | **silent** as a direct match target — but such a class may still match through *nested/iterable* properties | statically | linter structural pass — **warning**; never affects plan creation (nested/iterable traversal must keep working) |
| C4 | `#[Catch_]` on a **private property of a parent class** | **silent** — `ReflectionClass::getProperties()` on the child does not return parent privates; runtime and lint agree in ignoring it | statically (walk parent chain) | plan compiler structural pass — **warning** |

## D. Type compatibility (best-effort static approximation)

The actual checks are value-dependent and stay at bind time; but the *declared property type* often proves a
mapping can never succeed. These would be linter-side **warnings** (a `mixed`/undeclared type produces no
report); since the compiler has no warning channel, they are a linter follow-up, not initial-delivery scope.

| # | Mistake | Surfaces today | Target home |
|---|---|---|---|
| D1 | `match: enum_value` on a property whose declared type can never yield `int\|string\|Stringable` (e.g. `bool`, `array`) | mid-match `Assert` failure, only when the value is set | linter type-compat pass (follow-up) |
| D2 | `match: uid_value` on a property whose declared type can never yield `string\|Stringable` | mid-match, only when the value is set | linter type-compat pass (follow-up) |
| D3 | `if:` method signature incompatible: first parameter type is not a supertype of `exception:`, or return type is not `bool` | **silent wrong behavior or `TypeError`** when the closure is invoked | linter type-compat pass (follow-up; nothing validates this today) |
| D4 | `exception:` names a type that does not implement/extend `Throwable` | mid-match — same `LogicException` as B1. Note: the current check also rejects *interfaces* that don't extend `Throwable`; that (already strict) behavior is kept as-is | `ExceptionClassMatchCondition::__construct`, firing at plan compile |

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
| C. Structural | 4 | 1 error (lint-only), 3 warnings |
| D. Type compatibility | 4 | 1 already-enforced error (D4), 3 follow-up warnings |
| E. Runtime-only | 5 | documented limitation |

Two facts worth emphasizing, because they justify the whole effort:

1. **The structural failure modes (C1–C4) are silent today.** No amount of production traffic reveals
   them; they *only* manifest as "the API returned 500 instead of 422".
2. **Every mid-match failure fires while handling a real user-facing error**, i.e. the mapping bug converts
   one incident into two.
