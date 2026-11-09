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
4. Activate **Recipe Manager Core** under Plugins and
   **Recipe Manager** under Appearance → Themes.
5. After activating the core plugin, visit Settings → Permalinks and save
   your permalink settings if recipe URLs need refreshing.
6. Create a recipe, enter its structured
   fields, set a featured image, and publish it.

**Current status:** structured recipe editing and persistence are implemented.
The custom theme now renders recipe pages with serving scaling, supported unit
conversions, and a print layout. Archive discovery and filtering remain future work.

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
| `rm_get_recipe_ingredients($recipe_id)` | Ordered array with name, mass/volume quantities and units, optional free-text unit and quantity, legacy note, and group. Quantities are positive floats or `null`; unused units are `''`. Defaults to `[]`. |
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

## Recipe theme

### Public homepage

With Recipe Manager active, the next administrator visit creates a published
**Home** page and assigns it under Settings → Reading → Homepage. An existing
static homepage is preserved and supplies the introductory title and content.
An existing published `/home/` page is reused; an unpublished one is left alone
for review. In that case, publish and assign it manually in Settings → Reading.
Setup runs once, so later Reading settings changes are respected.

Edit the assigned page under Pages to change the introduction. Keep its content
brief and avoid adding another H1. The template supplies search, up to three
featured recipes, up to six populated categories, and up to six recent recipes
excluding those featured above. Mark recipes with **Featured recipe** to curate
the featured section. Empty sections are omitted. All recipe links use existing
WordPress archive, category, and search routes; advanced search remains future work.

Verify the homepage with and without published recipes, featured selections,
images, categories, and the core plugin. Check keyboard access and mobile layout.

Activate **Recipe Manager** under Appearance → Themes to use
`single-rm_recipe.php`. Set the site title under Settings → General and optionally
assign a primary navigation menu under Appearance → Menus. The theme uses stored
recipe data and WordPress media; no example content or images are hard-coded.

Recipe pages render description, servings, preparation/cooking/total time,
ingredient and instruction groups, step images, notes, categories, and tags.
Without JavaScript, the original amounts and complete instructions remain readable.
Controls appear when JavaScript loads. Servings are edited directly beside Prep, Cook,
and Total in the recipe summary, using the number field or −/+ buttons. Print and
Reset sit with the ingredient measurement control. Changing servings recalculates from original
quantities; Reset restores authored amounts and the default measurement mode.
A single button below Ingredients cycles Default → Mass → Volume → Optional unit
for all ingredients. Each mode uses the author's supplied amount in that dimension;
unavailable measurements fall back to the default (mass, then volume, then optional).
The button works with keyboard activation, and ingredient rows remain plain text. Free-text optional
units scale without conversion. Spoon/cup definitions are metric cooking measures,
not US customary measures. Printing uses the currently displayed quantities and
omits navigation, controls, taxonomy links, and photos.

Run the frontend calculation checks without dependencies:

```sh
node themes/recipe-manager/tests/measurement-test.js
```

Before release, verify a published recipe at desktop and mobile widths, keyboard
navigation, JavaScript disabled, and print preview. Include grouped and ungrouped
ingredients and steps, missing images, multiline notes, and ingredients with only
one measurement as well as author-supplied equivalents.
