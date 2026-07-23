# 03 — Design Options

Four candidate designs were evaluated against the requirements: single source of truth for every rule,
"fail on everything that can possibly fail", quality over expedience.

## Option A — Ghost-object dry-run over the existing pipeline

Instantiate every discovered `#[Try_]` class via `ReflectionClass::newInstanceWithoutConstructor()` (a
"ghost": all properties uninitialized ⇒ read as `null`), run the *real* assembly pipeline on it, and force
the lazy parts to evaluate. Uninitialized values make value-dependent branches take their null path
(`FalseCondition`) instead of crashing; nested recursion simply does not happen, which is fine because
nested classes need their own `#[Try_]` and are linted as independent roots.

Required changes:

1. Hoist the enum static checks above the null-value early return (catalog B11) — otherwise ghosts skip them.
2. Expose traversal (e.g. `getRules()` on `CompositeMatchingRule`) plus a small recursive walker that
   `build()`s `LazyMatchingRule`s and exhausts generators, so compiler assertions fire without `process()`.
3. The linter itself: discovery → ghost → walk → catch assertion → defect report.

Properties:

- ✅ Zero duplicated rules — the runtime condition compilers *are* the linter. Custom `match:` compilers
  are linted for free (their own assertions fire).
- ✅ Small, low-risk change set.
- ❌ **Granularity**: an assertion thrown mid-generator kills the generator — remaining properties of that
  class go unreported. First-error-per-class only.
- ❌ **Coverage holes**: abstract classes and enums cannot be ghosted; checks hidden behind value-dependent
  early returns rely on a fragile ordering *convention* rather than a structural guarantee.
- ❌ Fixes nothing about the runtime: mapping errors still surface mid-match in production; reflection is
  still re-done per call.
- ❌ The walker + `getRules()` exposure is throwaway once the model is improved.

## Option B — Flyweight split: compile plans, bind values ⭐ recommended

Separate intrinsic from extrinsic state (see [02-current-architecture.md](02-current-architecture.md)):

- **`ClassMatchingPlan`** — validated, per-class mapping structure (flyweight), materialized gradually
  (property plans on first iteration, catch plans on first access) and memoized, produced by a
  **`MappingPlanCompiler`** and cached by a **`PlanRegistry`** (flyweight factory).
- **Execution** — plans `bind()` to the subject instance, materializing the existing owner-chain rule
  objects (`ObjectMatchingRuleSet`, `PropertyMatchingRuleSet`, `MatchExceptionRule`) exactly as today; the
  rule tree keeps executing itself via `process()` (no executor service — object-oriented execution is
  preserved), so formatters and the public `MatchedException` contract are untouched.
- **All validation happens at compile.** Conditions split into a static half (validated blueprint) and a
  bind-time half (value binding). The compiler has exactly one failure behavior — the production one:
  throw `InvalidMatchingPlanException` on the first defect, location attached. There is deliberately no
  reporting-policy parameter (no `DefectHandler` strategy).

Then the linter is, definitionally, *the compiler run eagerly over every property of every discovered
class*, with the thrown exceptions turned into a report — single source of truth holds by construction,
not by discipline.

Properties:

- ✅ Single source of truth is structural: lint and runtime execute the same compiler code path.
- ✅ First-error-per-property granularity (the linter catches the compile exception per property and keeps
  walking) — the `lint:yaml` model: fix, re-run.
- ✅ Works for abstract classes (no instantiation), and static checks cannot hide behind value branches —
  the API shape makes the B11 class of bug unrepresentable.
- ✅ Deterministic surfacing at runtime: a property a match *reaches* fails at compile of that property —
  never hidden behind value-dependent branches. Properties a match never reaches stay dormant
  (short-circuit economics preserved); surfacing those ahead of time is exactly the linter's job.
- ✅ Performance: reflection + attribute instantiation + static condition construction happen once per class
  per process instead of per `match()` call — relevant for messenger workers; measurable with the existing
  phpbench setup.
- ✅ Dissolves accidental complexity: `LazyMatchingRule` and the generator dance lose their reason to exist
  (they amortized re-derivation of intrinsic state).
- ❌ The largest refactoring of the library core since inception — mitigated by the fact that everything
  touched is `@internal` and by the existing behavioral test suite.
- ✅ The extension surface is already compiler-style: `MatchConditionCompiler` replaced the old
  `MatchConditionFactory` outright during 2.0 development (2.0 is unreleased — no adapter or deprecation
  window needed), so custom conditions are lintable by construction.

## Option C — Parallel static validator (separate visitor over attributes)

A standalone `MappingValidator` that re-implements each check against reflection + attributes, never touching
the runtime pipeline. This is what several ecosystem tools do (e.g. Doctrine's `orm:validate-schema` is
separate from runtime metadata loading).

- ✅ Cheapest to bolt on; zero runtime risk.
- ❌ **Violates the core requirement**: every rule exists twice (compiler + validator); every new condition
  must be implemented twice; drift between the two is a matter of time — precisely the failure mode of this
  approach in other ecosystems.
- ❌ Custom user factories are invisible to it unless users also write a parallel validator.

Rejected.

## Option D — Probe-exception traversal (zero-refactor hack)

Call `$ruleSet->process(new ExceptionReciprocal([new LintProbeException()]))` with a sentinel exception that
matches nothing: since nothing ever reciprocates, `CompositeMatchingRule::process()` never short-circuits and
provably walks the *entire* tree, firing every lazy assembly and assertion.

- ✅ Genuinely zero changes to the library; validated as mechanically sound.
- ❌ **Unsafe**: `CompositeMatchCondition` guards user callbacks only by condition order. A mapping like
  `#[Catch_(Throwable::class, if: [...])]` passes the class guard, and the user's `if:` method — typed
  against *their* exception — receives the probe ⇒ `TypeError` ⇒ false positive. Similar hazards exist for
  broad `exception:` types combined with any value-dependent condition.
- ❌ Conflates matching semantics with validation; depends on `process()` implementation details.

Rejected as primary mechanism; documented because it demonstrates that full-tree forcing is possible and
cheap if ever needed for a quick diagnostic.

## Decision matrix

| Criterion | A ghost dry-run | B flyweight plans | C parallel validator | D probe hack |
|---|---|---|---|---|
| Single source of truth | ✅ by convention | ✅ **by construction** | ❌ | ✅ |
| Error granularity | per class | **first error per property** | per check | per class |
| Catches B11-style ordering-hidden checks | ⚠️ needs convention | ✅ structurally | ✅ | ❌ |
| Abstract classes / no-instantiation coverage | ❌ | ✅ | ✅ | ❌ |
| Custom conditions linted | ✅ | ✅ | ❌ | ✅ |
| Runtime failure timing improved | ❌ | ✅ deterministic at first reach | ❌ | ❌ |
| Runtime performance | unchanged | **improved** (cached plans) | unchanged | unchanged |
| False positives | none known | none known | drift over time | ❌ TypeError class |
| BC risk | minimal | moderate (internal-only + adapter) | none | none |
| Effort | S | **L** | M | XS |
| Long-term fit | throwaway | **destination** | debt | none |

## Recommendation

**Option B**, staged so that value ships early (see [06-migration-plan.md](06-migration-plan.md)). The
Phase-0 fix (enum ordering hoist) is zero-regret under every option and should ship first regardless (the
`exception:` existence check originally planned alongside it already exists in
`ExceptionClassMatchCondition::__construct`).

Precedent: this is the `lint:container` philosophy — Symfony does not maintain a second validator for the
container; *compiling* the container **is** the validation, and the lint command is just "compile with
stricter reporting". The flyweight split gives this library the same property: compiling the mapping plan is
the validation.
