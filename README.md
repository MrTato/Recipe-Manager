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
