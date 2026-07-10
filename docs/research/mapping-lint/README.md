# Research: Mapping Lint Command & Single Source of Truth

> Status: research complete; implementation planned as a staged, always-green commit sequence \
> Scope: `lint:exceptional-matcher` console command + domain model evolution required to support it

## The Ask

Introduce a console command (in the spirit of `lint:yaml`, `lint:twig`, `lint:container`) that fails on
**every mapping error that is statically detectable** — nonexistent `exception:` classes, missing `from:`
methods / property hooks, missing `if:` methods, unknown `match:` / `format:` ids, and so on — without
executing any user code path.

Hard requirement: **no duplication of validation rules**. Every rule must have exactly one authoritative
implementation, executed by both the runtime matcher and the linter. Refactoring of the core model is
explicitly allowed; code quality is the priority.

## Executive Summary

The library already contains almost all of the needed validation — in condition factories and condition
constructors. The problem is *when* and *against what* it runs:

1. **When**: validation fires lazily, in the middle of matching a real production exception — the worst
   possible moment to discover a typo. Worse, `CompositeMatchingRule::process()` short-circuits, so a broken
   `#[Catch_]` on a later property may *never* fire at all.
2. **Against what**: the assembly pipeline is fused to a *message instance* (property values are read during
   assembly). A linter has class names, not instances.

The root cause is a modeling gap: the rule tree mixes **intrinsic state** (mapping structure derived from
class + attributes — identical for every instance of a class) with **extrinsic state** (property values of
one particular message, one particular exception list). `LazyMatchingRule` and the generator dance exist
precisely to defer the cost of re-deriving intrinsic state on every match — they are symptoms, not design.

The recommended direction is a **Flyweight split** of the domain model:

| Concept | Role | Lifetime |
|---|---|---|
| `MatchingPlan` | compiled, validated mapping of one class (intrinsic) | cached per class (flyweight) |
| `MappingPlanCompiler` | the *single* validation boundary — all assertions live here or in the condition constructors it invokes | stateless service |
| `PlanRegistry` | flyweight factory: `getPlan(class-string)` with per-process cache | service |
| Match execution | binds a plan to a subject instance + exception list (extrinsic) | transient per match |

With this split, **"the mapping compiles" ⇔ "the mapping is valid"**, and:

- **Runtime** = compile (cached) + execute. Same errors, but fail-fast and only once per class.
- **Lint** = compile every discovered class with a collecting defect handler instead of a throwing one.
  Zero duplicated rules by construction — lint literally runs the same compiler.
- **Bonus**: reflection + attribute instantiation stop being re-done on every `match()` call — a measurable
  performance win for long-running workers (messenger middleware).

## Document Map

| Document | Contents |
|---|---|
| [01-failure-catalog.md](01-failure-catalog.md) | Exhaustive inventory of mapping failure modes: current behavior, static detectability, target home for each check |
| [02-current-architecture.md](02-current-architecture.md) | As-is walkthrough: assembly pipeline, where validation lives, why lint cannot be bolted on cleanly |
| [03-design-options.md](03-design-options.md) | Four options evaluated (ghost dry-run, flyweight plan, parallel validator, probe exception) with decision matrix |
| [04-target-model.md](04-target-model.md) | The proposed domain model in depth: ubiquitous language, plan structure, condition blueprints, compiler, executor, BC strategy |
| [05-lint-command.md](05-lint-command.md) | Command UX, class discovery, defect severities and codes, cache-warmup integration, standalone usage |
| [06-migration-plan.md](06-migration-plan.md) | Staged implementation plan, testing strategy, behavior changes, risks, open questions |

## Recommendation (TL;DR)

Adopt **Option B — the Flyweight plan split** ([03-design-options.md](03-design-options.md)), staged as:

1. A zero-regret centralization fix shipped immediately (enum check ordering). The `exception:` existence
   check originally planned alongside it turned out to already exist in
   `ExceptionClassMatchCondition::__construct`.
2. Plan model introduced *behind* the existing `@api` surface — `ExceptionMatcher`, `Try_` / `Catch_`,
   `MatchCondition`, formatters, and the matched-rule owner chain are untouched; existing tests stay green.
3. Condition compiler API with a BC adapter for legacy `MatchConditionFactory` implementations.
4. `MappingLinter` core service + `lint:exceptional-matcher` command on top.

Option A (ghost-object dry-run) is documented as a viable low-cost fallback, but it is throwaway work:
everything it needs (walk exposure, ordering fixes) is subsumed by Option B, while its limitations
(per-class error granularity, no abstract-class coverage, runtime cost unchanged) disappear under B.
