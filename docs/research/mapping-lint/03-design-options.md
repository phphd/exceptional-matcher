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
   `build()`s `LazyMatchingRule`s and exhausts generators, so factory assertions fire without `process()`.
3. The linter itself: discovery → ghost → walk → catch assertion → diagnostic.

Properties:

- ✅ Zero duplicated rules — the runtime factories *are* the linter. Custom `match:` factories are linted
  for free (their own assertions fire).
- ✅ Small, low-risk change set.
- ❌ **Granularity**: an assertion thrown mid-generator kills the generator — remaining properties of that
  class go unreported. First-error-per-class only.
- ❌ **Coverage holes**: abstract classes and enums cannot be ghosted; checks hidden behind value-dependent
  early returns rely on a fragile ordering *convention* in factories rather than a structural guarantee.
- ❌ Fixes nothing about the runtime: mapping errors still surface mid-match in production; reflection is
  still re-done per call.
- ❌ The walker + `getRules()` exposure is throwaway once the model is improved.

## Option B — Flyweight split: compile plans, bind values ⭐ recommended

Separate intrinsic from extrinsic state (see [02-current-architecture.md](02-current-architecture.md)):

- **`MatchingPlan`** — immutable, validated, per-class mapping structure (flyweight), produced by a
  **`MappingPlanCompiler`** and cached by a **`PlanRegistry`** (flyweight factory).
- **Execution** — a per-match walk that binds a plan to the subject instance and exception list, materializing
  the existing owner-chain objects (`ObjectMatchingRuleSet`, `PropertyMatchingRuleSet`, `MatchExceptionRule`)
  exactly as today, so formatters and the public `MatchedException` contract are untouched.
- **All validation happens at compile.** Conditions split into a static half (validated blueprint) and a
  bind-time half (value binding). The compiler emits into a `DiagnosticSink`: a throwing sink for runtime
  (fail fast on first use of a broken class), a collecting sink for lint (all errors, per-`Catch_`
  granularity).

Then the linter is, definitionally, *the compiler run over discovered classes with a collecting sink* —
single source of truth holds by construction, not by discipline.

Properties:

- ✅ Single source of truth is structural: lint and runtime execute the same compiler code path.
- ✅ Per-`Catch_` error granularity (each attribute compiles in isolation).
- ✅ Works for abstract classes (no instantiation), and static checks cannot hide behind value branches —
  the API shape makes the B11 class of bug unrepresentable.
- ✅ Deterministic fail-fast at runtime: a broken mapping throws on *first* match attempt of that class,
  regardless of short-circuiting.
- ✅ Performance: reflection + attribute instantiation + static condition construction happen once per class
  per process instead of per `match()` call — relevant for messenger workers; measurable with the existing
  phpbench setup.
- ✅ Dissolves accidental complexity: `LazyMatchingRule` and the generator dance lose their reason to exist
  (they amortized re-derivation of intrinsic state).
- ❌ The largest refactoring of the library core since inception — mitigated by the fact that everything
  touched is `@internal` and by the existing behavioral test suite.
- ⚠️ Custom `MatchConditionFactory` implementations need a BC adapter (dry-run at compile + delegate at
  bind); a new compiler-style extension interface becomes the documented path.

## Option C — Parallel static validator (separate visitor over attributes)

A standalone `MappingValidator` that re-implements each check against reflection + attributes, never touching
the runtime pipeline. This is what several ecosystem tools do (e.g. Doctrine's `orm:validate-schema` is
separate from runtime metadata loading).

- ✅ Cheapest to bolt on; zero runtime risk.
- ❌ **Violates the core requirement**: every rule exists twice (factory + validator); every new condition
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
| Error granularity | per class | **per `Catch_`** | per check | per class |
| Catches B11-style ordering-hidden checks | ⚠️ needs convention | ✅ structurally | ✅ | ❌ |
| Abstract classes / no-instantiation coverage | ❌ | ✅ | ✅ | ❌ |
| Custom factories linted | ✅ | ✅ (adapter dry-run) | ❌ | ✅ |
| Runtime failure timing improved | ❌ | ✅ fail-fast at first use | ❌ | ❌ |
| Runtime performance | unchanged | **improved** (cached plans) | unchanged | unchanged |
| False positives | none known | none known | drift over time | ❌ TypeError class |
| BC risk | minimal | moderate (internal-only + adapter) | none | none |
| Effort | S | **L** | M | XS |
| Long-term fit | throwaway | **destination** | debt | none |

## Recommendation

**Option B**, staged so that value ships early (see [06-migration-plan.md](06-migration-plan.md)). The two
Phase-0 fixes (enum ordering hoist, `exception:` existence check) are zero-regret under every option and
should ship first regardless.

Precedent: this is the `lint:container` philosophy — Symfony does not maintain a second validator for the
container; *compiling* the container **is** the validation, and the lint command is just "compile with
stricter reporting". The flyweight split gives this library the same property: compiling the mapping plan is
the validation.
