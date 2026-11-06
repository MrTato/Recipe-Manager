# Recipe Manager

A WordPress recipe website and portfolio project built with a custom theme,
a custom core plugin, and free plugins only. The MVP uses one content language.
See [PROJECT.md](PROJECT.md) for scope and architecture.

## Requirements

- A working WordPress installation (DevKinsta is used locally)
- Advanced Custom Fields (ACF), free edition
- Elementor Free, optional for static pages

No paid plugins or premium extensions are required.

## Installation and quick start

1. Create a local WordPress site in DevKinsta or your preferred environment.
2. Place this repository in the site's `wp-content` directory. Preserve any
   existing local uploads and third-party plugins.
3. In WordPress administration, install and activate **Advanced Custom Fields**
   (free). Install **Elementor** (free) if you want to edit static pages visually.
4. Once implemented, activate **Recipe Manager Core** under Plugins and
   **Recipe Manager** under Appearance → Themes.
5. After activating the core plugin, visit Settings → Permalinks and save
   your permalink settings if recipe URLs need refreshing.
6. Once recipe editing is implemented, create a recipe, enter its structured
   fields, set a featured image, and publish it.

**Current status:** planning and initial repository setup. The custom plugin
and theme directories currently contain placeholders; steps 4–6 become
available as implementation progresses.

## Repository layout

- `plugins/recipe-manager-core/` — recipe data model and reusable functionality
- `themes/recipe-manager/` — templates, styles, and frontend interactions
- `PROJECT.md` — detailed requirements and development approach

WordPress core, the database, uploads, and third-party plugins are not tracked.
Install dependencies separately for each environment.

## Recipe data helpers

With Recipe Manager Core active, templates can call these read-only helpers:

| Function | Return value |
| --- | --- |
| `rm_get_recipe_ingredients($recipe_id)` | Ordered array with name, mass/volume quantities and units, note, and group. Quantities are positive floats or `null`; unused units are `''`. Defaults to `[]`. |
| `rm_get_recipe_steps($recipe_id)` | Ordered array of `text`, `image_id` (integer, or `0` without an image), and `group`. Defaults to `[]`. |
| `rm_get_recipe_servings($recipe_id)` | Positive integer, defaulting to `1`. |
| `rm_get_recipe_total_minutes($recipe_id)` | Integer preparation plus cooking time; missing or invalid components count as `0`. |

The helpers work without ACF being active and accept recipe post IDs. Invalid
IDs return the same safe defaults. Text is plain text; escape it for the output
context in templates. Invalid collection rows are omitted, invalid measurements
are treated as absent, and unavailable step images become `0`. Step numbers come
from the returned array order. Existing single-measurement ingredients are also
supported.

Serving adjustments should calculate from these original values in memory.
The helpers never update stored recipe metadata.

Run the standalone data-access checks with:

```sh
php plugins/recipe-manager-core/tests/recipe-meta-test.php
```
