# 04 — Target Domain Model: Compiled Matching Plans

This document describes the to-be model for Option B (see [03-design-options.md](03-design-options.md)).
All code below is a sketch conveying shape and responsibilities, not final naming or signatures.

## Ubiquitous language

| Term | Meaning | DDD / pattern role |
|---|---|---|
| **Mapping declaration** | The `#[Try_]` / `#[Catch_]` attributes as authored on a class | source text |
| **Class matching plan** (`ClassMatchingPlan`) | The compiled, *validated* mapping of one class: which properties catch what, under which conditions, formatted how | gradually materialized, memoized value / **flyweight (intrinsic state)** |
| **Plan compiler** | Turns a declaration into a plan; the **single validation boundary** for reference/shape checks (catalog A/B) — each runs here or in a condition constructor it invokes, throwing exactly as in production | anti-corruption boundary; "parse, don't validate" |
| **Plan registry** | `getPlan(class-string): ?ClassMatchingPlan`, compile-on-first-use, per-process cache | **flyweight factory** |
| **Match scope** | The extrinsic context of one match attempt: subject instance, property value, owner chain, property path | extrinsic state |
| **Rule binding** | `plan->bind(…)` — the flyweight operation: weds intrinsic structure to one subject and yields the *existing* rule tree, which executes itself via `MatchingRule::process()` | flyweight operation (no executor service) |
| **Mapping defect** | One found mapping problem with location (class, property, attribute ordinal) — a **lint-layer** report row built from a caught compile failure or a structural finding; the compiler knows nothing of it | reporting value (`Lint\` namespace) |

The word *plan* is chosen over "metadata"/"blueprint-of-everything" because it names what the object is
*for*: a precomputed course of action the bound rules follow. `compile` pairs with it naturally.

## Plan structure (intrinsic state)

```php
final class ClassMatchingPlan
{
    public function __construct(
        public readonly string $className,
    ) {}

    /**
     * Materialized lazily on first iteration, memoized — the same gradual relationship
     * PropertyPlan has to its CatchPlans. Declaration order preserved — matching order is semantics.
     *
     * @return iterable<PropertyPlan>
     */
    public function getPropertyPlans(): iterable;
}

final class PropertyPlan
{
    public function __construct(
        public readonly ReflectionProperty $property,   // value reader; plans are per-process, no serialization concern
    ) {}

    /**
     * Compiled on first access, memoized on success (gradual materialization).
     *
     * @return iterable<CatchPlan>
     * @throws InvalidMatchingPlanException
     */
    public function getCatchPlans(): iterable;
}

final class CatchPlan
{
    public function __construct(
        public readonly MatchConditionBlueprint $condition,
        /** @var class-string<MatchedExceptionFormatter> validated against the formatter registry at compile */
        public readonly string $formatterId,
        public readonly ?string $messageTemplate,
    ) {}
}
```

## Invariants at construction: the conditions are their own validators

The reference checks (catalog B1–B7) already live in condition constructors and compiler guards. Because
intrinsic conditions are now **constructed at plan-compile time**, those constructors become the
compile-time validation home with no extraction needed:

- `ExceptionClassMatchCondition::__construct` — B1/D4 (class exists + is `Throwable`) — already present today.
- `ExceptionOriginMatchCondition::__construct` — B2–B6 — unchanged, now firing at compile.
- `if:` method existence (B7) — `Assert::methodExists` in the if-condition compiler.

*(Design change vs the first draft of this document: dedicated `CaughtExceptionType` / `ExceptionOrigin` /
`IfMethodReference` value objects were dropped — once conditions are constructed at compile time their
existing constructors ARE the single home, and the value objects would have added classes without adding a
second call site.)*

## Conditions: blueprint (compile) vs binding (match)

The heart of the flyweight split — **already implemented at the condition level**. The former
`MatchConditionFactory::getCondition(Catch_ $catch, MatchingRule $owner)` mixed static validation with
value capture; it has been split (and the factory removed outright — 2.0 is unreleased, no BC concern)
into:

```php
/** Intrinsic half — compile time. This is where validation lives. */
interface MatchConditionCompiler
{
    /** @throws LogicException on any statically detectable error */
    public function compile(Catch_ $catch): ?MatchConditionBlueprint;
}

/** Bridge to runtime — bind time. NO mapping validation here, only value binding. */
interface MatchConditionBlueprint
{
    public function bind(MatchingRule $rule): MatchCondition;   // MatchCondition (@api) stays as-is
}
```

`compile()` sees only the declaration (the `Catch_` attribute) — everything it can check is checkable
without an instance. `bind()` receives the existing `@api` `MatchingRule` carrying the extrinsic facts —
value, enclosing object, root object, property path. What remains for the plan model is moving *when*
`compile()` runs: today the per-match assembler invokes it on every `match()` call; the plan compiler will
invoke it once per class and memoize the blueprint.

Per built-in condition:

| Condition | Compile-time (validated once, shared) | Bind-time |
|---|---|---|
| exception class | constructs `ExceptionClassMatchCondition` (ctor validates B1/D4) | — condition is itself intrinsic: **one shared instance per plan**, `bind()` returns it |
| origin (`from:`) | constructs `ExceptionOriginMatchCondition` (ctor validates B2–B6) | — intrinsic, shared instance |
| `if:` closure | `Assert::methodExists` (D3 signature warning is a follow-up) | rebind `[class, method]` to the scope's enclosing instance |
| `match: enum_value` | `ValueError` subtype guard; `from:` is `BackedEnum` + `'from'` (B11 — now *structurally* unconditional) | null value ⇒ `FalseCondition`; coerce value `int\|string\|Stringable` |
| `match: uid_value` | subtype guard | null ⇒ `FalseCondition`; coerce stringable |
| `match: exception_value` / `validated_value` | subtype guards | capture scope value |
| `match:` custom id | registry lookup (B8) | delegate to the registered compiler's blueprint |
| composite | compose child blueprints; `FalseCondition` short-circuit moves to bind | evaluate as today |

`format:` (B10) is checked by the **linter** against the same tagged-locator registry the delegating
formatter uses — the registry stays the single source of *contents*; the formatter's own `has()` check
remains the runtime enforcement.

## The compiler fails exactly like production

There is deliberately **no reporting-policy parameter**. An earlier draft routed defects through a
`DefectHandler` strategy (throwing for runtime, collecting for lint); it was dropped (maintainer decision):
the compiler has a single behavior — the production one — and any statically detectable defect throws at
the moment the affected property is first compiled:

```php
final class MappingPlanCompiler
{
    /**
     * @return list<CatchPlan>
     * @throws InvalidMatchingPlanException
     */
    public function compileProperty(ReflectionProperty $property): array
    {
        // per #[Catch_] attribute:
        //   try { newInstance(); conditionCompiler->compile(...); }
        //   catch (Throwable $e) { throw InvalidMatchingPlanException::at($location, $e); }
    }
}
```

`InvalidMatchingPlanException extends LogicException`; its message keeps the original assertion message
verbatim as prefix and appends the location — ` ({Class}::${property}, #[Catch_] #{n})` — with the cause as
`previous`.

Notes:

- **Runtime** compiles gradually: `PropertyPlan::getCatchPlans()` invokes the compiler on first access and
  memoizes success; a failed compile is not memoized (it rethrows on the next match — today's timing,
  better message).
- **Lint** is the same compiler driven eagerly: the linter forces every property of every discovered class
  and lets the exception fail it. It catches per property (to keep walking remaining properties and
  classes), so granularity is **first error per property** — the `lint:yaml` model, which likewise reports
  the first parse error per file: fix, re-run. There is no collecting mode.
- The `catch (Throwable)` (not `catch (InvalidArgumentException)`) inside the compiler is what covers
  catalog entry B9: an undefined `match:` constant surfaces as an `Error` at `newInstance()` and is
  rethrown as `InvalidMatchingPlanException` with location like any other defect.
- Structural observations the runtime deliberately ignores (C1–C4) are **not** compiler concerns — they
  live in the linter (see [05-lint-command.md](05-lint-command.md)). This duplicates nothing: no runtime
  rule exists for them.
- The constants-autoloading closure (currently triggered by `ObjectMatchingRuleSetAssemblerService`) moves to
  the compiler — the guarantee "constants are loaded before any attribute instantiation" holds for both
  runtime and lint by construction.

## The registry (flyweight factory) and rule binding

```php
final class PlanRegistry
{
    /** @var array<class-string, ?ClassMatchingPlan> */
    private array $plans = [];

    public function getPlan(string $className): ?ClassMatchingPlan;
    // null ⇔ no #[Try_], checked BEFORE creating a plan (Catch_-without-Try_ stays a silent skip at
    // runtime; C1 is lint-only). Plans are skeletal at creation — even the property list materializes
    // lazily on first iteration; each property's catches compile on first use, and a failed compile is
    // not memoized — it rethrows on the next match.
}
```

There is **no executor service** — an earlier draft had a procedural `PlanExecutor` recursing over plans;
rejected (maintainer decision): execution stays object-oriented. **Binding** is the flyweight operation:
it weds the intrinsic plan to one subject and yields the *existing* rule tree, which executes itself
through `MatchingRule::process()` exactly as today — the same relationship
`MatchConditionBlueprint::bind()` has to `MatchCondition`, one level up:

```
ClassMatchingPlan::bind(subject, ?ownerRule): MatchingRule
  → ObjectMatchingRuleSet + CompositeMatchingRule whose children are produced by iterating
    getPropertyPlans() and binding each — lazily, so process() short-circuiting still skips
    unbound (and uncompiled) properties

PropertyPlan::bind(objectRuleSet): MatchingRule
  → reads the property value (uninitialized ⇒ null, statics as today) → PropertyMatchingRuleSet;
    children: each CatchPlan bound (below); a nested-object rule — registry.getPlan(value::class)?->bind(…),
    the flyweight lookup by the VALUE's runtime class; per-item rules for non-empty iterables
    (ItemOfIterableMatchingRule, keyed property path)

CatchPlan::bind(propertyRuleSet): MatchExceptionRule
  → new MatchExceptionRule(owner, blueprint->bind(owner), formatterId, messageTemplate)
    // getCatchPlans() compiles lazily here — may throw InvalidMatchingPlanException
```

`MainExceptionMatcher::match()` becomes
`registry->getPlan($message::class)?->bind($message)->process($reciprocal)` — reciprocal semantics,
declaration-order priority, and short-circuiting all keep living where they live today: in the rules.

What survives, what dissolves:

- **Survives (the object-oriented execution)**: `CompositeMatchingRule`, `ObjectMatchingRuleSet`,
  `PropertyMatchingRuleSet`, `ItemOfIterableMatchingRule`, `MatchExceptionRule` — the rule tree and its
  `process()` polymorphism are untouched, and with them the owner-chain contract (`getPropertyPath()` /
  `getRootObject()` / `getValue()` / `getEnclosingObject()`) that formatters consume, reciprocal matching
  semantics including declaration-order priority and early exit once all exceptions are reciprocated, and
  nested participation requiring `#[Try_]` on the nested class (now expressed as a registry lookup by the
  *runtime class of the value* — same behavior as today's `ObjectMatchingRuleSetAssembler` on the value).
  Static properties keep participating exactly as today (pinned by an existing test).
- **Dissolves**: the four assembler/assembler-service layers (the procedural derivation of structure —
  plans now hold it, memoized) and `LazyMatchingRule` (the owner↔children construction dance is gone: a
  rule set materializes its children on demand, passing itself as owner, with the structure supplied by
  the plan). All `@internal`.

## Backward compatibility

| Surface | Strategy |
|---|---|
| `ExceptionMatcher`, `Try_`, `Catch_`, formatters, `MatchedException(List)` | untouched |
| `MatchCondition` (@api, custom conditions) | untouched — blueprints *produce* `MatchCondition`s |
| `MatchingRule` (@api, received by condition blueprints at `bind()`) | the existing rule sets keep implementing it; contract preserved |
| `MatchConditionCompiler` / `MatchConditionBlueprint` (@api, custom `match:` conditions) | untouched — already the extension point; `MatchConditionFactory` no longer exists (removed outright during 2.0 development — 2.0 is unreleased, so no adapter or deprecation window was needed). Custom conditions are lintable by construction: their `compile()` runs inside the plan compiler. Documented in `docs/config/match-conditions.md` |
| Container service ids | the `@internal` assembler-service ids are dropped without aliases (nothing outside the library can construct meaningful arguments for them); moving the 2.0-only `ConstantsAutoloadingCompilerPass`/`ConstantsClassLoader` needs no rector entry (they never shipped in 1.x) — just keep the *targets* of existing `upgrade/2.0.php` renames pointing at final FQCNs |

(Earlier drafts carried a legacy-factory adapter with a compile-time ghost dry-run. Both are gone:
`MatchConditionFactory` was removed outright while 2.0 is unreleased, so there is no legacy surface left
to adapt.)

## Deliberate behavior changes (stricter, to be changelogged)

1. Mapping errors are wrapped in `InvalidMatchingPlanException` (extends `LogicException`; original message
   kept verbatim as prefix, location appended, cause as `previous`) — a type change for code that caught raw
   `InvalidArgumentException` from `match()` for *mapping* mistakes. Value-dependent bind-time assertions
   (e.g. a non-stringable `uid_value` property value) are unchanged and stay raw.
2. Enum static checks (B11) fire even when the property value is null.
3. A property's catches compile once per process (memoized on success). Error *timing* is unchanged — the
   first match that **reaches** the property — and short-circuit dormancy is preserved: a broken later
   property stays dormant until a match reaches it. Surfacing dormant defects ahead of time is the
   linter's job, not the runtime's.

The first two convert "hidden" into "located and loud"; the linter is what makes every defect catchable
ahead of time in CI.

## Performance expectations

Per `match()` call today: full reflection walk + attribute instantiation + condition construction (partially
lazy). After: one plan compile per class per process; per match, only rule objects + value-bound conditions
are allocated (intrinsic conditions are shared). Long-running messenger workers benefit most. The phpbench
setup in the repo should capture before/after on: first match (expect ≈ parity) and repeated match of the
same class (expect a significant win). No file/opcache-level plan cache is proposed — attributes cannot
change within a process, and cross-process caching would buy little for the added invalidation complexity.
