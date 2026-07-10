# 06 — Migration Plan

Staged so that each phase ships value on its own and the risky rewrite is bracketed by the existing
behavioral test suite.

## Phase 0 — zero-regret fixes (ship immediately, independent of everything else)

1. **Hoist enum static checks** above the null-value early return in `EnumValueMatchConditionFactory`
   (catalog B11). Five lines; fixes a real runtime blind spot where an unset property hides a broken
   mapping.

*(The `exception:` existence check originally planned here turned out to already exist —
`ExceptionClassMatchCondition::__construct` rejects nonexistent/non-Throwable types; B1/D4 were
mis-cataloged as silent.)* A behavior-strictening bugfix: changelog + UPGRADE note; no API change.

## Phase 1 — plan model behind existing seams (internal rewrite)

- Introduce `MatchingPlan` / `PropertyPlan` / `CatchPlan`, `MappingPlanCompiler` + `DefectHandler`,
  `PlanRegistry`, and the executor ([04-target-model.md](04-target-model.md)).
- Migrate built-in condition factories to `CatchConditionCompiler`s (no value-object extraction — the
  condition constructors, now invoked at compile time, are the single validation home).
- Rewire `MainExceptionMatcher` to registry + executor. Wrap not-yet-migrated / third-party
  `MatchConditionFactory` services in the legacy adapter blueprint.
- Delete the assembler layer, `LazyMatchingRule`, and the generator walk once green.

**Definition of done**: the entire existing test suite passes unchanged (`ServiceTest` / integration /
`BCBreakUnitTest` conventions) — it is the behavioral contract for matching semantics: declaration-order
priority, first-match-wins reciprocation, nested `#[Try_]` objects, iterable items, uninitialized-property
handling, formatter owner-chain data.

## Phase 2 — public extension API

- Publish `CatchConditionCompiler` (+ blueprint interface) as the documented way to add custom `match:`
  conditions; deprecate `MatchConditionFactory` (kept working through the adapter for ≥ one minor line).
- Update `docs/config/match-conditions.md`. The factory→compiler migration is **not** rector-automatable
  (the compile/bind split is semantic) — UPGRADE.md carries a manual guide; `upgrade/2.1.php` gets
  `RenameClassRector` entries only for the moved `ConstantsAutoloadingCompilerPass`/`ConstantsClassLoader`.
  Deprecation mechanics: `@deprecated` annotation + one-time `E_USER_DEPRECATED` in the legacy resolution
  path (no symfony/deprecation-contracts dependency).

## Phase 3 — linter and command

- `MappingLinter` core + defect codes (catalog ids), `lint:exceptional-matcher` command, discovery via
  `composer/class-map-generator` ([05-lint-command.md](05-lint-command.md)).
- composer: `symfony/console` + `composer/class-map-generator` into `require-dev`/`suggest`; extend
  `conflict` envelope for console.
- Docs: README section, a `docs/config/` page for the command, "lint as a unit test" recipe for standalone
  users.
- Dogfood: lint the library's own `src/**/Tests/Stub/` classes in CI; intentionally-broken stubs assert
  their specific defect codes.

## Phase 4 — optional follow-ups

- Cache-warmup validation behind bundle config (`lint.paths`).
- Type-compatibility warnings D1–D3.
- phpbench before/after publication (first-match vs repeated-match of the same class).
- `--format=github` annotations.

## Testing strategy

- **Behavior parity**: rely on existing feature-local suites; extend existing test classes rather than
  creating parallel ones; stubs live in the feature's `Tests/Stub/` directory (established repo convention).
- **Compiler defects**: one test per catalog id, driven by a broken stub each; PHP-version-dependent
  cases (property hooks, B4) use `markTestSkipped` on older runtimes.
- **Legacy adapter**: a stub legacy `MatchConditionFactory` asserting (a) its checks surface in lint via the
  compile-time dry-run, (b) it still receives a real scope at match time.
- **Static analysis**: CI runs PHPStan/Psalm on 8.1 + 8.5 × highest/lowest deps — reflection-of-hooks code
  needs the same version guards as `ExceptionOriginMatchCondition::propertyHookExists()`; prefer inline
  suppressions where unavoidable.

## Risks and mitigations

| Risk | Mitigation |
|---|---|
| Executor rewrite drifts from current matching semantics (ordering, short-circuit, nested traversal) | Phase 1 gate = full existing suite green *before* deleting the assemblers; add order-sensitivity tests first if coverage gaps are found |
| Legacy custom factories violate the "static checks before value-dependent early returns" convention and throw on the compile-time dry-run (null value) | adapter reports dry-run failures of *legacy* factories as **warnings**, not errors — uncertainty is explicit; migrated compilers report errors |
| Stricter failures surprise users (whole-class fail-fast; enum checks no longer hidden by null values; wrapped exception type) | changelog + UPGRADE entry; `InvalidMatchingPlanException` names the exact class/property/attribute and keeps the original message verbatim |
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
   `CatchConditionCompiler` / `MatchConditionBlueprint`; defects are `MappingDefect` (+ `DefectSeverity`,
   `DefectLocation`) reported into a `DefectHandler` (`ThrowingDefectHandler` runtime /
   `CollectingDefectHandler` lint) — the "Diagnostic"/"Sink" jargon was deliberately dropped in favor of
   the library's plain vocabulary.
