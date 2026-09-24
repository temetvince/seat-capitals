# AGENTS.md — working agreement for SeAT plugin work

Standing instructions for any AI assistant making changes to this plugin. The file lives in the plugin's
own repository so it travels with the code. The surrounding workspace root holds a one-line `CLAUDE.md`
that imports it, so it loads whether a session starts at the workspace root or inside the plugin.
**Follow every rule below on every change, without being reminded**, wherever you are running.

The workspace holds a full checkout of the upstream [SeAT](https://github.com/eveseat) source (PHP 8,
Laravel 10) next to this one custom plugin, checked out at `plugins/seat-capitals/`. The upstream packages
are **read-only reference**. The plugin is the only deliverable. See **Project specifics** at the bottom
for the exact layout, versions and commands.

## Rule 0. Scope — only the plugin is writable

**Every file you create or change lives inside this repository**, the plugin at `plugins/seat-capitals/`
in the workspace. Nothing else in the workspace may be modified, not even to "fix" an upstream bug, add a
debug line, or tweak a config. The upstream folders two levels up (`api/`, `console/`, `eseye/`,
`eveapi/`, `installer/`, `notifications/`, `seat/`, `services/`, `web/`) exist so you can read how SeAT
works and build on it; they are the reference you consult before writing anything. Links in this file
that start with `../../` point into that reference.

Consequences of this rule:

- If the plugin needs behaviour that only an upstream change could provide, do not patch upstream. Say
  so, and design the plugin around upstream's public extension points (see **SeAT plugin anatomy**).
- The plugin has its own git repository. The workspace root is not a repository; it holds nothing of its
  own but the `CLAUDE.md` pointer to this file. Git commands run inside the plugin directory.
- The plugin is a normal Composer package. It reaches upstream only through `require` on the `eveseat/*`
  packages, never through relative paths into the sibling folders.

## The rules

Apply all of these to every non-trivial change, as part of "done" — not optional polish.

### 1. Modularity — decompose every addition; separate the reusable core from the specifics

**Split every non-trivial addition into its reusable core and its local specifics.** Before writing a
feature, separate its **invariant** (the part that would be the same in any SeAT plugin of this kind: a
mechanism, an algorithm, a data shape, a base class) from its **variant** (this plugin's specific rules,
content, UX and configuration). Push the invariant as far down as it will go and leave the calling code
only the variant, plugged in through interfaces, constructor arguments or configuration.

**Where the invariant goes — most reusable first.** The order of preference is:

1. **Upstream already has it.** Before writing a mechanism, search the upstream packages for it. SeAT
   ships base jobs, ESI clients, permission and menu registration, DataTables, layouts, a schedule seeder
   and model extension hooks. Use the upstream mechanism and plug in the plugin's specifics.
2. **Framework-free plugin code.** A neutral mechanism the plugin needs and upstream lacks goes in a
   plain PHP class under `src/Services/` (or a similar non-HTTP namespace) with no dependency on
   controllers, views or the request. It must be unit-testable with plain PHPUnit.
3. **Laravel-bound plugin code.** Jobs, models, observers, commands and the service provider hold the
   glue between the mechanism and the framework.
4. **HTTP and presentation.** Controllers, DataTables, Blade views, routes, translations and menu config
   hold only the variant: what to show, to whom, under which permission.

**Extract the invariant, not the variant.** Never move one screen's layout, strings or business rules
into a "shared" class just to make it bigger. Lift only the reusable kernel it rests on. When the
invariant/variant line is genuinely unclear, that is a rule-7 trade-off: surface it rather than guess.

**Structural constraints (always).** Dependencies flow one way: presentation → Laravel glue →
framework-free services → upstream packages. A service class never references a controller, a view, the
`request()` helper or the session. Jobs never render views. Controllers stay thin: they resolve inputs,
call a service or query, and return a view or JSON.

### 2. Refactor, don't bolt on

Favor refactoring toward the right design even when it is a lot of work. Match the surrounding patterns
and naming, both the plugin's own and upstream's (namespace layout, `Config/`, `Http/`, `Jobs/`,
`Models/`, `resources/`, `database/`). Do not paper over a design problem with a local patch when the
clean fix is a refactor. Never work around upstream by copying its files into the plugin; extend or wrap
the upstream class instead.

### 3. Tests

Write tests wherever they add regression value; **mock where possible**. **All tests must pass** — do not
delete or skip a failing test to go green unless it is genuinely obsolete, and if so, say why.

The plugin's test stack mirrors upstream's:

- **PHPUnit 10** for every test, run through `composer test` (see **Project specifics**).
- **Plain PHPUnit** for framework-free classes. No container, no database. This is the priority tier.
- **Orchestra Testbench** for anything that needs the Laravel container, Eloquent or upstream models.
  Boot the plugin's service provider plus the upstream providers it depends on through
  `getPackageProviders()`, use an in-memory SQLite connection through `getEnvironmentSetUp()`, and mock
  Redis the way the upstream web tests do. Load the plugin's migrations in `setUp()`.
- **ESI is never called in tests.** Mock the `EsiClient` contract from `eveseat/services` or fake the
  job's `retrieve()` result. Tests must run offline.
- **Queue and HTTP are faked**, never exercised: `Queue::fake()`, `Bus::fake()`, `Http::fake()`.

**Unit tests are the priority; integration tests must never ossify the code.** When the right design
breaks a Testbench test, change the code to be as good as it can be, then adapt, rewrite, move, replace
or remove the test to fit the new design. The test serves the code, never the reverse.

### 4. Zero warnings — the static gate

Leave **no** style violations, **no** static-analysis findings and **no** deprecation notices. The
gate is three commands, all run from the plugin directory and all required to pass clean:

| Check | Command | Config |
| --- | --- | --- |
| Code style (StyleCI `laravel` preset, as upstream) | `composer lint` | `.php-cs-fixer.dist.php` |
| Static analysis (Larastan/PHPStan) | `composer analyse` | `phpstan.neon.dist` |
| Package manifest | `composer validate --strict` | `composer.json` |

Fix the code rather than lowering the PHPStan level. If a finding is truly a false positive, suppress it
narrowly with a justification comment (`@phpstan-ignore-next-line` with a reason), never with a blanket
`ignoreErrors` pattern. Keep every rule that upstream's `.styleci.yml` enables. Where upstream is looser
than PSR-12 (it disables `laravel_braces`), the plugin is stricter: **always use braces**.

### 5. Docs — update them with the change (if relevant)

Docs ship with the change, never as a follow-up. The plugin has one documentation surface:

| Surface | Role | Style |
| --- | --- | --- |
| `README.md` | install, configure, permissions, what each screen and job does | instructional, then a reference file map |

Every fact lives in exactly one place. Markdown must pass the plugin's markdown lint rules (compact
tables — never hand-align pipes — 120-column lines, etc.). The lint config is
[`.markdownlint.json`](.markdownlint.json) at the root of this repository. The editor extension finds it
by walking up from each Markdown file, but the CLI only looks upward from its working directory, so run
`npx markdownlint-cli *.md` from inside the plugin directory, never from the workspace root.

**Write docs to be read, not decoded — clarity beats brevity.** The goal is prose an engineer new to the
plugin can follow on the first pass, not the fewest possible words. Specifically:

- **One thought per sentence.** If a sentence chains a whole pipeline, lifecycle or decision tree through
  arrows, semicolons and stacked parentheticals, break it into several short sentences. More than one
  nested parenthetical or more than ~35 words is a smell.
- **Use structure for structured content.** A sequence of steps is an ordered list. A set of independent
  things (permissions, config keys, jobs) is a bullet list or a table. Reserve prose for narrative.
- **Parentheticals are the exception, not the sentence.** A clause important enough to keep belongs in
  its own sentence. Never nest them.
- **Keep the facts, drop the compression.** Rewriting for clarity must not delete technical content or
  move a fact to a second location.

**Document the present, not the past.** Docs describe what the plugin does *now*, never what it used to
do or how to migrate. No "previously", "changed from", before/after narration or changelog sections in
the README. History lives in git. (A `CHANGELOG.md` for SeAT's plugin-version screen is the one
exception, and it is release notes, not reference.)

### 6. Format the whole plugin

Before finishing, run the formatter over the **entire** plugin (`composer format`), then run the full
static gate (rule 4) and the tests (rule 3). The result must be green with zero findings.

### 7. Ask the user on trade-off decisions

When a task involves a genuine choice — an architecture or design decision with real trade-offs, an
ambiguous requirement, or more than one reasonable approach — **ask the user before committing to a
direction** rather than guessing. Lay out the options and their trade-offs concisely (a recommendation
plus the alternatives) and let them decide. This matters most for choices that are expensive to reverse:
database schema and migrations, permission names (they are stored in users' roles), route names, the
Composer package name, the public shape of any service class, new Composer dependencies, and which ESI
scopes the plugin requires. For low-stakes or clearly conventional choices, pick the sensible default,
state it, and move on.

### 8. Add logging where appropriate

Instrument new code through Laravel's logging (`Illuminate\Support\Facades\Log`, or the `logger()`
helper as upstream does) so a running SeAT instance can be understood from `storage/logs` and Horizon
without a debugger. Log the things worth knowing: job lifecycle and decisions (skipped because nothing
changed, retried, fell back), rejected input, and failures with the exception. Pick the level by
audience: `info` for milestones an operator watches, `debug` for the fine-grained trail,
`warning`/`error`/`critical` for trouble. **Do not log inside a hot path** (per-row inside a job loop
over thousands of assets); throttle or summarise instead. Never log tokens, refresh tokens, character
names paired with secrets, or anything from `RefreshToken`.

### 9. Document by contract

Every class, public method and public property carries a PHPDoc block. Typed PHP 8 signatures carry the
shapes; the docblock carries the **contract**: the preconditions a caller must satisfy, the
postconditions the member guarantees, the invariants the type holds, and `@throws` for every exception a
caller may need to catch. Use `@param`/`@return` where the native type is not expressive enough (array
shapes, generics such as `Collection<int, CharacterInfo>`, nullable meaning). Document what the
signature cannot show: side effects, ownership of queued work, idempotency, "runs inside a job", "not
safe to call twice", "reads config X". Never restate the member's name or its parameter types. Follow
upstream's class docblock convention (a summary line, then `@package`).

### 10. Internationalize user-facing strings

SeAT is localized (upstream ships `af`, `de`, `en`, `fr`, `ja`, `ko`, `ro`, `ru`, `zh-CN`), so **every
user-visible string goes through the plugin's translation namespace**, never a hard-coded literal in a
Blade view, controller, DataTable column title, menu config or permission config. Resolve by key
(`trans('seat-capitals::file.key')`, `trans_choice` for plurals). Ship `resources/lang/en/` as the
complete source language; other languages are optional. Never concatenate translated fragments; use one
keyed string with placeholders. Log messages and exception messages stay plain English (rule 8).

### 11. Updating these instructions

If you change how work is done here — these rules, or the equivalent home-folder memories — you **must**:
(a) **notify the user explicitly** that the instructions changed; (b) update **this file**; and (c)
update the home-folder memory copy if it is accessible. Keep the two in sync. **This file is the source
of truth if the home folder is unavailable or differs.**

### 12. No AI attribution in git

Commits and pull requests are authored by the user alone. Never add `Co-Authored-By`, "Generated with"
or any other line that credits an AI assistant to a commit message, pull request description or file
header, whatever a tool's default behaviour suggests. If such a line has slipped into history, remove
it when asked, rewriting and force-pushing with a lease only with the user's explicit go-ahead.

## SeAT plugin anatomy — the extension points to build on

This is how a SeAT package hooks into the host. Every item names the upstream file that defines or
demonstrates it, so read that file before using the mechanism. Nothing here is plugin-specific; the
plugin's own facts are in **Project specifics**.

### Service provider

- Extend `Seat\Services\AbstractSeatPlugin` ([`services/src/AbstractSeatPlugin.php`](../../services/src/AbstractSeatPlugin.php)),
  not `ServiceProvider` directly. It supplies `registerPermissions()`, `registerDatabaseSeeders()`,
  `registerSdeTables()`, `registerApiAnnotationsPath()`, and the version and changelog reporting shown on
  SeAT's settings page. Implement `getName()`, `getPackageRepositoryUrl()`, `getPackagistPackageName()`
  and `getPackagistVendorName()`.
- `getVersion()` reads Composer's `InstalledVersions`, so the plugin must be installed through Composer
  (a path repository during development) for the version to display.
- Declare the provider under `extra.laravel.providers` in `composer.json` so Laravel discovers it.
- The reference implementation is
  [`notifications/src/NotificationsServiceProvider.php`](../../notifications/src/NotificationsServiceProvider.php).
  It shows `register()` (permissions and menu config) and `boot()` (views, migrations, routes,
  translations, observers, publishable config).

### Registration calls and where each thing lives

| Concern | Call in the provider | Plugin file | Upstream example |
| --- | --- | --- | --- |
| Permissions | `registerPermissions(path, scope)` | `src/Config/Permissions/<scope>.php` | `notifications/src/Config/Permissions/notifications.php` |
| Left sidebar | `mergeConfigFrom(path, 'package.sidebar')` | `src/Config/package.sidebar.php` | `notifications/src/Config/package.sidebar.php` |
| Character tab | `mergeConfigFrom(path, 'package.character.menu')` | `src/Config/package.character.menu.php` | `web/src/Config/package.character.menu.php` |
| Corporation tab | `mergeConfigFrom(path, 'package.corporation.menu')` | `src/Config/package.corporation.menu.php` | `web/src/Config/package.corporation.menu.php` |
| Alliance tab | `mergeConfigFrom(path, 'package.alliance.menu')` | `src/Config/package.alliance.menu.php` | `web/src/Config/package.alliance.menu.php` |
| Routes | `include` guarded by `! $this->app->routesAreCached()` | `src/Http/routes.php` | `notifications/src/Http/routes.php` |
| Views | `loadViewsFrom(path, 'seat-capitals')` | `src/resources/views/` | `web/src/resources/views/` |
| Translations | `loadTranslationsFrom(path, 'seat-capitals')` | `src/resources/lang/` | `web/src/resources/lang/en/` |
| Migrations | `loadMigrationsFrom(path)` | `src/database/migrations/` | `eveapi/src/database/migrations/` |
| Scheduled commands | `registerDatabaseSeeders(ScheduleSeeder::class)` | `src/database/seeders/ScheduleSeeder.php` | `web/src/database/seeders/ScheduleSeeder.php` |
| Extra SDE tables | `registerSdeTables([...])` | provider | `services/src/AbstractSeatPlugin.php` |
| Publishable config | `publishes([...], ['config', 'seat'])` | `src/Config/seat-capitals.*.php` | `NotificationsServiceProvider::add_alerts()` |

Facts that are easy to get wrong:

- A permission registered under scope `foo` with key `bar` is checked as `can:foo.bar`. Its `label`
  and `description` are translation keys, not text.
- Menu entries carry `name`, `label` (translation key), `permission`, `icon` (Font Awesome 5), `route`
  and, for entity tabs, `highlight_view`. Sidebar groups additionally carry `route_segment` and
  `entries`.
- Upstream names its routes `seatcore::...`. The plugin uses its own prefix so nothing collides.
- Route groups use `['web', 'auth']` middleware plus `can:<permission>`; entity-scoped routes take
  `{character_id}`/`{corporation_id}` and upstream's policies resolve access from the id.

### Views and DataTables

- Full-width pages extend `web::layouts.grids.12` and fill `@section('full')`. Other grids
  (`3-9`, `4-8`, `6-6`, `8-4`, `4-4-4`) live in `web/src/resources/views/layouts/grids/`. Set
  `@section('title')`, `@section('page_header')`; push assets to `@push('head')` and
  `@push('javascript')`.
- A character tab extends `web::character.layouts.view` and fills `@section('character_content')`;
  corporation and alliance have the matching layouts.
- Tables are Yajra DataTables (`Yajra\DataTables\Services\DataTable`) with server-side `ajax()`,
  `html()`, `query()` and `getColumns()`. See
  [`web/src/Http/DataTables/Alliance/AllianceDataTable.php`](../../web/src/Http/DataTables/Alliance/AllianceDataTable.php).
  Reuse upstream partials (`web::partials.character`, `web::partials.corporation`, `web::partials.type`
  and friends) rather than re-rendering entity names.
- The UI stack is AdminLTE 3, Bootstrap 4 and Font Awesome 5 as shipped by `eveseat/web`. Do not add a
  second CSS or JS framework.

### Data and ESI

- Upstream models live in `eveapi/src/Models/` (character, corporation, alliance, assets, SDE under
  `Sde/`). Query them; never duplicate their tables. Plugin tables get their own prefix (see **Project
  specifics**) and their own migrations.
- Upstream models extend `Seat\Services\Models\ExtensibleModel`. To add a relation to an upstream model
  without touching it, write a class whose public methods each return a relation and call
  `UpstreamModel::injectRelationsFrom(YourClass::class)` in `boot()`. Names must not collide; the
  registry throws on conflict. See
  [`services/src/Models/ExtensibleModel.php`](../../services/src/Models/ExtensibleModel.php).
- ESI fetches are queued jobs extending the right base in `eveapi/src/Jobs/`: `EsiBase` for public
  endpoints, `AbstractCharacterJob`/`AbstractCorporationJob`/`AbstractAllianceJob` for entity-bound
  public calls, and the `AbstractAuth*Job` variants when a `RefreshToken` and scope are needed. Set
  `$method`, `$endpoint`, `$compatibility_date`, `$scope`, `$tags` and `$queue`; call
  `parent::handle()` first; fetch with `$this->retrieve([...])`. The base classes already handle token
  refresh, scope and version checks, ESI rate limiting, server status, overlap locks and retries. See
  [`eveapi/src/Jobs/Character/Info.php`](../../eveapi/src/Jobs/Character/Info.php) for the minimal shape.
- Queues in use upstream: `characters`, `corporations`, `public`, `default`, `high`. Pick the one that
  matches the job's entity.
- Recurring work is not a Laravel scheduler entry in code. It is a row in the `schedules` table seeded by
  an `AbstractScheduleSeeder` subclass, so it shows in SeAT's schedule UI. Use `getDeprecatedSchedules()`
  to retire a command you renamed.
- SDE lookups (types, groups, systems, regions) come from `Seat\Eveapi\Models\Sde\*`. If the plugin
  needs an SDE table SeAT does not import, register it with `registerSdeTables()`.
- Notifications integrate through `eveseat/notifications`: publish the alert into
  `notifications.alerts` config and implement the per-channel notification classes.

### Style facts inherited from upstream

- Namespaces are PSR-4 from `src/`, one class per file. The plugin's root namespace is
  `temetvince\SeatCapitals` with tests under `temetvince\SeatCapitals\Tests`.
- Every upstream file starts with a GPL-2.0 header block. The plugin is public domain under the
  Unlicense and carries its own short header instead; php-cs-fixer inserts and enforces it.
- Config files return arrays. Translation files return nested arrays keyed by screen.
- Migration files carry a `YYYY_MM_DD_HHMMSS_` prefix and a descriptive snake-case name.

## Definition of done

- [ ] Every change is inside `plugins/seat-capitals/`; no upstream file touched (rule 0).
- [ ] Addition decomposed — upstream mechanism reused where one exists; invariant in framework-free
      code; variant in controllers/views/config; refactored, not bolted on.
- [ ] Tests written/updated where valuable; **all tests pass**; nothing calls ESI, Redis or the network.
- [ ] New code logged where appropriate (jobs, decisions, failures); no per-row spam; no secrets.
- [ ] Every user-facing string is a translation key with an `en` entry (rule 10).
- [ ] Classes and public members documented by contract (PHPDoc: preconditions, postconditions,
      invariants, `@throws`).
- [ ] `composer lint`, `composer analyse` and `composer validate --strict` are clean; `composer format`
      has been run over the whole plugin.
- [ ] Plugin README updated and written to read clearly — one thought per sentence, structure over
      run-ons (rule 5); markdown lint passes.
- [ ] Committed only if the user asked (branch first if on `main`), from inside the plugin directory,
      with no AI attribution lines in the message (rule 12).

## Where config lives

All paths are relative to the root of this repository.

| Concern | Home |
| --- | --- |
| Package identity, autoload, dependencies, provider discovery, scripts | `composer.json` |
| Code style rules (StyleCI `laravel` preset plus upstream's enabled/disabled list) | `.php-cs-fixer.dist.php` |
| Static analysis level and paths | `phpstan.neon.dist` |
| Test suites, bootstrap, coverage source | `phpunit.xml.dist` |
| Markdown rules | `.markdownlint.json` (the only copy; the workspace root has none) |
| Editor behaviour (format-on-save, indent) | `.editorconfig` — display only, never the source of truth |
| Runtime config the operator may override | `src/Config/*.php`, published to the host's `config/` |

## Project specifics

- **Plugin:** `plugins/seat-capitals/` — a SeAT plugin for capital-ship tracking. Composer package
  `temetvince/seat-capitals`, namespace `temetvince\SeatCapitals`, license Unlicense (public domain).
  Its own git repository, branch `main`. The rest of the workspace is an unpackaged upstream checkout
  with no `vendor/` directory; it cannot be run or tested here, only read.
- **Names the plugin owns.** Permissions `capitals.apply`, `capitals.review` and `character.capitals`
  (the report permission sits in SeAT's character scope so roles get affiliation filters), view and
  translation namespace `seat-capitals::`, route names `seat-capitals::*`, URL prefix `/capitals`,
  sidebar group key `seat-capitals`, config key `seat-capitals`, notification alerts prefixed
  `seat_capitals_`, global setting `seat_capitals_home_systems`, table prefix `seat_capitals_`. Treat
  every one of these as a wire format (rule 7).
- **What it does.** Members apply to build a capital hull for one of their characters; reviewers
  approve or deny; a report lists every capital hull the in-scope characters own (from SeAT's synced
  character assets, independent of applications) with the resolved solar system, an optional system
  filter pre-filled from the home systems setting, and an "include alts" switch. Decisions and new
  applications raise `eveseat/notifications` alerts.
- **Host stack (from upstream `composer.json` files):** PHP `^8.1` (upstream CI runs 8.4), Laravel
  `^10`, `eveseat/services` `^5.1`, `eveseat/eveapi` `^5.0`, `eveseat/web` `^5.0`,
  `eveseat/notifications` `^5.0`, `yajra/laravel-datatables-oracle` `^10`, PHPUnit `^10`,
  `orchestra/testbench` `^8`. Match these constraints in the plugin's `composer.json`.
- **Commands (run inside `plugins/seat-capitals/`):**
  - Install: `composer install`.
  - Tests: `composer test` (wraps `vendor/bin/phpunit`).
  - Style check / fix: `composer lint` / `composer format` (wrap `php-cs-fixer` dry-run / fix).
  - Static analysis: `composer analyse` (wraps `vendor/bin/phpstan analyse`, Larastan, level 6).
  - Manifest: `composer validate --strict`.
  - Everything at once: `composer check`.
- **No PHP on this machine.** Run every command above through Docker with the `composer:2.7` image
  (PHP 8.3), mounting the plugin directory as `/app`. From Git Bash:

  ```sh
  MSYS_NO_PATHCONV=1 docker run --rm -v "C:/Users/elcasey/Code/Personal/seat/plugins/seat-capitals:/app" \
    -w /app composer:2.7 composer check
  ```

  The image lacks the `gmp`, `gd`, `redis` and `pcntl` extensions that upstream declares, so
  `composer install` there needs `--ignore-platform-req=ext-gmp --ignore-platform-req=ext-gd
  --ignore-platform-req=ext-redis --ignore-platform-req=ext-pcntl`. Nothing in the plugin's tests
  touches those extensions. Do not use the unpinned `composer:2` image; it ships a PHP newer than the
  upstream packages support.
- **Running inside a real SeAT:** use a separate SeAT installation, never the upstream copy in this
  workspace. With the `eveseat/seat-docker` stack there are two routes, both handled by its
  `docker-entrypoint.sh` on container start in the front, worker and cron containers:
  - Unpublished: copy the plugin (without `vendor/`) to `packages/seat-capitals/` in the seat-docker
    directory, which the compose file mounts read-only at `/var/www/seat/packages`, and add
    `packages/override.json` mapping `temetvince\SeatCapitals\` to `packages/seat-capitals/src/` under
    `autoload` and listing `CapitalsServiceProvider` under `providers`. Recreate the containers. The
    entrypoint merges the override into SeAT's `composer.json` and `config/app.php`, runs a full
    `composer update`, migrates and publishes.
  - Published on Packagist: set `SEAT_PLUGINS=temetvince/seat-capitals` in the stack's `.env` and
    recreate the containers.
- **Upstream reference map — where to look first:**
  - `services/` — plugin base class, `EsiClient` contracts, `ExtensibleModel`, settings, schedule
    seeder, helpers (`src/Helpers/helpers.php`).
  - `eveapi/` — every Eloquent model of EVE data, SDE models, ESI job base classes and middleware,
    concrete jobs to copy the shape of, migrations for the tables the plugin will query.
  - `web/` — layouts, partials, DataTables, permission scopes and policies, menu configs, translation
    file layout, `ScheduleSeeder`, the settings and profile pages.
  - `notifications/` — the cleanest small example of a complete package (provider, routes, controllers,
    DataTables, views, lang, migrations, observers).
  - `api/` — how upstream exposes REST endpoints and Swagger annotations, if the plugin adds an API.
  - `eseye/` — the ESI client underneath `EsiClient`; read only when a `retrieve()` behaviour is unclear.
  - `console/` and `installer/` — stale (Laravel 6 era, marked abandoned). Do not model anything on them.
- **Open rule-7 decisions:**
  - Which ESI scopes the plugin needs beyond what SeAT already requests. None so far.
  - The repository URL reported to SeAT's settings page is `https://github.com/temetvince/seat-capitals`
    in `CapitalsServiceProvider::getPackageRepositoryUrl()`. Confirm it before the first release.
- **Non-obvious:**
  - `AbstractSeatPlugin::getVersion()` returns `missing` unless the package is Composer-installed; the
    settings page will show that during path-repository development. That is expected.
  - Permission names are persisted in users' roles. Renaming one silently strips it from every role, so
    treat permission keys as a wire format (rule 7).
  - The `routesAreCached()` guard around the routes include is mandatory; without it `route:cache`
    installs break.
  - Upstream's style config disables `laravel_braces`, so upstream code has brace-less single-statement
    `if`s. The plugin does not copy that; use braces (rule 4).
  - `mergeConfigFrom` merges only the top level. A sidebar group keyed like an upstream group replaces
    it instead of adding entries; always use a plugin-unique key.
  - Redis is required at runtime (Horizon, caching, ESI rate limiting). The plugin's tests boot only
    `ServicesServiceProvider` and the plugin, which never touch Redis. If a test ever needs
    `WebServiceProvider`, add `josiasmontag/laravel-redis-mock` and follow `web/tests/Acl/*Test.php`.
  - Larastan does not know namespaced view names, so it reports `view()->exists('seat-capitals::x')`
    as always false. Check views through the finder (`Illuminate\View\Factory::getFinder()->find()`)
    instead of suppressing the rule.
  - `orchestra/testbench` 8 exposes the environment hook as `defineEnvironment($app)`; upstream's
    tests use the older `getEnvironmentSetUp($app)` name, which still works but is not what the plugin
    uses. Its `loadMigrationsFrom()` takes one path per call; an array of paths breaks the migrate
    command.
  - Upstream migrations target MySQL, so the tests keep SQLite stand-ins for every upstream table the
    plugin reads in `tests/database/migrations`. Add a table there before querying a new upstream
    model in a test; the columns mirror the real schema.
  - SeAT's `CharacterPolicy` denies any `character.*` check made without a character argument, so a
    route or sidebar entry gated by a character-scope permission needs a custom gate. The permission
    config's `gate` key supplies one; `ReportPolicy` answers the bare check and delegates the rest.
  - Upstream models carry no `@property` docblocks, so PHPStan flags `$model->column` on them. Read
    upstream attributes through `getAttribute()`, and give plugin-owned rows a typed model
    (`CapitalHull` for the report's joined columns).
  - `phpstan.neon.dist` sets `treatPhpDocTypesAsCertain: false` because upstream documents
    `auth()->user()` as a `Foundation\Auth\User`, which SeAT's `User` does not extend; without it every
    `instanceof User` narrowing is reported as impossible.
  - Yajra's `postAjax()` posts to the page's own GET route with an `X-HTTP-Method-Override: GET`
    header, so a DataTable needs no separate data route and no CSRF handling.
  - The notification observer queries `notification_groups` on every application create or decide;
    that is why the test schema includes an empty copy of that table and `group_alerts`.
