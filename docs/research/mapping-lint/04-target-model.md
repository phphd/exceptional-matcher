# 04 — Target Domain Model: Compiled Matching Plans

This document describes the to-be model for Option B (see [03-design-options.md](03-design-options.md)).
All code below is a sketch conveying shape and responsibilities, not final naming or signatures.

## Ubiquitous language

| Term | Meaning | DDD / pattern role |
|---|---|---|
| **Mapping declaration** | The `#[Try_]` / `#[Catch_]` attributes as authored on a class | source text |
| **Matching plan** | The compiled, *validated* mapping of one class: which properties catch what, under which conditions, formatted how | immutable value / **flyweight (intrinsic state)** |
| **Plan compiler** | Turns a declaration into a plan; the **single validation boundary** — every catalog check runs here or in a value object it constructs | anti-corruption boundary; "parse, don't validate" |
| **Plan registry** | `getPlan(class-string): ?MatchingPlan`, compile-on-first-use, per-process cache | **flyweight factory** |
| **Match scope** | The extrinsic context of one match attempt: subject instance, property value, owner chain, property path | extrinsic state |
| **Plan executor** | Walks a plan against a scope and an `ExceptionReciprocal` | domain service |
| **Diagnostic** | One mapping defect with location (class, property, attribute ordinal) and severity | reporting value |
| **Diagnostic sink** | Strategy deciding whether compilation throws (runtime) or collects (lint) | policy |

The word *plan* is chosen over "metadata"/"blueprint-of-everything" because it names what the object is
*for*: a precomputed course of action the executor follows. `compile` pairs with it naturally.

## Plan structure (intrinsic state)

```php
final class MatchingPlan
{
    /** @param list<PropertyPlan> $propertyPlans  declaration order preserved — matching order is semantics */
    public function __construct(
        public readonly string $className,
        public readonly array $propertyPlans,
    ) {}
}

final class PropertyPlan
{
    /** @param list<CatchPlan> $catchPlans */
    public function __construct(
        public readonly ReflectionProperty $property,   // value reader; plans are per-process, no serialization concern
        public readonly array $catchPlans,
    ) {}
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

## Value objects: invariants at construction

The reference checks (catalog B1–B7) move out of factories/conditions into small value objects whose
constructors enforce existence — *make invalid mappings unrepresentable*:

| Value object | Extracted from | Invariants |
|---|---|---|
| `CaughtExceptionType` | new (fixes silent B1/D4) | class or interface exists; a class must implement `Throwable` (interfaces exempt) |
| `ExceptionOrigin` | `ExceptionOriginMatchCondition::__construct` (the asserts move; the condition then *receives* a valid VO and shrinks to pure trace-matching) | class exists; method / property hook / function exists; at least one component set |
| `IfMethodReference` | `SimpleIfClosureMatchConditionFactory` | method exists; (warning-level) parameter compatible with `CaughtExceptionType`, returns `bool` |

Each rule keeps exactly one home; runtime conditions consume already-validated VOs instead of re-checking.

## Conditions: blueprint (compile) vs binding (match)

The heart of the flyweight split. Today `MatchConditionFactory::getCondition(Catch_ $catch, MatchingRule
$owner)` mixes static validation with value capture. It splits into:

```php
/** Intrinsic half — compile time. This is where validation lives. */
interface CatchConditionCompiler
{
    /** @throws MappingDefect on any statically detectable error */
    public function compile(Catch_ $catch, PropertyCompileContext $context): ?MatchConditionBlueprint;
}

/** Bridge to runtime — bind time. NO mapping validation here, only value binding. */
interface MatchConditionBlueprint
{
    public function bind(MatchScope $scope): MatchCondition;   // MatchCondition (@api) stays as-is
}
```

`PropertyCompileContext` carries intrinsic facts only: `ReflectionClass`, `ReflectionProperty`, declared
type. `MatchScope` carries extrinsic facts: value, enclosing object, root object, property path — and
*implements the existing `MatchingRule` interface*, which is what makes legacy support cheap (below).

Per built-in condition:

| Condition | Compile-time (validated once, shared) | Bind-time |
|---|---|---|
| exception class | `CaughtExceptionType` | — condition is itself intrinsic: **one shared instance per plan**, `bind()` returns it |
| origin (`from:`) | `ExceptionOrigin` | — intrinsic, shared instance |
| `if:` closure | `IfMethodReference` (+ D3 signature warning) | rebind `[class, method]` to the scope's enclosing instance |
| `match: enum_value` | `ValueError` subtype guard; `from:` is `BackedEnum` + `'from'` (B11 — now *structurally* unconditional); D1 type warning | null value ⇒ `FalseCondition`; coerce value `int\|string\|Stringable` |
| `match: uid_value` | subtype guard; D2 type warning | null ⇒ `FalseCondition`; coerce stringable |
| `match: exception_value` / `validated_value` | subtype guards | capture scope value |
| `match:` custom id | registry `has()` (B8) | delegate (see BC below) |
| composite | compose child blueprints; `FalseCondition` short-circuit moves to bind | evaluate as today |

`format:` is validated by the compiler against the same tagged-locator registry the delegating formatter
uses (B10) — the registry stays the single source of *contents*; the formatter's own `has()` check remains
as a safety net for containers assembled without the compiler.

## The compiler and diagnostic sinks

One traversal, two policies:

```php
final class MappingPlanCompiler
{
    public function compile(ReflectionClass $class, DiagnosticSink $sink): ?MatchingPlan
    {
        // structural pass: C1 Catch_ without Try_ (error), C2 abstract (warning),
        //                  C3 no catches (warning), C4 parent-private Catch_ (warning)
        // per property, per Catch_ attribute:
        //   try { newInstance(); conditionCompilers->compile(...); assert formatter registered; }
        //   catch (Throwable $e) { $sink->report(Diagnostic::fromThrowable($location, $e)); }
    }
}

interface DiagnosticSink { public function report(MappingDiagnostic $diagnostic): void; }

final class ThrowingSink   { /* runtime: first error-severity diagnostic → throw  */ }
final class CollectingSink { /* lint: accumulate everything, compiler continues with the next Catch_ */ }
```

Notes:

- **Per-`Catch_` isolation** is what gives lint its granularity *and* keeps individual compilers simple:
  they stay plain `Assert`-style throwing code (single implementation), while the sink decides fail-fast vs
  collect. Catalog entry B9 (undefined `match:` constant ⇒ `Error` at `newInstance()`) is caught by the same
  isolation — hence `catch (Throwable)`, not `catch (InvalidArgumentException)`.
- The constants-autoloading closure (currently triggered by `ObjectMatchingRuleSetAssemblerService`) moves to
  the compiler — the guarantee "constants are loaded before any attribute instantiation" holds for both
  runtime and lint by construction.

## The registry (flyweight factory) and executor

```php
final class PlanRegistry
{
    /** @var array<class-string, ?MatchingPlan> */
    private array $plans = [];

    public function getPlan(string $className): ?MatchingPlan;   // null ⇔ no #[Try_]; compiles once (ThrowingSink)
}
```

The executor replaces the assembler tree walk. Key simplification: today's graph needs *bidirectional*
owner↔children links (hence the `LazyMatchingRule` self-reference dance); the executor drives parent→child
traversal itself as recursion, so materialized scopes only need child→owner links:

```
execute(plan, subject, reciprocal, ownerScope): bool
  scope = ObjectScope(subject, ownerScope)                       // implements MatchingRule
  foreach plan.propertyPlans as propertyPlan:
    value = read(propertyPlan.property, subject)                 // uninitialized ⇒ null, as today
    propertyScope = PropertyScope(scope, name, value)
    foreach propertyPlan.catchPlans as catchPlan:
      rule = new MatchExceptionRule(propertyScope, catchPlan.condition.bind(propertyScope),
                                    catchPlan.formatterId, catchPlan.messageTemplate)
      if rule.process(reciprocal): return true                   // reciprocal semantics unchanged
    if value is object:                                          // nested: flyweight lookup by VALUE class
      nestedPlan = registry.getPlan(value::class)
      if nestedPlan && execute(nestedPlan, value, reciprocal, propertyScope): return true
    if value is non-empty iterable:                              // per object item, keyed scope (property path `[key]`)
      ... ItemScope(key) ... execute(itemPlan, item, ...)
  return false
```

What survives, what dissolves:

- **Survives unchanged (public behavior)**: `MatchExceptionRule` as the artifact inside `MatchedException`;
  the owner-chain contract (`getPropertyPath()` / `getRootObject()` / `getValue()` / `getEnclosingObject()`)
  that formatters consume; reciprocal matching semantics including declaration-order priority and
  early exit once all exceptions are reciprocated; nested participation requiring `#[Try_]` on the nested
  class (now expressed as a registry lookup by the *runtime class of the value* — same behavior as today's
  `ObjectMatchingRuleSetAssembler` on the value).
- **Dissolves**: `LazyMatchingRule`, the property-rules generator, `CompositeMatchingRule` as a structural
  node, the four assembler/assembler-service layers. The existing scope-like classes
  (`ObjectMatchingRuleSet`, `PropertyMatchingRuleSet`, `ItemOfIterableMatchingRule`) either *become* the
  scope implementations (likely just losing their `process()`/`$ruleSet` halves) or are replaced by
  equivalents — they are `@internal`.

## Backward compatibility

| Surface | Strategy |
|---|---|
| `ExceptionMatcher`, `Try_`, `Catch_`, formatters, `MatchedException(List)` | untouched |
| `MatchCondition` (@api, custom conditions) | untouched — blueprints *produce* `MatchCondition`s |
| `MatchingRule` (@api, seen by custom factories as `$owner`) | scopes implement it; contract preserved |
| `MatchConditionFactory` (@api, custom `match:` factories) | **adapter blueprint**: at compile, dry-run `getCondition($catch, $compileScope)` (null value, ghost enclosing object) so the factory's own assertions surface in lint; at bind, delegate with the real scope. Deprecate in favor of `CatchConditionCompiler`; rector rule candidate for `upgrade/ExceptionalMatcherSetList` |
| Container service ids | the assembler-service ids either alias the new services for one minor version or are dropped — they are `@internal`; verify against the BC-break compiler-pass checks in `PhdExceptionalMatcherExtension::process()` |

The compile-time dry-run of *legacy* factories is the one place the ghost-object trick from Option A
survives — scoped to a deprecation window, and only for factories not yet migrated to the compiler API.

## Deliberate behavior changes (stricter, to be changelogged)

1. A broken `#[Catch_]` anywhere in a class now fails the *whole class* on first match attempt — today it
   can hide behind short-circuiting forever. This is the point of the exercise.
2. Enum static checks (B11) fire even when the property value is null.
3. `exception:` classes that don't exist (B1) or plain non-throwable classes (D4) now throw instead of
   silently never matching.

All three convert "silently wrong in production" into "loud at first use, and catchable ahead of time by
lint / CI".

## Performance expectations

Per `match()` call today: full reflection walk + attribute instantiation + condition construction (partially
lazy). After: one plan compile per class per process; per match, only scope objects + value-bound conditions
are allocated (intrinsic conditions are shared). Long-running messenger workers benefit most. The phpbench
setup in the repo should capture before/after on: first match (expect ≈ parity) and repeated match of the
same class (expect a significant win). No file/opcache-level plan cache is proposed — attributes cannot
change within a process, and cross-process caching would buy little for the added invalidation complexity.
