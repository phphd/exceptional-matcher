# 02 — Current Architecture (As-Is)

How matching works today, where validation lives, and why a linter cannot be bolted on without touching the
model.

## Runtime pipeline

Entry point — `src/ExceptionalMatcher/MainExceptionMatcher.php`:

```
match(Throwable $exception, object $message)
 ├── assemble rule tree for $message            ← per CALL, per INSTANCE
 │     ObjectMatchingRuleSetAssemblerService
 │       └── ObjectMatchingRuleSetAssembler($message)
 │             ├── has #[Try_]? otherwise null
 │             └── CompositeMatchingRule(
 │                    ObjectMatchingRuleSet($message, …),
 │                    getPropertyRules(…)        ← Generator, NOT yet iterated
 │                 )
 ├── unwrap $exception into a list (ExceptionToolkit)
 └── $ruleSet->process(new ExceptionReciprocal($list))
       └── CompositeMatchingRule::process()
             foreach rules                       ← Generator iterated HERE
               ├── PropertyMatchingRuleSetAssembler::assemble()
               │     ├── reads the property VALUE from $message   ← instance-bound
               │     └── PropertyMatchingRulesAssembler
               │           foreach #[Catch_] attribute
               │             ├── $attribute->newInstance()        ← shape asserts fire
               │             └── MatchConditionFactory::getCondition(…)  ← reference asserts fire
               ├── PropertyNestedValidObjectRuleAssemblerService   ← recurses into property VALUE
               └── PropertyNestedValidIterableRulesAssemblerService
             short-circuit: return true as soon as a child reports
             all exceptions reciprocated         ← later properties never assembled
```

Two properties of this pipeline matter for lint:

1. **Validation timing.** Every assertion — `Catch_` shape checks, `ExceptionOriginMatchCondition`
   existence checks, `SimpleIfClosureMatchConditionFactory::methodExists`, `DelegatingMatchConditionFactory`
   registry lookup — fires inside `process()`, i.e. *while a real exception is being handled in production*.
   The `format:` check fires even later (`DelegatingMatchedExceptionFormatter::format()`, after a successful
   match).
2. **Instance coupling.** `PropertyMatchingRuleSetAssembler` reads property values
   (`ReflectionProperty::getValue`); nested recursion is driven by *actual values*, not declared types;
   several condition factories consume `$owner->getValue()` / `$owner->getEnclosingObject()`. There is no
   code path that derives the mapping from a *class* alone.

## Where validation lives today

| Check | Location | Trigger |
|---|---|---|
| `if:` / `from:` arity | `Catch_::__construct` | attribute `newInstance()` (mid-match) |
| origin class/method/hook/function existence | `ExceptionOriginMatchCondition::__construct` | condition creation (mid-match) |
| `if:` method existence | `SimpleIfClosureMatchConditionFactory` | condition creation (mid-match) |
| `match:` id registered | `DelegatingMatchConditionFactory` | condition creation (mid-match) |
| enum `from:` is a `BackedEnum` + `'from'` method | `EnumValueMatchConditionFactory` | condition creation, **only when property value ≠ null** (the null early-return precedes the static checks) |
| exception subtype guards (enum/uid/value/validated) | respective factories | condition creation (mid-match) |
| `format:` formatter registered | `DelegatingMatchedExceptionFormatter` | formatting (post-match) |
| `exception:` class exists + is `Throwable` | `ExceptionClassMatchCondition::__construct` | condition creation (mid-match) |
| structural mistakes (C1–C4 in the catalog) | — | **never** |

The key observation: the *rules themselves* are already centralized and well-factored — one condition type,
one factory, one set of assertions. What is missing is not validation logic but an execution mode that runs
it (a) eagerly and exhaustively, (b) without an instance. (The thrown exceptions themselves are fine — the
lint command reports them as thrown; no collecting mode is needed.)

## The diagnosis: intrinsic and extrinsic state are fused

For a given message **class**, the following never changes between instances:

- presence of `#[Try_]`, the property list, each property's `#[Catch_]` attributes;
- each catch's exception class, origin reference, `if:` method reference, `match:` id, `format:` id,
  message template;
- the *static half* of every condition (e.g. `ExceptionOriginMatchCondition` is fully determined by the
  attribute — it never looks at the message at all).

This is **intrinsic** state in Flyweight terms. Per-match **extrinsic** state is only: the subject instance,
its property values, and the exception list.

Today both live in one object graph (`ObjectMatchingRuleSet` holds the instance, `PropertyMatchingRuleSet`
holds the value, `MatchExceptionRule` holds a condition that may have captured a value). Consequences:

- **The graph cannot exist without an instance** → nothing to lint against.
- **The graph is rebuilt from reflection on every `match()` call** → `new ReflectionClass`, `getProperties()`,
  `getAttributes()`, `newInstance()` per attribute, factory calls — per failed message. `LazyMatchingRule`
  and the `getPropertyRules()` generator exist to *amortize* this cost by deferring and short-circuiting;
  they are workarounds for re-deriving intrinsic state, and they are exactly what makes error surfacing
  non-deterministic (a broken `#[Catch_]` on property 5 never throws if property 1 matched everything).
- **Bidirectional owner↔children references** require the `LazyMatchingRule` self-reference dance during
  construction.

## Supporting machinery worth preserving

- **Constants autoloading** (`ConstantsAutoloadingCompilerPass` → closure invoked on first
  `ObjectMatchingRuleSetAssemblerService::assemble()`): `match: enum_value`-style constants must be defined
  *before* any `Catch_` attribute is instantiated. Any compile/lint path must keep this guarantee — going
  through the same service (or hoisting the closure into the future compiler) gets it for free; a naive
  hand-rolled reflection walk would fatal with `Undefined constant`.
- **Owner chain contract.** Formatters (e.g. `MainExceptionViolationFormatter`) consume the matched rule's
  `getPropertyPath()` / `getRootObject()` / `getValue()` — the extrinsic owner chain must survive any
  refactoring, since `MatchedException` carries it into user-facing formatting.
- **Standalone (non-Symfony) usage.** `PhdExceptionalMatcherExtension::getContainer()` builds a
  self-contained container; the linter core must be a plain service usable there, with the console command
  as an optional layer.

## Change surface

`@api` (must not break): `ExceptionMatcher`, `Try_`, `Catch_`, `MatchCondition`, `MatchConditionFactory`
(custom conditions are a documented extension point — see `docs/config/match-conditions.md`),
`MatchedExceptionFormatter` / violation formatters, `MatchingRule` (consumed by custom factories via
`$owner`), `MatchedException(List)`.

`@internal` (free to restructure): all assemblers and assembler services, `CompositeMatchingRule`,
`LazyMatchingRule`, `ObjectMatchingRuleSet`, `PropertyMatchingRuleSet`, `ItemOfIterableMatchingRule`,
`MatchExceptionRule`, `ExceptionReciprocal`, `MainExceptionMatcher`, the delegating factory/formatter.

This split is what makes the flyweight refactoring feasible: the entire fused graph is internal.
