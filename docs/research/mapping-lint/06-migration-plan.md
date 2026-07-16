# 06 — Migration Plan

Staged so that each phase ships value on its own and the risky rewrite is bracketed by the existing
behavioral test suite.

## Phase 0 — zero-regret fixes (ship immediately, independent of everything else)

1. **Hoist enum static checks** above the null-value early return (catalog B11) — **already shipped**: the
   compile/bind split runs them unconditionally in `EnumValueMatchConditionCompiler::compile()`, before any
   value handling.

*(The `exception:` existence check originally planned here turned out to already exist —
`ExceptionClassMatchCondition::__construct` rejects nonexistent/non-Throwable types; B1/D4 were
mis-cataloged as silent.)*

## Phase 1 — plan model behind existing seams (internal rewrite)

- Introduce `MatchingPlan` / `PropertyPlan` / `CatchPlan`, `MappingPlanCompiler` (throws
  `InvalidMatchingPlanException` — production behavior is its only behavior; no defect-handler parameter),
  `PlanRegistry`, and the executor ([04-target-model.md](04-target-model.md)).
- Built-in conditions already implement `MatchConditionCompiler` (+ blueprints) — shipped; what remains is
  invoking `compile()` from the plan compiler once per class instead of from the per-match assembler
  (no value-object extraction — the condition constructors/compilers are the single validation home).
- Rewire `MainExceptionMatcher` to registry + executor.
- Delete the assembler layer, `LazyMatchingRule`, and the generator walk once green.

**Definition of done**: the entire existing test suite passes unchanged (`ServiceTest` / integration /
`BCBreakUnitTest` conventions) — it is the behavioral contract for matching semantics: declaration-order
priority, first-match-wins reciprocation, nested `#[Try_]` objects, iterable items, uninitialized-property
handling, formatter owner-chain data.

## Phase 2 — public extension API

**Already shipped**: `MatchConditionCompiler` (+ `MatchConditionBlueprint`) replaced `MatchConditionFactory`
outright, and `docs/config/match-conditions.md` documents the compiler/blueprint API with the "static
checks in compile, value capture in bind" convention. No adapter and no deprecation window were needed —
2.0 is unreleased, so BC breaks are allowed as long as `upgrade/2.0.php` stays current (its rename
*targets* must always point at final FQCNs; classes introduced during 2.0 development, like the `Autoload`
pair, need no entries when moved).

## Phase 3 — linter and command

- `MappingLinter` core (eager per-property compile in try/catch + the structural checks C1–C4/B10; compile
  failures reported as-is, no defect codes), `lint:exceptional-matcher` command, discovery via
  `composer/class-map-generator` ([05-lint-command.md](05-lint-command.md)).
- composer: `symfony/console` + `composer/class-map-generator` into `require-dev`/`suggest`; extend
  `conflict` envelope for console.
- Docs: README section, a `docs/config/` page for the command, "lint as a unit test" recipe for standalone
  users.
- Dogfood: lint the library's own `src/**/Tests/Stub/` classes in CI; intentionally-broken stubs assert
  their specific failure messages.

## Phase 4 — optional follow-ups

- Cache-warmup validation behind bundle config (`lint.paths`).
- Type-compatibility warnings D1–D3.
- phpbench before/after publication (first-match vs repeated-match of the same class).
- `--format=github` annotations.

## Testing strategy

- **Behavior parity**: rely on existing feature-local suites; extend existing test classes rather than
  creating parallel ones; stubs live in the feature's `Tests/Stub/` directory (established repo convention).
- **Compiler defects**: one test per catalog id, each driving a broken stub and asserting the
  `InvalidMatchingPlanException` message + location; PHP-version-dependent
  cases (property hooks, B4) use `markTestSkipped` on older runtimes.
- **Custom compilers**: a stub custom `MatchConditionCompiler` asserting (a) end-to-end matching works with
  the real rule at bind time, (b) its `compile()` assertions surface in lint.
- **Static analysis**: CI runs PHPStan/Psalm on 8.1 + 8.5 × highest/lowest deps — reflection-of-hooks code
  needs the same version guards as `ExceptionOriginMatchCondition::propertyHookExists()`; prefer inline
  suppressions where unavoidable.

## Risks and mitigations

| Risk | Mitigation |
|---|---|
| Executor rewrite drifts from current matching semantics (ordering, short-circuit, nested traversal) | Phase 1 gate = full existing suite green *before* deleting the assemblers; add order-sensitivity tests first if coverage gaps are found |
| Stricter failures surprise users (enum checks no longer hidden by null values; wrapped exception type) | UPGRADE.md `## 2.0` entry (2.0 unreleased — no BC constraint); `InvalidMatchingPlanException` names the exact class/property/attribute and keeps the original message verbatim |
| Plan cache memory in long-running workers | bounded by the number of mapped classes; plans hold reflection objects only — no instances |
| `ReflectionProperty::getValue()` on PHP 8.4 virtual/hooked properties executes get-hooks during matching | identical to current behavior (values are read today too); note in docs, no change |

## Resolved questions

1. **Static properties with `#[Catch_]`** — keep exact parity: they participate today, and
   `ExceptionMatcherUnitTest::testCaptureExceptionMappedToStaticProperty` pins that behavior. No skip, no
   warning.
2. **Nested plan lookup key** — runtime class of the value (today's behavior); declared-type linting of
   nested classes is already covered by linting every class independently.
3. **C3 severity** (`#[Try_]` with no catches) — warning, and it never blocks plan creation: the
   `RootObject` test stub is `#[Try_]` with zero catches and must keep matching through iterable items.
4. **`PlanRegistry` exposure** — `@api` (future warmup entry point).
5. **Naming** — settled with the maintainer: `MatchingPlan` / `PlanRegistry` / `PlanExecutor` /
   `MatchConditionCompiler` / `MatchConditionBlueprint` (the "Diagnostic"/"Sink" jargon was deliberately
   dropped in favor of the library's plain vocabulary).
6. **No `DefectHandler`** (maintainer decision): the compiler takes no reporting-policy parameter — it
   compiles exactly as it would in production and throws `InvalidMatchingPlanException` on the first
   defect. The lint command drives it eagerly and reports the exceptions (first error per property);
   `MappingDefect` survives only as the linter's report row, built from caught compile failures and the
   linter's own structural findings.
