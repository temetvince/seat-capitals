# SeAT Capitals

Capital ship management for [SeAT](https://github.com/eveseat/seat), the EVE Online corporation manager.
This is a SeAT 5 plugin: a Composer package that Laravel discovers automatically once it is installed
into a SeAT instance.

It does three things:

- **Applications.** A member asks to build a capital hull for one of their characters and explains why.
- **Review.** Reviewers approve or deny pending applications, with an optional note to the applicant.
- **Report.** A table of every capital hull owned by the characters in scope, with the solar system each
  hull sits in, filterable by system and extendable to the owners' alts.

Every hull in the report comes from the character assets SeAT already syncs from ESI. Whether a hull
went through an application makes no difference to the report.

## Installation

1. Add the package to the SeAT instance:

   ```sh
   composer require temetvince/seat-capitals
   ```

2. Run the migrations and refresh the caches:

   ```sh
   php artisan migrate
   php artisan config:cache
   php artisan route:cache
   ```

3. Grant permissions to roles (see below). The **Capitals** group appears in the sidebar for every user
   holding at least one of them.

4. Optionally subscribe a notification group to the two **Capitals** alerts, and set the home systems
   under **Capitals > Settings**.

## Permissions

| Permission | Where in the role editor | Grants |
| --- | --- | --- |
| `capitals.apply` | Capitals tab | Submit, follow and withdraw applications for the user's own characters. |
| `capitals.review` | Capitals tab | See every application, approve or deny pending ones, and edit the plugin settings. |
| `character.capitals` | Character tab | Open the report. Its character, corporation and alliance filters decide whose hulls appear. |

The report permission lives in SeAT's character scope on purpose. Set the role's filter to your
corporation and the report shows exactly the hulls of characters in that corporation. Without a filter
the report shows every character SeAT knows. Admin users always see everything.

## Screens

| Screen | Route | Permission | What it shows |
| --- | --- | --- | --- |
| My applications | `/capitals/applications` | `capitals.apply` | The application form and the user's own applications with their status and any reviewer note. |
| Review applications | `/capitals/review` | `capitals.review` | Every application. Pending rows carry approve and deny buttons that open a note dialog. |
| Capital report | `/capitals/report` | `character.capitals` | Every capital hull in scope with its owner, the owner's main, hull, class, ship name, assembled or packaged state, and system. |
| Settings | `/capitals/settings` | `capitals.review` | The home systems that pre-fill the report filter. |

### Report filters

- **Systems.** A multi-select backed by SeAT's system lookup. It starts with the configured home
  systems selected. Clear it to see hulls everywhere.
- **Include alts.** Widens the character set to every character registered under the same SeAT account
  as any character already in scope. This is how a corp member's out-of-corp alts get listed. The
  **Main** column names the account's main character so alts can be attributed.

### How a hull's system is found

ESI reports a location id and a location type per asset. The report resolves them in this order and
takes the first hit: the asset's own system when it is in space, the station or structure it is docked
in, the same two rules applied to the item that contains it, then the map id SeAT's location job derived
from coordinates. A hull whose location cannot be resolved shows an unknown system and is excluded by
any system filter.

## Application lifecycle

| From | To | Who | Rule |
| --- | --- | --- | --- |
| – | Pending | applicant | The character must be linked to the applicant, the hull must be a capital, and no pending application may exist for the same character and hull. |
| Pending | Withdrawn | applicant | Only the applicant. |
| Pending | Approved or Denied | reviewer | Records the reviewer, the note and the time, and writes a line to SeAT's security log. |

Every other transition is refused. Rows are never deleted, so the history stays auditable.

## Notifications

Two alerts are registered with `eveseat/notifications`. Subscribe a notification group to them and each
of its Discord, Slack or mail integrations receives the message.

| Alert | Fires when |
| --- | --- |
| Capitals: new build application | An application is submitted. Links to the review page. |
| Capitals: application approved or denied | A reviewer decides. Links to the applications page. |

Notification text is English, as with SeAT's own notifications, because webhook and mail messages have
no viewer locale.

## Configuration

Publish the config file to change which SDE groups count as capital hulls:

```sh
php artisan vendor:publish --tag=seat-capitals
```

`config/seat-capitals.php` then holds `capital_groups`, a map of `invGroups.groupID` to a label. The
default lists titans, dreadnoughts, lancer dreadnoughts, carriers, supercarriers, force auxiliaries,
capital industrial ships, freighters and jump freighters. A group not listed can neither be applied for
nor appears in the report.

Home systems are stored as a SeAT global setting, edited from the **Settings** screen.

## Development

The plugin is developed against the upstream SeAT source for reference and tested in isolation with
[Orchestra Testbench](https://packages.tools/testbench) on in-memory SQLite. It needs PHP 8.1 or newer
and Composer.

| Task | Command |
| --- | --- |
| Install dependencies | `composer install` |
| Run the tests | `composer test` |
| Check code style | `composer lint` |
| Fix code style | `composer format` |
| Static analysis | `composer analyse` |
| Everything, as CI would | `composer check` |

Upstream's migrations target MySQL, so the tests create SQLite stand-ins for the upstream tables the
plugin reads (`tests/database/migrations`). The plugin's own table comes from its real migration.

To try the plugin inside a running SeAT, add this directory to that instance's `composer.json` as a
path repository, then require it:

```json
{
  "repositories": [
    { "type": "path", "url": "../plugins/seat-capitals" }
  ]
}
```

```sh
composer require temetvince/seat-capitals:@dev
```

SeAT's settings page reports the plugin version as `missing` for a path install. That is expected: the
version comes from Composer's installed-package metadata, which a path repository does not carry.

## File map

| Path | Role |
| --- | --- |
| `src/CapitalsServiceProvider.php` | Registers permissions, sidebar, config, alerts, views, translations, migrations, routes and the model observer. |
| `src/Acl/ReportPolicy.php` | Gate for `character.capitals`; answers the bare check and defers character checks to SeAT's policy. |
| `src/Config/` | Permissions per scope, sidebar group, capital groups, notification alerts. |
| `src/Models/` | `CapitalApplication` and its `ApplicationStatus` enum; `CapitalHull`, the typed read model behind the report. |
| `src/Services/ApplicationWorkflow.php` | The only writer of applications; owns every status transition. |
| `src/Services/CapitalCatalog.php` | Which SDE groups are capitals; lists hulls for the form. |
| `src/Services/CapitalFleetQuery.php` | The report query, including solar system resolution. |
| `src/Services/AltResolver.php` | Widens a character set to same-account characters. |
| `src/Settings/HomeSystems.php` | The home systems global setting. |
| `src/Http/Controllers/` | Thin controllers per screen. |
| `src/Http/DataTables/` | The applications and report tables and their scopes. |
| `src/Http/Validation/` | Form request shape checks. |
| `src/Notifications/` | Discord, Slack and mail notifications for both alerts. |
| `src/Observers/CapitalApplicationObserver.php` | Raises the alerts when applications are created or decided. |
| `src/resources/views/` | Blade views in the `seat-capitals::` namespace. |
| `src/resources/lang/en/` | English translations in the `seat-capitals::` namespace. |
| `src/database/migrations/` | The `seat_capitals_applications` table. |
| `tests/Unit/` | Plain PHPUnit tests for framework-free classes. |
| `tests/Feature/` | Testbench tests for the workflow, report query, alt resolution, settings and provider. |

## License

Released into the public domain under the [Unlicense](LICENSE).
